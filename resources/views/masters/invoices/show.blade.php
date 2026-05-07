@extends('layouts.app')

@section('title', '請求書詳細')

@section('content')
<!-- 修改点：减小容器左右内边距 px-2 -->
<div class="container-fluid px-2">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb: 减小间距 mb-2，字体变小 -->
            <nav aria-label="breadcrumb" class="mb-2" style="font-size: 0.875rem;">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.invoices.index', ['group_id' => $invoice->group_id]) }}">請求書管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">請求書詳細</li>
                </ol>
            </nav>

            <!-- 成功/错误提示: 减小内边距 py-2，字体变小 -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2 mb-2" role="alert" style="font-size: 0.875rem;">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2 mb-2" role="alert" style="font-size: 0.875rem;">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Header Actions: 减小间距 mb-3，标题变小 -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <!-- 修改点：fs-5 (原 h4 默认更大) -->
                <h5 class="mb-0 text-primary fs-5" style="font-size: 1.1rem !important;">
                    <i class="bi bi-file-earmark-text"></i> 請求書詳細
                </h5>
                <div class="d-flex gap-1">
                    <!-- 修改点：btn-sm, 字体变小 -->
                    <a href="{{ route('masters.invoices.edit', [$invoice->id, 'group_id' => $invoice->group_id]) }}" class="btn btn-primary btn-sm" style="font-size: 0.875rem;">
                        <i class="bi bi-pencil-square"></i> 編集
                    </a>
                    <a href="{{ route('masters.invoices.index', ['group_id' => $invoice->group_id]) }}" class="btn btn-secondary btn-sm" style="font-size: 0.875rem;">
                        <i class="bi bi-x-circle"></i> 一覧に戻る
                    </a>
                </div>
            </div>

            <!-- ================= 第一部分：基本情報 (只读模式) ================= -->
            <!-- 修改点：mb-3 -->
            <div class="card shadow-sm mb-3">
                <!-- 修改点：py-1, 字体缩小 -->
                <div class="card-header bg-primary text-white py-1">
                    <h5 class="mb-0 fs-6" style="font-size: 0.9rem !important; line-height: 1.2;">
                        <i class="bi bi-info-circle"></i> 請求書基本情報
                    </h5>
                </div>
                <!-- 修改点：p-2 -->
                <div class="card-body p-2">
                    
                    <!-- Row 1: 修改点 g-2, mb-2 -->
                    <div class="row g-2 mb-2 align-items-start">
                        
                        <!-- 左侧：代理店 & Textarea -->
                        <div class="col-lg-6">
                            <!-- 修改点：mb-1, 字体变小 -->
                            <div class="d-flex align-items-center mb-1">
                                <label class="fw-bold me-1 mb-0 text-muted flex-shrink-0 text-nowrap" style="min-width: 70px; font-size: 0.875rem;">
                                    <i class="bi bi-building"></i> 代理店
                                </label>
                                <span class="form-control-plaintext fw-bold" style="font-size: 0.875rem;">{{ $invoice->agency->agency_name ?? '—' }}</span>
                            </div>
                            
                            <!-- 修改点：form-control-sm, rows 不变但内部 padding 变小 -->
                            <textarea class="form-control form-control-sm bg-light" rows="3" readonly style="font-size: 0.875rem;">{{ $invoice->agency_detail ?? '—' }}</textarea>
                        </div>

                        <!-- 右侧：8 个字段 -->
                        <div class="col-lg-6">
                            <!-- 修改点：g-1 -->
                            <div class="row g-1">
                                <!-- 第一行 -->
                                <div class="col-md-3 col-6">
                                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">請求担当</label>
                                    <div class="form-control-plaintext fw-normal" style="font-size: 0.875rem;">{{ $invoice->staff->name ?? '—' }}</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">内税/外税</label>
                                    <div>
                                        <span class="badge {{ $invoice->tax_mode == 1 ? 'bg-primary' : 'bg-outline-secondary text-dark border' }}" style="font-size: 0.75rem;">
                                            {{ $invoice->tax_mode == 1 ? '内税' : '外税' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">言語</label>
                                    <div>
                                        <span class="badge {{ $invoice->language == 1 ? 'bg-info text-dark' : 'bg-outline-secondary text-dark border' }}" style="font-size: 0.75rem;">
                                            {{ $invoice->language == 1 ? '日本語' : '英語' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">タイプ</label>
                                    <div>
                                        <span class="badge {{ $invoice->type == 1 ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 0.75rem;">
                                            {{ $invoice->type == 1 ? '正式' : '見積' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- 第二行 -->
                                <div class="col-md-3 col-6">
                                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">通貨</label>
                                    <div class="form-control-plaintext fw-bold" style="font-size: 0.875rem;">{{ $invoice->currency_code ?? '—' }}</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">請求日</label>
                                    <div class="form-control-plaintext" style="font-size: 0.875rem;">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') }}</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">運行日</label>
                                    <div class="form-control-plaintext" style="font-size: 0.875rem;">{{ $invoice->operation_date ? \Carbon\Carbon::parse($invoice->operation_date)->format('Y/m/d') : '—' }}</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">予約 ID</label>
                                    <div class="form-control-plaintext" style="font-size: 0.875rem;">{{ $invoice->reservation_id ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 修改点：my-2 -->
                    <hr class="my-2 text-muted">

                    <!-- Row 2: 标题、支付日、银行、锁 -->
                    <!-- 修改点：g-2, mb-2 -->
                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted mb-0" style="font-size: 0.75rem;">タイトル</label>
                            <div class="form-control-plaintext" style="font-size: 0.875rem;">{{ $invoice->billing_title ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted mb-0" style="font-size: 0.75rem;">支払指定日</label>
                            <div class="form-control-plaintext" style="font-size: 0.875rem;">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted mb-0" style="font-size: 0.75rem;">入金銀行</label>
                            <div class="form-control-plaintext" style="font-size: 0.875rem;">{{ $invoice->bank->bank_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted mb-0" style="font-size: 0.75rem;">ロック</label>
                            <div class="d-flex align-items-center gap-2">
                                <!-- 修改点：font-size 1.4rem (原 1.8rem) -->
                                <div style="font-size: 1.4rem; min-width: 30px; text-align: center;">
                                    @if($invoice->is_locked)
                                        <i class="bi bi-lock-fill text-danger" title="ロック中"></i>
                                    @else
                                        <i class="bi bi-unlock-fill text-success" title="ロック解除"></i>
                                    @endif
                                </div>
                                <div class="lh-sm" style="font-size: 0.75rem;">
                                    <div><strong>{{ $invoice->locked_user}}</strong></div>
                                    <div class="text-muted">{{ $invoice->locked_at }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: 備考 -->
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted mb-0" style="font-size: 0.75rem;">備考</label>
                            <textarea class="form-control form-control-sm bg-light" rows="2" readonly style="font-size: 0.875rem;">{{ $invoice->notes ?? '—' }}</textarea>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================= 第二部分：請求明細 (只读模式 + 统计) ================= -->
            <!-- 修改点：mb-3 -->
            <div class="card shadow-sm mb-3">
                <!-- 修改点：py-1, 字体缩小 -->
                <div class="card-header bg-secondary text-white py-1">
                    <h5 class="mb-0 fs-6" style="font-size: 0.9rem !important; line-height: 1.2;">
                        <i class="bi bi-list-task"></i> 請求明細
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <!-- 修改点：font-size 0.875rem -->
                        <table class="table table-bordered table-hover mb-0" id="itemsTable" style="font-size: 0.875rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center py-1">No.</th>
                                    <th class="py-1">内容</th>
                                    <th style="width: 100px;" class="text-end py-1">数量</th>
                                    <th style="width: 120px;" class="text-end py-1">単価</th>
                                    <th style="width: 100px;" class="text-end py-1">税率 (%)</th>
                                    <th style="width: 130px;" class="text-end py-1">小計</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                    <tr>
                                        <td class="text-center align-middle py-1" style="font-size: 0.8rem;">{{ $loop->iteration }}</td>
                                        <td class="align-middle py-1">{{ $item->description }}</td>
                                        <td class="text-end align-middle py-1">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-end align-middle py-1">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end align-middle py-1">
                                            @if($item->tax_rate == -1)
                                                <span class="badge bg-secondary" style="font-size: 0.75rem;">免税</span>
                                            @elseif($item->tax_rate == -2)
                                                <span class="badge bg-secondary" style="font-size: 0.75rem;">非課税</span>
                                            @else
                                                {{ number_format($item->tax_rate) }}%
                                            @endif
                                        </td>
                                        <td class="text-end align-middle fw-bold py-1">{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3" style="font-size: 0.875rem;">明細がありません。</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <!-- 修改点：py-1 -->
                                    <td colspan="5" class="text-end py-1" style="font-size: 0.875rem;">小計:</td>
                                    <td class="text-end py-1" style="font-size: 0.875rem;">
                                        {{ number_format($invoice->tax_mode == 1 ? $invoice->total_amount : $invoice->subtotal_amount, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end py-1" style="font-size: 0.875rem;">消費税合計:</td>
                                    <td class="text-end py-1 text-primary" style="font-size: 0.875rem;">{{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <!-- 修改点：py-2, fs-6 -> fs-7 (自定义更小) -->
                                    <td colspan="5" class="text-end py-2" style="font-size: 0.9rem;">合計（税込）:</td>
                                    <td class="text-end py-2 text-primary" style="font-size: 0.9rem;">{{ number_format($invoice->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 底部信息: 字体更小 -->
            <div class="row text-muted mb-4" style="font-size: 0.75rem;">
                <div class="col-md-6">
                    <p class="mb-1"><strong>登録日時:</strong> {{ $invoice->created_at ? $invoice->created_at->format('Y/m/d H:i') : '—' }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1"><strong>最終更新日時:</strong> {{ $invoice->updated_at ? $invoice->updated_at->format('Y/m/d H:i') : '—' }}</p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* 只读表单的样式微调 */
.form-control-plaintext {
    padding-top: 0.15rem !important;
    padding-bottom: 0.15rem !important;
    margin-bottom: 0; 
    line-height: 1.4;
    font-size: 0.875rem !important;
}
.bg-light {
    background-color: #f8f9fa !important;
}
/* 表格行高优化 */
#itemsTable tbody tr td {
    vertical-align: middle;
    padding: 0.25rem !important; /* 进一步压缩 */
}
#itemsTable .badge {
    font-weight: normal;
}
/* 卡片头部字体修正 */
.card-header h5 {
    font-size: 0.9rem !important;
    line-height: 1.2;
}
</style>
@endsection