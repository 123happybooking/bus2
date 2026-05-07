@extends('layouts.win')

@section('title', '予定登録')

@section('content')
<div class="container-fluid px-3 py-3" style="font-size: 0.8rem;">
    @php
        $userRole = session('role');
        $canEdit = ($userRole === 'admin' || $userRole === 'manager' || $userRole === 'operations_manager');
    @endphp
    
    <form id="dateRemarkForm">
        @csrf
        <div class="mb-2">
            <label class="form-label">日付：</label>
            {{ $date }}
            <input type="hidden" id="remark_date_value" name="date" value="{{ $date }}">
        </div>
        
        <div class="mb-2">
            <label class="form-label">予定</label>
            <textarea class="form-control" id="remark_text" name="remark" rows="3" maxlength="500" placeholder="例：車両点検、会社休業日など" {{ !$canEdit ? 'readonly disabled' : '' }}>{{ $remark }}</textarea>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="stop_order" name="stop_order" value="1" {{ $stopOrder == '1' ? 'checked' : '' }} {{ !$canEdit ? 'disabled' : '' }}>
            <label class="form-check-label" for="stop_order">
                受注停止
            </label>
        </div>
        
        <div class="d-flex gap-4">
            @if($canEdit)
                <button type="submit" class="btn btn-primary">保存</button>
                <button type="button" class="btn btn-danger" onclick="deleteRemark()">削除</button>
            @endif
            <button type="button" class="btn btn-secondary" onclick="parent.closeIframeModal()" style="background-color: #33a64c;">閉じる</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    var canEdit = {{ $canEdit ? 'true' : 'false' }};
    
    function deleteRemark() {
        if (!canEdit) {
            alert('編集権限がありません。');
            return;
        }
        
        var date = document.getElementById('remark_date_value') ? document.getElementById('remark_date_value').value : '';
        
        if (!date) {
            return;
        }
        
        if (!confirm('この予定を削除してもよろしいですか？')) {
            return;
        }
        
        var csrfToken = document.querySelector('input[name="_token"]').value;
        
        fetch('{{ url("masters/group-info-date-remarks") }}/' + date, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.parent && window.parent.postMessage) {
                    window.parent.postMessage({
                        action: 'close-iframe-and-reload'
                    }, '*');
                }
            } else {
                alert('削除失敗：' + (data.message || '不明なエラー'));
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('削除中にエラーが発生しました。');
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var remarkText = document.getElementById('remark_text');
        var remarkDateValue = document.getElementById('remark_date_value');
        var stopOrderCheckbox = document.getElementById('stop_order');
        var dateRemarkForm = document.getElementById('dateRemarkForm');
        var csrfToken = document.querySelector('input[name="_token"]').value;
        
        if (dateRemarkForm && canEdit) {
            dateRemarkForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData();
                formData.append('date', remarkDateValue ? remarkDateValue.value : '');
                formData.append('remark', remarkText ? remarkText.value : '');
                formData.append('stop_order', stopOrderCheckbox && stopOrderCheckbox.checked ? 1 : 0);
                formData.append('_token', csrfToken);
                
                fetch('{{ route("masters.group-info-date-remarks.store") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (window.parent && window.parent.postMessage) {
                            window.parent.postMessage({
                                action: 'close-iframe-and-reload'
                            }, '*');
                        }
                    } else {
                        alert('保存失敗：' + (data.message || '不明なエラー'));
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    alert('保存中にエラーが発生しました。');
                });
            });
        } else if (dateRemarkForm && !canEdit) {
            dateRemarkForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('編集権限がありません。');
            });
        }
    });
</script>
@endpush