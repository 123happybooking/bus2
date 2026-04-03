@extends('layouts.app')

@section('title', 'Bankマスター')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-bank me-2"></i>Bankマスター</h4>
        <a href="{{ route('masters.banks.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.banks.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Bank名、口座情報、備考で検索"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    @if(request('search'))
                        <a href="{{ route('masters.banks.index') }}" class="btn btn-outline-secondary">
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
            @if($banks->count() > 0)
                - {{ $banks->total() }}件の結果が見つかりました
            @else
                - 該当するBankが見つかりませんでした
            @endif
        </div>
    @endif


    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary align-middle">
                    <tr>
                        <th width="150">Bank名</th>
                        <th>口座情報</th>
                        <th>備考</th>
                        <th width="100">状態</th>
                        <th width="150" class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banks as $bank)
                    <tr>
                        <td>{{ $bank->bank_name }}</td>
                        <td>{{ Str::limit($bank->bank_info, 50) }}</td>
                        <td>
                            {{ \Illuminate\Support\Str::limit($bank->remarks, 50) }}
                        </td>
                        <td>
                            @if($bank->is_active)
                                <span class="badge bg-success">有効</span>
                            @else
                                <span class="badge bg-secondary">無効</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.banks.show', $bank) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.banks.edit', $bank) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当にこのBankを削除しますか？\nこの操作は元に戻せません。');
                                }
                                </script>
                                <form action="{{ route('masters.banks.destroy', $bank) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('{{ $bank->bank_name }}')">
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
                        <td colspan="4" class="text-center py-4">
                            @if(request('search'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致するBankが見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-bank display-6 mb-2"></i>
                                    <p class="mb-0">Bankデータが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初のBankを登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    
    @if($banks->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $banks->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $banks->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $banks->currentPage();
                        $last = $banks->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $banks->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $banks->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $banks->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$banks->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $banks->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $banks->firstItem() ?? 0 }} - {{ $banks->lastItem() ?? 0 }} / 全 {{ $banks->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection