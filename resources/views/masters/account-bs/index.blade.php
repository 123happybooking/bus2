@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <!-- 1. 头部导航栏 -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-balance-scale fa-sm text-gray-400 mr-2"></i>
            貸借対照表 (Balance Sheet)
        </h1>
        <a id="downloadPdf" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> PDFダウンロード
        </a>
    </div>

    <!-- 2. 搜索/日期选择栏 -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.account-bs.index') }}">
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
                                <!-- 隐藏的月份输入框，用于提交表单 -->
                                <!-- <input type="hidden" name="yearmonth" id="hiddenMonthInput" value="{{ $yearmonth }}"> -->
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- 3. 报表主体 -->
    <div class="row justify-content-center">
        <!-- 左侧：资产部分 -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">【資産 (Assets)】</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60%">科目</th>
                                    <th style="width: 40%" class="text-end">金額</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assetOrder as $catId => $categoryName)
                                    @if (isset($assets[$categoryName]))
                                        <!-- 大分类标题 -->
                                        <tr class="table-secondary">
                                            <td colspan="2"><strong>{{ $categoryName }}</strong></td>
                                        </tr>
                                        
                                        <!-- 明细科目 -->
                                        @foreach ($assets[$categoryName]['accounts'] as $account)
                                            <tr>
                                                <td style="padding-left: 2.5rem;">
                                                    <small class="text-muted">{{ $account['code'] }}</small> 
                                                    {{ $account['name'] }}
                                                </td>
                                                <td class="text-end {{ $account['is_negative'] ? 'text-danger' : '' }}">
                                                    {{ number_format($account['amount']) }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- 小计 -->
                                        <tr class="fw-bold border-top">
                                            <td class="text-end pe-4">{{ $categoryName }} 合計</td>
                                            <td class="text-end pe-4">{{ number_format($assets[$categoryName]['total']) }}</td>
                                        </tr>
                                    @endif
                                @endforeach

                                <!-- 资产总计 -->
                                <tr class="table-success fw-bold fs-5">
                                    <td class="text-end pe-4">【資産 合計】</td>
                                    <td class="text-end pe-4">{{ number_format($totalAssets) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧：负债与纯资产部分 -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">【負債および純資産 (Liabilities & Equity)】</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60%">科目</th>
                                    <th style="width: 40%" class="text-end">金額</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($liabilityOrder as $catId => $categoryName)
                                    @if (isset($liabilities[$categoryName]))
                                        <!-- 大分类标题 -->
                                        <tr class="table-secondary">
                                            <td colspan="2"><strong>{{ $categoryName }}</strong></td>
                                        </tr>
                                        
                                        <!-- 明细科目 -->
                                        @foreach ($liabilities[$categoryName]['accounts'] as $account)
                                            <tr>
                                                <td style="padding-left: 2.5rem;">
                                                    <small class="text-muted">{{ $account['code'] }}</small> 
                                                    {{ $account['name'] }}
                                                </td>
                                                <td class="text-end {{ $account['is_negative'] ? 'text-danger' : '' }}">
                                                    {{ number_format($account['amount']) }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        @if($categoryName == '純資産')
                                            <tr>
                                                <td style="padding-left: 2.5rem;">
                                                    繰越利益剰余金
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($netIncome) }}
                                                </td>
                                            </tr>

                                        @endif
                                        


                                        <!-- 小计 -->
                                         @if($categoryName == '純資産')
                                            <tr class="fw-bold border-top">
                                                <td class="text-end pe-4">{{ $categoryName }} 合計</td>
                                                <td class="text-end pe-4">{{ number_format($liabilities[$categoryName]['total'] + $netIncome) }}</td>
                                            </tr>
                                         @else
                                        <tr class="fw-bold border-top">
                                            <td class="text-end pe-4">{{ $categoryName }} 合計</td>
                                            <td class="text-end pe-4">{{ number_format($liabilities[$categoryName]['total'] ) }}</td>
                                        </tr>
                                        @endif
                                    @endif
                                @endforeach

                                <!-- 负债与权益总计 -->
                                <tr class="table-danger fw-bold fs-5">
                                    <td class="text-end pe-4">【負債・純資産 合計】</td>
                                    <td class="text-end pe-4">{{ number_format($totalLiabilities) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. 贷借平衡检查 -->
    <!-- <div class="row justify-content-center mb-4">
        <div class="col-lg-8">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                贷借平衡检查 (Balance Check)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                资产 ({{ number_format($totalAssets) }}) 
                                @if($totalAssets == $totalLiabilities) 
                                    = 
                                @else 
                                    ≠ 
                                @endif
                                负债权益 ({{ number_format($totalLiabilities) }})
                                @if($totalAssets == $totalLiabilities)
                                    <span class="badge badge-success">平衡</span>
                                @else
                                    <span class="badge badge-danger">不平衡</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

</div>

<style>
    /* 打印优化样式 */
    @media print {
        body * { visibility: hidden; }
        .container-fluid, .container-fluid * { visibility: visible; }
        .container-fluid { position: absolute; left: 0; top: 0; width: 100%; }
        .btn, .card-header .text-end, .border-left-warning { display: none !important; }
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
            
            // 如果没有选择月份，可以给个默认值或者提示，这里假设直接提交
            // 重新构建当前页面的 URL
            let newUrl = window.location.pathname + '?period_id=' + this.value;
            
            
            // 跳转页面，触发后端查询
            window.location.href = newUrl;
        });
    }
});

const currentParams = new URLSearchParams(window.location.search);

const baseUrl = "{{ route('masters.account-bs.pdf') }}";

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