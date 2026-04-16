@extends('layouts.driver')

@section('title', '設定')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">設定</div>
        <div class="header-right">
            <div style="width: 32px;"></div>
        </div>
    </div>

    <div class="settings-section">
        <div class="settings-item">
            <div class="settings-label">テーマ</div>
            <div class="theme-options">
                <button class="theme-btn" data-theme="dark">
                    🌙 ダークモード
                </button>
                <button class="theme-btn" data-theme="light">
                    ☀️ ライトモード
                </button>
            </div>
        </div>
    </div>

    <div class="save-container">
        <button class="save-btn" id="saveBtn">保存</button>
    </div>
</div>
@endsection

@push('styles')
<style>
.settings-section {
    background-color: var(--card-bg);
    margin: 12px;
    border-radius: 16px;
    overflow: hidden;
}

.settings-item {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
}

.settings-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 12px;
}

.theme-options {
    display: flex;
    gap: 12px;
}

.theme-btn {
    flex: 1;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    background-color: var(--card-bg);
    color: var(--text-primary);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.theme-btn.active {
    background-color: var(--accent-color);
    color: var(--accent-text);
    border-color: var(--accent-color);
}

.save-container {
    padding: 12px;
    margin: 12px;
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
</style>
@endpush

@push('scripts')
<script>
const backBtn = document.getElementById('backBtn');
if (backBtn) {
    backBtn.addEventListener('click', function() {
        window.location.href = '/driver/dashboard';
    });
}

const savedTheme = localStorage.getItem('driver_theme') || 'dark';
document.querySelectorAll('.theme-btn').forEach(btn => {
    if (btn.getAttribute('data-theme') === savedTheme) {
        btn.classList.add('active');
    }
    btn.addEventListener('click', function() {
        document.querySelectorAll('.theme-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});

const saveBtn = document.getElementById('saveBtn');
if (saveBtn) {
    saveBtn.addEventListener('click', function() {
        const activeBtn = document.querySelector('.theme-btn.active');
        const selectedTheme = activeBtn.getAttribute('data-theme');
        localStorage.setItem('driver_theme', selectedTheme);
        
        if (selectedTheme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
        
        alert('テーマを保存しました');
        
        setTimeout(() => {
            window.location.href = '/driver/dashboard';
        }, 500);
    });
}
</script>
@endpush