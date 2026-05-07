@extends('layouts.app')

@section('title', '通貨・為替レートマスター')

@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-currency-exchange me-2 text-primary"></i>通貨・為替レートマスター</h4>
        <a href="{{ route('masters.currencies.create') }}" class="btn btn-primary">
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
                <!-- 修改点 1: form 类改为 align-items-end，确保所有列底部对齐 -->
                <form method="GET" action="{{ route('masters.currencies.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">検索キーワード</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="通貨名称"
                               value="{{ request('search') }}">
                    </div>
                    
                    <!-- 修改点 2: 移除 mt-4，使用 d-flex align-items-end 确保按钮组与输入框底部平齐 -->
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request('search'))
                            <a href="{{ route('masters.currencies.index') }}" class="btn btn-outline-secondary">
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
                @if($currencies->count() > 0)
                    - {{ $currencies->total() }}件の結果が見つかりました
                @else
                    - 該当する通貨が見つかりませんでした
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
                        <th class="text-center" style="width: 100px;">コード</th>
                        <th class="text-center" style="width: 100px;">通貨名称</th>
                        <th class="text-center" style="width: 80px;">記号</th>
                        <th class="text-center" style="width: 80px;">小数桁</th>
                        <th class="text-center" style="width: 120px;">対円レート</th>
                        <th class="text-center" style="width: 140px;">有効期間</th>
                        <th class="text-center" style="width: 80px;">ソート</th>
                        <th class="text-center" style="width: 160px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($currencies as $currency)
                    <tr>
                        <td class="text-center text-muted small">{{ $currency->id }}</td>
                        <td class="text-center fw-bold text-primary">{{ $currency->currency_code }}</td>
                        <td  class="text-center text-muted small"><span class="fw-medium">{{ $currency->currency_name }}</span></td>
                        <td class="text-center fs-5">{{ $currency->symbol }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $currency->decimal_digits }}</span>
                        </td>
                        <td class="text-center font-monospace">
                            {{ number_format($currency->rate_to_jpy, 6) }}
                        </td>
                        <td class="text-center small">
                            <div>{{ \Carbon\Carbon::parse($currency->rate_valid_from)->format('Y/m/d') }} ~ {{ $currency->rate_valid_to ? \Carbon\Carbon::parse($currency->rate_valid_to)->format('Y/m/d') : '無期限' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $currency->sort }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.currencies.show', $currency) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.currencies.edit', $currency) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- 删除确认脚本 (局部定义，避免全局污染) -->
                                <form action="{{ route('masters.currencies.destroy', $currency) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('本当にこの通貨「{{ $currency->currency_name }} ({{ $currency->currency_code }})」を削除しますか？\nこの操作は元に戻せません。')">
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
                        <td colspan="9" class="text-center py-5">
                            @if(request('search'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">検索条件に一致する通貨が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-currency-exchange display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">通貨データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の通貨を登録してください</p>
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
    @if($currencies->hasPages() || $currencies->total() > 0)
        <div class="mt-4">
            <!-- 使用 flex 布局实现左右排列，align-items-center 确保高度一致 -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                
                <!-- 1. 左侧：行数选择器 -->
                <div class="d-flex align-items-center">
                    <label for="per_page_select" class="form-label small text-muted mb-0 me-2">
                        表示件数:
                    </label>
                    <!-- 添加 form-select-sm 以匹配分页按钮的小号尺寸 -->
                    <select id="per_page_select" class="form-select form-select-sm" style="width: auto;">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                    </select>
                </div>

                <!-- 2. 中间：分页链接 -->
                <nav aria-label="Page navigation">
                    <!-- 添加 pagination-sm 以匹配下拉框的小号尺寸 -->
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item {{ $currencies->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $currencies->previousPageUrl() }}">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        @php
                            $current = $currencies->currentPage();
                            $last = $currencies->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $currencies->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $currencies->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $currencies->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif

                        <li class="page-item {{ !$currencies->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $currencies->nextPageUrl() }}">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- 3. 底部：统计信息 -->
            <div class="text-center text-muted small mt-2">
                表示中：{{ $currencies->firstItem() ?? 0 }} - {{ $currencies->lastItem() ?? 0 }} / 全 {{ $currencies->total() }} 件
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