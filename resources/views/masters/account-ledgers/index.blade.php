@extends('layouts.app')
@section('title', '勘定元帳')
@section('content')
    <div class="container-fluid">
        
        <!-- 标题与新建按钮 -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4><i class="bi bi-list-task me-2 text-primary"></i>勘定元帳</h4>
            <!-- 如果需要导出按钮，可以放在这里 -->
        </div>

        <!-- 成功/错误提示 (保持不变) -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- 搜索区域 (已修改为仅包含年月选择) -->
        <div class="mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('masters.account-ledgers.index') }}" class="d-flex align-items-end gap-2">

                        <!-- 1. 区分下拉 (放在左侧) -->
                        <div style="min-width: 200px;">
                            <label class="form-label small text-muted mb-1" style="display: block; margin-bottom: 4px;">区分</label>
                            <input 
                                type="text" 
                                name="category_name" 
                                class="form-control form-control-sm" 
                                list="category-list" 
                                placeholder="区分を入力..."
                                value="{{ request('category_name') }}"
                                style="border-color: #E5E7EB;"
                            >
                            <datalist id="category-list">
                                @foreach($categories as $category)
                                    <option value="{{ $category->name }}">
                                @endforeach
                            </datalist>
                        </div>

                        <!-- 2. 按钮区域 (紧挨着区分) -->
                        <div class="d-flex gap-2" style="margin-top: 8px;">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-search"></i> 表示
                            </button>
                            
                            <!-- 清除按钮 -->
                            @if(request()->hasAny(['category_name']))
                                <a href="{{ route('masters.account-ledgers.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> クリア
                                </a>
                            @endif
                        </div>

                        <!-- 3. 占位符 (将日期框推到最右边) -->
                        <div class="flex-grow-1"></div>
                            <!-- 外层容器：靠右对齐 -->
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                
                                <div class="flex-shrink-0">
                                    <!-- 标题 -->
                                    <label class="form-label mb-1 text-muted d-block" style="font-size: 0.75rem;">周期/月份</label>
                                    
                                    <div class="d-flex align-items-center">
                                        <!-- 1. 周期下拉框 -->
                                        <div class="position-relative me-2">
                                            <!-- 防止自动填充 -->
                                            <input type="text" style="display: none;" tabindex="-1" autocomplete="off">
                                            
                                            <select id="periodSelect" name="period_id" class="form-control form-control-sm" 
                                                style="font-size: 0.85rem; padding-right: 25px; appearance: none; -webkit-appearance: none; -moz-appearance: none; background-color: #fff; min-width: 120px;">
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
                                        <div id="month-select-container" class="d-flex align-items-center">
                                            @foreach($months as $key => $monthName)
                                                @php
                                                    $isActive =  ($yearmonth == $monthName);;
                                                @endphp
                                                <button type="submit" 
                                                        name="yearmonth" 
                                                        value="{{ $monthName }}"
                                                        onclick="document.getElementById('searchForm').submit();"
                                                        class="btn btn-sm ms-1 p-0 px-1 {{ $isActive ? 'btn-primary' : 'btn-outline-primary' }}" 
                                                        style="min-width: 30px; font-size: 1.0rem; {{ $isActive ? 'background-color: #0d6efd; border-color: transparent; color: white !important;' : 'border-color: #E5E7EB;' }}">
                                                    {{ $key }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>


        <!-- 表格区域 (保持不变，但建议替换为表格样式) -->
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 table-striped align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center" style="width: 60px;">ID</th>
                            <th class="text-center" style="width: 100px;">区分</th>
                            <th class="text-center" style="width: 250px;">科目名</th>
                            <!-- 操作列宽度调整 -->
                            <th class="text-center" style="width: 160px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td class="text-center text-muted small">{{ $account->id }}</td>
                                <td class="text-center text-muted small">{{ $account->category->name ?? '' }}</td>
                                <td class="text-center ps-3"><span class="fw-medium">{{ $account->name }}</span></td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <!-- 原有的元帳作成按钮 -->
                                        <a 
                                            href="javascript:void(0)" 
                                            class="btn btn-sm btn-outline-success open-ledger-modal"
                                            data-url="{{ route('masters.account-ledgers.generate', $account->id) }}"
                                            data-account-name="{{ $account->name }}"
                                            data-account-id="{{ $account->id }}"
                                            title="元帳作成"
                                        >
                                            <i class="bi bi-journal-plus"></i> 元帳作成
                                        </a>

                                        <!-- 新增的 PDF 下载按钮 -->
                                        <a href="javascript:void(0)" 
                                        class="btn btn-sm btn-outline-primary open-pdf-btn" 
                                        data-base-url="{{ route('masters.account-ledgers.pdf') }}"
                                        title="PDFダウンロード"
                                        data-account-id="{{ $account->id }}">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    @if(request('year_month'))
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2 d-block"></i>
                                            <p class="mb-0 fw-bold">該当する勘定科目が見つかりませんでした</p>
                                            <p class="small">検索条件を見直してください</p>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-calendar3 display-6 mb-2 d-block"></i>
                                            <p class="mb-0 fw-bold">表示する年月を選択してください</p>
                                            <p class="small">検索バーから年月を選択してください</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 分页区域 (完全复用原有逻辑) -->
        @if($accounts->hasPages() || $accounts->total() > 0)
            <div class="mt-4">
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                    <!-- 1. 左侧：行数选择器 -->
                    <div class="d-flex align-items-center">
                        <label for="per_page_select" class="form-label small text-muted mb-0 me-2">
                            表示件数:
                        </label>
                        <select id="per_page_select" class="form-select form-select-sm" style="width: auto;">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                        </select>
                    </div>
                    
                    <!-- 2. 中间：分页链接 -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item {{ $accounts->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $accounts->previousPageUrl() }}">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            @php 
                                $current = $accounts->currentPage(); 
                                $last = $accounts->lastPage(); 
                                $start = max(1, $current - 2); 
                                $end = min($last, $current + 2); 
                            @endphp
                            @if($start > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $accounts->url(1) }}">1</a>
                                </li>
                                @if($start > 2)
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                @endif
                            @endif
                            @for($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $accounts->url($i) }}">{{ $i }}</a>
                                </li>
                            @endfor
                            @if($end < $last)
                                @if($end < $last - 1)
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $accounts->url($last) }}">{{ $last }}</a>
                                </li>
                            @endif
                            <li class="page-item {{ !$accounts->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $accounts->nextPageUrl() }}">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                
                <!-- 3. 底部：统计信息 -->
                <div class="text-center text-muted small mt-2">
                    表示中：{{ $accounts->firstItem() ?? 0 }} - {{ $accounts->lastItem() ?? 0 }} / 全 {{ $accounts->total() }} 件
                </div>
            </div>
        @endif
    </div>


    <div class="modal fade" id="ledgerModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ledgerModalLabel">総勘定元帳</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- 下载按钮 (暂时隐藏或置灰，等数据加载完再操作) -->

                    <!-- 账簿表格 -->
                    <div id="tableContainer">
                        <table class="table table-bordered table-sm" id="ledgerTable">
                            <thead>
                                <tr>
                                    <th>日付</th>
                                    <th>勘定科目</th>
                                    <th>補助科目/税区分</th>
                                    <th>借方</th>
                                    <th>貸方</th>
                                    <th>残高</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- 数据将在这里被动态插入 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    // 1. 获取日期输入框 (你之前的年份选择框)
    const periodSelect = document.getElementById('periodSelect'); // 获取周期下拉框元素
    const urlParams = new URLSearchParams(window.location.search); // 从当前URL中获取参数
    const yearMonthValue = urlParams.get('yearmonth'); // 获取URL中的 yearmonth 参数 (格式如: 2026-04)

    // 2. 为所有“元帳作成”按钮添加点击事件
    document.querySelectorAll('.open-ledger-modal').forEach(button => {
        button.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const accountName = this.getAttribute('data-account-name');

            // 设置模态框标题
            document.getElementById('ledgerModalLabel').textContent = `総勘定元帳 - ${accountName}`;

            // 显示模态框
            const modal = new bootstrap.Modal(document.getElementById('ledgerModal'));
            modal.show();

            // --- 关键：开始 AJAX 请求 ---
            const periodId = periodSelect ? periodSelect.value : '';
            const requestUrl = `${url}?period_id=${periodId}&yearmonth=${yearMonthValue}`;
            document.querySelector('#ledgerTable tbody').innerHTML = '<tr><td colspan="6" class="text-center">データ読み込み中...</td></tr>';

            fetch(requestUrl)
                .then(response => {
                    if (!response.ok) throw new Error('网络响应错误: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    const tbody = document.querySelector('#ledgerTable tbody');
                    let currentBalance = 0;
                    tbody.innerHTML = '';

                    if (data.opening_balance > 0) {

                        const openingTr = document.createElement('tr');
                        openingTr.className = 'table-warning';
                        openingTr.innerHTML = `
                            <td colspan="3" class="text-center fw-bold text-success">前月繰越</td>
                            <td class="text-end">${data.opening_balance >= 0 ? Math.abs(data.opening_balance).toLocaleString('ja-JP') : ''}</td>
                            <td class="text-end">${data.opening_balance < 0 ? Math.abs(data.opening_balance).toLocaleString('ja-JP') : ''}</td>
                            <td class="text-end fw-bold">${Math.abs(data.opening_balance).toLocaleString('ja-JP')}</td>
                        `;
                        tbody.appendChild(openingTr);
                        
                        // 更新初始余额变量
                        currentBalance = Math.abs(data.opening_balance);
                    }

                    // 如果没有数据
                    if (!data.rows || data.rows.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">該当月のデータはありません</td></tr>';
                        return;
                    }

                    // --- 单次循环处理逻辑 (性能最优) ---
                    
                    let monthlyJieTotal = 0; // 当月借方累计
                    let monthlyDaiTotal = 0; // 当月贷方累计
                    let lastMonthKey = '';  // 记录上一行的月份
                    let initialBalance = currentBalance; 

                    data.rows.forEach((row, index) => {
                        // --- A. 日期与月份处理 ---
                        let dateStr = row.date;
                        let currentMonthKey = '';

                        // 根据日期格式提取 "YYYY-MM"
                        if (dateStr.includes('-')) {
                            currentMonthKey = dateStr.substring(0, 7);
                        } else if (dateStr.includes('/')) {
                            const parts = dateStr.split('/');
                            currentMonthKey = `20${parts[0]}-${parts[1]}`; // 假设是 YY/MM 格式
                        }



                        // --- B. 汇总逻辑：月份切换检测 (核心点) ---
                        // 如果不是第一行，且当前月份 != 上一行的月份
                        // 说明：上个月的数据已经全部读取完毕，可以画汇总线了
                        if (index > 0 && currentMonthKey !== lastMonthKey) {
                            // 渲染上个月的汇总行
                            renderSummaryRow(tbody, lastMonthKey, monthlyJieTotal, monthlyDaiTotal);
                            
                            // 重置当月累计器，开始计算新月份
                            monthlyJieTotal = 0;
                            monthlyDaiTotal = 0;
                        }

                        if (index > 0 && currentMonthKey !== lastMonthKey) {
                            // 创建 "前月繰越" 行
                            const openingTr = document.createElement('tr');
                            openingTr.className = 'table-warning'; // 可以加个背景色区分
                            openingTr.innerHTML = `
                                <td colspan="3" class="text-center fw-bold text-success">前月繰越</td>
                                <td class="text-end">${currentBalance >= 0 ? Math.abs(currentBalance).toLocaleString('ja-JP') : ''}</td>
                                <td class="text-end">${currentBalance < 0 ? Math.abs(currentBalance).toLocaleString('ja-JP') : ''}</td>
                                <td class="text-end fw-bold">${currentBalance.toLocaleString('ja-JP')}</td>
                            `;
                            tbody.appendChild(openingTr);
                        }

                        // --- C. 金额处理 (去掉小数点，取整) ---
                        let jieVal = Math.round(parseFloat(String(row.jie_money).replace(/,/g, '')) || 0);
                        let daiVal = Math.round(parseFloat(String(row.dai_money).replace(/,/g, '')) || 0);

                        // 累加到当月总额 (用于下一次循环判断时显示)
                        monthlyJieTotal += jieVal;
                        monthlyDaiTotal += daiVal;

                        // 计算总余额 (带格式显示)
                        currentBalance = currentBalance + jieVal - daiVal;

                        // --- D. 渲染当前数据行 ---
                        const tr = document.createElement('tr');

                        // 摘要 + 税区分
                        const taxText = row.tax_category ? `（${row.tax_category}）` : '';
                        const summary = (row.sub_account_name || '') + ' ' + taxText;

                        tr.innerHTML = `
                            <td>${row.date}</td>
                            <td>${row.account_name}</td>
                            <td class="text-muted">${summary}</td>
                            <td class="text-end">${jieVal.toLocaleString('ja-JP')}</td>
                            <td class="text-end">${daiVal.toLocaleString('ja-JP')}</td>
                            <td class="text-end fw-bold">${currentBalance.toLocaleString('ja-JP')}</td>
                        `;
                        tbody.appendChild(tr);

                        // --- E. 更新状态 ---
                        lastMonthKey = currentMonthKey;
                    });

                    // --- 3. 收尾：渲染最后一个月的汇总 ---
                    // 循环结束后，最后一个切换点不会被触发，必须手动画一次
                    if (data.rows.length > 0) {
                        renderSummaryRow(tbody, lastMonthKey, monthlyJieTotal, monthlyDaiTotal);
                    }

                    // 启用下载按钮
                    const downloadBtn = document.getElementById('downloadBtn');
                    if (downloadBtn) downloadBtn.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('データの読み込みに失敗しました');
                });
        });
    });

    // --- 辅助函数：渲染汇总行 ---
    function renderSummaryRow(tbody, month, jieTotal, daiTotal) {
        if (!month) return;

        const sumTr = document.createElement('tr');
        sumTr.className = 'table-secondary fw-bold';
        sumTr.innerHTML = `
            <td colspan="3" class="text-end">当月合計 (${month})</td>
            <td class="text-end">${jieTotal.toLocaleString('ja-JP')}</td>
            <td class="text-end">${daiTotal.toLocaleString('ja-JP')}</td>
            <td></td>
        `;
        tbody.appendChild(sumTr);
    }
});

