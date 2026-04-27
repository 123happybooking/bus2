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

    <div class="report-list-container">
        <div class="section-title">車両別日報</div>
        <div class="report-list">
            @foreach($reports as $item)
            <div class="report-card">
                <div class="report-card-header">
                    <span class="vehicle-name">{{ $item['vehicle_name'] }}</span>
                    @if($item['exists'])
                        @if($item['allow_edit'])
                            <span class="status-badge status-edit">編集可能</span>
                        @else
                            <span class="status-badge status-readonly">閲覧のみ</span>
                        @endif
                    @else
                        <span class="status-badge status-pending">未作成</span>
                    @endif
                </div>
                <div class="report-card-footer">
                    @if($item['exists'])
                        @if($item['allow_edit'])
                        <a href="{{ route('driver.daily-reports', ['date' => $date, 'vehicleId' => $item['vehicle_id']]) }}" class="btn-edit">編集する</a>
                        @else
                        <a href="{{ route('driver.daily-reports', ['date' => $date, 'vehicleId' => $item['vehicle_id']]) }}" class="btn-view">閲覧する</a>
                        @endif
                    @else
                        <button class="btn-create" data-vehicle-id="{{ $item['vehicle_id'] }}">作成する</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.report-list-container {
    padding: 12px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 16px;
    padding-left: 4px;
}

.report-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.report-card {
    background-color: var(--card-bg);
    border-radius: 16px;
    overflow: hidden;
}

.report-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
}

.vehicle-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
}

.status-badge {
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
}

.status-edit {
    background-color: #10b981;
    color: white;
}

.status-readonly {
    background-color: #6c757d;
    color: white;
}

.status-pending {
    background-color: #f59e0b;
    color: white;
}

.report-card-body {
    padding: 16px;
}

.report-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-label {
    font-size: 12px;
    color: var(--text-secondary);
    min-width: 50px;
}

.info-value {
    font-size: 14px;
    color: var(--text-primary);
    font-weight: 500;
}

.info-empty {
    text-align: center;
    font-size: 13px;
    color: var(--text-secondary);
    padding: 12px 0;
}

.report-card-footer {
    padding: 12px 16px;
}

.btn-edit, .btn-view, .btn-create {
    display: block;
    width: 100%;
    padding: 12px;
    text-align: center;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}

.btn-edit {
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
}

.btn-view {
    background-color: var(--bg-color);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-create {
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
}
</style>
@endpush

@push('scripts')
<script>
const currentDate = '{{ $date }}';

document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = '/driver/daily-itineraries/' + currentDate;
});

document.querySelectorAll('.btn-create').forEach(btn => {
    btn.addEventListener('click', function() {
        const vehicleId = this.getAttribute('data-vehicle-id');
        
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
                window.location.href = `/driver/daily-reports/${currentDate}/${vehicleId}`;
            } else {
                alert('作成に失敗しました: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('エラーが発生しました');
        });
    });
});
</script>
@endpush