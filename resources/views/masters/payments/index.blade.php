@extends('layouts.app')
@section('title', '入金消し込み履歴')
@section('content')
    <!-- 1. 全局缩小字体并压缩容器内边距 -->
    <div class="container-fluid px-1" style="font-size: 0.85rem;">
        <!-- 标题与新建按钮 -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0" style="font-size: 1rem;"><i class="bi bi-cash-coin me-1 text-success"></i>入金消し込み履歴</h4>
        </div>

        <!-- 成功/错误提示 -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-1 mb-2" role="alert" style="font-size: 0.85rem;">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-1 mb-2" role="alert" style="font-size: 0.85rem;">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- 搜索区域 -->
        <div class="mb-2">
            <div class="card shadow-sm">
                <div class="card-body p-1">
                    <form method="GET" action="{{ route('masters.payments.index') }}" class="row g-1 align-items-end">
                        {{-- 隐藏域 --}}
                        <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1" style="font-size: 0.75rem;">バッチ番号/備考</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="バッチまたは備考" value="{{ request('search') }}" style="font-size: 0.8rem;">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1" style="font-size: 0.75rem;">入金日</label>
                            <input type="text" name="payment_date" class="form-control form-control-sm datepicker-3months" value="{{ request('payment_date') }}" placeholder="" style="font-size: 0.8rem;">
                        </div>
                        <div class="col-md-auto">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-search"></i> 検索
                                </button>
                                @if(request()->hasAny(['customer_name', 'search', 'payment_date']))
                                    <a href="{{ route('masters.payments.index', ['group_id' => request('group_id')]) }}" class="btn btn-sm btn-outline-secondary">
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
            <div class="alert alert-info mb-2 d-flex align-items-center py-1" style="font-size: 0.8rem;">
                <i class="bi bi-info-circle me-1 fs-6"></i>
                <div>
                    検索条件: 
                    @if(request('search')) "キーワード：{{ request('search') }}" @endif
                    @if(request('payment_date')) "日付：{{ request('payment_date') }}" @endif
                    @if($payments->count() > 0) - {{ $payments->total() }}件の結果が見つかりました @else - 該当する入金記録が見つかりませんでした @endif
                </div>
            </div>
        @endif

        <!-- 表格区域 -->
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center" style="width: 60px; font-size: 0.75rem;">ID</th>
                            <th class="text-center" style="width: 120px; font-size: 0.75rem;">バッチ番号</th>
                            <th class="text-center" style="width: 100px; font-size: 0.75rem;">入金日</th>
                            <th class="text-center" style="width: 80px; font-size: 0.75rem;">件数</th>
                            <th class="text-center" style="width: 120px; font-size: 0.75rem;">合計金額</th>
                            <th class="text-center" style="width: 80px; font-size: 0.75rem;">請求担当</th>
                            <th class="text-center" style="width: 140px; font-size: 0.75rem;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr class="{{ $payment->is_deleted ? 'table-secondary text-muted' : '' }}">
                            <td class="text-center text-muted small" style="font-size: 0.75rem;">{{ $payment->id }}</td>
                            <td class="text-center font-monospace small" style="font-size: 0.75rem;">{{ $payment->batch_token }}</td>
                            <td class="text-center" style="font-size: 0.75rem;">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') }}
                            </td>
                            <td class="text-center" style="font-size: 0.75rem;">
                                <span class="badge bg-info text-dark"> {{ $payment->details()->where('is_deleted', 0)->count() }} </span>
                            </td>
                            <td class="text-center font-monospace fw-bold text-success" style="font-size: 0.75rem;">
                                {{ number_format($payment->total_amount, 0) }} {{$payment->currency_code}}
                            </td>
                            <td class="text-center small text-muted" style="font-size: 0.75rem;">
                                {{ $payment->staff->name ?? '' }}
                            </td>
                            <td class="p-1">
                                <div class="d-flex gap-1 justify-content-center">
                                    <!-- 详细 -->
                                    <a href="{{ route('masters.payments.show', $payment) }}" class="btn btn-sm btn-outline-info" title="詳細" style="font-size: 0.7rem; padding: 0.1rem 0.25rem;">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('masters.payments.edit', ['payment' => $payment]) }}" class="btn btn-sm btn-outline-primary" title="編集" style="font-size: 0.7rem; padding: 0.1rem 0.25rem;">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <!-- 删除 -->
                                    <form action="{{ route('masters.payments.destroy', ['payment' => $payment]) }}" method="POST" class="d-inline" onsubmit="return confirm('本当にこの入金記録 (バッチ：{{ $payment->batch_token }}) を取消しますか？\n関連する請求書の未入金残高が復元されます。\nこの操作はログに残ります。')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="削除" style="font-size: 0.7rem; padding: 0.1rem 0.25rem;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-3"> <!-- 修复：这里原来是 colspan="9"，改为了 7 -->
                                @if(request()->hasAny(['customer_name', 'search', 'payment_date']))
                                    <div class="text-muted">
                                        <i class="bi bi-search display-6 mb-1 d-block"></i>
                                        <p class="mb-0 fw-bold" style="font-size: 0.8rem;">検索条件に一致する入金記録が見つかりませんでした</p>
                                        <p class="small" style="font-size: 0.7rem;">検索キーワードを変更してお試しください</p>
                                    </div>
                                @else
                                    <div class="text-muted">
                                        <i class="bi bi-cash-coin display-6 mb-1 d-block"></i>
                                        <p class="mb-0 fw-bold" style="font-size: 0.8rem;">入金記録が登録されていません</p>
                                        <p class="small" style="font-size: 0.7rem;">請求書一覧から「入金処理」を実行してください</p>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 分页区域 -->
        @if($payments->hasPages())
            <div class="mt-2">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item {{ $payments->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $payments->previousPageUrl() }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"><span aria-hidden="true">&laquo;</span></a>
                        </li>
                        @php 
                            $current = $payments->currentPage(); 
                            $last = $payments->lastPage(); 
                            $start = max(1, $current - 2); 
                            $end = min($last, $current + 2); 
                        @endphp
                        @if($start > 1)
                            <li class="page-item"><a class="page-link" href="{{ $payments->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a></li>
                            @if($start > 2)<li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>@endif
                        @endif
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}"><a class="page-link" href="{{ $payments->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a></li>
                        @endfor
                        @if($end < $last)
                            @if($end < $last - 1)<li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>@endif
                            <li class="page-item"><a class="page-link" href="{{ $payments->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a></li>
                        @endif
                        <li class="page-item {{ !$payments->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $payments->nextPageUrl() }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"><span aria-hidden="true">&raquo;</span></a>
                        </li>
                    </ul>
                </nav>
                <div class="text-center text-muted small mt-1" style="font-size: 0.7rem;">
                    表示中: {{ $payments->firstItem() ?? 0 }} - {{ $payments->lastItem() ?? 0 }} / 全 {{ $payments->total() }} 件
                </div>
            </div>
        @endif
    </div>


    <style>
        /* 强制覆盖所有表格行的悬浮颜色 */
    body .table tbody tr:hover td {
        background-color: #e9ecef !important; /* 浅灰色背景 */
        cursor: pointer !important;
    }

    /* 针对删除行的特殊处理 */
    body .table tbody tr.table-secondary:hover td {
        background-color: #cbd5e1 !important; /* 稍深一点的灰，便于区分 */
    }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('.datepicker-3months', {
                locale: "ja",
                dateFormat: "Y-m-d",
                showMonths: 3,
                allowInput: true,
                clickOpens: true,
                disableMobile: true,
                onOpen: function(selectedDates, dateStr, instance) {
                    instance.calendarContainer.style.zIndex = '9999';
                }
            });
        });
    </script>

@endsection