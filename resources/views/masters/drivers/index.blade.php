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
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.drivers.index') }}" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="コード・氏名・免許種類で検索"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    @if(request()->hasAny(['search', 'branch_id', 'is_active', 'license_expiring']))
                        <a href="{{ route('masters.drivers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> クリア
                        </a>
                    @endif
                </div>
            </form>
        </div>
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
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary">
                    <tr>
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
                    @forelse($drivers as $driver)
                    <tr>
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
                        <td colspan="8" class="text-center py-4">
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
    
    @if($drivers->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $drivers->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $drivers->previousPageUrl() }}">
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
                            <a class="page-link" href="{{ $drivers->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $drivers->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $drivers->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$drivers->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $drivers->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $drivers->firstItem() ?? 0 }} - {{ $drivers->lastItem() ?? 0 }} / 全 {{ $drivers->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection