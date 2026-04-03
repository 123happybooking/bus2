@extends('layouts.app')

@section('title', '入金詳細情報')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.payments.index', ['group_id' => request('group_id')]) }}">入金消し込み履歴</a></li>
                    <li class="breadcrumb-item active" aria-current="page">入金詳細</li>
                </ol>
            </nav>
            
            <!-- 成功/错误提示 -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <!-- 卡片主体 -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center {{ $payment->is_deleted ? 'bg-secondary' : 'bg-success' }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt"></i> 入金詳細情報
                    </h5>
                    <div>
                        @if($payment->is_deleted)
                            <span class="badge bg-danger me-2">取消済み</span>
                        @else
                            <span class="badge bg-light text-success me-2">有効</span>
                        @endif
                        <span class="badge bg-light text-dark font-monospace">{{ $payment->batch_token }}</span>
                    </div>
                </div>
                
                <div class="card-body">
                    
                    @if($payment->is_deleted)
                        <div class="alert alert-warning border-start border-4 border-warning mb-4">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            <strong>注意：</strong> この入金記録は取消されています。関連する請求書の未入金残高は復元されています。
                        </div>
                    @endif

                    <!-- 第一部分：基本情报 -->
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="bi bi-info-circle"></i> 基本情報
                    </h6>
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label class="form-label text-muted small">入金日</label>
                            <div class="fs-6">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label text-muted small">処理担当者</label>
                            <div class="fs-6">{{ $payment->handled_by }}</div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted small">備考</label>
                            <div class="p-2 bg-light rounded border">{{ $payment->remark ?? 'なし' }}</div>
                        </div>
                    </div>

                    <!-- 第二部分：金额汇总 -->
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="bi bi-calculator"></i> 金額サマリー
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-1">合計入金額</div>
                                    <div class="fs-4 fw-bold text-success font-monospace">
                                        {{ number_format($payment->total_amount, 0) }} <small class="fs-6">{{$payment->currency_code}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-1">対象件数</div>
                                    <div class="fs-4 fw-bold text-primary font-monospace">
                                        {{ $details->count() }} <small class="fs-6">件</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-1">登録日時</div>
                                    <div class="fs-6 fw-bold text-dark mt-2">
                                        {{ $payment->created_at->format('Y/m/d H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 第三部分：核销明细列表 -->
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="bi bi-list-check"></i> 消し込み明細
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No.</th>
                                    <th class="text-center" style="width: 150px;">請求書番号</th>
                                    <th class="text-center" style="width: 200px;">請求先</th>
                                    <th class="text-center" style="width: 120px;">請求日</th>
                                    <th class="text-end" style="width: 120px;">請求金額</th>
                                    <th class="text-end" style="width: 120px;">消込金額</th>
                                    <th class="text-end" style="width: 120px;">残高</th>
                                    <th class="text-center" style="width: 100px;">状態</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($details as $index => $detail)
                                    @php
                                        $invoice = $detail->invoice;
                                        $remaining = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0);
                                        // 简单计算当前这一笔核销后的即时状态逻辑可能复杂，这里直接显示发票当前状态
                                        $statusClass = '';
                                        $statusText = '';
                                        if (($invoice->paid_amount ?? 0) >= ($invoice->total_amount ?? 0)) {
                                            $statusClass = 'bg-success';
                                            $statusText = '完了';
                                        } elseif (($invoice->paid_amount ?? 0) > 0) {
                                            $statusClass = 'bg-warning text-dark';
                                            $statusText = '一部';
                                        } else {
                                            $statusClass = 'bg-secondary';
                                            $statusText = '未済';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td class="text-center font-monospace fw-bold text-primary">
                                            {{ $invoice->invoice_number ?? 'N/A' }}
                                        </td>
                                        <td class="small">
                                            {{ $invoice->agency->agency_name ?? '' }}
                                        </td>
                                        <td class="text-center small">
                                            {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') : '-' }}
                                        </td>
                                        <td class="text-end font-monospace">
                                            {{ number_format($invoice->total_amount ?? 0, 0) }}
                                        </td>
                                        <td class="text-end font-monospace fw-bold text-success">
                                            {{ number_format($detail->write_off_amount, 0) }}
                                        </td>
                                        <td class="text-end font-monospace text-muted">
                                            {{ number_format($remaining, 0) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            明細データがありません
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- 按钮区域 -->
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <a href="{{ route('masters.payments.edit', $payment) }}" class="btn btn-primary">
                                <i class="bi bi-edit"></i> 编辑
                            </a>
                            <a href="{{ route('masters.payments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> キャンセル
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 隐藏的删除表单 -->
<form id="cancel-form" action="{{ route('masters.payments.destroy', $payment) }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmCancel() {
    const confirmed = confirm(
        '【重要】本当にこの入金記録を取消しますか？\n\n' +
        'バッチ番号：{{ $payment->batch_token }}\n' +
        '金額：{{ number_format($payment->total_amount, 0) }} JPY\n\n' +
        '・関連する {{ $details->count() }} 件の請求書の未入金残高が復元されます。\n' +
        '・この操作はログに記録されます。\n' +
        '・取り消し後、再度入金登録を行う必要があります。\n\n' +
        '実行してもよろしいですか？'
    );
    
    if (confirmed) {
        document.getElementById('cancel-form').submit();
    }
}
</script>
@endpush