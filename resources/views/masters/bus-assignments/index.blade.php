@extends('layouts.app')

@section('title', '運行一覧')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">運行一覧</h5>

        <div class="d-flex gap-2">
            <button type="button" id="newGroupBtn" class="btn btn-primary btn-sm px-3" 
                    style="background-color: #2563eb; border-color: #2563eb; font-size: 0.875rem;">
                <i class="bi bi-plus-lg"></i> 新規予約
            </button>
        </div>
    </div>

    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.bus-assignments.index') }}" class="row g-1" id="searchForm">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運行日</span>
                        <input type="text" name="start_date" value="{{ request('start_date', \Carbon\Carbon::today()->format('Y-m-d')) }}"
                               class="form-control form-control-sm datepicker-3months" style="width: 120px; border-color: #E5E7EB;" placeholder="YYYY-MM-DD" readonly>
                        <span class="mx-1">~</span>
                        <input type="text" name="end_date" value="{{ request('end_date', \Carbon\Carbon::today()->format('Y-m-d')) }}"
                               class="form-control form-control-sm datepicker-3months" style="width: 120px; border-color: #E5E7EB;" placeholder="YYYY-MM-DD" readonly>
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
                        <select name="branch_id" class="form-select form-select-sm" style="width: 100px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 30px;">車種</span>
                        <select name="vehicle_type_id" class="form-select form-select-sm" style="width: 90px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($vehicleTypes ?? [] as $type)
                                <option value="{{ $type->id }}" {{ request('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">車両名</span>
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

                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">団体名</span>
                        <input type="text" name="group_name" value="{{ request('group_name') }}"
                               class="form-control form-control-sm" style="width: 120px; border-color: #E5E7EB;" placeholder="団体名">
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運転手</span>
                        <select name="driver_id" class="form-select form-select-sm" style="width: 120px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($drivers ?? [] as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} @if($driver->driver_code)({{ $driver->driver_code }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

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
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">予約状態</span>
                        <div class="status-dropdown">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 130px; text-align: left; background-color: #fff; border-color: #ced4da;">
                                    <span id="statusSelectedText">予約状態</span>
                                    <span id="statusSelectedCount" class="selected-count" style="display: none;">0</span>
                                </button>
                                <div class="dropdown-menu p-0" style="min-width: 180px;">
                                    <div class="dropdown-header border-bottom px-3 py-2">
                                        <label class="d-flex align-items-center w-100" style="cursor: pointer;">
                                            <input type="checkbox" id="statusSelectAll" class="me-2"> 
                                            <span>全て選択</span>
                                        </label>
                                    </div>
                                    <div style="max-height: 300px; overflow-y: auto;">
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="予約" class="me-2 status-checkbox"
                                                {{ in_array('予約', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #ccf5ff; border-radius: 4px; padding: 2px 8px; display: inline-block;">予約</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="仮押さえ" class="me-2 status-checkbox"
                                                {{ in_array('仮押さえ', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #ffff99; border-radius: 4px; padding: 2px 8px; display: inline-block;">仮押さえ</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="見積" class="me-2 status-checkbox"
                                                {{ in_array('見積', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #ccffcc; border-radius: 4px; padding: 2px 8px; display: inline-block;">見積</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="危ない" class="me-2 status-checkbox"
                                                {{ in_array('危ない', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #ffcccc; border-radius: 4px; padding: 2px 8px; display: inline-block;">危ない</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="確定待ち" class="me-2 status-checkbox"
                                                {{ in_array('確定待ち', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #ffd9b3; border-radius: 4px; padding: 2px 8px; display: inline-block;">確定待ち</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="確定" class="me-2 status-checkbox"
                                                {{ in_array('確定', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #cbb87c; border-radius: 4px; padding: 2px 8px; display: inline-block;">確定</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="送信済" class="me-2 status-checkbox"
                                                {{ in_array('送信済', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #e6e6fa; border-radius: 4px; padding: 2px 8px; display: inline-block;">送信済</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="実績待ち" class="me-2 status-checkbox"
                                                {{ in_array('実績待ち', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #e0b0ff; border-radius: 4px; padding: 2px 8px; display: inline-block;">実績待ち</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="運行済" class="me-2 status-checkbox"
                                                {{ in_array('運行済', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #c0c0c0; border-radius: 4px; padding: 2px 8px; display: inline-block;">運行済</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="請求済" class="me-2 status-checkbox"
                                                {{ in_array('請求済', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #b0e0e6; border-radius: 4px; padding: 2px 8px; display: inline-block;">請求済</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="キャンセル" class="me-2 status-checkbox"
                                                {{ in_array('キャンセル', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #d3d3d3; border-radius: 4px; padding: 2px 8px; display: inline-block;">キャンセル</span>
                                        </label>
                                        <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                            <input type="checkbox" name="status_checkbox" value="稼働不可" class="me-2 status-checkbox"
                                                {{ in_array('稼働不可', (array)request('reservation_statuses', [])) ? 'checked' : '' }}>
                                            <span style="background-color: #2c2c2c; color: white; border-radius: 4px; padding: 2px 8px; display: inline-block;">稼働不可</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <input type="checkbox" name="show_cancel_estimate" id="show_cancel_estimate" value="1" 
                               class="form-check-input me-1" style="margin-top: 0;" {{ request('show_cancel_estimate') ? 'checked' : '' }}>
                        <label for="show_cancel_estimate" style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0;">キャンセル・見積を表示</label>
                    </div>

                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm px-2"
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.8rem;">
                            検索
                        </button>
                        <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-sm btn-outline-secondary px-2"
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.8rem;">
                            クリア
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 table-hover" style="border-color: #E5E7EB; font-size: 0.75rem;">
            <thead>
                <tr>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 60px;">No.</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">運行日</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">車両名<br>号車</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 80px;">運転手</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">予約ID<br>運行ID</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 120px;">開始時刻<br>開始場所</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 60px;">最終確認</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">業務分類<br>行程名</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 120px;">団体名<br>ステッカー</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">代理店名<br>国籍</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 70px;">予約状況</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 80px;">請求額<br>未納額</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 50px;">立替</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 70px;">操作</th>
                </tr>
             </thead>
            <tbody>
                @forelse($assignments as $index => $assignment)
                @php
                    $groupInfo = $assignment->groupInfo;
                    $busCount = \App\Models\Masters\BusAssignment::where('group_info_id', $assignment->group_info_id)->count();
                    $startItinerary = $assignment->dailyItineraries->first();
                    $endItinerary = $assignment->dailyItineraries->last();
                    $startLocation = $startItinerary ? $startItinerary->start_location : '';
                    $endLocation = $endItinerary ? $endItinerary->end_location : '';
                @endphp
                <tr>
                    <td class="text-center px-1 py-1 align-middle">{{ $assignments->firstItem() + $index }}</td>
                    <td class="px-1 py-1 align-middle">
                        <span>{{ $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date)->format('Y/m/d') : '---' }}</span>
                        @if($assignment->start_date != $assignment->end_date)
                            <br><span>～ {{ $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date)->format('Y/m/d') : '---' }}</span>
                        @endif
                    </td>
                    <td class="px-1 py-1 align-middle">
                        @if($assignment->vehicle)
                            <span>{{ $assignment->vehicle->registration_number }}</span>
                            @if($assignment->vehicle->vehicleModel)
                                <br><small>({{ $assignment->vehicle->vehicleModel->model_name }})</small>
                            @endif
                            @if($assignment->vehicle_number)
                                <br><span>{{ $assignment->vehicle_number }}</span>
                            @endif
                        @else
                            <span style="background-color: #fee2e2; border-radius: 12px; padding: 2px 8px; font-size: 0.7rem; display: inline-block;">未確定</span>
                            @if($assignment->vehicle_number)
                                <br><span>{{ $assignment->vehicle_number }}</span>
                            @endif
                        @endif
                    </td>
                    <td class="px-1 py-1 align-middle">
                        @if($assignment->temporary_driver)
                            <span style="color: #f59e0b; font-weight: 600;">仮</span>
                            {{ $assignment->driver?->name ?? '---' }}
                        @else
                            {{ $assignment->driver?->name ?? '---' }}
                        @endif
                    </td>
                    <td class="px-1 py-1 align-middle text-center">
                        <a href="{{ route('masters.bus-assignments.index', ['reservation_id' => $assignment->group_info_id]) }}" class="text-decoration-none">
                            {{ $assignment->group_info_id ?? '---' }}
                        </a>
                        @if($busCount > 1)
                            <span style="color: #6b7280;">[{{ $busCount }}]</span>
                        @endif
                        <br>
                        {{ $assignment->id }}
                    </td>
                    <td class="px-1 py-1 align-middle">
                        <div>{{ $assignment->start_time ? \Carbon\Carbon::parse($assignment->start_time)->format('H:i') : '--:--' }} {{ $startLocation ?: '' }}</div>
                        <div>{{ $assignment->end_time ? \Carbon\Carbon::parse($assignment->end_time)->format('H:i') : '--:--' }} {{ $endLocation ?: '' }}</div>
                    </td>
                    <td class="text-center px-1 py-1 align-middle">
                        @php
                            $statusColor = '#fee2e2';
                            $statusText = '未確定';
                            if ($assignment->status_finalized) {
                                $statusColor = '#d1fae5';
                                $statusText = '最終確定';
                            } elseif ($assignment->status_sent) {
                                $statusColor = '#fef3c7';
                                $statusText = '送信済';
                            } elseif ($assignment->lock_arrangement) {
                                $statusColor = '#ffedd5';
                                $statusText = 'ロック中';
                            }
                        @endphp
                        <span style="background-color: {{ $statusColor }}; border-radius: 12px; padding: 2px 8px; font-size: 0.7rem; display: inline-block;">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        {{ $groupInfo->category_name ?? '---' }}<br>
                        <small>{{ $groupInfo?->itinerary_name ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        <span class="fw-bold">{{ $groupInfo?->group_name ?? '---' }}</span><br>
                        <small class="text-muted">{{ $assignment->step_car ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        {{ $groupInfo?->agency ?? '---' }}<br>
                        <small>{{ $groupInfo?->agency_country ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        @php
                            $statusBgColor = '#ffffff';
                            $statusTextColor = '#000000';
                            switch($groupInfo?->reservation_status ?? '') {
                                case '予約':
                                    $statusBgColor = '#ccf5ff';
                                    break;
                                case '仮押さえ':
                                    $statusBgColor = '#ffff99';
                                    break;
                                case '見積':
                                    $statusBgColor = '#ccffcc';
                                    break;
                                case '危ない':
                                    $statusBgColor = '#ffcccc';
                                    break;
                                case '確定待ち':
                                    $statusBgColor = '#ffd9b3';
                                    break;
                                case '確定':
                                    $statusBgColor = '#cbb87c';
                                    break;
                                case '送信済':
                                    $statusBgColor = '#e6e6fa';
                                    break;
                                case '実績待ち':
                                    $statusBgColor = '#e0b0ff';
                                    break;
                                case '運行済':
                                    $statusBgColor = '#c0c0c0';
                                    break;
                                case '請求済':
                                    $statusBgColor = '#b0e0e6';
                                    break;
                                case 'キャンセル':
                                    $statusBgColor = '#d3d3d3';
                                    break;
                                case '稼働不可':
                                    $statusBgColor = '#2c2c2c';
                                    $statusTextColor = '#ffffff';
                                    break;
                            }
                        @endphp
                        <span style="background-color: {{ $statusBgColor }}; color: {{ $statusTextColor }}; border-radius: 4px; padding: 2px 6px; font-size: 0.7rem; display: inline-block; white-space: nowrap;">
                            {{ $groupInfo?->reservation_status ?? '---' }}
                        </span>
                    </td>
                    <td class="text-center px-1 py-1 align-middle">--<br>--</td>
                    <td class="text-center px-1 py-1 align-middle">--</td>
                    <td class="text-center px-1 py-1 align-middle">
                        <div class="d-flex flex-column gap-1">
                            <a href="{{ route('masters.bus-assignments.show', $assignment->id) }}" style="color: #2563eb; text-decoration: none; font-size: 0.7rem;">詳細</a>
                            <a href="{{ route('masters.group-infos.edit', $assignment->groupInfo?->id) }}" style="color: #2563eb; text-decoration: none; font-size: 0.7rem;">編集</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="14" class="text-center py-3" style="color: #9ca3af;">運行データがありません</td></tr>
                @endforelse
            </tbody>
         <table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div style="color: #6b7280; font-size: 0.875rem;">
            全 {{ $assignments->total() }} 件中 {{ $assignments->firstItem() ?? 0 }} - {{ $assignments->lastItem() ?? 0 }} 件表示
        </div>
        <div>{{ $assignments->withQueryString()->links('pagination::bootstrap-4') }}</div>
    </div>
</div>

<form id="deleteForm" method="POST" action="">
    @csrf
    @method('DELETE')
</form>

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
.table td { vertical-align: middle; line-height: 1.3; }
.table hr { margin: 2px 0; opacity: 0.3; }

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


.selected-count {
    background-color: #0d6efd;
    color: white;
    border-radius: 10px;
    padding: 0 6px;
    font-size: 0.7rem;
    margin-left: 8px;
    display: inline-block;
    min-width: 20px;
    text-align: center;
}

.status-dropdown .btn-outline-secondary {
    color: #212529 !important;
}

.status-dropdown .btn-outline-secondary:hover {
    color: #212529 !important;
    background-color: #e9ecef !important;
    border-color: #adb5bd !important;
}

.status-dropdown .btn-outline-secondary:focus {
    color: #212529 !important;
    background-color: #fff !important;
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}
</style>
@endpush

@push('scripts')
<script>
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
}

function closeIframeModal() {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    
    if (iframe) iframe.src = '';
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

function confirmDelete(id, name) {
    if (confirm(name + ' を削除してもよろしいですか？')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route('masters.bus-assignments.destroy', ':id') }}'.replace(':id', id);
        form.submit();
    }
}



function initStatusSelect() {
    const checkboxes = document.querySelectorAll('.status-checkbox');
    const selectAllCheckbox = document.getElementById('statusSelectAll');
    const statusSelectedText = document.getElementById('statusSelectedText');
    const statusSelectedCount = document.getElementById('statusSelectedCount');
    const searchForm = document.getElementById('searchForm');
    
    function updateStatusDisplay() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        const count = selected.length;
        
        if (count === 0) {
            statusSelectedText.textContent = '予約状態';
            statusSelectedCount.style.display = 'none';
        } else {
            statusSelectedText.textContent = '予約状態';
            statusSelectedCount.textContent = count;
            statusSelectedCount.style.display = 'inline-block';
        }
        
        if (selectAllCheckbox) {
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        }
        
        document.querySelectorAll('.status-hidden-input').forEach(input => input.remove());
        
        selected.forEach(cb => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'reservation_statuses[]';
            hiddenInput.value = cb.value;
            hiddenInput.className = 'status-hidden-input';
            searchForm.appendChild(hiddenInput);
        });
    }
    
    function toggleCheckbox(checkbox) {
        checkbox.checked = !checkbox.checked;
        updateStatusDisplay();
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.removeEventListener('change', checkbox._changeHandler);
        const changeHandler = function(e) {
            e.stopPropagation();
            updateStatusDisplay();
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
            updateStatusDisplay();
        };
        selectAllCheckbox._changeHandler = selectAllHandler;
        selectAllCheckbox.addEventListener('change', selectAllHandler);
        
        selectAllCheckbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    document.querySelectorAll('.status-dropdown .dropdown-item').forEach(item => {
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
    
    updateStatusDisplay();
}



document.addEventListener('DOMContentLoaded', function() {
    let startDateValue = null;
    let endDateValue = null;
    
    initStatusSelect();
    
    const startDatePicker = flatpickr('input[name="start_date"]', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
            if (startDateValue) {
                instance.redraw();
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                startDateValue = selectedDates[0];
                endDatePicker.setDate(selectedDates[0]);
                endDatePicker.open();
                endDatePicker.set('minDate', selectedDates[0]);
                setTimeout(function() {
                    endDatePicker.redraw();
                    instance.redraw();
                }, 10);
            } else {
                startDateValue = null;
                endDatePicker.set('minDate', null);
                endDatePicker.redraw();
                instance.redraw();
            }
        },
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const dayDate = dayElem.dateObj;
            if (!dayDate) return;
            
            const dayDateStr = dayDate.toDateString();
            
            if (startDateValue && dayDateStr === startDateValue.toDateString()) {
                dayElem.classList.remove('flatpickr-disabled');
                dayElem.classList.add('start-range-highlight');
            }
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
            if (startDateValue) {
                instance.redraw();
            }
        }
    });
    
    const endDatePicker = flatpickr('input[name="end_date"]', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        minDate: startDatePicker.input.value || null,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
            if (startDateValue) {
                setTimeout(function() {
                    instance.redraw();
                }, 10);
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                endDateValue = selectedDates[0];
            } else {
                endDateValue = null;
            }
            instance.redraw();
        },
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const dayDate = dayElem.dateObj;
            if (!dayDate) return;
            
            const dayDateStr = dayDate.toDateString();
            
            if (startDateValue && dayDateStr === startDateValue.toDateString()) {
                dayElem.classList.remove('flatpickr-disabled');
                dayElem.classList.add('start-range-highlight');
            }
            
            if (endDateValue && dayDateStr === endDateValue.toDateString()) {
                dayElem.classList.remove('flatpickr-disabled');
                dayElem.classList.add('end-range-highlight');
            }
            
            if (startDateValue && endDateValue && dayDate) {
                const startTime = startDateValue.getTime();
                const endTime = endDateValue.getTime();
                const dayTime = dayDate.getTime();
                
                if (dayTime > startTime && dayTime < endTime) {
                    dayElem.classList.add('in-range-highlight');
                }
            }
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
            if (startDateValue) {
                instance.redraw();
            }
        }
    });
});

document.getElementById('newGroupBtn').addEventListener('click', function() {
    openIframeModal('{{ route('masters.group-infos.create') }}', '新規グループ作成');
});
</script>
@endpush