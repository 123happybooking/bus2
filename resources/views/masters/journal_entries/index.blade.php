@extends('layouts.app')
@section('content')
<style>
    /* === 1. 全局基础样式 === */
    body {
        font-size: 0.9rem;
    }

    /* === 2. 表格紧凑模式 (优化版：仅影响列表，不影响编辑器) === */
    .table-compact th,
    .table-compact td {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.85rem;
        vertical-align: middle;
        line-height: 1.2;
    }

    .table-compact th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }

    /* === 2.1 极致紧凑模式：上半部分列表专用 === */
    .card-body .table-compact th,
    .card-body .table-compact td {
        padding: 0.1rem 0.25rem !important;
        font-size: 0.75rem !important;
        line-height: 1.7;
        height: auto;
    }

    .card-body .table-compact thead th {
        font-size: 0.8rem !important;
        padding-top: 0.2rem !important;
        padding-bottom: 0.2rem !important;
    }

    /* === 3. 搜索表单紧凑化 === */
.search-form .form-control, .search-form .form-select {
    height: 31px; 
    font-size: 0.875rem; 
    padding: 0.25rem 0.5rem; 
}
.search-form .btn { 
    height: 31px; 
    font-size: 0.875rem; 
    padding: 0.25rem 0.75rem; /* 微调左右内边距，让视觉更舒适 */
    margin-top: 0;    /* 清除默认上边距 */
    margin-bottom: 0; /* 清除默认下边距 */
    line-height: 1.5; /* 强制行高，防止文字下沉 */
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
}
    /* === 3.1 搜索表单：列表专用压缩 === */
    .card-body .search-form .form-control,
    .card-body .search-form .form-select {
        height: 26px !important;
        font-size: 0.75rem !important;
        padding: 0.1rem 0.4rem !important;
    }

    .card-body .search-form .btn {
        height: 27px !important;
        font-size: 0.7rem !important;
        padding: 0.1rem 0.4rem !important;
    }

    /* === 4. 底部编辑器紧凑化 === */
    .fixed-bottom {
        height: 260px !important;
        padding: 0.5rem 0.5rem 0.25rem 0.5rem;
        z-index: 1000;
        border-top: 1px solid #dee2e6;
        background-color: #fff;
        display: flex;
        flex-direction: column;
    }

    .editor-header {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.85rem;
        border-bottom: 1px solid #dee2e6;
    }

    .editor-compact .form-control,
    .editor-compact .form-select {
        height: 28px;
        font-size: 0.8rem;
        padding: 0.1rem 0.4rem;
    }

    .editor-compact table th,
    .editor-compact table td {
        padding: 0.15rem 0.3rem !important;
        font-size: 0.75rem;
        height: 26px;
    }

    .account-input.is-invalid {
        border-color: #dc3545 !important;
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        padding-right: calc(1.5em + 0.75rem);
    }

    .editor-compact .input-group .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        height: 28px;
    }
    .editor-compact .input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        height: 28px;
        padding: 0 0.25rem;
        display: flex;
        align-items: center;
    }

    /* === 5. 分页样式重写 (适配深色头部) === */
    .card-header .pagination {
        margin: 0 !important;
        height: 30px;
    }

    .card-header .page-link {
        background-color: #212529;
        border-color: #6c757d;
        color: #fff;
        padding: 0.1rem 0.4rem;
        font-size: 0.75rem;
    }

    .card-header .page-link:hover {
        background-color: #495057;
        color: #fff;
    }

    .card-header .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .card-header .page-item.disabled .page-link {
        background-color: #212529;
        border-color: #495057;
        color: #6c757d;
    }

    /* === 6. 关键：增加列表可视区域高度 === */
    /* 移除固定的 max-height，使用动态计算 */
    .table-responsive.dynamic-height {
        max-height: calc(100vh - 160px) !important; /* 你可以根据需要调整这个数值，越小表格越高 */
        overflow-y: auto !important; /* 强制显示纵向滚动条 */
        overflow-x: auto !important; /* 修复横向滚动条消失的问题，防止表头错位 */
        display: block; /* 确保容器块级显示，这对滚动很重要 */
    }

    /* === 7. 优化右上角组件对齐 === */
    .card-header {
        padding: 0.4rem 0.75rem !important;
        min-height: auto; /* 允许高度自适应内容 */
        align-items: center !important;
    }

    /* 修正分页链接的行高，使其与下拉框对齐 */
    .card-header .pagination {
        margin: 0 !important;
        height: auto;
    }
    .card-header .page-link {
        line-height: 1.5; /* 调整行高以匹配下拉框 */
        padding: 0.1rem 0.4rem;
    }

    /* 修正下拉框在深色背景下的边框 */
    .card-header .form-select {
        background-color: #343a40;
        border-color: #6c757d;
        color: #fff;
    }
    .custom-fixed-bottom {
        /* 默认状态：假设菜单收起，宽度为 60px */
        left: 60px !important; 
        width: calc(100% - 60px) !important;
        transition: left 0.3s ease, width 0.3s ease; /* 添加动画过渡 */
    }
    .editor-compact {
        left: 0 !important;      
        right: 0 !important;      
        width: auto !important;   
        position: fixed;          
        bottom: 0;               
        z-index: 1000;
    }
    .custom-input:focus {
        box-shadow: none;   /* 2. 去除 Bootstrap 默认的蓝色阴影 */
    }
