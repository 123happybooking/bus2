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
            
            <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
                <form method="GET" action="{{ route('masters.vehicles.index') }}" class="row g-2">
                    <div class="col">
                        <input type="text" name="search" class="form-control form-control-sm" style="border-color: #E5E7EB;" 
                               placeholder="車両コード・登録番号・車種・営業所で検索"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm px-3" 
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('masters.vehicles.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                            クリア
                        </a>
                    </div>
                </form>
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
                    <table class="table table-sm table-bordered mb-0 table-list">
                        <thead>
                            <tr>
                                <th>No.</th>
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
                            @forelse($vehicles as $index => $vehicle)
                            <tr>
                                <td>{{ $vehicles->firstItem() + $index }}</td>
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
                                <td colspan="11" class="text-center py-4">
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

                
                @if($vehicles->hasPages() || $vehicles->total() > 0)
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
                                    <li class="page-item {{ $vehicles->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $vehicles->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
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
                                            <a class="page-link" href="{{ $vehicles->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                                        @endif
                                    @endif
                
                                    @for($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $vehicles->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                                        </li>
                                    @endfor
                
                                    @if($end < $last)
                                        @if($end < $last - 1)
                                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $vehicles->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                                        </li>
                                    @endif
                
                                    <li class="page-item {{ !$vehicles->hasMorePages() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $vehicles->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                
                        <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                            表示中：{{ $vehicles->firstItem() ?? 0 }} - {{ $vehicles->lastItem() ?? 0 }} / 全 {{ $vehicles->total() }} 件
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
    url.searchParams.set('per_page', this.value);
    if (search) {
        url.searchParams.set('search', search);
    }
    window.location.href = url.toString();
});
</script>
@endpush