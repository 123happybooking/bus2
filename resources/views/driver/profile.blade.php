@extends('layouts.driver')

@section('title', '個人情報変更')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">個人情報変更</div>
        <div class="header-right">
            <div style="width: 32px;"></div>
        </div>
    </div>

    @if(session('success'))
    <div class="success-message">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="error-message">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <div class="form-container">
        <form method="POST" action="{{ route('driver.update-profile') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">氏名</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $driver->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">氏名（カナ）</label>
                <input type="text" name="name_kana" class="form-input" value="{{ old('name_kana', $driver->name_kana) }}">
            </div>

            <div class="form-group">
                <label class="form-label">電話番号</label>
                <input type="text" name="phone_number" class="form-input" value="{{ old('phone_number', $driver->phone_number) }}">
            </div>

            <div class="form-group">
                <label class="form-label">メールアドレス</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $driver->email) }}">
            </div>

            <button type="submit" class="save-btn">保存</button>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-container {
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
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 14px;
    background-color: var(--card-bg);
    color: var(--text-primary);
    outline: none;
}

.form-input:focus {
    border-color: var(--accent-color);
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
    margin-top: 20px;
}

.success-message {
    background-color: #10b981;
    color: white;
    padding: 12px 16px;
    margin: 12px;
    border-radius: 12px;
    font-size: 14px;
}

.error-message {
    background-color: #ef4444;
    color: white;
    padding: 12px 16px;
    margin: 12px;
    border-radius: 12px;
    font-size: 14px;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = '/driver/dashboard';
});
</script>
@endpush