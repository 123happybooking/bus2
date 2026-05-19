@extends('layouts.app')

@section('title', '試算表')

@section('content')
<div class="container-fluid">
    <!-- 1. 标题 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cash-coin text-success me-2"></i>試算表</h3>
        <a id="downloadPdf" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> PDFダウンロード
        </a>
    </div>

    <!-- 2. 搜索区域 -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- 表单开始 -->
            <form id="searchForm" method="GET" action="{{ route('masters.account-sums.index') }}">
                
                <div class="row g-3 align-items-end">
                    
                    <!-- 左侧/主要筛选区域：周期与月份 -->
                    <div class="col-auto">
                        <div class="d-flex flex-column">
                            <!-- 标签 -->
                            <label class="form-label mb-1 text-muted small">
                                <i class="bi bi-calendar-event"></i> 決算期
                            </label>
                            
                            <!-- 控件容器 -->
                            <div class="d-flex align-items-center gap-2">
                                
                                <!-- 1. 周期下拉框 -->
                                <div class="position-relative">
                                    <select id="periodSelect" name="period_id" class="form-select form-select-sm" 
                                        style="min-width: 140px;"
                                        > 
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

                                <!-- 2. 月份快速筛选 (1-12月) -->
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

    <!-- 3. 表主体 -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 text-end align-middle">
                    <thead class="table-light">
                        <tr>
                             <!-- 借方残高 -->
                            <th class="text-end" style="width: 15%;">借方残高</th>
                            <!-- 借方合计 -->
                            <th class="text-end" style="width: 15%;">借方合計</th>
                           
                            <!-- 勘定科目 -->
                            <th class="text-center" style="width: 15%;">勘定科目</th>
                            <!-- 贷方合计 -->
                            <th class="text-end" style="width: 15%;">貸方合計</th>
                            <!-- 贷方残高 -->
                            <th class="text-end" style="width: 15%;">貸方残高</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotalJie = 0;
                            $grandTotalDai = 0;
                            $grandTotalBalanceJie = 0;
                            $grandTotalBalanceDai = 0;
                        @endphp

                        @forelse($data as $row)
                            @php
                                // 计算本期余额方向
                                // 逻辑：期末余额 = 期初 + 本期借 - 本期贷 (假设期初为0或已包含在逻辑中，这里仅根据控制器传回的期末余额判断)
                                // 注意：控制器中 $row->ending_balance 是最终的期末余额数值。
                                // 如果余额 > 0 通常为借方余额，< 0 为贷方余额 (取决于具体会计准则，这里假设正数为借方，负数为贷方)
                                
                                $balance = $row->total_jie - $row->total_dai;
                                if($balance > 0){
                                    $balanceJie = $balance;
                                }elseif($balance < 0){
                                    $balanceDai = $balance;
                                }else{
                                    $balanceJie = 0;
                                    $balanceDai = 0;
                                }
                                $balanceJie = $balance > 0 ? $balance : 0;
                                $balanceDai = $balance < 0 ? abs($balance) : 0;

                                // 累计求和
                                $grandTotalJie += $row->total_jie;
                                $grandTotalDai += $row->total_dai;
                                $grandTotalBalanceJie += $balanceJie;
                                $grandTotalBalanceDai += $balanceDai;
                            @endphp
                            <tr>
                                <td>{{ $balanceJie > 0 ? number_format($balanceJie, 0) : '-' }}</td>
                                <td>{{ number_format($row->total_jie, 0) }}</td>
                                <td class="text-center">{{ $row->name }}</td>
                                <td>{{ number_format($row->total_dai, 0) }}</td>
                                
                                <td>{{ $balanceDai > 0 ? number_format($balanceDai, 0) : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    暂无数据
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>{{ number_format($grandTotalBalanceJie, 0) }}</td>
                            
                            <td>{{ number_format($grandTotalJie, 0) }}</td>
                            <td class="text-center">合计</td>
                            <td>{{ number_format($grandTotalDai, 0) }}</td>
                            
                            <td>{{ number_format($grandTotalBalanceDai, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<style>
/* 保持原有的打印样式 */
@media print {
    .btn, .card-header, .form-control, .form-select { display: none !important; }
    body * { visibility: hidden; }
    .container-fluid, .container-fluid * { visibility: visible; }
    .container-fluid { position: absolute; left: 0; top: 0; width: 100%; }
    .card { box-shadow: none !important; border: none !important; }
    table { width: 100% !important; }
}
/* 表格微调 */
.table th {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('periodSelect');
    
    // 检查元素是否存在（防止报错）
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            // 获取当前的 yearmonth 参数
            const urlParams = new URLSearchParams(window.location.search);
            const yearMonthValue = urlParams.get('yearmonth');
            
            // 重新构建当前页面的 URL
            let newUrl = window.location.pathname + '?period_id=' + this.value;
            
            // 如果有月份参数，带上
            if(yearMonthValue){
                newUrl += '&yearmonth=' + yearMonthValue;
            }
            
            // 跳转页面，触发后端查询
            window.location.href = newUrl;
        });
    }
});

const currentParams = new URLSearchParams(window.location.search);

const baseUrl = "{{ route('masters.account-sums.pdf') }}";

// 构建最终 URL
let finalUrl = baseUrl;
if (currentParams.toString()) {
    // 判断 baseUrl 是否已包含参数
    finalUrl += baseUrl.includes('?') ? '&' : '?';
    finalUrl += currentParams.toString();
}

// 赋值给按钮
document.getElementById('downloadPdf').href = finalUrl;
</script>
@endsection