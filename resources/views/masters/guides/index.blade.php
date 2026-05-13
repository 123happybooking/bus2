@extends('layouts.app')

@section('title', 'ガイドマスター')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-person-video3"></i>ガイドマスター</h4>
                <a href="{{ route('masters.guides.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
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
                <form method="GET" action="{{ route('masters.guides.index') }}" class="row g-2">
                    <div class="col">
                        <input type="text" name="search" class="form-control form-control-sm" style="border-color: #E5E7EB;" 
                               placeholder="ガイドコード・氏名で検索"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm px-3" 
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('masters.guides.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
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
                    @if($guides->count() > 0)
                        - {{ $guides->total() }}件の結果が見つかりました
                    @else
                        - 該当するガイドが見つかりませんでした
                    @endif
                </div>
            @endif
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 table-list">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>ガイドコード</th>
                                <th>氏名</th>
                                <th>所属営業所</th>
                                <th>電話番号</th>
                                <th>雇用区分</th>
                                <th>状態</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guides as $index => $guide)
                            <tr>
                                <td>{{ $guides->firstItem() + $index }}</td>
                                <td><code>{{ $guide->guide_code }}</code></td>
                                <td>
                                    <div>{{ $guide->name }}</div>
                                    <div class="small text-muted">{{ $guide->name_kana }}</div>
                                </td>
                                <td>
                                    @if($guide->branch)
                                        {{ $guide->branch->branch_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </td>
                                <td>{{ $guide->phone_number }}</td>
                                <td>
                                    @php
                                        $employmentLabels = [
                                            '自社' => ['label' => '自社', 'class' => 'badge bg-primary'],
                                            '契約' => ['label' => '契約', 'class' => 'badge bg-warning'],
                                            '業務委託' => ['label' => '業務委託', 'class' => 'badge bg-info'],
                                        ];
                                        $employment = $employmentLabels[$guide->employment_type] ?? ['label' => '不明', 'class' => 'badge bg-secondary'];
                                    @endphp
                                    <span class="{{ $employment['class'] }}">{{ $employment['label'] }}</span>
                                </td>
                                <td>
                                    @if($guide->is_active)
                                        <span class="badge bg-success">稼働中</span>
                                    @else
                                        <span class="badge bg-secondary">停止</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('masters.guides.show', $guide) }}" 
                                           class="btn btn-sm btn-outline-info" title="詳細">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('masters.guides.edit', $guide) }}" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDelete(name) {
                                            return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="{{ route('masters.guides.destroy', $guide) }}" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirmDelete('{{ $guide->name }}')">
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
                                    @if(request('search'))
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致するガイドが見つかりませんでした</p>
                                            <p class="small">検索キーワードを変更してお試しください</p>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-person-video3 display-6 mb-2"></i>
                                            <p class="mb-0">ガイドデータが登録されていません</p>
                                            <p class="small">「新規追加」ボタンから最初のガイドを登録してください</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                
                @if($guides->hasPages() || $guides->total() > 0)
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
                                    <li class="page-item {{ $guides->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $guides->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                
                                    @php
                                        $current = $guides->currentPage();
                                        $last = $guides->lastPage();
                                        $start = max(1, $current - 2);
                                        $end = min($last, $current + 2);
                                    @endphp
                
                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $guides->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                                        @endif
                                    @endif
                
                                    @for($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $guides->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                                        </li>
                                    @endfor
                
                                    @if($end < $last)
                                        @if($end < $last - 1)
                                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $guides->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                                        </li>
                                    @endif
                
                                    <li class="page-item {{ !$guides->hasMorePages() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $guides->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                
                        <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                            表示中：{{ $guides->firstItem() ?? 0 }} - {{ $guides->lastItem() ?? 0 }} / 全 {{ $guides->total() }} 件
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