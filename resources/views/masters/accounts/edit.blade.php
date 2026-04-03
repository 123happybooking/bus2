@extends('layouts.app')

@section('title', '会計科目編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.accounts.index') }}">会計科目マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">会計科目編集</li>
                </ol>
            </nav>
            
            <!-- 成功消息 -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- 错误消息 (Session) -->
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- 验证错误列表 -->
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <!-- 卡片表单 -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-task"></i> 会計科目編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.accounts.update', $account) }}" method="POST" id="accountForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- 科目代码 -->
                            <div class="col-md-4 mb-3">
                                <label for="code" class="form-label required">科目コード</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" 
                                       value="{{ old('code', $account->code) }}" 
                                       required maxlength="20" placeholder="例：1001, 4001">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 科目名称 -->
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label required">科目名</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name', $account->name) }}" 
                                       required maxlength="100" placeholder="例：現金売掛金、売上高">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第二行：区分ID 与 税区分ID -->
                        <div class="row">
                            <!-- 区分ID (中分類) -->
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label required">区分 (中分類)</label><span class="text-danger">*</span>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" 
                                        required>
                                    <option value="" disabled {{ old('category_id', $account->category_id ?? '') === '' ? 'selected' : '' }}>
                                        選択してください
                                    </option>
                                    
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $account->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 税区分ID -->
                            <div class="col-md-6 mb-3">
                                <label for="tax_id" class="form-label">税区分</label>
                                <select class="form-select @error('tax_id') is-invalid @enderror" 
                                        id="tax_id" name="tax_id">
                                    <!-- 非必填项，允许选择空值 -->
                                    <option value="" {{ old('tax_id', $account->tax_id ?? '') === '' ? 'selected' : '' }}>
                                        未設定 (なし)
                                    </option>
                                    
                                    @foreach($taxes as $tax)
                                        <option value="{{ $tax->id }}" 
                                                {{ old('tax_id', $account->tax_id ?? '') == $tax->id ? 'selected' : '' }}>
                                            {{ $tax->name }}({{$tax->code}})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tax_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第三行：有效状态 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_active">有効にする</label>
                                </div>
                                @error('is_active')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- 按钮区域 -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.accounts.index') }}" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.accounts.show', $account) }}" class="btn btn-info text-white">
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 可以在这里添加额外的前端逻辑，例如自动格式化代码等
    const codeInput = document.getElementById('code');
    if(codeInput) {
        codeInput.addEventListener('input', function() {
            // 简单示例：强制转为大写
            this.value = this.value.toUpperCase();
        });
    }
});
</script>
@endpush