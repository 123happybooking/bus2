@extends('layouts.app')

@section('title', 'Excel データ取込')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">Excel データ取込</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('masters.group-infos.import.history.list') }}" class="btn btn-outline-secondary btn-sm px-3 py-1" style="font-size: 0.875rem;">
                <i class="bi bi-clock-history"></i> インポート履歴
            </a>
            <a href="{{ route('masters.group-infos.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1" style="font-size: 0.875rem;">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-3">
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="file" class="form-control form-control-sm" id="importFile" name="file" accept=".xlsx,.xls" required style="font-size: 0.875rem;">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3" id="uploadBtn" style="font-size: 0.875rem;">
                            <i class="bi bi-cloud-upload"></i> アップロード実行
                        </button>
                        <!--<a href="{{ route('masters.group-infos.import.template') }}" class="btn btn-outline-secondary btn-sm px-3" style="font-size: 0.875rem;">-->
                        <!--    <i class="bi bi-download"></i> テンプレート-->
                        <!--</a>-->
                    </div>
                </div>
            </form>

            <div class="py-2" style="font-size:0.875rem;color:#ccc;">
                対応フォーマット: .xlsx, .xls (最大5MB)
            </div>

            <div id="importProgress" style="display:none;" class="mt-3">
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; font-size: 0.8rem;">処理中...</div>
                </div>
            </div>

            <div id="importResult" style="display:none;" class="mt-3"></div>
        </div>
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

.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.progress-bar {
    background-color: #2563eb;
}

.result-card {
    background-color: #f8fafc;
    padding: 16px 20px;
    border-radius: 6px;
}

.result-card .result-icon {
    font-size: 2rem;
}

.result-card .result-number {
    font-size: 1.5rem;
    font-weight: bold;
}

.result-card .result-number.text-success {
    color: #16a34a;
}

.result-card .result-number.text-danger {
    color: #dc2626;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('importForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('importFile');
        if (!fileInput.files.length) {
            alert('ファイルを選択してください。');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        document.getElementById('uploadBtn').disabled = true;
        document.getElementById('uploadBtn').innerHTML = '<i class="bi bi-hourglass-split"></i> 処理中...';
        document.getElementById('importProgress').style.display = 'block';
        document.getElementById('importResult').style.display = 'block';
        document.getElementById('importResult').innerHTML = '';

        fetch('{{ route("masters.group-infos.import.upload") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('importProgress').style.display = 'none';
            document.getElementById('uploadBtn').disabled = false;
            document.getElementById('uploadBtn').innerHTML = '<i class="bi bi-cloud-upload"></i> アップロード実行';

            let html = '';

            if (data.success) {
                const successCount = data.success_rows || 0;
                const failedCount = data.failed_rows || 0;
                const totalCount = data.total || 0;
                const historyId = data.history_id;

                html += `
                    <div class="result-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="result-icon">
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; font-weight: 500;">インポートが完了しました</div>
                                <div style="font-size: 0.8rem; color: #6b7280;">
                                    総行数: <strong>${totalCount}</strong> 件
                                    / 成功: <span class="text-success"><strong>${successCount}</strong></span> 件
                                    / 失敗: <span class="text-danger"><strong>${failedCount}</strong></span> 件
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="/masters/group-infos/import/history/${historyId}" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye"></i> 詳細を見る
                            </a>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload();">
                                <i class="bi bi-arrow-repeat"></i> 続けて取込
                            </button>
                        </div>
                    </div>
                `;

                if (data.errors && data.errors.length > 0) {
                    html += `
                        <div class="alert alert-warning py-2 mt-3" style="font-size: 0.875rem;">
                            <h6 class="mb-1"><i class="bi bi-exclamation-triangle"></i> エラー詳細</h6>
                            <ul class="mb-0 ps-3">
                                ${data.errors.map(err => `<li>${err.row}行目: ${err.errors.join('、')}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }
            } else {
                html = `
                    <div class="alert alert-danger py-2" style="font-size: 0.875rem;">
                        <i class="bi bi-exclamation-triangle"></i> ${data.message || 'インポートに失敗しました'}
                    </div>
                `;
            }

            document.getElementById('importResult').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('importProgress').style.display = 'none';
            document.getElementById('uploadBtn').disabled = false;
            document.getElementById('uploadBtn').innerHTML = '<i class="bi bi-cloud-upload"></i> アップロード実行';
            
            document.getElementById('importResult').innerHTML = `
                <div class="alert alert-danger py-2" style="font-size: 0.875rem;">
                    <i class="bi bi-exclamation-triangle"></i> エラーが発生しました: ${error.message}
                </div>
            `;
            document.getElementById('importResult').style.display = 'block';
        });
    });
});
</script>
@endpush