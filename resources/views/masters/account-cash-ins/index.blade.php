@extends('layouts.app')
@section('title', '現金入力マスター')
@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-cash-plus me-2 text-primary"></i>現金入力マスター</h4>
        <a href="{{ route('masters.account-cash-ins.create') }}" class="btn btn-primary">
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
                <form method="GET" action="{{ route('masters.account-cash-ins.index') }}" class="row g-3 align-items-end">
                    <!-- 1. 搜索关键词 (タイトル) -->
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">検索キーワード</label>
                        <input type="text" name="search" class="form-control" placeholder="タイトル" value="{{ request('search') }}">
                    </div>
                    <!-- 2. 按钮区域 -->
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request('search'))
                            <a href="{{ route('masters.account-cash-ins.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> クリア
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 搜索结果提示 -->
    @if(request('search'))
        <div class="alert alert-info mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>
                検索条件: "<strong>{{ request('search') }}</strong>"
                @if($cashIns->count() > 0)
                    - {{ $cashIns->total() }}件の結果が見つかりました
                @else
                    - 該当する現金入力が見つかりませんでした
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
                        <th class="text-center" style="width: 20%;">名称</th>
                        <th class="text-center" style="width: 15%;">モード</th>
                        <th class="text-center" style="width: 15%;">タイプID</th>
                        <th class="text-center" style="width: 15%;">ソート</th>
                        <th class="text-center" style="width: 150px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashIns as $item)
                    <tr>
                        <td class="text-center text-muted small">{{ $item->id }}</td>
                        <td class="text-center">
                            <span class="fw-bold text-dark">{{ $item->title }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badgey">{{ $item->mode==1 ? '貸借対照表' : ($item->mode==2 ? '損益計算書' : '前期の利益処分計算書のうち') }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badgey">{{ $types[$item->type_id] ?? '' }}</span>
                        </td>
                        <td class="text-center small text-muted">
                            {{ $item->sort }}
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.account-cash-ins.edit', $item) }}" class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('masters.account-cash-ins.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('本当にこの現金入力「{{ $item->title }}」を削除しますか？\nこの操作は元に戻せません。')">
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
                            @if(request('search'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">検索条件に一致する現金入力が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-cash-plus display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">現金入力データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初のデータを登録してください</p>
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
    @if($cashIns->hasPages() || $cashIns->total() > 0)
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
                    <!-- 上一页 -->
                    <li class="page-item {{ $cashIns->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $cashIns->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @php 
                        $current = $cashIns->currentPage(); 
                        $last = $cashIns->lastPage(); 
                        $start = max(1, $current - 2); 
                        $end = min($last, $current + 2); 
                    @endphp
                    @if($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $cashIns->url(1) }}">1</a></li>
                        @if($start > 2)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                    @endif
                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $cashIns->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    @if($end < $last)
                        @if($end < $last - 1)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                        <li class="page-item"><a class="page-link" href="{{ $cashIns->url($last) }}">{{ $last }}</a></li>
                    @endif
                    <li class="page-item {{ !$cashIns->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $cashIns->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <!-- 3. 统计信息 -->
        <div class="text-center text-muted small mt-2">
            表示中：{{ $cashIns->firstItem() ?? 0 }} - {{ $cashIns->lastItem() ?? 0 }} / 全 {{ $cashIns->total() }} 件
        </div>
    </div>
    @endif
</div>

<!-- JavaScript 处理行数选择 -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const perPageSelect = document.getElementById('per_page_select');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', this.value);
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            });
        }
    });
</script>
@endsection