@extends('layouts.app')

@section('title', '車両管理')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-truck"></i>車両管理</h4>
        <a href="{{ route('masters.vehicles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>
    
    <div class="row">
        <div class="col-md-12">
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
            
            <div class="mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('masters.vehicles.index') }}" class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="車両コード・登録番号・車種・営業所で検索"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> 検索
                            </button>
                            @if(request('search'))
                                <a href="{{ route('masters.vehicles.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> クリア
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            
            @if(request('search'))
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    検索条件: "{{ request('search') }}" 
                    @if($vehicles->count() > 0)
                        - {{ $vehicles->total() }}件の結果が見つかりました
                    @else
                        - 該当する車両が見つかりませんでした
                    @endif
                </div>
            @endif
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>車両コード</th>
                                <th>登録番号</th>
                                <th>車種</th>
                                <th>モデル</th>
                                <th>所属営業所</th>
                                <th>乗車定員</th>
                                <th>所有形態</th>
                                <th>車検満了日</th>
                                <th>状態</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicles as $vehicle)
                            <tr>
                                <td><code>{{ $vehicle->vehicle_code }}</code></td>
                                <td>{{ $vehicle->registration_number }}</td>
                                <td>
                                    @if($vehicle->vehicleType)
                                        {{ $vehicle->vehicleType->type_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </td>
                                <td>
                                    @if($vehicle->vehicleModel)
                                        {{ $vehicle->vehicleModel->model_name }}
                                        @if($vehicle->vehicleModel->maker)
                                            <small class="text-muted">({{ $vehicle->vehicleModel->maker }})</small>
                                        @endif
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </td>
                                <td>
                                    @if($vehicle->branch)
                                        {{ $vehicle->branch->branch_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </td>
                                <td>{{ $vehicle->seating_capacity }}名</td>
                                <td>
                                    @php
                                        $ownershipTypes = [
                                            'company' => '会社所有',
                                            'rental' => 'レンタル',
                                            'personal' => '個人所有'
                                        ];
                                    @endphp
                                    <span class="badge bg-info">{{ $ownershipTypes[$vehicle->ownership_type] ?? $vehicle->ownership_type }}</span>
                                </td>
                                <td>
                                    @php
                                        $date = $vehicle->inspection_expiration_date;
                                        if ($date instanceof \Carbon\Carbon) {
                                            $formattedDate = $date->format('Y/m/d');
                                            $daysRemaining = now()->startOfDay()->diffInDays($date->startOfDay(), false);
                                        } else {
                                            try {
                                                $carbonDate = \Carbon\Carbon::parse($date)->startOfDay();
                                                $formattedDate = $carbonDate->format('Y/m/d');
                                                $daysRemaining = now()->startOfDay()->diffInDays($carbonDate, false);
                                            } catch (Exception $e) {
                                                $formattedDate = $date;
                                                $daysRemaining = null;
                                            }
                                        }
                                    @endphp
                                    {{ $formattedDate }}
                                    @if(isset($daysRemaining))
                                        @if($daysRemaining < 0)
                                            <span class="badge bg-danger ms-1">切れ</span>
                                        @elseif($daysRemaining <= 30 && $daysRemaining >= 0)
                                            <span class="badge bg-warning ms-1">残{{ $daysRemaining }}日</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($vehicle->is_active)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('masters.vehicles.show', $vehicle) }}" 
                                           class="btn btn-sm btn-outline-info" title="詳細">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('masters.vehicles.edit', $vehicle) }}" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDelete(vehicleInfo) {
                                            return confirm(`以下の車両を削除しますか？\n\n${vehicleInfo}\n\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="{{ route('masters.vehicles.destroy', $vehicle) }}" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirmDelete('車両コード: {{ $vehicle->vehicle_code }}')">
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
                                <td colspan="10" class="text-center py-4">
                                    @if(request('search'))
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致する車両が見つかりませんでした</p>
                                            <p class="small">検索キーワードを変更してお試しください</p>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-truck display-6 mb-2"></i>
                                            <p class="mb-0">車両データが登録されていません</p>
                                            <p class="small">「新規登録」ボタンから最初の車両を登録してください</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                
                @if($vehicles->hasPages())
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item {{ $vehicles->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $vehicles->previousPageUrl() }}">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                @php
                                    $current = $vehicles->currentPage();
                                    $last = $vehicles->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                @endphp
            
                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $vehicles->url(1) }}">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif
            
                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $vehicles->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endfor
            
                                @if($end < $last)
                                    @if($end < $last - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $vehicles->url($last) }}">{{ $last }}</a>
                                    </li>
                                @endif
            
                                <li class="page-item {{ !$vehicles->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $vehicles->nextPageUrl() }}">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: {{ $vehicles->firstItem() ?? 0 }} - {{ $vehicles->lastItem() ?? 0 }} / 全 {{ $vehicles->total() }} 件
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection