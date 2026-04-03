@extends('layouts.app')

@section('title', '補助科目マスター')

@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-collection me-2 text-primary"></i>補助科目マスター</h4>
        <a href="{{ route('masters.account-subs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> 新規追加
        </a>
    </div>

    <!-- 成功提示 -->
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
                <form method="GET" action="{{ route('masters.account-subs.index') }}" class="row g-3 align-items-end">
                    <!-- 搜索关键词 -->
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">検索キーワード</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="補助科目名、勘定科目コード"
                               value="{{ request('search') }}">
                    </div>
                    
                    <!-- 筛选：所属主科目 -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">勘定科目 (親)</label>
                        <select name="account_id" class="form-select">
                            <option value="">全て</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 筛选：状态 -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">状態</label>
                        <select name="is_active" class="form-select">
                            <option value="">全て</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>有効</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>無効</option>
                        </select>
                    </div>
                    
                    <!-- 按钮组 -->
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request('search') || request('account_id') || request('is_active'))
                            <a href="{{ route('masters.account-subs.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> クリア
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 搜索结果提示 -->
    @if(request('search') || request('account_id') || request('is_active'))
        <div class="alert alert-info mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>
                検索条件: 
                @if(request('search')) "<strong>{{ request('search') }}</strong>" @endif
                @if(request('account_id')) | 勘定科目: {{ $accounts->firstWhere('id', request('account_id'))->code ?? 'Unknown' }} @endif
                @if(request('is_active') !== null) | 状態: {{ request('is_active') == '1' ? '有効' : '無効' }} @endif
                
                @if($subsidiaries->count() > 0)
                    - {{ $subsidiaries->total() }}件の結果が見つかりました
                @else
                    - 該当する補助科目が見つかりませんでした
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
                        <th class="text-center" style="width: 60px;">ID</th>
                        <th class="text-center" style="width: 250px;">補助科目名</th>
                        <th class="text-center" style="width: 200px;">勘定科目</th>
                        <th class="text-center" style="width: 80px;">状態</th>
                        <th class="text-center" style="width: 150px;">最終更新</th>
                        <th class="text-center" style="width: 160px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subsidiaries as $sub)
                    <tr>
                        <td class="text-center text-muted small">{{ $sub->id }}</td>
                        <td>
                            <span class="fw-bold text-dark">{{ $sub->name }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary me-1">{{ $sub->account->code ?? ''}}</span>
                            <span class="text-muted small">{{ $sub->account->name ?? ''}}</span>
                        </td>
                        <td class="text-center">
                            @if($sub->is_active)
                                <span class="badge bg-success">有効</span>
                            @else
                                <span class="badge bg-danger">無効</span>
                            @endif
                        </td>
                        <td class="text-center small text-muted">
                            {{ \Carbon\Carbon::parse($sub->updated_at)->format('Y/m/d H:i') }}
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.account-subs.show', $sub) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.account-subs.edit', $sub) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- 删除确认脚本 -->
                                <form action="{{ route('masters.account-subs.destroy', $sub) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('本当にこの補助科目「{{ $sub->name }}」を削除しますか？\nこの操作は元に戻せません。')">
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
                        <td colspan="6" class="text-center py-5">
                            @if(request('search') || request('account_id') || request('is_active'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">検索条件に一致する補助科目が見つかりませんでした</p>
                                    <p class="small">検索キーワードやフィルター条件を変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-collection display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">補助科目データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の補助科目を登録してください</p>
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
    @if($subsidiaries->hasPages() || $subsidiaries->total() > 0)
        <div class="mt-4">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                
                <!-- 1. 左侧：行数选择器 -->
                <div class="d-flex align-items-center">
                    <label for="per_page_select" class="form-label small text-muted mb-0 me-2">
                        表示件数:
                    </label>
                    <select id="per_page_select" class="form-select form-select-sm" style="width: auto;">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                    </select>
                </div>

                <!-- 2. 中间：分页链接 -->
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item {{ $subsidiaries->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $subsidiaries->previousPageUrl() }}">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        @php
                            $current = $subsidiaries->currentPage();
                            $last = $subsidiaries->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $subsidiaries->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $subsidiaries->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $subsidiaries->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif

                        <li class="page-item {{ !$subsidiaries->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $subsidiaries->nextPageUrl() }}">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- 3. 底部：统计信息 -->
            <div class="text-center text-muted small mt-2">
                表示中：{{ $subsidiaries->firstItem() ?? 0 }} - {{ $subsidiaries->lastItem() ?? 0 }} / 全 {{ $subsidiaries->total() }} 件
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const perPageSelect = document.getElementById('per_page_select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const newPerPage = this.value;
            const url = new URL(window.location.href);
            
            // 设置新的 per_page 参数
            url.searchParams.set('per_page', newPerPage);
            
            // 重置到第一页
            url.searchParams.set('page', '1');
            
            // 跳转
            window.location.href = url.toString();
        });
    }
});
</script>
@endsection