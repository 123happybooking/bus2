@extends('layouts.driver')

@section('title', '运行日报')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">{{ $dateTitle }}</div>
        <div class="header-right">
            <div class="page-subtitle">运行日报</div>
        </div>
    </div>

    <div class="report-form">
        <div class="form-group">
            <label class="form-label">車両</label>
            <input type="text" class="form-input" value="{{ $vehicles->first()->registration_number ?? '' }}" readonly disabled>
            <input type="hidden" id="vehicle_id" value="{{ $defaultVehicleId }}">
        </div>

        @if($report)
        <div class="form-group">
            <label class="form-label">天気</label>
            <select name="weather" id="weather" class="form-input" {{ $allowEdit ? '' : 'disabled' }}>
                <option value="">選択してください</option>
                <option value="晴れ" {{ ($report->weather ?? '') == '晴れ' ? 'selected' : '' }}>晴れ</option>
                <option value="曇り" {{ ($report->weather ?? '') == '曇り' ? 'selected' : '' }}>曇り</option>
                <option value="雨" {{ ($report->weather ?? '') == '雨' ? 'selected' : '' }}>雨</option>
                <option value="雪" {{ ($report->weather ?? '') == '雪' ? 'selected' : '' }}>雪</option>
                <option value="霧" {{ ($report->weather ?? '') == '霧' ? 'selected' : '' }}>霧</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">始業時刻</label>
            <input type="time" id="start_work_time" class="form-input" value="{{ $report->start_work_time ? \Carbon\Carbon::parse($report->start_work_time)->format('H:i') : '' }}" {{ $allowEdit ? '' : 'readonly disabled' }}>
        </div>

        <div class="form-group">
            <label class="form-label">出庫時間</label>
            <input type="time" id="start_time" class="form-input" value="{{ $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '' }}" {{ $allowEdit ? '' : 'readonly disabled' }}>
        </div>

        <div class="form-group">
            <label class="form-label">出庫時メーター</label>
            <div class="input-with-unit">
                <input type="number" id="start_mileage" class="form-input" value="{{ $report->start_mileage }}" min="0" {{ $allowEdit ? '' : 'readonly disabled' }}>
                <span class="unit">km</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">帰庫時間</label>
            <input type="time" id="end_time" class="form-input" value="{{ $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '' }}" {{ $allowEdit ? '' : 'readonly disabled' }}>
        </div>

        <div class="form-group">
            <label class="form-label">帰庫時メーター</label>
            <div class="input-with-unit">
                <input type="number" id="end_mileage" class="form-input" value="{{ $report->end_mileage }}" min="0" {{ $allowEdit ? '' : 'readonly disabled' }}>
                <span class="unit">km</span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">終業時刻</label>
            <input type="time" id="end_work_time" class="form-input" value="{{ $report->end_work_time ? \Carbon\Carbon::parse($report->end_work_time)->format('H:i') : '' }}" {{ $allowEdit ? '' : 'readonly disabled' }}>
        </div>

        <div class="form-group">
            <label class="form-label">走行距離</label>
            <div class="distance-value">
                <span id="distance">{{ $report->distance ?? '0' }}</span>
                <span class="unit">km</span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">実車距離</label>
            <div class="input-with-unit">
                <input type="number" name="actual_distance" id="actual_distance" class="form-input" 
                       value="{{ old('actual_distance', $report->actual_distance) }}" min="0" step="1" {{ $allowEdit ? '' : 'readonly disabled' }}>
                <span class="unit">km</span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">空車距離</label>
            <div class="input-with-unit">
                <input type="number" name="empty_distance" id="empty_distance" class="form-input" 
                       value="{{ old('empty_distance', $report->empty_distance) }}" min="0" step="1" {{ $allowEdit ? '' : 'readonly disabled' }}>
                <span class="unit">km</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">備考</label>
            <textarea name="remark" id="remark" class="form-input" rows="3" {{ $allowEdit ? '' : 'readonly disabled' }}>{{ $report->remark ?? '' }}</textarea>
        </div>

        @if($completedItineraries->count() > 0)
        <div class="completed-tasks-section">
            <div class="section-title">操作詳細</div>
            @foreach($completedItineraries as $itinerary)
            <div class="completed-task-card">
                <div class="task-header">
                    <span class="task-id">{{ $itinerary->busAssignment->groupInfo->id ?? '' }}-{{ $itinerary->bus_assignment_id }}</span>
                    <span class="task-time">{{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }}</span>
                </div>
                <div class="task-logs">
                    <div class="logs-header">
                        <span class="log-time">時間</span>
                        <span class="log-mileage">走行距離</span>
                        <span class="log-action">操作</span>
                    </div>
                    @foreach($itinerary->operationLogs as $log)
                    <div class="log-row">
                        <span class="log-time">{{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}</span>
                        <span class="log-mileage">{{ $log->mileage ? $log->mileage . ' km' : '-' }}</span>
                        <span class="log-action">{{ $log->action }}</span>
                    </div>
                    @endforeach
                </div>
                
                @php
                    $itineraryExpenses = $expensesByItinerary[$itinerary->id] ?? collect();
                @endphp
                @if($itineraryExpenses->count() > 0)
                <div class="expense-section">
                    <div class="expense-header">立替金</div>
                    <div class="expense-list">
                        @foreach($itineraryExpenses as $expense)
                        <div class="expense-item">
                            <div class="expense-row">
                                <div class="expense-left">
                                    <div class="expense-date">{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y/m/d') }}</div>
                                    <div class="expense-type">
                                        {{ $expense->expenseType->type_name ?? '' }}
                                        @if($expense->agency_flag)
                                        <span class="expense-badge">代理店負担</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="expense-right">
                                    <div class="expense-amount">¥ {{ number_format($expense->amount) }}</div>
                                    <div class="expense-payment">{{ $expense->paymentMethod->method_name ?? '' }}</div>
                                </div>
                            </div>
                            @if($expense->remark)
                            <div class="expense-remark">{{ $expense->remark }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if($allowEdit)
        <button class="save-btn" id="saveBtn">保存</button>
        @else
        <button class="save-btn" id="backToListBtn">戻る</button>
        @endif
        @else
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <div class="empty-text">この車両の運転日報はありません</div>
            <button class="create-btn" id="createBtn">作成する</button>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.report-form {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 0;
    border-radius: 12px;
    font-size: 14px;
    background-color: var(--card-bg);
    color: var(--text-primary);
    outline: none;
}

.form-input:focus {
    border-color: var(--accent-color);
}

.form-input:read-only,
.form-input:disabled {
    background-color: var(--card-bg);
    color: var(--text-primary);
    cursor: default;
    opacity: 1;
}

.input-with-unit {
    display: flex;
    align-items: center;
    gap: 8px;
}

.input-with-unit .form-input {
    flex: 1;
}

.unit {
    font-size: 14px;
    color: var(--text-secondary);
    width: 40px;
}

.distance-value {
    font-size: 24px;
    font-weight: 600;
    color: var(--accent-color);
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 20px;
}

.distance-value .unit {
    font-size: 14px;
    font-weight: normal;
}

.completed-tasks-section {
    margin-bottom: 20px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 12px;
    padding-left: 4px;
}

.completed-task-card {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 12px;
}

.task-header {
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border-color);
}

.task-id {
    font-size: 12px;
    color: var(--text-secondary);
    margin-right: 12px;
}

.task-time {
    font-size: 13px;
    color: var(--text-primary);
}

.task-logs {
    background-color: var(--bg-color);
    border-radius: 12px;
    overflow: hidden;
}

.logs-header {
    display: flex;
    padding: 10px 12px;
    border-bottom: 1px solid var(--border-color);
    font-size: 11px;
    font-weight: 600;
    color: var(--text-secondary);
}

.log-row {
    display: flex;
    padding: 8px 12px;
    border-bottom: 1px solid var(--border-color);
    font-size: 12px;
    color: var(--text-primary);
}

.log-row:last-child {
    border-bottom: none;
}

.log-time {
    width: 80px;
    flex-shrink: 0;
}

.log-mileage {
    width: 80px;
    flex-shrink: 0;
    text-align: right;
}

.log-action {
    flex: 1;
    text-align: right;
}

.save-btn {
    width: 100%;
    padding: 14px;
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-text {
    font-size: 16px;
    color: var(--text-secondary);
    margin-bottom: 30px;
}

.create-btn {
    padding: 12px 32px;
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}


.expense-section {
    margin-top: 12px;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
}

.expense-header {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.expense-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.expense-item {
    background-color: var(--bg-color);
    border-radius: 12px;
    padding: 12px;
    position: relative;
}

.expense-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.expense-left {
    flex: 1;
}

.expense-right {
    text-align: right;
}

.expense-date {
    font-size: 11px;
    color: var(--text-secondary);
    margin-bottom: 4px;
}

.expense-type {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-primary);
}

.expense-amount {
    font-size: 14px;
    font-weight: 600;
    color: var(--accent-color);
}

.expense-payment {
    font-size: 10px;
    color: var(--text-secondary);
    margin-top: 2px;
}

.expense-remark {
    font-size: 11px;
    color: var(--text-secondary);
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px dashed var(--border-color);
}

.expense-badge {
    font-size: 9px;
    padding: 2px 8px;
    margin-left: 8px;
    background-color: #f59e0b;
    color: white;
    border-radius: 20px;
    display: inline-block;
}
</style>
@endpush

@push('scripts')
<script>
const reportId = {{ $report ? $report->id : 'null' }};
const currentDate = '{{ $date }}';
const allowEdit = {{ $allowEdit ? 'true' : 'false' }};
const currentVehicleId = '{{ $vehicleId ?? '' }}';
const totalReports = {{ $totalReports ?? 1 }};

function updateDistance() {
    const startMileage = parseInt(document.getElementById('start_mileage').value) || 0;
    const endMileage = parseInt(document.getElementById('end_mileage').value) || 0;
    const distanceSpan = document.getElementById('distance');
    
    if (startMileage > 0 && endMileage > 0 && endMileage >= startMileage) {
        const distance = endMileage - startMileage;
        distanceSpan.textContent = distance;
    } else {
        distanceSpan.textContent = '0';
    }
}

function changeVehicle(vehicleId) {
    window.location.href = `/driver/daily-reports/${currentDate}/${vehicleId}`;
}

document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = '/driver/daily-itineraries/' + currentDate;
});

if (document.getElementById('createBtn')) {
    document.getElementById('createBtn').addEventListener('click', function() {
        const vehicleId = document.getElementById('vehicle_id').value;
        
        if (!vehicleId) {
            alert('車両を選択してください。');
            return;
        }
        
        fetch(`/driver/daily-reports/create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                date: currentDate,
                vehicle_id: vehicleId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('作成に失敗しました: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('エラーが発生しました');
        });
    });
}

if (document.getElementById('saveBtn')) {
    const startTimeInput = document.getElementById('start_time');
    const startMileageInput = document.getElementById('start_mileage');
    const endTimeInput = document.getElementById('end_time');
    const endMileageInput = document.getElementById('end_mileage');
    const vehicleSelect = document.getElementById('vehicle_id');
    const weatherSelect = document.getElementById('weather');
    const startWorkTimeInput = document.getElementById('start_work_time');
    const endWorkTimeInput = document.getElementById('end_work_time');
    const remarkTextarea = document.getElementById('remark');
    const actualDistance = document.getElementById('actual_distance');
    const emptyDistance = document.getElementById('empty_distance');
    
    startMileageInput.addEventListener('input', updateDistance);
    endMileageInput.addEventListener('input', updateDistance);
    
    document.getElementById('saveBtn').addEventListener('click', function() {
        const data = {
            start_time: startTimeInput.value,
            start_mileage: startMileageInput.value,
            end_time: endTimeInput.value,
            end_mileage: endMileageInput.value,
            vehicle_id: vehicleSelect ? vehicleSelect.value : null,
            weather: weatherSelect ? weatherSelect.value : null,
            start_work_time: startWorkTimeInput ? startWorkTimeInput.value : null,
            end_work_time: endWorkTimeInput ? endWorkTimeInput.value : null,
            remark: remarkTextarea ? remarkTextarea.value : null,
            actual_distance: actualDistance ? actualDistance.value : null,
            empty_distance: emptyDistance ? emptyDistance.value : null,
        };
        
        fetch(`/driver/daily-reports/${reportId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('保存しました');
                window.location.href = `/driver/daily-itineraries/${currentDate}`;
            } else {
                alert('保存に失敗しました');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('エラーが発生しました');
        });
    });
}

if (document.getElementById('backToListBtn')) {
    if (totalReports > 1) {
        document.getElementById('backToListBtn').style.display = 'block';
        document.getElementById('backToListBtn').addEventListener('click', function() {
            window.location.href = `/driver/daily-reports/${currentDate}`;
        });
    }
}

@if(session('error_alert'))
    alert('{{ session('error_alert') }}');
@endif
</script>
@endpush