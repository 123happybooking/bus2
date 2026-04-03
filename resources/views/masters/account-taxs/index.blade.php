@extends('layouts.app')

@section('title', '税区分マスター')

@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-percent me-2 text-primary"></i>税区分マスター</h4>
        <a href="{{ route('masters.account-taxs.create') }}" class="btn btn-primary">
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
                <form method="GET" action="{{ route('masters.account-taxs.index') }}" class="row g-3 align-items-end">
                    <!-- 关键词搜索 -->
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">税区分名称 / Code</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="例：課税売上 10%, S10"
                               value="{{ request('search') }}">
                    </div>
                    
                    <!-- 税計算筛选 -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">税計算</label>
                        <select name="calculation_type" class="form-select">
                            <option value="">すべて</option>
                            <option value="外税" {{ request('calculation_type') == '外税' ? 'selected' : '' }}>外税</option>
                            <option value="内税" {{ request('calculation_type') == '内税' ? 'selected' : '' }}>内税</option>
                            <option value="不課税" {{ request('calculation_type') == '不課税' ? 'selected' : '' }}>不課税</option>
                            <option value="非課税" {{ request('calculation_type') == '非課税' ? 'selected' : '' }}>非課税</option>
                        </select>
                    </div>

                    <!-- インボイス筛选 -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">インボイス</label>
                        <select name="is_invoice_eligible" class="form-select">
                            <option value="">すべて</option>
                            <option value="1" {{ request('is_invoice_eligible') == '1' ? 'selected' : '' }}>適格</option>
                            <option value="2" {{ request('is_invoice_eligible') === '2' ? 'selected' : '' }}>非対象</option>
                        </select>
                    </div>
                    
                    <!-- 按钮组 -->
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request('search') || request('calculation_type') || request('is_invoice_eligible') !== null)
                            <a href="{{ route('masters.account-taxs.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> クリア
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 搜索结果提示 -->
    @if(request('search') || request('calculation_type') || request('is_invoice_eligible') !== null)
        <div class="alert alert-info mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>
                検索条件: 
                @if(request('search')) "<strong>{{ request('search') }}</strong>" @endif
                @if(request('calculation_type')) | 税計算:{{ request('calculation_type') }} @endif
                @if(request('is_invoice_eligible') !== null) | インボイス:{{ request('is_invoice_eligible') == '1' ? '適格' : '非対象' }} @endif
                
                @if($taxs->count() > 0)
                    - {{ $taxs->total() }}件の結果が見つかりました
                @else
                    - 該当する税区分が見つかりませんでした
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
                        <th class="text-center" style="width: 100;">Code</th>
                        <th class="text-center" style="width: 200;">税区分名称</th>
                        <th class="text-center" style="width: 100px;">税率</th>
                        <th class="text-center" style="width: 100px;">税計算</th>
                        <th class="text-center" style="width: 100px;">インボイス</th>
                        <th class="text-center" style="width: 160px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxs as $tax)
                    <tr>
                        <td class="text-center fw-bold">{{ $tax->id }}</td>
                        <!-- Code -->
                        <td class="text-center fw-bold text-primary">{{ $tax->code }}</td>
                        
                        <!-- 名称 -->
                        <td class="text-center fw-bold">{{ $tax->name }}</td>
                        
                        <!-- 税率 -->
                        <td class="text-center">
                            <span class="badge bg-light text-dark border fs-6">
                                {{ number_format($tax->rate, 2) }}%
                            </span>
                        </td>
                        
                        <!-- 税計算 -->
                        <td class="text-center">
                            @if($tax->calculation_type === '外税')
                                <span class="badge bg-primary">外税</span>
                            @elseif($tax->calculation_type === '内税')
                                <span class="badge bg-success">内税</span>
                            @elseif(in_array($tax->calculation_type, ['不課税', '非課税']))
                                <span class="badge bg-secondary">{{ $tax->calculation_type }}</span>
                            @else
                                <span class="badge bg-light text-dark border">{{ $tax->calculation_type }}</span>
                            @endif
                        </td>

                        <!-- インボイス -->
                        <td class="text-center">
                            @if($tax->is_invoice_eligible == 1)
                                <span class="badge bg-success">適格</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">非対象</span>
                            @endif
                        </td>

                        <!-- 操作按钮 -->
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.account-taxs.show', $tax) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.account-taxs.edit', $tax) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- 删除确认 -->
                                <form action="{{ route('masters.account-taxs.destroy', $tax) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('本当に税区分「{{ $tax->name }} ({{ $tax->code }})」を削除しますか？\n関連する取引データがある場合は削除できません。\nこの操作は元に戻せません。')">
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
                            @if(request('search') || request('calculation_type') || request('invoice') !== null)
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">検索条件に一致する税区分が見つかりませんでした</p>
                                    <p class="small">検索条件を変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-percent display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">税区分が登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の税区分を登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- 分页区域 (复用之前的逻辑) -->
    @if($taxs->hasPages() || $taxs->total() > 0)
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
                        <li class="page-item {{ $taxs->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $taxs->previousPageUrl() }}">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        @php
                            $current = $taxs->currentPage();
                            $last = $taxs->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $taxs->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $taxs->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $taxs->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif

                        <li class="page-item {{ !$taxs->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $taxs->nextPageUrl() }}">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- 统计信息 -->
            <div class="text-center text-muted small mt-2">
                表示中：{{ $taxs->firstItem() ?? 0 }} - {{ $taxs->lastItem() ?? 0 }} / 全 {{ $taxs->total() }} 件
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