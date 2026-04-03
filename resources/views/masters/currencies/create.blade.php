@extends('layouts.app')

@section('title', '新規通貨登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.currencies.index') }}">通貨・為替レートマスター</a></li>
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
                        <i class="bi bi-currency-exchange"></i> 新規通貨登録
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.currencies.store') }}" method="POST" id="currencyForm">
                        @csrf
                        
                        <!-- 第一行：代码与名称 -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="currency_code" class="form-label required">通貨コード</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('currency_code') is-invalid @enderror" 
                                       id="currency_code" name="currency_code" 
                                       value="{{ old('currency_code') }}" 
                                       required maxlength="10" placeholder="例：USD, JPY, EUR" autofocus>
                                @error('currency_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-8 mb-3">
                                <label for="currency_name" class="form-label required">通貨名称</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('currency_name') is-invalid @enderror" 
                                       id="currency_name" name="currency_name" 
                                       value="{{ old('currency_name') }}" 
                                       required maxlength="50" placeholder="例：米ドル、日本円、ユーロ">
                                @error('currency_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第二行：符号与小数位数 -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="symbol" class="form-label required">通貨記号</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('symbol') is-invalid @enderror" 
                                       id="symbol" name="symbol" 
                                       value="{{ old('symbol') }}" 
                                       required maxlength="10" placeholder="例：$, ¥, €, ₩">
                                @error('symbol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="decimal_digits" class="form-label required">小数桁数</label><span class="text-danger">*</span>
                                <select class="form-select @error('decimal_digits') is-invalid @enderror" 
                                        id="decimal_digits" name="decimal_digits" required>
                                    <option value="0" {{ old('decimal_digits') == '0' ? 'selected' : '' }}>0</option>
                                    <option value="1" {{ old('decimal_digits') == '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ old('decimal_digits') == '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ old('decimal_digits') == '3' ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ old('decimal_digits') == '4' ? 'selected' : '' }}>4</option>
                                </select>
                                @error('decimal_digits')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="sort" class="form-label required">ソート順</label><span class="text-danger">*</span>
                                <input type="number" class="form-control @error('sort') is-invalid @enderror" 
                                       id="sort" name="sort" 
                                       value="{{ old('sort', 1) }}"
                                       min="0" max="999" step="1">
                                @error('sort')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第三行：汇率 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rate_to_jpy" class="form-label required">対円レート (1 単位あたりの日本円)</label><span class="text-danger">*</span>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('rate_to_jpy') is-invalid @enderror" 
                                           id="rate_to_jpy" name="rate_to_jpy" 
                                           value="{{ old('rate_to_jpy') }}" 
                                           required step="0.000001" min="0.000001" placeholder="例：1.500000">
                                    <span class="input-group-text">JPY</span>
                                </div>
                                @error('rate_to_jpy')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第四行：有效期 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rate_valid_from" class="form-label required">適用開始日</label><span class="text-danger">*</span>
                                <input type="date" class="form-control @error('rate_valid_from') is-invalid @enderror" 
                                       id="rate_valid_from" name="rate_valid_from" 
                                       value="{{ old('rate_valid_from', date('Y-m-d')) }}" 
                                       required>
                                @error('rate_valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="rate_valid_to" class="form-label">適用終了日</label><span class="text-danger">*</span>
                                <input type="date" class="form-control @error('rate_valid_to') is-invalid @enderror" 
                                       id="rate_valid_to" name="rate_valid_to" 
                                       value="{{ old('rate_valid_to') }}" 
                                       placeholder="未設定の場合は無期限">
                                <div class="form-text">空白または未設定の場合、このレートは無期限に有効となります。</div>
                                @error('rate_valid_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- 按钮区域 -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 登録する
                                </button>
                                <a href="{{ route('masters.currencies.index') }}" class="btn btn-secondary ms-2">
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
    // 简单的验证：如果结束日期早于开始日期，前端提示（后端也会验证）
    const fromDate = document.getElementById('rate_valid_from');
    const toDate = document.getElementById('rate_valid_to');

    fromDate.addEventListener('change', function() {
        if (toDate.value && toDate.value < this.value) {
            alert('適用終了日は適用開始日以降の日付である必要があります。');
            toDate.value = '';
            toDate.focus();
        }
        // 设置结束日期的最小值为开始日期
        toDate.min = this.value;
    });
});
</script>
@endpush