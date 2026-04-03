@extends('layouts.app')

@section('title', '新規勤怠分類登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.attendance-categories.index') }}">勤怠分類マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">新規登録</li>
                </ol>
            </nav>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">
                        <i class="bi bi-exclamation-triangle"></i> 入力エラーがあります
                    </h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-plus"></i> 新規勤怠分類登録
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.attendance-categories.store') }}" method="POST" id="categoryForm">
                        @csrf
                        
                        <h6 class="mb-3 border-bottom pb-2">基本情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="attendance_code" class="form-label required">勤怠コード</label>
                                <input type="text" class="form-control @error('attendance_code') is-invalid @enderror" id="attendance_code" name="attendance_code" value="{{ old('attendance_code') }}" required maxlength="20" placeholder="例: ATT001">
                                @error('attendance_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内、他と重複不可</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="attendance_name" class="form-label required">勤怠名</label>
                                <input type="text" class="form-control @error('attendance_name') is-invalid @enderror" id="attendance_name" name="attendance_name" value="{{ old('attendance_name') }}" required maxlength="100" placeholder="例: 出勤">
                                @error('attendance_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 100文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="color_code" class="form-label required">カラーコード</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color @error('color_code') is-invalid @enderror" id="color_picker" value="#007bff" title="カラーを選択">
                                    <input type="text" class="form-control @error('color_code') is-invalid @enderror" id="color_code" name="color_code" value="{{ old('color_code', '#007bff') }}" required maxlength="7" placeholder="#007bff">
                                    @error('color_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">※ HEXカラーコード (#RRGGBB)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">表示順</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" name="display_order" value="{{ old('display_order') }}" min="0" placeholder="例: 10">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 数値を入力（未設定の場合は自動設定）</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4 pt-2">
                                    <input class="form-check-input @error('is_work_day') is-invalid @enderror" type="checkbox" id="is_work_day" name="is_work_day" value="1" {{ old('is_work_day', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_work_day">
                                        勤務日として扱う
                                    </label>
                                    @error('is_work_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted d-block">
                                        ※ チェックするとこの勤怠分類は勤務日とみなされます
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 登録する
                                </button>
                                <a href="{{ route('masters.attendance-categories.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.getElementById('color_picker');
    const colorCode = document.getElementById('color_code');
    
    colorPicker.addEventListener('input', function() {
        colorCode.value = this.value;
    });
    
    colorCode.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-F]{6}$/i)) {
            colorPicker.value = this.value;
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
.form-control-color {
    width: 3rem;
    height: calc(2.25rem + 2px);
    padding: 0.375rem;
}
</style>
@endpush
@endsection