@extends('layouts.app')

@section('title', '運転手マスター')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-person-badge me-2"></i>運転手マスター</h4>
        <a href="{{ route('masters.drivers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

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
    
    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.drivers.index') }}" class="row g-2">
            <div class="col">
                <input type="text" name="search" class="form-control form-control-sm" style="border-color: #E5E7EB;" 
                       placeholder="コード・氏名・免許種類で検索"
                       value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm px-3" 
                        style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                    検索
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('masters.drivers.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
                   style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                    クリア
                </a>
            </div>
        </form>
    </div>
    
    @if(request()->hasAny(['search', 'branch_id', 'is_active', 'license_expiring']))
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            検索条件: 
            @php
                $filters = [];
                if(request('search')) $filters[] = 'キーワード: "' . request('search') . '"';
                if(request('branch_id')) {
                    $branch = $branches->firstWhere('id', request('branch_id'));
                    if($branch) $filters[] = '支店: ' . $branch->branch_name;
                }
                if(request('is_active') !== '') {
                    $filters[] = '状態: ' . (request('is_active') ? '有効' : '無効');
                }
                if(request('license_expiring')) {
                    $filters[] = '免許期限間近のみ';
                }
            @endphp
            {{ implode('、', $filters) }}
            
            @if($drivers->count() > 0)
                - {{ $drivers->total() }}件の結果が見つかりました
            @else
                - 該当する運転手が見つかりませんでした
            @endif
        </div>
    @endif

    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 table-list">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>コード</th>
                        <th>氏名</th>
                        <th>支店</th>
                        <th>電話番号</th>
                        <th>免許種類</th>
                        <th>免許有効期限</th>
                        <th>状態</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $index => $driver)
                    <tr>
                        <td>{{ $drivers->firstItem() + $index }}</td>
                        <td>{{ $driver->driver_code }}</td>
                        <td>
                            <div>{{ $driver->name }}</div>
                            <div class="small text-muted">{{ $driver->name_kana }}</div>
                        </td>
                        <td>
                            @if($driver->branch)
                                <div class="small text-muted">{{ $driver->branch->branch_code }}</div>
                                <div>{{ $driver->branch->branch_name }}</div>
                            @else
                                <span class="text-muted">未設定</span>
                            @endif
                        </td>
                        <td>{{ $driver->phone_number }}</td>
                        <td>{{ $driver->license_type }}</td>
                        <td>
                            @php
                                $expirationDate = \Carbon\Carbon::parse($driver->license_expiration_date);
                                $daysRemaining = now()->diffInDays($expirationDate, false);
                                $daysRemainingInt = (int)round($daysRemaining);
                                $isExpiring = $daysRemainingInt <= 30 && $daysRemainingInt >= 0;
                                $isExpired = $daysRemainingInt < 0;
                            @endphp
                            <div class="{{ $isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : '') }}">
                                {{ $expirationDate->format('Y-m-d') }}
                                @if($driver->is_active)
                                    @if($isExpired)
                                        <span class="badge bg-danger">期限切れ</span>
                                    @elseif($isExpiring)
                                        <span class="badge bg-warning">間近</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($driver->is_active)
                                <span class="badge bg-success">有効</span>
                            @else
                                <span class="badge bg-secondary">無効</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.drivers.show', $driver) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.drivers.edit', $driver) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当に削除しますか？この操作は元に戻せません。');
                                }
                                </script>
                                <form action="{{ route('masters.drivers.destroy', $driver) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('{{ $driver->name }}')">
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
                        <td colspan="9" class="text-center py-4">
                            @if(request()->hasAny(['search', 'branch_id', 'is_active', 'license_expiring']))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致する運転手が見つかりませんでした</p>
                                    <p class="small">検索条件を変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-person display-6 mb-2"></i>
                                    <p class="mb-0">運転手データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の運転手を登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($drivers->hasPages() || $drivers->total() > 0)
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
                        <li class="page-item {{ $drivers->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $drivers->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
    
                        @php
                            $current = $drivers->currentPage();
                            $last = $drivers->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp
    
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $drivers->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                        @endif
    
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $drivers->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                            </li>
                        @endfor
    
                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $drivers->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                            </li>
                        @endif
    
                        <li class="page-item {{ !$drivers->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $drivers->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
    
            <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                表示中：{{ $drivers->firstItem() ?? 0 }} - {{ $drivers->lastItem() ?? 0 }} / 全 {{ $drivers->total() }} 件
            </div>
        </div>
    @endif
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