@extends('layouts.driver')

@section('title', '行程一覧')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">{{ $formattedDate }}</div>
        <div class="header-right">
            <div class="page-subtitle">行程一覧</div>
        </div>
    </div>

    <div class="report-btn-container">
        <button class="report-btn" id="reportBtn">运行日报</button>
    </div>

    <div class="itinerary-list">
        @forelse($itineraries as $itinerary)
        @php
            $groupInfo = $itinerary->busAssignment->groupInfo ?? null;
            $reservationCategory = $groupInfo ? $groupInfo->reservationCategory : null;
            $group_info_id = $groupInfo ? $groupInfo->id : '';
            $bus_assignment_id = $itinerary->bus_assignment_id ?? '';
            $categoryName = $reservationCategory ? $reservationCategory->category_name : '';
            $isCompleted = $itinerary->operation_status === '終了';
        @endphp
        <div class="itinerary-card" data-id="{{ $itinerary->id }}">
            <div class="card-header-row">
                <div class="left-group">
                    <span class="booking-operation-id">{{ $group_info_id }}-{{ $bus_assignment_id }}</span>
                    @if($isCompleted)
                    <span class="completed-badge">完了</span>
                    @endif
                </div>
                <span class="category-name">{{ $categoryName }}</span>
            </div>
            <div class="divider"></div>
            <div class="itinerary-row">
                <div class="itinerary-left">
                    <div class="start-time">{{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }}</div>
                    <div class="start-location">{{ $itinerary->start_location ?? '' }}</div>
                </div>

                <div class="itinerary-center">
                    <div class="itinerary-vehicle">{{ $itinerary->vehicle ?? '' }}</div>
                    <div class="arrow-container">
                        <div class="arrow-line"></div>
                        <div class="arrow-triangle"></div>
                    </div>
                </div>

                <div class="itinerary-right">
                    <div class="end-time">{{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }}</div>
                    <div class="end-location">{{ $itinerary->end_location ?? '' }}</div>
                </div>
            </div>

            <div class="itinerary-footer">
                <span class="detail-link">行程详情 &gt;</span>
            </div>
        </div>
        @empty
        <div class="empty">予定はありません</div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background-color: var(--header-bg);
    color: var(--text-primary);
}

.menu-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.back-arrow {
    width: 10px;
    height: 10px;
    border-left: 2px solid var(--text-primary);
    border-bottom: 2px solid var(--text-primary);
    transform: rotate(45deg);
}

.page-title {
    font-size: 16px;
    font-weight: 500;
    color: var(--text-primary);
    flex: 1;
    text-align: left;
    margin-left: 8px;
}

.header-right {
    flex-shrink: 0;
}

.page-subtitle {
    font-size: 16px;
    font-weight: 500;
    color: var(--text-primary);
}

.report-btn-container {
    display: flex;
    justify-content: center;
    padding: 12px;
}

.report-btn {
    padding: 10px 24px;
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.itinerary-list {
    padding: 0 12px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.itinerary-card {
    padding: 16px;
    background-color: var(--card-bg);
    cursor: pointer;
    border-radius: 16px;
}

.card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.left-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.booking-operation-id {
    font-size: 12px;
    color: var(--text-secondary);
}

.completed-badge {
    font-size: 10px;
    padding: 2px 8px;
    background-color: #10b981;
    color: white;
    border-radius: 20px;
}

.category-name {
    font-size: 12px;
    color: var(--accent-color);
}

.divider {
    height: 1px;
    background-color: var(--border-color);
    margin: 8px 0;
}

.itinerary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.itinerary-left {
    text-align: left;
    width: 25%;
    flex-shrink: 0;
}

.itinerary-center {
    width: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.itinerary-right {
    text-align: right;
    width: 25%;
    flex-shrink: 0;
}

.start-time {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

.start-location {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 120px;
}

.end-time {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

.end-location {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 120px;
}

.itinerary-vehicle {
    font-size: 13px;
    color: var(--accent-color);
}

.arrow-container {
    width: 100%;
    display: flex;
    align-items: center;
    margin: 8px 0;
}

.arrow-line {
    flex: 1;
    height: 2px;
    background-color: var(--text-secondary);
}

.arrow-triangle {
    width: 0;
    height: 0;
    border-left: 8px solid var(--text-secondary);
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
}

.itinerary-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 12px;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
}

.detail-link {
    font-size: 13px;
    color: var(--accent-color);
    text-decoration: none;
}

.empty {
    text-align: center;
    padding: 40px;
    color: var(--text-secondary);
    font-size: 13px;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = '/driver/dashboard';
});

document.getElementById('reportBtn').addEventListener('click', function() {
    const date = '{{ $date }}';
    window.location.href = `/driver/daily-reports/${date}`;
});

document.querySelectorAll('.itinerary-card').forEach(card => {
    card.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        if (id) {
            window.location.href = `/driver/itinerary/${id}`;
        }
    });
});

@if(session('error_alert'))
    alert("{{ session('error_alert') }}");
@endif
</script>
@endpush