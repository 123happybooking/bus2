@extends('layouts.app')

@section('title', '予約分類編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.reservation-categories.index') }}">予約分類マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">予約分類編集</li>
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
                        <i class="bi bi-tag-gear"></i> 予約分類編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.reservation-categories.update', $reservationCategory) }}" method="POST" id="categoryForm">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="mb-3 border-bottom pb-2">基本情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_code" class="form-label required">カテゴリコード</label>
                                <input type="text" class="form-control @error('category_code') is-invalid @enderror" 
                                       id="category_code" name="category_code" 
                                       value="{{ old('category_code', $reservationCategory->category_code) }}" 
                                       required maxlength="20" placeholder="例: CAT001">
                                @error('category_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内、他と重複不可</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category_name" class="form-label required">分類名</label>
                                <input type="text" class="form-control @error('category_name') is-invalid @enderror" 
                                       id="category_name" name="category_name" 
                                       value="{{ old('category_name', $reservationCategory->category_name) }}" 
                                       required maxlength="100" placeholder="例: 一般予約">
                                @error('category_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 100文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="color_code" class="form-label required">カラーコード</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color @error('color_code') is-invalid @enderror" 
                                           id="color_picker" value="{{ old('color_code', $reservationCategory->color_code) }}" title="カラーを選択">
                                    <input type="text" class="form-control @error('color_code') is-invalid @enderror" 
                                           id="color_code" name="color_code" 
                                           value="{{ old('color_code', $reservationCategory->color_code) }}" 
                                           required maxlength="7" placeholder="#007bff">
                                    @error('color_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">※ HEXカラーコード (#RRGGBB)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">表示順</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" 
                                       value="{{ old('display_order', $reservationCategory->display_order) }}" 
                                       min="0" placeholder="例: 10">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 数値を入力</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4 pt-2">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                           type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $reservationCategory->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        有効状態
                                    </label>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted d-block">
                                        ※ チェックを外すとこのカテゴリは使用できなくなります
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.reservation-categories.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.reservation-categories.show', $reservationCategory) }}" class="btn btn-info">
                                    <i class="bi bi-eye"></i> 詳細を見る
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