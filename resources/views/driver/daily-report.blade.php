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

    @if($report)
    <div class="report-form">
        <div class="form-group">
            <label class="form-label">車両</label>
            <select id="vehicle_id" class="form-input" {{ $isToday ? '' : 'disabled' }}>
                @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ $defaultVehicleId == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->registration_number }}
                </option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">出庫時間</label>
            <input type="time" id="start_time" class="form-input" value="{{ $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '' }}" {{ $isToday ? '' : 'readonly disabled' }}>
        </div>

        <div class="form-group">
            <label class="form-label">出庫時メーター</label>
            <div class="input-with-unit">
                <input type="number" id="start_mileage" class="form-input" value="{{ $report->start_mileage }}" min="0" {{ $isToday ? '' : 'readonly disabled' }}>
                <span class="unit">km</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">帰庫時間</label>
            <input type="time" id="end_time" class="form-input" value="{{ $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '' }}" {{ $isToday ? '' : 'readonly disabled' }}>
        </div>

        <div class="form-group">
            <label class="form-label">帰庫時メーター</label>
            <div class="input-with-unit">
                <input type="number" id="end_mileage" class="form-input" value="{{ $report->end_mileage }}" min="0" {{ $isToday ? '' : 'readonly disabled' }}>
                <span class="unit">km</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">走行距離</label>
            <div class="distance-value">
                <span id="distance">{{ $report->distance ?? '0' }}</span>
                <span class="unit">km</span>
            </div>
        </div>

        @if($completedItineraries && count($completedItineraries) > 0)
        <div class="completed-tasks-section">
            <div class="section-title">完了した運行</div>
            @foreach($completedItineraries as $itinerary)
            <div class="completed-task-card">
                <div class="task-header">
                    <span class="task-id">{{ $itinerary->busAssignment->groupInfo->id ?? '' }}-{{ $itinerary->bus_assignment_id ?? '' }}</span>
                    <span class="task-time">{{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }} {{ $itinerary->start_location ?? '' }} → {{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }} {{ $itinerary->end_location ?? '' }}</span>
                </div>
                <div class="task-logs">
                    <div class="logs-header">
                        <span class="log-time">時間</span>
                        <span class="log-mileage">走行距離</span>
                        <span class="log-action">操作</span>
                    </div>
                    @foreach($itinerary->operationLogs as $log)
                    <div class="log-row">
                        <span class="log-time">{{ \Carbon\Carbon::parse($log->logged_at)->format('H:i:s') }}</span>
                        <span class="log-mileage">{{ $log->mileage ?? '-' }}</span>
                        <span class="log-action">{{ $log->action }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($isToday)
        <button class="save-btn" id="saveBtn">保存</button>
        @else
        <button class="save-btn" id="backToListBtn">戻る</button>
        @endif
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">📋</div>
        <div class="empty-text">本日の運転日報がありません</div>
        @if($isToday)
        <button class="create-btn" id="createBtn">作成する</button>
        @endif
    </div>
    @endif
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
</style>
@endpush

@push('scripts')
<script>
const reportId = {{ $report ? $report->id : 'null' }};
const currentDate = '{{ $date }}';
const isToday = {{ $isToday ? 'true' : 'false' }};

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

document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = '/driver/daily-itineraries/' + currentDate;
});

if (document.getElementById('createBtn')) {
    document.getElementById('createBtn').addEventListener('click', function() {
        fetch(`/driver/daily-reports/create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                date: currentDate
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
    
    startMileageInput.addEventListener('input', updateDistance);
    endMileageInput.addEventListener('input', updateDistance);
    
    document.getElementById('saveBtn').addEventListener('click', function() {
        const data = {
            start_time: startTimeInput.value,
            start_mileage: startMileageInput.value,
            end_time: endTimeInput.value,
            end_mileage: endMileageInput.value,
            vehicle_id: vehicleSelect ? vehicleSelect.value : null,
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
    document.getElementById('backToListBtn').addEventListener('click', function() {
        window.location.href = '/driver/daily-itineraries/' + currentDate;
    });
}

@if(session('error_alert'))
    alert('{{ session('error_alert') }}');
@endif
</script>
@endpush