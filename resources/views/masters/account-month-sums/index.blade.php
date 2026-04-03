@extends('layouts.app')
@section('title', '月度汇总')

@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-calendar3 me-2 text-primary"></i>月度汇总管理</h4>
        <a href="{{ route('masters.account-month-sums.create') }}" class="btn btn-primary">
            更新
        </a>
    </div>

    <!-- 成功/错误提示 -->
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
    <!-- <div class="mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('masters.account-month-sums.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">検索キーワード</label>
                        <input type="text" name="search" class="form-control" placeholder="年份或月份 (如: 2024 或 01)" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request('search'))
                            <a href="{{ route('masters.account-month-sums.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> クリア
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div> -->

    <!-- 搜索结果提示 -->
    @if(request('search'))
        <div class="alert alert-info mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>
                検索条件: "<strong>{{ request('search') }}</strong>"
                @if($sums->count() > 0)
                    - {{ $sums->total() }}件の結果が見つかりました
                @else
                    - 該当するデータが見つかりませんでした
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
                        <th class="text-center" style="width: 150px;">年份</th>
                        <th class="text-center" style="width: 150px;">月份</th>
                        <th class="text-center" style="width: 200px;">操作人</th>
                        <th class="text-center" style="width: 160px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sums as $sum)
                    <tr>
                        <td class="text-center text-muted small">{{ $sum->id }}</td>
                        <td class="text-center fw-medium text-dark">{{ $sum->year }}</td>
                        <td class="text-center text-primary fw-bold">{{ $sum->month }}</td>
                        <td class="text-center small">{{ $sum->created_by }}</td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- 查看详情 -->
                                <a href="{{ route('masters.account-month-sums.show', $sum) }}" class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <!-- 编辑 -->
                                <!-- <a href="{{ route('masters.account-month-sums.edit', $sum) }}" class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a> -->
                                <!-- 删除 -->
                                <!-- <form action="{{ route('masters.account-month-sums.destroy', $sum) }}" method="POST" class="d-inline" onsubmit="return confirm('本当にこのデータ「{{ $sum->year }}年{{ $sum->month }}月」を削除しますか？\nこの操作は元に戻せません。')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="削除">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form> -->
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            @if(request('search'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">検索条件に一致するデータが見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-calendar3 display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">月度データが登録されていません</p>
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
    @if($sums->hasPages() || $sums->total() > 0)
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
                    <li class="page-item {{ $sums->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $sums->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @php
                        $current = $sums->currentPage();
                        $last = $sums->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp
                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $sums->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        @endif
                    @endif
                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $sums->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    @if($end < $last)
                        @if($end < $last - 1)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $sums->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif
                    <li class="page-item {{ !$sums->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $sums->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- 3. 底部：统计信息 -->
            <div class="text-center text-muted small mt-2">
                表示中：{{ $sums->firstItem() ?? 0 }} - {{ $sums->lastItem() ?? 0 }} / 全 {{ $sums->total() }} 件
            </div>
        </div>
    </div>
    @endif
</div>

<!-- 分页脚本 (保持不变) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const perPageSelect = document.getElementById('per_page_select');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                const newPerPage = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', newPerPage);
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            });
        }
    });
</script>
@endsection