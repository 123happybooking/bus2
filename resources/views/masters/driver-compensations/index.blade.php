@extends('layouts.app')

@section('title', 'ドライバー報酬')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-cash-stack"></i> ドライバー報酬</h4>
                <a href="{{ route('masters.driver-compensations.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新規追加
                </a>
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
                <form method="GET" action="{{ route('masters.driver-compensations.index') }}" class="row g-2">
                    <div class="col">
                        <input type="text" name="search" class="form-control form-control-sm" style="border-color: #E5E7EB;" 
                               placeholder="ドライバー名、報酬種別、対象日、備考で検索"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select name="driver_id" class="form-select form-select-sm" style="border-color: #E5E7EB; width: 150px;">
                            <option value="">全てのドライバー</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm px-3" 
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('masters.driver-compensations.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                            クリア
                        </a>
                    </div>
                </form>
            </div>
            
            @if(request('search') || request('driver_id'))
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    検索条件: 
                    @if(request('search')) "{{ request('search') }}" @endif
                    @if(request('driver_id')) ドライバー: {{ $drivers->firstWhere('id', request('driver_id'))->name ?? '' }} @endif
                    @if($compensations->count() > 0)
                        - {{ $compensations->total() }}件の結果が見つかりました
                    @else
                        - 該当する報酬が見つかりませんでした
                    @endif
                </div>
            @endif
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 table-list">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>対象日</th>
                                <th>ドライバー</th>
                                <th>報酬種別</th>
                                <th>単価</th>
                                <th>数量</th>
                                <th>金額</th>
                                <th>運行ID</th>
                                <th>行程ID</th>
                                <th>備考</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($compensations as $compensation)
                            <tr>
                                <td>{{ $compensation->id }}</td>
                                <td>{{ $compensation->target_date }}</td>
                                <td>{{ $compensation->driver->name ?? '-' }}</td>
                                <td>{{ $compensation->compensationType->comp_name ?? '-' }}</td>
                                <td class="text-right">{{ number_format($compensation->price) }}</td>
                                <td class="text-right">{{ number_format($compensation->qty) }}</td>
                                <td class="text-right">{{ number_format($compensation->amount) }}</td>
                                <td class="text-center">{{ $compensation->bus_assignment_id ?? '-' }}</td>
                                <td class="text-center">{{ $compensation->itinerary_id ?? '-' }}</td>
                                <td>{{ $compensation->remark ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('masters.driver-compensations.edit', $compensation) }}" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDelete(id, name) {
                                            return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="{{ route('masters.driver-compensations.destroy', $compensation) }}" method="POST" 
                                              class="d-inline" onsubmit="return confirmDelete({{ $compensation->id }}, '{{ $compensation->id }}')">
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
                                <td colspan="11" class="text-center py-4">
                                    @if(request('search') || request('driver_id'))
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致する報酬が見つかりませんでした</p>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-cash-stack display-6 mb-2"></i>
                                            <p class="mb-0">報酬データが登録されていません</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($compensations->hasPages() || $compensations->total() > 0)
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
                                    <li class="page-item {{ $compensations->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $compensations->previousPageUrl() }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">&laquo;</a>
                                    </li>

                                    @php
                                        $current = $compensations->currentPage();
                                        $last = $compensations->lastPage();
                                        $start = max(1, $current - 2);
                                        $end = min($last, $current + 2);
                                    @endphp

                                    @if($start > 1)
                                        <li class="page-item"><a class="page-link" href="{{ $compensations->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a></li>
                                        @if($start > 2)<li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>@endif
                                    @endif

                                    @for($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $compensations->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                                        </li>
                                    @endfor

                                    @if($end < $last)
                                        @if($end < $last - 1)<li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>@endif
                                        <li class="page-item"><a class="page-link" href="{{ $compensations->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a></li>
                                    @endif

                                    <li class="page-item {{ !$compensations->hasMorePages() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $compensations->nextPageUrl() }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">&raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                            表示中：{{ $compensations->firstItem() ?? 0 }} - {{ $compensations->lastItem() ?? 0 }} / 全 {{ $compensations->total() }} 件
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
    const driverId = document.querySelector('select[name="driver_id"]')?.value;
    url.searchParams.set('per_page', this.value);
    if (search) url.searchParams.set('search', search);
    if (driverId) url.searchParams.set('driver_id', driverId);
    window.location.href = url.toString();
});
</script>
@endpush