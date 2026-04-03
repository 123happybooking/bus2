@extends('layouts.app')

@section('title', '営業所マスター')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-building me-2"></i>営業所マスター</h4>
        <a href="{{ route('masters.branches.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.branches.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="営業所名、住所、電話番号で検索"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    <!--<a href="{{ route('masters.branches.index') }}" class="ms-3">-->
                    <!--    リセット-->
                    <!--</a>-->
                    @if(request('search'))
                        <a href="{{ route('masters.branches.index') }}" class="btn btn-outline-secondary">
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
            @if($branches->count() > 0)
                - {{ $branches->total() }}件の結果が見つかりました
            @else
                - 該当する営業所が見つかりませんでした
            @endif
        </div>
    @endif


    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary align-middle">
                    <tr>
                        <th>営業所ID</th>
                        <th>営業所名</th>
                        <th>郵便番号</th>
                        <th>住所</th>
                        <th>電話</th>
                        <th>FAX</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                    <tr>
                        <td>{{ $branch->branch_code }}</td>
                        <td>{{ $branch->branch_name }}</td>
                        <td>{{ $branch->postal_code ?? '--' }}</td>
                        <td>{{ $branch->address }}</td>
                        <td>{{ $branch->phone_number }}</td>
                        <td>{{ $branch->fax_number ?? '--' }}</td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.branches.show', $branch) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.branches.edit', $branch) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当にこの営業所を削除しますか？\nこの操作は元に戻せません。');
                                }
                                </script>
                                <form action="{{ route('masters.branches.destroy', $branch) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('{{ $branch->branch_name }}')">
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
                                    <p class="mb-0">検索条件に一致する営業所が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-building display-6 mb-2"></i>
                                    <p class="mb-0">営業所データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の営業所を登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    
    @if($branches->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $branches->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $branches->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $branches->currentPage();
                        $last = $branches->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $branches->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $branches->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $branches->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$branches->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $branches->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $branches->firstItem() ?? 0 }} - {{ $branches->lastItem() ?? 0 }} / 全 {{ $branches->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection