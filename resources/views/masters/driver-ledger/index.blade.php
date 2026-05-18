@extends('layouts.app')

@php use Carbon\Carbon; @endphp

@section('title', '運転手台帳')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">運転手台帳</h5>
    </div>
    
    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.driver-ledger.index') }}" class="row g-1" id="searchForm">
            <input type="hidden" name="display_days" id="display_days" value="{{ $displayDays ?? 7 }}">
            
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運行日</span>
                        <input type="text" name="start_date" value="{{ $startDate }}" 
                               class="form-control form-control-sm datepicker-3months" style="width: 120px; border-color: #E5E7EB;" placeholder="開始日" id="start_date" onchange="submitWithEndDate()">
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <select name="period" class="form-select form-select-sm" id="period_select">
                            <option value="1" {{ request('period') == 1 ? 'selected' : '' }}>1週間</option>
                            <option value="2" {{ request('period') == 2 ? 'selected' : '' }}>2週間</option>
                            <option value="3" {{ request('period') == 3 ? 'selected' : '' }}>3週間</option>
                            <option value="4" {{ request('period') == 4 ? 'selected' : '' }}>1ヶ月</option>
                        </select>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="btn-group btn-group-sm w-100">
                            <button type="button" class="btn btn-outline-secondary" onclick="moveDate('month', -1)">&lt;&lt;</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="moveDate('week', -1)">&lt;</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setToday()">今日</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="moveDate('week', 1)">&gt;</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="moveDate('month', 1)">&gt;&gt;</button>
                        </div>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">予約ID</span>
                        <input type="text" name="reservation_id" value="{{ request('reservation_id') }}"
                               class="form-control form-control-sm" style="width: 90px; border-color: #E5E7EB;">
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運行ID</span>
                        <input type="text" name="operation_id" value="{{ request('operation_id') }}"
                               class="form-control form-control-sm" style="width: 90px; border-color: #E5E7EB;">
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">営業所</span>
                        <div class="branch-dropdown">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 100px; text-align: left; background-color: #fff; border-color: #ced4da;">
                                    <span id="branchSelectedText">選択</span>
                                    <span id="branchSelectedCount" class="selected-count" style="display: none;">0</span>
                                </button>
                                <div class="dropdown-menu p-0" style="min-width: 200px;">
                                    <div class="dropdown-header border-bottom px-3 py-2">
                                        <label class="d-flex align-items-center w-100" style="cursor: pointer;">
                                            <input type="checkbox" id="branchSelectAll" class="me-2"> 
                                            <span>全て選択</span>
                                        </label>
                                    </div>
                                    <div style="max-height: 250px; overflow-y: auto;">
                                        @foreach($branches ?? [] as $branch)
                                            <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                                <input type="checkbox" name="branch_checkbox" value="{{ $branch->id }}" class="me-2 branch-checkbox"
                                                    {{ in_array($branch->id, (array)request('branch_ids', [])) ? 'checked' : '' }}>
                                                {{ $branch->branch_name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">業務分類</span>
                        <select name="reservation_categories_id" class="form-select form-select-sm" style="width: 120px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($reservationCategories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ request('reservation_categories_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">車両</span>
                        <select name="vehicle_id" class="form-select form-select-sm" style="width: 120px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($vehicles ?? [] as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }} 
                                    @if($vehicle->vehicleModel)
                                        ({{ $vehicle->vehicleModel->model_name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
    
                    <!--<div class="d-flex align-items-center">-->
                    <!--    <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運転手</span>-->
                    <!--    <select name="driver_id" class="form-select form-select-sm" style="width: 120px; border-color: #E5E7EB;">-->
                    <!--        <option value="">選択</option>-->
                    <!--        @foreach($drivers ?? [] as $driver)-->
                    <!--            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>-->
                    <!--                {{ $driver->name }} @if($driver->driver_code)({{ $driver->driver_code }})@endif-->
                    <!--            </option>-->
                    <!--        @endforeach-->
                    <!--    </select>-->
                    <!--</div>-->
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">代理店</span>
                        <select name="agency_id" class="form-select form-select-sm" style="width: 120px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($agencies ?? [] as $agency)
                                <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                    {{ $agency->agency_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">車種</span>
                        <select name="vehicle_type_id" class="form-select form-select-sm" style="width: 100px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($vehicleTypes ?? [] as $type)
                                <option value="{{ $type->id }}" {{ request('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">団体名</span>
                        <input type="text" name="group_name" value="{{ request('group_name') }}"
                               class="form-control form-control-sm" style="width: 120px; border-color: #E5E7EB;" placeholder="団体名">
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">予約状態</span>
                        <select name="reservation_status" class="form-select form-select-sm" style="width: 100px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            <option value="予約" {{ request('reservation_status') == '予約' ? 'selected' : '' }} style="background-color: #ccf5ff;">予約</option>
                            <option value="仮押さえ" {{ request('reservation_status') == '仮押さえ' ? 'selected' : '' }} style="background-color: #ffff99;">仮押さえ</option>
                            <option value="見積" {{ request('reservation_status') == '見積' ? 'selected' : '' }} style="background-color: #ccffcc;">見積</option>
                            <option value="危ない" {{ request('reservation_status') == '危ない' ? 'selected' : '' }} style="background-color: #ffcccc;">危ない</option>
                            <option value="確定待ち" {{ request('reservation_status') == '確定待ち' ? 'selected' : '' }} style="background-color: #ffd9b3;">確定待ち</option>
                            <option value="確定" {{ request('reservation_status') == '確定' ? 'selected' : '' }} style="background-color: #cbb87c;">確定</option>
                            <option value="送信済" {{ request('reservation_status') == '送信済' ? 'selected' : '' }} style="background-color: #e6e6fa;">送信済</option>
                            <option value="実績待ち" {{ request('reservation_status') == '実績待ち' ? 'selected' : '' }} style="background-color: #e0b0ff;">実績待ち</option>
                            <option value="運行済" {{ request('reservation_status') == '運行済' ? 'selected' : '' }} style="background-color: #c0c0c0;">運行済</option>
                            <option value="請求済" {{ request('reservation_status') == '請求済' ? 'selected' : '' }} style="background-color: #b0e0e6;">請求済</option>
                            <option value="キャンセル" {{ request('reservation_status') == 'キャンセル' ? 'selected' : '' }} style="background-color: #d3d3d3;">キャンセル</option>
                            <option value="稼働不可" {{ request('reservation_status') == '稼働不可' ? 'selected' : '' }} style="background-color: #2c2c2c; color: white;">稼働不可</option>
                        </select>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <input type="radio" class="btn-check" name="color_type" id="color_type_status" value="status" autocomplete="off" 
                                   {{ (request('color_type') == 'status' || !request()->has('color_type')) ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="btn btn-outline-secondary" for="color_type_status" style="background-color: #fff; border-color: #ced4da;">
                                <div style="display:inline-block; width:12px; height:12px; background-color:#ccf5ff; border:1px solid #999; margin-right:4px;"></div>
                                予約状態
                            </label>
                            
                            <input type="radio" class="btn-check" name="color_type" id="color_type_category" value="category" autocomplete="off" 
                                   {{ request('color_type') == 'category' ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="btn btn-outline-secondary" for="color_type_category" style="background-color: #fff; border-color: #ced4da;">
                                <div style="display:inline-block; width:12px; height:12px; background: linear-gradient(45deg, #ff9999, #99ff99); border:1px solid #999; margin-right:4px;"></div>
                                予約分類
                            </label>
                        </div>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="has_guide" name="has_guide" value="1" 
                                   {{ request('has_guide') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_guide" style="font-size: 0.8rem;">添乗員あり</label>
                        </div>
                    </div>
    
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm px-3"
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                        <a href="{{ route('masters.driver-ledger.index', ['reset_search' => 1]) }}" class="btn btn-sm btn-outline-secondary px-3"
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                            リセット
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="table-responsive" style="overflow-x: auto; overflow-y: visible;">
        <table class="table table-bordered table-sm ledger-table" style="font-size: 0.75rem; min-width: 800px;">
            <thead>
                    <th class="text-center" style="position: sticky; left: 0; background-color: #f8f9fa; z-index: 10; min-width: 180px;">運転手名 / 所属</th>
                    @foreach($dates as $date)
                        @php
                            $dateStr = $date['date']->format('Y-m-d');
                            $dateRemark = $dateRemarks[$dateStr] ?? null;
                            $dateColor = '';
                            if ($date['is_saturday']) {
                                $dateColor = 'color: #0066cc;';
                            } elseif ($date['is_sunday'] || $date['is_holiday']) {
                                $dateColor = 'color: #ff0000;';
                            }
                        @endphp
                        <th class="text-center date-header-cell" style="background-color: #e9ecef; min-width: 100px; vertical-align: top; cursor: pointer;" onclick="openDateRemarkModal('{{ $dateStr }}')">
                            <div style="padding: 4px;">
                                <div style="{{ $dateColor }}">{{ $date['display'] }}</div>
                                @if ($date['is_holiday'] && $date['holiday_name'])
                                    <div class="holiday-name" style="color: #ff0000; font-size: 0.6rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $date['holiday_name'] }}
                                    </div>
                                @endif
                                @if ($dateRemark && $dateRemark->remark)
                                    <div class="date-remark" title="{{ $dateRemark->remark }}">
                                        {{ \Illuminate\Support\Str::limit($dateRemark->remark, 12) }}
                                    </div>
                                @endif
                                @if ($dateRemark && $dateRemark->stop_order)
                                    <div class="stop-order-badge" style="color: #dc3545; font-size: 0.6rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        ⛔ 受注停止
                                    </div>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </thead>
            <tbody>
                @foreach($groupedDrivers as $index => $driverData)
                    @php
                        $driver = $driverData['driver'];
                        $rowBgColor = $index % 2 == 0 ? '#f8f9fa' : '#ffffff';
                        $schedule = $scheduleData[$driver->id]['schedule'] ?? [];
                        
                        $groupedByBus = [];
                        foreach ($schedule as $dateStr => $dayItineraries) {
                            if (!empty($dayItineraries)) {
                                foreach ($dayItineraries as $itinerary) {
                                    $busId = $itinerary['bus_assignment_id'];
                                    if (!isset($groupedByBus[$busId])) {
                                        $groupedByBus[$busId] = [];
                                    }
                                    $groupedByBus[$busId][] = [
                                        'date' => $dateStr,
                                        'itinerary' => $itinerary
                                    ];
                                }
                            }
                        }
                        
                        $mergedItineraries = [];
                        foreach ($groupedByBus as $busId => $items) {
                            usort($items, function($a, $b) {
                                return strcmp($a['date'], $b['date']);
                            });
                            
                            $currentGroup = null;
                            foreach ($items as $item) {
                                $currentDate = Carbon::parse($item['date']);
                                
                                if ($currentGroup === null) {
                                    $currentGroup = $item['itinerary'];
                                    $currentGroup['start_date'] = $item['date'];
                                    $currentGroup['end_date'] = $item['date'];
                                    $currentGroup['start_minutes'] = $item['itinerary']['start_minutes'];
                                    $currentGroup['end_minutes'] = $item['itinerary']['end_minutes'];
                                    $currentGroup['dates'] = [$item['date']];
                                } else {
                                    $lastDate = Carbon::parse($currentGroup['end_date']);
                                    $diffDays = $lastDate->diffInDays($currentDate);
                                    
                                    if ($diffDays == 1) {
                                        $currentGroup['end_date'] = $item['date'];
                                        $currentGroup['end_minutes'] = $item['itinerary']['end_minutes'];
                                        $currentGroup['dates'][] = $item['date'];
                                    } else {
                                        $mergedItineraries[] = $currentGroup;
                                        $currentGroup = $item['itinerary'];
                                        $currentGroup['start_date'] = $item['date'];
                                        $currentGroup['end_date'] = $item['date'];
                                        $currentGroup['start_minutes'] = $item['itinerary']['start_minutes'];
                                        $currentGroup['end_minutes'] = $item['itinerary']['end_minutes'];
                                        $currentGroup['dates'] = [$item['date']];
                                    }
                                }
                            }
                            if ($currentGroup !== null) {
                                $mergedItineraries[] = $currentGroup;
                            }
                        }
                        
                        usort($mergedItineraries, function($a, $b) {
                            return $a['start_minutes'] - $b['start_minutes'];
                        });
                    @endphp
                    <tr style="background-color: {{ $rowBgColor }};">
                        <td class="align-top" style="position: sticky; left: 0; background-color: {{ $rowBgColor }}; z-index: 5;">
                            @if($driverData['is_first_in_group'])
                                <span style="display: inline-block; background-color: #6c757d; color: white; font-size: 0.6rem; padding: 2px 8px; border-radius: 12px; margin-bottom: 4px;">
                                    {{ $driverData['group_name'] }}
                                </span>
                                <br>
                            @endif
                            <strong>{{ $driver->name }}</strong>
                            <br><small>{{ $driver->branch->branch_name ?? '' }}</small>
                        </div>
                    @foreach($dates as $dateIndex => $dateInfo)
                        @php
                            $dateStr = $dateInfo['date']->format('Y-m-d');
                            $displayItems = [];
                            $dayItineraryCount = 0;
                            if (isset($schedule[$dateStr])) {
                                $dayItineraryCount = count($schedule[$dateStr]);
                            }
                            
                            $attendanceKey = $driver->id . '_' . $dateStr;
                            $attendanceGroup = $attendanceGroups[$attendanceKey] ?? null;
                            
                            $showAttendanceBlock = false;
                            $attendanceStartPercent = null;
                            $attendanceWidthPercent = null;
                            $attendanceCategoryName = null;
                            $attendanceColor = null;
                            
                            if ($attendanceGroup && $attendanceGroup['category']) {
                                $attendanceCategory = $attendanceGroup['category'];
                                $attendanceCategoryName = $attendanceCategory->attendance_name;
                                $attendanceColor = $attendanceCategory->color_code;
                                
                                $groupDates = [];
                                $tempDate = Carbon::parse($attendanceGroup['start_date']);
                                $tempEnd = Carbon::parse($attendanceGroup['end_date']);
                                while ($tempDate <= $tempEnd) {
                                    $groupDates[] = $tempDate->format('Y-m-d');
                                    $tempDate->addDay();
                                }
                                
                                $anyDayLongRest = false;
                                $allDaysLongRest = true;
                                foreach ($groupDates as $gd) {
                                    $gdKey = $driver->id . '_' . $gd;
                                    $gdAttendance = $attendances[$gdKey] ?? null;
                                    if ($gdAttendance && $gdAttendance->category) {
                                        $startM = Carbon::parse($gdAttendance->start_time)->hour * 60 + Carbon::parse($gdAttendance->start_time)->minute;
                                        $endM = Carbon::parse($gdAttendance->end_time)->hour * 60 + Carbon::parse($gdAttendance->end_time)->minute;
                                        $duration = $endM - $startM;
                                        if ($duration >= 480) {
                                            $anyDayLongRest = true;
                                        } else {
                                            $allDaysLongRest = false;
                                        }
                                    } else {
                                        $allDaysLongRest = false;
                                    }
                                }
                                
                                $showAttendanceBlock = true;
                                
                                $currentAttendance = $attendances[$attendanceKey] ?? null;
                                $currentStartM = 0;
                                $currentEndM = 1440;
                                $currentDuration = 0;
                                
                                if ($currentAttendance && $currentAttendance->category) {
                                    $currentStartM = Carbon::parse($currentAttendance->start_time)->hour * 60 + Carbon::parse($currentAttendance->start_time)->minute;
                                    $currentEndM = Carbon::parse($currentAttendance->end_time)->hour * 60 + Carbon::parse($currentAttendance->end_time)->minute;
                                    $currentDuration = $currentEndM - $currentStartM;
                                }
                                
                                if ($allDaysLongRest && count($groupDates) > 1) {
                                    if ($dateStr == $attendanceGroup['start_date']) {
                                        $attendanceStartPercent = 0;
                                        $attendanceWidthPercent = count($groupDates) * 100;
                                    } else {
                                        $showAttendanceBlock = false;
                                    }
                                } elseif ($currentDuration >= 480) {
                                    $attendanceStartPercent = 0;
                                    $attendanceWidthPercent = 100;
                                } else {
                                    $attendanceStartPercent = ($currentStartM / 1440) * 100;
                                    $attendanceWidthPercent = ($currentDuration / 1440) * 100;
                                }
                            }
                            
                            foreach ($mergedItineraries as $idx => $itinerary) {
                                if (in_array($dateStr, $itinerary['dates'])) {
                                    if ($dateStr == $itinerary['start_date']) {
                                        $startPercent = ($itinerary['start_minutes'] / 1440) * 100;
                                        
                                        $startDateObj = Carbon::parse($itinerary['start_date']);
                                        $endDateObj = Carbon::parse($itinerary['end_date']);
                                        $daysDiff = $startDateObj->diffInDays($endDateObj);
                                        
                                        $endPercent = ($itinerary['end_minutes'] / 1440) * 100;
                                        
                                        if ($daysDiff == 0) {
                                            $spanWidth = $endPercent - $startPercent;
                                        } else {
                                            $firstDayWidth = 100 - $startPercent;
                                            $middleDaysWidth = ($daysDiff - 1) * 100;
                                            $spanWidth = $firstDayWidth + $middleDaysWidth + $endPercent;
                                        }
                                        
                                        $itemData = $itinerary;
                                        $itemData['span_width'] = $spanWidth;
                                        $itemData['start_percent'] = $startPercent;
                                        $itemData['z_index'] = 100 + $idx;
                                        $displayItems[] = $itemData;
                                    }
                                }
                            }
                        @endphp
                        <td class="position-relative p-0" style="background-color: {{ $rowBgColor }}; cursor: pointer;" 
                            onclick="openDriverAttendanceModal({{ $driver->id }}, '{{ $dateStr }}', '{{ addslashes($driver->name) }}')">
                            <div class="timeline-cell" style="background-color: {{ $rowBgColor }}; position: relative;">
                                @if($dayItineraryCount > 1)
                                    <div class="itinerary-count-badge" title="{{ $dayItineraryCount }}件の運行があります">
                                        {{ $dayItineraryCount }}
                                    </div>
                                @endif
                                
                                @if($showAttendanceBlock && $attendanceCategoryName)
                                    <div class="attendance-block" 
                                         style="position: absolute; top: 0; left: {{ $attendanceStartPercent }}%; width: {{ $attendanceWidthPercent }}%; height: 100%; background-color: {{ $attendanceColor }}; z-index: 200; cursor: pointer;"
                                         onclick="event.stopPropagation(); openDriverAttendanceModal({{ $driver->id }}, '{{ $dateStr }}', '{{ addslashes($driver->name) }}')">
                                        <div class="attendance-content" style="padding: 4px; font-size: 0.65rem; font-weight: bold; white-space: nowrap; text-overflow: ellipsis; text-align: center; height: 100%; display: flex; align-items: center; justify-content: center;">
                                            {{ $attendanceCategoryName }}
                                        </div>
                                    </div>
                                @endif
                                
                                @foreach($displayItems as $idx => $itinerary)
                                    @php
                                        $backgroundColor = request('color_type') == 'category' 
                                            ? ($itinerary['category_color'] ?? 'transparent')
                                            : ($itinerary['status_color'] ?? 'transparent');
                                    @endphp
                                    <div class="timeline-event" 
                                         style="left: {{ $itinerary['start_percent'] }}%; width: {{ $itinerary['span_width'] }}%; z-index: {{ $itinerary['z_index'] }}; background-color: {{ $backgroundColor }};" 
                                         onclick="event.stopPropagation(); openBusAssignmentEdit({{ $itinerary['bus_assignment_id'] }})">
                                        <div class="event-content">
                                            <div>
                                                {{ $itinerary['group_info_id'] }} [{{ $itinerary['bus_assignment_id'] }}]
                                            </div>
                                            <div>
                                                @if($itinerary['vehicle_type_spec_check'])
                                                    <span style="color: #f59e0b; cursor: help;" title="車種指定">⭐</span>
                                                @endif
                                                @if($itinerary['guide_name'])
                                                    <span style="color: #10b981; cursor: help;" title="添乗員: {{ $itinerary['guide_name'] }}">👤</span>
                                                @endif
                                                @if($itinerary['status_finalized'])
                                                    <span style="color: #22c55e; cursor: help; font-weight: bold;" title="最終確認済み">✓</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span title="団体名: {{ $itinerary['group_name'] }}">{{ $itinerary['group_name'] }}</span>
                                            </div>
                                            <div>
                                                <span title="車両: {{ $itinerary['vehicle_name'] }}">
                                                    {{ $itinerary['vehicle_name'] }}
                                                </span>
                                            </div>
                                            <div>
                                                @if($itinerary['is_temporary_driver'])
                                                    <span style="color: #f59e0b; cursor: help;" title="仮運転手">(仮)</span>
                                                @endif
                                                @if($itinerary['driver_name'] && $itinerary['driver_name'] != '未割当')
                                                    <span title="運転手名: {{ $itinerary['driver_name'] }}{{ $itinerary['driver_name_kana'] ? ' (' . $itinerary['driver_name_kana'] . ')' : '' }}">
                                                        {{ $itinerary['driver_name'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($itinerary['remarks'])
                                                <div style="font-size: 0.6rem; color: #666; white-space: normal; cursor: help;" title="備考: {{ $itinerary['remarks'] }}">
                                                    {{ Str::limit($itinerary['remarks'], 50) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if(count($displayItems) == 0 && !$showAttendanceBlock)
                                    <div></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    </div>
                @endforeach
            </tbody>
            <thead>
                    <th class="text-center" style="position: sticky; left: 0; background-color: #f8f9fa; z-index: 10; min-width: 180px;"></th>
                    @foreach($dates as $date)
                        @php
                            $dateStr = $date['date']->format('Y-m-d');
                            $dateRemark = $dateRemarks[$dateStr] ?? null;
                            $dateColor = '';
                            if ($date['is_saturday']) {
                                $dateColor = 'color: #0066cc;';
                            } elseif ($date['is_sunday'] || $date['is_holiday']) {
                                $dateColor = 'color: #ff0000;';
                            }
                        @endphp
                        <th class="text-center" style="background-color: #e9ecef; min-width: 100px; vertical-align: top;">
                            <div style="padding: 4px;">
                                <div style="{{ $dateColor }}">{{ $date['display'] }}</div>
                            </div>
                        </th>
                    @endforeach
                </thead>
        </table>
    </div>
</div>

<div id="iframeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; overflow: auto;">
    <div style="position: relative; width: 100%; min-height: 100%; display: flex; justify-content: center; align-items: center; padding: 20px;">
        <div id="modalContent" style="background-color: #f3f4f6; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); width: 90%; max-width: 550px; overflow: hidden; transition: all 0.3s ease;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 16px; color: #fff; font-size: 14px; font-weight: 500; background-color: #374151;">
                <span id="modalTitle">新規グループ作成</span>
                <button onclick="closeIframeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #fff;">&times;</button>
            </div>
            <iframe id="modalIframe" src="" style="width: 100%; height: 480px; border: none; display: block; transition: height 0.3s ease;"></iframe>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ledger-table th,
.ledger-table td {
    border: 1px solid #dee2e6;
    vertical-align: top;
    position: relative;
    overflow: visible;
}

.ledger-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #e9ecef;
}

.timeline-cell {
    position: relative;
    height: 68px;
    width: 100%;
    overflow: visible;
}

.timeline-event {
    position: absolute;
    top: 0;
    height: 68px;
    border-left: 1px dashed #666;
    border-right: 1px dashed #666;
    overflow: visible;
    cursor: pointer;
    pointer-events: auto;
    min-width: 0;
}

.timeline-event:hover {
    border: 1px solid #ff0000;
    z-index: 1000 !important;
}

.event-content {
    position: relative;
    padding: 0;
    font-size: 0.7rem;
    line-height: 1.3;
    z-index: 101;
    color: #000;
    height: 66px;
    white-space: nowrap;
    background-color: inherit;
}

.attendance-block {
    position: absolute;
    top: 0;
    cursor: pointer;
}

.attendance-block:hover {
    opacity: 0.9;
}

.attendance-content {
    color: #fff;
    text-shadow: 0 0 3px rgba(0, 0, 0, 0.9);
    font-weight: 500;
}

.datepicker-3months {
    border-color: #E5E7EB;
    border-radius: 4px;
    font-size: 0.8rem;
}

.table-responsive {
    max-height: none;
    overflow-x: auto;
    overflow-y: visible;
}

.table-responsive .table {
    overflow: visible;
}

.position-relative {
    overflow: visible !important;
}

.ledger-table td {
    line-height: 1.2;
}

.table-responsive::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.itinerary-count-badge {
    position: absolute;
    top: 2px;
    left: 2px;
    background-color: #ef4444;
    color: white;
    font-size: 0.6rem;
    font-weight: bold;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    z-index: 200;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    cursor: pointer;
    pointer-events: auto;
}

.itinerary-count-badge:hover {
    background-color: #dc2626;
    transform: scale(1.05);
}

.timeline-event[data-text-color="white"] .event-content,
.timeline-event[data-text-color="white"] .event-content * {
    color: #ffffff !important;
}

.date-header-cell {
    cursor: pointer;
}

.date-remark {
    font-size: 0.65rem;
    color: #cc0000;
    padding: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
}

.holiday-name {
    color: #ff0000;
    font-size: 0.6rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
    font-weight: normal;
}

.branch-dropdown .dropdown-toggle {
    min-width: 140px;
    text-align: left;
    background-color: #fff !important;
    border-color: #ced4da !important;
}

.branch-dropdown .dropdown-toggle:after {
    float: right;
    margin-top: 8px;
}

.branch-dropdown .dropdown-menu {
    min-width: 220px;
}

.branch-dropdown .dropdown-item {
    cursor: pointer;
}

.btn-outline-secondary {
    color: #212529 !important;
}

.selected-count {
    background-color: #0d6efd;
    color: white;
    border-radius: 10px;
    padding: 0 6px;
    font-size: 0.7rem;
    margin-left: 8px;
}

.btn-group .btn-outline-secondary {
    background-color: #fff;
    border-color: #ced4da;
    color: #212529;
}

.btn-group .btn-outline-secondary:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
    color: #212529;
}

.btn-group .btn-check:checked + .btn-outline-secondary {
    background-color: #cfe2ff !important;
    color: #212529;
    font-weight: 500 !important;
}

.btn-group .btn-check:checked + .btn-outline-secondary:hover {
    background-color: #b6d4fe !important;
    color: #212529;
}




.flatpickr-calendar {
    border: 1px solid #ddd !important;
    border-radius: 6px !important;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12) !important;
    font-family: inherit !important;
    font-size: 11px !important;
    overflow: hidden !important;
}

.flatpickr-calendar.multiMonth {
    width: 516px !important;
    max-width: 95vw !important;
}

.flatpickr-calendar.multiMonth .flatpickr-innerContainer {
    width: 100% !important;
}

.flatpickr-calendar.multiMonth .flatpickr-months {
    display: flex !important;
}

.flatpickr-calendar.multiMonth .flatpickr-month {
    flex: 1 !important;
}

.flatpickr-calendar.multiMonth .flatpickr-month:not(:last-child) {
    border-right: 1px solid #e9ecef !important;
}

.flatpickr-months {
    background: linear-gradient(135deg, #1f3241 0%, #2d4a5e 100%) !important;
    border-radius: 6px 6px 0 0 !important;
    display: flex !important;
}

.flatpickr-month {
    height: 28px !important;
    padding-right: 0 !important;
}

.flatpickr-current-month {
    padding: 3px 0 0 0 !important;
}

.flatpickr-current-month .flatpickr-monthDropdown-months {
    font-weight: 600 !important;
    color: #fff !important;
    font-size: 11px !important;
}

.flatpickr-current-month .numInputWrapper span {
    color: #fff !important;
}

.flatpickr-current-month input.cur-year {
    color: #fff !important;
    font-weight: 600 !important;
    font-size: 11px !important;
}

.flatpickr-months .flatpickr-month,
.flatpickr-months .flatpickr-next-month,
.flatpickr-months .flatpickr-prev-month {
    color: #fff !important;
    fill: #fff !important;
}

.flatpickr-months .flatpickr-next-month:hover svg,
.flatpickr-months .flatpickr-prev-month:hover svg {
    fill: #ffc107 !important;
}

.flatpickr-months .flatpickr-next-month,
.flatpickr-months .flatpickr-prev-month {
    width: 20px !important;
    height: 20px !important;
    padding: 2px !important;
}

.flatpickr-weekdays {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    margin: 0 !important;
}

.flatpickr-weekday {
    color: #495057 !important;
    font-weight: 600 !important;
    font-size: 10px !important;
    padding: 1px 0 !important;
}

.flatpickr-days {
    border: none !important;
    padding: 0 !important;
}

.flatpickr-day {
    color: #374151 !important;
    border-radius: 2px !important;
    margin: 0 !important;
    border: 1px solid transparent !important;
    max-width: 24px !important;
    width: 24px !important;
    height: 22px !important;
    line-height: 20px !important;
    font-size: 10px !important;
}

.flatpickr-day:hover {
    background: #e0f2fe !important;
    border-color: #2563eb !important;
    color: #2563eb !important;
}

.flatpickr-day.selected {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #fff !important;
    font-weight: 600 !important;
}

.flatpickr-day.selected:hover {
    background: #1d4ed8 !important;
}

.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #fff !important;
}

.flatpickr-day.inRange {
    background: #dbeafe !important;
    border-color: transparent !important;
    color: #1e40af !important;
}

.flatpickr-day.today {
    border-color: #ffc107 !important;
    background: #fffbeb !important;
    color: #374151 !important;
}

.flatpickr-day.today:hover {
    background: #fef3c7 !important;
    border-color: #f59e0b !important;
    color: #374151 !important;
}

.flatpickr-months .flatpickr-month {
    background: transparent !important;
}

span.flatpickr-weekday {
    background: #f8f9fa !important;
}

.flatpickr-calendar.showTimeInput.hasTime .flatpickr-time {
    border-top: 1px solid #e9ecef !important;
}

.flatpickr-calendar.multiMonth .dayContainer {
    width: 168px !important;
    min-width: 168px !important;
    max-width: 168px !important;
    position: relative !important;
}

.month-wrapper {
    flex: 1 !important;
    position: relative !important;
    padding: 2px !important;
    height: 135px !important;
}

.month-wrapper:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 1px;
    background-color: #e9ecef;
}

.flatpickr-calendar.multiMonth .flatpickr-days {
    display: flex !important;
    position: relative;
    width: 514px !important;
}

.flatpickr-calendar.multiMonth .flatpickr-days .dayContainer {
    padding: 0 !important;
}

.flatpickr-calendar.multiMonth .flatpickr-rContainer {
    width: 514px !important;
}





@media (max-width: 768px) {
    .container-fluid {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    .bg-light.p-2 {
        padding: 10px !important;
    }
    
    .bg-light .d-flex.flex-wrap {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;
    }
    
    .bg-light .d-flex.flex-wrap > .d-flex {
        margin: 0 !important;
        min-width: 0 !important;
    }
    
    .d-flex.align-items-center {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        flex-wrap: nowrap !important;
    }
    
    .d-flex.align-items-center span:first-child {
        font-size: 0.7rem !important;
        font-weight: 500 !important;
        min-width: 50px !important;
        flex-shrink: 0 !important;
        color: #374151 !important;
    }
    
    .d-flex.align-items-center .form-control-sm,
    .d-flex.align-items-center .form-select-sm {
        flex: 1 !important;
        min-width: 0 !important;
        width: auto !important;
        font-size: 0.7rem !important;
        padding: 5px 6px !important;
        height: auto !important;
    }
    
    .branch-dropdown,
    .status-dropdown {
        width: 100% !important;
        min-width: 0 !important;
    }
    
    .branch-dropdown .dropdown-toggle,
    .status-dropdown .dropdown-toggle {
        width: 100% !important;
        min-width: 0 !important;
        font-size: 0.7rem !important;
        padding: 5px 6px !important;
    }
    
    .dropdown-menu {
        max-width: 90vw !important;
    }
    
    .dropdown-item {
        white-space: normal !important;
        word-break: break-word !important;
        font-size: 0.7rem !important;
        padding: 6px 10px !important;
    }
    
    .btn-group.btn-group-sm {
        display: flex !important;
        gap: 4px !important;
    }
    
    .btn-group.btn-group-sm .btn {
        padding: 4px 6px !important;
        font-size: 0.65rem !important;
    }
    
    .form-check {
        display: flex !important;
        align-items: center !important;
        gap: 5px !important;
        white-space: nowrap !important;
    }
    
    .form-check-label {
        font-size: 0.7rem !important;
        white-space: nowrap !important;
    }
    
    .d-flex.gap-1 {
        grid-column: span 2 !important;
        justify-content: flex-end !important;
        margin-top: 5px !important;
        gap: 8px !important;
    }
    
    .d-flex.gap-1 .btn-sm,
    .d-flex.gap-1 a {
        padding: 5px 14px !important;
        font-size: 0.7rem !important;
    }
    
    .selected-count {
        font-size: 0.55rem !important;
        padding: 0 3px !important;
        min-width: 14px !important;
    }
    
    .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    
    .ledger-table {
        font-size: 0.7rem !important;
        min-width: auto !important;
    }
    
    .ledger-table th:first-child,
    .ledger-table td:first-child {
        min-width: 110px !important;
        max-width: 130px !important;
    }
    
    .ledger-table th:not(:first-child),
    .ledger-table td:not(:first-child) {
        min-width: 70px !important;
    }
    
    .ledger-table th:first-child,
    .ledger-table td:first-child {
        position: sticky !important;
        left: 0 !important;
        z-index: 101 !important;
    }
    
    .ledger-table th:first-child {
        background-color: #e9ecef !important;
    }
    
    .ledger-table tbody tr:nth-child(even) td:first-child {
        background-color: #f8f9fa !important;
    }
    
    .ledger-table tbody tr:nth-child(odd) td:first-child {
        background-color: #fff !important;
    }
    
    .timeline-event {
        height: 45px !important;
    }
    
    .event-content {
        font-size: 0.5rem !important;
        line-height: 1.2 !important;
        white-space: normal !important;
        overflow: hidden !important;
        padding: 1px !important;
    }
    
    .event-content div {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        font-size: 0.45rem !important;
    }
    
    .timeline-cell {
        height: 45px !important;
    }
    
    .itinerary-count-badge {
        font-size: 0.4rem !important;
        min-width: 12px !important;
        height: 12px !important;
        border-radius: 6px !important;
        top: 1px !important;
        left: 1px !important;
    }
    
    .date-header-cell {
        font-size: 0.6rem !important;
        padding: 2px !important;
    }
    
    .holiday-name,
    .date-remark,
    .stop-order-badge {
        font-size: 0.4rem !important;
        white-space: normal !important;
        word-break: break-word !important;
    }
    
    #iframeModal #modalContent {
        width: 95% !important;
        max-width: 95% !important;
    }
    
    #modalIframe {
        height: 70vh !important;
    }
}

@media (max-width: 480px) {
    .bg-light .d-flex.flex-wrap {
        gap: 8px !important;
    }
    
    .d-flex.align-items-center span:first-child {
        font-size: 0.65rem !important;
        min-width: 55px !important;
    }
    
    .d-flex.align-items-center .form-control-sm,
    .d-flex.align-items-center .form-select-sm {
        font-size: 0.65rem !important;
        padding: 5px 6px !important;
    }
    
    .ledger-table th:first-child,
    .ledger-table td:first-child {
        min-width: 100px !important;
    }
    
    .ledger-table th:not(:first-child),
    .ledger-table td:not(:first-child) {
        min-width: 60px !important;
    }
    
    .event-content div:nth-child(3) {
        display: none !important;
    }
    
    .event-content div:nth-child(4) span:not(:first-child) {
        display: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function getCurrentDisplayDays() {
    let displayDays = document.getElementById('display_days')?.value;
    if (displayDays) {
        return parseInt(displayDays);
    }
    return 7;
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function submitWithEndDate() {
    const startDateInput = document.getElementById('start_date');
    if (!startDateInput.value) return;
    
    const periodSelect = document.getElementById('period_select');
    const periodValue = periodSelect ? parseInt(periodSelect.value) : 1;
    
    let endDate = new Date(startDateInput.value);
    if (periodValue === 1) {
        endDate.setDate(endDate.getDate() + 6);
    } else if (periodValue === 2) {
        endDate.setDate(endDate.getDate() + 13);
    } else if (periodValue === 3) {
        endDate.setDate(endDate.getDate() + 20);
    } else if (periodValue === 4) {
        endDate.setMonth(endDate.getMonth() + 1);
        endDate.setDate(endDate.getDate() - 1);
    } else {
        endDate.setDate(endDate.getDate() + 6);
    }
    
    const displayDays = Math.round((endDate - new Date(startDateInput.value)) / (1000 * 60 * 60 * 24)) + 1;
    const displayDaysInput = document.getElementById('display_days');
    if (displayDaysInput) {
        displayDaysInput.value = displayDays;
    }
    
    document.getElementById('searchForm').submit();
}

function moveDate(unit, direction) {
    const startDateInput = document.getElementById('start_date');
    const periodSelect = document.getElementById('period_select');
    const displayDaysInput = document.getElementById('display_days');
    
    let currentStart = startDateInput.value ? new Date(startDateInput.value) : new Date();
    let newStart = new Date(currentStart);
    
    if (unit === 'week') {
        newStart.setDate(currentStart.getDate() + (7 * direction));
    } else if (unit === 'month') {
        newStart.setMonth(currentStart.getMonth() + direction);
        if (newStart.getDate() !== currentStart.getDate()) {
            newStart.setDate(0);
        }
    }
    
    startDateInput.value = formatDate(newStart);
    
    const periodValue = periodSelect ? parseInt(periodSelect.value) : 1;
    let newEnd = new Date(newStart);
    if (periodValue === 1) {
        newEnd.setDate(newStart.getDate() + 6);
    } else if (periodValue === 2) {
        newEnd.setDate(newStart.getDate() + 13);
    } else if (periodValue === 3) {
        newEnd.setDate(newStart.getDate() + 20);
    } else if (periodValue === 4) {
        newEnd.setMonth(newStart.getMonth() + 1);
        newEnd.setDate(newEnd.getDate() - 1);
    } else {
        newEnd.setDate(newStart.getDate() + 6);
    }
    
    const newDisplayDays = Math.round((newEnd - newStart) / (1000 * 60 * 60 * 24)) + 1;
    if (displayDaysInput) {
        displayDaysInput.value = newDisplayDays;
    }
    
    document.getElementById('searchForm').submit();
}

function setToday() {
    const today = new Date();
    const startDateInput = document.getElementById('start_date');
    const periodSelect = document.getElementById('period_select');
    const displayDaysInput = document.getElementById('display_days');
    
    let period = periodSelect ? parseInt(periodSelect.value) : 1;
    
    startDateInput.value = formatDate(today);
    
    let endDate = new Date(today);
    if (period === 1) {
        endDate.setDate(today.getDate() + 6);
    } else if (period === 2) {
        endDate.setDate(today.getDate() + 13);
    } else if (period === 3) {
        endDate.setDate(today.getDate() + 20);
    } else if (period === 4) {
        endDate.setMonth(today.getMonth() + 1);
        endDate.setDate(endDate.getDate() - 1);
    } else {
        endDate.setDate(today.getDate() + 6);
    }
    
    const actualDays = Math.round((endDate - today) / (1000 * 60 * 60 * 24)) + 1;
    if (displayDaysInput) {
        displayDaysInput.value = actualDays;
    }
    
    document.getElementById('searchForm').submit();
}

function submitPeriod() {
    const periodSelect = document.getElementById('period_select');
    const startDateInput = document.getElementById('start_date');
    const displayDaysInput = document.getElementById('display_days');
    
    const period = parseInt(periodSelect.value);
    let startDate = startDateInput.value ? new Date(startDateInput.value) : new Date();
    
    let endDate = new Date(startDate);
    if (period === 1) {
        endDate.setDate(startDate.getDate() + 6);
    } else if (period === 2) {
        endDate.setDate(startDate.getDate() + 13);
    } else if (period === 3) {
        endDate.setDate(startDate.getDate() + 20);
    } else if (period === 4) {
        endDate.setMonth(startDate.getMonth() + 1);
        endDate.setDate(endDate.getDate() - 1);
    } else {
        endDate.setDate(startDate.getDate() + 6);
    }
    
    const actualDays = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
    if (displayDaysInput) {
        displayDaysInput.value = actualDays;
    }
    
    document.getElementById('searchForm').submit();
}

function initBranchSelect() {
    const checkboxes = document.querySelectorAll('.branch-checkbox');
    const selectAllCheckbox = document.getElementById('branchSelectAll');
    const branchSelectedText = document.getElementById('branchSelectedText');
    const branchSelectedCount = document.getElementById('branchSelectedCount');
    const searchForm = document.getElementById('searchForm');
    
    function updateBranchDisplay() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        const count = selected.length;
        
        if (count === 0) {
            branchSelectedText.textContent = '営業所';
            branchSelectedCount.style.display = 'none';
        } else {
            branchSelectedText.textContent = '営業所';
            branchSelectedCount.textContent = count;
            branchSelectedCount.style.display = 'inline-block';
        }
        
        if (selectAllCheckbox) {
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        }
        
        document.querySelectorAll('.branch-hidden-input').forEach(input => input.remove());
        
        selected.forEach(cb => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'branch_ids[]';
            hiddenInput.value = cb.value;
            hiddenInput.className = 'branch-hidden-input';
            searchForm.appendChild(hiddenInput);
        });
    }
    
    function toggleCheckbox(checkbox) {
        checkbox.checked = !checkbox.checked;
        updateBranchDisplay();
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.removeEventListener('change', checkbox._changeHandler);
        const changeHandler = function(e) {
            e.stopPropagation();
            updateBranchDisplay();
        };
        checkbox._changeHandler = changeHandler;
        checkbox.addEventListener('change', changeHandler);
        
        checkbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    if (selectAllCheckbox) {
        selectAllCheckbox.removeEventListener('change', selectAllCheckbox._changeHandler);
        const selectAllHandler = function(e) {
            e.stopPropagation();
            const isChecked = this.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateBranchDisplay();
        };
        selectAllCheckbox._changeHandler = selectAllHandler;
        selectAllCheckbox.addEventListener('change', selectAllHandler);
        
        selectAllCheckbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    document.querySelectorAll('.branch-dropdown .dropdown-item').forEach(item => {
        item.removeEventListener('click', item._clickHandler);
        
        const clickHandler = function(e) {
            if (e.target.type === 'checkbox') {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            const checkbox = this.querySelector('input[type="checkbox"]');
            if (checkbox) {
                toggleCheckbox(checkbox);
            }
        };
        item._clickHandler = clickHandler;
        item.addEventListener('click', clickHandler);
    });
    
    updateBranchDisplay();
}

function initColorTypeButtons() {
    const statusRadio = document.getElementById('color_type_status');
    const categoryRadio = document.getElementById('color_type_category');
    const searchForm = document.getElementById('searchForm');
    
    if (statusRadio) {
        statusRadio.addEventListener('change', function() {
            if (this.checked) searchForm.submit();
        });
    }
    if (categoryRadio) {
        categoryRadio.addEventListener('change', function() {
            if (this.checked) searchForm.submit();
        });
    }
}

function getContrastColor(bgColor) {
    let r, g, b;
    
    if (bgColor.startsWith('rgb')) {
        const match = bgColor.match(/[\d\.]+/g);
        if (match && match.length >= 3) {
            r = parseFloat(match[0]);
            g = parseFloat(match[1]);
            b = parseFloat(match[2]);
        }
    } else if (bgColor.startsWith('#')) {
        let hex = bgColor.slice(1);
        if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
        r = parseInt(hex.slice(0, 2), 16);
        g = parseInt(hex.slice(2, 4), 16);
        b = parseInt(hex.slice(4, 6), 16);
    }
    
    if (r === undefined) return '#000000';
    const brightness = (r * 0.299 + g * 0.587 + b * 0.114);
    return brightness > 128 ? '#000000' : '#ffffff';
}

function adjustTextColorByBackground() {
    document.querySelectorAll('.timeline-event').forEach(event => {
        const bgColor = window.getComputedStyle(event).backgroundColor;
        if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
            const textColor = getContrastColor(bgColor);
            const isWhite = textColor === '#ffffff';
            event.setAttribute('data-text-color', isWhite ? 'white' : 'black');
            const contentDiv = event.querySelector('.event-content');
            if (contentDiv) contentDiv.style.color = textColor;
        }
    });
    
    document.querySelectorAll('.attendance-block').forEach(block => {
        const bgColor = window.getComputedStyle(block).backgroundColor;
        if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
            const textColor = getContrastColor(bgColor);
            const contentDiv = block.querySelector('.attendance-content');
            if (contentDiv) contentDiv.style.color = textColor;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initBranchSelect();
    initColorTypeButtons();
    
    flatpickr('.datepicker-3months', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
        },
        onReady: function(selectedDates, dateStr, instance) {
            const daysContainer = instance.daysContainer;
            if (daysContainer) {
                const dayContainers = daysContainer.querySelectorAll('.dayContainer');
                dayContainers.forEach(function(dayContainer) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'month-wrapper';
                    dayContainer.parentNode.insertBefore(wrapper, dayContainer);
                    wrapper.appendChild(dayContainer);
                });
            }
        }
    });
    
    const periodSelect = document.getElementById('period_select');
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            submitPeriod();
        });
    }
    
    adjustTextColorByBackground();
    
    const observer = new MutationObserver(function(mutations) {
        let shouldUpdate = false;
        mutations.forEach(mutation => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                shouldUpdate = true;
            }
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                shouldUpdate = true;
            }
        });
        if (shouldUpdate) adjustTextColorByBackground();
    });
    
    const tableContainer = document.querySelector('.table-responsive');
    if (tableContainer) {
        observer.observe(tableContainer, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }
});

function openIframeModal(url, title = '新規グループ作成') {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    
    if (!iframe || !modal) return;
    
    iframe.src = url;
    modalTitle.textContent = title;
    iframe.style.height = '480px';
    if (modalContent) modalContent.style.maxWidth = '550px';
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    iframe.onload = function() {
        try {
            const iframeHeight = iframe.contentWindow.document.body.scrollHeight;
            if (iframeHeight > 100 && iframeHeight < 800) {
                iframe.style.height = (iframeHeight + 40) + 'px';
            }
        } catch(e) {
            console.log('iframeの高さを取得できません');
        }
    };
}

function closeIframeModal() {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    
    if (iframe) iframe.src = '';
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

function openCreateGroup(vehicleId, date, vehicleName, driverId, driverName) {
    let url = '{{ route("masters.group-infos.create") }}' + 
                '?start_date=' + encodeURIComponent(date) +
                '&end_date=' + encodeURIComponent(date);
    
    if (vehicleId && vehicleName) {
        url += '&vehicle_id=' + encodeURIComponent(vehicleId) +
               '&vehicle_name=' + encodeURIComponent(vehicleName);
    }
    
    if (driverId && driverName) {
        url += '&driver_id=' + encodeURIComponent(driverId) +
               '&driver_name=' + encodeURIComponent(driverName);
    }
    
    openIframeModal(url, '新規グループ作成');
}

function openBusAssignmentEdit(busAssignmentId) {
    const url = '/masters/bus-assignments/' + busAssignmentId + '/edit?source=driver-ledger';
    openIframeModal(url, '運行割当編集');
}

function openDateRemarkModal(date) {
    const url = '/masters/group-info-date-remarks/' + encodeURIComponent(date);
    openIframeModal(url, '予定登録');
}

function openDriverAttendanceModal(driverId, date, driverName) {
    const url = '{{ route("masters.driver-attendance.edit") }}' + 
                '?driver_id=' + encodeURIComponent(driverId) +
                '&date=' + encodeURIComponent(date) +
                '&driver_name=' + encodeURIComponent(driverName);
    
    openIframeModal(url, '運転手勤怠');
}

window.addEventListener('message', function(event) {
    if (event.data && event.data.action === 'close-iframe-and-reload') {
        closeIframeModal();
        location.reload();
    }
});
</script>
@endpush