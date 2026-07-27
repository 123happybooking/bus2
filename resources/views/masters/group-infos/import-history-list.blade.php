@extends('layouts.app')

@section('title', 'インポート履歴')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">インポート履歴</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('masters.group-infos.import') }}" class="btn btn-primary btn-sm px-3 py-1" style="font-size: 0.875rem;">
                <i class="bi bi-cloud-upload"></i> 新規インポート
            </a>
            <a href="{{ route('masters.group-infos.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1" style="font-size: 0.875rem;">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 table-list">
                    <thead>
                        <tr>
                            <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 200px;">日時</th>
                            <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">ファイル名</th>
                            <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">総行数</th>
                            <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">成功</th>
                            <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">失敗</th>
                            <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">ステータス</th>
                            <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 120px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($histories as $history)
                        <tr>
                            <td class="text-center px-2 py-1" style="font-size: 0.8rem;">{{ $history->imported_at->format('Y-m-d H:i:s') }}</td>
                            <td class="text-start px-2 py-1" style="font-size: 0.8rem;">{{ $history->file_name }}</td>
                            <td class="text-center px-2 py-1" style="font-size: 0.8rem;">{{ $history->total_rows }}</td>
                            <td class="text-center px-2 py-1 text-success" style="font-size: 0.8rem;">{{ $history->success_rows }}</td>
                            <td class="text-center px-2 py-1 text-danger" style="font-size: 0.8rem;">{{ $history->failed_rows }}</td>
                            <td class="text-center px-2 py-1">
                                @if($history->status == 'completed')
                                    <span class="badge bg-success" style="font-size: 0.7rem; padding: 2px 10px;">完了</span>
                                @elseif($history->status == 'failed')
                                    <span class="badge bg-danger" style="font-size: 0.7rem; padding: 2px 10px;">失敗</span>
                                @else
                                    <span class="badge bg-warning" style="font-size: 0.7rem; padding: 2px 10px;">処理中</span>
                                @endif
                            </td>
                            <td class="text-center px-2 py-1">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('masters.group-infos.import.history', $history->id) }}" class="btn btn-sm btn-outline-primary" style="padding: 2px 6px; font-size: 0.75rem;">
                                        詳細／編集
                                    </a>
                                    <!--<button type="button" class="btn btn-sm btn-outline-danger delete-history" data-id="{{ $history->id }}" style="padding: 2px 6px; font-size: 0.75rem;">-->
                                    <!--    <i class="bi bi-trash"></i>-->
                                    <!--</button>-->
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($histories->hasPages() || $histories->total() > 0)
    <div class="mt-3">
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
            <div class="d-flex align-items-center">
                <label for="per_page_select" class="form-label small text-muted mb-0 me-2" style="white-space: nowrap; font-size: 0.75rem;">
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
                    <li class="page-item {{ $histories->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $histories->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $histories->currentPage();
                        $last = $histories->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $histories->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $histories->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $histories->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$histories->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $histories->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
            表示中：{{ $histories->firstItem() ?? 0 }} - {{ $histories->lastItem() ?? 0 }} / 全 {{ $histories->total() }} 件
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.table-list {
    border: 1px solid #E5E7EB !important;
}

.table-list th, .table-list td {
    padding: 0.2rem 0.2rem !important;
    vertical-align: middle;
    border-color: #E5E7EB;
    color: #111827;
    font-size: 0.8rem;
}

.table-list thead th {
    border-bottom-width: 1px;
    font-weight: 500;
    background-color: #F3F4F6;
    color: #374151;
    white-space: nowrap;
}

.table-list tbody tr:hover td,
.table-list tbody tr:hover th {
    background-color: #d8e1e9 !important;
    cursor: pointer !important;
    position: relative !important;
    z-index: 1 !important;
}

.pagination {
    margin-bottom: 0;
    gap: 2px;
}

.pagination .page-link {
    color: #374151;
    border-color: #E5E7EB;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.pagination .page-item.active .page-link {
    background-color: #2563eb;
    border-color: #2563eb;
    color: white;
}

.container-fluid {
    max-width: 1600px;
}

a:hover {
    text-decoration: underline !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-history').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('このインポート履歴を削除してもよろしいですか？')) return;
            const id = this.dataset.id;
            fetch(`/masters/group-infos/import/history/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(function(data) {
                if (data.success) location.reload();
            });
        });
    });

    const perPageSelect = document.getElementById('per_page_select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', this.value);
            window.location.href = url.toString();
        });
    }
});
</script>
@endpush