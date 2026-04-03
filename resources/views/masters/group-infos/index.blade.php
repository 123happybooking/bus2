@extends('layouts.app')

@section('title', 'グループ情報一覧')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">予約一覧</h5>
        <button type="button" id="newGroupBtn" class="btn btn-primary btn-sm px-3 py-1" 
                style="background-color: #2563eb; border-color: #2563eb; font-size: 0.875rem;">
            新規予約
        </button>
    </div>

    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.group-infos.index') }}" class="row g-2">
            <div class="col-auto">
                <input type="text" name="date_from" value="{{ request('date_from', \Carbon\Carbon::today()->format('Y-m-d')) }}" 
                       class="form-control form-control-sm datepicker-3months" style="width: 140px; border-color: #E5E7EB;" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-auto">
                <input type="text" name="date_end" value="{{ request('date_end', \Carbon\Carbon::today()->format('Y-m-d')) }}" 
                       class="form-control form-control-sm datepicker-3months" style="width: 140px; border-color: #E5E7EB;" placeholder="YYYY-MM-DD">
            </div>
            <div class="col">
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="form-control form-control-sm" style="border-color: #E5E7EB;" placeholder="代理店・車両...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm px-3" 
                        style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                    検索
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('masters.group-infos.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
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
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 160px;">初始</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 160px;">终了</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">代理店</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">状态</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 60px;">人数</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 60px;">車両種類</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">車両</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">请求</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">備考</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">操作</th>
                </tr>
             </thead>
            <tbody>
                @forelse($groupInfos as $index => $groupInfo)
                <tr>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $groupInfos->firstItem() + $index }}
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if($groupInfo->start_date)
                            {{ \Carbon\Carbon::parse($groupInfo->start_date)->format('Y/m/d') }}
                            @if($groupInfo->start_time)
                                {{ substr($groupInfo->start_time, 0, 5) }}
                            @endif
                        @endif
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if($groupInfo->end_date)
                            {{ \Carbon\Carbon::parse($groupInfo->end_date)->format('Y/m/d') }}
                            @if($groupInfo->end_time)
                                {{ substr($groupInfo->end_time, 0, 5) }}
                            @endif
                        @endif
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $groupInfo->agency ?? '' }}
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $groupInfo->reservation_status ?? '不明' }}
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @php
                            $totalPax = ($groupInfo->adult_count ?? 0) + 
                                       ($groupInfo->child_count ?? 0) + 
                                       ($groupInfo->guide_count ?? 0) + 
                                       ($groupInfo->other_count ?? 0);
                        @endphp
                        {{ $totalPax }}
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <a href="{{ route('masters.bus-assignments.show', $groupInfo->id) }}" 
                           style="color: #2563eb; text-decoration: none;">
                            詳細
                        </a>
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $groupInfo->vehicle ?? '--' }}
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        --
                     </td>
                    <td class="text-center px-2 py-1 align-middle" style="max-width: 150px;">
                        <span class="text-truncate d-inline-block" style="max-width: 120px;" title="{{ $groupInfo->remarks ?? '' }}">
                            {{ $groupInfo->remarks ?? '--' }}
                        </span>
                     </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <a href="{{ route('masters.group-infos.edit', $groupInfo->id) }}" 
                           style="color: #2563eb; text-decoration: none; margin-right: 8px;">
                            編集
                        </a>
                        <a href="javascript:void(0);" onclick="confirmDelete('{{ $groupInfo->id }}', '{{ $groupInfo->agency ?? 'このグループ' }}')" 
                           style="color: #dc3545; text-decoration: none;">
                            削除
                        </a>
                     </td>
                 </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-3" style="color: #9ca3af;">
                        グループデータがありません
                     </td>
                 </tr>
                @endforelse
            </tbody>
         </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div style="color: #6b7280; font-size: 0.875rem;">
            全 {{ $groupInfos->total() }} 件中 
            {{ $groupInfos->firstItem() ?? 0 }} - {{ $groupInfos->lastItem() ?? 0 }} 件表示
        </div>
        <div>
            {{ $groupInfos->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<div id="iframeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; overflow: auto;">
    <div style="position: relative; width: 100%; min-height: 100%; display: flex; justify-content: center; align-items: center; padding: 20px;">
        <div id="modalContent" style="background-color: #f3f4f6; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); width: 90%; max-width: 550px; overflow: hidden; transition: all 0.3s ease;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 16px; color: #fff; font-size: 14px; font-weight: 500; background-color: #374151;">
                <span id="modalTitle">新規グループ作成</span>
                <button onclick="closeIframeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #fff;">&times;</button>
            </div>
            <iframe id="modalIframe" src="" style="width: 100%; height: 480px; border: none; display: block; transition: height 0.3s ease;"></iframe>
        </div>
    </div>
</div>
@endsection


@push('styles')
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

.operation-links a {
    margin-right: 8px;
}

.operation-links a:last-child {
    margin-right: 0;
}
</style>
@endpush


@push('scripts')
<script>
function openIframeModal(url, title = '新規グループ作成') {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    
    if (!iframe || !modal) return;
    
    iframe.src = url;
    modalTitle.textContent = title;
    iframe.style.height = '480px';
    if (modalContent) modalContent.style.maxWidth = '550px';
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeIframeModal() {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    
    if (iframe) iframe.src = '';
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

function confirmDelete(id, name) {
    if (confirm(`「${name}」を削除してもよろしいですか？\nこの操作は元に戻せません。`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/masters/group-infos/${id}`;
        form.submit();
    }
}

window.addEventListener('message', function(event) {
    const data = event.data;
    
    if (typeof data === 'object' && data !== null) {
        if (data.action === 'open-edit') {
            closeIframeModal();
            window.location.href = data.url;
        } else if (data.action === 'redirect') {
            closeIframeModal();
            window.location.href = data.url;
        }
    } else if (data === 'close-iframe') {
        closeIframeModal();
        location.reload();
    } else if (data === 'refresh-list') {
        location.reload();
    }
});

document.getElementById('newGroupBtn').addEventListener('click', function() {
    openIframeModal('{{ route('masters.group-infos.create') }}', '新規グループ作成');
});

document.getElementById('iframeModal').addEventListener('click', function(e) {
    if (e.target === this) closeIframeModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('iframeModal').style.display === 'block') {
        closeIframeModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    initDateRangePicker('input[name="date_from"]', 'input[name="date_end"]');
});
</script>
@endpush