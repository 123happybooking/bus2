@extends('layouts.app')

@section('title', '預金出納帳')

@section('content')
<div class="container-fluid">
    <!-- 1. 标题 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>預金出納帳</h3>
    </div>

    <!-- 2. 搜索区域 (保持你原有的代码) -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="searchForm" method="GET" action="{{ route('masters.account-deposit.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-auto">
                        <div class="d-flex flex-column">
                            <label class="form-label mb-1 text-muted small">
                                <i class="bi bi-calendar-event"></i> 周期/月份
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="position-relative">
                                    <select id="periodSelect" name="period_id" class="form-select form-select-sm" style="min-width: 140px;"> 
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
                                <div id="month-select-container" class="d-flex align-items-center">
                                    @foreach($months as $key => $monthName)
                                        @php
                                            $isActive = ($yearmonth == $monthName);
                                        @endphp
                                        <button type="submit" name="yearmonth" value="{{ $monthName }}"
                                            class="btn btn-sm ms-1 p-0 px-1 {{ $isActive ? 'btn-primary' : 'btn-outline-primary' }}" 
                                            style="min-width: 30px; font-size: 1.0rem; {{ $isActive ? 'background-color: #0d6efd; border-color: transparent; color: white !important;' : 'border-color: #E5E7EB;' }}">
                                            {{ $key }}
                                        </button>
                                    @endforeach
                                </div>

                                <div class="position-relative">
                                    <select id="subAccountSelect" name="sub_account_id" class="form-select form-select-sm" style="min-width: 140px;"> 
                                        <option value="0"
                                            {{ $sub_account_id == 0 ? 'selected' : '' }}>
                                            全部
                                        </option>    
                                        @foreach($subAccounts as $subAccount)
                                            <option value="{{ $subAccount->id }}"
                                                {{ $sub_account_id == $subAccount->id ? 'selected' : '' }}>
                                                {{ $subAccount->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. 表主体：嵌入总账 (勘定元帳) -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <!-- 总账专属样式 (局部作用域，避免污染全局) -->
            <style>
                .ledger-table { font-family: "Meiryo", sans-serif; font-size: 13px; color: #333; width: 100%; border-collapse: collapse; table-layout: fixed; }
                .ledger-table th, .ledger-table td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: left; vertical-align: middle; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                .ledger-table th { background-color: #f8f9fa; font-weight: bold; text-align: center; }
                .ledger-table .col-date { width: 10%; text-align: center; }
                .ledger-table .col-account { width: 15%; }
                .ledger-table .col-summary { width: 35%; }
                .ledger-table .col-debit { width: 12%; text-align: right; }
                .ledger-table .col-credit { width: 12%; text-align: right; }
                .ledger-table .col-balance { width: 16%; text-align: right; }
                .ledger-table .summary-row td { background-color: #e9ecef !important; font-weight: bold; }
                .ledger-table .opening-row td { background-color: #f8f9fa; font-weight: bold; color: #0d6efd; }
            </style>

            <div class="p-3 border-bottom">
                @if(!empty($datas['start_date']) || !empty($datas['end_date']))
                    <small class="text-muted">
                        期間：{{ $datas['start_date'] ?? '---' }} ～ {{ $datas['end_date'] ?? '---' }}
                    </small>
                @endif
            </div>

            <div class="table-responsive">
                <table class="ledger-table">
                    <thead>
                        <tr>
                            <th class="col-date">日付</th>
                            <th class="col-account">勘定科目</th>
                            <th class="col-summary">補助科目 / 税区分</th>
                            <th class="col-debit">收入金額</th>
                            <th class="col-credit">支出金額</th>
                            <th class="col-balance">残高</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // 初始化变量，假设 $datas['rows'] 是明细数组，$datas['opening_balance'] 是期初余额
                            $rows = $datas['rows'] ?? [];
                            $opening_balance = $datas['opening_balance'] ?? 0;
                            $mark = $datas['mark'] ?? 1; // 1为正数显示，其他为绝对值
                            $currentBalance = $opening_balance;
                            $monthlyJieTotal = 0;
                            $monthlyDaiTotal = 0;
                            $lastMonthKey = '';
                            $rowCount = 0;
                        @endphp

                        @forelse($rows as $index => $row)
                            @php
                                // 1. 数据清洗
                                $dateStr = $row['date'] ?? '';
                                $jieVal = (int) round((float) str_replace(',', '', $row['jie_money'] ?? '') ?: 0);
                                $daiVal = (int) round((float) str_replace(',', '', $row['dai_money'] ?? '') ?: 0);

                                // 2. 提取月份 Key (YYYY-MM)
                                $currentMonthKey = '';
                                if (strpos($dateStr, '-') !== false) {
                                    $currentMonthKey = substr($dateStr, 0, 7);
                                } elseif (strpos($dateStr, '/') !== false) {
                                    $parts = explode('/', $dateStr);
                                    $currentMonthKey = "20{$parts[0]}-{$parts[1]}";
                                }

                                // 3. 累加当月总额
                                $monthlyJieTotal += $jieVal;
                                $monthlyDaiTotal += $daiVal;

                                // 4. 计算全局余额
                                $currentBalance += $jieVal - $daiVal;

                                // 5. 判断是否切换月份
                                $shouldRenderSummary = ($index > 0 && !empty($lastMonthKey) && $currentMonthKey !== $lastMonthKey);
                            @endphp

                            {{-- A. 渲染上个月的汇总行 --}}
                            @if($shouldRenderSummary)
                                <tr class="summary-row">
                                    <td colspan="3" class="text-end">当月合計 ({{ $lastMonthKey }})</td>
                                    <td class="text-right text-end">{{ number_format($monthlyJieTotal - $jieVal) }}</td>
                                    <td class="text-right text-end">{{ number_format($monthlyDaiTotal - $daiVal) }}</td>
                                    <td ></td>
                                </tr>
                                @php
                                    // 重置总额（保留当前行数据）
                                    $monthlyJieTotal = $jieVal;
                                    $monthlyDaiTotal = $daiVal;
                                @endphp
                            @endif

                            {{-- B. 渲染期初结转行 (首行或跨月) --}}
                            @if($index === 0 || $shouldRenderSummary)
                                <tr class="opening-row">
                                    <td colspan="3" class="text-end">繰越金額</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right text-end">
                                        @php
                                            // 计算结转时的余额（减去当前行的变动）
                                            $carryBalance = $currentBalance - $jieVal + $daiVal;
                                            if($index === 0) $carryBalance = $opening_balance;
                                        @endphp
                                        {{ $mark==1 ? number_format($carryBalance) : number_format(abs($carryBalance)) }}
                                    </td>
                                </tr>
                            @endif

                            {{-- C. 渲染当前数据行 --}}
                            <tr>
                                <td class="text-center col-date">{{ $dateStr }}</td>
                                <td class="col-account">{{ $row['account_name'] ?? '' }}</td>
                                <td class="col-summary">
                                    {{ $row['sub_account_name'] ?? '' }}
                                    @if(!empty($row['tax_category']))
                                        <span class="badge bg-secondary ms-1">{{ $row['tax_category'] }}</span>
                                    @endif
                                </td>
                                <td class="text-right col-debit">
                                    @if($jieVal > 0) {{ number_format($jieVal) }} @endif
                                </td>
                                <td class="text-right col-credit">
                                    @if($daiVal > 0) {{ number_format($daiVal) }} @endif
                                </td>
                                <td class="text-right col-balance">
                                    {{ $mark==1 ? number_format($currentBalance) : number_format(abs($currentBalance)) }}
                                </td>
                            </tr>

                            @php
                                $lastMonthKey = $currentMonthKey;
                                $rowCount++;
                            @endphp
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    該当するデータがありません。
                                </td>
                            </tr>
                        @endforelse

                        {{-- D. 收尾：渲染最后一个月的汇总行 --}}
                        @if($rowCount > 0)
                            <tr class="summary-row">
                                <td colspan="3" class="text-end">当月合計 ({{ $lastMonthKey }})</td>
                                <td class="text-right text-end">{{ number_format($monthlyJieTotal) }}</td>
                                <td class="text-right text-end">{{ number_format($monthlyDaiTotal) }}</td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* 页面原有的打印样式 */
@media print {
    .btn, .card-header, .form-control, .form-select, #month-select-container { display: none !important; }
    body * { visibility: hidden; }
    .container-fluid, .container-fluid * { visibility: visible; }
    .container-fluid { position: absolute; left: 0; top: 0; width: 100%; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('periodSelect');
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const yearMonthValue = urlParams.get('yearmonth');
            const sub_account_id = urlParams.get('sub_account_id');
            let newUrl = window.location.pathname + '?period_id=' + this.value;
            if(yearMonthValue){
                newUrl += '&yearmonth=' + yearMonthValue;
            }
            if(sub_account_id){
                newUrl += '&sub_account_id=' + sub_account_id;
            }
            window.location.href = newUrl;
        });
    }


    const subAccountSelect = document.getElementById('subAccountSelect');
    if (subAccountSelect) {
        subAccountSelect.addEventListener('change', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const yearMonthValue = urlParams.get('yearmonth');
            const period_id = urlParams.get('period_id');
            let newUrl = window.location.pathname + '?sub_account_id=' + this.value;
            if(yearMonthValue){
                newUrl += '&yearmonth=' + yearMonthValue;
            }
            if(period_id){
                newUrl += '&period_id=' + period_id;
            }

            window.location.href = newUrl;
        });
    }
});
</script>
@endsection