document.querySelectorAll('.open-pdf-btn').forEach(button => {
    button.addEventListener('click', function () {
        const baseUrl = this.getAttribute('data-base-url');
        const accountId = this.getAttribute('data-account-id');
        const periodSelect = document.getElementById('periodSelect'); // 获取周期下拉框元素
        const urlParams = new URLSearchParams(window.location.search); // 从当前URL中获取参数
        const yearMonthValue = urlParams.get('yearmonth'); // 获取URL中的 yearmonth 参数 (格式如: 2026-04)
        
        const params = new URLSearchParams({
            id: accountId,
            period_id: periodSelect.value, 
            yearmonth: yearMonthValue 
        });
        
        const url = `${baseUrl}?${params}`;
        window.open(url, '_blank'); // 在新标签页打开，避免打断当前页面操作
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('periodSelect');
    
    // 检查元素是否存在（防止报错）
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            // 获取当前的 yearmonth 参数
            const urlParams = new URLSearchParams(window.location.search);
            const yearMonthValue = urlParams.get('yearmonth');
            
            // 如果没有选择月份，可以给个默认值或者提示，这里假设直接提交
            // 重新构建当前页面的 URL
            let newUrl = window.location.pathname + '?period_id=' + this.value;
            
            
            // 跳转页面，触发后端查询
            window.location.href = newUrl;
        });
    }
});
</script>
@endsection