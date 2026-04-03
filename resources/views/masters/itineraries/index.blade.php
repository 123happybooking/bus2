@extends('layouts.app')

@section('title', '行程マスター')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-map me-2"></i>行程マスター</h4>
        <a href="{{ route('masters.itineraries.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.itineraries.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="行程コード、行程名、カテゴリーで検索"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    @if(request('search'))
                        <a href="{{ route('masters.itineraries.index') }}" class="btn btn-outline-secondary">
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
            @if($itineraries->count() > 0)
                - {{ $itineraries->total() }}件の結果が見つかりました
            @else
                - 該当する行程が見つかりませんでした
            @endif
        </div>
    @endif

    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary align-middle">
                    <tr>
                        <th width="100">コード</th>
                        <th width="120">区分</th>
                        <th>行程名</th>
                        <th>備考</th>
                        <th width="150" class="text-center">最終更新</th>
                        <th width="140" class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itineraries as $itinerary)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">{{ $itinerary->itinerary_code }}</span>
                        </td>
                        <td>
                            @if($itinerary->category)
                                <span class="badge bg-info">{{ $itinerary->category }}</span>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $itinerary->itinerary_name }}</strong>
                        </td>
                        <td>
                            {{ \Illuminate\Support\Str::limit($itinerary->remarks, 50) }}
                        </td>
                        <td class="text-center">
                            @if($itinerary->updated_at)
                                <span class="small text-muted">{{ $itinerary->updated_at->format('Y/m/d H:i') }}</span>
                            @else
                                <span class="text-muted small">--</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <!--<a href="{{ route('masters.itineraries.show', $itinerary) }}" -->
                                <!--   class="btn btn-sm btn-outline-info" title="詳細">-->
                                <!--    <i class="bi bi-eye"></i>-->
                                <!--</a>-->
                                <a href="{{ route('masters.itineraries.edit', $itinerary) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当にこの行程を削除しますか？\nこの操作は元に戻せません。');
                                }
                                </script>
                                <form action="{{ route('masters.itineraries.destroy', $itinerary) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('{{ $itinerary->itinerary_name }}')">
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
                        <td colspan="6" class="text-center py-4">
                            @if(request('search'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致する行程が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-map display-6 mb-2"></i>
                                    <p class="mb-0">行程データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の行程を登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($itineraries->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $itineraries->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $itineraries->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $itineraries->currentPage();
                        $last = $itineraries->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $itineraries->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $itineraries->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $itineraries->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$itineraries->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $itineraries->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $itineraries->firstItem() ?? 0 }} - {{ $itineraries->lastItem() ?? 0 }} / 全 {{ $itineraries->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.badge {
    font-size: 0.85em;
}

.table th {
    font-weight: 600;
}
</style>
@endpush