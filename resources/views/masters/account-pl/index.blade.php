@extends('layouts.app')
@section('title', '損益計算書')

@section('content')
<div class="container-fluid">
    <!-- 1. 标题 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cash-coin text-success me-2"></i>損益計算書 (Profit and Loss)</h3>
        <span class="text-muted">期間で絞り込みを行ってください</span>
        <a id="downloadPdf" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> 导出PDF
        </a>
    </div>

    <!-- 2. 搜索区域 -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- 表单开始 -->
            <form id="searchForm" method="GET" action="{{ route('masters.account-pl.index') }}">
                
                <div class="row g-3 align-items-end">
                    
                    <!-- 左侧/主要筛选区域：周期与月份 -->
                    <div class="col-auto">
                        <div class="d-flex flex-column">
                            <!-- 标签 -->
                            <label class="form-label mb-1 text-muted small">
                                <i class="bi bi-calendar-event"></i> 周期/月份
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

    <!-- 3. 损益表主体 -->
    @if(request()->hasAny(['yearmonth']))
    <div class="card shadow-sm">
        
        <div class="table-responsive">
            <table class="table table-borderless mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60%">項目 (勘定科目)</th>
                        <th style="width: 20%" class="text-end">借方 (¥)</th>
                        <th style="width: 20%" class="text-end">贷方 (¥)</th>
                        <th style="width: 20%" class="text-end">金額</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <!-- ====================================== -->
                    <!-- 営業損益の部 -->
                    <!-- ====================================== -->

                    <!-- 1. 売上高 (ID 7) -->
                    <tr class="fw-bold bg-light">
                        <td colspan="4"><span class="badge bg-primary">売上高</span></td>
                    </tr>
                    <!-- 遍历所有数据，筛选出 Category ID = 7 的 -->
                    @foreach($groupedData as $row)
                        @if($row['category_id'] == 7)
                        <tr class="border-bottom">
                            <td class="ps-5 small">{{ $row['account_name'] }}</td>
                            <td class="text-end text-muted">¥{{ number_format($row['debit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['credit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['credit']) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold border-top border-bottom">
                        <td class="ps-4">売上高 合計</td>
                        <td></td>
                        <td class="text-end">¥{{ number_format($totalRevenue) }}</td>
                        <td class="text-end">¥{{ number_format($totalRevenue) }}</td>
                    </tr>

                    <!-- 2. 売上原価 (ID 8) -->
                    <tr class="fw-bold bg-light">
                        <td colspan="4"><span class="badge bg-danger">売上原価</span></td>
                    </tr>
                    @foreach($groupedData as $row)
                        @if($row['category_id'] == 8)
                        <tr class="border-bottom">
                            <td class="ps-5 small">{{ $row['account_name'] }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                            <td class="text-end text-muted">¥{{ number_format($row['credit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold border-top border-bottom">
                        <td class="ps-4">売上原価 合計</td>
                        <td class="text-end">¥{{ number_format($totalCogs) }}</td>
                        <td></td>
                        <td class="text-end">¥{{ number_format($totalCogs) }}</td>
                    </tr>

                    <!-- 3. 販売管理費 (ID 9) -->
                    <tr class="fw-bold bg-light">
                        <td colspan="4"><span class="badge bg-warning">販売管理費</span></td>
                    </tr>
                    @foreach($groupedData as $row)
                        @if($row['category_id'] == 9)
                        <tr class="border-bottom">
                            <td class="ps-5 small">{{ $row['account_name'] }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                            <td class="text-end text-muted">¥{{ number_format($row['credit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold border-top border-bottom">
                        <td class="ps-4">販売管理費 合計</td>
                        <td class="text-end">¥{{ number_format($totalExpenses) }}</td>
                        <td></td>
                        <td class="text-end">¥{{ number_format($totalExpenses) }}</td>
                    </tr>

                    <!-- 営業利益 -->
                    <tr>
                        <td class="ps-4 fw-bold">営業利益</td>
                        <td colspan="2"></td>
                        <td class="text-end h5 fw-bold" style="color: #2E86AB;">
                            ¥{{ number_format($operatingIncome) }}
                        </td>
                    </tr>

                    <!-- ====================================== -->
                    <!-- 営業外損益 -->
                    <!-- ====================================== -->

                    <!-- 4. 営業外収益 (ID 10) -->
                    <tr class="fw-bold bg-light">
                        <td colspan="4"><span class="badge bg-info">営業外収益</span></td>
                    </tr>
                    @foreach($groupedData as $row)
                        @if($row['category_id'] == 10)
                        <tr class="border-bottom">
                            <td class="ps-5 small">{{ $row['account_name'] }}</td>
                            <td class="text-end text-muted">¥{{ number_format($row['debit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['credit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['credit']) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold border-top border-bottom">
                        <td class="ps-4">営業外収益 合計</td>
                        <td></td>
                        <td class="text-end">¥{{ number_format($totalOIncome) }}</td>
                        <td class="text-end">¥{{ number_format($totalOIncome) }}</td>
                    </tr>

                    <!-- 5. 営業外費用 (ID 11) -->
                    <tr class="fw-bold bg-light">
                        <td colspan="4"><span class="badge bg-secondary">営業外費用</span></td>
                    </tr>
                    @foreach($groupedData as $row)
                        @if($row['category_id'] == 11)
                        <tr class="border-bottom">
                            <td class="ps-5 small">{{ $row['account_name'] }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                            <td class="text-end text-muted">¥{{ number_format($row['credit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold border-top border-bottom">
                        <td class="ps-4">営業外費用 合計</td>
                        <td class="text-end">¥{{ number_format($totalOExpenses) }}</td>
                        <td></td>
                        <td class="text-end">¥{{ number_format($totalOExpenses) }}</td>
                    </tr>

                    <!-- 経常利益 -->
                    <tr>
                        <td class="ps-4 fw-bold">経常利益</td>
                        <td colspan="2"></td>
                        <td class="text-end h5 fw-bold" style="color: #16A085;">
                            ¥{{ number_format($ordinaryIncome) }}
                        </td>
                    </tr>

                    <!-- ====================================== -->
                    <!-- 特別損益 -->
                    <!-- ====================================== -->

                    <!-- 6. 特別利益 (ID 12) -->
                    <tr class="fw-bold bg-light">
                        <td colspan="4"><span class="badge bg-success">特別利益</span></td>
                    </tr>
                    @foreach($groupedData as $row)
                        @if($row['category_id'] == 12)
                        <tr class="border-bottom">
                            <td class="ps-5 small">{{ $row['account_name'] }}</td>
                            <td class="text-end text-muted">¥{{ number_format($row['debit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['credit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['credit']) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold border-top border-bottom">
                        <td class="ps-4">特別利益 合計</td>
                        <td></td>
                        <td class="text-end">¥{{ number_format($totalSOIncome) }}</td>
                        <td class="text-end">¥{{ number_format($totalSOIncome) }}</td>
                    </tr>

                    <!-- 7. 特別損失 (ID 13) -->
                    <tr class="fw-bold bg-light">
                        <td colspan="4"><span class="badge bg-dark">特別損失</span></td>
                    </tr>
                    @foreach($groupedData as $row)
                        @if($row['category_id'] == 13)
                        <tr class="border-bottom">
                            <td class="ps-5 small">{{ $row['account_name'] }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                            <td class="text-end text-muted">¥{{ number_format($row['credit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold border-top border-bottom">
                        <td class="ps-4">特別損失 合計</td>
                        <td class="text-end">¥{{ number_format($totalSOExpenses) }}</td>
                        <td></td>
                        <td class="text-end">¥{{ number_format($totalSOExpenses) }}</td>
                    </tr>



                    <!-- 税引前当期純利益 -->
                    <tr class="table-secondary">
                        <td class="ps-4 fw-bold">税引前当期純利益</td>
                        <td colspan="2"></td>
                        <td class="text-end h4 fw-bold">
                            ¥{{ number_format($profitBeforeTax) }}
                        </td>
                    </tr>

                    <!-- 税金等 (ID 14) -->
                    <tr class="fw-bold bg-light">
                        <td colspan="4"><span class="badge bg-secondary">税等</span></td>
                    </tr>
                    @foreach($groupedData as $row)
                        @if($row['category_id'] == 14)
                        <tr class="border-bottom">
                            <td class="ps-5 small">{{ $row['account_name'] }}</td>
                            <!-- 税金属于费用，金额在借方 -->
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                            <td class="text-end text-muted">¥{{ number_format($row['credit']) }}</td>
                            <td class="text-end fw-bold">¥{{ number_format($row['debit']) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold border-top border-bottom">
                        <td class="ps-4">税等 合计</td>
                        <td class="text-end">¥{{ number_format($totalTaxes) }}</td>
                        <td></td>
                        <td class="text-end">¥{{ number_format($totalTaxes) }}</td>
                    </tr>

                    <!-- 当期純利益 (最终净利润) -->
                    <tr class="table-success">
                        <td class="ps-4 fw-bold fs-5">当期純利益 (净利润)</td>
                        <td colspan="2"></td>
                        <td class="text-end h4 fw-bold">
                            ¥{{ number_format($netIncome) }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<style>
@media print {
    .btn, .card-header { display: none !important; }
    body * { visibility: hidden; }
    .container-fluid, .container-fluid * { visibility: visible; }
    .container-fluid { position: absolute; left: 0; top: 0; width: 100%; }
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

const baseUrl = "{{ route('masters.account-pl.pdf') }}";

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