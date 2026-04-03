@extends('layouts.win')

@section('title', '日別旅程登録')

@section('content')
<div class="container-fluid mt-2">
    <form method="POST" action="{{ route('masters.daily-itineraries.store') }}" id="createForm">
        @csrf
        
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-1">
                    <span class="label-mini">予約</span>
                    <select name="key_uuid" class="form-control form-control-xs" required>
                        <option value="">-- 選択 --</option>
                        @foreach($groupInfos as $groupInfo)
                            <option value="{{ $groupInfo->key_uuid }}" {{ old('key_uuid') == $groupInfo->key_uuid ? 'selected' : '' }}>
                                {{ $groupInfo->agency }} - {{ $groupInfo->group_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="d-flex align-items-center mb-1">
                    <span class="label-mini">日付</span>
                    <input type="date" name="date" class="form-control form-control-xs" value="{{ old('date') }}" required>
                </div>
                
                <div class="d-flex mb-1">
                    <div class="flex-1 d-flex align-items-center me-1">
                        <span class="label-mini">開始</span>
                        <input type="time" name="time_start" class="form-control form-control-xs" value="{{ old('time_start') }}">
                    </div>
                    <div class="flex-1 d-flex align-items-center">
                        <span class="label-mini">終了</span>
                        <input type="time" name="time_end" class="form-control form-control-xs" value="{{ old('time_end') }}">
                    </div>
                </div>
                
                <div class="d-flex align-items-center mb-1">
                    <span class="label-mini">旅程</span>
                    <input type="text" name="itinerary" class="form-control form-control-xs" value="{{ old('itinerary') }}" placeholder="旅程内容" maxlength="200">
                </div>
                
                <div class="d-flex mb-1">
                    <div class="flex-1 d-flex align-items-center me-1">
                        <span class="label-mini">開始地</span>
                        <input type="text" name="start_location" class="form-control form-control-xs" value="{{ old('start_location') }}" placeholder="開始場所">
                    </div>
                    <div class="flex-1 d-flex align-items-center">
                        <span class="label-mini">終了地</span>
                        <input type="text" name="end_location" class="form-control form-control-xs" value="{{ old('end_location') }}" placeholder="終了場所">
                    </div>
                </div>
                
                <div class="d-flex align-items-center mb-1">
                    <span class="label-mini">宿泊</span>
                    <select name="accommodation" class="form-control form-control-xs">
                        <option value="0" {{ old('accommodation') == '0' ? 'selected' : '' }}>なし</option>
                        <option value="1" {{ old('accommodation') == '1' ? 'selected' : '' }}>あり</option>
                    </select>
                </div>
                
                <div class="d-flex mb-1">
                    <div class="flex-1 d-flex align-items-center me-1">
                        <span class="label-mini">車両</span>
                        <input type="text" name="vehicle" class="form-control form-control-xs" value="{{ old('vehicle') }}" placeholder="車両名">
                    </div>
                    <div class="flex-1 d-flex align-items-center">
                        <span class="label-mini">driver</span>
                        <input type="text" name="driver" class="form-control form-control-xs" value="{{ old('driver') }}" placeholder="ドライバー">
                    </div>
                </div>
                
                <div class="d-flex align-items-center mb-1">
                    <span class="label-mini">予約ID</span>
                    <input type="text" name="yoyaku_uuid" class="form-control form-control-xs" value="{{ old('yoyaku_uuid') }}" placeholder="任意">
                </div>
                
                <div class="d-flex align-items-center">
                    <span class="label-mini">備考</span>
                    <textarea name="remarks" class="form-control form-control-xs" rows="1" placeholder="備考">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>
            

            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="submit" class="btn-primary">作成</button>
            </div>
    </form>
</div>
@endsection


@push('styles')
<style>
    .p-1 { padding: 0.25rem !important; }
    .p-2 { padding: 0.5rem !important; }
    .mb-0 { margin-bottom: 0 !important; }
    .mb-1 { margin-bottom: 0.25rem !important; }
    .me-1 { margin-right: 0.25rem !important; }
    
    .text-md { font-size: 14px; font-weight: 600; }
    
    .card {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
    }
    
    .card-body { padding: 0.5rem; }
    .card-footer {
        background-color: #f9fafb;
        border-top: 1px solid #e5e7eb;
        padding: 0.5rem;
    }
    
    .form-control-xs {
        padding: 4px 6px;
        line-height: 1.3;
        border-radius: 3px;
    }
    
    textarea.form-control-xs {
        height: auto;
        min-height: 24px;
    }
    
    .label-mini {
        width: 45px;
        font-size: 11px;
        color: #6b7280;
        flex-shrink: 0;
        white-space: nowrap;
    }
    
    .d-flex { display: flex; }
    .align-items-center { align-items: center; }
    .justify-content-between { justify-content: space-between; }
    
    .flex-1 { flex: 1; }
    
    .btn-primary {
        background-color: #2563eb;
        border: none;
        color: white;
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
    
    .btn-primary:hover { background-color: #1d4ed8; }
    .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
    
    .btn-secondary {
        background-color: #6b7280;
        border: none;
        color: white;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
    
    .btn-secondary:hover { background-color: #4b5563; }
    
    .btn-xs {
        padding: 3px 8px;
        font-size: 10px;
    }
    
    .form-control-xs:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
    }
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

    document.getElementById('createForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const timeStart = document.querySelector('input[name="time_start"]').value;
        const timeEnd = document.querySelector('input[name="time_end"]').value;
        
        if (timeStart && timeEnd && timeStart >= timeEnd) {
            alert('終了時刻は開始時刻より後でなければなりません。');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = '登録中...';
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (isInIframe) {
                    window.parent.postMessage('close-iframe', '*');
                } else {
                    window.location.href = '{{ route('masters.daily-itineraries.index') }}';
                }
            } else {
                if (data.errors) {
                    let errorMessage = '入力エラー:\n';
                    for (let field in data.errors) {
                        errorMessage += data.errors[field].join('\n') + '\n';
                    }
                    alert(errorMessage);
                } else {
                    alert(data.message || '登録に失敗しました');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('通信エラーが発生しました');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = '登録';
        });
    });
});
</script>
@endpush