@extends('layouts.win')

@section('title', '日別旅程詳細')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-lg font-semibold">日別旅程詳細</h2>
        <div>
            <a href="{{ route('masters.daily-itineraries.edit', $dailyItinerary->key_uuid) }}" class="btn-primary mr-2">編集</a>
            <button type="button" id="backBtn" class="btn-secondary">戻る</button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="text-md">基本情報</h3>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <th style="width: 150px;">ID</th>
                    <td>{{ $dailyItinerary->id }}</td>
                </tr>
                <tr>
                    <th>UUID</th>
                    <td>{{ $dailyItinerary->key_uuid }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ $dailyItinerary->formatted_date ?? '—' }}</td>
                </tr>
                <tr>
                    <th>開始時刻</th>
                    <td>{{ $dailyItinerary->time_start ?? '—' }}</td>
                </tr>
                <tr>
                    <th>終了時刻</th>
                    <td>{{ $dailyItinerary->time_end ?? '—' }}</td>
                </tr>
                <tr>
                    <th>旅程内容</th>
                    <td>{{ $dailyItinerary->itinerary ?? '—' }}</td>
                </tr>
                <tr>
                    <th>開始場所</th>
                    <td>{{ $dailyItinerary->start_location ?? '—' }}</td>
                </tr>
                <tr>
                    <th>終了場所</th>
                    <td>{{ $dailyItinerary->end_location ?? '—' }}</td>
                </tr>
                <tr>
                    <th>宿泊</th>
                    <td>
                        <span class="status-badge {{ $dailyItinerary->accommodation ? 'status-yes' : 'status-no' }}">
                            {{ $dailyItinerary->accommodation_display }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="text-md">車両・ドライバー情報</h3>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <th style="width: 150px;">車両</th>
                    <td>{{ $dailyItinerary->vehicle ?? '—' }}</td>
                </tr>
                <tr>
                    <th>ドライバー</th>
                    <td>{{ $dailyItinerary->driver ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="text-md">関連情報</h3>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <th style="width: 150px;">予約情報</th>
                    <td>
                        @if($dailyItinerary->groupInfo)
                            <a href="{{ route('masters.group-infos.show', $dailyItinerary->groupInfo->id) }}" target="_blank">
                                {{ $dailyItinerary->groupInfo->agency ?? '—' }} ({{ $dailyItinerary->groupInfo->reservation_status_display ?? '—' }})
                            </a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>バス割当</th>
                    <td>
                        @if($dailyItinerary->busAssignment)
                            <a href="{{ route('masters.bus-assignment.show', $dailyItinerary->busAssignment->key_uuid) }}" target="_blank">
                                {{ $dailyItinerary->busAssignment->key_uuid ?? '—' }}
                            </a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>予約UUID</th>
                    <td>{{ $dailyItinerary->yoyaku_uuid ?? '—' }}</td>
                </tr>
                <tr>
                    <th>バス割当UUID</th>
                    <td>{{ $dailyItinerary->bus_ass_uuid ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="text-md">システム情報</h3>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <th style="width: 150px;">作成者</th>
                    <td>{{ $dailyItinerary->created_by ?? '—' }}</td>
                </tr>
                <tr>
                    <th>作成日時</th>
                    <td>{{ $dailyItinerary->created_at ? $dailyItinerary->created_at->format('Y/m/d H:i:s') : '—' }}</td>
                </tr>
                <tr>
                    <th>更新者</th>
                    <td>{{ $dailyItinerary->updated_by ?? '—' }}</td>
                </tr>
                <tr>
                    <th>更新日時</th>
                    <td>{{ $dailyItinerary->updated_at ? $dailyItinerary->updated_at->format('Y/m/d H:i:s') : '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <div>
            <button type="button" id="backBtn2" class="btn-secondary">戻る</button>
        </div>
        <div>
            <button type="button" class="btn-danger delete-btn" data-uuid="{{ $dailyItinerary->key_uuid }}">削除</button>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">削除確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                <p>この日別旅程を削除してもよろしいですか？</p>
                <p class="text-danger small">この操作は取り消せません。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary btn-sm" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn-danger btn-sm" id="confirmDelete">削除</button>
            </div>
        </div>
    </div>
</div>
@endsection


@push('styles')
<style>
    .text-lg { font-size: 16px; font-weight: 600; }
    .text-md { font-size: 14px; font-weight: 600; }
    .text-sm { font-size: 12px; }
    .text-xs { font-size: 11px; }
    
    .card {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #f9fafb;
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .card-body {
        padding: 12px;
    }
    
    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .detail-table th {
        text-align: left;
        padding: 8px 12px;
        background-color: #f3f4f6;
        font-weight: 500;
        font-size: 12px;
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    
    .detail-table td {
        padding: 8px 12px;
        font-size: 12px;
        color: #1f2937;
        border: 1px solid #e5e7eb;
    }
    
    .btn-primary {
        background-color: #2563eb;
        border: none;
        color: white;
        font-size: 12px;
        padding: 6px 16px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary:hover {
        background-color: #1d4ed8;
    }
    
    .btn-secondary {
        background-color: #6b7280;
        border: none;
        color: white;
        font-size: 12px;
        padding: 6px 16px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .btn-secondary:hover {
        background-color: #4b5563;
    }
    
    .btn-danger {
        background-color: #dc2626;
        border: none;
        color: white;
        font-size: 12px;
        padding: 6px 16px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .btn-danger:hover {
        background-color: #b91c1c;
    }
    
    .btn-sm {
        font-size: 11px;
        padding: 4px 12px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .status-yes {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .status-no {
        background-color: #e5e7eb;
        color: #4b5563;
    }
    
    .mr-2 { margin-right: 8px; }
    .mb-3 { margin-bottom: 16px; }
    
    .modal-content {
        border-radius: 8px;
    }
    
    .modal-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .modal-body {
        padding: 16px;
    }
    
    .modal-footer {
        padding: 12px 16px;
        border-top: 1px solid #e5e7eb;
    }
    
    .text-danger { color: #dc2626; }
    .small { font-size: 11px; }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isInIframe = window.self !== window.top;
    
    function goBack() {
        if (isInIframe) {
            window.parent.postMessage('close-iframe', '*');
        } else {
            window.location.href = '{{ route('masters.daily-itineraries.index') }}';
        }
    }
    
    document.getElementById('backBtn')?.addEventListener('click', goBack);
    document.getElementById('backBtn2')?.addEventListener('click', goBack);

    const deleteBtn = document.querySelector('.delete-btn');
    const deleteModal = document.getElementById('deleteModal');
    
    if (deleteBtn && deleteModal) {
        const modal = new bootstrap.Modal(deleteModal);
        
        deleteBtn.addEventListener('click', function() {
            modal.show();
        });
        
        document.getElementById('confirmDelete').addEventListener('click', function() {
            const deleteBtn = document.querySelector('.delete-btn');
            const uuid = deleteBtn.dataset.uuid;
            
            fetch(`{{ url('masters/daily-itineraries') }}/${uuid}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    
                    if (isInIframe) {
                        window.parent.postMessage('close-iframe', '*');
                    } else {
                        window.location.href = '{{ route('masters.daily-itineraries.index') }}';
                    }
                } else {
                    alert(data.message || '削除に失敗しました');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('エラーが発生しました');
            });
        });
    }
});
</script>
@endpush