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
        <div class="itinerary-card">
            <div class="itinerary-row">
                <div class="itinerary-left">
                    <div class="start-time">{{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }}</div>
                    <div class="start-location">{{ $itinerary->start_location ?? '未設定' }}</div>
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
                    <div class="end-location">{{ $itinerary->end_location ?? '未設定' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-list">
            <div class="detail-item">
                <div class="detail-label">開始時間</div>
                <div class="detail-value">{{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">開始場所</div>
                <div class="detail-value">{{ $itinerary->start_location ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">終了時間</div>
                <div class="detail-value">{{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">終了場所</div>
                <div class="detail-value">{{ $itinerary->end_location ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">車両名</div>
                <div class="detail-value">{{ $itinerary->vehicle ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">日付</div>
                <div class="detail-value">{{ \Carbon\Carbon::parse($itinerary->date)->format('Y年m月d日') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">行程</div>
                <div class="detail-value">{{ $itinerary->busAssignment->groupInfo->itinerary_name ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">団体名</div>
                <div class="detail-value">{{ $itinerary->busAssignment->groupInfo->group_name ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">運転手</div>
                <div class="detail-value">{{ $itinerary->driver ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">ガイド</div>
                <div class="detail-value">{{ $itinerary->guide ?? '未設定' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">備考</div>
                <div class="detail-value">{{ $itinerary->remarks ?? 'なし' }}</div>
            </div>
        </div>

        <div class="button-container">
            <button class="back-btn" id="backToDashboard">戻る</button>
        </div>
    </div>
</div>

<style>
.itinerary-detail-section {
    padding: 12px;
}

.itinerary-card {
    padding: 16px;
    background-color: var(--card-bg);
    border-radius: 16px;
    margin-bottom: 16px;
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
    color: var(--accent-color);
}

.arrow-container {
    width: 100%;
    display: flex;
    align-items: center;
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
    font-size: 12px;
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
    width: 100px;
    font-size: 14px;
    color: var(--text-secondary);
    flex-shrink: 0;
}

.detail-value {
    flex: 1;
    font-size: 14px;
    color: var(--text-primary);
    word-break: break-word;
}

.button-container {
    display: flex;
    justify-content: center;
}

.back-btn {
    width: 100%;
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

<script>
const backBtn = document.getElementById('backBtn');
const backToDashboard = document.getElementById('backToDashboard');

if (backBtn) {
    backBtn.addEventListener('click', function() {
        window.location.href = '/driver/dashboard';
    });
}

if (backToDashboard) {
    backToDashboard.addEventListener('click', function() {
        window.location.href = '/driver/dashboard';
    });
}
</script>
@endsection