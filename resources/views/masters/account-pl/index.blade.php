@extends('layouts.app')
@section('title', '損益計算書')

@section('content')
<div class="container-fluid">
    <!-- 1. 标题 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cash-coin text-success me-2"></i>損益計算書 (Profit and Loss)</h3>
        <span class="text-muted">期間で絞り込みを行ってください</span>
    </div>

    <!-- 2. 搜索区域 -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.account-pl.index') }}" class="d-flex align-items-end gap-2">
                <div class="col-md-auto">
                    <!-- 标签部分：完全复用上方样式 -->
                    <label class="form-label mb-1 text-muted" style="font-size: 0.75rem;">期間</label>
                    
                    <div class="d-flex flex-nowrap align-items-center">
                        <!-- 开始日输入框 -->
                        <input 
                            type="text" 
                            name="start_date" 
                            id="start_date"
                            class="form-control form-control-sm datepicker-3months" 
                            value="{{ request('start_date') }}" 
                            placeholder="開始日"
                            autocomplete="off"
                            style="border-color: #E5E7EB; width: 130px;"
                            required
                        >
                        
                        <!-- 分隔符 -->
                        <span class="mx-2 text-muted" style="font-size: 0.75rem;">～</span>
                        
                        <!-- 结束日输入框 -->
                        <input 
                            type="text" 
                            name="end_date" 
                            id="end_date"
                            class="form-control form-control-sm datepicker-3months" 
                            value="{{ request('end_date') }}" 
                            placeholder="終了日"
                            autocomplete="off"
                            style="border-color: #E5E7EB; width: 130px;"
                            required
                        >
                    </div>
                </div>
                <div class="col-md-auto d-flex align-items-end">
                    <!-- 标签部分：添加 margin-bottom: 4px 以匹配输入框的视觉重心 -->
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm" style="height: 31px; padding: 0 1rem; white-space: nowrap;">
                            <i class="bi bi-search"></i> 計算
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. 损益表主体 -->
    @if(request()->hasAny(['start_date', 'end_date']))
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <!-- 修改点：添加 d-flex 和 align-items-center -->
            <h5 class="mb-0 d-flex align-items-center text-white">
                損益計算書 (期間: {{ $startDate }} ～ {{ $endDate }})
            </h5>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer"></i> PDF
            </button>
        </div>
        
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
document.addEventListener('DOMContentLoaded', function() {
    initDateRangePicker('input[id="start_date"]', 'input[id="end_date"]');
});    
</script>
@endsection