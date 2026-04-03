@extends('layouts.app')

@section('title', '入金消し込み履歴')

@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-cash-coin me-2 text-success"></i>入金消し込み履歴</h4>
    </div>

    <!-- 成功/错误提示 -->
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

    <!-- 搜索区域 -->
    <div class="mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('masters.payments.index') }}" class="row g-2 align-items-end">
                    {{-- 隐藏域 --}}
                    <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">バッチ番号/備考</label>
                        <input type="text" name="search" class="form-control" 
                            placeholder="バッチまたは備考"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">入金日</label>
                        <input type="date" name="payment_date" class="form-control" 
                            value="{{ request('payment_date') }}">
                    </div>
                    <div class="col-md-auto">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> 検索
                            </button>
                            @if(request()->hasAny(['customer_name', 'search', 'payment_date']))
                                <a href="{{ route('masters.payments.index', ['group_id' => request('group_id')]) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> クリア
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 搜索结果提示 -->
    @if(request()->hasAny(['search', 'payment_date']))
        <div class="alert alert-info mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>
                検索条件: 
                @if(request('search')) "キーワード：{{ request('search') }}" @endif
                @if(request('payment_date')) "日付：{{ request('payment_date') }}" @endif
                
                @if($payments->count() > 0)
                    - {{ $payments->total() }}件の結果が見つかりました
                @else
                    - 該当する入金記録が見つかりませんでした
                @endif
            </div>
        </div>
    @endif

    <!-- 表格区域 -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th class="text-center" style="width: 80px;">ID</th>
                        <th class="text-center" style="width: 160px;">バッチ番号</th>
                        <!-- <th class="text-center" style="width: 150px;">請求先</th> -->
                        <th class="text-center" style="width: 120px;">入金日</th>
                        <th class="text-center" style="width: 100px;">件数</th>
                        <th class="text-center" style="width: 140px;">合計金額</th>
                        <th class="text-center" style="width: 100px;">請求担当</th>
                        <th class="text-center" style="width: 160px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr class="{{ $payment->is_deleted ? 'table-secondary text-muted' : '' }}">
                        <td class="text-center text-muted small">{{ $payment->id }}</td>
                        <td class="text-center font-monospace small">{{ $payment->batch_token }}</td>
                        <!-- <td class="text-center fw-bold">
                            {{ $payment->invoice->agency->agency_name ?? '' }}
                        </td> -->
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark">
                                {{ $payment->details()->where('is_deleted', 0)->count() }} 件
                            </span>
                        </td>
                        <td class="text-center font-monospace fw-bold text-success">
                            {{ number_format($payment->total_amount, 0) }} {{$payment->currency_code}}
                        </td>
                        <td class="text-center small text-muted">
                            {{ $payment->staff->name ?? '' }}
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- 详细 -->
                                <a href="{{ route('masters.payments.show', $payment) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <a href="{{ route('masters.payments.edit', ['payment' => $payment]) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- 删除 -->
                                <form action="{{ route('masters.payments.destroy', ['payment' => $payment]) }}" 
                                      method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('本当にこの入金記録 (バッチ：{{ $payment->batch_token }}) を取消しますか？\n関連する請求書の未入金残高が復元されます。\nこの操作はログに残ります。')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="削除">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            @if(request()->hasAny(['customer_name', 'search', 'payment_date']))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">検索条件に一致する入金記録が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-cash-coin display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">入金記録が登録されていません</p>
                                    <p class="small">請求書一覧から「入金処理」を実行してください</p>
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
    @if($payments->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $payments->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $payments->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $payments->currentPage();
                        $last = $payments->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $payments->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $payments->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $payments->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$payments->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $payments->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $payments->firstItem() ?? 0 }} - {{ $payments->lastItem() ?? 0 }} / 全 {{ $payments->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection