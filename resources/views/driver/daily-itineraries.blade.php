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
            <button class="report-btn" id="reportBtn">
                运行日报
            </button>
        </div>
    </div>

    <div class="tab-bar">
        <button class="tab-btn active" data-filter="pending">未運転</button>
        <button class="tab-btn" data-filter="all">全部</button>
        <button class="tab-btn" data-filter="completed">完了</button>
    </div>

    <div class="itinerary-list">
        @forelse($itineraries as $itinerary)
        @php
            $groupInfo = $itinerary->busAssignment->groupInfo ?? null;
            $reservationCategory = $groupInfo ? $groupInfo->reservationCategory : null;
            $group_info_id = $groupInfo ? $groupInfo->id : '';
            $bus_assignment_id = $itinerary->bus_assignment_id ?? '';
            $categoryName = $reservationCategory ? $reservationCategory->category_name : '';
            $isCompleted = $itinerary->is_completed ?? false;
            $guideName = $itinerary->busAssignment->guide->name ?? '';
        @endphp
        <div class="itinerary-card" data-id="{{ $itinerary->id }}" data-status="{{ $isCompleted ? 'completed' : 'pending' }}">
            <div class="card-header-row">
                <div class="left-group">
                    <span class="booking-operation-id">{{ $group_info_id }}-{{ $bus_assignment_id }}</span>
                    @if($isCompleted)
                    <span class="completed-badge">完了</span>
                    @endif
                </div>
                <div class="right-group">
                    <span class="category-name">{{ $categoryName }}</span>
                    <span class="guide-name">{{ $guideName }}</span>
                </div>
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

.report-btn {
    padding: 8px 16px;
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.tab-bar {
    display: flex;
    background-color: var(--card-bg);
    margin: 0 12px;
    border-radius: 16px;
    padding: 4px;
    gap: 4px;
}

.tab-btn {
    flex: 1;
    padding: 10px 0;
    border: none;
    background: transparent;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
    cursor: pointer;
    border-radius: 12px;
    transition: all 0.2s;
}

.tab-btn.active {
    background-color: var(--accent-color);
    color: var(--accent-text);
}

.itinerary-list {
    padding: 12px;
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

.itinerary-card.hidden {
    display: none;
}

.card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    gap: 8px;
}

.right-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
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
    background-color: var(--border-color);
    padding: 2px 8px;
    border-radius: 12px;
}

.guide-name {
    font-size: 12px;
    color: var(--text-secondary);
    background-color: var(--border-color);
    padding: 2px 8px;
    border-radius: 12px;
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

const filterBtns = document.querySelectorAll('.tab-btn');
const itineraryCards = document.querySelectorAll('.itinerary-card');

function filterItineraries(filterValue) {
    itineraryCards.forEach(card => {
        const status = card.getAttribute('data-status');
        
        if (filterValue === 'all') {
            card.classList.remove('hidden');
        } else if (filterValue === 'pending') {
            if (status === 'pending') {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        } else if (filterValue === 'completed') {
            if (status === 'completed') {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        }
    });
    
    const visibleCards = document.querySelectorAll('.itinerary-card:not(.hidden)');
    const emptyDiv = document.querySelector('.empty');
    
    if (visibleCards.length === 0 && !emptyDiv) {
        const itineraryList = document.querySelector('.itinerary-list');
        const newEmptyDiv = document.createElement('div');
        newEmptyDiv.className = 'empty';
        newEmptyDiv.textContent = '予定はありません';
        itineraryList.appendChild(newEmptyDiv);
    } else if (visibleCards.length > 0 && emptyDiv) {
        emptyDiv.remove();
    }
}

filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        filterBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filterValue = this.getAttribute('data-filter');
        filterItineraries(filterValue);
    });
});

filterItineraries('pending');

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