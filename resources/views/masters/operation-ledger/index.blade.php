@extends('layouts.app')

@section('title', '運行台帳')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">運行台帳</h5>
    </div>
    
    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.operation-ledger.index') }}" class="row g-2 align-items-center" id="searchForm">
            <input type="hidden" name="display_days" id="display_days" value="{{ $displayDays ?? 7 }}">
            
            <div class="col-auto">
                <input type="text" name="start_date" value="{{ $startDate }}" 
                       class="form-control form-control-sm datepicker-3months" style="width: 120px;" placeholder="開始日" 
                       id="start_date" onchange="submitWithEndDate()">
            </div>
            <div class="col-auto">
                <input type="text" name="end_date" value="{{ $endDate }}" 
                       class="form-control form-control-sm datepicker-3months" style="width: 120px;" placeholder="終了日" 
                       id="end_date">
            </div>
            
            <div class="col-auto">
                <select name="period" class="form-select form-select-sm" style="width: 100px;" id="period_select">
                    <option value="1" {{ request('period') == 1 ? 'selected' : '' }}>1週間</option>
                    <option value="2" {{ request('period') == 2 ? 'selected' : '' }}>2週間</option>
                    <option value="3" {{ request('period') == 3 ? 'selected' : '' }}>3週間</option>
                    <option value="4" {{ request('period') == 4 ? 'selected' : '' }}>1ヶ月</option>
                </select>
            </div>
            
            <div class="col-auto">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" onclick="moveDate('month', -1)">&lt;&lt;</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="moveDate('week', -1)">&lt;</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setToday()">今日</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="moveDate('week', 1)">&gt;</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="moveDate('month', 1)">&gt;&gt;</button>
                </div>
            </div>
            
            <div class="col-auto">
                <select name="vehicle_type_id" class="form-select form-select-sm">
                    <option value="">車種：全て</option>
                    @foreach($vehicleTypes ?? [] as $type)
                        <option value="{{ $type->id }}" {{ request('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->type_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-auto">
                <input type="text" name="reservation_id" value="{{ request('reservation_id') }}" 
                       class="form-control form-control-sm" style="width: 100px;" placeholder="予約ID">
            </div>
            
            <div class="col-auto">
                <input type="text" name="group_name" value="{{ request('group_name') }}" 
                       class="form-control form-control-sm" style="width: 120px;" placeholder="団体名">
            </div>
            
            <div class="col-auto branch-dropdown">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 140px; text-align: left; background-color: #fff; border-color: #ced4da;">
                        <span id="branchSelectedText">営業所</span>
                        <span id="branchSelectedCount" class="selected-count" style="display: none;">0</span>
                    </button>
                    <div class="dropdown-menu p-0" style="min-width: 220px;">
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
            
            <div class="col-auto">
                <select name="reservation_status" class="form-select form-select-sm">
                    <option value="">予約状態：全て</option>
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
            
            <div class="col-auto">
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="color_type" id="color_type_status" value="status" autocomplete="off" 
                           {{ (request('color_type') == 'status' || !request()->has('color_type')) ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="btn btn-outline-secondary" for="color_type_status" style="background-color: #fff; border-color: #ced4da;">
                        <span style="display:inline-block; width:12px; height:12px; background-color:#ccf5ff; border:1px solid #999; margin-right:4px;"></span>
                        予約状態
                    </label>
                    
                    <input type="radio" class="btn-check" name="color_type" id="color_type_category" value="category" autocomplete="off" 
                           {{ request('color_type') == 'category' ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="btn btn-outline-secondary" for="color_type_category" style="background-color: #fff; border-color: #ced4da;">
                        <span style="display:inline-block; width:12px; height:12px; background: linear-gradient(45deg, #ff9999, #99ff99); border:1px solid #999; margin-right:4px;"></span>
                        予約分類
                    </label>
                </div>
            </div>
            
            <div class="col-auto">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="has_guide" name="has_guide" value="1" 
                           {{ request('has_guide') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_guide" style="font-size: 0.8rem;">添乗員あり</label>
                </div>
            </div>
    
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">検索</button>
            </div>
            
            <div class="col-auto">
                <a href="{{ route('masters.operation-ledger.index') }}" class="btn btn-sm btn-outline-secondary">リセット</a>
            </div>
        </form>
    </div>
    
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="table table-bordered table-sm ledger-table" style="font-size: 0.75rem; min-width: 800px;">
            <thead>
                <tr>
                    <th class="text-center" style="position: sticky; left: 0; background-color: #f8f9fa; z-index: 10; min-width: 180px;">車両名 / 代理店</th>
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
                        <th class="text-center date-header-cell" style="background-color: #e9ecef; min-width: 100px; vertical-align: top; cursor: pointer;"  onclick="openDateRemarkModal('{{ $dateStr }}')">
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
                </tr>
            </thead>
            <tbody>
                @foreach($groupedVehicles as $index => $vehicleData)
                    @php
                        $vehicle = $vehicleData['vehicle'];
                        $rowBgColor = $index % 2 == 0 ? '#f8f9fa' : '#ffffff';
                        $schedule = $scheduleData[$vehicle->id]['schedule'] ?? [];
                        
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
                                $currentDate = \Carbon\Carbon::parse($item['date']);
                                
                                if ($currentGroup === null) {
                                    $currentGroup = $item['itinerary'];
                                    $currentGroup['start_date'] = $item['date'];
                                    $currentGroup['end_date'] = $item['date'];
                                    $currentGroup['start_minutes'] = $item['itinerary']['start_minutes'];
                                    $currentGroup['end_minutes'] = $item['itinerary']['end_minutes'];
                                    $currentGroup['dates'] = [$item['date']];
                                } else {
                                    $lastDate = \Carbon\Carbon::parse($currentGroup['end_date']);
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
                            @if($vehicleData['is_first_in_group'])
                                <span style="display: inline-block; background-color: #6c757d; color: white; font-size: 0.6rem; padding: 2px 8px; border-radius: 12px; margin-bottom: 4px;">
                                    {{ $vehicleData['group_name'] }}
                                </span>
                                <br>
                            @endif
                            <strong>{{ $vehicle->registration_number }}</strong>
                            @if($vehicle->vehicleModel)
                                <small class="text-muted">{{ $vehicle->vehicleModel->model_name }}</small>
                            @endif
                            <br><small>{{ $vehicle->branch->branch_name ?? '' }}</small>
                        </td>
                    @foreach($dates as $dateIndex => $dateInfo)
                        @php
                            $dateStr = $dateInfo['date']->format('Y-m-d');
                            $displayItems = [];
                            $itemIndex = 0;
                            
                            $dayItineraryCount = 0;
                            if (isset($schedule[$dateStr])) {
                                $dayItineraryCount = count($schedule[$dateStr]);
                            }
                            
                            foreach ($mergedItineraries as $idx => $itinerary) {
                                if (in_array($dateStr, $itinerary['dates'])) {
                                    if ($dateStr == $itinerary['start_date']) {
                                        $startPercent = ($itinerary['start_minutes'] / 1440) * 100;
                                        
                                        $startDateObj = \Carbon\Carbon::parse($itinerary['start_date']);
                                        $endDateObj = \Carbon\Carbon::parse($itinerary['end_date']);
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
                            onclick="openCreateGroup({{ $vehicle->id }}, '{{ $dateStr }}', '{{ $vehicle->registration_number }}')">
                            <div class="timeline-cell" style="background-color: {{ $rowBgColor }}; position: relative;">
                                @if($dayItineraryCount > 1)
                                    <div class="itinerary-count-badge" title="{{ $dayItineraryCount }}件の運行があります">
                                        {{ $dayItineraryCount }}
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
                                                @if($itinerary['status_finalized'])
                                                    <span style="color: #ff0000; cursor: help; font-weight: bold;" title="最終確認済み">✓</span>
                                                @endif
                                                @if($itinerary['vehicle_type_spec_check'])
                                                    <span style="color: #f59e0b; cursor: help;" title="車種指定">⭐</span>
                                                @endif
                                                @if($itinerary['guide_name'])
                                                    <span style="color: #10b981; cursor: help;" title="添乗員: {{ $itinerary['guide_name'] }}">👤</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span title="団体名: {{ $itinerary['group_name'] }}">{{ $itinerary['group_name'] }}</span>
                                            </div>
                                            <div>
                                                @if($itinerary['is_temporary_driver'])
                                                    <span style="color: #f59e0b; cursor: help;" title="仮運転手">(仮)</span>
                                                @endif
                                                @if($itinerary['driver_name'] && $itinerary['driver_name'] != '未割当')
                                                    <span title="運転手名: {{ $itinerary['driver_name'] }}{{ $itinerary['driver_name_kana'] ? ' (' . $itinerary['driver_name_kana'] . ')' : '' }}{{ $itinerary['driver_phone'] ? ' / 電話: ' . $itinerary['driver_phone'] : '' }}">
                                                        {{ $itinerary['driver_name'] }}
                                                        @if($itinerary['driver_name_kana'])
                                                            <span style="font-size: 0.6rem; color: #666;">({{ $itinerary['driver_name_kana'] }})</span>
                                                        @endif
                                                    </span>
                                                    @if($itinerary['driver_phone'])
                                                        <span style="cursor: help;" title="電話番号: {{ $itinerary['driver_phone'] }}">📞</span>
                                                    @endif
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
                                
                                @if(count($displayItems) == 0)
                                    <div></div>
                                @endif
                            </div>
                        </td>
                    @endforeach
                    </tr>
                @endforeach
            </tbody>
            <thead>
                <tr>
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
                </tr>
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
    height: 60px;
    width: 100%;
    overflow: visible;
}

.timeline-event {
    position: absolute;
    top: 0;
    height: 60px;
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
    height: 58px;
    white-space: nowrap;
    background-color: inherit;
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

.holiday-name {
    color: #ff0000;
    font-size: 0.6rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
    font-weight: normal;
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
    
    const startDate = document.getElementById('start_date')?.value;
    const endDate = document.getElementById('end_date')?.value;
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        return Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
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
    const endDateInput = document.getElementById('end_date');
    const displayDaysInput = document.getElementById('display_days');
    
    if (!startDateInput.value) return;
    
    let displayDays = getCurrentDisplayDays();
    
    const newStart = new Date(startDateInput.value);
    const newEnd = new Date(newStart);
    newEnd.setDate(newStart.getDate() + displayDays - 1);
    
    endDateInput.value = formatDate(newEnd);
    
    if (displayDaysInput) {
        displayDaysInput.value = displayDays;
    }
    
    document.getElementById('searchForm').submit();
}

function moveDate(unit, direction) {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const displayDaysInput = document.getElementById('display_days');
    const periodSelect = document.getElementById('period_select');
    
    let currentStart = startDateInput.value ? new Date(startDateInput.value) : new Date();
    let currentEnd = endDateInput.value ? new Date(endDateInput.value) : new Date();
    
    let newStart = new Date(currentStart);
    let newEnd = new Date(currentEnd);
    
    if (unit === 'week') {
        const weekDays = 7 * direction;
        newStart.setDate(currentStart.getDate() + weekDays);
        newEnd.setDate(currentEnd.getDate() + weekDays);
    } else if (unit === 'month') {
        newStart.setMonth(currentStart.getMonth() + direction);
        newEnd.setMonth(currentEnd.getMonth() + direction);
        
        if (newStart.getDate() !== currentStart.getDate()) {
            newStart.setDate(0);
        }
        if (newEnd.getDate() !== currentEnd.getDate()) {
            newEnd.setDate(0);
        }
    }
    
    startDateInput.value = formatDate(newStart);
    endDateInput.value = formatDate(newEnd);
    
    const newDisplayDays = Math.round((newEnd - newStart) / (1000 * 60 * 60 * 24)) + 1;
    if (displayDaysInput) {
        displayDaysInput.value = newDisplayDays;
    }
    
    if (periodSelect) {
        if (newDisplayDays === 7) {
            periodSelect.value = '1';
        } else if (newDisplayDays === 14) {
            periodSelect.value = '2';
        } else if (newDisplayDays === 21) {
            periodSelect.value = '3';
        } else if (newDisplayDays >= 28 && newDisplayDays <= 31) {
            periodSelect.value = '4';
        } else {
            periodSelect.value = '';
        }
    }
    
    document.getElementById('searchForm').submit();
}

function setToday() {
    const today = new Date();
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const displayDaysInput = document.getElementById('display_days');
    const periodSelect = document.getElementById('period_select');
    
    let period = periodSelect ? parseInt(periodSelect.value) : 1;
    let endDate = new Date(today);
    
    if (period === 1) {
        endDate.setDate(today.getDate() + 6);
    } else if (period === 2) {
        endDate.setDate(today.getDate() + 13);
    } else if (period === 3) {
        endDate.setDate(today.getDate() + 20);
    } else if (period === 4) {
        endDate = new Date(today);
        endDate.setMonth(today.getMonth() + 1);
        endDate.setDate(endDate.getDate() - 1);
    } else {
        endDate.setDate(today.getDate() + 6);
    }
    
    startDateInput.value = formatDate(today);
    endDateInput.value = formatDate(endDate);
    
    const actualDays = Math.round((endDate - today) / (1000 * 60 * 60 * 24)) + 1;
    if (displayDaysInput) {
        displayDaysInput.value = actualDays;
    }
    
    document.getElementById('searchForm').submit();
}

function submitPeriod() {
    const periodSelect = document.getElementById('period_select');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const displayDaysInput = document.getElementById('display_days');
    
    const period = parseInt(periodSelect.value);
    const today = new Date();
    let startDate = new Date(today);
    let endDate = new Date(today);
    
    if (period === 1) {
        endDate.setDate(today.getDate() + 6);
    } else if (period === 2) {
        endDate.setDate(today.getDate() + 13);
    } else if (period === 3) {
        endDate.setDate(today.getDate() + 20);
    } else if (period === 4) {
        endDate = new Date(today);
        endDate.setMonth(today.getMonth() + 1);
        endDate.setDate(endDate.getDate() - 1);
    } else {
        endDate.setDate(today.getDate() + 6);
    }
    
    startDateInput.value = formatDate(startDate);
    endDateInput.value = formatDate(endDate);
    
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

function openCreateGroup(vehicleId, date, vehicleName) {
    const url = '{{ route("masters.group-infos.create") }}' + 
                '?vehicle_id=' + encodeURIComponent(vehicleId) +
                '&vehicle_name=' + encodeURIComponent(vehicleName) +
                '&start_date=' + encodeURIComponent(date) +
                '&end_date=' + encodeURIComponent(date);
    
    openIframeModal(url, '新規グループ作成');
}

function openBusAssignmentEdit(busAssignmentId) {
    const url = '/masters/bus-assignments/' + busAssignmentId + '/edit';
    openIframeModal(url, '運行割当編集');
}

function openDateRemarkModal(date) {
    const url = '/masters/group-info-date-remarks/' + encodeURIComponent(date);
    openIframeModal(url, '予定登録');
}

window.addEventListener('message', function(event) {
    if (event.data && event.data.action === 'close-iframe-and-reload') {
        closeIframeModal();
        location.reload();
    }
});
</script>
@endpush