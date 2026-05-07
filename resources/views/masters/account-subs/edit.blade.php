@extends('layouts.app')

@section('title', '補助科目編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-subs.index') }}">補助科目マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">補助科目編集</li>
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
                        <i class="bi bi-collection"></i> 補助科目編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.account-subs.update', $sub) }}" method="POST" id="accountSubForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- 所属勘定科目 (核心字段) -->
                            <div class="col-md-8 mb-3">
                                <label for="account_id" class="form-label required">勘定科目 (親)</label><span class="text-danger">*</span>
                                <select class="form-select @error('account_id') is-invalid @enderror" 
                                        id="account_id" name="account_id" required autofocus>
                                    <option value="">選択してください</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}" 
                                            {{ old('account_id', $sub->account_id) == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->code }} - {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 状态开关 -->
                            <div class="col-md-4 mb-3">
                                <label for="is_active" class="form-label">状態</label>
                                <div class="mt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $sub->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_active">有効にする</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 補助科目名 -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label required">補助科目名</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name', $sub->name) }}" 
                                       required maxlength="100" placeholder="例：国内出張、交通費、消耗品費">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 按钮区域 -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.account-subs.index') }}" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.account-subs.show', $sub) }}" class="btn btn-info text-white">
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
@endpush