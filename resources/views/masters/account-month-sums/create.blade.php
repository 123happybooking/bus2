@extends('layouts.app')

@section('title', '月度汇总 - 新規作成')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-month-sums.index') }}">月度汇总管理</a></li>
                    <a href="{{ route('masters.account-month-sums.create') }}" class="btn btn-primary">更新</a>
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
                        <i class="bi bi-calendar-plus"></i> 新規月度汇总
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.account-month-sums.store') }}" method="POST" id="monthForm">
                        @csrf
                        
                        <div class="row">
                            <!-- 字段 1：年份 -->
                            <div class="col-md-6 mb-3">
                                <label for="year" class="form-label required">年份</label>
                                <!-- 使用 type="number" 限制只能输入数字，默认值设为当前年份 -->
                                <input type="number" 
                                       class="form-control @error('year') is-invalid @enderror" 
                                       id="year" 
                                       name="year" 
                                       value="{{ old('year', date('Y')) }}" 
                                       required 
                                       min="1900" 
                                       max="2100" 
                                       placeholder="例：2026"
                                       autofocus>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 字段 2：月份 -->
                            <div class="col-md-6 mb-3">
                                <label for="month" class="form-label required">月份</label>
                                <!-- 使用 type="number" 限制输入，默认值设为当前月份 -->
                                <input type="number" 
                                       class="form-control @error('month') is-invalid @enderror" 
                                       id="month" 
                                       name="month" 
                                       value="{{ old('month', date('m')) }}" 
                                       required 
                                       min="1" 
                                       max="12" 
                                       placeholder="例：4"
                                       step="1">
                                @error('month')
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
                                <a href="{{ route('masters.account-month-sums.index') }}" class="btn btn-secondary ms-2">
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

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
</style>
@endpush