@extends('layouts.app')

@section('title', '顧客マスター')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-people me-2"></i>顧客マスター</h4>
        <a href="{{ route('masters.customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

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
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.customers.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="顧客コード、顧客名、住所、電話番号で検索"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    @if(request('search'))
                        <a href="{{ route('masters.customers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> クリア
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    
    @if(request('search') && $customers->total() > 0)
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            検索条件: "{{ request('search') }}" 
            - {{ $customers->total() }}件の結果が見つかりました
        </div>
    @endif

    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary align-middle">
                    <tr>
                        <th>顧客コード</th>
                        <th>顧客名</th>
                        <th>顧客名（カナ）</th>
                        <th>住所</th>
                        <th>電話</th>
                        <th>FAX</th>
                        <th>担当者</th>
                        <th class="text-center">状態</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr class="{{ $customer->is_active ? '' : 'table-secondary' }}">
                        <td>{{ $customer->customer_code }}</td>
                        <td>
                            {{ $customer->customer_name }}
                            @if($customer->customer_name_kana)
                                <br>
                                <small class="text-muted">{{ $customer->customer_name_kana }}</small>
                            @endif
                        </td>
                        <td>{{ $customer->customer_name_kana ?? '--' }}</td>
                        <td>
                            @if($customer->postal_code)
                                〒{{ $customer->postal_code }}<br>
                            @endif
                            {{ $customer->address }}
                        </td>
                        <td>{{ $customer->phone_number ?? '--' }}</td>
                        <td>{{ $customer->fax_number ?? '--' }}</td>
                        <td>
                            @if($customer->manager_name)
                                {{ $customer->manager_name }}
                                @if($customer->email)
                                    <br>
                                    <small class="text-muted">{{ $customer->email }}</small>
                                @endif
                            @else
                                --
                            @endif
                        </td>
                        <td class="text-center">
                            @if($customer->is_active)
                                <span class="badge bg-success">有効</span>
                            @else
                                <span class="badge bg-secondary">無効</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.customers.show', $customer) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.customers.edit', $customer) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当にこの顧客を削除しますか？\nこの操作は元に戻せません。');
                                }
                                </script>
                                <form action="{{ route('masters.customers.destroy', $customer) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('{{ $customer->customer_name }}')">
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
                        <td colspan="9" class="text-center py-4">
                            @if(request('search'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致する顧客が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-people display-6 mb-2"></i>
                                    <p class="mb-0">顧客データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の顧客を登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($customers->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $customers->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $customers->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $customers->currentPage();
                        $last = $customers->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $customers->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $customers->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $customers->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$customers->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $customers->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} / 全 {{ $customers->total() }} 件
            </div>
        </div>
    @endif
</div>

<style>
.table-striped tbody tr.table-secondary:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.03);
}
.table-striped tbody tr.table-secondary:nth-of-type(even) {
    background-color: rgba(0, 0, 0, 0.05);
}
</style>
@endsection