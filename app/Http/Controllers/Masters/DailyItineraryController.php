<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Driver;
use App\Models\Masters\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyItineraryController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyItinerary::with([
            'groupInfo', 
            'busAssignment.vehicle', 
            'busAssignment.driver'
        ]);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('itinerary', 'like', "%{$search}%")
                  ->orWhere('start_location', 'like', "%{$search}%")
                  ->orWhere('end_location', 'like', "%{$search}%")
                  ->orWhere('vehicle', 'like', "%{$search}%")
                  ->orWhere('driver', 'like', "%{$search}%")
                  ->orWhereHas('groupInfo', function($q) use ($search) {
                      $q->where('group_name', 'like', "%{$search}%")
                        ->orWhere('agency', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('accommodation')) {
            $query->where('accommodation', $request->accommodation);
        }

        $dailyItineraries = $query->orderBy('date', 'asc')
                                 ->orderBy('time_start')
                                 ->paginate(20)
                                 ->withQueryString();

        return view('masters.daily-itineraries.index', compact('dailyItineraries'));
    }

    public function create()
    {
        $groupInfos = GroupInfo::select(['id', 'group_name', 'agency', 'start_date'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $busAssignments = BusAssignment::with('vehicle')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('masters.daily-itineraries.create', compact('groupInfos', 'busAssignments'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'group_info_id' => 'required|integer|exists:group_info,id',
                'date' => 'required|date',
                'time_start' => 'nullable|date_format:H:i',
                'time_end' => 'nullable|date_format:H:i|after:time_start',
                'itinerary' => 'nullable|string|max:200',
                'start_location' => 'nullable|string|max:200',
                'end_location' => 'nullable|string|max:200',
                'accommodation' => 'nullable|boolean',
                'remarks' => 'nullable|string',
                
                'vehicles' => 'nullable|array',
                'vehicles.*.vehicle_id' => 'nullable|exists:vehicles,id',
                'vehicles.*.vehicle_name' => 'nullable|string|max:200',
                'vehicles.*.vehicle_number' => 'nullable|string|max:50',
                'vehicles.*.driver_id' => 'nullable|exists:drivers,id',
                'vehicles.*.driver_name' => 'nullable|string|max:100',
                'vehicles.*.guide' => 'nullable|string|max:100',
                'vehicles.*.seating_capacity' => 'nullable|integer',
                'vehicles.*.vehicle_type' => 'nullable|string|max:100',
                'vehicles.*.vehicle_model' => 'nullable|string|max:200',
                'vehicles.*.vehicle_branch' => 'nullable|string|max:200',
                'vehicles.*.remarks' => 'nullable|string|max:255',
                'vehicles.*.display_order' => 'required|integer|min:1',
            ]);

            $userId = session('user_id', auth()->id() ?? 0);
            
            if (!empty($validated['time_start'])) {
                $validated['time_start'] = $validated['time_start'] . ':00';
            }
            if (!empty($validated['time_end'])) {
                $validated['time_end'] = $validated['time_end'] . ':00';
            }

            $groupInfo = GroupInfo::find($validated['group_info_id']);
            $ignoreOperation = $groupInfo && $groupInfo->ignore_operation;

            if (!$ignoreOperation && !empty($validated['vehicles']) && is_array($validated['vehicles'])) {
                foreach ($validated['vehicles'] as $vehicle) {
                    $this->checkConflict(
                        $vehicle['vehicle_id'] ?? null,
                        $vehicle['driver_id'] ?? null,
                        $validated['date'],
                        $validated['time_start'],
                        $validated['date'],
                        $validated['time_end'],
                        null
                    );
                }
            }

            DB::beginTransaction();

            $vehicleData = $this->processVehicleData($validated['vehicles'] ?? []);
            
            $mainData = array_merge($validated, $vehicleData['main']);
            
            $mainData['created_by'] = $userId;
            $mainData['updated_by'] = $userId;

            $dailyItinerary = DailyItinerary::create($mainData);
            
            if ($request->has('vehicles') && is_array($request->vehicles) && count($request->vehicles) > 0) {
                $this->saveBusAssignments($dailyItinerary, $request->vehicles, $userId);
            }
            
            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '日別旅程を登録しました。',
                    'data' => $dailyItinerary
                ]);
            }

            return redirect()
                ->route('masters.daily-itineraries.index')
                ->with('success', '日別旅程を登録しました。');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '入力内容にエラーがあります。',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', '登録に失敗しました: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $dailyItinerary = DailyItinerary::with([
            'groupInfo', 
            'busAssignment.vehicle', 
            'busAssignment.driver'
        ])->findOrFail($id);

        return view('masters.daily-itineraries.show', compact('dailyItinerary'));
    }

    public function edit($id)
    {
        $dailyItinerary = DailyItinerary::with([
            'groupInfo',
            'busAssignments.vehicle.vehicleType',
            'busAssignments.vehicle.vehicleModel',
            'busAssignments.vehicle.branch',
            'busAssignments.driver'
        ])->findOrFail($id);
        
        $groupItineraries = DailyItinerary::where('group_info_id', $dailyItinerary->group_info_id)
            ->orderBy('date')
            ->orderBy('time_start')
            ->get();
            
        $groupInfos = GroupInfo::select(['id', 'group_name', 'agency'])
            ->orderBy('group_name')
            ->get();
        
        $vehicles = Vehicle::with(['vehicleType', 'vehicleModel', 'branch'])
                          ->where('is_active', true)
                          ->orderBy('display_order', 'asc')
                          ->orderBy('vehicle_code', 'asc')
                          ->get();
        
        $drivers = Driver::with('branch')
                        ->where('is_active', true)
                        ->orderBy('display_order', 'asc')
                        ->orderBy('driver_code', 'asc')
                        ->get();
        
        $guides = Guide::with('branch')
                      ->where('is_active', true)
                      ->orderBy('display_order', 'asc')
                      ->orderBy('guide_code', 'asc')
                      ->get();
        
        return view('masters.daily-itineraries.edit', compact(
            'dailyItinerary', 
            'groupItineraries', 
            'groupInfos', 
            'vehicles',
            'drivers',
            'guides'
        ));
    }

    public function update(Request $request, $id)
    {
        try {
            $dailyItinerary = DailyItinerary::findOrFail($id);
            
            if ($request->has('time_start') && $request->time_start) {
                if (strlen($request->time_start) > 5) {
                    $request->merge(['time_start' => substr($request->time_start, 0, 5)]);
                }
            }
            
            if ($request->has('time_end') && $request->time_end) {
                if (strlen($request->time_end) > 5) {
                    $request->merge(['time_end' => substr($request->time_end, 0, 5)]);
                }
            }
            
            $rules = [
                'group_info_id' => 'required|integer|exists:group_info,id',
                'date' => 'required|date',
                'time_start' => 'required|date_format:H:i',
                'time_end' => 'required|date_format:H:i|after:time_start',
                'itinerary' => 'nullable|string|max:200',
                'start_location' => 'nullable|string|max:200',
                'end_location' => 'nullable|string|max:200',
                'accommodation' => 'nullable|boolean',
                'remarks' => 'nullable|string',
                
                'vehicles' => 'nullable|array',
                'vehicles.*.id' => 'nullable|integer|exists:bus_assignment,id',
                'vehicles.*.vehicle_id' => 'nullable|exists:vehicles,id',
                'vehicles.*.vehicle_name' => 'nullable|string|max:200',
                'vehicles.*.vehicle_number' => 'nullable|string|max:50',
                'vehicles.*.driver_id' => 'nullable|exists:drivers,id',
                'vehicles.*.driver_name' => 'nullable|string|max:100',
                'vehicles.*.guide' => 'nullable|string|max:100',
                'vehicles.*.remarks' => 'nullable|string|max:255',
                'vehicles.*.display_order' => 'required|integer|min:1',
            ];
            
            $messages = [
                'group_info_id.required' => '団体は必須です。',
                'date.required' => '日付は必須です。',
                'time_start.required' => '開始時刻は必須です。',
                'time_start.date_format' => '開始時刻はHH:MM形式で入力してください。',
                'time_end.required' => '終了時刻は必須です。',
                'time_end.date_format' => '終了時刻はHH:MM形式で入力してください。',
                'time_end.after' => '終了時刻は開始時刻より後でなければなりません。',
            ];
            
            $validated = $request->validate($rules, $messages);
            
            $userId = session('user_id', auth()->id() ?? 0);
            $validated['updated_by'] = $userId;
            
            foreach ($validated as $key => $value) {
                if ($value === '') {
                    $validated[$key] = null;
                }
            }
            
            if (isset($validated['accommodation'])) {
                $validated['accommodation'] = filter_var($validated['accommodation'], FILTER_VALIDATE_BOOLEAN);
            }
            
            if (isset($validated['time_start']) && $validated['time_start']) {
                $validated['time_start'] = $validated['time_start'] . ':00';
            }
            
            if (isset($validated['time_end']) && $validated['time_end']) {
                $validated['time_end'] = $validated['time_end'] . ':00';
            }

            $groupInfo = GroupInfo::find($validated['group_info_id']);
            $ignoreOperation = $groupInfo && $groupInfo->ignore_operation;

            if (!$ignoreOperation && !empty($validated['vehicles']) && is_array($validated['vehicles'])) {
                foreach ($validated['vehicles'] as $vehicle) {
                    $this->checkConflict(
                        $vehicle['vehicle_id'] ?? null,
                        $vehicle['driver_id'] ?? null,
                        $validated['date'],
                        $validated['time_start'],
                        $validated['date'],
                        $validated['time_end'],
                        $vehicle['id'] ?? null
                    );
                }
            }
            
            DB::beginTransaction();
            
            $dailyItinerary->update($validated);
            
            if ($request->has('vehicles') && is_array($request->vehicles) && count($request->vehicles) > 0) {
                $this->saveBusAssignments($dailyItinerary, $request->vehicles, $userId);
            }
            
            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '日別旅程を更新しました。',
                    'redirect_url' => route('masters.daily-itineraries.edit', $dailyItinerary->id)
                ]);
            }
            
            return redirect()
                ->route('masters.daily-itineraries.edit', $dailyItinerary->id)
                ->with('success', '日別旅程を更新しました。');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '入力内容にエラーがあります。',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '更新に失敗しました: ' . $e->getMessage());
        }
    }

    private function checkConflict($vehicleId, $driverId, $startDate, $startTime, $endDate, $endTime, $excludeId = null)
    {
        if (empty($vehicleId) && empty($driverId)) {
            return;
        }

        $startDateTime = $startDate . ' ' . ($startTime ?? '00:00:00');
        $endDateTime = $endDate . ' ' . ($endTime ?? '23:59:59');

        if (!empty($vehicleId)) {
            $vehicleQuery = BusAssignment::where('vehicle_id', $vehicleId)
                ->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '<=', $startDateTime)
                           ->where('end_date', '>=', $startDateTime);
                    })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '<=', $endDateTime)
                           ->where('end_date', '>=', $endDateTime);
                    })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '>=', $startDateTime)
                           ->where('end_date', '<=', $endDateTime);
                    });
                });

            if ($excludeId) {
                $vehicleQuery->where('id', '!=', $excludeId);
            }

            if ($vehicleQuery->exists()) {
                throw new \Exception('選択された車両は、指定された時間に既に割り当てられています。');
            }
        }

        if (!empty($driverId)) {
            $driverQuery = BusAssignment::where('driver_id', $driverId)
                ->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '<=', $startDateTime)
                           ->where('end_date', '>=', $startDateTime);
                    })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '<=', $endDateTime)
                           ->where('end_date', '>=', $endDateTime);
                    })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '>=', $startDateTime)
                           ->where('end_date', '<=', $endDateTime);
                    });
                });

            if ($excludeId) {
                $driverQuery->where('id', '!=', $excludeId);
            }

            if ($driverQuery->exists()) {
                throw new \Exception('選択された運転手は、指定された時間に既に割り当てられています。');
            }
        }
    }

    private function saveBusAssignments($dailyItinerary, $vehicles, $userId)
    {
        if (empty($vehicles) || !is_array($vehicles)) {
            return;
        }
        
        usort($vehicles, function($a, $b) {
            return ($a['display_order'] ?? 0) - ($b['display_order'] ?? 0);
        });
        
        $existingAssignments = BusAssignment::where('daily_itinerary_id', $dailyItinerary->id)->get();
        $existingIds = $existingAssignments->pluck('id')->toArray();
        $submittedIds = [];
        
        foreach ($vehicles as $index => $vehicle) {
            $assignmentId = $vehicle['id'] ?? null;
            
            if ($assignmentId && in_array($assignmentId, $existingIds)) {
                $submittedIds[] = $assignmentId;
            }
            
            $busAssignmentData = [
                'group_info_id' => $dailyItinerary->group_info_id,
                'daily_itinerary_id' => $dailyItinerary->id,
                'vehicle_id' => $vehicle['vehicle_id'] ?? null,
                'driver_id' => $vehicle['driver_id'] ?? null,
                'start_date' => $dailyItinerary->date,
                'start_time' => $dailyItinerary->time_start,
                'end_date' => $dailyItinerary->date,
                'end_time' => $dailyItinerary->time_end,
                'lock_arrangement' => 0,
                'status_sent' => 0,
                'status_finalized' => 0,
                'count_daily' => 1,
                'vehicle_number' => $vehicle['vehicle_number'] ?? null,
                'step_car' => $vehicle['step_car'] ?? null,
                'adult_count' => $vehicle['adult_count'] ?? 0,
                'child_count' => $vehicle['child_count'] ?? 0,
                'guide_count' => $vehicle['guide_count'] ?? 0,
                'other_count' => $vehicle['other_count'] ?? 0,
                'luggage_count' => $vehicle['luggage_count'] ?? 0,
                'vehicle_type_spec_check' => $vehicle['vehicle_type_spec_check'] ?? false,
                'temporary_driver' => $vehicle['temporary_driver'] ?? false,
                'representative' => $vehicle['representative'] ?? null,
                'representative_phone' => $vehicle['representative_phone'] ?? null,
                'attention' => $vehicle['attention'] ?? null,
                'operation_remarks' => $vehicle['operation_remarks'] ?? null,
                'operation_memo' => $vehicle['operation_memo'] ?? null,
                'operation_basic_remarks' => $vehicle['operation_basic_remarks'] ?? null,
                'doc_remarks' => $vehicle['doc_remarks'] ?? null,
                'history_remarks' => $vehicle['history_remarks'] ?? null,
                'vehicle_index' => $vehicle['display_order'] ?? ($index + 1),
                'updated_by' => $userId,
                'updated_at' => now(),
            ];
            
            if ($assignmentId && in_array($assignmentId, $existingIds)) {
                BusAssignment::where('id', $assignmentId)->update($busAssignmentData);
            } else {
                $busAssignmentData['created_by'] = $userId;
                $busAssignmentData['created_at'] = now();
                $newAssignment = BusAssignment::create($busAssignmentData);
                $submittedIds[] = $newAssignment->id;
            }
        }
        
        $toDelete = array_diff($existingIds, $submittedIds);
        if (!empty($toDelete)) {
            BusAssignment::whereIn('id', $toDelete)->delete();
        }
        
        $firstVehicle = $vehicles[0] ?? null;
        $dailyItinerary->update([
            'vehicle' => $firstVehicle['vehicle_name'] ?? null,
            'driver' => $firstVehicle['driver_name'] ?? null,
        ]);
    }

    private function processVehicleData($vehicles)
    {
        $mainVehicle = [
            'vehicle' => null,
            'driver' => null,
        ];
        
        $details = [];
        
        if (!empty($vehicles) && is_array($vehicles)) {
            usort($vehicles, function($a, $b) {
                return ($a['display_order'] ?? 0) - ($b['display_order'] ?? 0);
            });
            
            foreach ($vehicles as $index => $vehicle) {
                if ($index === 0) {
                    $mainVehicle['vehicle'] = $vehicle['vehicle_name'] ?? null;
                    $mainVehicle['driver'] = $vehicle['driver_name'] ?? null;
                }
                
                $details[] = $vehicle;
            }
        }
        
        return [
            'main' => $mainVehicle,
            'details' => $details
        ];
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $dailyItinerary = DailyItinerary::findOrFail($id);
            $groupId = $dailyItinerary->group_info_id;
            
            $itineraryCount = DailyItinerary::where('group_info_id', $groupId)->count();
            
            if ($dailyItinerary->bus_assignment_id) {
                BusAssignment::where('id', $dailyItinerary->bus_assignment_id)->delete();
            }
            
            $dailyItinerary->delete();
            
            if ($itineraryCount <= 1) {
                GroupInfo::where('id', $groupId)->delete();
            }

            DB::commit();

            return redirect()->route('masters.daily-itineraries.index')
                ->with([
                    'success' => '日別旅程を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with([
                    'error' => '削除中にエラーが発生しました: ' . $e->getMessage(),
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function byGroup($id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $dailyItineraries = DailyItinerary::where('group_info_id', $id)
                                          ->orderBy('date', 'asc')
                                          ->orderBy('time_start', 'asc')
                                          ->get();
        
        return view('masters.daily-itineraries.by-group', compact('groupInfo', 'dailyItineraries'));
    }
    
    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $itineraries = DailyItinerary::with(['groupInfo', 'busAssignment.vehicle', 'busAssignment.driver'])
            ->whereBetween('date', [$request->date_from, $request->date_to])
            ->orderBy('date')
            ->orderBy('time_start')
            ->get();

        $filename = 'itineraries_' . date('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($itineraries) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', '代理店', '団体名', '日付', '開始時間', '終了時間', '旅程', '出発地', '到着地', 
                '宿泊', '車両', '運転手', '備考'
            ]);
            
            foreach ($itineraries as $itinerary) {
                fputcsv($file, [
                    $itinerary->id,
                    $itinerary->groupInfo->agency ?? '',
                    $itinerary->groupInfo->group_name ?? '',
                    $itinerary->date,
                    $itinerary->time_start,
                    $itinerary->time_end,
                    $itinerary->itinerary,
                    $itinerary->start_location,
                    $itinerary->end_location,
                    $itinerary->accommodation ? '有' : '無',
                    $itinerary->vehicle,
                    $itinerary->driver,
                    $itinerary->remarks,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}