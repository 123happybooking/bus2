@extends('layouts.app')

@section('title', 'インポート履歴詳細')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0" style="color: #374151; font-size: 0.9rem;">インポート履歴詳細</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('masters.group-infos.import.history.list') }}" class="btn btn-outline-secondary btn-sm px-2 py-1" style="font-size: 0.75rem;">
                <i class="bi bi-arrow-left"></i> 戻る
            </a>
            <!--<button type="button" class="btn btn-outline-danger btn-sm px-2 py-1" id="deleteHistoryBtn" style="font-size: 0.75rem;">-->
            <!--    <i class="bi bi-trash"></i> 削除-->
            <!--</button>-->
        </div>
    </div>

    <div class="card shadow-sm mb-2">
        <div class="card-body py-2">
            <div class="row" style="font-size: 0.8rem; display: flex; flex-wrap: nowrap; gap: 10px;">
                <div class="col-auto" style="flex-shrink: 0;">
                    <strong>総行数:</strong> {{ $history->total_rows }}
                </div>
                <div class="col-auto" style="flex-shrink: 0;">
                    <strong>成功:</strong> <span class="text-success">{{ $history->success_rows }}</span>
                </div>
                <div class="col-auto" style="flex-shrink: 0;">
                    <strong>失敗:</strong> <span class="text-danger">{{ $history->failed_rows }}</span>
                </div>
                <div class="col-auto" style="flex-shrink: 0;">
                    <strong>インポート日時:</strong> {{ $history->imported_at->format('Y-m-d H:i:s') }}
                </div>
                <div class="col-auto" style="flex-shrink: 0;">
                    <strong>ファイル名:</strong> {{ $history->file_name }}
                </div>
                <div class="col-auto" style="flex-shrink: 0;">
                    <strong>インポート者:</strong> {{ $history->imported_by_name ?? '--' }}
                </div>
                <div class="col-auto" style="flex-shrink: 0;">
                    <strong>ステータス:</strong>
                    @if($history->status == 'completed')
                        <span class="badge bg-success" style="font-size: 0.7rem;">完了</span>
                    @elseif($history->status == 'failed')
                        <span class="badge bg-danger" style="font-size: 0.7rem;">失敗</span>
                    @else
                        <span class="badge bg-warning" style="font-size: 0.7rem;">処理中</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 mb-2 text-end">
        <button type="button" class="btn btn-primary btn-sm px-2 py-1" id="reimportBtn" style="font-size: 0.75rem;">
            <i class="bi bi-arrow-repeat"></i> 再インポート
        </button>
    </div>

    @if($history->error_log && count($history->error_log) > 0)
    <div class="alert alert-warning py-1 mb-2" style="font-size: 0.8rem;">
        <h6 style="font-size: 0.85rem;"><i class="bi bi-exclamation-triangle"></i> エラー詳細</h6>
        <ul class="mb-0 ps-3">
            @foreach($history->error_log as $error)
                <li>{{ $error['row'] }}行目: {{ is_array($error['errors']) ? implode('、', $error['errors']) : $error['errors'] }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" id="dataTable" style="font-size: 0.75rem;">
                    <thead>
                        <tr>
                            <th class="px-1 py-1" style="width: 30px;">#</th>
                            <th class="px-1 py-1">AGT</th>
                            <th class="px-1 py-1">担当</th>
                            <th class="px-1 py-1">支払方法</th>
                            <th class="px-1 py-1">金額</th>
                            <th class="px-1 py-1">開始日</th>
                            <th class="px-1 py-1">開始時刻</th>
                            <th class="px-1 py-1">終了日</th>
                            <th class="px-1 py-1">終了時刻</th>
                            <th class="px-1 py-1">団体名</th>
                            <th class="px-1 py-1">車両モデルCode</th>
                            <th class="px-1 py-1" style="width: 50px;">状態</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-2 text-muted" style="font-size: 0.7rem;">
        <i class="bi bi-info-circle"></i> データを編集すると、予約データ（団体情報・運行情報・行程情報）が直接更新されます。
    </div>
</div>
@endsection


@push('styles')
<style>
.container-fluid {
    max-width: 1600px;
}

a:hover {
    text-decoration: underline !important;
}

.btn-outline-secondary {
    color: #212529 !important;
    background-color: #fff !important;
    border-color: #ced4da !important;
}

.btn-outline-secondary:hover {
    background-color: #e9ecef !important;
    border-color: #adb5bd !important;
    color: #212529 !important;
}

#dataTableBody td[contenteditable="true"]:hover {
    background-color: #f0f0f0 !important;
    cursor: text;
}

