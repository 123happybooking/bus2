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
        <form method="GET" action="{{ route('masters.bus-assignments.index') }}" class="row g-1">
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
                        <div class="form-check form-check-inline me-1">
                            <input class="form-check-input" type="radio" name="date_type" id="date_type_today" value="today" {{ request('date_type') == 'today' ? 'checked' : '' }} style="transform: scale(0.8);">
                            <label class="form-check-label" for="date_type_today" style="font-size: 0.8rem;">当日</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="date_type" id="date_type_same" value="same" {{ request('date_type') == 'same' ? 'checked' : '' }} style="transform: scale(0.8);">
                            <label class="form-check-label" for="date_type_same" style="font-size: 0.8rem;">同日</label>
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
        <table class="table table-sm table-bordered mb-0" style="border-color: #E5E7EB; font-size: 0.75rem;">
            <thead>
                <tr>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 60px;">No.</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">運行期間</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">車両名<br>号車</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 80px;">運転手</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">予約ID<br>運行ID</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">開始時刻<br>開始場所</th>
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
                <tr>
                    <td class="text-center px-1 py-1 align-middle">{{ $assignments->firstItem() + $index }}</td>
                    <td class="px-1 py-1 align-middle"><span>{!! nl2br(e($assignment->period_display)) !!}</span></td>
                    <td class="px-1 py-1 align-middle"><span>{!! nl2br(e($assignment->vehicle_display)) !!}</span></td>
                    <td class="px-1 py-1 align-middle">
                        @if($assignment->temporary_driver)
                            <span style="color: #f59e0b; font-weight: 600;">仮</span>
                            {{ $assignment->driver?->name ?? '---'  }}
                        @else
                            {{ $assignment->driver?->name ?? '---'  }}
                        @endif
                    </td>
                    <td class="px-1 py-1 align-middle text-center">
                        <a href="{{ route('masters.group-infos.edit', $assignment->groupInfo?->id) }}" class="text-decoration-none">{{ $assignment->group_info_id ?? '---' }}</a><br>
                        {{ $assignment->id }}
                    </td>
                    <td class="px-1 py-1 align-middle">{!! nl2br(e($assignment->start_info)) !!}</td>
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
                        {{ $assignment->groupInfo?->business_category ?? '---' }}<br>
                        <small>{{ $assignment->groupInfo?->itinerary_name ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        <span class="fw-bold">{{ $assignment->groupInfo?->group_name ?? '---' }}</span><br>
                        <small class="text-muted">{{ $assignment->step_car ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        {{ $assignment->groupInfo?->agency ?? '---' }}<br>
                        <small>{{ $assignment->groupInfo?->agency_country ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">{{ $assignment->groupInfo?->reservation_status ?? '---' }}</td>
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
         </table>
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

document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.datepicker-3months', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        mode: 'single',
        disableMobile: true,
        wrap: false,
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
});


document.getElementById('newGroupBtn').addEventListener('click', function() {
    openIframeModal('{{ route('masters.group-infos.create') }}', '新規グループ作成');
});
</script>
@endpush