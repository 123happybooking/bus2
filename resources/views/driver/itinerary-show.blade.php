@extends('layouts.driver')

@section('title', '行程詳細')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">行程詳細</div>
        <div class="header-right">
            <div style="width: 32px;"></div>
        </div>
    </div>

    <div class="itinerary-detail-section">
        @php
            $groupInfo = $itinerary->busAssignment->groupInfo ?? null;
            $reservationCategory = $groupInfo ? $groupInfo->reservationCategory : null;
            $bookingId = $groupInfo ? $groupInfo->id : '';
            $operationId = $itinerary->bus_assignment_id ?? '';
            $categoryName = $reservationCategory ? $reservationCategory->category_name : '';
            $busAssignment = $itinerary->busAssignment;
            $isCompleted = $itinerary->operation_status === '終了';
        @endphp

        <div class="itinerary-card">
            <div class="card-header-row">
                <span class="booking-operation-id">{{ $bookingId }}-{{ $operationId }}</span>
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
                    <div class="itinerary-date">{{ \Carbon\Carbon::parse($itinerary->date)->format('m月d日') }}</div>
                </div>

                <div class="itinerary-right">
                    <div class="end-time">{{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }}</div>
                    <div class="end-location">{{ $itinerary->end_location ?? '' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-list">
            <div class="detail-item">
                <div class="detail-label">開始</div>
                <div class="detail-value">{{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }}</div>
                <div class="detail-value-right">{{ $itinerary->start_location ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">終了</div>
                <div class="detail-value">{{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }}</div>
                <div class="detail-value-right">{{ $itinerary->end_location ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">行程</div>
                <div class="detail-value-full">{{ $itinerary->itinerary ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">人数</div>
                <div class="detail-value-full">
                    大: {{ $busAssignment->adult_count ?? 0 }}　
                    小: {{ $busAssignment->child_count ?? 0 }}
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">荷物数</div>
                <div class="detail-value-full">{{ $busAssignment->luggage ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">注意</div>
                <div class="detail-value-full">{{ $selectedOptionsText }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">備考</div>
                <div class="detail-value-full">{{ $busAssignment->operation_remarks ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">ステッカー</div>
                <div class="detail-value-full">{{ $busAssignment->step_car ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">お客様氏名・連絡先</div>
                <div class="detail-value-full">
                    {{ $busAssignment->representative ?? '' }} {{ $busAssignment->representative_phone ?? '' }}
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

.booking-operation-id {
    font-size: 12px;
    color: var(--text-secondary);
}

.category-name {
    font-size: 12px;
    color: var(--accent-color);
}

.divider {
    height: 1px;
    background-color: var(--border-color);
    margin-bottom: 12px;
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
</script>
@endpush