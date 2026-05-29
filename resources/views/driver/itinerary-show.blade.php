@extends('layouts.driver')

@section('title', '行程詳細')

@section('content')
<div class="mobile-container">
    @php
        $startDate = \Carbon\Carbon::parse($busAssignment->start_date);
        $endDate = \Carbon\Carbon::parse($busAssignment->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $currentDate = \Carbon\Carbon::parse($itinerary->date);
        $currentDay = $startDate->diffInDays($currentDate) + 1;
        $dayInfo = $currentDay . '/' . $totalDays;
    @endphp
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">{{ \Carbon\Carbon::parse($itinerary->date)->format('m月d日') }}</div>
        <div class="header-right">
            <div style="width: 32px;">{{ $dayInfo }}</div>
        </div>
    </div>

    <div class="itinerary-detail-section">
        @php
            $groupInfo = $itinerary->busAssignment->groupInfo ?? null;
            $reservationCategory = $groupInfo ? $groupInfo->reservationCategory : null;
            $bookingId = $groupInfo ? $groupInfo->id : '';
            $operationId = $itinerary->bus_assignment_id ?? '';
            $categoryName = $reservationCategory ? $reservationCategory->category_name : '未設定';
            $busAssignment = $itinerary->busAssignment;
            $isCompleted = $itinerary->is_completed ?? false;
            $agencyContactName = $groupInfo->agency_contact_name ?? '未設定';
        @endphp

        <div class="itinerary-card">
            <div class="card-header-row">
                <div class="left-group">
                    <span class="booking-operation-id">{{ $bookingId }}-{{ $operationId }}</span>
                    @if($isCompleted)
                    <span class="completed-badge">完了</span>
                    @endif
                </div>
                <div class="right-group">
                    @if($itinerary->busAssignment?->collection_amount)
                    <span class="collection-badge">集金</span>
                    @endif
                    <span class="category-name">{{ $categoryName }}</span>
                    <span class="guide-name">{{ $agencyContactName }}</span>
                </div>
            </div>

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

        <div class="detail-list">
            <div class="detail-item">
                <div class="detail-value-full">{{ $itinerary->itinerary ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-value-full" style="text-align: center;">
                    大: {{ $busAssignment->adult_count ?? 0 }}　
                    小: {{ $busAssignment->child_count ?? 0 }}
                </div>
                <div class="detail-value-full" style="text-align: center;">
                    荷物：{{ $busAssignment->luggage ?? '未設定' }}
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">注意</div>
                <div class="detail-value-full">{{ $selectedOptionsText }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">備考</div>
                <div class="detail-value-full">{{ $selectedOptionsText }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">集金</div>
                <div class="detail-value-full">{{ $itinerary->busAssignment?->collection_amount?? '' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">ステッカー</div>
                <div class="detail-value-full">{{ $busAssignment->step_car ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">お客様氏名・連絡先</div>
                <div class="detail-value-full">
                    {{ $busAssignment->representative ?? '' }} {{ $busAssignment->representative_phone ?? '' }}
                    @if($groupInfo && $groupInfo->agency_country)
                    <br>[{{ $groupInfo->agency_country }}]
                    @endif
                </div>
            </div>
        </div>
        
        
        @if($files->count() > 0)
        <div class="detail-list files-list">
            @foreach($files as $file)
            <div class="detail-item">
                <div class="detail-value">{{ $file->file_name }}</div>
                <div class="detail-value-right">
                    <a href="{{ route('driver.files.download', $file->id) }}" style="color: #2563eb; text-decoration: none;">
                        <i class="bi bi-download" style="font-size: 18px;"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="button-container">
            <button class="start-operation-btn" id="startOperationBtn" {{ $isCompleted ? 'disabled' : '' }}>運行開始</button>
            <button class="start-operation-btn" id="advancePaymentBtn">立替計算</button>
            <button class="back-btn" id="cancelBtn">戻る</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.files-list .detail-value-right { text-align: right;}
.itinerary-detail-section {
    padding: 12px;
}

.itinerary-card {
    padding: 16px;
    background-color: var(--card-bg);
    border-radius: 16px;
    margin-bottom: 16px;
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

.right-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.completed-badge {
    font-size: 10px;
    padding: 2px 8px;
    background-color: #10b981;
    color: white;
    border-radius: 20px;
}

.guide-name {
    font-size: 12px;
    color: var(--text-secondary);
    background-color: var(--border-color);
    padding: 2px 8px;
    border-radius: 12px;
}

.booking-operation-id {
    font-size: 12px;
    color: var(--text-secondary);
}

.category-name {
    font-size: 12px;
    color: var(--accent-color);
    background-color: var(--border-color);
    padding: 2px 8px;
    border-radius: 12px;
}

.divider {
    height: 1px;
    background-color: var(--border-color);
    margin-bottom: 12px;
}

.itinerary-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.itinerary-left {
    text-align: left;
    width: 35%;
    flex-shrink: 0;
}

.itinerary-center {
    width: 30%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.itinerary-right {
    text-align: right;
    width: 35%;
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

.itinerary-date {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-secondary);
}

.detail-list {
    background-color: var(--card-bg);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 16px;
}

.detail-item {
    display: flex;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color);
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    width: 80px;
    margin-right: 10px;
    font-size: 14px;
    color: var(--text-secondary);
    flex-shrink: 0;
}

.detail-value {
    width: 70px;
    font-size: 14px;
    color: var(--text-primary);
    flex-shrink: 0;
}

.detail-value-right {
    flex: 1;
    font-size: 14px;
    color: var(--text-primary);
    text-align: left;
    word-break: break-word;
}

.detail-value-full {
    flex: 1;
    font-size: 14px;
    color: var(--text-primary);
    word-break: break-word;
}

.button-container {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 16px;
}

.start-operation-btn {
    flex: 1;
    padding: 12px 16px;
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.start-operation-btn:disabled {
    display: none;
}

.back-btn {
    flex: 1;
    padding: 12px 16px;
    background-color: var(--card-bg);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.collection-badge {
    display: inline-block;
    background-color: #ffc107;
    color: #000;
    font-size: 0.7rem;
    font-weight: bold;
    padding: 1px 16px;
    border-radius: 12px;
}
</style>
@endpush

@push('scripts')
<script>
const backBtn = document.getElementById('backBtn');
const cancelBtn = document.getElementById('cancelBtn');
const startOperationBtn = document.getElementById('startOperationBtn');

if (backBtn) {
    backBtn.addEventListener('click', function() {
        window.history.back();
    });
}

if (cancelBtn) {
    cancelBtn.addEventListener('click', function() {
        window.history.back();
    });
}

if (startOperationBtn) {
    startOperationBtn.addEventListener('click', function() {
        const id = '{{ $itinerary->id }}';
        window.location.href = `/driver/operation/run/${id}`;
    });
}

const advancePaymentBtn = document.getElementById('advancePaymentBtn');
if (advancePaymentBtn) {
    advancePaymentBtn.addEventListener('click', function() {
        const id = '{{ $itinerary->id }}';
        window.location.href = `/driver/advance-payment/${id}`;
    });
}
</script>
@endpush