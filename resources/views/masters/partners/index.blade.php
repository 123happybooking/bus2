@extends('layouts.app')

@section('title', '取引先マスター')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-building me-2"></i>取引先マスター</h4>
        <a href="{{ route('masters.partners.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.partners.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="コード、会社名、支店名で検索"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    @if(request('search'))
                        <a href="{{ route('masters.partners.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> クリア
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    
    @if(request('search'))
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            検索条件: "{{ request('search') }}" 
            @if($partners->count() > 0)
                - {{ $partners->total() }}件の結果が見つかりました
            @else
                - 該当する取引先が見つかりませんでした
            @endif
        </div>
    @endif

    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary">
                    <tr>
                        <th>コード</th>
                        <th>会社名 / 支店名</th>
                        <th>電話番号</th>
                        <th>インボイス番号</th>
                        <th>支払条件</th>
                        <th>取引状態</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $partner)
                    <tr>
                        <td>{{ $partner->partner_code }}</td>
                        <td>
                            <div class="fw-semibold">{{ $partner->partner_name }}</div>
                            @if($partner->branch_name)
                                <small class="text-muted">{{ $partner->branch_name }}</small>
                            @endif
                        </td>
                        <td>
                            @if($partner->phone_number)
                                {{ $partner->phone_number }}
                            @else
                                <span class="text-muted small">未設定</span>
                            @endif
                        </td>
                        <td>
                            @if($partner->invoice_number)
                                <span class="badge bg-info">{{ $partner->invoice_number }}</span>
                            @else
                                <span class="text-muted small">未設定</span>
                            @endif
                        </td>
                        <td>
                            @if($partner->payment_month !== null || $partner->payment_day !== null)
                                @if($partner->payment_month == 0)当月
                                @elseif($partner->payment_month == 1)翌月
                                @elseif($partner->payment_month == 2)翌々月
                                @endif
                                @if($partner->payment_day){{ $partner->payment_day }}日@endif
                            @else
                                <span class="text-muted small">未設定</span>
                            @endif
                        </td>
                        <td>
                            @if($partner->is_active)
                                <span class="badge bg-success">取引中</span>
                            @else
                                <span class="badge bg-secondary">停止</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.partners.show', $partner) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.partners.edit', $partner) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当にこの取引先を削除しますか？\nこの操作は元に戻せません。');
                                }
                                </script>
                                <form action="{{ route('masters.partners.destroy', $partner) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('{{ $partner->partner_name }}')">
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
                        <td colspan="7" class="text-center py-4">
                            @if(request('search'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致する取引先が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-building display-6 mb-2"></i>
                                    <p class="mb-0">取引先データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の取引先を登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    
    @if($partners->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $partners->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $partners->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $partners->currentPage();
                        $last = $partners->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $partners->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $partners->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $partners->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$partners->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $partners->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $partners->firstItem() ?? 0 }} - {{ $partners->lastItem() ?? 0 }} / 全 {{ $partners->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection