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

    <div class="coming-soon">
        <div class="coming-soon-icon">📋</div>
        <div class="coming-soon-text">準備中</div>
        <div class="coming-soon-hint">この機能は現在開発中です</div>
    </div>
</div>

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

.coming-soon {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
}

.coming-soon-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.coming-soon-text {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.coming-soon-hint {
    font-size: 14px;
    color: var(--text-secondary);
}
</style>

<script>
document.getElementById('backBtn').addEventListener('click', function() {
    const date = '{{ $date }}';
    window.location.href = `/driver/daily-itineraries/${date}`;
});
</script>
@endsection