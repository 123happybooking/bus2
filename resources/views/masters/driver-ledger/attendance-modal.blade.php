@extends('layouts.win')

@section('title', '運転手勤怠')

@section('content')
<div class="container-fluid px-3 py-3" style="font-size: 0.8rem;">
    @php
        $userRole = session('role');
        $canEdit = ($userRole === 'admin' || $userRole === 'manager' || $userRole === 'operations_manager');
    @endphp
    
    <form id="attendanceForm">
        @csrf
        <input type="hidden" name="driver_id" value="{{ $driver->id }}">
        
        <div class="mb-2">
            <label class="form-label">運転手：</label>
            {{ $driver->name }}
        </div>
        
        <div class="mb-2">
            <label class="form-label">開始日</label>
            <div class="date-range-group">
                <div class="date-start-group">
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" 
                           class="form-control" {{ !$canEdit ? 'readonly disabled' : '' }}>
                    <input type="time" name="start_time" id="start_time" value="{{ \Carbon\Carbon::parse($startTime)->format('H:i') }}" 
                           class="form-control" step="60" {{ !$canEdit ? 'readonly disabled' : '' }}>
                </div>
                <div class="date-separator">~</div>
                <div class="date-end-group">
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" 
                           class="form-control" {{ !$canEdit ? 'readonly disabled' : '' }}>
                    <input type="time" name="end_time" id="end_time" value="{{ \Carbon\Carbon::parse($endTime)->format('H:i') }}" 
                           class="form-control" step="60" {{ !$canEdit ? 'readonly disabled' : '' }}>
                </div>
            </div>
        </div>
        
        <div class="mb-2">
            <label class="form-label">勤怠分類</label>
            <select name="attendance_category_id" id="attendance_category_id" class="form-select" {{ !$canEdit ? 'disabled' : '' }}>
                <option value="">-- 選択してください --</option>
                @foreach($attendanceCategories as $category)
                    <option value="{{ $category->id }}" 
                        style="background-color: {{ $category->color_code ?? '#ffffff' }}; color: #000000;"
                        {{ $categoryId == $category->id ? 'selected' : '' }}>
                        {{ $category->attendance_name }}
                    </option>
                @endforeach
            </select>
            <div id="categoryError" class="text-danger" style="font-size: 0.7rem; display: none;">勤怠分類を選択してください。</div>
        </div>
        
        <div class="mb-2">
            <label class="form-label">備考</label>
            <textarea name="remarks" id="remarks" class="form-control" rows="3" maxlength="500" {{ !$canEdit ? 'readonly disabled' : '' }}>{{ $remarks }}</textarea>
        </div>
        
        <div class="d-flex gap-4 mt-3">
            @if($canEdit)
                <button type="submit" class="btn btn-primary">保存</button>
            @endif
            @if($canEdit && !empty($categoryId))
                <button type="button" class="btn btn-danger" id="deleteBtn">削除</button>
            @endif
            <button type="button" class="btn btn-secondary" onclick="parent.closeIframeModal()">キャンセル</button>
            <button type="button" class="btn btn-secondary" onclick="parent.closeIframeModal()" style="background-color: #33a64c;">閉じる</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.form-select option {
    color: #000000 !important;
}

input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover,
input[type="time"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}


.date-range-group {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 5px;
}

.date-start-group {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.date-start-group input,
.date-end-group input {
    flex: 1;
    min-width: 0;
}

.date-separator {
    text-align: center;
    font-size: 0.9rem;
    color: #6b7280;
    flex-shrink: 0;
}

.date-end-group {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    flex: 1;
}


@media (max-width: 500px) {
    .date-range-group {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
    }
    
    .date-start-group {
        display: flex !important;
        flex-direction: row !important;
        gap: 8px !important;
    }
    
    .date-start-group input,
    .date-end-group input {
        flex: 1 !important;
        width: auto !important;
    }
    
    .date-separator {
        text-align: center !important;
        font-size: 0.9rem !important;
        color: #6b7280 !important;
        line-height: 3pt;
    }
    
    .date-end-group {
        display: flex !important;
        flex-direction: row !important;
        gap: 8px !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
    var canEdit = {{ $canEdit ? 'true' : 'false' }};
    
    var remarkText = document.getElementById('remarks');
    var charCount = document.getElementById('charCount');
    
    function updateCharCount() {
        if (charCount && remarkText) {
            charCount.textContent = remarkText.value.length;
        }
    }
    
    if (remarkText) {
        remarkText.addEventListener('input', updateCharCount);
    }
    
    function validateDates() {
        var startDate = document.getElementById('start_date').value;
        var endDate = document.getElementById('end_date').value;
        var startTime = document.getElementById('start_time').value;
        var endTime = document.getElementById('end_time').value;
        
        if (!startDate || !endDate) {
            return true;
        }
        
        var startDateTime = new Date(startDate + 'T' + (startTime || '00:00') + ':00');
        var endDateTime = new Date(endDate + 'T' + (endTime || '00:00') + ':00');
        
        if (endDateTime < startDateTime) {
            alert('終了日時は開始日時より後の日時を設定してください。');
            return false;
        }
        
        return true;
    }
    
    function validateForm() {
        var categorySelect = document.getElementById('attendance_category_id');
        var categoryError = document.getElementById('categoryError');
        
        if (!categorySelect.value) {
            categoryError.style.display = 'block';
            categorySelect.classList.add('is-invalid');
            return false;
        }
        
        categoryError.style.display = 'none';
        categorySelect.classList.remove('is-invalid');
        
        if (!validateDates()) {
            return false;
        }
        
        return true;
    }
    
    function deleteAttendance() {
        if (!canEdit) {
            alert('編集権限がありません。');
            return;
        }
        
        var driverId = document.querySelector('input[name="driver_id"]').value;
        var startDate = document.getElementById('start_date').value;
        
        if (!driverId || !startDate) {
            return;
        }
        
        if (!confirm('この勤怠情報を削除してもよろしいですか？')) {
            return;
        }
        
        var csrfToken = document.querySelector('input[name="_token"]').value;
        
        fetch('{{ url("masters/driver-attendance") }}/' + driverId + '/' + startDate, {
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
        var attendanceForm = document.getElementById('attendanceForm');
        var startDateInput = document.getElementById('start_date');
        var endDateInput = document.getElementById('end_date');
        var startTimeInput = document.getElementById('start_time');
        var endTimeInput = document.getElementById('end_time');
        var categorySelect = document.getElementById('attendance_category_id');
        var remarksTextarea = document.getElementById('remarks');
        var deleteBtn = document.getElementById('deleteBtn');
        var csrfToken = document.querySelector('input[name="_token"]').value;
        
        if (deleteBtn) {
            deleteBtn.addEventListener('click', deleteAttendance);
        }
        
        if (attendanceForm && canEdit) {
            attendanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!validateForm()) {
                    return;
                }
                
                var formData = new FormData();
                formData.append('driver_id', document.querySelector('input[name="driver_id"]').value);
                formData.append('start_date', startDateInput.value);
                formData.append('end_date', endDateInput.value);
                formData.append('attendance_category_id', categorySelect.value);
                formData.append('start_time', startTimeInput.value);
                formData.append('end_time', endTimeInput.value);
                formData.append('remarks', remarksTextarea ? remarksTextarea.value : '');
                formData.append('_token', csrfToken);
                
                fetch('{{ route("masters.driver-attendance.store") }}', {
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
        } else if (attendanceForm && !canEdit) {
            attendanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('編集権限がありません。');
            });
        }
    });
</script>
@endpush