@extends('layouts.app')

@section('title', '備考マスター')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-chat-text"></i>備考マスター</h4>
                <a href="{{ route('masters.remarks.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
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
                    <form method="GET" action="{{ route('masters.remarks.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="コード・タイトル・内容・カテゴリで検索"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> 検索
                            </button>
                            @if(request('search'))
                                <a href="{{ route('masters.remarks.index') }}" class="btn btn-outline-secondary">
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
                    @if($remarks->count() > 0)
                        - {{ $remarks->total() }}件の結果が見つかりました
                    @else
                        - 該当する備考が見つかりませんでした
                    @endif
                </div>
            @endif
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th width="100">コード</th>
                                <th>タイトル</th>
                                <th width="120">カテゴリ</th>
                                <th>備考本文（定型文）</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($remarks as $remark)
                            <tr>
                                <td><code>{{ $remark->remark_code }}</code></td>
                                <td>
                                    <div class="fw-bold">{{ $remark->title }}</div>
                                </td>
                                <td>
                                    @if($remark->category)
                                        <span class="badge bg-info">{{ $remark->category }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ Str::limit($remark->content, 80) }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('masters.remarks.show', $remark) }}" 
                                           class="btn btn-sm btn-outline-info" title="詳細">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('masters.remarks.edit', $remark) }}" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDelete(name) {
                                            return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="{{ route('masters.remarks.destroy', $remark) }}" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirmDelete('{{ $remark->title }}')">
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
                                <td colspan="5" class="text-center py-4">
                                    @if(request('search'))
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致する備考が見つかりませんでした</p>
                                            <p class="small">検索キーワードを変更してお試しください</p>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-chat-text display-6 mb-2"></i>
                                            <p class="mb-0">備考データが登録されていません</p>
                                            <p class="small">「新規登録」ボタンから最初の備考を登録してください</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                
                @if($remarks->hasPages())
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item {{ $remarks->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $remarks->previousPageUrl() }}">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                @php
                                    $current = $remarks->currentPage();
                                    $last = $remarks->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                @endphp
            
                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $remarks->url(1) }}">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif
            
                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $remarks->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endfor
            
                                @if($end < $last)
                                    @if($end < $last - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $remarks->url($last) }}">{{ $last }}</a>
                                    </li>
                                @endif
            
                                <li class="page-item {{ !$remarks->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $remarks->nextPageUrl() }}">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: {{ $remarks->firstItem() ?? 0 }} - {{ $remarks->lastItem() ?? 0 }} / 全 {{ $remarks->total() }} 件
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection