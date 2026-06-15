@extends('layouts.app')

@section('title', '運行日報')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">運行日報</h5>
        <button type="button" id="btnDownloadAttachments" class="btn btn-sm btn-primary px-3 py-1" style="background-color: #10b981; border-color: #10b981;">
            <i class="bi bi-paperclip"></i> 添付ダウンロード
        </button>
    </div>

    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.daily-reports.index') }}" class="row g-1" id="searchForm">
            <input type="hidden" name="display_days" id="display_days" value="{{ $displayDays ?? 7 }}">
            
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">開始日</span>
                        <input type="text" name="start_date" value="{{ request('start_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" 
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
                    
                    <div class="d-flex align-items-center date-nav-wrapper">
                        <div class="btn-group btn-group-sm w-100">
                            <button type="button" class="btn btn-outline-secondary" onclick="moveDate('month', -1)">&lt;&lt;</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="moveDate('week', -1)">&lt;</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setToday()">今日</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="moveDate('week', 1)">&gt;</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="moveDate('month', 1)">&gt;&gt;</button>
                        </div>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運転手</span>
                        <select name="driver_id" class="form-select form-select-sm" style="border-color: #E5E7EB;">
                            <option value="">-- 全ての運転手 --</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">車両</span>
                        <select name="vehicle_id" class="form-select form-select-sm" style="border-color: #E5E7EB;">
                            <option value="">-- 全ての車両 --</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm px-3"
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                        <a href="{{ route('masters.daily-reports.index', ['reset_search' => 1]) }}" class="btn btn-sm btn-outline-secondary px-3"
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                            リセット
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert" style="font-size: 0.875rem;">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert" style="font-size: 0.875rem;">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 table-list">
            <thead>
                <tr>
                    <th>No.</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">日付</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">運転手</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">車両</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 90px;">出庫時間</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">出庫距離</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 90px;">帰庫時間</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">帰庫距離</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">走行距離</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 80px;">編集許可</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 80px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $index => $report)
                @php
                    $distance = null;
                    if ($report->start_mileage && $report->end_mileage) {
                        $distance = $report->end_mileage - $report->start_mileage;
                    }
                @endphp
                <tr>
                    <td>{{ $reports->firstItem() + $index }}</td>
                    <td class="text-center px-2 py-1 align-middle">{{ \Carbon\Carbon::parse($report->date)->format('Y/m/d') }}</td>
                    <td class="px-2 py-1 align-middle">{{ $report->driver->name ?? '-' }}</td>
                    <td class="px-2 py-1 align-middle">{{ $report->vehicle->registration_number ?? '-' }}</td>
                    <td class="text-center px-2 py-1 align-middle">{{ $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '-' }}</td>
                    <td class="text-end px-2 py-1 align-middle">{{ $report->start_mileage ? number_format($report->start_mileage) : '-' }}</td>
                    <td class="text-center px-2 py-1 align-middle">{{ $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '-' }}</td>
                    <td class="text-end px-2 py-1 align-middle">{{ $report->end_mileage ? number_format($report->end_mileage) : '-' }}</td>
                    <td class="text-end px-2 py-1 align-middle">{{ $distance !== null ? number_format($distance) : '-' }}</td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if($report->allow_edit)
                            <span class="badge bg-success" style="background-color: #10b981 !important;">ON</span>
                        @else
                            <span class="badge bg-secondary" style="background-color: #6c757d !important;">OFF</span>
                        @endif
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <a href="{{ route('masters.daily-reports.edit', $report->id) }}" 
                           style="color: #2563eb; text-decoration: none;">
                            編集
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-3" style="color: #9ca3af;">データがありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages() || $reports->total() > 0)
        <div class="mt-3">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                
                <div class="d-flex align-items-center">
                    <label for="per_page_select" class="form-label small text-muted mb-0 me-2" style="white-space: nowrap;">
                        表示件数:
                    </label>
                    <select id="per_page_select" class="form-select form-select-sm" style="font-size: 0.75rem; min-width: 80px;">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                    </select>
                </div>
    
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item {{ $reports->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $reports->previousPageUrl() }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
    
                        @php
                            $current = $reports->currentPage();
                            $last = $reports->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp
    
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $reports->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                        @endif
    
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $reports->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                            </li>
                        @endfor
    
                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $reports->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                            </li>
                        @endif
    
                        <li class="page-item {{ !$reports->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $reports->nextPageUrl() }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
    
            <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                表示中：{{ $reports->firstItem() ?? 0 }} - {{ $reports->lastItem() ?? 0 }} / 全 {{ $reports->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.table-sm th, .table-sm td {
    padding: 0.2rem 0.2rem !important;
    vertical-align: middle;
    border-color: #E5E7EB;
    color: #111827;
    font-size: 0.8rem;
}

.table-bordered {
    border: 1px solid #E5E7EB;
}

.table thead th {
    border-bottom-width: 1px;
    font-weight: 500;
    background-color: #F3F4F6;
    color: #374151;
    white-space: nowrap;
}

.pagination {
    margin-bottom: 0;
    gap: 2px;
}

.pagination .page-link {
    color: #374151;
    border-color: #E5E7EB;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.pagination .page-item.active .page-link {
    background-color: #2563eb;
    border-color: #2563eb;
    color: white;
}

.form-control:focus, .form-select:focus, .btn:focus {
    box-shadow: none;
    border-color: #2563eb;
}

.container-fluid {
    max-width: 1600px;
}

a:hover {
    text-decoration: underline !important;
}

.alert {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.alert-dismissible .btn-close {
    padding: 0.75rem 1rem;
}

.btn-outline-secondary {
    color: #212529 !important;
    background-color: #fff !important;
    border-color: #ced4da !important;
}

.btn-outline-secondary:hover {
    background-color: #e9ecef !important;
    border-color: #adb5bd !important;
    color: #212529 !important;
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

const perPageSelect = document.getElementById('per_page_select');
if (perPageSelect) {
    perPageSelect.addEventListener('change', function() {
        const url = new URL(window.location.href);
        const startDate = document.querySelector('input[name="start_date"]')?.value;
        const driverId = document.querySelector('select[name="driver_id"]')?.value;
        const vehicleId = document.querySelector('select[name="vehicle_id"]')?.value;
        url.searchParams.set('per_page', this.value);
        if (startDate) url.searchParams.set('start_date', startDate);
        if (driverId) url.searchParams.set('driver_id', driverId);
        if (vehicleId) url.searchParams.set('vehicle_id', vehicleId);
        window.location.href = url.toString();
    });
}

const periodSelect = document.getElementById('period_select');
if (periodSelect) {
    periodSelect.addEventListener('change', function() {
        submitPeriod();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    flatpickr('input[name="start_date"]', {
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
});



document.getElementById('btnDownloadAttachments')?.addEventListener('click', function() {
    const form = document.getElementById('searchForm');
    const formData = new FormData(form);
    
    const params = new URLSearchParams();
    formData.forEach((value, key) => {
        if (value) params.append(key, value);
    });
    
    window.location.href = "{{ route('masters.daily-reports.download-attachments') }}?" + params.toString();
});
</script>
@endpush