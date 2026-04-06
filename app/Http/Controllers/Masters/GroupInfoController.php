<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Driver;
use App\Models\Masters\Guide;
use App\Models\Masters\Agency;
use App\Models\Masters\ReservationCategory;
use App\Models\Masters\Branch;
use App\Models\Masters\GroupInfoDateRemark;
use App\Models\Masters\BusAssignmentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GroupInfoController extends Controller
{
    public function index(Request $request)
    {
        $query = GroupInfo::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('agency', 'like', "%{$search}%")
                  ->orWhere('vehicle', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $groupInfos = $query->orderBy('start_date', 'asc')
                           ->orderBy('start_time')
                           ->paginate(20)
                           ->withQueryString();

        return view('masters.group-infos.index', compact('groupInfos'));
    }

    public function show($id)
    {
        $groupInfo = GroupInfo::with([
            'busAssignments.vehicle',
            'busAssignments.driver',
            'busAssignments.guide',
            'dailyItineraries'
        ])->findOrFail($id);
        
        $busAssignments = $groupInfo->busAssignments->sortBy('vehicle_index');
        
        $dailyItineraries = $groupInfo->dailyItineraries->sortBy('date');
        
        return view('masters.group-infos.show', compact('groupInfo', 'busAssignments', 'dailyItineraries'));
    }

    public function create(Request $request)
    {
        $agencies = Agency::where('is_active', true)
                         ->orderBy('display_order', 'asc')
                         ->orderBy('agency_code', 'asc')
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
                      
        $reservationCategories = ReservationCategory::where('is_active', true)
            ->orderBy('display_order')
            ->get();
            
        $selectedVehicleId = $request->input('vehicle_id');
        $selectedVehicleName = $request->input('vehicle_name');
        $selectedDriverId = $request->input('driver_id');
        $selectedDriverName = $request->input('driver_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        return view('masters.group-infos.create', compact(
            'agencies', 
            'vehicles', 
            'drivers', 
            'guides', 
            'reservationCategories',
            'selectedVehicleId', 
            'selectedVehicleName',
            'selectedDriverId',
            'selectedDriverName',
            'startDate', 
            'endDate'
        ));
    }

    private function checkConflict($vehicleId, $driverId, $startDate, $startTime, $endDate, $endTime, $excludeBusAssignmentId = null, $excludeGroupInfoId = null)
    {
        if (empty($vehicleId) && empty($driverId)) {
            return;
        }

        if (strpos($startDate, ' ') !== false) {
            $newStart = Carbon::parse($startDate);
        } else {
            $newStart = Carbon::parse($startDate . ' ' . ($startTime ?? '00:00:00'));
        }

        if (strpos($endDate, ' ') !== false) {
            $newEnd = Carbon::parse($endDate);
        } else {
            $newEnd = Carbon::parse($endDate . ' ' . ($endTime ?? '23:59:59'));
        }

        if (!empty($vehicleId)) {
            $query = BusAssignment::where('vehicle_id', $vehicleId);

            if (!empty($excludeBusAssignmentId)) {
                $query->where('id', '!=', $excludeBusAssignmentId);
            }

            if (!empty($excludeGroupInfoId)) {
                $query->where('group_info_id', '!=', $excludeGroupInfoId);
            }

            $existingAssignments = $query->get();

            foreach ($existingAssignments as $assignment) {
                if (strpos($assignment->start_date, ' ') !== false) {
                    $existingStart = Carbon::parse($assignment->start_date);
                } else {
                    $existingStart = Carbon::parse($assignment->start_date . ' ' . ($assignment->start_time ?? '00:00:00'));
                }

                if (strpos($assignment->end_date, ' ') !== false) {
                    $existingEnd = Carbon::parse($assignment->end_date);
                } else {
                    $existingEnd = Carbon::parse($assignment->end_date . ' ' . ($assignment->end_time ?? '23:59:59'));
                }

                if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                    throw new \Exception('選択された車両は、指定された期間に既に割り当てられています。');
                }
            }
        }

        if (!empty($driverId)) {
            $query = BusAssignment::where('driver_id', $driverId);

            if (!empty($excludeBusAssignmentId)) {
                $query->where('id', '!=', $excludeBusAssignmentId);
            }

            if (!empty($excludeGroupInfoId)) {
                $query->where('group_info_id', '!=', $excludeGroupInfoId);
            }

            $existingAssignments = $query->get();

            foreach ($existingAssignments as $assignment) {
                if (strpos($assignment->start_date, ' ') !== false) {
                    $existingStart = Carbon::parse($assignment->start_date);
                } else {
                    $existingStart = Carbon::parse($assignment->start_date . ' ' . ($assignment->start_time ?? '00:00:00'));
                }

                if (strpos($assignment->end_date, ' ') !== false) {
                    $existingEnd = Carbon::parse($assignment->end_date);
                } else {
                    $existingEnd = Carbon::parse($assignment->end_date . ' ' . ($assignment->end_time ?? '23:59:59'));
                }

                if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                    throw new \Exception('選択された運転手は、指定された期間に既に割り当てられています。');
                }
            }
        }
    }

    private function checkResourceConflicts($resourceType, $resourceId, $date, $startTime, $endTime, $excludeBusId = null, $excludeGroupId = null)
    {
        if (empty($resourceId)) {
            return;
        }
        
        if ($excludeGroupId) {
            $groupInfo = GroupInfo::find($excludeGroupId);
            if ($groupInfo && in_array($groupInfo->reservation_status, ['見積', 'キャンセル'])) {
                return;
            }
        }
        
        $query = DailyItinerary::whereDate('date', $date)
            ->whereHas('busAssignment', function($q) use ($resourceType, $resourceId) {
                if ($resourceType === 'vehicle') {
                    $q->where('vehicle_id', $resourceId);
                } elseif ($resourceType === 'driver') {
                    $q->where('driver_id', $resourceId);
                }
            });
        
        if ($excludeBusId) {
            $query->where('bus_assignment_id', '!=', $excludeBusId);
        }
        
        if ($excludeGroupId) {
            $query->whereHas('busAssignment', function($q) use ($excludeGroupId) {
                $q->where('group_info_id', '!=', $excludeGroupId);
            });
        }
        
        $conflictingItineraries = $query->get();
        
        foreach ($conflictingItineraries as $itinerary) {
            $existingStart = Carbon::parse($itinerary->time_start);
            $existingEnd = Carbon::parse($itinerary->time_end);
            $newStart = Carbon::parse($startTime);
            $newEnd = Carbon::parse($endTime);
            
            if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                $otherGroup = GroupInfo::find($itinerary->group_info_id);
                $otherGroupName = $otherGroup ? $otherGroup->group_name : '不明';
                $otherBusId = $itinerary->bus_assignment_id;
                
                if ($resourceType === 'vehicle') {
                    $vehicle = Vehicle::find($resourceId);
                    $resourceName = $vehicle ? $vehicle->registration_number : '不明';
                    throw new \Exception("車両「{$resourceName}」は日付「{$date}」 " . substr($startTime, 0, 5) . "～" . substr($endTime, 0, 5) . " に他のグループ「{$otherGroupName}」(運行ID: {$otherBusId})の運行で既に使用されています。");
                } else {
                    $driver = Driver::find($resourceId);
                    $resourceName = $driver ? $driver->name : '不明';
                    throw new \Exception("運転手「{$resourceName}」は日付「{$date}」 " . substr($startTime, 0, 5) . "～" . substr($endTime, 0, 5) . " に他のグループ「{$otherGroupName}」(運行ID: {$otherBusId})の運行で既に使用されています。");
                }
            }
        }
    }

    private function checkSameGroupConflicts($groupId)
    {
        $busAssignments = BusAssignment::where('group_info_id', $groupId)->get();
        
        if ($busAssignments->count() < 2) {
            return;
        }

        foreach ($busAssignments as $i => $bus1) {
            foreach ($busAssignments as $j => $bus2) {
                if ($i >= $j) continue;

                if ($bus1->vehicle_id && $bus1->vehicle_id === $bus2->vehicle_id) {
                    $startDate1 = $bus1->start_date;
                    if (strpos($startDate1, ' ') !== false) {
                        $startDate1 = explode(' ', $startDate1)[0];
                    }
                    
                    $endDate1 = $bus1->end_date;
                    if (strpos($endDate1, ' ') !== false) {
                        $endDate1 = explode(' ', $endDate1)[0];
                    }
                    
                    $startDate2 = $bus2->start_date;
                    if (strpos($startDate2, ' ') !== false) {
                        $startDate2 = explode(' ', $startDate2)[0];
                    }
                    
                    $endDate2 = $bus2->end_date;
                    if (strpos($endDate2, ' ') !== false) {
                        $endDate2 = explode(' ', $endDate2)[0];
                    }
                    
                    $start1 = Carbon::parse($startDate1 . ' ' . ($bus1->start_time ?? '00:00:00'));
                    $end1 = Carbon::parse($endDate1 . ' ' . ($bus1->end_time ?? '23:59:59'));
                    $start2 = Carbon::parse($startDate2 . ' ' . ($bus2->start_time ?? '00:00:00'));
                    $end2 = Carbon::parse($endDate2 . ' ' . ($bus2->end_time ?? '23:59:59'));

                    if ($start1->lt($end2) && $end1->gt($start2)) {
                        $vehicle = Vehicle::find($bus1->vehicle_id);
                        $vehicleName = $vehicle ? $vehicle->registration_number : '不明';
                        throw new \Exception("同一グループ内で車両「{$vehicleName}」が運行ID {$bus1->id} と {$bus2->id} で重複しています。");
                    }
                }

                if ($bus1->driver_id && $bus1->driver_id === $bus2->driver_id) {
                    $startDate1 = $bus1->start_date;
                    if (strpos($startDate1, ' ') !== false) {
                        $startDate1 = explode(' ', $startDate1)[0];
                    }
                    
                    $endDate1 = $bus1->end_date;
                    if (strpos($endDate1, ' ') !== false) {
                        $endDate1 = explode(' ', $endDate1)[0];
                    }
                    
                    $startDate2 = $bus2->start_date;
                    if (strpos($startDate2, ' ') !== false) {
                        $startDate2 = explode(' ', $startDate2)[0];
                    }
                    
                    $endDate2 = $bus2->end_date;
                    if (strpos($endDate2, ' ') !== false) {
                        $endDate2 = explode(' ', $endDate2)[0];
                    }
                    
                    $start1 = Carbon::parse($startDate1 . ' ' . ($bus1->start_time ?? '00:00:00'));
                    $end1 = Carbon::parse($endDate1 . ' ' . ($bus1->end_time ?? '23:59:59'));
                    $start2 = Carbon::parse($startDate2 . ' ' . ($bus2->start_time ?? '00:00:00'));
                    $end2 = Carbon::parse($endDate2 . ' ' . ($bus2->end_time ?? '23:59:59'));

                    if ($start1->lt($end2) && $end1->gt($start2)) {
                        $driver = Driver::find($bus1->driver_id);
                        $driverName = $driver ? $driver->name : '不明';
                        throw new \Exception("同一グループ内で運転手「{$driverName}」が運行ID {$bus1->id} と {$bus2->id} で重複しています。");
                    }
                }
            }
        }
    }

    private function recalculateGroupTotals($groupId)
    {
        $busAssignments = BusAssignment::where('group_info_id', $groupId)->get();
        
        $totalAdult = 0;
        $totalChild = 0;
        $totalGuide = 0;
        $totalOther = 0;
        $totalLuggage = 0;
        
        foreach ($busAssignments as $busAssignment) {
            $totalAdult += $busAssignment->adult_count ?? 0;
            $totalChild += $busAssignment->child_count ?? 0;
            $totalGuide += $busAssignment->guide_count ?? 0;
            $totalOther += $busAssignment->other_count ?? 0;
            $totalLuggage += $busAssignment->luggage_count ?? 0;
        }
        
        $groupInfo = GroupInfo::find($groupId);
        if ($groupInfo) {
            $groupInfo->update([
                'adult_count' => $totalAdult,
                'child_count' => $totalChild,
                'guide_count' => $totalGuide,
                'other_count' => $totalOther,
                'luggage_count' => $totalLuggage,
            ]);
        }
        
        return [
            'adult_count' => $totalAdult,
            'child_count' => $totalChild,
            'guide_count' => $totalGuide,
            'other_count' => $totalOther,
            'luggage_count' => $totalLuggage,
        ];
    }

    public function store(Request $request)
    {
        $rules = [
            'vehicle_name_input' => 'nullable|string|max:200',
            'guide_name_input' => 'nullable|string|max:100',
            'driver_name_input' => 'nullable|string|max:100',
            'agency_name_input' => 'nullable|string|max:200',
            
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'guide_id' => 'nullable|exists:guides,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'agency_id' => 'nullable|exists:agencies,id',
            
            'agency' => 'nullable|string|max:200',
            'agency_code' => 'nullable|string|max:50',
            'agency_branch' => 'nullable|string|max:100',
            'agency_phone' => 'nullable|string|max:20',
            'group_name' => 'nullable|string|max:200',
            'itinerary_name' => 'nullable|string|max:200',
            'vehicle' => 'nullable|string|max:200',
            'vehicle_number' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required|date_format:H:i',
            'guide' => 'nullable|string|max:100',
            'driver' => 'nullable|string|max:100',
            'reservation_status' => 'nullable|string|max:50',
            'business_category' => 'nullable|string|max:100',
            'vehicle_type_selection' => 'nullable|string|max:200',
            'adult_count' => 'nullable|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'guide_count' => 'nullable|integer|min:0',
            'other_count' => 'nullable|integer|min:0',
            'agency_contact_name' => 'nullable|string|max:100',
            'agency_country' => 'nullable|string|max:100',
            'agt_tour_id' => 'nullable|string|max:100',
            'luggage_count' => 'nullable|integer|min:0',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:200',
            'vehicle_branch' => 'nullable|string|max:200',
            'start_location' => 'nullable|string|max:255',
            'end_location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'ignore_operation' => 'nullable',
            'ignore_attendance' => 'nullable',
            'reservation_channel' => 'nullable|string|max:100',
        ];
    
        $messages = [
            'start_date.required' => '開始日は必須です。',
            'start_date.date' => '開始日の形式が正しくありません。YYYY/MM/DD形式で入力してください。',
            'start_time.required' => '開始時間は必須です。',
            'start_time.date_format' => '開始時間の形式が正しくありません。HH:MM形式で入力してください。',
            'end_date.required' => '終了日は必須です。',
            'end_date.date' => '終了日の形式が正しくありません。YYYY/MM/DD形式で入力してください。',
            'end_time.required' => '終了時間は必須です。',
            'end_time.date_format' => '終了時間の形式が正しくありません。HH:MM形式で入力してください。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付を入力してください。',
            'vehicle_id.exists' => '選択された車両は存在しません。',
            'guide_id.exists' => '選択されたガイドは存在しません。',
            'driver_id.exists' => '選択された運転手は存在しません。',
            'agency_id.exists' => '選択された代理店は存在しません。',
            'adult_count.integer' => '大人人数は数値で入力してください。',
            'child_count.integer' => '子供人数は数値で入力してください。',
            'guide_count.integer' => 'ガイド人数は数値で入力してください。',
            'other_count.integer' => 'その他人数は数値で入力してください。',
            'luggage_count.integer' => '荷物数は数値で入力してください。',
        ];
    
        $validated = $request->validate($rules, $messages);
    
        $validated['ignore_operation'] = $request->has('ignore_operation');
        $validated['ignore_attendance'] = $request->has('ignore_attendance');
        
        
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $reservationStatus = $validated['reservation_status'] ?? null;
        if (!in_array($reservationStatus, ['見積', 'キャンセル'])) {
            $stopOrderDates = GroupInfoDateRemark::getStopOrderDates($startDate, $endDate);
            
            if ($stopOrderDates->isNotEmpty()) {
                $dateList = $stopOrderDates->map(function($date) {
                    return Carbon::parse($date)->format('Y/m/d');
                })->implode('、');
                
                $errorMessage = "以下の日付は受注停止のため、予約できません：{$dateList}";
                
                if ($request->ajax() || $request->wantsJson() || $request->input('iframe') == '1') {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => [
                            'stop_order' => [$errorMessage]
                        ]
                    ], 422);
                }
                
                return back()->withInput()->withErrors([
                    'stop_order' => $errorMessage
                ]);
            }
        }
        
    
        try {
            DB::beginTransaction();
    
            $userId = session('user_id', auth()->id() ?? 0);

            $startTime = !empty($validated['start_time']) ? $validated['start_time'] . ':00' : null;
            $endTime = !empty($validated['end_time']) ? $validated['end_time'] . ':00' : null;
            
            $vehicleInfo = null;
            if (!empty($request->vehicle_id)) {
                $vehicleInfo = Vehicle::with(['vehicleType', 'vehicleModel', 'branch'])
                                     ->find($request->vehicle_id);
            }
    
            $driverInfo = null;
            if (!empty($request->driver_id)) {
                $driverInfo = Driver::find($request->driver_id);
            }
    
            $guideInfo = null;
            if (!empty($request->guide_id)) {
                $guideInfo = Guide::find($request->guide_id);
            }
    
            $agencyInfo = null;
            if (!empty($request->agency_id)) {
                $agencyInfo = Agency::find($request->agency_id);
            }
    
            if (!$validated['ignore_operation'] && (!empty($request->vehicle_id) || !empty($request->driver_id))) {
                $this->checkConflictsByItinerary(
                    $request->vehicle_id,
                    $request->driver_id,
                    $validated['start_date'],
                    $startTime,
                    $validated['end_date'],
                    $endTime,
                    null,
                    $validated['reservation_status']
                );
            }
            
            
            $hasVehicleSpec = $request->has('vehicle_type_spec_check') && $request->vehicle_type_spec_check == 1;
            $finalVehicleSpec = $hasVehicleSpec ? 1 : 0;
    
            $groupData = [
                'vehicle_id' => $request->vehicle_id,
                'driver_id' => $request->driver_id,
                'guide_id' => $request->guide_id,
                'group_name' => $validated['group_name'] ?? null,
                'itinerary_name' => $validated['itinerary_name'] ?? null,
                'agency' => $validated['agency_name_input'] ?? $validated['agency'] ?? null,
                'agency_code' => $validated['agency_code'] ?? ($agencyInfo->agency_code ?? null),
                'agency_branch' => $validated['agency_branch'] ?? ($agencyInfo->branch_name ?? null),
                'agency_phone' => $validated['agency_phone'] ?? ($agencyInfo->phone_number ?? null),
                'agency_contact_name' => $validated['agency_contact_name'] ?? ($agencyInfo->manager_name ?? null),
                'agency_country' => $validated['agency_country'] ?? ($agencyInfo->country ?? null),
                'reservation_status' => $validated['reservation_status'] ?? '予約',
                'start_date' => $validated['start_date'],
                'start_time' => $startTime,
                'end_date' => $validated['end_date'],
                'end_time' => $endTime,
                'vehicle_type_selection' => $finalVehicleSpec,
                'remarks' => $validated['remarks'] ?? null,
                'itinerary_id' => 0,
                'business_category' => $validated['business_category'] ?? null,
                'itinerary_name' => null,
                'reservation_channel' => $validated['reservation_channel'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? ($vehicleInfo->vehicleType->type_name ?? null),
                'vehicle_model' => $validated['vehicle_model'] ?? ($vehicleInfo->vehicleModel->model_name ?? null),
                'vehicle' => $validated['vehicle_name_input'] ?? $validated['vehicle'] ?? ($vehicleInfo->registration_number ?? null),
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'driver' => $validated['driver_name_input'] ?? $validated['driver'] ?? ($driverInfo->name ?? null),
                'guide' => $validated['guide_name_input'] ?? $validated['guide'] ?? ($guideInfo->name ?? null),
                'vehicle_branch' => $validated['vehicle_branch'] ?? ($vehicleInfo->branch->branch_name ?? null),
                'adult_count' => $validated['adult_count'] ?? 0,
                'child_count' => $validated['child_count'] ?? 0,
                'guide_count' => $validated['guide_count'] ?? 0,
                'other_count' => $validated['other_count'] ?? 0,
                'luggage_count' => $validated['luggage_count'] ?? 0,
                'copy_new_start_date' => null,
                'agt_tour_code' => null,
                'agt_tour_id' => $validated['agt_tour_id'] ?? null,
                'ignore_operation' => $validated['ignore_operation'] ? 1 : 0,
                'ignore_attendance' => $validated['ignore_attendance'] ? 1 : 0,
                'reception_contact' => null,
                'reception_office' => null,
                'created_tag' => null,
                'lock_arrangement' => 0,
                'operation_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ];
    
            $groupInfo = GroupInfo::create($groupData);

            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $daysDiff = $startDate->diffInDays($endDate) + 1;

            $busAssignmentData = [
                'group_info_id' => (int)$groupInfo->id,
                'daily_itinerary_id' => null,
                'vehicle_id' => !empty($request->vehicle_id) ? (int)$request->vehicle_id : null,
                'driver_id' => !empty($request->driver_id) ? (int)$request->driver_id : null,
                'guide_id' => !empty($request->guide_id) ? (int)$request->guide_id : null,
                'start_date' => $validated['start_date'],
                'start_time' => $startTime,
                'end_date' => $validated['end_date'],
                'end_time' => $endTime,
                'lock_arrangement' => 0,
                'status_sent' => 0,
                'status_finalized' => 0,
                'count_daily' => (int)$daysDiff,
                'vehicle_number' => '01',
                'step_car' => null,
                'adult_count' => $validated['adult_count'] ?? 0,
                'child_count' => $validated['child_count'] ?? 0,
                'guide_count' => $validated['guide_count'] ?? 0,
                'other_count' => $validated['other_count'] ?? 0,
                'luggage_count' => $validated['luggage_count'] ?? 0,
                'vehicle_type_spec_check' => false,
                'temporary_driver' => false,
                'accompanying' => null,
                'representative' => null,
                'representative_phone' => null,
                'attention' => null,
                'operation_remarks' => null,
                'operation_memo' => null,
                'operation_basic_remarks' => null,
                'doc_remarks' => null,
                'history_remarks' => null,
                'vehicle_index' => 1,
                'created_by' => (int)$userId,
                'updated_by' => (int)$userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $busAssignmentData['vehicle_type_spec_check'] = $finalVehicleSpec;

            $busAssignment = BusAssignment::create($busAssignmentData);

            $createdItineraries = [];

            for ($i = 0; $i < $daysDiff; $i++) {
                $currentDate = $startDate->copy()->addDays($i);
                
                $itineraryText = '';
                if (!empty($validated['start_location']) || !empty($validated['end_location'])) {
                    if ($i == 0) {
                        $itineraryText = trim(
                            ($validated['start_location'] ?? '') . ' → ' . ($validated['end_location'] ?? '')
                        );
                    } elseif ($i == $daysDiff - 1) {
                        $itineraryText = trim(
                            ($validated['end_location'] ?? '') . ' → ' . ($validated['start_location'] ?? '')
                        );
                    } else {
                        $itineraryText = ($validated['end_location'] ?? '目的地') . ' 滞在';
                    }
                }
            
                $startLocation = null;
                $endLocation = null;
                
                if ($i == 0) {
                    $startLocation = $validated['start_location'] ?? null;
                }
                
                if ($i == $daysDiff - 1) {
                    $endLocation = $validated['end_location'] ?? null;
                }

                $vehicleDisplay = $groupData['vehicle'] ?? null;
                $driverDisplay = $groupData['driver'] ?? null;
                $guideDisplay = $groupData['guide'] ?? null;

                $dailyItineraryData = [
                    'group_info_id' => (int)$groupInfo->id,
                    'bus_assignment_id' => (int)$busAssignment->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'time_start' => $startTime,
                    'time_end' => $endTime,
                    'itinerary' => $itineraryText,
                    'start_location' => $startLocation,
                    'end_location' => $endLocation,
                    'accommodation' => ($i < $daysDiff - 1) ? true : false,
                    'vehicle' => $vehicleDisplay,
                    'driver' => $driverDisplay,
                    'guide' => $guideDisplay,
                    'remarks' => $validated['remarks'] ?? null,
                    'created_by' => (int)$userId,
                    'updated_by' => (int)$userId,
                ];

                if (!empty($request->vehicle_id)) {
                    $dailyItineraryData['vehicle_id'] = (int)$request->vehicle_id;
                } else {
                    $dailyItineraryData['vehicle_id'] = null;
                }

                if (!empty($request->driver_id)) {
                    $dailyItineraryData['driver_id'] = (int)$request->driver_id;
                } else {
                    $dailyItineraryData['driver_id'] = null;
                }

                if (!empty($request->guide_id)) {
                    $dailyItineraryData['guide_id'] = (int)$request->guide_id;
                } else {
                    $dailyItineraryData['guide_id'] = null;
                }
            
                $itinerary = DailyItinerary::create($dailyItineraryData);
                $createdItineraries[] = $itinerary;
            }

            $firstItinerary = $createdItineraries[0] ?? null;
            if ($firstItinerary) {
                $busAssignment->update(['daily_itinerary_id' => $firstItinerary->id]);
            }

            $this->recalculateGroupTotals($groupInfo->id);

            DB::commit();
    
            if ($request->ajax() || $request->wantsJson() || $request->input('iframe') == '1') {
                return response()->json([
                    'success' => true,
                    'id' => $groupInfo->id,
                    'message' => "グループ情報と{$daysDiff}日間の旅程を登録しました。",
                    'redirect' => route('masters.group-infos.edit', $groupInfo->id)
                ]);
            }
    
            return redirect()->route('masters.group-infos.edit', $groupInfo->id)
                ->with([
                    'success' => "グループ情報と{$daysDiff}日間の旅程を登録しました。",
                    'alert-type' => 'success'
                ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson() || $request->input('iframe') == '1') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => [
                        'system' => [$e->getMessage()]
                    ]
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '保存中にエラーが発生しました: ' . $e->getMessage(),
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function edit($id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $agencies = Agency::where('is_active', true)
                         ->orderBy('display_order', 'asc')
                         ->orderBy('agency_code', 'asc')
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
                      
        $branches = Branch::orderBy('display_order', 'asc')
                         ->orderBy('branch_code', 'asc')
                         ->get();
        
        $reservationCategories = ReservationCategory::where('is_active', true)
                                                   ->orderBy('display_order', 'asc')
                                                   ->get();
        
        $busAssignments = BusAssignment::where('group_info_id', $groupInfo->id)
                                      ->orderBy('vehicle_index', 'asc')
                                      ->get();

        $allItineraries = DailyItinerary::where('group_info_id', $groupInfo->id)
                                        ->orderBy('date', 'asc')
                                        ->orderBy('id', 'asc')
                                        ->get();

        $groupedItineraries = [];
        $uniqueVehicles = [];

        foreach ($allItineraries as $itinerary) {
            $groupKey = $itinerary->bus_assignment_id ?? ($itinerary->vehicle_id ?? 0);
            
            if (!isset($groupedItineraries[$groupKey])) {
                $vehicleInfo = null;
                if ($itinerary->vehicle_id && $itinerary->vehicle_id > 0) {
                    $vehicleInfo = $vehicles->firstWhere('id', $itinerary->vehicle_id);
                }
                
                $busAssignment = null;
                if ($itinerary->bus_assignment_id) {
                    $busAssignment = $busAssignments->firstWhere('id', $itinerary->bus_assignment_id);
                }
            
                $busId = $busAssignment->id ?? $groupKey;
                
                $logs = BusAssignmentLog::where('bus_assignment_id', $busId)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $groupedItineraries[$groupKey] = [
                    'vehicle_id' => $itinerary->vehicle_id,
                    'vehicle_name' => $vehicleInfo ? $vehicleInfo->registration_number : ($itinerary->vehicle ?? '未分配车辆'),
                    'vehicle_model' => $vehicleInfo && $vehicleInfo->vehicleModel ? $vehicleInfo->vehicleModel->model_name : '',
                    'driver_name' => $itinerary->driver ?? '',
                    'bus_assignment' => $busAssignment,
                    'itineraries' => [],
                    'logs' => $logs
                ];
                
                if ($itinerary->vehicle_id && $itinerary->vehicle_id > 0 && !in_array($itinerary->vehicle_id, array_column($uniqueVehicles, 'id'))) {
                    $uniqueVehicles[] = [
                        'id' => $itinerary->vehicle_id,
                        'name' => $vehicleInfo ? $vehicleInfo->registration_number : $itinerary->vehicle,
                        'model' => $vehicleInfo && $vehicleInfo->vehicleModel ? $vehicleInfo->vehicleModel->model_name : ''
                    ];
                }
            }
            
            $groupedItineraries[$groupKey]['itineraries'][] = $itinerary;
        }
        
        foreach ($groupedItineraries as &$group) {
            usort($group['itineraries'], function($a, $b) {
                return strtotime($a->date) - strtotime($b->date);
            });
        }
        
        if (isset($groupedItineraries[0])) {
            $groupedItineraries[0]['vehicle_name'] = '未分配车辆';
        }
        
        $hasMultipleVehicles = count($groupedItineraries) > 1;
        
        return view('masters.group-infos.edit', compact(
            'groupInfo', 
            'agencies',
            'vehicles', 
            'drivers', 
            'guides', 
            'busAssignments', 
            'allItineraries',
            'groupedItineraries',
            'uniqueVehicles',
            'hasMultipleVehicles',
            'branches',
            'reservationCategories'
        ));
    }

    public function update(Request $request, $id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
    
        $rules = [
            'agt_tour_id' => 'nullable|string|max:200',
            'agency' => 'nullable|string|max:200',
            'agency_code' => 'nullable|string|max:50',
            'agency_branch' => 'nullable|string|max:100',
            'agency_phone' => 'nullable|string|max:20',
            'group_name' => 'nullable|string|max:200',
            'itinerary_name' => 'nullable|string|max:200',
            'vehicle' => 'nullable|string|max:200',
            'vehicle_number' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'nullable|date_format:H:i',
            'guide' => 'nullable|string|max:100',
            'guide_id' => 'nullable|exists:guides,id',
            'driver' => 'nullable|string|max:100',
            'reservation_status' => 'nullable|string|max:50',
            'business_category' => 'nullable|string|max:100',
            'reservation_categories_id' => 'nullable|integer',
            'vehicle_type_selection' => 'nullable|string|max:200',
            'adult_count' => 'nullable|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'guide_count' => 'nullable|integer|min:0',
            'other_count' => 'nullable|integer|min:0',
            'agency_contact_name' => 'nullable|string|max:100',
            'agency_country' => 'nullable|string|max:100',
            'luggage_count' => 'nullable|integer|min:0',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:200',
            'vehicle_branch' => 'nullable|string|max:200',
            'remarks' => 'nullable|string',
            'ignore_operation' => 'nullable',
            'ignore_attendance' => 'nullable',
            'reservation_channel' => 'nullable|string|max:100',
            
            'bus_assignments' => 'sometimes|array',
            'bus_assignments.*.id' => 'nullable|string',
            'bus_assignments.*.vehicle_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !Vehicle::where('id', $value)->exists()) {
                        $fail('選択された車両は存在しません。');
                    }
                },
            ],
            'bus_assignments.*.driver_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !Driver::where('id', $value)->exists()) {
                        $fail('選択された運転手は存在しません。');
                    }
                },
            ],
            'bus_assignments.*.guide_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !Guide::where('id', $value)->exists()) {
                        $fail('選択された添乗員は存在しません。');
                    }
                },
            ],
            'bus_assignments.*.vehicle_number' => 'nullable|string|max:50',
            'bus_assignments.*.step_car' => 'nullable|string|max:255',
            'bus_assignments.*.adult_count' => 'nullable|integer|min:0',
            'bus_assignments.*.child_count' => 'nullable|integer|min:0',
            'bus_assignments.*.guide_count' => 'nullable|integer|min:0',
            'bus_assignments.*.other_count' => 'nullable|integer|min:0',
            'bus_assignments.*.luggage_count' => 'nullable|integer|min:0',
            'bus_assignments.*.vehicle_type_spec_check' => 'nullable|boolean',
            'bus_assignments.*.temporary_driver' => 'nullable|boolean',
            'bus_assignments.*.accompanying' => 'nullable|string|max:100',
            'bus_assignments.*.representative' => 'nullable|string|max:100',
            'bus_assignments.*.representative_phone' => 'nullable|string|max:20',
            'bus_assignments.*.attention' => 'nullable|string',
            'bus_assignments.*.operation_remarks' => 'nullable|string',
            'bus_assignments.*.operation_memo' => 'nullable|string',
            'bus_assignments.*.operation_basic_remarks' => 'nullable|string',
            'bus_assignments.*.doc_remarks' => 'nullable|string',
            'bus_assignments.*.history_remarks' => 'nullable|string',
            'bus_assignments.*.lock_arrangement' => 'nullable|boolean',
            'bus_assignments.*.status_sent' => 'nullable|boolean',
            'bus_assignments.*.status_finalized' => 'nullable|boolean',
            'bus_assignments.*.vehicle_index' => 'required|integer|min:1',
            
            'daily_itineraries' => 'sometimes|array',
            'daily_itineraries.*.id' => 'nullable|integer|exists:daily_itinerary,id',
            'daily_itineraries.*.date' => 'required|date',
            'daily_itineraries.*.time_start' => 'required|date_format:H:i',
            'daily_itineraries.*.time_end' => 'required|date_format:H:i',
            'daily_itineraries.*.start_location' => 'nullable|string|max:255',
            'daily_itineraries.*.end_location' => 'nullable|string|max:255',
            'daily_itineraries.*.itinerary' => 'nullable|string',
            'daily_itineraries.*.vehicle_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !Vehicle::where('id', $value)->exists()) {
                        $fail('選択された車両は存在しません。');
                    }
                },
            ],
            'daily_itineraries.*.driver_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !Driver::where('id', $value)->exists()) {
                        $fail('選択された運転手は存在しません。');
                    }
                },
            ],
            'daily_itineraries.*.guide_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !Guide::where('id', $value)->exists()) {
                        $fail('選択された添乗員は存在しません。');
                    }
                },
            ],
            'daily_itineraries.*.bus_assignment_id' => 'nullable|string',
            'daily_itineraries.*.accommodation' => 'nullable|boolean',
            'daily_itineraries.*.remarks' => 'nullable|string|max:255',
            'daily_itineraries.*.display_order' => 'required|integer|min:1',
            'daily_itineraries.*.vehicle_group' => 'nullable|integer',
            
            'deleted_itineraries' => 'sometimes|array',
            'deleted_itineraries.*' => 'integer|exists:daily_itinerary,id',
        ];
    
        $messages = [
            'start_date.required' => '開始日は必須です。',
            'end_date.required' => '終了日は必須です。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付を入力してください。',
            'daily_itineraries.*.date.required' => 'すべての旅程の運行日は必須です。',
            'daily_itineraries.*.time_start.required' => 'すべての旅程の開始時刻は必須です。',
            'daily_itineraries.*.time_end.required' => 'すべての旅程の終了時刻は必須です。',
            'daily_itineraries.*.time_start.date_format' => '開始時刻はH:i形式で入力してください。',
            'daily_itineraries.*.time_end.date_format' => '終了時刻はH:i形式で入力してください。',
            'adult_count.integer' => '大人人数は数値で入力してください。',
            'child_count.integer' => '子供人数は数値で入力してください。',
            'guide_count.integer' => 'ガイド人数は数値で入力してください。',
            'other_count.integer' => 'その他人数は数値で入力してください。',
            'luggage_count.integer' => '荷物数は数値で入力してください。',
        ];
    
        $validated = $request->validate($rules, $messages);
    
        $validated['ignore_operation'] = $request->has('ignore_operation');
        $validated['ignore_attendance'] = $request->has('ignore_attendance');
    
    
        $startDate = $validated['start_date'] ?? $groupInfo->start_date;
        $endDate = $validated['end_date'] ?? $groupInfo->end_date;
        $reservationStatus = $validated['reservation_status'] ?? $groupInfo->reservation_status;
        if (!in_array($reservationStatus, ['見積', 'キャンセル'])) {
            $stopOrderDates = GroupInfoDateRemark::getStopOrderDates($startDate, $endDate);
            
            if ($stopOrderDates->isNotEmpty()) {
                $dateList = $stopOrderDates->map(function($date) {
                    return Carbon::parse($date)->format('Y/m/d');
                })->implode('、');
                
                $errorMessage = "以下の日付は受注停止のため、予約できません：{$dateList}";
                
                if ($request->ajax() || $request->wantsJson() || $request->input('iframe') == '1') {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => [
                            'stop_order' => [$errorMessage]
                        ]
                    ], 422);
                }
                
                return back()->withInput()->withErrors([
                    'stop_order' => $errorMessage
                ]);
            }
        }
    
        try {
            DB::beginTransaction();
    
            $userId = session('user_id', auth()->id() ?? 0);
            
            $guideId = null;
            $guideName = null;
            
            if ($request->filled('guide_id')) {
                $guideId = $request->guide_id;
                $guide = Guide::find($guideId);
                $guideName = $guide ? $guide->name : '';
            }
            
            $vehicleInfo = null;
            if (!empty($request->vehicle_id)) {
                $vehicleInfo = Vehicle::with(['vehicleType', 'vehicleModel', 'branch'])
                                ->find($request->vehicle_id);
            }
    
            $driverInfo = null;
            if (!empty($request->driver_id)) {
                $driverInfo = Driver::find($request->driver_id);
            }
    
            $guideInfo = null;
            if (!empty($guideId)) {
                $guideInfo = Guide::find($guideId);
                if ($guideInfo) {
                    $guideName = $guideInfo->name;
                }
            }
    
            $agencyInfo = null;
            if (!empty($request->agency_id)) {
                $agencyInfo = Agency::find($request->agency_id);
            }
    
            if ($request->has('deleted_itineraries')) {
                $deletedIds = $request->input('deleted_itineraries');
                if (is_array($deletedIds) && !empty($deletedIds)) {
                    DailyItinerary::whereIn('id', $deletedIds)
                            ->where('group_info_id', $groupInfo->id)
                            ->delete();
                }
            }
    
            $existingBusIds = BusAssignment::where('group_info_id', $groupInfo->id)
                                        ->pluck('id')
                                        ->toArray();
    
            $newBusAssignmentsToCreate = [];
            if ($request->has('bus_assignments') && is_array($request->bus_assignments)) {
                foreach ($request->bus_assignments as $index => $busData) {
                    if (empty($busData['id']) || 
                        (is_string($busData['id']) && (strpos($busData['id'], 'copy_') === 0 || strpos($busData['id'], 'split_') === 0))) {
                        $newBusAssignmentsToCreate[$index] = $busData;
                    }
                }
            }
    
            $newlyCreatedBusIds = [];
            $newBusMappings = [];
    
            foreach ($newBusAssignmentsToCreate as $newBusIndex => $newBusData) {
                $tempBusId = $newBusData['id'] ?? null;
                
                $itinerariesForThisBus = collect($request->daily_itineraries)
                    ->filter(function($item) use ($newBusIndex) {
                        return isset($item['vehicle_group']) && $item['vehicle_group'] == $newBusIndex;
                    })
                    ->values();
                
                if ($itinerariesForThisBus->isEmpty() && $tempBusId) {
                    $itinerariesForThisBus = collect($request->daily_itineraries)
                        ->filter(function($item) use ($tempBusId) {
                            return isset($item['bus_assignment_id']) && $item['bus_assignment_id'] === $tempBusId;
                        })
                        ->values();
                }
                
                if ($itinerariesForThisBus->isEmpty()) {
                    continue;
                }
                
                $firstItinerary = $itinerariesForThisBus->first();
                $lastItinerary = $itinerariesForThisBus->last();
                
                $startDate = $firstItinerary['date'] ?? $groupInfo->start_date;
                $startTime = isset($firstItinerary['time_start']) ? $firstItinerary['time_start'] . ':00' : ($groupInfo->start_time ?? '08:00:00');
                $endDate = $lastItinerary['date'] ?? $groupInfo->end_date;
                $endTime = isset($lastItinerary['time_end']) ? $lastItinerary['time_end'] . ':00' : ($groupInfo->end_time ?? '18:00:00');
                
                $vehicleName = '';
                if (!empty($newBusData['vehicle_id']) && $newBusData['vehicle_id'] > 0) {
                    $vehicle = Vehicle::find($newBusData['vehicle_id']);
                    $vehicleName = $vehicle ? $vehicle->registration_number : '';
                }
                
                $driverName = '';
                if (!empty($newBusData['driver_id']) && $newBusData['driver_id'] > 0) {
                    $driver = Driver::find($newBusData['driver_id']);
                    $driverName = $driver ? $driver->name : '';
                }
                
                $guideNameForBus = '';
                $guideIdForBus = $newBusData['guide_id'] ?? null;
                if ($guideIdForBus) {
                    $guide = Guide::find($guideIdForBus);
                    $guideNameForBus = $guide ? $guide->name : '';
                }
                
                $maxIndex = BusAssignment::where('group_info_id', $groupInfo->id)->max('vehicle_index') ?? 0;
                $newVehicleIndex = $maxIndex + 1;
                
                $busAssignmentData = [
                    'group_info_id' => $groupInfo->id,
                    'daily_itinerary_id' => $firstItinerary ? ($firstItinerary['id'] ?? null) : null,
                    'vehicle_id' => !empty($newBusData['vehicle_id']) && $newBusData['vehicle_id'] > 0 ? $newBusData['vehicle_id'] : null,
                    'driver_id' => !empty($newBusData['driver_id']) && $newBusData['driver_id'] > 0 ? $newBusData['driver_id'] : null,
                    'guide_id' => $guideIdForBus,
                    'start_date' => $startDate,
                    'start_time' => $startTime,
                    'end_date' => $endDate,
                    'end_time' => $endTime,
                    'lock_arrangement' => isset($newBusData['lock_arrangement']) ? (bool)$newBusData['lock_arrangement'] : false,
                    'status_sent' => isset($newBusData['status_sent']) ? (bool)$newBusData['status_sent'] : false,
                    'status_finalized' => isset($newBusData['status_finalized']) ? (bool)$newBusData['status_finalized'] : false,
                    'count_daily' => $itinerariesForThisBus->count(),
                    'vehicle_number' => $newBusData['vehicle_number'] ?? sprintf('%02d', $newVehicleIndex),
                    'step_car' => $newBusData['step_car'] ?? null,
                    'adult_count' => $newBusData['adult_count'] ?? $groupInfo->adult_count ?? 0,
                    'child_count' => $newBusData['child_count'] ?? $groupInfo->child_count ?? 0,
                    'guide_count' => $newBusData['guide_count'] ?? 0,
                    'other_count' => $newBusData['other_count'] ?? 0,
                    'luggage_count' => $newBusData['luggage_count'] ?? 0,
                    'vehicle_type_spec_check' => isset($newBusData['vehicle_type_spec_check']) ? (bool)$newBusData['vehicle_type_spec_check'] : false,
                    'temporary_driver' => isset($newBusData['temporary_driver']) ? (bool)$newBusData['temporary_driver'] : false,
                    'accompanying' => $newBusData['accompanying'] ?? null,
                    'representative' => $newBusData['representative'] ?? null,
                    'representative_phone' => $newBusData['representative_phone'] ?? null,
                    'attention' => $newBusData['attention'] ?? null,
                    'operation_remarks' => $newBusData['operation_remarks'] ?? null,
                    'operation_memo' => $newBusData['operation_memo'] ?? null,
                    'operation_basic_remarks' => $newBusData['operation_basic_remarks'] ?? null,
                    'doc_remarks' => $newBusData['doc_remarks'] ?? null,
                    'history_remarks' => $newBusData['history_remarks'] ?? null,
                    'vehicle_index' => $newVehicleIndex,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                $newBus = BusAssignment::create($busAssignmentData);
                $newlyCreatedBusIds[] = $newBus->id;
                $newBusMappings[$tempBusId] = $newBus->id;
                $newBusMappings[$newBusIndex] = $newBus->id;
                
                $updatedItineraryIds = [];
                foreach ($itinerariesForThisBus as $itineraryData) {
                    if (!empty($itineraryData['id'])) {
                        $updatedItineraryIds[] = $itineraryData['id'];
                    }
                    
                    $newItineraryData = [
                        'group_info_id' => $groupInfo->id,
                        'bus_assignment_id' => $newBus->id,
                        'date' => $itineraryData['date'],
                        'time_start' => ($itineraryData['time_start'] ?? '08:00') . ':00',
                        'time_end' => ($itineraryData['time_end'] ?? '18:00') . ':00',
                        'itinerary' => $itineraryData['itinerary'] ?? '',
                        'start_location' => $itineraryData['start_location'] ?? null,
                        'end_location' => $itineraryData['end_location'] ?? null,
                        'accommodation' => isset($itineraryData['accommodation']) ? (bool)$itineraryData['accommodation'] : false,
                        'vehicle_id' => isset($newBusData['vehicle_id']) && $newBusData['vehicle_id'] > 0 ? (int)$newBusData['vehicle_id'] : 0,
                        'vehicle' => $vehicleName,
                        'driver_id' => isset($newBusData['driver_id']) && $newBusData['driver_id'] > 0 ? (int)$newBusData['driver_id'] : 0,
                        'driver' => $driverName,
                        'guide_id' => $guideIdForBus ? (int)$guideIdForBus : 0,
                        'guide' => $guideNameForBus,
                        'remarks' => $itineraryData['remarks'] ?? null,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    if (!empty($itineraryData['id'])) {
                        $existingItinerary = DailyItinerary::find($itineraryData['id']);
                        if ($existingItinerary) {
                            $existingItinerary->update($newItineraryData);
                        }
                    } else {
                        DailyItinerary::create($newItineraryData);
                    }
                }
                
                if (!empty($updatedItineraryIds)) {
                    DailyItinerary::whereIn('id', $updatedItineraryIds)
                        ->where('group_info_id', $groupInfo->id)
                        ->where('bus_assignment_id', '!=', $newBus->id)
                        ->update([
                            'vehicle_id' => 0,
                            'driver_id' => 0,
                            'guide_id' => 0
                        ]);
                }
                
                $existingBusIds[] = $newBus->id;
            }
    
            if (!empty($newBusMappings)) {
                $updatedBusAssignments = [];
                foreach ($request->bus_assignments as $idx => $busData) {
                    $originalId = $busData['id'] ?? null;
                    if ($originalId && isset($newBusMappings[$originalId])) {
                        $busData['id'] = $newBusMappings[$originalId];
                    } elseif (isset($newBusMappings[$idx])) {
                        $busData['id'] = $newBusMappings[$idx];
                    }
                    $updatedBusAssignments[$idx] = $busData;
                }
                $request->merge(['bus_assignments' => $updatedBusAssignments]);
            }
    
            $filteredItineraries = [];
            foreach ($request->daily_itineraries as $itineraryData) {
                $vehicleGroup = $itineraryData['vehicle_group'] ?? null;
                if (!$vehicleGroup || !isset($newBusMappings[$vehicleGroup])) {
                    $filteredItineraries[] = $itineraryData;
                }
            }
            $request->merge(['daily_itineraries' => $filteredItineraries]);
    
            $submittedBusIds = [];
            $busAssignmentDataArray = [];
    
            if ($request->has('bus_assignments') && is_array($request->bus_assignments)) {
                foreach ($request->bus_assignments as $busData) {
                    $busId = $busData['id'] ?? null;
                    if ($busId && is_numeric($busId)) {
                        $submittedBusIds[] = (int)$busId;
                    }
                    
                    $busDataGuideId = $busData['guide_id'] ?? null;
                    
                    $busAssignmentDataArray[] = [
                        'id' => $busId,
                        'group_info_id' => $groupInfo->id,
                        'vehicle_id' => !empty($busData['vehicle_id']) && $busData['vehicle_id'] > 0 ? $busData['vehicle_id'] : null,
                        'driver_id' => !empty($busData['driver_id']) && $busData['driver_id'] > 0 ? $busData['driver_id'] : null,
                        'guide_id' => $busDataGuideId,
                        'lock_arrangement' => isset($busData['lock_arrangement']) ? (bool)$busData['lock_arrangement'] : false,
                        'status_sent' => isset($busData['status_sent']) ? (bool)$busData['status_sent'] : false,
                        'status_finalized' => isset($busData['status_finalized']) ? (bool)$busData['status_finalized'] : false,
                        'vehicle_number' => $busData['vehicle_number'] ?? sprintf('%02d', $busData['vehicle_index'] ?? 1),
                        'step_car' => $busData['step_car'] ?? null,
                        'adult_count' => $busData['adult_count'] ?? 0,
                        'child_count' => $busData['child_count'] ?? 0,
                        'guide_count' => $busData['guide_count'] ?? 0,
                        'other_count' => $busData['other_count'] ?? 0,
                        'luggage_count' => $busData['luggage_count'] ?? 0,
                        'vehicle_type_spec_check' => isset($busData['vehicle_type_spec_check']) ? (bool)$busData['vehicle_type_spec_check'] : false,
                        'temporary_driver' => isset($busData['temporary_driver']) ? (bool)$busData['temporary_driver'] : false,
                        'accompanying' => $busData['accompanying'] ?? null,
                        'representative' => $busData['representative'] ?? null,
                        'representative_phone' => $busData['representative_phone'] ?? null,
                        'attention' => $busData['attention'] ?? null,
                        'operation_remarks' => $busData['operation_remarks'] ?? null,
                        'operation_memo' => $busData['operation_memo'] ?? null,
                        'operation_basic_remarks' => $busData['operation_basic_remarks'] ?? null,
                        'doc_remarks' => $busData['doc_remarks'] ?? null,
                        'history_remarks' => $busData['history_remarks'] ?? null,
                        'vehicle_index' => $busData['vehicle_index'] ?? 1,
                        'updated_by' => $userId,
                        'updated_at' => now(),
                    ];
                }
            }
            
            $username = session('username', auth()->user()->name ?? 'system');
            
            $busAssignmentsToKeep = [];
            
                    
            foreach ($request->bus_assignments as $busData) {
                $busId = $busData['id'] ?? null;
                if ($busId && is_numeric($busId)) {
                    $busAssignmentsToKeep[] = (int)$busId;
                }
            }
            
            foreach ($newlyCreatedBusIds as $newId) {
                if (!in_array((int)$newId, $busAssignmentsToKeep)) {
                    $busAssignmentsToKeep[] = (int)$newId;
                }
            }
            
            $busAssignmentsToKeep = array_unique($busAssignmentsToKeep);
    
            $hasVehicleSpec = false;
            foreach ($busAssignmentDataArray as $busData) {
                if (isset($busData['vehicle_type_spec_check']) && $busData['vehicle_type_spec_check'] === true) {
                    $hasVehicleSpec = true;
                    break;
                }
            }
            
            $finalVehicleSpec = $hasVehicleSpec ? 1 : 0;
            
            foreach ($busAssignmentDataArray as &$busData) {
                $busData['vehicle_type_spec_check'] = $finalVehicleSpec;
            }
            
            foreach ($busAssignmentDataArray as $busData) {
                $busId = $busData['id'];
                if ($busId && is_numeric($busId)) {
                    $busAssignmentsToKeep[] = (int)$busId;
                }
                
                if ($busId && is_numeric($busId)) {
                    $existingBus = BusAssignment::find($busId);
                    if ($existingBus) {
                        $oldLock = (bool)$existingBus->lock_arrangement;
                        $oldFinalized = (bool)$existingBus->status_finalized;
                        $oldSent = (bool)$existingBus->status_sent;
                        $oldVehicleSpec = (bool)$existingBus->vehicle_type_spec_check;
                        
                        $newLock = isset($busData['lock_arrangement']) ? (bool)$busData['lock_arrangement'] : false;
                        $newFinalized = isset($busData['status_finalized']) ? (bool)$busData['status_finalized'] : false;
                        $newSent = isset($busData['status_sent']) ? (bool)$busData['status_sent'] : false;

                        $newVehicleSpec = isset($busData['vehicle_type_spec_check']) ? (bool)$busData['vehicle_type_spec_check'] : false;
                        
                        $busData['created_by'] = $existingBus->created_by;
                        $busData['created_at'] = $existingBus->created_at;
                        
                        $busData['vehicle_id'] = isset($busData['vehicle_id']) 
                            ? ($busData['vehicle_id'] > 0 ? $busData['vehicle_id'] : null) 
                            : null;
                        $busData['driver_id'] = isset($busData['driver_id']) 
                            ? ($busData['driver_id'] > 0 ? $busData['driver_id'] : null) 
                            : null;
                        $busData['guide_id'] = isset($busData['guide_id']) 
                            ? ($busData['guide_id'] > 0 ? $busData['guide_id'] : null) 
                            : null;
                        
                        $existingBus->update($busData);
                        
                        if ($oldLock != $newLock) {
                            $actionDesc = $newLock ? 'Lock' : 'Un-Lock';
                            $this->logBusAssignmentChange(
                                $existingBus->id,
                                $groupInfo->id,
                                'lock_arrangement',
                                'lock',
                                $oldLock ? '1' : '0',
                                $newLock ? '1' : '0',
                                $actionDesc,
                                $userId,
                                $username
                            );
                        }
                        
                        if ($oldFinalized != $newFinalized) {
                            $actionDesc = $newFinalized ? '最終確認' : 'Clear-最終確認';
                            $this->logBusAssignmentChange(
                                $existingBus->id,
                                $groupInfo->id,
                                'status_finalized',
                                'finalized',
                                $oldFinalized ? '1' : '0',
                                $newFinalized ? '1' : '0',
                                $actionDesc,
                                $userId,
                                $username
                            );
                        }
                        
                        if ($oldSent != $newSent) {
                            $actionDesc = $newSent ? '送信済' : 'Clear-送信済';
                            $this->logBusAssignmentChange(
                                $existingBus->id,
                                $groupInfo->id,
                                'status_sent',
                                'sent',
                                $oldSent ? '1' : '0',
                                $newSent ? '1' : '0',
                                $actionDesc,
                                $userId,
                                $username
                            );
                        }
                        
                        if ($oldVehicleSpec != $newVehicleSpec) {
                            $actionDesc = $newVehicleSpec ? '車種指定' : 'Clear-車種指定';
                            $this->logBusAssignmentChange(
                                $existingBus->id,
                                $groupInfo->id,
                                'vehicle_type_spec_check',
                                'vehicle_spec',
                                $oldVehicleSpec ? '1' : '0',
                                $newVehicleSpec ? '1' : '0',
                                $actionDesc,
                                $userId,
                                $username
                            );
                        }
                    }
                } elseif (empty($busId)) {
                    $busData['created_by'] = $userId;
                    $busData['created_at'] = now();
                    $newBus = BusAssignment::create($busData);
                }
            }
            
            $busAssignmentsToDelete = array_diff($existingBusIds, $busAssignmentsToKeep);
    
            foreach ($busAssignmentsToDelete as $busIdToDelete) {
                BusAssignment::where('id', $busIdToDelete)->delete();
            }
    
            $existingItineraryIds = DailyItinerary::where('group_info_id', $groupInfo->id)
                                            ->pluck('id')
                                            ->toArray();
    
            $submittedItineraryIds = [];
    
            $minStartDate = null;
            $maxEndDate = null;
            $minStartTime = null;
            $maxEndTime = null;
    
            $vehicleSchedules = [];
            $driverSchedules = [];
    
            if ($request->has('daily_itineraries') && is_array($request->daily_itineraries)) {
                $itinerariesById = [];
                foreach ($request->daily_itineraries as $index => $itineraryData) {
                    $itineraryId = $itineraryData['id'] ?? null;
                    if ($itineraryId) {
                        $itinerariesById[$itineraryId] = $itineraryData;
                        $submittedItineraryIds[] = $itineraryId;
                    }
                }
                
                $allExistingItineraries = DailyItinerary::where('group_info_id', $groupInfo->id)->get();
                
                foreach ($allExistingItineraries as $existingItinerary) {
                    $itineraryId = $existingItinerary->id;
                    
                    if (isset($itinerariesById[$itineraryId])) {
                        $itineraryData = $itinerariesById[$itineraryId];
    
                        $date = $itineraryData['date'];
                        if (strpos($date, ' ') !== false) {
                            $date = explode(' ', $date)[0];
                        }
                        if (strpos($date, 'T') !== false) {
                            $date = explode('T', $date)[0];
                        }
                        
                        $timeStart = $itineraryData['time_start'] . ':00';
                        $timeEnd = $itineraryData['time_end'] . ':00';
                        
                        if (strpos($timeStart, ' ') !== false) {
                            $timeStart = explode(' ', $timeStart)[0];
                        }
                        if (strpos($timeEnd, ' ') !== false) {
                            $timeEnd = explode(' ', $timeEnd)[0];
                        }
                        
                        $timeStart = preg_replace('/[^0-9:]/', '', $timeStart);
                        $timeEnd = preg_replace('/[^0-9:]/', '', $timeEnd);
    
                        $vehicleId = isset($itineraryData['vehicle_id']) ? (int)$itineraryData['vehicle_id'] : 0;
                        $driverId = isset($itineraryData['driver_id']) ? (int)$itineraryData['driver_id'] : 0;
    
                        $guideIdForItinerary = isset($itineraryData['guide_id']) ? (int)$itineraryData['guide_id'] : (int)$existingItinerary->guide_id;
                        
                        $vehicleName = '';
                        if ($vehicleId > 0) {
                            $vehicle = Vehicle::find($vehicleId);
                            $vehicleName = $vehicle ? $vehicle->registration_number : '';
                        }
                        
                        $driverName = '';
                        if ($driverId > 0) {
                            $driver = Driver::find($driverId);
                            $driverName = $driver ? $driver->name : '';
                        }
                        
                        $guideNameForItinerary = '';
                        if ($guideIdForItinerary) {
                            $guide = Guide::find($guideIdForItinerary);
                            $guideNameForItinerary = $guide ? $guide->name : '';
                        }
                        
                        $finalVehicleId = ($vehicleId > 0) ? $vehicleId : $existingItinerary->vehicle_id;
                        $finalDriverId = ($driverId > 0) ? $driverId : $existingItinerary->driver_id;
                        $finalGuideId = $guideIdForItinerary;
                        
                        $currentDate = $date;
                        $currentStartTime = $timeStart;
                        $currentEndTime = $timeEnd;
                        
                        if (!$minStartDate || $currentDate < $minStartDate) {
                            $minStartDate = $currentDate;
                            $minStartTime = $currentStartTime;
                        }
                        
                        if (!$maxEndDate || $currentDate > $maxEndDate) {
                            $maxEndDate = $currentDate;
                            $maxEndTime = $currentEndTime;
                        }
                        
                        $busAssignmentId = isset($itineraryData['bus_assignment_id']) && is_numeric($itineraryData['bus_assignment_id']) 
                            ? (int)$itineraryData['bus_assignment_id'] 
                            : (int)$existingItinerary->bus_assignment_id;
                        
                        $itineraryVehicleId = null;
                        $itineraryDriverId = null;
                        $itineraryGuideId = null;
                        
                        if ($busAssignmentId && isset($submittedBusAssignmentData[$busAssignmentId])) {
                            $busForThisItinerary = $submittedBusAssignmentData[$busAssignmentId];
                            $itineraryVehicleId = $busForThisItinerary['vehicle_id'] ?? null;
                            $itineraryDriverId = $busForThisItinerary['driver_id'] ?? null;
                            $itineraryGuideId = $busForThisItinerary['guide_id'] ?? null;
                        }
                        
                        if (empty($itineraryVehicleId)) {
                            $itineraryVehicleId = $itineraryData['vehicle_id'] ?? null;
                        }
                        if (empty($itineraryDriverId)) {
                            $itineraryDriverId = $itineraryData['driver_id'] ?? null;
                        }
                        
                        $itineraryFields = [
                            'group_info_id' => (int)$groupInfo->id,
                            'date' => $date,
                            'time_start' => $timeStart,
                            'time_end' => $timeEnd,
                            'itinerary' => $itineraryData['itinerary'] ?? $existingItinerary->itinerary,
                            'start_location' => $itineraryData['start_location'] ?? $existingItinerary->start_location,
                            'end_location' => $itineraryData['end_location'] ?? $existingItinerary->end_location,
                            'accommodation' => isset($itineraryData['accommodation']) ? (bool)$itineraryData['accommodation'] : $existingItinerary->accommodation,
                            'vehicle_id' => !empty($itineraryVehicleId) && $itineraryVehicleId > 0 ? $itineraryVehicleId : null,
                            'vehicle' => $vehicleName ?: $existingItinerary->vehicle,
                            'driver_id' => !empty($itineraryDriverId) && $itineraryDriverId > 0 ? $itineraryDriverId : null,
                            'driver' => $driverName ?: $existingItinerary->driver,
                            'guide_id' => !empty($itineraryGuideId) && $itineraryGuideId > 0 ? $itineraryGuideId : null,
                            'guide' => $guideNameForItinerary ?: $existingItinerary->guide,
                            'remarks' => $itineraryData['remarks'] ?? $existingItinerary->remarks,
                            'bus_assignment_id' => $busAssignmentId,
                            'updated_at' => now(),
                            'updated_by' => (int)$userId,
                        ];
    
                        $existingItinerary->update($itineraryFields);
                        
                        if (!$groupInfo->ignore_operation) {
                            if ($finalVehicleId > 0) {
                                $vehicleSchedules[] = [
                                    'id' => $finalVehicleId,
                                    'date' => $date,
                                    'start' => $timeStart,
                                    'end' => $timeEnd,
                                    'itinerary_id' => $itineraryId,
                                    'bus_id' => $busAssignmentId
                                ];
                            }
                            if ($finalDriverId > 0) {
                                $driverSchedules[] = [
                                    'id' => $finalDriverId,
                                    'date' => $date,
                                    'start' => $timeStart,
                                    'end' => $timeEnd,
                                    'itinerary_id' => $itineraryId,
                                    'bus_id' => $busAssignmentId
                                ];
                            }
                        }
                    } else {
                        $currentDate = $existingItinerary->date;
                        
                        if (!$minStartDate || $currentDate < $minStartDate) {
                            $minStartDate = $currentDate;
                            $minStartTime = $existingItinerary->time_start;
                        }
                        
                        if (!$maxEndDate || $currentDate > $maxEndDate) {
                            $maxEndDate = $currentDate;
                            $maxEndTime = $existingItinerary->time_end;
                        }
                    }
                }
                
                $newItineraryRows = array_filter($request->daily_itineraries, function($itineraryData) {
                    return empty($itineraryData['id']);
                });
                
                foreach ($newItineraryRows as $index => $itineraryData) {
                    $vehicleGroup = $itineraryData['vehicle_group'] ?? null;
                    $busAssignmentId = 0;
                    
                    if ($vehicleGroup) {
                        $busForThisGroup = collect($request->bus_assignments)->firstWhere('vehicle_index', $vehicleGroup);
                        if ($busForThisGroup) {
                            $busId = $busForThisGroup['id'] ?? null;
                            if ($busId && is_numeric($busId)) {
                                $busAssignmentId = (int)$busId;
                            } elseif ($busId && !is_numeric($busId) && isset($newBusMappings[$busId])) {
                                $busAssignmentId = $newBusMappings[$busId];
                            } elseif (isset($newBusMappings[$vehicleGroup])) {
                                $busAssignmentId = $newBusMappings[$vehicleGroup];
                            }
                        }
                    }
                    
                    if ($busAssignmentId == 0 && isset($itineraryData['bus_assignment_id']) && is_numeric($itineraryData['bus_assignment_id'])) {
                        $busAssignmentId = (int)$itineraryData['bus_assignment_id'];
                    }
                    
                    $date = $itineraryData['date'];
                    if (strpos($date, ' ') !== false) {
                        $date = explode(' ', $date)[0];
                    }
                    if (strpos($date, 'T') !== false) {
                        $date = explode('T', $date)[0];
                    }
                    
                    $timeStartRaw = $itineraryData['time_start'] . ':00';
                    $timeEndRaw = $itineraryData['time_end'] . ':00';
                    
                    if (strpos($timeStartRaw, ' ') !== false) {
                        $timeStartRaw = explode(' ', $timeStartRaw)[0];
                    }
                    if (strpos($timeEndRaw, ' ') !== false) {
                        $timeEndRaw = explode(' ', $timeEndRaw)[0];
                    }
                    
                    $timeStartRaw = preg_replace('/[^0-9:]/', '', $timeStartRaw);
                    $timeEndRaw = preg_replace('/[^0-9:]/', '', $timeEndRaw);
    
                    $vehicleId = isset($itineraryData['vehicle_id']) ? (int)$itineraryData['vehicle_id'] : 0;
                    $driverId = isset($itineraryData['driver_id']) ? (int)$itineraryData['driver_id'] : 0;
    
                    $guideIdForItinerary = isset($itineraryData['guide_id']) ? (int)$itineraryData['guide_id'] : 0;
                    
                    $vehicleName = '';
                    if ($vehicleId > 0) {
                        $vehicle = Vehicle::find($vehicleId);
                        $vehicleName = $vehicle ? $vehicle->registration_number : '';
                    }
                    
                    $driverName = '';
                    if ($driverId > 0) {
                        $driver = Driver::find($driverId);
                        $driverName = $driver ? $driver->name : '';
                    }
                    
                    $guideNameForItinerary = '';
                    if ($guideIdForItinerary) {
                        $guide = Guide::find($guideIdForItinerary);
                        $guideNameForItinerary = $guide ? $guide->name : '';
                    }
                    
                    $currentDate = $date;
                    $currentStartTime = $timeStartRaw;
                    $currentEndTime = $timeEndRaw;
                    
                    if (!$minStartDate || $currentDate < $minStartDate) {
                        $minStartDate = $currentDate;
                        $minStartTime = $currentStartTime;
                    }
                    
                    if (!$maxEndDate || $currentDate > $maxEndDate) {
                        $maxEndDate = $currentDate;
                        $maxEndTime = $currentEndTime;
                    }
                    
                    $itineraryFields = [
                        'group_info_id' => (int)$groupInfo->id,
                        'date' => $date,
                        'time_start' => $timeStartRaw,
                        'time_end' => $timeEndRaw,
                        'itinerary' => $itineraryData['itinerary'] ?? null,
                        'start_location' => $itineraryData['start_location'] ?? null,
                        'end_location' => $itineraryData['end_location'] ?? null,
                        'accommodation' => isset($itineraryData['accommodation']) ? (bool)$itineraryData['accommodation'] : false,
                        'vehicle_id' => $vehicleId,
                        'vehicle' => $vehicleName,
                        'driver_id' => $driverId,
                        'driver' => $driverName,
                        'guide_id' => $guideIdForItinerary,
                        'guide' => $guideNameForItinerary,
                        'remarks' => $itineraryData['remarks'] ?? null,
                        'bus_assignment_id' => $busAssignmentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => (int)$userId,
                        'updated_by' => (int)$userId,
                    ];
                    
                    $newItinerary = DailyItinerary::create($itineraryFields);
                    
                    if (!$groupInfo->ignore_operation) {
                        if ($vehicleId > 0) {
                            $vehicleSchedules[] = [
                                'id' => $vehicleId,
                                'date' => $date,
                                'start' => $timeStartRaw,
                                'end' => $timeEndRaw,
                                'itinerary_id' => $newItinerary->id,
                                'bus_id' => $busAssignmentId
                            ];
                        }
                        if ($driverId > 0) {
                            $driverSchedules[] = [
                                'id' => $driverId,
                                'date' => $date,
                                'start' => $timeStartRaw,
                                'end' => $timeEndRaw,
                                'itinerary_id' => $newItinerary->id,
                                'bus_id' => $busAssignmentId
                            ];
                        }
                    }
                }
                
                if (!$validated['ignore_operation']) {
                    foreach ($vehicleSchedules as $schedule) {
                        $this->checkResourceConflicts(
                            'vehicle',
                            $schedule['id'],
                            $schedule['date'],
                            $schedule['start'],
                            $schedule['end'],
                            $schedule['bus_id'],
                            $groupInfo->id
                        );
                    }
                    
                    foreach ($driverSchedules as $schedule) {
                        $this->checkResourceConflicts(
                            'driver',
                            $schedule['id'],
                            $schedule['date'],
                            $schedule['start'],
                            $schedule['end'],
                            $schedule['bus_id'],
                            $groupInfo->id
                        );
                    }
                    
                    foreach ($driverSchedules as $schedule) {
                        $startDateTime = Carbon::parse($schedule['date'] . ' ' . $schedule['start']);
                        $endDateTime = Carbon::parse($schedule['date'] . ' ' . $schedule['end']);
                        
                        $restConflict = $this->checkDriverRestConflict(
                            $schedule['id'],
                            $startDateTime,
                            $endDateTime,
                            $groupInfo->id
                        );
                        
                        if ($restConflict) {
                            $driver = Driver::find($schedule['id']);
                            $driverName = $driver ? $driver->name : '#' . $schedule['id'];
                            throw new \Exception(
                                "運転手「{$driverName}」は日付「{$schedule['date']}」 " . 
                                substr($schedule['start'], 0, 5) . "～" . substr($schedule['end'], 0, 5) . 
                                " に休憩時間が設定されています。\n" .
                                "休憩時間: {$restConflict['start_datetime']} ～ {$restConflict['end_datetime']}\n" .
                                "内容: {$restConflict['attendance_name']}"
                            );
                        }
                    }
                    
                    $vehicleConflicts = [];
                    foreach ($vehicleSchedules as $schedule) {
                        $key = $schedule['id'] . '_' . $schedule['date'];
                        if (!isset($vehicleConflicts[$key])) {
                            $vehicleConflicts[$key] = [];
                        }
                        $vehicleConflicts[$key][] = $schedule;
                    }
                    
                    foreach ($vehicleConflicts as $key => $schedules) {
                        if (count($schedules) > 1) {
                            for ($i = 0; $i < count($schedules); $i++) {
                                for ($j = $i + 1; $j < count($schedules); $j++) {
                                    $s1 = $schedules[$i];
                                    $s2 = $schedules[$j];
                                    
                                    $start1 = Carbon::parse($s1['date'] . ' ' . $s1['start']);
                                    $end1 = Carbon::parse($s1['date'] . ' ' . $s1['end']);
                                    $start2 = Carbon::parse($s2['date'] . ' ' . $s2['start']);
                                    $end2 = Carbon::parse($s2['date'] . ' ' . $s2['end']);
                                    
                                    if ($start1->lt($end2) && $end1->gt($start2)) {
                                        $vehicle = Vehicle::find($s1['id']);
                                        $vehicleName = $vehicle ? $vehicle->registration_number : '不明';
                                        throw new \Exception("同一グループ内で車両「{$vehicleName}」が同じ日付（{$s1['date']}）に重複して割り当てられています。");
                                    }
                                }
                            }
                        }
                    }
                    
                    $driverConflicts = [];
                    foreach ($driverSchedules as $schedule) {
                        $key = $schedule['id'] . '_' . $schedule['date'];
                        if (!isset($driverConflicts[$key])) {
                            $driverConflicts[$key] = [];
                        }
                        $driverConflicts[$key][] = $schedule;
                    }
                    
                    foreach ($driverConflicts as $key => $schedules) {
                        if (count($schedules) > 1) {
                            for ($i = 0; $i < count($schedules); $i++) {
                                for ($j = $i + 1; $j < count($schedules); $j++) {
                                    $s1 = $schedules[$i];
                                    $s2 = $schedules[$j];
                                    
                                    $start1 = Carbon::parse($s1['date'] . ' ' . $s1['start']);
                                    $end1 = Carbon::parse($s1['date'] . ' ' . $s1['end']);
                                    $start2 = Carbon::parse($s2['date'] . ' ' . $s2['start']);
                                    $end2 = Carbon::parse($s2['date'] . ' ' . $s2['end']);
                                    
                                    if ($start1->lt($end2) && $end1->gt($start2)) {
                                        $driver = Driver::find($s1['id']);
                                        $driverName = $driver ? $driver->name : '不明';
                                        throw new \Exception("同一グループ内で運転手「{$driverName}」が同じ日付（{$s1['date']}）に重複して割り当てられています。");
                                    }
                                }
                            }
                        }
                    }
                }
                
                $busAssignments = BusAssignment::where('group_info_id', $groupInfo->id)->get();
    
                $submittedBusAssignmentData = [];
                if ($request->has('bus_assignments') && is_array($request->bus_assignments)) {
                    foreach ($request->bus_assignments as $busData) {
                        $busId = $busData['id'] ?? null;
                        if ($busId && is_numeric($busId)) {
                            $submittedBusAssignmentData[(int)$busId] = $busData;
                        }
                    }
                }
    
                foreach ($busAssignments as $bus) {
                    $bus->refresh();
                    
                    $itinerariesForBus = DailyItinerary::where('group_info_id', $groupInfo->id)
                                                    ->where('bus_assignment_id', $bus->id)
                                                    ->orderBy('date', 'asc')
                                                    ->get();
                    
                    if ($itinerariesForBus->isEmpty()) {
                        $bus->delete();
                        continue;
                    }
                    
                    $firstItinerary = $itinerariesForBus->first();
                    $lastItinerary = $itinerariesForBus->last();
                    
                    $submittedBusData = $submittedBusAssignmentData[$bus->id] ?? null;
                    
                    $submittedVehicleId = $submittedBusData['vehicle_id'] ?? null;
                    $submittedDriverId = $submittedBusData['driver_id'] ?? null;
                    $guideIdFromRequest = $submittedBusData['guide_id'] ?? null;
                    
                    $finalVehicleId = !empty($submittedVehicleId) && $submittedVehicleId > 0 ? $submittedVehicleId : null;
                    $finalDriverId = !empty($submittedDriverId) && $submittedDriverId > 0 ? $submittedDriverId : null;
                    $finalGuideId = !empty($guideIdFromRequest) && $guideIdFromRequest > 0 ? $guideIdFromRequest : null;
                    
                    $oldLock = (bool)$bus->lock_arrangement;
                    $oldFinalized = (bool)$bus->status_finalized;
                    $oldSent = (bool)$bus->status_sent;
                    $oldTemporaryDriver = (bool)$bus->temporary_driver;
                    $oldVehicleSpec = (bool)$bus->vehicle_type_spec_check;
                    
                    $newLock = isset($submittedBusData['lock_arrangement']) ? (bool)$submittedBusData['lock_arrangement'] : false;
                    $newFinalized = isset($submittedBusData['status_finalized']) ? (bool)$submittedBusData['status_finalized'] : false;
                    $newSent = isset($submittedBusData['status_sent']) ? (bool)$submittedBusData['status_sent'] : false;
                    $newTemporaryDriver = isset($submittedBusData['temporary_driver']) ? (bool)$submittedBusData['temporary_driver'] : false;
                    $newVehicleSpec = $finalVehicleSpec;
                    
                    $bus->update([
                        'vehicle_id' => $finalVehicleId,
                        'driver_id' => $finalDriverId,
                        'guide_id' => $finalGuideId,
                        'start_date' => $firstItinerary->date,
                        'start_time' => $firstItinerary->time_start,
                        'end_date' => $lastItinerary->date,
                        'end_time' => $lastItinerary->time_end,
                        'count_daily' => $itinerariesForBus->count(),
                        'lock_arrangement' => $newLock,
                        'status_sent' => $newSent,
                        'status_finalized' => $newFinalized,
                        'temporary_driver' => $newTemporaryDriver,
                        'vehicle_type_spec_check' => $newVehicleSpec,
                        'operation_basic_remarks' => $submittedBusData['operation_basic_remarks'] ?? null,
                        'operation_remarks' => $submittedBusData['operation_remarks'] ?? null,
                        'operation_memo' => $submittedBusData['operation_memo'] ?? null,
                        'attention' => $submittedBusData['attention'] ?? null,
                        'representative' => $submittedBusData['representative'] ?? null,
                        'representative_phone' => $submittedBusData['representative_phone'] ?? null,
                        'step_car' => $submittedBusData['step_car'] ?? null,
                        'vehicle_number' => $submittedBusData['vehicle_number'] ?? null,
                        'adult_count' => $submittedBusData['adult_count'] ?? 0,
                        'child_count' => $submittedBusData['child_count'] ?? 0,
                        'guide_count' => $submittedBusData['guide_count'] ?? 0,
                        'other_count' => $submittedBusData['other_count'] ?? 0,
                        'luggage_count' => $submittedBusData['luggage_count'] ?? 0,
                    ]);
                    
                    if ($oldLock != $newLock) {
                        $actionDesc = $newLock ? 'Lock' : 'Un-Lock';
                        $this->logBusAssignmentChange(
                            $bus->id,
                            $groupInfo->id,
                            'lock_arrangement',
                            'lock',
                            $oldLock ? '1' : '0',
                            $newLock ? '1' : '0',
                            $actionDesc,
                            $userId,
                            $username
                        );
                    }
                    
                    if ($oldFinalized != $newFinalized) {
                        $actionDesc = $newFinalized ? '最終確認' : 'Clear-最終確認';
                        $this->logBusAssignmentChange(
                            $bus->id,
                            $groupInfo->id,
                            'status_finalized',
                            'finalized',
                            $oldFinalized ? '1' : '0',
                            $newFinalized ? '1' : '0',
                            $actionDesc,
                            $userId,
                            $username
                        );
                    }
                    
                    if ($oldSent != $newSent) {
                        $actionDesc = $newSent ? '送信済' : 'Clear-送信済';
                        $this->logBusAssignmentChange(
                            $bus->id,
                            $groupInfo->id,
                            'status_sent',
                            'sent',
                            $oldSent ? '1' : '0',
                            $newSent ? '1' : '0',
                            $actionDesc,
                            $userId,
                            $username
                        );
                    }
                    
                    if ($oldVehicleSpec != $newVehicleSpec) {
                        $actionDesc = $newVehicleSpec ? '車種指定' : 'Clear-車種指定';
                        $this->logBusAssignmentChange(
                            $bus->id,
                            $groupInfo->id,
                            'vehicle_type_spec_check',
                            'vehicle_spec',
                            $oldVehicleSpec ? '1' : '0',
                            $newVehicleSpec ? '1' : '0',
                            $actionDesc,
                            $userId,
                            $username
                        );
                    }
                    
                    if ($guideIdFromRequest !== null) {
                        $guideName = '';
                        if ($finalGuideId) {
                            $guide = Guide::find($finalGuideId);
                            $guideName = $guide ? $guide->name : '';
                        }
                        
                        foreach ($itinerariesForBus as $itinerary) {
                            $itinerary->update([
                                'guide_id' => $finalGuideId,
                                'guide' => $guideName,
                            ]);
                        }
                    }
                }
            }
    
            if (!$validated['ignore_operation']) {
                $this->checkSameGroupConflicts($groupInfo->id);
            }
    
            $this->recalculateGroupTotals($groupInfo->id);
    
            $guideIdForGroup = $guideId;
            $guideNameForGroup = $guideName;
            
            if ($request->filled('guide_id')) {
                $guideIdForGroup = $request->guide_id;
                $guide = Guide::find($guideIdForGroup);
                $guideNameForGroup = $guide ? $guide->name : '';
            }
    
            $updateData = [
                'agency' => $validated['agency_name_input'] ?? $validated['agency'] ?? $groupInfo->agency,
                'agency_code' => $validated['agency_code'] ?? ($agencyInfo->agency_code ?? $groupInfo->agency_code),
                'agency_branch' => $validated['agency_branch'] ?? ($agencyInfo->branch_name ?? $groupInfo->agency_branch),
                'agency_phone' => $validated['agency_phone'] ?? ($agencyInfo->phone_number ?? $groupInfo->agency_phone),
                'agency_contact_name' => $validated['agency_contact_name'] ?? ($agencyInfo->manager_name ?? $groupInfo->agency_contact_name),
                'agency_country' => $validated['agency_country'] ?? ($agencyInfo->country ?? $groupInfo->agency_country),
                'group_name' => $validated['group_name'] ?? $groupInfo->group_name,
                'itinerary_name' => $validated['itinerary_name'] ?? $groupInfo->itinerary_name,
                'reservation_status' => $validated['reservation_status'] ?? '',
                'start_date' => $minStartDate ?? $validated['start_date'],
                'start_time' => $minStartTime,
                'end_date' => $maxEndDate ?? $validated['end_date'],
                'end_time' => $maxEndTime,
                'remarks' => $validated['remarks'] ?? null,
                'agt_tour_id' => $validated['agt_tour_id'] ?? null,
                'business_category' => $validated['business_category'] ?? null,
                'reservation_categories_id' => $validated['reservation_categories_id'] ?? null,
                'adult_count' => $validated['adult_count'] ?? 0,
                'child_count' => $validated['child_count'] ?? 0,
                'other_count' => $validated['other_count'] ?? 0,
                'luggage_count' => $validated['luggage_count'] ?? 0,
                'reservation_channel' => $validated['reservation_channel'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? ($vehicleInfo->vehicleType->type_name ?? $groupInfo->vehicle_type),
                'vehicle_model' => $validated['vehicle_model'] ?? ($vehicleInfo->vehicleModel->model_name ?? $groupInfo->vehicle_model),
                'vehicle' => $validated['vehicle_name_input'] ?? $validated['vehicle'] ?? ($vehicleInfo->registration_number ?? $groupInfo->vehicle),
                'vehicle_number' => $validated['vehicle_number'] ?? $groupInfo->vehicle_number,
                'driver' => $validated['driver_name_input'] ?? $validated['driver'] ?? ($driverInfo->name ?? $groupInfo->driver),
                'guide' => $guideNameForGroup,
                'guide_id' => $guideIdForGroup,
                'vehicle_branch' => $validated['vehicle_branch'] ?? ($vehicleInfo->branch->branch_name ?? $groupInfo->vehicle_branch),
                'guide_count' => $validated['guide_count'] ?? $groupInfo->guide_count,
                'vehicle_type_selection' => $finalVehicleSpec,
                'ignore_operation' => $validated['ignore_operation'] ? 1 : 0,
                'ignore_attendance' => $validated['ignore_attendance'] ? 1 : 0,
                'updated_at' => now(),
                'updated_by' => $userId,
            ];
            
            $oldReservationStatus = $groupInfo->reservation_status;
    
            $groupInfo->update($updateData);
            
            $newReservationStatus = $groupInfo->reservation_status;
            
            if ($oldReservationStatus != $newReservationStatus) {
                $oldStatus = $oldReservationStatus ?? '未設定';
                $newStatus = $newReservationStatus ?? '未設定';
                $actionDesc = "{$newStatus}";
                
                $relatedBusAssignments = BusAssignment::where('group_info_id', $groupInfo->id)->get();
                foreach ($relatedBusAssignments as $bus) {
                    $this->logBusAssignmentChange(
                        $bus->id,
                        $groupInfo->id,
                        'reservation_status',
                        'reservation_status',
                        $oldReservationStatus,
                        $newReservationStatus,
                        $actionDesc,
                        $userId,
                        $username
                    );
                }
            }
    
            DB::commit();
    
            if ($request->ajax() || $request->wantsJson() || $request->input('iframe') == '1') {
                return response()->json([
                    'success' => true,
                    'id' => $groupInfo->id,
                    'message' => 'グループ情報を更新しました。',
                    'redirect' => route('masters.group-infos.show', $groupInfo->id) . '#edit-section',
                    'reload' => true
                ]);
            }
    
            return redirect()->route('masters.group-infos.show', $groupInfo->id) . '#edit-section'
                ->with([
                    'success' => 'グループ情報を更新しました。データは正常に保存されました。',
                    'alert-type' => 'success'
                ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson() || $request->input('iframe') == '1') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => [
                        'system' => [$e->getMessage()]
                    ]
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '更新中にエラーが発生しました: ' . $e->getMessage(),
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function destroy($id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            $reservationStatus = $groupInfo->reservation_status ?? '';
            
            if ($reservationStatus !== 'キャンセル') {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => '削除するには、予約状況を「キャンセル」に変更してください。'
                    ], 400);
                }
                
                return redirect()->back()
                    ->with('error', '削除するには、予約状況を「キャンセル」に変更してください。');
            }
            
            DailyItinerary::where('group_info_id', $groupInfo->id)->delete();
            
            BusAssignment::where('group_info_id', $groupInfo->id)->delete();
            
            $groupInfo->delete();
            
            DB::commit();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'グループと関連データを削除しました。'
                ]);
            }
            
            return redirect()->route('masters.group-infos.index')
                ->with('success', 'グループと関連データを削除しました。');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '削除中にエラーが発生しました: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', '削除中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    public function getVehicleInfo($id)
    {
        $vehicle = Vehicle::with(['vehicleType', 'vehicleModel', 'branch'])
                         ->findOrFail($id);
        
        return response()->json([
            'id' => $vehicle->id,
            'registration_number' => $vehicle->registration_number,
            'vehicle_code' => $vehicle->vehicle_code,
            'vehicle_type' => $vehicle->vehicleType->type_name ?? null,
            'vehicle_model' => $vehicle->vehicleModel->model_name ?? null,
            'vehicle_branch' => $vehicle->branch->branch_name ?? null,
            'seating_capacity' => $vehicle->seating_capacity,
            'display_name' => $vehicle->registration_number . ' (' . ($vehicle->vehicleModel->model_name ?? '') . ')'
        ]);
    }

    public function splitItineraries(Request $request, $id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $request->validate([
            'itinerary_ids' => 'required|array',
            'itinerary_ids.*' => 'exists:daily_itinerary,id',
            'source_vehicle_id' => 'nullable|exists:vehicles,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $userId = session('user_id', auth()->id() ?? 0);
            
            $selectedItineraries = DailyItinerary::whereIn('id', $request->itinerary_ids)
                                                 ->where('group_info_id', $groupInfo->id)
                                                 ->orderBy('date', 'asc')
                                                 ->get();

            if ($selectedItineraries->isEmpty()) {
                throw new \Exception('選択された行程が見つかりません。');
            }

            $originalBusId = $selectedItineraries->first()->bus_assignment_id;
            $originalBus = $originalBusId ? BusAssignment::find($originalBusId) : null;
            
            $maxVehicleIndex = BusAssignment::where('group_info_id', $groupInfo->id)
                                           ->max('vehicle_index') ?? 0;
            $newVehicleIndex = $maxVehicleIndex + 1;

            $firstItinerary = $selectedItineraries->first();
            $lastItinerary = $selectedItineraries->last();

            $busAssignmentData = [
                'group_info_id' => $groupInfo->id,
                'vehicle_id' => $firstItinerary->vehicle_id > 0 ? $firstItinerary->vehicle_id : null,
                'driver_id' => $firstItinerary->driver_id > 0 ? $firstItinerary->driver_id : null,
                'guide_id' => $firstItinerary->guide_id,
                'start_date' => $firstItinerary->date,
                'start_time' => $firstItinerary->time_start,
                'end_date' => $lastItinerary->date,
                'end_time' => $lastItinerary->time_end,
                'lock_arrangement' => false,
                'status_sent' => false,
                'status_finalized' => false,
                'count_daily' => $selectedItineraries->count(),
                'vehicle_number' => sprintf('%02d', $newVehicleIndex),
                'step_car' => null,
                'adult_count' => $groupInfo->adult_count,
                'child_count' => $groupInfo->child_count,
                'guide_count' => $groupInfo->guide_count,
                'other_count' => $groupInfo->other_count,
                'luggage_count' => $groupInfo->luggage_count,
                'vehicle_type_spec_check' => false,
                'temporary_driver' => false,
                'accompanying' => null,
                'representative' => null,
                'representative_phone' => null,
                'attention' => null,
                'operation_remarks' => null,
                'operation_memo' => null,
                'operation_basic_remarks' => null,
                'doc_remarks' => null,
                'history_remarks' => null,
                'vehicle_index' => $newVehicleIndex,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $newBus = BusAssignment::create($busAssignmentData);
            
            foreach ($selectedItineraries as $itinerary) {
                $itinerary->update([
                    'bus_assignment_id' => $newBus->id,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
            }
            
            if ($originalBus) {
                $remainingItineraries = DailyItinerary::where('bus_assignment_id', $originalBus->id)
                                                      ->where('group_info_id', $groupInfo->id)
                                                      ->orderBy('date', 'asc')
                                                      ->get();
                
                if ($remainingItineraries->isEmpty()) {
                    $originalBus->delete();
                } else {
                    $firstRemaining = $remainingItineraries->first();
                    $lastRemaining = $remainingItineraries->last();
                    
                    $originalBus->update([
                        'start_date' => $firstRemaining->date,
                        'start_time' => $firstRemaining->time_start,
                        'end_date' => $lastRemaining->date,
                        'end_time' => $lastRemaining->time_end,
                        'count_daily' => $remainingItineraries->count(),
                        'updated_by' => $userId,
                        'updated_at' => now(),
                    ]);
                    
                    foreach ($remainingItineraries as $itinerary) {
                        $itinerary->update([
                            'vehicle_id' => $originalBus->vehicle_id ?? 0,
                            'driver_id' => $originalBus->driver_id ?? 0,
                            'guide_id' => $originalBus->guide_id ?? 0,
                            'updated_by' => $userId,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            if (!$groupInfo->ignore_operation) {
                $this->checkSameGroupConflicts($groupInfo->id);
            }
            
            $this->recalculateGroupTotals($groupInfo->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $selectedItineraries->count() . '件の行程を分割しました。',
                'new_vehicle_index' => $newVehicleIndex,
                'new_bus_id' => $newBus->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '分割処理に失敗しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function mergeItineraries(Request $request, $id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $request->validate([
            'operation_id' => 'required|integer',
        ]);
        
        try {
            DB::beginTransaction();
            
            $userId = session('user_id', auth()->id() ?? 0);
            
            $sourceGroup = GroupInfo::find($request->operation_id);

            if (!$sourceGroup) {
                throw new \Exception('指定された運行IDのグループが見つかりません。');
            }

            $sourceItineraries = DailyItinerary::where('group_info_id', $sourceGroup->id)
                                              ->orderBy('date', 'asc')
                                              ->orderBy('time_start', 'asc')
                                              ->get();

            if ($sourceItineraries->isEmpty()) {
                throw new \Exception('統合元のグループに行程がありません。');
            }

            foreach ($sourceItineraries as $itinerary) {
                $newItinerary = $itinerary->replicate();
                $newItinerary->group_info_id = $groupInfo->id;
                $newItinerary->bus_assignment_id = null;
                $newItinerary->created_by = $userId;
                $newItinerary->updated_by = $userId;
                $newItinerary->created_at = now();
                $newItinerary->updated_at = now();
                $newItinerary->save();
            }

            if (!$groupInfo->ignore_operation) {
                $this->checkSameGroupConflicts($groupInfo->id);
            }
            
            $this->recalculateGroupTotals($groupInfo->id);
            $this->recalculateGroupTotals($sourceGroup->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $sourceItineraries->count() . '件の行程を統合しました。',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '統合処理に失敗しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function mergeItinerariesById(Request $request, $id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $request->validate([
            'target_bus_id' => 'required|integer',
            'source_operation_id' => 'required|integer',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'driver_id' => 'nullable|integer|exists:drivers,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $userId = session('user_id', auth()->id() ?? 0);
            
            $sourceBus = BusAssignment::where('id', $request->source_operation_id)
                                     ->where('group_info_id', $groupInfo->id)
                                     ->first();

            if (!$sourceBus) {
                throw new \Exception('指定された運行ID (#' . $request->source_operation_id . ') が見つかりません。');
            }

            $sourceBusId = $sourceBus->id;

            if ($sourceBusId === $request->target_bus_id) {
                throw new \Exception('同じ運行ID (#' . $request->source_operation_id . ') には統合できません。');
            }

            $targetBus = BusAssignment::where('id', $request->target_bus_id)
                                     ->where('group_info_id', $groupInfo->id)
                                     ->first();

            if (!$targetBus) {
                throw new \Exception('対象の運行が見つかりません。');
            }

            $sourceItineraries = DailyItinerary::where('bus_assignment_id', $sourceBusId)
                                              ->where('group_info_id', $groupInfo->id)
                                              ->orderBy('date', 'asc')
                                              ->orderBy('time_start', 'asc')
                                              ->get();
            
            if ($sourceItineraries->isEmpty()) {
                throw new \Exception('統合元の運行 (#' . $request->source_operation_id . ') に行程がありません。');
            }
            
            foreach ($sourceItineraries as $itinerary) {
                $itinerary->update([
                    'vehicle_id' => $request->vehicle_id ?? $targetBus->vehicle_id ?? 0,
                    'driver_id' => $request->driver_id ?? $targetBus->driver_id ?? 0,
                    'bus_assignment_id' => $request->target_bus_id,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
            }

            $targetItineraries = DailyItinerary::where('bus_assignment_id', $request->target_bus_id)
                                              ->where('group_info_id', $groupInfo->id)
                                              ->orderBy('date', 'asc')
                                              ->get();
            
            if ($targetItineraries->isNotEmpty()) {
                $firstItinerary = $targetItineraries->first();
                $lastItinerary = $targetItineraries->last();
                
                $targetBus->update([
                    'start_date' => $firstItinerary->date,
                    'start_time' => $firstItinerary->time_start,
                    'end_date' => $lastItinerary->date,
                    'end_time' => $lastItinerary->time_end,
                    'count_daily' => $targetItineraries->count(),
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
            }
            
            $sourceBus->delete();

            if (!$groupInfo->ignore_operation) {
                $this->checkSameGroupConflicts($groupInfo->id);
            }
            
            $this->recalculateGroupTotals($groupInfo->id);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $sourceItineraries->count() . '件の行程を運行ID #' . $request->source_operation_id . ' から統合しました。',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '統合処理に失敗しました: ' . $e->getMessage()
            ], 500);
        }
    }

public function updateBusAssignment(Request $request, $id)
{
    try {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $request->validate([
            'bus_id' => 'required',
        ]);
        
        DB::beginTransaction();
        
        $userId = session('user_id', auth()->id() ?? 0);
        $username = session('username', auth()->user()->name ?? 'system');
        
        $isNewBus = !is_numeric($request->bus_id) || 
                    (is_string($request->bus_id) && (strpos($request->bus_id, 'copy_') === 0 || strpos($request->bus_id, 'split_') === 0));
        
        $busAssignment = null;
        
        if (!$isNewBus) {
            $busAssignment = BusAssignment::where('id', $request->bus_id)
                                            ->where('group_info_id', $groupInfo->id)
                                            ->first();
        }
        
        $isNewlyCreated = false;
        if (!$busAssignment) {
            $maxIndex = BusAssignment::where('group_info_id', $groupInfo->id)->max('vehicle_index') ?? 0;
            $newVehicleIndex = $maxIndex + 1;
            
            $busAssignment = new BusAssignment();
            $busAssignment->group_info_id = $groupInfo->id;
            $busAssignment->vehicle_index = $newVehicleIndex;
            $busAssignment->vehicle_number = $request->vehicle_number ?? sprintf('%02d', $newVehicleIndex);
            $busAssignment->created_by = $userId;
            $busAssignment->created_at = now();
            $busAssignment->save();
            
            $busAssignment->refresh();
            
            $isNewlyCreated = true;
        }
        
        if ($request->has('deleted_itineraries') && is_array($request->deleted_itineraries)) {
            $deletedIds = $request->deleted_itineraries;
            DailyItinerary::whereIn('id', $deletedIds)
                            ->where('group_info_id', $groupInfo->id)
                            ->where('bus_assignment_id', $busAssignment->id)
                            ->delete();
        }
        
        $itinerariesData = $request->input('itineraries', []);
        if (!empty($itinerariesData)) {
            foreach ($itinerariesData as $itineraryData) {
                $date = $itineraryData['date'] ?? null;
                if ($date) {
                    if (strpos($date, ' ') !== false) {
                        $date = explode(' ', $date)[0];
                    }
                    if (strpos($date, 'T') !== false) {
                        $date = explode('T', $date)[0];
                    }
                    $date = preg_replace('/[^0-9-]/', '', $date);
                }
                
                $timeStart = isset($itineraryData['time_start']) 
                    ? (strpos($itineraryData['time_start'], ':') === false 
                        ? $itineraryData['time_start'] . ':00' 
                        : (strlen($itineraryData['time_start']) == 5 
                            ? $itineraryData['time_start'] . ':00' 
                            : $itineraryData['time_start']))
                    : null;
                    
                $timeEnd = isset($itineraryData['time_end']) 
                    ? (strpos($itineraryData['time_end'], ':') === false 
                        ? $itineraryData['time_end'] . ':00' 
                        : (strlen($itineraryData['time_end']) == 5 
                            ? $itineraryData['time_end'] . ':00' 
                            : $itineraryData['time_end']))
                    : null;
                
                if ($timeStart && strpos($timeStart, ' ') !== false) {
                    $timeStart = explode(' ', $timeStart)[0];
                }
                if ($timeEnd && strpos($timeEnd, ' ') !== false) {
                    $timeEnd = explode(' ', $timeEnd)[0];
                }
                
                if (!empty($itineraryData['id'])) {
                    $itinerary = DailyItinerary::find($itineraryData['id']);
                    if ($itinerary && $itinerary->group_info_id == $groupInfo->id) {
                        $updateData = [
                            'date' => $date,
                            'time_start' => $timeStart,
                            'time_end' => $timeEnd,
                            'start_location' => $itineraryData['start_location'] ?? null,
                            'end_location' => $itineraryData['end_location'] ?? null,
                            'itinerary' => $itineraryData['itinerary'] ?? null,
                            'bus_assignment_id' => $busAssignment->id,
                            'updated_by' => $userId,
                            'updated_at' => now(),
                        ];
                        $itinerary->update($updateData);
                    }
                } else {
                    $vehicleName = '';
                    if (!empty($request->vehicle_id)) {
                        $vehicle = Vehicle::find($request->vehicle_id);
                        $vehicleName = $vehicle ? $vehicle->registration_number : '';
                    }
                    
                    $driverName = '';
                    if (!empty($request->driver_id)) {
                        $driver = Driver::find($request->driver_id);
                        $driverName = $driver ? $driver->name : '';
                    }
                    
                    $guideName = '';
                    if (!empty($request->guide_id)) {
                        $guide = Guide::find($request->guide_id);
                        $guideName = $guide ? $guide->name : '';
                    }
                    
                    DailyItinerary::create([
                        'group_info_id' => $groupInfo->id,
                        'bus_assignment_id' => $busAssignment->id,
                        'date' => $date,
                        'time_start' => $timeStart,
                        'time_end' => $timeEnd,
                        'start_location' => $itineraryData['start_location'] ?? null,
                        'end_location' => $itineraryData['end_location'] ?? null,
                        'itinerary' => $itineraryData['itinerary'] ?? null,
                        'vehicle_id' => $request->vehicle_id ?? 0,
                        'vehicle' => $vehicleName,
                        'driver_id' => $request->driver_id ?? 0,
                        'driver' => $driverName,
                        'guide_id' => $request->guide_id ?? 0,
                        'guide' => $guideName,
                        'accommodation' => false,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        $itineraries = DailyItinerary::where('bus_assignment_id', $busAssignment->id)
                                        ->where('group_info_id', $groupInfo->id)
                                        ->orderBy('date', 'asc')
                                        ->get();
        
        $startDate = null;
        $startTime = null;
        $endDate = null;
        $endTime = null;
        
        if ($itineraries->isNotEmpty()) {
            $firstItinerary = $itineraries->first();
            $lastItinerary = $itineraries->last();
            
            $startDate = $firstItinerary->date;
            $startTime = $firstItinerary->time_start;
            $endDate = $lastItinerary->date;
            $endTime = $lastItinerary->time_end;
        }
        
        $updateData = [
            'vehicle_id' => !empty($request->vehicle_id) && $request->vehicle_id > 0 ? $request->vehicle_id : null,
            'driver_id' => !empty($request->driver_id) && $request->driver_id > 0 ? $request->driver_id : null,
            'guide_id' => $request->guide_id ?? null,
            'vehicle_number' => $request->vehicle_number,
            'step_car' => $request->step_car,
            'adult_count' => $request->adult_count ?? 0,
            'child_count' => $request->child_count ?? 0,
            'guide_count' => $request->guide_count ?? 0,
            'other_count' => $request->other_count ?? 0,
            'luggage_count' => $request->luggage_count ?? 0,
            'vehicle_type_spec_check' => $request->boolean('vehicle_type_spec_check'),
            'temporary_driver' => $request->boolean('temporary_driver'),
            'accompanying' => $request->accompanying,
            'representative' => $request->representative,
            'representative_phone' => $request->representative_phone,
            'attention' => $request->attention,
            'operation_remarks' => $request->operation_remarks,
            'operation_memo' => $request->operation_memo,
            'operation_basic_remarks' => $request->operation_basic_remarks,
            'doc_remarks' => $request->doc_remarks,
            'history_remarks' => $request->history_remarks,
            'lock_arrangement' => $request->boolean('lock_arrangement'),
            'status_sent' => $request->boolean('status_sent'),
            'status_finalized' => $request->boolean('status_finalized'),
            'updated_by' => $userId,
            'updated_at' => now(),
        ];
        
        if ($itineraries->isNotEmpty()) {
            $updateData['start_date'] = $startDate;
            $updateData['start_time'] = $startTime;
            $updateData['end_date'] = $endDate;
            $updateData['end_time'] = $endTime;
            $updateData['count_daily'] = $itineraries->count();
        }
        
        $busAssignment->update($updateData);
        
        if ($isNewlyCreated) {
            $this->logBusAssignmentChange(
                $busAssignment->id,
                $groupInfo->id,
                'created',
                'create',
                null,
                null,
                '運行を作成しました',
                $userId,
                $username
            );
        }
        
        $this->recalculateGroupTotals($groupInfo->id);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => '運行詳細を更新しました。',
            'bus_id' => $busAssignment->id,
            'new_bus_id' => $busAssignment->id
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'バリデーションエラー',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    
    public function deleteItinerary(Request $request, $id)
    {
        try {
            $request->validate([
                'itinerary_id' => 'required|integer|exists:daily_itinerary,id',
                'bus_id' => 'nullable|integer',
            ]);
            
            DB::beginTransaction();
            
            $itinerary = DailyItinerary::findOrFail($request->itinerary_id);
            
            if ($itinerary->group_info_id != $id) {
                throw new \Exception('不正な操作です。');
            }
            
            $busId = $itinerary->bus_assignment_id;
            
            $itinerary->delete();
            
            if ($busId) {
                $remainingItineraries = DailyItinerary::where('bus_assignment_id', $busId)
                    ->where('group_info_id', $id)
                    ->orderBy('date', 'asc')
                    ->get();
                
                if ($remainingItineraries->isEmpty()) {
                    BusAssignment::where('id', $busId)->delete();
                } else {
                    $firstItinerary = $remainingItineraries->first();
                    $lastItinerary = $remainingItineraries->last();
                    
                    BusAssignment::where('id', $busId)->update([
                        'start_date' => $firstItinerary->date,
                        'start_time' => $firstItinerary->time_start,
                        'end_date' => $lastItinerary->date,
                        'end_time' => $lastItinerary->time_end,
                        'count_daily' => $remainingItineraries->count(),
                    ]);
                }
            }
            
            $this->recalculateGroupTotals($id);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '行程を削除しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    private function checkConflictsByItinerary($vehicleId, $driverId, $startDate, $startTime, $endDate, $endTime, $excludeGroupId = null, $currentReservationStatus = null)
    {
        if (in_array($currentReservationStatus, ['見積', 'キャンセル'])) {
            return;
        }
        
        if (empty($vehicleId) && empty($driverId)) {
            return;
        }
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $startTimeStr = $startTime ?? '08:00:00';
        $endTimeStr = $endTime ?? '18:00:00';
        
        $itineraries = [];
        $current = $start->copy();
        while ($current <= $end) {
            $itineraries[] = [
                'date' => $current->format('Y-m-d'),
                'time_start' => $startTimeStr,
                'time_end' => $endTimeStr,
            ];
            $current->addDay();
        }
        
        if (!empty($driverId)) {
            foreach ($itineraries as $itinerary) {
                $date = $itinerary['date'];
                $timeStart = $itinerary['time_start'];
                $timeEnd = $itinerary['time_end'];
                
                $startDateTime = Carbon::parse($date . ' ' . $timeStart);
                $endDateTime = Carbon::parse($date . ' ' . $timeEnd);
                
                $restConflict = $this->checkDriverRestConflict(
                    $driverId,
                    $startDateTime,
                    $endDateTime,
                    $excludeGroupId
                );
                
                if ($restConflict) {
                    $driver = Driver::find($driverId);
                    $driverName = $driver ? $driver->name : '#' . $driverId;
                    throw new \Exception(
                        "運転手「{$driverName}」は日付「{$date}」 " . 
                        substr($timeStart, 0, 5) . "～" . substr($timeEnd, 0, 5) . 
                        " に休憩時間が設定されています。\n" .
                        "休憩時間: {$restConflict['start_datetime']} ～ {$restConflict['end_datetime']}\n" .
                        "内容: {$restConflict['attendance_name']}"
                    );
                }
            }
        }
        
        $schedules = [];
        if (!empty($vehicleId)) {
            foreach ($itineraries as $itinerary) {
                $schedules[] = [
                    'type' => 'vehicle',
                    'id' => $vehicleId,
                    'date' => $itinerary['date'],
                    'start' => $itinerary['time_start'],
                    'end' => $itinerary['time_end'],
                ];
            }
        }
        
        if (!empty($driverId)) {
            foreach ($itineraries as $itinerary) {
                $schedules[] = [
                    'type' => 'driver',
                    'id' => $driverId,
                    'date' => $itinerary['date'],
                    'start' => $itinerary['time_start'],
                    'end' => $itinerary['time_end'],
                ];
            }
        }
        
        $groupedSchedules = [];
        foreach ($schedules as $schedule) {
            $key = $schedule['type'] . '_' . $schedule['id'] . '_' . $schedule['date'];
            if (!isset($groupedSchedules[$key])) {
                $groupedSchedules[$key] = [];
            }
            $groupedSchedules[$key][] = $schedule;
        }
        
        foreach ($groupedSchedules as $daySchedules) {
            $firstSchedule = $daySchedules[0];
            $resourceType = $firstSchedule['type'];
            $resourceId = $firstSchedule['id'];
            $date = $firstSchedule['date'];
            
            $query = DailyItinerary::where('date', $date);
            
            if ($resourceType === 'vehicle') {
                $query->where('vehicle_id', $resourceId);
            } else {
                $query->where('driver_id', $resourceId);
            }
            
            if ($excludeGroupId) {
                $query->where('group_info_id', '!=', $excludeGroupId);
            }
            
            $query->whereHas('groupInfo', function($q) {
                $q->whereNotIn('reservation_status', ['見積', 'キャンセル']);
            });
            
            $existingItineraries = $query->get();
            
            if ($existingItineraries->isEmpty()) {
                continue;
            }
            
            foreach ($daySchedules as $schedule) {
                $newStart = Carbon::parse($schedule['start']);
                $newEnd = Carbon::parse($schedule['end']);
                
                foreach ($existingItineraries as $existing) {
                    $otherBus = $existing->busAssignment;
                    if ($otherBus && $otherBus->ignore_operation) {
                        continue;
                    }
                    
                    $existingStart = Carbon::parse($existing->time_start);
                    $existingEnd = Carbon::parse($existing->time_end);
                    
                    if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                        $conflictGroup = GroupInfo::find($existing->group_info_id);
                        $conflictGroupName = $conflictGroup ? $conflictGroup->group_name : '不明';
                        $otherBusId = $existing->bus_assignment_id;
                        
                        if ($resourceType === 'vehicle') {
                            $vehicle = Vehicle::find($resourceId);
                            $resourceName = $vehicle ? $vehicle->registration_number : '不明';
                            throw new \Exception(
                                "車両「{$resourceName}」は日付「{$date}」 " . 
                                substr($schedule['start'], 0, 5) . "～" . substr($schedule['end'], 0, 5) . 
                                " に他のグループ「{$conflictGroupName}」(運行ID: {$otherBusId})の運行で既に使用されています。"
                            );
                        } else {
                            $driver = Driver::find($resourceId);
                            $resourceName = $driver ? $driver->name : '不明';
                            throw new \Exception(
                                "運転手「{$resourceName}」は日付「{$date}」 " . 
                                substr($schedule['start'], 0, 5) . "～" . substr($schedule['end'], 0, 5) . 
                                " に他のグループ「{$conflictGroupName}」(運行ID: {$otherBusId})の運行で既に使用されています。"
                            );
                        }
                    }
                }
            }
        }
    }
    
    private function checkDriverRestConflict($driverId, $startDateTime, $endDateTime, $excludeGroupId = null)
    {
        $startDateOnly = $startDateTime->format('Y-m-d');
        $endDateOnly = $endDateTime->format('Y-m-d');
        $startTimeOnly = $startDateTime->format('H:i:s');
        $endTimeOnly = $endDateTime->format('H:i:s');
        
        $query = \App\Models\Masters\DriverAttendance::where('driver_id', $driverId)
            ->whereDate('date', '>=', $startDateOnly)
            ->whereDate('date', '<=', $endDateOnly)
            ->where(function($q) use ($startTimeOnly, $endTimeOnly) {
                $q->where('start_time', '<', $endTimeOnly)
                  ->where('end_time', '>', $startTimeOnly);
            });
        
        $conflict = $query->first();
        
        if ($conflict) {
            $attributes = $conflict->getAttributes();
            $rawDate = $attributes['date'];
            $rawStartTime = $attributes['start_time'];
            $rawEndTime = $attributes['end_time'];
            
            if ($rawDate instanceof Carbon) {
                $rawDate = $rawDate->format('Y-m-d');
            }
            if ($rawStartTime instanceof Carbon) {
                $rawStartTime = $rawStartTime->format('H:i:s');
            }
            if ($rawEndTime instanceof Carbon) {
                $rawEndTime = $rawEndTime->format('H:i:s');
            }
            
            $conflictStart = Carbon::parse($rawDate . ' ' . $rawStartTime);
            $conflictEnd = Carbon::parse($rawDate . ' ' . $rawEndTime);
            
            return [
                'id' => $conflict->id,
                'start_datetime' => $conflictStart->format('Y-m-d H:i'),
                'end_datetime' => $conflictEnd->format('Y-m-d H:i'),
                'attendance_name' => $conflict->category->attendance_name ?? '休憩',
                'remarks' => $conflict->remarks
            ];
        }
        
        return null;
    }
    
    
    private function logBusAssignmentChange($busAssignmentId, $groupId, $fieldName, $operationType, $oldValue, $newValue, $actionDescription, $userId, $username)
    {
        try {
            BusAssignmentLog::create([
                'bus_assignment_id' => $busAssignmentId,
                'group_info_id' => $groupId,
                'field_name' => $fieldName,
                'operation_type' => $operationType,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'action_description' => $actionDescription,
                'user_id' => $userId,
                'username' => $username,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
        }
    }
}