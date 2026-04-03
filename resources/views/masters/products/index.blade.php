@extends('layouts.app')

@section('title', '設計品マスター')

@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-box-seam me-2 text-primary"></i>設計品マスター</h4>
        <a href="{{ route('masters.products.create') }}" class="btn btn-primary">
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
                <form method="GET" action="{{ route('masters.products.index') }}" class="row g-3 align-items-end">
                    
                    <!-- 1. 搜索关键词 (品名) -->
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">検索キーワード</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="品名"
                               value="{{ request('search') }}">
                    </div>
                    
                    <!-- 2. 语言筛选 (新增) -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">言語</label>
                        <select name="language" class="form-select">
                            <option value="">-- すべて --</option>
                            <option value="1" {{ request('language') == '1' ? 'selected' : '' }}>日本語</option>
                            <option value="2" {{ request('language') == '2' ? 'selected' : '' }}>英語</option>
                        </select>
                    </div>
                    
                    <!-- 3. 按钮区域 -->
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request('search') || request('language'))
                            <a href="{{ route('masters.products.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> クリア
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- 【修改结束】 -->
    
    <!-- 搜索结果提示 (更新以显示语言条件) -->
    @if(request('search') || request('language'))
        <div class="alert alert-info mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>
                検索条件: 
                @if(request('search'))"<strong>{{ request('search') }}</strong>" @endif
                @if(request('search') && request('language')) / @endif
                @if(request('language'))
                    言語: <strong>{{ request('language') == '1' ? '日本語' : '英語' }}</strong>
                @endif
                
                @if($lists->count() > 0)
                    - {{ $lists->total() }}件の結果が見つかりました
                @else
                    - 該当する設計品が見つかりませんでした
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
                        <th class="text-center" style="width: 30%;">品名</th>
                        <th class="text-center" style="width: 15%;">言語</th>
                        <th class="text-center" style="width: 18%;">作成日</th>
                        <th class="text-center" style="width: 18%;">更新日</th>
                        <th class="text-center" style="width: 140px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lists as $product)
                    <tr>
                        <td class="text-center text-muted small">{{ $product->id }}</td>
                        <td class="text-center">
                            <span class="fw-bold text-dark">{{ $product->name }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark">{{ $product->language == 1 ? "日本語" : "英語" }}</span>
                        </td>
                        <td class="text-center small text-muted">
                            {{ \Carbon\Carbon::parse($product->created_at)->format('Y/m/d H:i') }}
                        </td>
                        <td class="text-center small text-muted">
                            {{ \Carbon\Carbon::parse($product->updated_at)->format('Y/m/d H:i') }}
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.products.show', $product) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.products.edit', $product) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <form action="{{ route('masters.products.destroy', $product) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('本当にこの設計品「{{ $product->name }}」を削除しますか？\nこの操作は元に戻せません。')">
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
                                    <p class="mb-0 fw-bold">検索条件に一致する設計品が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-box-seam display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">設計品データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の設計品を登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- 【更新】分页区域 (统一样式 + 行数选择) -->
    @if($lists->hasPages() || $lists->total() > 0)
        <div class="mt-4">
            <!-- 核心：align-items-center 会自动拉齐高度 -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                
                <!-- 1. 左侧：行数选择器 -->
                <div class="d-flex align-items-center">
                    <label for="per_page_select" class="form-label small text-muted mb-0 me-2">
                        表示件数:
                    </label>
                    <!-- 关键修改：使用 form-select-sm 并移除 style="width: auto;" 中的高度限制 -->
                    <select id="per_page_select" class="form-select form-select-sm" style="width: auto; line-height: 1.5;">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                    </select>
                </div>

                <!-- 2. 中间：分页链接 (保持原样) -->
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0"> 
                        <!-- 注意：这里加了 pagination-sm 以匹配下拉框的小号尺寸 -->
                        
                        <!-- 上一页 -->
                        <li class="page-item {{ $lists->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $lists->previousPageUrl() }}" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        @php
                            $current = $lists->currentPage();
                            $last = $lists->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item"><a class="page-link" href="{{ $lists->url(1) }}">1</a></li>
                            @if($start > 2)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $lists->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                            <li class="page-item"><a class="page-link" href="{{ $lists->url($last) }}">{{ $last }}</a></li>
                        @endif

                        <li class="page-item {{ !$lists->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $lists->nextPageUrl() }}" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- 3. 统计信息 -->
            <div class="text-center text-muted small mt-2">
                表示中：{{ $lists->firstItem() ?? 0 }} - {{ $lists->lastItem() ?? 0 }} / 全 {{ $lists->total() }} 件
            </div>
        </div>
    @endif
</div>

<!-- 【新增】JavaScript 处理行数选择 -->
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