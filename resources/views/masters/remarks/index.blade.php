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
            
            <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
                <form method="GET" action="{{ route('masters.remarks.index') }}" class="row g-2">
                    <div class="col">
                        <input type="text" name="search" class="form-control form-control-sm" style="border-color: #E5E7EB;" 
                               placeholder="コード・タイトル・内容・カテゴリで検索"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm px-3" 
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('masters.remarks.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                            クリア
                        </a>
                    </div>
                </form>
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
                    <table class="table table-sm table-bordered mb-0 table-list">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th width="100">コード</th>
                                <th>タイトル</th>
                                <th width="120">カテゴリ</th>
                                <th>備考本文（定型文）</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($remarks as $index => $remark)
                            <tr>
                                <td>{{ $remarks->firstItem() + $index }}</td>
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
                                <td colspan="6" class="text-center py-4">
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

                
                @if($remarks->hasPages() || $remarks->total() > 0)
                    <div class="mt-3">
                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                            
                            <div class="d-flex align-items-center">
                                <label for="per_page_select" class="form-label small text-muted mb-0 me-2" style="white-space: nowrap;">
                                    表示件数:
                                </label>
                                <select id="per_page_select" class="form-select form-select-sm" style="font-size: 0.75rem; min-width: 80px;">
                                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                                    <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                                </select>
                            </div>
                
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item {{ $remarks->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $remarks->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
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
                                            <a class="page-link" href="{{ $remarks->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                                        @endif
                                    @endif
                
                                    @for($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $remarks->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                                        </li>
                                    @endfor
                
                                    @if($end < $last)
                                        @if($end < $last - 1)
                                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $remarks->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                                        </li>
                                    @endif
                
                                    <li class="page-item {{ !$remarks->hasMorePages() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $remarks->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                
                        <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                            表示中：{{ $remarks->firstItem() ?? 0 }} - {{ $remarks->lastItem() ?? 0 }} / 全 {{ $remarks->total() }} 件
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.getElementById('per_page_select').addEventListener('change', function() {
    const url = new URL(window.location.href);
    const search = document.querySelector('input[name="search"]')?.value;
    url.searchParams.set('per_page', this.value);
    if (search) {
        url.searchParams.set('search', search);
    }
    window.location.href = url.toString();
});
</script>
@endpush