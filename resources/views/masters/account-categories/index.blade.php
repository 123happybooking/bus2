@extends('layouts.app')

@section('title', '勘定科目区分マスター')

@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-list-check me-2 text-primary"></i>勘定科目区分マスター</h4>
        <a href="{{ route('masters.account-categories.create') }}" class="btn btn-primary">
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

    <!-- 搜索与筛选区域 -->
    <div class="mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('masters.account-categories.index') }}" class="row g-3 align-items-end">
                    <!-- 关键词搜索 -->
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">区分名称</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="例：流動資産"
                               value="{{ request('search') }}">
                    </div>
                    
                    <!-- 层级筛选 -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">レベル (階層)</label>
                        <select name="level" class="form-select">
                            <option value="">すべて</option>
                            <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>五大要素</option>
                            <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>大分類</option>
                            <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>中分類</option>
                        </select>
                    </div>

                    <!-- 借贷方向筛选 -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">貸借区分</label>
                        <select name="mark" class="form-select">
                            <option value="">すべて</option>
                            <option value="借" {{ request('mark') == '借' ? 'selected' : '' }}>借</option>
                            <option value="貸" {{ request('mark') == '貸' ? 'selected' : '' }}>貸</option>
                        </select>
                    </div>
                    
                    <!-- 按钮组 -->
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request('search') || request('level') || request('mark'))
                            <a href="{{ route('masters.account-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> クリア
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 搜索结果提示 -->
    @if(request('search') || request('level') || request('mark'))
        <div class="alert alert-info mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>
                検索条件: 
                @if(request('search')) "<strong>{{ request('search') }}</strong>" @endif
                @if(request('level')) | レベル:{{ request('level') }} @endif
                @if(request('mark')) | 貸借:{{ request('mark') }} @endif
                
                @if($categories->count() > 0)
                    - {{ $categories->total() }}件の結果が見つかりました
                @else
                    - 該当する区分が見つかりませんでした
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
                        <th class="text-center" style="width: 50px;">ID</th>
                        <th class="text-start" style="width: 100px;">区分名称</th>   
                        <th class="text-center" style="width: 80px;">貸借</th>
                        <th class="text-center" style="width: 80px;">类别</th>
                        <th class="text-center" style="width: 100px;">レベル</th>
                        <th class="text-center" style="width: 160px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="text-center text-muted small">{{ $category->id }}</td>
                        <!-- 名称 -->
                        <td class="text-start fw-bold">{{ $category->name }}</td>

                        <!-- 借贷方向显示 (颜色区分) -->
                        <td class="text-center">
                             <span class="badge bg-light text-dark border">{{ $category->mark }}</span>
                        </td>
                         <td class="text-start fw-bold">{{ $category->type }}</td>
                        
                        <!-- 层级显示 -->
                        <td class="text-center">
                            @if($category->level == 1)
                                <span class="text-start fw-bold">五大要素</span>
                            @elseif($category->level == 2)
                                <span class="text-start fw-bold">大分類</span>
                            @elseif($category->level == 3)
                                <span class="text-start fw-bold">中分類</span>
                            @endif
                        </td>
                        
                        
                        <!-- 操作按钮 -->
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.account-categories.show', $category) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.account-categories.edit', $category) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- 删除确认 -->
                                <form action="{{ route('masters.account-categories.destroy', $category) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('本当にこの区分「{{ $category->name }}」を削除しますか？\n関連する勘定科目がある場合は削除できません。\nこの操作は元に戻せません。')">
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
                        <td colspan="5" class="text-center py-5">
                            @if(request('search') || request('level') || request('mark'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">検索条件に一致する区分が見つかりませんでした</p>
                                    <p class="small">検索条件を変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-list-check display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">勘定科目区分が登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の区分を登録してください</p>
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
    @if($categories->hasPages() || $categories->total() > 0)
        <div class="mt-4">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                
                <!-- 行数选择器 -->
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

                <!-- 分页链接 -->
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item {{ $categories->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $categories->previousPageUrl() }}">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        @php
                            $current = $categories->currentPage();
                            $last = $categories->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $categories->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $categories->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $categories->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif

                        <li class="page-item {{ !$categories->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $categories->nextPageUrl() }}">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- 统计信息 -->
            <div class="text-center text-muted small mt-2">
                表示中：{{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} / 全 {{ $categories->total() }} 件
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
            
            url.searchParams.set('per_page', newPerPage);
            url.searchParams.set('page', '1'); // 重置到第一页
            
            window.location.href = url.toString();
        });
    }
});
</script>
@endsection