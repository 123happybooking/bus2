@extends('layouts.app')

@section('title', '予約一覧')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">予約一覧</h5>
        <div class="d-flex gap-2">
            <button type="button" id="exportExcelBtn" class="btn btn-success btn-sm px-3 py-1" 
                    style="background-color: #10b981; border-color: #10b981; font-size: 0.875rem;">
                <i class="bi bi-file-earmark-excel"></i> Excel 出力
            </button>
            <button type="button" id="newGroupBtn" class="btn btn-primary btn-sm px-3 py-1" 
                    style="background-color: #2563eb; border-color: #2563eb; font-size: 0.875rem;">
                新規予約
            </button>
        </div>
    </div>

    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.group-infos.index') }}" class="row g-1" id="searchForm">
            <input type="hidden" name="display_days" id="display_days" value="{{ $displayDays ?? 7 }}">
            
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">開始日</span>
                        <input type="text" name="start_date" value="{{ request('start_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" 
                               class="form-control form-control-sm datepicker-3months" style="width: 120px; border-color: #E5E7EB;" id="start_date">
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <select name="period" class="form-select form-select-sm" style="width: 100px;" id="period_select">
                            <option value="1" {{ request('period') == 1 ? 'selected' : '' }}>1週間</option>
                            <option value="2" {{ request('period') == 2 ? 'selected' : '' }}>2週間</option>
                            <option value="3" {{ request('period') == 3 ? 'selected' : '' }}>3週間</option>
                            <option value="4" {{ request('period') == 4 ? 'selected' : '' }}>1ヶ月</option>
                        </select>
                    </div>
                    
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" onclick="moveDate('month', -1)">&lt;&lt;</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="moveDate('week', -1)">&lt;</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="setToday()">今日</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="moveDate('week', 1)">&gt;</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="moveDate('month', 1)">&gt;&gt;</button>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">予約ID</span>
                        <input type="text" name="reservation_id" value="{{ request('reservation_id') }}"
                               class="form-control form-control-sm" style="width: 100px; border-color: #E5E7EB;">
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
                    
                    <div class="col">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-sm" style="border-color: #E5E7EB;" placeholder="代理店・車両...">
                    </div>
                    
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm px-3"
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                        <a href="{{ route('masters.group-infos.index', ['reset_search' => 1]) }}" class="btn btn-sm btn-outline-secondary px-3"
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                            クリア
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 table-list">
            <thead>
                <tr>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 60px;">No.</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 160px;">期間</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">予約ID</th>
                    <th class="text-start px-2 py-1" style="text-align: left !important; color: #374151; font-weight: 500;">代理店</th>
                    <th class="text-start px-2 py-1" style="color: #374151; font-weight: 500;">団体名</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">状態</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 40px;">人数</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 80px;">等級</th>
                    <th class="text-start px-2 py-1" style="text-align: left !important; color: #374151; font-weight: 500; min-width: 100px;">車両</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 150px;">請求</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">未入金</th>
                    <th class="text-start px-2 py-1" style="text-align: left !important; color: #374151; font-weight: 500; width: 150px;">備考</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 60px !important;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupInfos as $index => $groupInfo)
                @php
                    $statusBgColor = '#ffffff';
                    $statusTextColor = '#000000';
                    switch($groupInfo->reservation_status ?? '') {
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
                    
                    $startDateTime = '';
                    if ($groupInfo->start_date) {
                        $startDateTime = \Carbon\Carbon::parse($groupInfo->start_date)->format('Y/m/d');
                        if ($groupInfo->start_time) {
                            $startDateTime .= ' ' . substr($groupInfo->start_time, 0, 5);
                        }
                    }
                    
                    $periodText = '';
                    if ($groupInfo->trip_days == 1) {
                        $endTime = $groupInfo->end_time ? substr($groupInfo->end_time, 0, 5) : '';
                        $periodText = $startDateTime . ' - ' . $endTime;
                    } else {
                        $periodText = $startDateTime . ' - ' . $groupInfo->trip_days . '日';
                    }
                @endphp
                <tr>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $groupInfos->firstItem() + $index }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle" style="font-size: 0.75rem;">
                        {{ $periodText }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <a href="{{ route('masters.group-infos.index', array_merge(request()->except('reservation_id'), ['reservation_id' => $groupInfo->id])) }}" 
                           style="color: #2563eb; text-decoration: none;">
                            {{ $groupInfo->id }}
                        </a>
                    </td>
                    <td class="text-start px-2 py-1 align-middle" style="text-align: left !important;">
                        <a href="{{ route('masters.group-infos.index', array_merge(request()->except('search'), ['search' => $groupInfo->agency ?? ''])) }}" 
                           style="color: #2563eb; text-decoration: none;">
                            {{ $groupInfo->agency ?? '' }}
                        </a>
                    </td>
                    <td class="text-start px-2 py-1 align-middle">
                        {{ $groupInfo->group_name ?? '' }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <span style="background-color: {{ $statusBgColor }}; color: {{ $statusTextColor }}; border-radius: 4px; padding: 2px 6px; font-size: 0.7rem; display: inline-block; white-space: nowrap;">
                            {{ $groupInfo->reservation_status ?? '不明' }}
                        </span>
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @php
                            $totalPax = ($groupInfo->adult_count ?? 0) + 
                                       ($groupInfo->child_count ?? 0) + 
                                       ($groupInfo->guide_count ?? 0) + 
                                       ($groupInfo->other_count ?? 0);
                        @endphp
                        {{ $totalPax }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $groupInfo->vehicle_grade_name }}
                    </td>
                    <td class="text-start px-2 py-1 align-middle" style="text-align: left !important;">
                        {{ $groupInfo->vehicle ?? '--' }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle" style="font-size: 0.7rem;">
                        @if(isset($groupInfo->invoice_count) && $groupInfo->invoice_count > 0)
                            {{ $groupInfo->invoice_count }}件 / 
                            <span style="color: #dc2626;">¥{{ number_format($groupInfo->invoice_total) }}</span>
                        @else
                            --
                        @endif
                    </td>
                    <td class="text-center px-2 py-1 align-middle" style="font-size: 0.7rem;">
                        @if(isset($groupInfo->invoice_unpaid) && $groupInfo->invoice_unpaid > 0)
                            ¥{{ number_format($groupInfo->invoice_unpaid) }}
                        @else
                            --
                        @endif
                    </td>
                    <td class="text-start px-2 py-1 align-middle" style="max-width: 150px; text-align: left !important;">
                        <span class="d-inline-block" title="{{ $groupInfo->remarks ?? '' }}">
                            {{ $groupInfo->remarks ?? '--' }}
                        </span>
                    </td>
                    <td class="text-center px-2 py-1 align-middle" style="width: 60px !important;">
                        <a href="{{ route('masters.group-infos.edit', $groupInfo->id) }}" 
                           style="color: #2563eb; text-decoration: none;">
                            編集
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center py-3" style="color: #9ca3af;">
                        グループデータがありません
                    </td>
                </tr>
                @endforelse
            </tbody>
         </table>
    </div>

    @if($groupInfos->hasPages() || $groupInfos->total() > 0)
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
                        <li class="page-item {{ $groupInfos->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $groupInfos->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
    
                        @php
                            $current = $groupInfos->currentPage();
                            $last = $groupInfos->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp
    
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $groupInfos->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                        @endif
    
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $groupInfos->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                            </li>
                        @endfor
    
                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $groupInfos->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                            </li>
                        @endif
    
                        <li class="page-item {{ !$groupInfos->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $groupInfos->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
    
            <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                表示中：{{ $groupInfos->firstItem() ?? 0 }} - {{ $groupInfos->lastItem() ?? 0 }} / 全 {{ $groupInfos->total() }} 件
            </div>
        </div>
    @endif
</div>

<form id="deleteForm" method="POST" style="display: none;">
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
.table-list {
    border: 1px solid #E5E7EB !important;
}

.table-list th, .table-list td {
    padding: 0.2rem 0.2rem !important;
    vertical-align: middle;
    border-color: #E5E7EB;
    color: #111827;
    font-size: 0.8rem;
}

.table-list thead th {
    border-bottom-width: 1px;
    font-weight: 500;
    background-color: #F3F4F6;
    color: #374151;
    white-space: nowrap;
}

.table-list tbody tr:hover td,
.table-list tbody tr:hover th {
    background-color: #d8e1e9  !important;
    cursor: pointer !important;
    position: relative !important;
    z-index: 1 !important;
    font-weight: 500 !important;
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

#iframeModal {
    animation: fadeIn 0.2s ease;
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

.btn-group .btn-check:checked + .btn-outline-secondary {
    background-color: #cfe2ff !important;
    color: #212529;
    font-weight: 500 !important;
}

.btn-group .btn-check:checked + .btn-outline-secondary:hover {
    background-color: #b6d4fe !important;
    color: #212529;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

iframe {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f3f4f6;
}

iframe::-webkit-scrollbar {
    width: 6px;
}

iframe::-webkit-scrollbar-track {
    background: #f3f4f6;
}

iframe::-webkit-scrollbar-thumb {
    background-color: #d1d5db;
    border-radius: 3px;
}

.badge {
    font-weight: normal;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 4px;
}

small {
    font-size: 0.7rem;
    color: #6b7280;
}

.operation-links a {
    margin-right: 8px;
}

.operation-links a:last-child {
    margin-right: 0;
}



@media (max-width: 768px) {
    .bg-light .d-flex.flex-wrap {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;
    }
    
    .bg-light .d-flex.flex-wrap > .d-flex {
        margin: 0 !important;
        min-width: 0 !important;
    }
    
    .d-flex.align-items-center:first-child {
        grid-column: span 2 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }
    
    .d-flex.align-items-center:first-child .me-1 {
        font-size: 0.7rem !important;
        min-width: 45px !important;
    }
    
    .d-flex.align-items-center:first-child .form-control-sm {
        flex: 1 !important;
        min-width: 100px !important;
    }
    
    .d-flex.align-items-center:first-child .form-select-sm {
        flex: 1 !important;
        min-width: 80px !important;
    }
    
    .btn-group.ms-2 {
        margin-left: 0 !important;
        margin-top: 8px !important;
        width: 100% !important;
        display: flex !important;
        justify-content: center !important;
        gap: 8px !important;
    }
    
    .btn-group.ms-2 .btn {
        flex: 1 !important;
        max-width: 60px !important;
        padding: 6px 0 !important;
        font-size: 0.7rem !important;
        text-align: center !important;
    }
    
    .bg-light .col {
        grid-column: span 2 !important;
        width: 100% !important;
    }
    
    .bg-light .col input {
        width: 100% !important;
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
}

.text-start {
    text-align: left !important;
}

.table-list tr th:first-child, .table-list tr td:first-child {
    width: 40px !important;
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
    if (confirm(`「${name}」を削除してもよろしいですか？\nこの操作は元に戻せません。`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/masters/group-infos/${id}`;
        form.submit();
    }
}

window.addEventListener('message', function(event) {
    const data = event.data;
    
    if (typeof data === 'object' && data !== null) {
        if (data.action === 'open-edit') {
            closeIframeModal();
            window.location.href = data.url;
        } else if (data.action === 'redirect') {
            closeIframeModal();
            window.location.href = data.url;
        }
    } else if (data === 'close-iframe') {
        closeIframeModal();
        location.reload();
    } else if (data === 'refresh-list') {
        location.reload();
    }
});

document.getElementById('newGroupBtn').addEventListener('click', function() {
    openIframeModal('{{ route('masters.group-infos.create') }}', '新規グループ作成');
});

document.getElementById('iframeModal').addEventListener('click', function(e) {
    if (e.target === this) closeIframeModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('iframeModal').style.display === 'block') {
        closeIframeModal();
    }
});

const perPageSelect = document.getElementById('per_page_select');
if (perPageSelect) {
    perPageSelect.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        const search = document.querySelector('input[name="search"]')?.value;
        const startDate = document.querySelector('input[name="start_date"]')?.value;
        if (search) url.searchParams.set('search', search);
        if (startDate) url.searchParams.set('start_date', startDate);
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







function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
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

document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('period_select');
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            submitPeriod();
        });
    }
    
    flatpickr('input[name="start_date"]', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
        }
    });
});






document.getElementById('exportExcelBtn').addEventListener('click', function() {
    const url = new URL('{{ route("masters.group-infos.export-excel") }}');
    const params = new URLSearchParams(window.location.search);
    params.forEach((value, key) => {
        url.searchParams.append(key, value);
    });
    window.location.href = url.toString();
});
</script>
@endpush