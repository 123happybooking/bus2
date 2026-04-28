@extends('layouts.app')

@section('title', '会計周期マスター')

@section('content')
<div class="container-fluid">
    <!-- 顶部标题与按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-calendar-range me-2"></i>会計周期マスター</h4>
        <a href="{{ route('masters.account-periods.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> 新規追加
        </a>
    </div>

    <!-- 成功提示 -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <!-- 搜索栏 -->
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.account-periods.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="周期名称で検索"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    @if(request('search'))
                        <a href="{{ route('masters.account-periods.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> クリア
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    
    <!-- 搜索结果提示 -->
    @if(request('search'))
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            検索条件: "{{ request('search') }}" 
            @if($periods->count() > 0)
                - {{ $periods->total() }}件の結果が見つかりました
            @else
                - 該当する周期が見つかりませんでした
            @endif
        </div>
    @endif

    <!-- 数据表格 -->
    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary align-middle">
                    <tr>
                        <th width="200">周期名称</th>
                        <th width="150" class="text-center">開始日</th>
                        <th width="150" class="text-center">終了日</th>
                        <th width="150" class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $period)
                    <tr>
                        <td class="fw-bold">{{ $period->title }}</td>
                        <td class="text-center">{{ $period->start }}</td>
                        <td class="text-center">{{ $period->end }}</td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- 编辑 -->
                                <a href="{{ route('masters.account-periods.edit', $period) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- 删除 -->
                                <form action="{{ route('masters.account-periods.destroy', $period) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('本当にこの周期「{{ $period->title }}」を削除しますか？\nこの操作は元に戻せません。')">
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
                                    <p class="mb-0">検索条件に一致する周期が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-calendar-x display-6 mb-2"></i>
                                    <p class="mb-0">会計周期が登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の周期を登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- 分页 -->
    @if($periods->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $periods->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $periods->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $periods->currentPage();
                        $last = $periods->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $periods->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $periods->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $periods->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$periods->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $periods->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $periods->firstItem() ?? 0 }} - {{ $periods->lastItem() ?? 0 }} / 全 {{ $periods->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection