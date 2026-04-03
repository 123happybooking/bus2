@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <!-- 1. 头部导航栏 -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-balance-scale fa-sm text-gray-400 mr-2"></i>
            貸借対照表 (Balance Sheet)
        </h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> 导出报表
        </a>
    </div>

    <!-- 2. 搜索/日期选择栏 -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.account-bs.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="date" class="form-label">基準日 (As of Date)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="height: 38px;">
                                <i class="bi bi-calendar-event"></i>
                            </span>
                            <!-- 确保 class 包含 datepicker-1months -->
                            <input type="date" name="date" id="date" class="form-control datepicker-1months" 
                                   value="{{ $date ?? date('Y-m-d') }}" style="height: 38px;" required>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> 表示
                        </button>
                    </div>
                    <div class="col-md-7 text-end">
                        <small class="text-muted">报表生成时间: {{ now()->format('Y-m-d H:i:s') }}</small>
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
                                    <th style="width: 60%">科目名称</th>
                                    <th style="width: 40%" class="text-end">金额 (¥)</th>
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
                                    <th style="width: 60%">科目名称</th>
                                    <th style="width: 40%" class="text-end">金额 (¥)</th>
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

                                        <!-- 小计 -->
                                        <tr class="fw-bold border-top">
                                            <td class="text-end pe-4">{{ $categoryName }} 合計</td>
                                            <td class="text-end pe-4">{{ number_format($liabilities[$categoryName]['total']) }}</td>
                                        </tr>
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
    <div class="row justify-content-center mb-4">
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
    </div>

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
document.addEventListener('DOMContentLoaded', function() {
    initDatePicker('input[id="date"]');
});   
</script>
@endsection