@extends('layouts.app')

@section('title', '新規現金入力登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-cash-ins.index') }}">現金入力マスター</a></li>
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
                        <i class="bi bi-cash-plus"></i> 新規現金入力登録
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.account-cash-ins.store') }}" method="POST" id="cashInForm">
                        @csrf
                        
                        <!-- 第一行：标题、模式 -->
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="mode" class="form-label required">モード</label>
                                <select class="form-select @error('mode') is-invalid @enderror"
                                        id="mode" name="mode" required>
                                    <option value="1" {{ old('mode',1) == '1' ? 'selected' : '' }}>貸借対照表</option>
                                    <option value="2" {{ old('mode',1) == '2' ? 'selected' : '' }}>損益計算書</option>
                                    <option value="3" {{ old('mode',1) == '3' ? 'selected' : '' }}>前期の利益処分計算書のうち</option>
                                </select>
                                @error('mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type_id" class="form-label">タイプID</label>
                                <select class="form-select @error('type_id') is-invalid @enderror" 
                                        id="type_id" name="type_id">
                                    <option value="">選択してください</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" {{ old('type_id', $cashIn->type_id ?? null) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第二行：类型ID、排序 -->
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label required">名称</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" 
                                       value="{{ old('title') }}" 
                                       required maxlength="255" placeholder="例：売掛金入金" autofocus>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort" class="form-label">表示順 (Sort)</label>
                                <input type="number" class="form-control @error('sort') is-invalid @enderror" 
                                       id="sort" name="sort" 
                                       value="{{ old('sort', 0) }}" 
                                       min="0" placeholder="数字が小さいほど前に表示されます">
                                @error('sort')
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
                                <a href="{{ route('masters.account-cash-ins.index') }}" class="btn btn-secondary ms-2">
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