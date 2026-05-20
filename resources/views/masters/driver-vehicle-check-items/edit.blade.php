@extends('layouts.app')

@section('title', '点検項目編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.driver-vehicle-check-items.index') }}">点検項目</a></li>
                    <li class="breadcrumb-item active" aria-current="page">編集</li>
                </ol>
            </nav>
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> 入力エラーがあります</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="card shadow-sm card-edit">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> 点検項目編集</h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.driver-vehicle-check-items.update', $checkItem) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="category" class="form-label required">カテゴリー</label>
                                <select class="form-control @error('category') is-invalid @enderror" 
                                        id="category" name="category" required>
                                    <option value="">選択してください</option>
                                    @foreach($categories as $categoryName)
                                        <option value="{{ $categoryName }}" {{ old('category', $checkItem->category) == $categoryName ? 'selected' : '' }}>
                                            {{ $categoryName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="display_order" class="form-label required">表示順</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" value="{{ old('display_order', $checkItem->display_order) }}" 
                                       required min="0">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <label for="item_name" class="form-label required">点検項目名</label>
                                <input type="text" class="form-control @error('item_name') is-invalid @enderror" 
                                       id="item_name" name="item_name" value="{{ old('item_name', $checkItem->item_name) }}" 
                                       required maxlength="200">
                                @error('item_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $checkItem->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        有効
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle"></i> 更新する
                            </button>
                            <a href="{{ route('masters.driver-vehicle-check-items.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
</style>
@endpush