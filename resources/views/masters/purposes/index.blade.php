@extends('layouts.app')

@section('title', '目的管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-tags"></i>目的管理</h4>
                <a href="{{ route('masters.purposes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
            </div>
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            <div class="mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('masters.purposes.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="目的コード・目的名・カテゴリで検索"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> 検索
                            </button>
                            @if(request('search'))
                                <a href="{{ route('masters.purposes.index') }}" class="btn btn-outline-secondary">
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
                    @if($purposes->count() > 0)
                        - {{ $purposes->total() }}件の結果が見つかりました
                    @else
                        - 該当する目的が見つかりませんでした
                    @endif
                </div>
            @endif
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>目的コード</th>
                                <th>目的名</th>
                                <th>カテゴリ</th>
                                <th>状態</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purposes as $purpose)
                            <tr>
                                <td><code>{{ $purpose->purpose_code }}</code></td>
                                <td>{{ $purpose->purpose_name }}</td>
                                <td>
                                    @if($purpose->category)
                                        <span class="badge bg-secondary">{{ $purpose->category }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </td>
                                <td>
                                    @if($purpose->is_active)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('masters.purposes.show', $purpose) }}" 
                                           class="btn btn-sm btn-outline-info" title="詳細">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('masters.purposes.edit', $purpose) }}" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDelete(name) {
                                            return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="{{ route('masters.purposes.destroy', $purpose) }}" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirmDelete('{{ $purpose->purpose_name }}')">
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
                                <td colspan="6" class="text-center py-4">
                                    @if(request('search'))
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致する目的が見つかりませんでした</p>
                                            <p class="small">検索キーワードを変更してお試しください</p>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-tag display-6 mb-2"></i>
                                            <p class="mb-0">目的データが登録されていません</p>
                                            <p class="small">「新規登録」ボタンから最初の目的を登録してください</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                
                @if($purposes->hasPages())
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item {{ $purposes->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $purposes->previousPageUrl() }}">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                @php
                                    $current = $purposes->currentPage();
                                    $last = $purposes->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                @endphp
            
                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $purposes->url(1) }}">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif
            
                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $purposes->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endfor
            
                                @if($end < $last)
                                    @if($end < $last - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $purposes->url($last) }}">{{ $last }}</a>
                                    </li>
                                @endif
            
                                <li class="page-item {{ !$purposes->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $purposes->nextPageUrl() }}">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: {{ $purposes->firstItem() ?? 0 }} - {{ $purposes->lastItem() ?? 0 }} / 全 {{ $purposes->total() }} 件
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection