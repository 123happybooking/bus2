@extends('layouts.app')

@section('title', 'オプション')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-gear"></i> オプション</h4>
                <a href="{{ route('masters.options.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新規追加
                </a>
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
                    <form method="GET" action="{{ route('masters.options.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="オプション名・カテゴリで検索"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> 検索
                            </button>
                            @if(request('search'))
                                <a href="{{ route('masters.options.index') }}" class="btn btn-outline-secondary">
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
                    @if($options->count() > 0)
                        - {{ $options->total() }}件の結果が見つかりました
                    @else
                        - 該当するオプションが見つかりませんでした
                    @endif
                </div>
            @endif
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>オプション名</th>
                                <th>カテゴリ</th>
                                <th>説明</th>
                                <th>表示順</th>
                                <th width="80">状態</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($options as $option)
                            <tr>
                                <td>{{ $option->name }}</td>
                                <td>{{ $option->category }}</td>
                                <td>{{ $option->description ?? '-' }}</td>
                                <td class="text-center">{{ $option->display_order }}</td>
                                <td class="text-center">
                                    @if($option->is_active)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('masters.options.edit', $option) }}" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDeleteOption(name) {
                                            return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="{{ route('masters.options.destroy', $option) }}" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirmDeleteOption('{{ $option->name }}')">
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
                                            <p class="mb-0">検索条件に一致するオプションが見つかりませんでした</p>
                                            <p class="small">検索キーワードを変更してお試しください</p>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-gear display-6 mb-2"></i>
                                            <p class="mb-0">オプションデータが登録されていません</p>
                                            <p class="small">「新規追加」ボタンから最初のオプションを登録してください</p>
                                        </div>
                                    @endif
                                 </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($options->hasPages())
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item {{ $options->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $options->previousPageUrl() }}">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                @php
                                    $current = $options->currentPage();
                                    $last = $options->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                @endphp
            
                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $options->url(1) }}">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif
            
                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $options->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endfor
            
                                @if($end < $last)
                                    @if($end < $last - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $options->url($last) }}">{{ $last }}</a>
                                    </li>
                                @endif
            
                                <li class="page-item {{ !$options->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $options->nextPageUrl() }}">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: {{ $options->firstItem() ?? 0 }} - {{ $options->lastItem() ?? 0 }} / 全 {{ $options->total() }} 件
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection