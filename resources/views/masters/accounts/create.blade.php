@extends('layouts.app')

@section('title', '新規会計科目登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.accounts.index') }}">会計科目マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">新規作成</li>
                </ol>
            </nav>
            
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
                        <i class="bi bi-list-task"></i> 新規会計科目登録
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.accounts.store') }}" method="POST" id="accountForm">
                        @csrf
                        
                        <!-- 第一行：代码与名称 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label required">科目コード</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" 
                                       value="{{ old('code') }}" 
                                       required maxlength="20" placeholder="例：1001, 4001" autofocus>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required">科目名</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name') }}" 
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
                                <label for="category_id" class="form-label required">区分ID</label><span class="text-danger">*</span>
                                <select required class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" 
                                        required>
                                    <option value="" disabled {{ old('category_id') === null ? 'selected' : '' }}>選択してください</option>
                                    
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id') ? 'selected' : '' }}>
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
                                <label for="tax_id" class="form-label">税区分ID</label>
                                <select required class="form-select @error('tax_id') is-invalid @enderror" 
                                        id="tax_id" name="tax_id">
                                    <!-- 非必填项，增加一个“未設定”或“なし”的选项 -->
                                    <option value="" {{ old('tax_id') === '' ? 'selected' : '' }}>未設定 (なし)</option>
                                    
                                    @foreach($taxes as $tax)
                                        <option value="{{ $tax->id }}" 
                                                {{ old('tax_id')  ? 'selected' : '' }}>
                                            {{ $tax->name }} ({{ $tax->code }})
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
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
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
                                    <i class="bi bi-check-circle"></i> 登録する
                                </button>
                                <a href="{{ route('masters.accounts.index') }}" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            <div class="text-muted small align-self-center">
                                <span class="text-danger">*</span> は必須項目です
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