</style>

<div class="container-fluid py-2" style="padding-bottom: 100px;">
    <div class="card shadow-sm mb-2 border-0">
        
        <!-- === 右上角：标题、新增、分页、行数选择器 === -->
        <div class="card-header bg-dark py-2 d-flex justify-content-between align-items-center">
            <!-- 左侧：标题 -->
            <h6 class="mb-0 text-white fw-bold"><i class="bi bi-list-ul"></i> 仕訳伝票一覧</h6>
            
            <!-- 右侧：所有功能组件 -->
            <div class="d-flex align-items-center gap-2">
                
                <!-- 1. 每页显示行数选择器 -->
                <div class="d-flex align-items-center text-white">
                    <select id="perPageSelect" class="form-select form-select-sm bg-dark text-white border-secondary" style="font-size: 0.75rem; height: 26px;">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                <!-- 2. 分页导航 -->
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <!-- 上一页 -->
                        <li class="page-item {{ $entries->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $entries->previousPageUrl() }}" aria-label="Previous">
                                <span>&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- 页码 -->
                        @php
                            $current = $entries->currentPage();
                            $last = $entries->lastPage();
                            $start = max(1, $current - 1);
                            $end = min($last, $current + 1);
                        @endphp
                        
                        @if($start > 1)
                            <li class="page-item"><a class="page-link" href="{{ $entries->url(1) }}">1</a></li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endif
                        
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $entries->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor
                        
                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item"><a class="page-link" href="{{ $entries->url($last) }}">{{ $last }}</a></li>
                        @endif

                        <!-- 下一页 -->
                        <li class="page-item {{ !$entries->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $entries->nextPageUrl() }}" aria-label="Next">
                                <span>&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>

            </div>
        </div>

        <div class="card-body p-2">
            <!-- 搜索表单 (保持原样) -->
            <form method="GET" action="{{ route('masters.journal_entries.index') }}" class="row g-2 mb-3 search-form" id="searchForm">
                <input type="hidden" id="js-yearmonth" name="yearmonth" value="">
                <!-- 周期与月份组 -->
                <div class="col-md-auto">
                    <label class="form-label mb-1 text-muted" style="font-size: 0.75rem;">周期/月份</label>
                    
                    <div class="d-flex align-items-end">
                        <!-- 1. 周期下拉框 (样式已统一) -->
                        <div class="position-relative me-2">
                            <!-- 防止自动填充的隐藏框 -->
                            <input type="text" style="display: none;" tabindex="-1" autocomplete="off">
                            
                            <select id="periodSelect" name="period_id" class="form-control form-control-sm" style="font-size: 0.85rem; padding-right: 25px; appearance: none; -webkit-appearance: none; -moz-appearance: none; background-color: #fff;">
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}" 
                                        data-start="{{ $period->start }}" 
                                        data-end="{{ $period->end }}"
                                    {{ $period_id == $period->id ? 'selected' : '' }}>
                                        {{ $period->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. 循环12个月份按钮 -->
                        <div id="month-select-container">
                            @foreach($months as $key => $monthName)
                                @php
                                    $isActive = ($yearmonth == $monthName);
                                @endphp
                                <!-- 
                                修改点：
                                1. type="submit" 保持不变
                                2. name="yearmonth" 保持不变
                                3. onclick 改为直接提交 form，确保参数能发出去
                                -->
                                <button type="submit" 
                                        name="yearmonth" 
                                        value="{{ $monthName }}"
                                        onclick="document.getElementById('searchForm').submit();"
                                        class="btn btn-sm ms-1 p-0 px-1 {{ $isActive ? 'btn-primary' : 'btn-outline-primary' }}" 
                                        style="min-width: 28px; font-size: 0.8rem; {{ $isActive ? 'background-color: #0d6efd; border-color: transparent; color: white !important;' : 'border-color: #E5E7EB;' }}">
                                    {{ $key }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 勘定科目搜索组 -->
                <div class="col-md-3">
                    <label class="form-label mb-1 text-muted" style="font-size: 0.75rem;">勘定科目</label>
                    <div class="position-relative">
                        <input type="text" style="display: none;" tabindex="-1" autocomplete="off">
                        <input type="text" 
                            id="search-account-input" 
                            class="form-control form-control-sm account-input" 
                            list="account-list-search" 
                            placeholder="科目コードまたは名前を入力" 
                            style="font-size: 0.85rem;"
                            autocomplete="off"
                            value="{{ $accounts->find(request('account_id')) ? $accounts->find(request('account_id'))->code . ' - ' . $accounts->find(request('account_id'))->name : '' }}"
                            oninput="if(this.value === '') document.getElementById('search-account-id').value = ''">

                        <input type="hidden" id="search-account-id" name="account_id" value="{{ request('account_id') }}">

                        <datalist id="account-list-search">
                            @foreach($accounts as $account)
                                <option value="{{ $account->code }} - {{ $account->name }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <!-- 操作按钮组 -->
                <div class="col-md-auto d-flex align-items-end" style="padding-top: 2px;">
                    <button type="submit" class="btn btn-outline-primary btn-sm d-flex align-items-center" onclick="autoSelectMonth()">
                        <i class="bi bi-search me-1"></i> 検索
                    </button>
                    
                    <!-- 修正点：在判断条件中加入 period_id，确保切换周期后能显示清除按钮 -->
                    @if(request()->hasAny([ 'account_id', 'period_id', 'yearmonth']))
                        <a href="{{ route('masters.journal_entries.index') }}" class="btn btn-outline-secondary btn-sm ms-1 d-flex align-items-center">
                            <i class="bi bi-x-circle me-1"></i> クリア
                        </a>
                    @endif
                </div>
            </form>

            <!-- 列表表格 -->
            <div class="table-responsive dynamic-height" style="max-height: 550px !important;">
                <table class="table table-hover table-bordered align-middle table-compact mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <!-- 1. 添加了 "No" 列 -->
                            <th width="4%" class="text-center">No</th>
                            <th width="10%">伝票ID</th>
                            <th width="10%" class="position-relative" style="cursor: pointer;" onclick="sortTable('posting_date')">
                                <!-- 文字部分：设置 padding-right 给箭头留出空间 -->
                                <span class="pe-4">仕訳日</span>

                                <!-- 箭头容器：使用绝对定位，强制固定在右侧居中 -->
                                <div class="sort-arrows" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); display: flex; flex-direction: column; align-items: center; line-height: 0.5;">
                                    <!-- 上箭头 -->
                                    <i class="bi bi-caret-up-fill sort-icon asc" 
                                    style="font-size: 0.6rem; color: #ccc; margin: 0; padding: 0;"></i>
                                    <!-- 下箭头 -->
                                    <i class="bi bi-caret-down-fill sort-icon desc" 
                                    style="font-size: 0.6rem; color: #ccc; margin: 0; padding: 0;"></i>
                                </div>

                                <!-- 隐藏域：存储排序状态 -->
                                <input type="hidden" name="sort_field" value="posting_date">
                                <input type="hidden" name="sort_order" class="sort-order-input" value="">
                            </th>
                            <th width="16%">借方</th>
                            <th width="16%">貸方</th>
                            <th width="8%">摘要</th>
                            <th width="5%">部門/分類</th>
                            <th width="3%" class="text-center">操作</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr style="cursor: pointer;" onclick="fetchAndLoadEntry({{ $entry->id }})">
                            <!-- 2. 自动生成累加序号 -->
                            <td class="fw-bold text-center" style="vertical-align: middle;">
                                {{ $loop->index + $entries->firstItem() }}
                            </td>

                            <td>{{ $entry->source_id}}</td>
                            <td>{{ $entry->posting_date->format('Y-m-d') }}</td>

                            <td>{!! $entry->debit_details_html !!}</td>
                            <td>{!! $entry->credit_details_html !!}</td>
                            <td>{{ $entry->remark}}</td>
                            <td>{{ $entry->department->name ?? '' }} {{ $entry->source_type}}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                                    <form action="{{ route('masters.journal_entries.destroy', $entry->id) }}?{{ http_build_query(request()->only(['period_id', 'yearmonth', 'account_id'])) }}" 
                                        method="POST" 
                                        onsubmit="return confirm('本当に削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <!-- 保持原有的按钮结构 -->
                                        <button type="submit" class="btn btn-link text-danger p-0" style="font-size: 0.8rem;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-3 text-muted">データがありません。</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

{{-- 底部编辑器 --}}

    <div class="position-relative bg-white border-top shadow-lg p-2 editor-compact" style="z-index: 1000; height: 330px; overflow: hidden; display: flex; flex-direction: column;">
        <div class="d-flex justify-content-between align-items-center mb-1 flex-shrink-0">
            <!-- 左侧区域：标题 + 按钮 -->
            <div class="d-flex gap-2 align-items-center">
                <h6 class="mb-0 text-success fw-bold" style="font-size: 0.9rem;"><i class="bi bi-keyboard"></i> クイック入力</h6>
                
                <!-- 插入位置：这里就是标题的右边 -->
                <button type="button" 
                    class="btn text-white py-1 ms-2" 
                    style="font-size: 0.75rem; height: 22px; line-height: 1;
                        background: linear-gradient(145deg, #6c757d, #495057);
                        border: none;
                        border-radius: 0.375rem;
                        padding: 0 0.75rem;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                    onmouseover="this.style.background='linear-gradient(145deg, #5a6268, #343a40)'"
                    onmouseout="this.style.background='linear-gradient(145deg, #6c757d, #495057)'"
                    onclick="clearEditor()">
                    <i class="bi bi-plus-lg me-1"></i> 新規
                </button>
                
                <span id="editing-id-badge" class="badge bg-warning text-dark d-none" style="font-size: 0.75rem;">ID: <span id="editing-id-val"></span></span>
            </div>
            
            <!-- 右侧区域：只剩下金额和保存按钮 -->
            <div class="d-flex gap-3 align-items-center">
                <!-- 金额显示 -->
                <div class="text-end" style="font-size: 0.8rem; line-height: 1.1;">
                    <span class="text-danger">借方：<span id="total-debit-display" class="fw-bold">0</span></span> | 
                    <span class="text-primary">貸方：<span id="total-credit-display" class="fw-bold">0</span></span>
                    <br><span id="balance-status" class="badge bg-success" style="font-size: 0.7rem;">balanced</span>
                </div>
                <!-- 保存按钮 -->
                <button type="button" class="btn btn-primary px-3 py-0" style="height: 28px; font-size: 0.85rem;" onclick="submitJournalEntry()"> 
                    <i class="bi bi-save"></i> 保存 
                </button>
            </div>
        </div>

        <input type="hidden" id="edit-entry-id" value="">
        
        <div class="row g-1 flex-grow-1" style="overflow: hidden;">
            <div class="col-md-12 mb-1">
                <div class="row g-1">
                    <div class="col-md-2"><input type="date" id="post-date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                    <div class="col-md-2">
                        <input type="text" id="post-dept" class="form-control" autocomplete="off" list="dept-list-main" placeholder="部門を入力または選択" onchange="validateMainDeptInput()">
                        <datalist id="dept-list-main">
                            @foreach($departments as $dept)<option value="{{ $dept->name }}">{{ $dept->name }}</option>@endforeach
                        </datalist>
                    </div>
                    <div class="col-md-2"><input type="text" id="post-source-type" class="form-control" placeholder="伝票種別"></div>
                    <div class="col-md-6"><input type="text" id="post-source-remark" class="form-control" placeholder="摘要"></div>
                </div>
            </div>

            <div class="col-md-6 d-flex flex-column h-100">
                <div class="card h-100 border-danger border-1">
                    <div class="card-header bg-danger text-white py-0 d-flex justify-content-between align-items-center editor-header">
                        <span><i class="bi bi-arrow-down-right"></i> 借方</span>
                        <button type="button" class="btn btn-sm btn-light text-danger py-0" style="height: 22px;" onclick="addLine(1)"><i class="bi bi-plus"></i> 行追加</button>
                    </div>
                    <div class="card-body p-1 overflow-auto" style="max-height: 220px;">
                        <table class="table table-sm table-bordered mb-0" id="table-debit">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="30%">勘定科目</th>
                                    <th width="20%">補助科目</th>
                                    <th width="15%">取引先</th>
                                    <th width="15%">税区分</th>
                                    <th width="16%">金額</th>
                                </tr>
                            </thead>
                            <tbody class="sortable-list" data-side="1"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 d-flex flex-column h-100">
                <div class="card h-100 border-primary border-1">
                    <div class="card-header bg-primary text-white py-0 d-flex justify-content-between align-items-center editor-header">
                        <span><i class="bi bi-arrow-up-right"></i> 貸方</span>
                        <button type="button" class="btn btn-sm btn-light text-primary py-0" style="height: 22px;" onclick="addLine(2)"><i class="bi bi-plus"></i> 行追加</button>
                    </div>
                    <div class="card-body p-1 overflow-auto" style="max-height: 220px;">
                        <table class="table table-sm table-bordered mb-0" id="table-credit">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="30%">勘定科目</th>
                                    <th width="20%">補助科目</th>
                                    <th width="15%">取引先</th>
                                    <th width="15%">税区分</th>
                                    <th width="16%">金額</th>
                                </tr>
                            </thead>
                            <tbody class="sortable-list" data-side="2"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function autoSelectMonth() {
    // 1. 获取表单元素
    const form = document.getElementById('searchForm');
    // 2. 获取专门用于 yearmonth 的隐藏域
    const hiddenYearMonth = document.getElementById('js-yearmonth');
    if(!hiddenYearMonth) return;

    // 3. 查找当前高亮的按钮
    const activeBtn = document.querySelector('#month-select-container .btn-primary');
    
    if (activeBtn) {
        // 如果有高亮按钮，就把按钮的 value 填入隐藏域
        hiddenYearMonth.value = activeBtn.value;
        form.submit(); // 提交表单
    } else {
        // 如果没有高亮按钮（直接点检索）
        const firstMonthBtn = document.querySelector('#month-select-container button');
        if (firstMonthBtn) {
            hiddenYearMonth.value = firstMonthBtn.value; // 填入第一个月份
        }
        // 如果连按钮都没有（极端情况），hiddenYearMonth.value 保持为空，提交空值
        form.submit(); // 提交表单
    }
}

    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('periodSelect');
        
        if(select) {
            // 监听下拉框的变化
            select.addEventListener('change', function() {
                // 1. 获取当前选中的周期ID
                const periodId = this.value;
                
                // 2. 获取当前选中的月份按钮的值 (如果有的话)
                // 我们尝试在容器里找一个被选中的按钮，或者找第一个按钮作为默认值
                const activeMonthBtn = document.querySelector('#month-select-container button.btn-primary');
                let yearMonth = '';
                
                if (activeMonthBtn) {
                    yearMonth = activeMonthBtn.value;
                } else {
                    // 如果没有高亮按钮，尝试获取第一个按钮的值，防止月份参数丢失
                    const firstMonthBtn = document.querySelector('#month-select-container button');
                    if (firstMonthBtn) {
                        yearMonth = firstMonthBtn.value;
                    }
                }

                // 3. 构建新的 URL
                // 使用 URLSearchParams 来处理参数，这样最安全，不会弄丢其他参数
                const url = new URL(window.location.href);
                
                // 设置参数 (会自动覆盖旧的)
                url.searchParams.set('period_id', periodId);
                
                // 只有当确实有月份值的时候才设置，否则只传 period_id
                if (yearMonth) {
                    url.searchParams.set('yearmonth', yearMonth);
                }

                // 4. 跳转！(页面会刷新，带着参数回到后台)
                window.location.href = url.toString();
            });
        }
    });

    // 全局变量
    const accountsData = @json($accounts);
    const accountsDataJie = @json($accountsJie ?? []); // 借方科目数据
    const accountsDataDai = @json($accountsDai ?? []); // 贷方科目数据
    const partnersData = @json($partners);
    const taxesData = @json($taxes);
    const departmentsData = @json($departments);
    const csrfToken = '{{ csrf_token() }}';
    const saveUrl = "{{ route('masters.journal_entries.store') }}";
    const updateUrlBase = "{{ route('masters.journal_entries.update', '__ID__') }}";
    let getSubsUrlTemplate = "{{ route('masters.account.account-subs', ['accountId' => '__ID__']) }}";

    // 1. 初始化
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Sortable !== 'undefined') {
            document.querySelectorAll('.sortable-list').forEach(list => {
                new Sortable(list, { 
                    animation: 150, 
                    handle: '.drag-handle', 
                    ghostClass: 'bg-light', 
                    onEnd: calculateTotals 
                });
            });
        }
        // 初始化添加借(1)贷(2)行
        addLine(1);
        addLine(2);
    });

    // 2. 添加行 (Side 使用数字 1 或 2)
    function addLine(side, data = {}) {
        // 确保 side 是数字
        side = parseInt(side);
        
        // 根据数字 side (1/2) 获取对应的 tbody
        const tbody = document.querySelector(`.sortable-list[data-side="${side}"]`);
        if (!tbody) {
            console.error('找不到对应的 tbody:', side);
            return;
        }

        const rowId = 'row-' + Date.now() + Math.random().toString(36).substr(2, 9);
        const tr = document.createElement('tr');
        tr.setAttribute('data-row-id', rowId);
        tr.setAttribute('data-side', side);
        if(data && data.id) tr.setAttribute('data-db-id', data.id);

        const accList = accountsData || [];
        const partList = partnersData || [];
        const taxList = taxesData || [];

        const currentAccountData = side === 1 ? accountsDataJie : accountsDataDai;

        // 1. 勘定科目
        let currentAccountText = '';
        if (data.account_full_text) {
            currentAccountText = data.account_full_text;
        } else if (data.account_id) {
            const found = currentAccountData.find(a => a.id == data.account_id);
            if (found) currentAccountText = `${found.code} - ${found.name}`;
        }
        
        const accountDataListId = `account-list-${rowId}`;
        const accountOptions = currentAccountData.map(a => `<option value="${a.code} - ${a.name}">`).join('');

        // 2. 補助科目
        const subDataListId = `sub-list-${rowId}`;
        let currentSubText = data.account_sub_name || ''; 

        // 3. 取引先
        let currentPartnerText = '';
        if (data.partner_id) {
            const found = partList.find(p => p.id == data.partner_id);
            if (found) currentPartnerText = found.name;
        } else if (data.partner_name) {
            currentPartnerText = data.partner_name;
        }
        const partnerDataListId = `partner-list-${rowId}`;
        const partnerOptions = partList.map(p => `<option value="${p.name}">`).join('');

        // 4. 税区分
        const taxOptions = `<option value="">[税区分無し]</option>` + taxList.map(t => `<option value="${t.id}" ${(data.tax_type_id == t.id) ? 'selected' : ''}>${t.name}</option>` ).join('');

        tr.innerHTML = `
            <td>
                <div class="position-relative">
                    <input type="text" autocomplete="off" class="form-control form-control-sm account-input" 
                           list="${accountDataListId}" value="${currentAccountText}" placeholder="科目"
                           onchange="handleAccountChange(this); this.classList.remove('is-invalid');" onblur="validateAccountInput(this)">
                    <datalist id="${accountDataListId}">${accountOptions}</datalist>
                    <input type="hidden" class="account-id-hidden" value="${data.account_id || ''}">
                </div>
            </td>
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm sub-input" 
                           list="${subDataListId}" value="${currentSubText}" placeholder="補助 (任意)"
                           ${!data.account_id ? 'disabled' : ''}>
                    <datalist id="${subDataListId}"></datalist>
                    <input type="hidden" class="sub-id-hidden" value="${data.account_sub_id || ''}">
                </div>
            </td>
            <td>
                <div class="position-relative">
                    <input type="text" autocomplete="off" class="form-control form-control-sm partner-input" 
                           list="${partnerDataListId}" value="${currentPartnerText}" placeholder="取引先 (任意)">
                    <datalist id="${partnerDataListId}">${partnerOptions}</datalist>
                    <input type="hidden" class="partner-id-hidden" value="${data.partner_id || ''}">
                </div>
            </td>
            <td><select class="form-select tax-select">${taxOptions}</select></td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" class="form-control amount-input text-end" 
                           value="${data.amount || ''}" oninput="calculateTotals(); this.classList.remove('is-invalid');" placeholder="0">
                           <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)" style="border-left:0;"><i class="bi bi-x-lg"></i></button>
                
                </div>
            </td>
        `;
        
        tbody.appendChild(tr);
        calculateTotals();

        if (data.account_id) {
            const accInput = tr.querySelector('.account-input');
            fetchAccountSubs(accInput, data.account_sub_id, currentSubText);
        }
    }

    // 3. 勘定科目变更
    function handleAccountChange(inputElement) {
        const textValue = inputElement.value.trim();
        const row = inputElement.closest('tr');
        const hiddenIdInput = row.querySelector('.account-id-hidden');
        const subInput = row.querySelector('.sub-input');
        const subHidden = row.querySelector('.sub-id-hidden');
        const accList = accountsData || [];

        subInput.value = '';
        subHidden.value = '';
        subInput.disabled = true;
        const subDatalist = row.querySelector(`datalist[id^="sub-list-"]`);
        if(subDatalist) subDatalist.innerHTML = '';

        if (!textValue) {
            hiddenIdInput.value = '';
            inputElement.classList.remove('is-invalid');
            calculateTotals();
            return;
        }

        const found = accList.find(a => `${a.code} - ${a.name}` === textValue);
        
        if (found) {
            hiddenIdInput.value = found.id;
            inputElement.value = `${found.code} - ${found.name}`;
            inputElement.classList.remove('is-invalid');
            subInput.disabled = false;
            fetchAccountSubs(inputElement, null, null);

            const taxSelect = row.querySelector('.tax-select');
            if (taxSelect && found.tax_id) {
                taxSelect.value = found.tax_id; 
            }
        } else {
            hiddenIdInput.value = ''; 
            inputElement.classList.add('is-invalid');
            setTimeout(() => inputElement.classList.remove('is-invalid'), 2000);
        }
        calculateTotals();
    }
    function validateAccountInput(el) { handleAccountChange(el); }

    // 4. AJAX 获取辅助科目
    function fetchAccountSubs(accountInput, existingSubId = null, existingSubName = null) {
        const row = accountInput.closest('tr');
        const accountId = row.querySelector('.account-id-hidden').value;
        const subInput = row.querySelector('.sub-input');
        const subDatalist = row.querySelector(`datalist[id^="sub-list-"]`);
        const subHidden = row.querySelector('.sub-id-hidden');

        if (!accountId || !subDatalist) return;

        let url = '';
        try {
            url = getSubsUrlTemplate.replace('__ID__', accountId);
        } catch(e) {
            console.warn('Route URL generation failed, skipping subs fetch');
            return;
        }

        fetch(url)
            .then(res => res.ok ? res.json() : [])
            .then(data => {
                row.dataset.subsCache = JSON.stringify(data);
                let options = '';
                data.forEach(sub => {
                    options += `<option value="${sub.display}">`;
                });
                subDatalist.innerHTML = options;

                if (existingSubId && existingSubName) {
                    const found = data.find(s => s.id == existingSubId);
                    if (found) {
                        subHidden.value = found.id;
                        subInput.value = found.display;
                    } else {
                        subHidden.value = '';
                        subInput.value = '';
                    }
                }
            })
            .catch(err => console.error('Failed to load subs:', err));
    }

    function validateMainDeptInput() {
        const inputEl = document.getElementById('post-dept');
        inputEl.classList.remove('is-invalid');
    }

    function removeRow(btn) {
        const tbody = btn.closest('tbody');
        if (tbody.querySelectorAll('tr').length <= 1) { alert('最低 1 行は必要です。'); return; }
        btn.closest('tr').remove();
        calculateTotals();
    }

    function clearEditor(addDefaultLines = true) {
        document.getElementById('edit-entry-id').value = '';
        document.getElementById('editing-id-badge').classList.add('d-none');
        document.getElementById('post-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('post-dept').value = '';
        document.getElementById('post-source-type').value = '';
        document.getElementById('post-source-remark').value = '';
        
        // 清空列表
        document.querySelector('.sortable-list[data-side="1"]').innerHTML = '';
        document.querySelector('.sortable-list[data-side="2"]').innerHTML = '';
        
        // 只有当 addDefaultLines 为 true 时才添加空行
        if (addDefaultLines) {
            addLine(1);
            addLine(2);
        }
        
        calculateTotals();
    }

    // 5. 计算合计
    function calculateTotals() {
        let debitTotal = 0, creditTotal = 0;
        document.querySelectorAll('.sortable-list[data-side="1"] .amount-input').forEach(i => debitTotal += parseFloat(i.value)||0);
        document.querySelectorAll('.sortable-list[data-side="2"] .amount-input').forEach(i => creditTotal += parseFloat(i.value)||0);
        
        document.getElementById('total-debit-display').innerText = debitTotal.toLocaleString();
        document.getElementById('total-credit-display').innerText = creditTotal.toLocaleString();
        
        const badge = document.getElementById('balance-status');
        if (Math.abs(debitTotal - creditTotal) < 0.01 && debitTotal > 0) {
            badge.className = 'badge bg-success'; badge.innerText = 'OK'; return true;
        } else {
            badge.className = 'badge bg-danger'; badge.innerText = 'NG (' + (debitTotal - creditTotal).toLocaleString() + ')'; return false;
        }
    }

    // 6. 提交数据 (Side 使用数字 1 或 2)
    function submitJournalEntry() {
        // 0. 预清理：提交前先清除所有红框，让界面清爽
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // 1. 再次确认平衡
        if (!calculateTotals()) { 
            Swal.fire('エラー', '借貸合計が一致していません。', 'error'); 
            return; 
        }

        const entryId = document.getElementById('edit-entry-id').value;
        const linesData = [];
        
        // 2. 定义三个独立的错误标志
        let hasAccountError = false; // 科目错误
        let hasAmountError = false;  // 金额错误
        let hasTaxError = false;     // 税区分错误

        // 3. 定义处理行的函数
        const processRows = (selector, sideCode) => {
            const rows = document.querySelectorAll(selector + ' tr');
            
            rows.forEach(tr => {
                // 获取 DOM 元素
                const accInput = tr.querySelector('.account-input');
                const amountInput = tr.querySelector('.amount-input');
                const taxSelect = tr.querySelector('.tax-select');

                // 获取值 (使用 ?. 防止报错)
                const accId = tr.querySelector('.account-id-hidden')?.value;
                const amount = amountInput?.value;
                const taxValue = taxSelect?.value;

                // 获取辅助信息 (用于提交)
                const subText = tr.querySelector('.sub-input')?.value.trim() || '';
                const partText = tr.querySelector('.partner-input')?.value.trim() || '';
                const subId = tr.querySelector('.sub-id-hidden')?.value || null;
                const partId = tr.querySelector('.partner-id-hidden')?.value || null;

                let rowValid = true;

                // --- 独立校验 1: 勘定科目 ---
                if (!accId) {
                    hasAccountError = true;
                    rowValid = false;
                    if (accInput) accInput.classList.add('is-invalid');
                }

                // --- 独立校验 2: 金额 ---
                if (!amount) {
                    hasAmountError = true;
                    rowValid = false;
                    if (amountInput) amountInput.classList.add('is-invalid');
                }


                // 只有当这一行所有校验都通过时，才加入数据数组
                if (rowValid) {
                    linesData.push({
                        id: tr.getAttribute('data-db-id') || null,
                        side: sideCode,
                        account_id: accId,
                        account_sub_id: subId, 
                        account_sub_name: subText,
                        partner_id: partId,
                        partner_name: partText,
                        tax_type_id: taxValue,
                        amount: amount,
                        remark: ''
                    });
                }
            });
        };

        // 4. 处理借方 (1) 和 贷方 (2)
        processRows('.sortable-list[data-side="1"]', 1);
        processRows('.sortable-list[data-side="2"]', 2);

        // 5. 根据错误类型，分别弹出提示 (或者合并提示)
        // 这里为了用户体验，如果同时有多个错误，我会把它们合并在一个弹窗里，但红框是分开标的
        
        let errorMessage = '';
        if (hasAccountError) errorMessage += '・勘定科目が選択されていません。<br>';
        if (hasAmountError) errorMessage += '・金額が入力されていません。<br>';

        if (errorMessage) {
            Swal.fire('入力エラー', errorMessage, 'warning');
            return;
        }
        
        // 6. 处理部门
        let deptId = null;
        const deptName = document.getElementById('post-dept').value;
        if(deptName) {
            const found = departmentsData.find(d => d.name === deptName);
            deptId = found ? found.id : null;
        }

        const formData = {
            posting_date: document.getElementById('post-date').value,
            department_id: deptId,
            department_name: deptName, 
            source_type: document.getElementById('post-source-type').value,
            remark: document.getElementById('post-source-remark').value,
            lines: linesData,
            _token: csrfToken
        };

        // 7. 发送请求
        const url = entryId ? updateUrlBase.replace('__ID__', entryId) : saveUrl;
        const method = entryId ? 'PUT' : 'POST';
        const btn = document.querySelector('button[onclick="submitJournalEntry()"]');
        
        btn.disabled = true; 
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> ...';

        fetch(url, {
            method: method,
            headers: { 
                'Content-Type': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest', 
                'Accept': 'application/json' 
            },
            body: JSON.stringify(formData)
        })
        .then(res => res.ok ? res.json() : res.json().then(e => { throw new Error(e.message || 'Error'); }))
        .then(data => {
            Swal.fire('成功', data.message || '保存しました', 'success').then(() => location.reload());
        })
        .catch(err => {
            console.error(err);
            Swal.fire('エラー', err.message || '通信エラーが発生しました', 'error');
            btn.disabled = false; 
            btn.innerHTML = '<i class="bi bi-save"></i> 保存';
        });
    }

    // 7. AJAX 获取完整数据
    function fetchAndLoadEntry(entryId) {
        const btn = document.querySelector('button[onclick="submitJournalEntry()"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> 読込中...';

        const fetchUrl = "{{ route('masters.journal_entries.show', '__ID__') }}".replace('__ID__', entryId);

        fetch(fetchUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('データの取得に失敗しました');
            return res.json();
        })
        .then(data => {
            loadEntryToEditorFull(data);
        })
        .catch(err => {
            Swal.fire('エラー', err.message, 'error');
            console.error(err);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    // 8. 渲染回显数据
    function loadEntryToEditorFull(data) {
        clearEditor(false);

        document.getElementById('edit-entry-id').value = data.id;
        document.getElementById('editing-id-val').innerText = data.id;
        document.getElementById('editing-id-badge').classList.remove('d-none');
        document.getElementById('post-date').value = data.posting_date;
        document.getElementById('post-source-type').value = data.source_type || '';
        document.getElementById('post-source-remark').value = data.remark || '';
        
        const deptInput = document.getElementById('post-dept');
        if (data.department && data.department.name) {
            deptInput.value = data.department.name;
        } else if (data.department_id) {
            const found = departmentsData.find(d => d.id == data.department_id);
            deptInput.value = found ? found.name : '';
        }

        if (data.lines && Array.isArray(data.lines)) {
            data.lines.forEach(line => {
                const lineData = {
                    id: line.id,
                    account_id: line.account_id,
                    account_sub_id: line.account_sub_id,
                    partner_id: line.partner_id,
                    tax_type_id: line.tax_type_id,
                    amount: line.amount,
                    side: line.side, // 直接使用数字 1 或 2
                    account_full_text: line.account_full_text || '',
                    account_sub_name: line.account_sub ? (line.account_sub.display_name || `${line.account_sub.code||''} - ${line.account_sub.name}`) : '',
                    partner_name: line.partner ? line.partner.name : ''
                };

                addLine(line.side, lineData);
            });
        }
        
        calculateTotals();
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchForm = document.getElementById('searchForm');
        const perPageSelect = document.getElementById('perPageSelect');

        // 1. 监听“每页显示行数”变化
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                // 将选择的行数作为参数添加到表单中
                let input = document.querySelector('input[name="per_page"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'per_page';
                    searchForm.appendChild(input);
                }
                input.value = this.value;
                searchForm.submit(); // 自动提交表单
            });
        }


    });

    document.addEventListener('DOMContentLoaded', function () {
        const searchAccountInput = document.getElementById('search-account-input');
        const searchAccountIdInput = document.getElementById('search-account-id');
        const accountListSearch = document.getElementById('account-list-search');
        const allAccounts = @json($accounts ?? []);

        if (searchAccountInput) {
            // --- 修改点 1: 移除 input 事件中的自动填充逻辑 ---
            // 现在 input 事件只负责“过滤下拉列表”，不负责“修改输入框的值”
            searchAccountInput.addEventListener('input', function() {
                const inputValue = this.value.trim().toLowerCase();
                
                // 如果输入为空，不做处理
                if (!inputValue) {
                    // 这里不要清空 hidden，或者根据需求处理
                    return;
                }

                // 过滤选项
                const filteredOptions = allAccounts.filter(acc => 
                    (acc.code + ' - ' + acc.name).toLowerCase().includes(inputValue) || 
                    acc.name.toLowerCase().includes(inputValue) || 
                    acc.code.toLowerCase().includes(inputValue) 
                );

                // 动态更新 Datalist
                accountListSearch.innerHTML = '';
                filteredOptions.forEach(acc => {
                    const option = document.createElement('option');
                    option.value = acc.code + ' - ' + acc.name;
                    accountListSearch.appendChild(option);
                });

                // ⚠️ 关键：删除了这里 if (filteredOptions.length === 1) { this.value = ... } 的代码
                // 让用户自己决定选什么，不要强制改写 this.value
            });

            // --- 修改点 2: 依赖 change 事件来保存 ID ---
            // 只有当用户选中（或者失去焦点确认）时，才去匹配 ID
            searchAccountInput.addEventListener('change', function() {
                const selectedValue = this.value;
                const matchedAccount = allAccounts.find(acc => 
                    (acc.code + ' - ' + acc.name) === selectedValue 
                );
                if (matchedAccount) {
                    searchAccountIdInput.value = matchedAccount.id;
                } else {
                    // 如果用户输入了不存在的值，可以清空或者保留
                    // searchAccountIdInput.value = ''; 
                }
            });
        }
    });

    function sortTable(field) {
        // 1. 获取当前页面所有的 URL 参数
        const urlParams = new URLSearchParams(window.location.search);
        
        // 2. 获取当前的排序字段和顺序
        const currentSortField = urlParams.get('sort_field');
        const currentSortOrder = urlParams.get('sort_order');

        // 3. 判断逻辑：
        // 如果点击的是同一个字段，则切换升序/降序（默认降序 desc）
        // 如果点击的是不同字段，则重置为降序
        let newOrder = 'desc';
        if (field === currentSortField && currentSortOrder === 'desc') {
            newOrder = 'asc';
        }

        // 4. 设置新的参数
        urlParams.set('sort_field', field);
        urlParams.set('sort_order', newOrder);

        // 5. 更新页面 URL（会触发页面刷新并重新加载数据）
        // 注意：这里假设你的路由是 GET 请求，这样刷新后参数依然保留
        window.location.search = urlParams.toString();
    }

    // --- 可选：页面加载时，根据当前的排序状态高亮箭头 ---
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const sortField = urlParams.get('sort_field');
        const sortOrder = urlParams.get('sort_order');

        // 找到所有被点击的列（通过 data-sort 属性匹配）
        const header = document.querySelector(`th[data-sort="${sortField}"]`);
        if (header) {
            const activeIcon = header.querySelector(`.sort-icon.${sortOrder}`);
            if (activeIcon) {
                activeIcon.style.color = '#0d6efd'; // 激活时变蓝色 (Bootstrap primary color)
                activeIcon.style.opacity = '1';
            }
        }
    });
</script>

@endsection