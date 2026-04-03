@extends('layouts.app')

@section('title', '日別旅程一覧')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">日別旅程一覧</h5>
    </div>

    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.daily-itineraries.index') }}" class="row g-2">
            <div class="col-auto">
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="form-control form-control-sm" style="width: 140px; border-color: #E5E7EB;" placeholder="開始日">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="form-control form-control-sm" style="width: 140px; border-color: #E5E7EB;" placeholder="終了日">
            </div>
            <div class="col">
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="form-control form-control-sm" style="border-color: #E5E7EB;" placeholder="旅程・場所・車両・運転手...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm px-3" 
                        style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                    検索
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('masters.daily-itineraries.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
                   style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                    クリア
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0" style="border-color: #E5E7EB; font-size: 0.875rem;">
            <thead style="background-color: #F3F4F6;">
                <tr>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 60px;">No.</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 120px;">日付</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 150px;">時間</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">代理店</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">団体名</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">車両/運転手</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">備考</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailyItineraries as $index => $itinerary)
                <tr>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $dailyItineraries->firstItem() + $index }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $itinerary->date ? \Carbon\Carbon::parse($itinerary->date)->format('Y/m/d') : '--' }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if($itinerary->time_start || $itinerary->time_end)
                            {{ substr($itinerary->time_start, 0, 5) ?? '--:--' }}
                            〜 {{ substr($itinerary->time_end, 0, 5) ?? '--:--' }}
                        @endif
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if($itinerary->groupInfo)
                            {{ $itinerary->groupInfo->agency ?? '--' }}
                        @endif
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if($itinerary->groupInfo)
                            <a href="{{ route('masters.group-infos.edit', $itinerary->groupInfo->id) }}" 
                               style="color: #2563eb; text-decoration: none;">
                               {{ $itinerary->groupInfo->group_name ?? '--' }}
                            </a>
                        @endif
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if($itinerary->vehicle || $itinerary->driver)
                            {{ $itinerary->vehicle ?? '--' }} / 
                            {{ $itinerary->driver ?? '--' }}
                        @endif
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <span class="text-truncate d-inline-block" style="max-width: 120px;" title="{{ $itinerary->remarks ?? '' }}">
                            {{ $itinerary->remarks ?? '--' }}
                        </span>
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <a href="{{ route('masters.daily-itineraries.edit', $itinerary->id) }}" 
                           style="color: #2563eb; text-decoration: none; margin-right: 8px;">
                            編集
                        </a>
                        <a href="javascript:void(0);" onclick="confirmDelete('{{ $itinerary->id }}', '{{ $itinerary->date ? \Carbon\Carbon::parse($itinerary->date)->format('Y/m/d') : '--' }} の旅程')" 
                           style="color: #ef4444; text-decoration: none;">
                            削除
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-3" style="color: #9ca3af;">
                        旅程データがありません
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div style="color: #6b7280; font-size: 0.875rem;">
            全 {{ $dailyItineraries->total() }} 件中 
            {{ $dailyItineraries->firstItem() ?? 0 }} - {{ $dailyItineraries->lastItem() ?? 0 }} 件表示
        </div>
        <div>
            {{ $dailyItineraries->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<div id="iframeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; overflow: auto;">
    <div style="position: relative; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
        <div style="background-color: #f3f4f6; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 90%; max-width: 550px; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; color: #fff; font-size: 14px; font-weight: 500; background-color: #374151;">
                <span id="modalTitle">新規旅程作成</span>
                <button onclick="closeIframeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #fff;">&times;</button>
            </div>
            <iframe id="modalIframe" src="" style="width: 100%; height: 470px; border: none;"></iframe>
        </div>
    </div>
</div>

<div id="deleteConfirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 10000; overflow: auto;">
    <div style="position: relative; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
        <div style="background-color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 90%; max-width: 400px; padding: 20px;">
            <div style="margin-bottom: 20px;">
                <h5 style="color: #374151; font-size: 1.1rem; margin-bottom: 10px;">削除確認</h5>
                <p id="deleteConfirmMessage" style="color: #6b7280; font-size: 0.95rem;">本当に削除しますか？</p>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="closeDeleteModal()" class="btn btn-sm btn-outline-secondary" 
                        style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                    キャンセル
                </button>
                <form id="deleteForm" method="POST" action="" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" 
                            style="background-color: #ef4444; border-color: #ef4444; color: white; font-size: 0.875rem;">
                        削除
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
.table-sm th, .table-sm td {
    padding: 0.2rem 0.2rem !important;
    vertical-align: middle;
    border-color: #E5E7EB;
    color: #111827;
    font-size: 0.8rem;
}

.table-bordered {
    border: 1px solid #E5E7EB;
}

.table thead th {
    border-bottom-width: 1px;
    font-weight: 500;
    background-color: #F3F4F6;
    color: #374151;
    white-space: nowrap;
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

.form-control:focus, .form-select:focus, .btn:focus {
    box-shadow: none;
    border-color: #2563eb;
}

.container-fluid {
    max-width: 1600px;
}

a:hover {
    text-decoration: underline !important;
}

#iframeModal {
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

iframe {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f3f4f6;
}

iframe::-webkit-scrollbar {
    width: 6px;
}

iframe::-webkit-scrollbar-track {
    background: #f3f4f6;
}

iframe::-webkit-scrollbar-thumb {
    background-color: #d1d5db;
    border-radius: 3px;
}

.badge {
    font-weight: normal;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 4px;
}

small {
    font-size: 0.7rem;
    color: #6b7280;
}

td a {
    transition: opacity 0.2s;
}

td a:hover {
    opacity: 0.8;
    text-decoration: underline !important;
}

td a:first-child {
    margin-right: 12px;
}

td a:nth-child(2) {
    margin-right: 12px;
}
</style>
@endpush


@push('scripts')
<script>
function openIframeModal(url, title = '新規旅程作成') {
    document.getElementById('modalIframe').src = url;
    document.getElementById('iframeModal').style.display = 'block';
    document.getElementById('modalTitle').textContent = title;
    document.body.style.overflow = 'hidden';
}

function closeIframeModal() {
    document.getElementById('iframeModal').style.display = 'none';
    document.getElementById('modalIframe').src = '';
    document.body.style.overflow = '';
}

function openDetailIframe(url) {
    openIframeModal(url, '旅程詳細');
}

function openEditIframe(url) {
    openIframeModal(url, '旅程編集');
}

function openGroupDetail(uuid) {
    window.location.href = '/masters/groups/' + uuid;
}

function confirmDelete(id, description) {
    if (confirm(description + ' を削除してもよろしいですか？')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ url("masters/daily-itineraries") }}/' + id;
        form.submit();
    }
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}

document.getElementById('newItineraryBtn').addEventListener('click', function() {
    openIframeModal('{{ route('masters.daily-itineraries.create') }}', '新規旅程作成');
});

document.getElementById('iframeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeIframeModal();
    }
});

document.getElementById('deleteConfirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('iframeModal').style.display === 'block') {
            closeIframeModal();
        } else if (document.getElementById('deleteConfirmModal').style.display === 'block') {
            closeDeleteModal();
        }
    }
});

window.addEventListener('message', function(event) {
    if (event.data === 'close-iframe') {
        closeIframeModal();
        location.reload();
    } else if (event.data === 'refresh-list') {
        location.reload();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    @if(request()->has('open_id'))
        openDetailIframe('{{ route('masters.daily-itineraries.show', request('open_id')) }}');
    @endif
});
</script>
@endpush