#dataTableBody td[contenteditable="true"]:focus {
    background-color: #f0f0f0 !important;
    outline: 2px solid #2563eb;
    outline-offset: -1px;
}
</style>
@endpush


@push('scripts')
<script>
const historyId = {{ $history->id }};
const data = @json($history->imported_data ?? []);
const errors = @json($history->error_log ?? []);

function renderTable() {
    const tbody = document.getElementById('dataTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" class="text-center py-2" style="font-size: 0.75rem;">データがありません</td></tr>';
        return;
    }

    let html = '';
    data.forEach((row, index) => {
        const isError = errors.some(e => e.row === (index + 1));
        const status = row.status || (isError ? 'failed' : 'success');
        const statusBadge = status === 'success' 
            ? '<span class="badge bg-success" style="font-size: 0.6rem;">成功</span>' 
            : '<span class="badge bg-danger" style="font-size: 0.6rem;">失敗</span>';
        const rowClass = isError ? 'table-danger' : '';

        let stickerDisplay = '';
        if (row.sticker) {
            if (typeof row.sticker === 'object' && row.sticker.text) {
                stickerDisplay = row.sticker.text;
            } else if (typeof row.sticker === 'string') {
                stickerDisplay = row.sticker;
            }
        }

        html += `
            <tr class="${rowClass}">
                <td class="text-center px-1 py-1">${index + 1}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="agt_tour_id" data-index="${index}">${row.agt_tour_id || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="agency_contact_name" data-index="${index}">${row.agency_contact_name || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="payment_method" data-index="${index}">${row.payment_method || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="amount" data-index="${index}">${row.amount || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="start_date" data-index="${index}">${row.start_date || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="start_time" data-index="${index}">${row.start_time || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="end_date" data-index="${index}">${row.end_date || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="end_time" data-index="${index}">${row.end_time || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="group_name" data-index="${index}">${row.group_name || ''}</td>
                <td class="px-1 py-1" contenteditable="true" data-field="vehicle_model_code" data-index="${index}">${row.vehicle_model_code || ''}</td>
                <td class="text-center px-1 py-1">${statusBadge}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    document.querySelectorAll('[contenteditable="true"]').forEach(el => {
        el.addEventListener('blur', function() {
            const index = this.dataset.index;
            const field = this.dataset.field;
            const value = this.textContent.trim();
            
            let sendField = field;
            let sendValue = value;

            if (field === 'sticker_text') {
                sendField = 'sticker';
                const originalSticker = data[index]?.sticker;
                if (originalSticker && typeof originalSticker === 'object') {
                    sendValue = {
                        text: value,
                        link: originalSticker.link || ''
                    };
                } else {
                    sendValue = {
                        text: value,
                        link: ''
                    };
                }
            }

            fetch(`/masters/group-infos/import/history/${historyId}/data`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    row_index: index, 
                    field: sendField, 
                    value: sendValue 
                })
            });
        });
    });
}

renderTable();

document.getElementById('reimportBtn').addEventListener('click', function() {
    if (!confirm('再インポートを実行しますか？')) return;
    
    this.disabled = true;
    this.innerHTML = '<i class="bi bi-hourglass-split"></i> 実行中...';

    fetch(`/masters/group-infos/import/history/${historyId}/reimport`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`再インポート完了: 成功 ${data.success_rows} 件、失敗 ${data.failed_rows} 件`);
            location.reload();
        } else {
            alert('再インポート失敗: ' + data.message);
        }
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-arrow-repeat"></i> 再インポート';
    })
    .catch(error => {
        alert('エラーが発生しました: ' + error.message);
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-arrow-repeat"></i> 再インポート';
    });
});

document.getElementById('deleteHistoryBtn').addEventListener('click', function() {
    if (!confirm('このインポート履歴を削除してもよろしいですか？')) return;
    
    fetch(`/masters/group-infos/import/history/${historyId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("masters.group-infos.import.history.list") }}';
        }
    });
});
</script>
@endpush