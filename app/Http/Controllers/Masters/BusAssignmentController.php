<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Driver;
use App\Models\Masters\Guide;
use App\Models\Masters\Agency;
use App\Models\Masters\ReservationCategory;
use App\Models\Masters\Branch;
use App\Models\Masters\VehicleType;
use App\Models\Masters\VehicleGrade;
use App\Models\Masters\GroupInfoDateRemark;
use App\Models\Masters\BusAssignmentLog;
use App\Models\Masters\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Mpdf\Mpdf;

class BusAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $sessionKey = 'bus_assignments_search';
                
        $searchFields = ['start_date', 'period', 'display_days'];
                
        $isNewSearch = false;
        foreach ($searchFields as $field) {
            if ($request->filled($field)) {
                $isNewSearch = true;
                break;
            }
        }
                
        if ($request->has('reset_search')) {
            session()->forget($sessionKey);
            $isNewSearch = false;
        }
                
        if ($isNewSearch) {
            $searchParams = $request->only($searchFields);
            session([$sessionKey => $searchParams]);
        } else {
            $searchParams = session($sessionKey, []);
            $request->merge($searchParams);
        }
        
        $groupName = $request->input('group_name');
        $startDate = $request->input('start_date');
        $period = $request->input('period', 1);
        $reservationId = $request->input('reservation_id');
        $operationId = $request->input('operation_id');
        $branchId = $request->input('branch_id');
        $vehicleTypeId = $request->input('vehicle_type_id');
        $vehicleName = $request->input('vehicle_name');
        $vehicleId = $request->input('vehicle_id');
        $driverId = $request->input('driver_id');
        $agencyId = $request->input('agency_id');
        $reservationCategoriesId = $request->input('reservation_categories_id');
        $showCancelEstimate = $request->input('show_cancel_estimate');
        
        $hasExactSearch = !empty($reservationId) || !empty($operationId) || !empty($groupName);
        
        if (!$startDate && !$hasExactSearch) {
            $startDate = now()->format('Y-m-d');
        }
        
        if ($startDate) {
            $start = Carbon::parse($startDate);
            
            if ($period == 1) {
                $end = $start->copy()->addDays(6);
                $displayDays = 7;
            } elseif ($period == 2) {
                $end = $start->copy()->addDays(13);
                $displayDays = 14;
            } elseif ($period == 3) {
                $end = $start->copy()->addDays(20);
                $displayDays = 21;
            } elseif ($period == 4) {
                $end = $start->copy()->addMonth()->subDay();
                $displayDays = $start->diffInDays($end) + 1;
            } else {
                $end = $start->copy()->addDays(6);
                $displayDays = 7;
            }
            
            $endDate = $end->format('Y-m-d');
        }
        
        $query = BusAssignment::with([
            'groupInfo',
            'vehicle.vehicleModel',
            'driver',
            'dailyItineraries' => function($query) {
                $query->orderBy('date', 'asc')->orderBy('time_start', 'asc');
            }
        ]);
    
        if ($operationId) {
            $query->where('id', $operationId);
        }
    
        if ($reservationId) {
            $query->where('group_info_id', $reservationId);
        }
    
        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }
    
        if ($driverId) {
            $query->where('driver_id', $driverId);
        }
    
        if ($agencyId) {
            $agency = Agency::find($agencyId);
            if ($agency) {
                $query->whereHas('groupInfo', function($q) use ($agency) {
                    $q->where('agency', $agency->agency_name);
                });
            }
        }
    
        if ($reservationCategoriesId) {
            $query->whereHas('groupInfo', function($q) use ($reservationCategoriesId) {
                $q->where('reservation_categories_id', $reservationCategoriesId);
            });
        }
        
        $reservationStatuses = $request->input('reservation_statuses', []);
        
        if (!empty($reservationStatuses)) {
            $query->whereHas('groupInfo', function($q) use ($reservationStatuses) {
                $q->whereIn('reservation_status', $reservationStatuses);
            });
        } elseif (!$showCancelEstimate) {
            $query->whereHas('groupInfo', function($q) {
                $q->whereNotIn('reservation_status', ['キャンセル', '見積']);
            });
        }
    
        if (!$hasExactSearch && $startDate && $endDate) {
            $query->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($subQ) use ($startDate, $endDate) {
                      $subQ->where('start_date', '<=', $startDate)
                           ->where('end_date', '>=', $endDate);
                  });
            });
        }
    
        if ($vehicleName) {
            $query->whereHas('vehicle', function($q) use ($vehicleName) {
                $q->where('registration_number', 'like', '%' . $vehicleName . '%');
            });
        }
    
        if ($vehicleTypeId) {
            $query->whereHas('vehicle', function($q) use ($vehicleTypeId) {
                $q->where('vehicle_type_id', $vehicleTypeId);
            });
        }
    
        if ($branchId) {
            $query->whereHas('vehicle', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
    
        if ($groupName) {
            $query->whereHas('groupInfo', function($q) use ($groupName) {
                $q->where('group_name', 'like', '%' . $groupName . '%');
            });
        }
    
        $query->orWhereDoesntHave('groupInfo');
        
        $perPage = $request->input('per_page', 20);
        if ($request->input('sort_by_driver')) {
                $assignments = $query->orderBy('driver_id', 'desc')
                    ->orderBy('start_date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->paginate($perPage)
                    ->withQueryString();
        } else {
                $assignments = $query->orderBy('vehicle_index', 'asc')
                    ->orderBy('start_date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->paginate($perPage)
                    ->withQueryString();
        }
            
                    
        $busIds = $assignments->getCollection()->pluck('id')->toArray();
        
        $compensationSums = $this->getCompensationSums($busIds);
        
        $expenseSums = $this->getExpenseSums($busIds);
        
        $operationStatuses = [];
        if (!empty($busIds)) {
            $statusResults = DB::table('daily_itinerary')
                ->whereIn('bus_assignment_id', $busIds)
                ->whereNotNull('operation_status')
                ->where('operation_status', '!=', '')
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->select('bus_assignment_id', 'operation_status')
                ->get()
                ->unique('bus_assignment_id');
            
            $operationStatuses = $statusResults->pluck('operation_status', 'bus_assignment_id')->toArray();
        }
        
        $assignments->getCollection()->transform(function($assignment) use ($compensationSums, $expenseSums, $operationStatuses) {
            $assignment->compensation_total = $compensationSums[$assignment->id] ?? 0;
            $assignment->expense_total = $expenseSums[$assignment->id] ?? 0;
            $assignment->latest_operation_status = $operationStatuses[$assignment->id] ?? null;
            return $assignment;
        });
    
        $categoryIds = $assignments->getCollection()->filter(function($assignment) {
            return $assignment->groupInfo && $assignment->groupInfo->reservation_categories_id;
        })->map(function($assignment) {
            return $assignment->groupInfo->reservation_categories_id;
        })->unique()->values()->toArray();
    
        if (!empty($categoryIds)) {
            $categories = ReservationCategory::whereIn('id', $categoryIds)->get()->keyBy('id');
            $assignments->getCollection()->transform(function($assignment) use ($categories) {
                if ($assignment->groupInfo && $assignment->groupInfo->reservation_categories_id) {
                    $category = $categories[$assignment->groupInfo->reservation_categories_id] ?? null;
                    $assignment->groupInfo->category_name = $category ? $category->category_name : null;
                }
                return $assignment;
            });
        }
    
        $totalAdult = $assignments->sum('adult_count');
        $totalChild = $assignments->sum('child_count');
        $totalGuide = $assignments->sum('guide_count');
        $totalAmount = $totalAdult * 15000;
    
        $branches = Branch::orderBy('display_order')->get();
        $vehicleTypes = VehicleType::with('models')->orderBy('type_name')->get();
        $vehicles = Vehicle::with('vehicleModel')->orderBy('registration_number')->get();
        $drivers = Driver::where('is_active', true)->orderBy('display_order', 'asc')->orderBy('driver_code', 'asc')->get();
        $agencies = Agency::where('is_active', true)->orderBy('display_order', 'asc')->orderBy('agency_code', 'asc')->get();
        $reservationCategories = ReservationCategory::where('is_active', true)->orderBy('display_order', 'asc')->get();
    
        return view('masters.bus-assignments.index', compact(
            'assignments',
            'groupName',
            'startDate',
            'endDate',
            'reservationId',
            'operationId',
            'branchId',
            'vehicleTypeId',
            'vehicleId',
            'vehicleName',
            'totalAdult',
            'totalChild',
            'totalGuide',
            'totalAmount',
            'branches',
            'vehicleTypes',
            'vehicles',
            'drivers',
            'agencies',
            'reservationCategories'
        ));
    }

    public function create()
    {
        $groupInfos = GroupInfo::orderBy('created_at', 'desc')->get();
        $vehicles = Vehicle::all();
        $drivers = Driver::all();
        $guides = Guide::all();

        return view('masters.bus-assignments.create', compact('groupInfos', 'vehicles', 'drivers', 'guides'));
    }

    public function store(Request $request)
    {
        $rules = [
            'group_info_id' => 'required|exists:group_info,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'guide' => 'nullable|string|max:200',
            'start_date' => 'required|date',
            'start_time' => 'nullable',
            'end_date' => 'required|date',
            'end_time' => 'nullable',
            'vehicle_number' => 'nullable|string|max:50',
            'step_car' => 'nullable|string|max:50',
            'adult_count' => 'nullable|integer',
            'child_count' => 'nullable|integer',
            'guide_count' => 'nullable|integer',
            'luggage' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'options.*' => 'exists:option,id',
            'representative' => 'nullable|string|max:100',
            'representative_phone' => 'nullable|string|max:20',
            'operation_remarks' => 'nullable|string',
            'ignore_operation' => 'nullable|boolean',
            'ignore_driver' => 'nullable|boolean',
            'reservation_status' => 'nullable|string',
        ];
        
        $messages = [
            'group_info_id.required' => 'グループ情報IDは必須です。',
            'group_info_id.exists' => '指定されたグループ情報が存在しません。',
            'vehicle_id.exists' => '選択された車両は存在しません。',
            'driver_id.exists' => '選択された運転手は存在しません。',
            'guide_id.exists' => '選択されたガイドは存在しません。',
            'start_date.required' => '開始日は必須です。',
            'start_date.date' => '開始日の形式が正しくありません。',
            'end_date.required' => '終了日は必須です。',
            'end_date.date' => '終了日の形式が正しくありません。',
            'vehicle_number.max' => '号車は50文字以内で入力してください。',
            'step_car.max' => 'ステップカーは50文字以内で入力してください。',
            'adult_count.integer' => '大人人数は数値で入力してください。',
            'child_count.integer' => '小人人数は数値で入力してください。',
            'guide_count.integer' => 'ガイド人数は数値で入力してください。',
            'luggage.max' => '荷物は255文字以内で入力してください。',
            'options.*.exists' => '選択されたオプションが存在しません。',
            'representative.max' => '代表者名は100文字以内で入力してください。',
            'representative_phone.max' => '代表者電話番号は20文字以内で入力してください。',
        ];
        
        $validated = $request->validate($rules, $messages);

        $validated['ignore_operation'] = filter_var($validated['ignore_operation'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['ignore_driver'] = filter_var($validated['ignore_driver'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $itineraries = [];
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $startTime = $validated['start_time'] ?? '08:00:00';
        $endTime = $validated['end_time'] ?? '18:00:00';
        
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $itineraries[] = [
                'date' => $current->format('Y-m-d'),
                'time_start' => $startTime,
                'time_end' => $endTime,
            ];
            $current->addDay();
        }
        
        $reservationStatus = $validated['reservation_status'] ?? null;
        if (!in_array($reservationStatus, ['見積', 'キャンセル'])) {
            $stopOrderDates = GroupInfoDateRemark::getStopOrderDates($validated['start_date'], $validated['end_date']);
            
            if ($stopOrderDates->isNotEmpty()) {
                $dateList = $stopOrderDates->map(function($date) {
                    return Carbon::parse($date)->format('Y/m/d');
                })->implode('、');
                
                return back()->withInput()->withErrors([
                    'stop_order' => "以下の日付は受注停止のため、運行を割り当てできません：{$dateList}"
                ]);
            }
        }
        
        $shouldCheck = !$validated['ignore_operation'] 
            && !in_array($validated['reservation_status'] ?? null, ['見積', 'キャンセル']);
        
        if ($shouldCheck) {
            try {
                $this->checkConflicts(
                    $validated['vehicle_id'] ?? null,
                    $validated['driver_id'] ?? null,
                    $itineraries,
                    null,
                    null,
                    $validated['ignore_operation'],
                    $validated['ignore_driver'],
                    $validated['reservation_status'] ?? null
                );
            } catch (\Exception $e) {
                return back()->withInput()->withErrors([
                    'conflict' => $e->getMessage()
                ]);
            }
        }

        $maxIndex = BusAssignment::where('group_info_id', $validated['group_info_id'])
                                 ->max('vehicle_index') ?? 0;
        $validated['vehicle_index'] = $maxIndex + 1;

        $validated['key_uuid'] = (string) \Str::uuid();
        
        if ($request->has('options')) {
            $validated['options'] = implode(',', $request->options);
        } else {
            $validated['options'] = null;
        }

        BusAssignment::create($validated);

        return redirect()->route('masters.bus-assignments.index')
                         ->with('success', '運行割当を作成しました。');
    }

    public function show($id)
    {
        $busAssignment = BusAssignment::with(['groupInfo', 'vehicle', 'driver', 'dailyItineraries'])
            ->findOrFail($id);
        
        return view('masters.bus-assignments.show', compact('busAssignment'));
    }

    public function edit($id)
    {
        $busAssignment = BusAssignment::with([
            'groupInfo',
            'vehicle.vehicleModel',
            'vehicle.vehicleType',
            'vehicle.branch',
            'driver',
            'dailyItineraries'
        ])->findOrFail($id);
        
        $groupInfo = $busAssignment->groupInfo;
        
        $logs = BusAssignmentLog::where('bus_assignment_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $vehicles = Vehicle::with(['vehicleModel', 'vehicleType', 'branch'])
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
        
        $agencies = Agency::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->orderBy('agency_code', 'asc')
            ->get();
        
        $reservationCategories = ReservationCategory::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
            
        $vehicleGrades = VehicleGrade::orderBy('id')->get();
        
        $options = Option::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get(['id', 'name']);
        $selectedOptions = [];
        if ($busAssignment->options) {
            $selectedOptions = explode(',', $busAssignment->options);
        }
        
        return view('masters.bus-assignments.edit', compact(
            'busAssignment',
            'groupInfo',
            'vehicles',
            'drivers',
            'guides',
            'agencies',
            'reservationCategories',
            'logs',
            'vehicleGrades',
            'options',
            'selectedOptions'
        ));
    }

    public function update(Request $request, $id)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->input('iframe') == '1';
        
        try {
            $busAssignment = BusAssignment::findOrFail($id);
            
            $oldLock = (bool)$busAssignment->lock_arrangement;
            $oldFinalized = (bool)$busAssignment->status_finalized;
            $oldSent = (bool)$busAssignment->status_sent;
            $oldVehicleSpec = (bool)$busAssignment->vehicle_type_spec_check;
            $oldReservationStatus = $busAssignment->groupInfo->reservation_status ?? null;
            
            $groupInfo = $busAssignment->groupInfo;
            
            $oldVehicleId = $busAssignment->vehicle_id;
            $oldDriverId = $busAssignment->driver_id;
            
            $oldStartDate = $busAssignment->start_date ? Carbon::parse($busAssignment->start_date)->format('Y-m-d') : null;
            $oldEndDate = $busAssignment->end_date ? Carbon::parse($busAssignment->end_date)->format('Y-m-d') : null;
            $oldStartTime = $busAssignment->start_time;
            $oldEndTime = $busAssignment->end_time;
            
            $rules = [
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'driver_id' => 'nullable|exists:drivers,id',
                'guide' => 'nullable|string|max:200',
                'start_date' => 'nullable|date',
                'start_time' => 'nullable',
                'end_date' => 'nullable|date',
                'end_time' => 'nullable',
                'vehicle_number' => 'nullable|string|max:50',
                'step_car' => 'nullable|string|max:50',
                'adult_count' => 'nullable|integer|min:0',
                'child_count' => 'nullable|integer|min:0',
                'guide_count' => 'nullable|integer|min:0',
                'luggage' => 'nullable|string|max:255',
                'options' => 'nullable|array',
                'options.*' => 'exists:option,id',
                'representative' => 'nullable|string|max:100',
                'representative_phone' => 'nullable|string|max:20',
                'attention' => 'nullable|string',
                'operation_remarks' => 'nullable|string',
                'operation_memo' => 'nullable|string',
                'operation_basic_remarks' => 'nullable|string',
                'doc_remarks' => 'nullable|string',
                'history_remarks' => 'nullable|string',
                'lock_arrangement' => 'nullable|boolean',
                'status_sent' => 'nullable|boolean',
                'status_finalized' => 'nullable|boolean',
                'vehicle_type_spec_check' => 'nullable|boolean',
                'vehicle_grade_id' => 'nullable|exists:vehicle_grades,id',
                'temporary_driver' => 'nullable|boolean',
                'ignore_operation' => 'nullable|boolean',
                'ignore_driver' => 'nullable|boolean',
                'reservation_status' => 'nullable|string',
                'reservation_categories_id' => 'nullable|integer|exists:reservation_categories,id',
                'agency' => 'nullable|string|max:200',
                'agency_code' => 'nullable|string|max:50',
                'agency_branch' => 'nullable|string|max:100',
                'agency_phone' => 'nullable|string|max:20',
                'agency_contact_name' => 'nullable|string|max:100',
                'agency_country' => 'nullable|string|max:100',
                'group_name' => 'nullable|string|max:200',
                'itinerary_name' => 'nullable|string|max:200',
                'agt_tour_id' => 'nullable|string|max:100',
                'remarks' => 'nullable|string',
            ];
            
            $messages = [
                'vehicle_id.exists' => '選択された車両は存在しません。',
                'driver_id.exists' => '選択された運転手は存在しません。',
                'start_date.date' => '開始日の形式が正しくありません。',
                'end_date.date' => '終了日の形式が正しくありません。',
                'adult_count.integer' => '大人人数は数値で入力してください。',
                'adult_count.min' => '大人人数は0以上の数値を入力してください。',
                'child_count.integer' => '小人人数は数値で入力してください。',
                'child_count.min' => '小人人数は0以上の数値を入力してください。',
                'guide_count.integer' => 'ガイド人数は数値で入力してください。',
                'guide_count.min' => 'ガイド人数は0以上の数値を入力してください。',
                'options.*.exists' => '選択されたオプションが存在しません。',
                'reservation_categories_id.exists' => '選択された業務分類が存在しません。',
                'agency.string' => '代理店名は文字列で入力してください。',
                'agency.max' => '代理店名は200文字以内で入力してください。',
                'group_name.max' => '団体名は200文字以内で入力してください。',
                'itinerary_name.max' => '行程名は200文字以内で入力してください。',
                'agt_tour_id.max' => 'AGT予約IDは100文字以内で入力してください。',
            ];
            
            $validated = $request->validate($rules, $messages);
            
            $checkboxFields = [
                'lock_arrangement',
                'status_sent',
                'status_finalized',
                'vehicle_type_spec_check',
                'temporary_driver',
                'ignore_operation',
                'ignore_driver',
            ];
            
            foreach ($checkboxFields as $field) {
                $validated[$field] = filter_var($validated[$field] ?? false, FILTER_VALIDATE_BOOLEAN);
            }
            
            if (isset($validated['reservation_categories_id']) && $validated['reservation_categories_id'] == 0) {
                $validated['reservation_categories_id'] = null;
            }
            
            $newStartDate = $validated['start_date'] ? Carbon::parse($validated['start_date'])->format('Y-m-d') : $oldStartDate;
            $newEndDate = $validated['end_date'] ? Carbon::parse($validated['end_date'])->format('Y-m-d') : $oldEndDate;
            $newStartTime = $validated['start_time'] ?? $oldStartTime;
            $newEndTime = $validated['end_time'] ?? $oldEndTime;
            
            $dateRangeChanged = ($oldStartDate != $newStartDate) || ($oldEndDate != $newEndDate);
            $timeChanged = ($oldStartTime != $newStartTime) || ($oldEndTime != $newEndTime);
            
            $newLock = filter_var($validated['lock_arrangement'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $newFinalized = filter_var($validated['status_finalized'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $newSent = filter_var($validated['status_sent'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $newVehicleSpec = filter_var($validated['vehicle_type_spec_check'] ?? false, FILTER_VALIDATE_BOOLEAN);
            
            
            $itineraries = DailyItinerary::where('bus_assignment_id', $busAssignment->id)
                ->orderBy('date', 'asc')
                ->get();
            
            if ($itineraries->isNotEmpty()) {
                $itineraryData = [];
                foreach ($itineraries as $itinerary) {
                    $itineraryData[] = [
                        'date' => Carbon::parse($itinerary->date)->format('Y-m-d'),
                        'time_start' => $itinerary->time_start,
                        'time_end' => $itinerary->time_end,
                    ];
                }
                
                $groupInfoData = $request->input('group_info', []);
                $newIgnoreOperation = isset($groupInfoData['ignore_operation']) 
                    ? filter_var($groupInfoData['ignore_operation'], FILTER_VALIDATE_BOOLEAN) 
                    : false;
                
                $newIgnoreAttendance = isset($groupInfoData['ignore_attendance']) 
                    ? filter_var($groupInfoData['ignore_attendance'], FILTER_VALIDATE_BOOLEAN) 
                    : false;

                $newReservationStatus = $validated['reservation_status'] ?? ($groupInfo ? $groupInfo->reservation_status : null);
                
                $shouldCheck = !in_array($newReservationStatus, ['見積', 'キャンセル']) && !$newIgnoreOperation;
                
                if ($shouldCheck) {
                    $this->checkConflicts(
                        $validated['vehicle_id'] ?? $busAssignment->vehicle_id,
                        $validated['driver_id'] ?? $busAssignment->driver_id,
                        $itineraryData,
                        $busAssignment->id,
                        $groupInfo ? $groupInfo->id : null,
                        $newIgnoreOperation,
                        $newIgnoreAttendance,
                        $groupInfo ? $groupInfo->reservation_status : null,
                    );
                }
            }
            
            if ($request->has('options')) {
                $validated['options'] = implode(',', $request->options);
            } else {
                $validated['options'] = null;
            }
            
            $busAssignment->update($validated);
            

            $groupInfoData = [];
            $groupInfoData['ignore_operation'] = isset($request->group_info['ignore_operation']) ? 1 : 0;
            $groupInfoData['ignore_attendance'] = isset($request->group_info['ignore_attendance']) ? 1 : 0;
            if ($request->has('group_info.vehicle_grade_id')) {
                $groupInfoData['vehicle_grade_id'] = $request->input('group_info.vehicle_grade_id');
            }
            $groupInfo->update($groupInfoData);
            
            if (isset($groupInfoData['vehicle_grade_id'])) {
                $busAssignment->vehicle_grade_id = $groupInfoData['vehicle_grade_id'];
                $busAssignment->save();
            }
            
            
            $userId = session('user_id', auth()->id() ?? 0);
            $username = session('username', auth()->user()->name ?? 'system');
            
            if ($oldLock != $newLock) {
                $actionDesc = $newLock ? 'Lock' : 'Un-Lock';
                BusAssignmentLog::log(
                    $busAssignment->id,
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
                BusAssignmentLog::log(
                    $busAssignment->id,
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
                BusAssignmentLog::log(
                    $busAssignment->id,
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
                BusAssignmentLog::log(
                    $busAssignment->id,
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
            
            $vehicleName = '';
            if ($busAssignment->vehicle_id) {
                $vehicle = Vehicle::find($busAssignment->vehicle_id);
                $vehicleName = $vehicle ? $vehicle->registration_number : '';
            }
            
            $driverName = '';
            if ($busAssignment->driver_id) {
                $driver = Driver::find($busAssignment->driver_id);
                $driverName = $driver ? $driver->name : '';
            }
            
            $guideName = '';
            if ($busAssignment->guide) {
                $guideName = $busAssignment->guide;
            }
            
            Log::info("リソース名取得", [
                'bus_assignment_vehicle_id' => $busAssignment->vehicle_id,
                'vehicle_name' => $vehicleName,
                'bus_assignment_driver_id' => $busAssignment->driver_id,
                'driver_name' => $driverName
            ]);
            
            $updateItineraryData = [
                'vehicle_id' => $busAssignment->vehicle_id,
                'vehicle' => $vehicleName,
                'driver_id' => $busAssignment->driver_id,
                'driver' => $driverName,
                'guide' => $guideName,
                'updated_at' => now(),
                'updated_by' => session('user_id', auth()->id() ?? 0),
            ];
            
            Log::info("行程データ強制同期", [
                'bus_assignment_id' => $busAssignment->id,
                'update_data' => $updateItineraryData
            ]);
            
            $updatedCount = DailyItinerary::where('bus_assignment_id', $busAssignment->id)
                ->update($updateItineraryData);
            
            Log::info("強制同期完了", [
                'bus_assignment_id' => $busAssignment->id,
                'updated_count' => $updatedCount
            ]);
            
            $verifyItineraries = DailyItinerary::where('bus_assignment_id', $busAssignment->id)
                ->select('id', 'vehicle_id', 'vehicle', 'driver_id', 'driver')
                ->first();
            
            if ($verifyItineraries) {
                Log::info("同期結果確認", [
                    'vehicle_id' => $verifyItineraries->vehicle_id,
                    'vehicle' => $verifyItineraries->vehicle,
                    'driver_id' => $verifyItineraries->driver_id,
                    'driver' => $verifyItineraries->driver
                ]);
            }
            
            if ($timeChanged) {
                Log::info("開始同步時間変化", [
                    'bus_assignment_id' => $busAssignment->id,
                    'new_start_time' => $newStartTime,
                    'new_end_time' => $newEndTime
                ]);
                
                DailyItinerary::where('bus_assignment_id', $busAssignment->id)
                    ->update([
                        'time_start' => $newStartTime,
                        'time_end' => $newEndTime,
                        'updated_at' => now(),
                        'updated_by' => session('user_id', auth()->id() ?? 0),
                    ]);
                
                Log::info("時間同步完了", [
                    'bus_assignment_id' => $busAssignment->id,
                    'updated_time_start' => $newStartTime,
                    'updated_time_end' => $newEndTime
                ]);
            }
            
            if ($dateRangeChanged && $newStartDate && $newEndDate) {
                $this->syncDailyItinerariesByDateRange(
                    $busAssignment,
                    $oldStartDate,
                    $oldEndDate,
                    $newStartDate,
                    $newEndDate,
                    $newStartTime,
                    $newEndTime
                );
            }
            
            $newStartDate = $validated['start_date'] ?? $busAssignment->start_date;
            $newEndDate = $validated['end_date'] ?? $busAssignment->end_date;
            $newReservationStatus = $validated['reservation_status'] ?? ($groupInfo ? $groupInfo->reservation_status : null);
            $newIgnoreOperation = $validated['ignore_operation'] ?? $busAssignment->ignore_operation;
            
            if (!in_array($newReservationStatus, ['見積', 'キャンセル']) && !$newIgnoreOperation) {
                $stopOrderDates = GroupInfoDateRemark::getStopOrderDates($newStartDate, $newEndDate);
                
                if ($stopOrderDates->isNotEmpty()) {
                    $dateList = $stopOrderDates->map(function($date) {
                        return Carbon::parse($date)->format('Y/m/d');
                    })->implode('、');
                    
                    if ($isAjax) {
                        return response()->json([
                            'success' => false,
                            'message' => "以下の日付は受注停止のため、運行を割り当てできません：{$dateList}"
                        ], 422);
                    }
                    
                    return back()->withInput()->withErrors([
                        'stop_order' => "以下の日付は受注停止のため、運行を割り当てできません：{$dateList}"
                    ]);
                }
            }
            
            
            if ($groupInfo) {
                $directUpdateFields = [
                    'reservation_status',
                    'reservation_categories_id',
                    'agency',
                    'agency_code',
                    'agency_branch',
                    'agency_phone',
                    'agency_contact_name',
                    'agency_country',
                    'group_name',
                    'itinerary_name',
                    'agt_tour_id',
                    'remarks',
                ];
                
                $groupInfoData = [];
                foreach ($directUpdateFields as $field) {
                    if (array_key_exists($field, $validated)) {
                        $groupInfoData[$field] = $validated[$field];
                    }
                }
                
                $totalAdult = 0;
                $totalChild = 0;
                $totalGuide = 0;
                $totalOther = 0;
                $totalLuggage = 0;
                
                $allBusAssignments = BusAssignment::where('group_info_id', $groupInfo->id)->get();
                foreach ($allBusAssignments as $ba) {
                    $totalAdult += $ba->adult_count ?? 0;
                    $totalChild += $ba->child_count ?? 0;
                    $totalGuide += $ba->guide_count ?? 0;
                    $totalOther += $ba->other_count ?? 0;
                    $totalLuggage += $ba->luggage_count ?? 0;
                }
                
                $groupInfoData['adult_count'] = $totalAdult;
                $groupInfoData['child_count'] = $totalChild;
                $groupInfoData['guide_count'] = $totalGuide;
                $groupInfoData['other_count'] = $totalOther;
                $groupInfoData['luggage_count'] = $totalLuggage;
                
                $minStartDate = null;
                $minStartTime = null;
                $maxEndDate = null;
                $maxEndTime = null;
                
                foreach ($allBusAssignments as $ba) {
                    if ($ba->start_date) {
                        $baStartDate = Carbon::parse($ba->start_date)->format('Y-m-d');
                        if (!$minStartDate || $baStartDate < $minStartDate) {
                            $minStartDate = $baStartDate;
                            $minStartTime = $ba->start_time;
                        }
                    }
                    if ($ba->end_date) {
                        $baEndDate = Carbon::parse($ba->end_date)->format('Y-m-d');
                        if (!$maxEndDate || $baEndDate > $maxEndDate) {
                            $maxEndDate = $baEndDate;
                            $maxEndTime = $ba->end_time;
                        }
                    }
                }
                
                if ($minStartDate) {
                    $groupInfoData['start_date'] = $minStartDate;
                    $groupInfoData['start_time'] = $minStartTime;
                }
                if ($maxEndDate) {
                    $groupInfoData['end_date'] = $maxEndDate;
                    $groupInfoData['end_time'] = $maxEndTime;
                }
                
                $mainBus = $allBusAssignments->first();
                if ($mainBus) {
                    if ($mainBus->vehicle_id) {
                        $vehicle = Vehicle::find($mainBus->vehicle_id);
                        $groupInfoData['vehicle'] = $vehicle ? $vehicle->registration_number : null;
                    }
                    if ($mainBus->driver_id) {
                        $driver = Driver::find($mainBus->driver_id);
                        $groupInfoData['driver'] = $driver ? $driver->name : null;
                    }
                    if ($mainBus->guide_id) {
                        $guide = Guide::find($mainBus->guide_id);
                        $groupInfoData['guide'] = $guide ? $guide->name : null;
                    }
                }
                
                if (!empty($groupInfoData)) {
                    $groupInfo->update($groupInfoData);
                    
                    $groupInfo->refresh();
                    $newReservationStatus = $groupInfo->reservation_status ?? null;
                    
                    if ($oldReservationStatus != $newReservationStatus) {
                        $oldStatus = $oldReservationStatus ?? '未設定';
                        $newStatus = $newReservationStatus ?? '未設定';
                        $actionDesc = "{$newStatus}";
                        BusAssignmentLog::log(
                            $busAssignment->id,
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
            }
            
            Log::info("=== 更新完了 ===", ['bus_assignment_id' => $busAssignment->id]);
            
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => '運行割当を更新しました。'
                ]);
            }
            
            return redirect()->route('masters.bus-assignments.index')
                ->with('success', '運行割当を更新しました。');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("バリデーションエラー", ['errors' => $e->errors()]);
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => '入力エラーがあります',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
            
        } catch (\Exception $e) {
            Log::error("更新エラー", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => '更新中にエラーが発生しました: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    private function checkConflicts(
        $vehicleId,
        $driverId,
        array $itineraries,
        $excludeBusId = null,
        $excludeGroupId = null,
        $skipIgnoreOperation = false,
        $ignoreAttendance = false,
        $currentReservationStatus = null
    ) {
        
        if (in_array($currentReservationStatus, ['見積', 'キャンセル'])) {
            return;
        }
        
        $checkVehicle = !empty($vehicleId);
        $checkDriver = !empty($driverId) && !$ignoreAttendance;
        
        if (!$checkVehicle && !$checkDriver) {
            return;
        }
        
        if (empty($itineraries)) {
            return;
        }
        
        if ($checkDriver && $driverId) {
            foreach ($itineraries as $itinerary) {
                $date = $itinerary['date'];
                $timeStart = $itinerary['time_start'];
                $timeEnd = $itinerary['time_end'];
                
                $startDateTime = Carbon::parse($date . ' ' . $timeStart);
                $endDateTime = Carbon::parse($date . ' ' . $timeEnd);
                
                if (!$ignoreAttendance) {
                    $restConflict = $this->checkDriverRestConflict(
                        $driverId,
                        $startDateTime,
                        $endDateTime,
                        $excludeBusId
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
        }
        
        $schedules = [];
        foreach ($itineraries as $itinerary) {
            $date = $itinerary['date'];
            $timeStart = $itinerary['time_start'];
            $timeEnd = $itinerary['time_end'];
            
            if ($checkVehicle) {
                $schedules[] = [
                    'type' => 'vehicle',
                    'id' => $vehicleId,
                    'date' => $date,
                    'start' => $timeStart,
                    'end' => $timeEnd,
                ];
            }
            
            if ($checkDriver) {
                $schedules[] = [
                    'type' => 'driver',
                    'id' => $driverId,
                    'date' => $date,
                    'start' => $timeStart,
                    'end' => $timeEnd,
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
        
        foreach ($groupedSchedules as $key => $daySchedules) {
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
            
            if ($excludeBusId) {
                $query->where('bus_assignment_id', '!=', $excludeBusId);
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
                    
                    if ($skipIgnoreOperation && $otherBus && $otherBus->ignore_operation) {
                        continue;
                    }
                    
                    $existingStart = Carbon::parse($existing->time_start);
                    $existingEnd = Carbon::parse($existing->time_end);
                    
                    if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                        $conflictGroup = GroupInfo::find($existing->group_info_id);
                        $conflictGroupName = $conflictGroup ? $conflictGroup->group_name : '不明';
                        
                        if ($resourceType === 'vehicle') {
                            $vehicle = Vehicle::find($resourceId);
                            $resourceName = $vehicle ? $vehicle->registration_number : '#' . $resourceId;
                            throw new \Exception(
                                "車両「{$resourceName}」は日付「{$date}」 " . 
                                substr($schedule['start'], 0, 5) . "～" . substr($schedule['end'], 0, 5) . 
                                " に他のグループ「{$conflictGroupName}」(運行ID: {$existing->bus_assignment_id})の運行で既に使用されています。"
                            );
                        } else {
                            $driver = Driver::find($resourceId);
                            $resourceName = $driver ? $driver->name : '#' . $resourceId;
                            throw new \Exception(
                                "運転手「{$resourceName}」は日付「{$date}」 " . 
                                substr($schedule['start'], 0, 5) . "～" . substr($schedule['end'], 0, 5) . 
                                " に他のグループ「{$conflictGroupName}」(運行ID: {$existing->bus_assignment_id})の運行で既に使用されています。"
                            );
                        }
                    }
                }
            }
        }
        
        foreach ($schedules as $i => $schedule1) {
            foreach ($schedules as $j => $schedule2) {
                if ($i >= $j) continue;
                
                if ($schedule1['type'] !== $schedule2['type']) continue;
                if ($schedule1['id'] !== $schedule2['id']) continue;
                if ($schedule1['date'] !== $schedule2['date']) continue;
                
                $start1 = Carbon::parse($schedule1['start']);
                $end1 = Carbon::parse($schedule1['end']);
                $start2 = Carbon::parse($schedule2['start']);
                $end2 = Carbon::parse($schedule2['end']);
                
                if ($start1->lt($end2) && $end1->gt($start2)) {
                    if ($schedule1['type'] === 'vehicle') {
                        $vehicle = Vehicle::find($schedule1['id']);
                        $resourceName = $vehicle ? $vehicle->registration_number : '#' . $schedule1['id'];
                        throw new \Exception(
                            "同一運行内で車両「{$resourceName}」が日付「{$schedule1['date']}」に時間が重複しています。" .
                            "({$schedule1['start']}～{$schedule1['end']} と {$schedule2['start']}～{$schedule2['end']})"
                        );
                    } else {
                        $driver = Driver::find($schedule1['id']);
                        $resourceName = $driver ? $driver->name : '#' . $schedule1['id'];
                        throw new \Exception(
                            "同一運行内で運転手「{$resourceName}」が日付「{$schedule1['date']}」に時間が重複しています。" .
                            "({$schedule1['start']}～{$schedule1['end']} と {$schedule2['start']}～{$schedule2['end']})"
                        );
                    }
                }
            }
        }
    }
    
    private function checkDriverRestConflict($driverId, $startDateTime, $endDateTime, $excludeBusId = null)
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

    private function syncDailyItinerariesByDateRange(
        BusAssignment $busAssignment,
        $oldStartDate,
        $oldEndDate,
        $newStartDate,
        $newEndDate,
        $defaultStartTime = null,
        $defaultEndTime = null
    ) {
        try {
            Log::info("行程同期開始", [
                'bus_assignment_id' => $busAssignment->id,
                'old_start' => $oldStartDate,
                'old_end' => $oldEndDate,
                'new_start' => $newStartDate,
                'new_end' => $newEndDate
            ]);
            
            $newStart = Carbon::parse($newStartDate);
            $newEnd = Carbon::parse($newEndDate);
            
            $newDateRange = [];
            $current = $newStart->copy();
            while ($current <= $newEnd) {
                $dateStr = $current->format('Y-m-d');
                $newDateRange[] = $dateStr;
                $current->addDay();
            }
            
            $oldDateRange = [];
            if ($oldStartDate && $oldEndDate) {
                $oldStart = Carbon::parse($oldStartDate);
                $oldEnd = Carbon::parse($oldEndDate);
                $current = $oldStart->copy();
                while ($current <= $oldEnd) {
                    $oldDateRange[] = $current->format('Y-m-d');
                    $current->addDay();
                }
            }
            
            Log::info("日付範囲", [
                'old_range' => $oldDateRange,
                'new_range' => $newDateRange
            ]);
            
            $datesToDelete = array_diff($oldDateRange, $newDateRange);
            $datesToAdd = array_diff($newDateRange, $oldDateRange);
            
            Log::info("処理対象", [
                'dates_to_delete' => $datesToDelete,
                'dates_to_add' => $datesToAdd
            ]);
            
            if (!empty($datesToDelete)) {
                $deleted = DailyItinerary::where('bus_assignment_id', $busAssignment->id)
                    ->whereIn('date', $datesToDelete)
                    ->delete();
                
                Log::info("運行 #{$busAssignment->id} から {$deleted} 日分の行程を削除", [
                    'dates' => $datesToDelete
                ]);
            }
            
            $existingItineraries = DailyItinerary::where('bus_assignment_id', $busAssignment->id)
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });
            
            if (!empty($datesToAdd)) {
                $templateItinerary = $this->getTemplateItinerary($busAssignment, $oldDateRange, $existingItineraries);
                
                Log::info("テンプレート行程", [
                    'has_template' => $templateItinerary ? true : false,
                    'template_date' => $templateItinerary ? $templateItinerary->date : null
                ]);
                
                sort($datesToAdd);
                
                foreach ($datesToAdd as $newDate) {
                    $this->createItineraryFromTemplate(
                        $busAssignment,
                        $newDate,
                        $templateItinerary,
                        $defaultStartTime,
                        $defaultEndTime
                    );
                }
                
                Log::info("運行 #{$busAssignment->id} に " . count($datesToAdd) . " 日分の行程を追加", [
                    'dates' => $datesToAdd
                ]);
            } else {
                Log::info("追加する行程はありません");
            }
            
        } catch (\Exception $e) {
            Log::error("行程同期中にエラー: " . $e->getMessage(), [
                'bus_assignment_id' => $busAssignment->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    private function getTemplateItinerary(BusAssignment $busAssignment, array $oldDateRange, $existingItineraries)
    {
        if (!empty($oldDateRange)) {
            $reversedOldDates = array_reverse($oldDateRange);
            foreach ($reversedOldDates as $date) {
                if ($existingItineraries->has($date)) {
                    Log::info("テンプレート行程を発見", ['date' => $date]);
                    return $existingItineraries->get($date);
                }
            }
        }
        
        $lastItinerary = DailyItinerary::where('bus_assignment_id', $busAssignment->id)
            ->orderBy('date', 'desc')
            ->first();
        
        if ($lastItinerary) {
            Log::info("最終日の行程をテンプレートとして使用", ['date' => $lastItinerary->date]);
            return $lastItinerary;
        }
        
        Log::info("テンプレート行程が見つかりません。デフォルト値を使用します");
        return null;
    }
    
    private function createItineraryFromTemplate(
        BusAssignment $busAssignment,
        $newDate,
        $template = null,
        $defaultStartTime = null,
        $defaultEndTime = null
    ) {
        $data = [
            'bus_assignment_id' => $busAssignment->id,
            'group_info_id' => $busAssignment->group_info_id,
            'date' => $newDate,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => session('user_id', auth()->id() ?? 0),
            'updated_by' => session('user_id', auth()->id() ?? 0),
        ];
        
        if ($template) {
            $data['time_start'] = $template->time_start;
            $data['time_end'] = $template->time_end;
            $data['itinerary'] = $template->itinerary;
            $data['start_location'] = $template->start_location;
            $data['end_location'] = $template->end_location;
            $data['accommodation'] = $template->accommodation;
            $data['vehicle_id'] = $template->vehicle_id;
            $data['vehicle'] = $template->vehicle;
            $data['driver_id'] = $template->driver_id;
            $data['driver'] = $template->driver;
            $data['guide'] = $template->guide;
            
            Log::info("テンプレートから行程を作成", [
                'bus_assignment_id' => $busAssignment->id,
                'new_date' => $newDate,
                'template_date' => $template->date
            ]);
        } else {
            $data['time_start'] = $defaultStartTime ?: ($busAssignment->start_time ?: '08:00:00');
            $data['time_end'] = $defaultEndTime ?: ($busAssignment->end_time ?: '18:00:00');
            $data['itinerary'] = null;
            $data['start_location'] = null;
            $data['end_location'] = null;
            $data['accommodation'] = 0;
            
            if ($busAssignment->vehicle_id) {
                $data['vehicle_id'] = $busAssignment->vehicle_id;
                $vehicle = Vehicle::find($busAssignment->vehicle_id);
                $data['vehicle'] = $vehicle ? $vehicle->registration_number : null;
            }
            if ($busAssignment->driver_id) {
                $data['driver_id'] = $busAssignment->driver_id;
                $driver = Driver::find($busAssignment->driver_id);
                $data['driver'] = $driver ? $driver->name : null;
            }
            if ($busAssignment->guide_id) {
                $data['guide_id'] = $busAssignment->guide_id;
                $guide = Guide::find($busAssignment->guide_id);
                $data['guide'] = $guide ? $guide->name : null;
            }
            
            Log::info("デフォルト値で行程を作成", [
                'bus_assignment_id' => $busAssignment->id,
                'new_date' => $newDate,
                'time_start' => $data['time_start'],
                'time_end' => $data['time_end']
            ]);
        }
        
        $data['time_start'] = $data['time_start'] ?? '08:00:00';
        $data['time_end'] = $data['time_end'] ?? '18:00:00';
        
        try {
            $itinerary = DailyItinerary::create($data);
            Log::info("行程作成成功", [
                'id' => $itinerary->id,
                'bus_assignment_id' => $busAssignment->id,
                'date' => $newDate
            ]);
        } catch (\Exception $e) {
            Log::error("行程作成失敗", [
                'bus_assignment_id' => $busAssignment->id,
                'date' => $newDate,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function updateDailyItineraries($itineraries, $busAssignmentId, $vehicleId = null, $driverId = null, $guideId = null, $vehicleName = '', $driverName = '', $guideName = '')
    {
        foreach ($itineraries as $index => $itineraryData) {
            if (empty($itineraryData['date'])) {
                continue;
            }
            
            $itineraryId = $itineraryData['id'] ?? null;
            
            $data = [
                'bus_assignment_id' => $busAssignmentId,
                'date' => $itineraryData['date'],
                'time_start' => $itineraryData['time_start'] . ':00',
                'time_end' => $itineraryData['time_end'] . ':00',
                'itinerary' => $itineraryData['itinerary'] ?? null,
                'start_location' => $itineraryData['start_location'] ?? null,
                'end_location' => $itineraryData['end_location'] ?? null,
                'accommodation' => isset($itineraryData['accommodation']) ? (int)$itineraryData['accommodation'] : 0,
                'updated_at' => now(),
            ];
            
            if ($vehicleId) {
                $data['vehicle_id'] = $vehicleId;
                $data['vehicle'] = $vehicleName;
            } else {
                $data['vehicle_id'] = null;
                $data['vehicle'] = null;
            }
            
            if ($driverId) {
                $data['driver_id'] = $driverId;
                $data['driver'] = $driverName;
            } else {
                $data['driver_id'] = null;
                $data['driver'] = null;
            }
            
            if ($guideId) {
                $data['guide_id'] = $guideId;
                $data['guide'] = $guideName;
            } else {
                $data['guide_id'] = null;
                $data['guide'] = null;
            }
            
            $data = array_filter($data, function($value) {
                return !is_null($value);
            });
            
            if ($itineraryId && is_numeric($itineraryId)) {
                DailyItinerary::where('id', $itineraryId)
                    ->where('bus_assignment_id', $busAssignmentId)
                    ->update($data);
            } elseif (empty($itineraryId)) {
                $data['created_at'] = now();
                $data['created_by'] = session('user_id', auth()->id() ?? 0);
                $data['updated_by'] = session('user_id', auth()->id() ?? 0);
                DailyItinerary::create($data);
            }
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $busAssignment = BusAssignment::findOrFail($id);
            $groupId = $busAssignment->group_info_id;
            $reservationStatus = $busAssignment->groupInfo->reservation_status ?? '';
            
            if ($reservationStatus !== 'キャンセル') {
                return response()->json([
                    'success' => false,
                    'message' => '削除するには、予約状況を「キャンセル」に変更してください。'
                ], 400);
            }
            
            DailyItinerary::where('bus_assignment_id', $busAssignment->id)->delete();
            
            $busAssignment->delete();
            
            $remainingBuses = BusAssignment::where('group_info_id', $groupId)->count();
            
            if ($remainingBuses == 0) {
                GroupInfo::where('id', $groupId)->delete();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '削除しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '削除中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids)) {
            return back()->with('error', '項目を選択してください。');
        }

        DB::transaction(function() use ($ids, $action) {
            $assignments = BusAssignment::whereIn('id', $ids)->get();

            foreach ($assignments as $assignment) {
                switch ($action) {
                    case 'lock':
                        $assignment->lock_arrangement = true;
                        break;
                    case 'unlock':
                        $assignment->lock_arrangement = false;
                        break;
                    case 'mark_sent':
                        $assignment->status_sent = true;
                        break;
                    case 'finalize':
                        $assignment->status_finalized = true;
                        $assignment->status_sent = true;
                        break;
                    case 'unfinalize':
                        $assignment->status_finalized = false;
                        break;
                }
                $assignment->save();
            }
        });

        return back()->with('success', '一括更新が完了しました。');
    }

    public function printInstructions(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', '印刷する項目を選択してください。');
        }

        $assignments = BusAssignment::with(['groupInfo', 'vehicle', 'driver', 'dailyItineraries'])
            ->whereIn('id', $ids)
            ->orderBy('vehicle_index')
            ->get();

        return view('masters.bus-assignments.print', compact('assignments'));
    }

    public function updateDateTime(Request $request, BusAssignment $busAssignment)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'nullable',
            'end_date' => 'required|date',
            'end_time' => 'nullable',
        ]);

        $busAssignment->update($validated);

        return response()->json(['success' => true]);
    }
    
    
    
    
    
    
    
    public function batchExportPdf(Request $request)
    {
        $busIds = $request->input('bus_ids', []);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $driverName = $request->input('driver_name');
        
        if (empty($busIds)) {
            return response()->json(['success' => false, 'message' => '運行を選択してください。'], 400);
        }
        
        if (empty($startDate) || empty($endDate)) {
            return response()->json(['success' => false, 'message' => '日付範囲を選択してください。'], 400);
        }
        
        if (empty($driverName)) {
            return response()->json(['success' => false, 'message' => '運転手を選択してください。'], 400);
        }
        
        $busAssignments = BusAssignment::with([
            'groupInfo',
            'vehicle',
            'driver',
            'dailyItineraries',
        ])->whereIn('id', $busIds)
          ->orderBy('start_date', 'asc')
          ->orderBy('start_time', 'asc')
          ->get();
        
        foreach ($busAssignments as $assignment) {
            if (!$assignment->driver_id) {
                return response()->json([
                    'success' => false, 
                    'message' => "運行ID {$assignment->id} は運転手が設定されていません。"
                ], 400);
            }
        }
        
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_footer' => 10,
            'tempDir' => sys_get_temp_dir(),
            'fontDir' => [
                storage_path('fonts'),
            ],
            'fontdata' => [
                'msyh' => [
                    'R' => 'msyh.ttf',
                    'B' => 'msyhbd.ttf',
                ],
                'genshin' => [
                    'R' => 'GenShinGothic-Normal.ttf',
                    'B' => 'GenShinGothic-Bold.ttf',
                ],
            ],
            'default_font' => 'msyh',
            
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'useSubstitutions'  => true,
        ]);
        
        $mpdf->shrink_tables_to_fit = 0;
        $mpdf->keep_table_proportions = true;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        
        $firstPage = true;
        
        
        $mpdf->SetHTMLFooter('
            <table style="width: 100%; border: 0; margin-top: 5px;">
                <tr>
                    <td style="text-align: left; width: 50%; font-size: 9pt; border: 0;">{PAGENO} / {nbpg}</td>
                    <td style="text-align: right; width: 50%;font-size: 9pt; border: 0;">
                        発行日：' . date('Y/m/d') . '　　
                        発行担当：' . (session('staff_name') ?? 'システム') . '
                    </td>
                </tr>
            </table>
        ');
        
        foreach ($busAssignments as $busAssignment) {
            $busAssignment->dailyItineraries = $busAssignment->dailyItineraries
                ->sortBy('date')
                ->sortBy('time_start')
                ->values();
            
            $data = $this->prepareBatchPdfData($busAssignment);
            $html = view('masters.group-infos.bus-assignment-pdf', $data)->render();
            
            if ($firstPage) {
                $mpdf->WriteHTML($html);
                $firstPage = false;
            } else {
                $mpdf->AddPage();
                $mpdf->WriteHTML($html);
            }
        }
        
        $formattedStartDate = Carbon::parse($startDate)->format('Ymd');
        $formattedEndDate = Carbon::parse($endDate)->format('Ymd');
        $filename = $driverName . '_運行指示書 _' . $formattedStartDate . '_' . $formattedEndDate . '.pdf';
        
        return $mpdf->Output($filename, 'D');
    }
    
    private function prepareBatchPdfData($busAssignment)
    {
        $groupInfo = $busAssignment->groupInfo;
        $vehicle = $busAssignment->vehicle;
        $driver = $busAssignment->driver;
        $guide = $busAssignment->guide;
        
        $companyInfo = ['name' => '', 'tel' => ''];
        $companyLogo = null;
        
        try {
            $userCompany = DB::table('user_company_info')->first();
            if ($userCompany) {
                $companyInfo['name'] = $userCompany->user_company_name ?? '';
                $companyInfo['tel'] = $userCompany->phone_number ?? '';
                if (!empty($userCompany->company_logo)) {
                    $logoPath = storage_path('app/public/' . $userCompany->company_logo);
                    $companyLogo = url('/storage/' . $userCompany->company_logo);
                }
            }
        } catch (\Exception $e) {}
        
        $businessCategoryName = '';
        if ($groupInfo && $groupInfo->reservation_categories_id) {
            $category = DB::table('reservation_categories')->find($groupInfo->reservation_categories_id);
            $businessCategoryName = $category->category_name ?? '';
        }
        
        $manager = $groupInfo->agency_contact_name ?? '';
        $route = $groupInfo->itinerary_name ?? '';
        
        $office = '';
        if ($vehicle && $vehicle->branch_id) {
            $branch = DB::table('branches')->find($vehicle->branch_id);
            $office = $branch->branch_name ?? '';
        }
        
        $vehicleName = $vehicle->vehicle_code ?? '';
        $vehicleColor = $vehicle->vehicle_color ?? '';
        $vehicleNumber = $vehicle->registration_number ?? '';
        $busNumber = $busAssignment->vehicle_number ?? '';
        $luggage = $busAssignment->luggage ?? '';
        $representativeName = $busAssignment->representative ?? '';
        $representativeContact = $busAssignment->representative_phone ?? '';
        
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $operationDate = Carbon::parse($busAssignment->operation_date ?? $busAssignment->start_date);
        $formattedDate = $operationDate->format('Y年n月j日') . '(' . $weekdays[$operationDate->dayOfWeek] . ')';
        
        $adultCount = $groupInfo->adult_count ?? 0;
        $childCount = $groupInfo->child_count ?? 0;
        $personnelCount = '大：' . $adultCount . ' 小：' . $childCount;
        
        $itineraryRows = [];
        foreach ($busAssignment->dailyItineraries as $index => $itinerary) {
            $itineraryRows[] = [
                'day' => Carbon::parse($itinerary->date)->format('m/d'),
                'start_time' => $itinerary->time_start ? Carbon::parse($itinerary->time_start)->format('H:i') : '',
                'start_location' => $itinerary->start_location ?? '',
                'arrow' => '-->',
                'end_time' => $itinerary->time_end ? Carbon::parse($itinerary->time_end)->format('H:i') : '',
                'end_location' => $itinerary->end_location ?? '',
                'description' => $itinerary->itinerary ?? '',
            ];
        }
        
        if (empty($itineraryRows)) {
            $today = Carbon::now();
            $itineraryRows[] = [
                'day' => $today->format('m/d'), 
                'start_time' => '', 
                'start_location' => '', 
                'arrow' => '-->', 
                'end_time' => '', 
                'end_location' => '', 
                'description' => ''
            ];
        }
        
        $optionsNames = '';
        if ($busAssignment->options) {
            $optionIds = explode(',', $busAssignment->options);
            $options = DB::table('option')->whereIn('id', $optionIds)->pluck('name')->toArray();
            $optionsNames = implode('、', $options);
        }
        
        $issueDate = Carbon::now()->format('Y/m/d');
        $issueBy = session('username', auth()->user()->name ?? '');
        
        return [
            'companyInfo' => $companyInfo,
            'companyLogo' => $companyLogo,
            'busAssignment' => $busAssignment,
            'groupInfo' => $groupInfo,
            'vehicle' => $vehicle,
            'driver' => $driver,
            'guide' => $guide,
            'formattedDate' => $formattedDate,
            'personnelCount' => $personnelCount,
            'itineraryRows' => $itineraryRows,
            'issueDate' => $issueDate,
            'issueBy' => $issueBy,
            'businessCategoryName' => $businessCategoryName,
            'manager' => $manager,
            'route' => $route,
            'office' => $office,
            'vehicleName' => $vehicleName,
            'vehicleColor' => $vehicleColor,
            'vehicleNumber' => $vehicleNumber,
            'busNumber' => $busNumber,
            'luggage' => $luggage,
            'representativeName' => $representativeName,
            'representativeContact' => $representativeContact,
            'optionsNames' => $optionsNames,
        ];
    }
    
    
    
    
    private function getCompensationSums(array $busIds)
    {
        if (empty($busIds)) {
            return [];
        }
        
        $results = DB::table('driver_compensations')
            ->whereIn('bus_assignment_id', $busIds)
            ->select('bus_assignment_id', DB::raw('SUM(amount) as total'))
            ->groupBy('bus_assignment_id')
            ->get();
        
        return $results->pluck('total', 'bus_assignment_id')->toArray();
    }
    
    private function getExpenseSums(array $busIds)
    {
        if (empty($busIds)) {
            return [];
        }
        
        $results = DB::table('driver_expenses')
            ->leftJoin('driver_payment_methods', 'driver_expenses.payment_method_id', '=', 'driver_payment_methods.id')
            ->whereIn('driver_expenses.bus_assignment_id', $busIds)
            ->where(function($query) {
                $query->where('driver_payment_methods.is_reimbursable', '!=', 0)
                      ->orWhereNull('driver_payment_methods.is_reimbursable')
                      ->orWhereNull('driver_expenses.payment_method_id');
            })
            ->select('driver_expenses.bus_assignment_id', DB::raw('SUM(driver_expenses.amount) as total'))
            ->groupBy('driver_expenses.bus_assignment_id')
            ->get();
        
        return $results->pluck('total', 'bus_assignment_id')->toArray();
    }

}