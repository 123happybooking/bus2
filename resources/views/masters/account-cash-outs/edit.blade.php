@extends('layouts.app')

@section('title', '現金出力編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-cash-outs.index') }}">現金出力マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">編集</li>
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
                        <i class="bi bi-pencil-square"></i> 現金出力編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <!-- 路由指向 update，并添加 PUT 方法 -->
                    <form action="{{ route('masters.account-cash-outs.update', $cashOut->id) }}" method="POST" id="cashOutForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- 第一行：标题、类型ID -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label required">名称</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" 
                                       value="{{ old('title', $cashOut->title) }}" 
                                       required maxlength="255" placeholder="例：交通費精算">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type_id" class="form-label">タイプID</label>
                                <select class="form-select @error('type_id') is-invalid @enderror" 
                                        id="type_id" name="type_id">
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" 
                                            {{ (old('type_id', $cashOut->type_id) == $key) ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第二行：排序 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sort" class="form-label">表示順 (Sort)</label>
                                <input type="number" class="form-control @error('sort') is-invalid @enderror" 
                                       id="sort" name="sort" 
                                       value="{{ old('sort', $cashOut->sort) }}" 
                                       min="0" placeholder="数字が小さいほど前に表示されます">
                                @error('sort')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <label for="cashin_id" class="form-cashin">入力関連</label>
                                <select class="form-select @error('cashin_id') is-invalid @enderror" 
                                        id="cashin_id" name="cashin_id">
                                    <option value="" >選択してください</option>
                                    @foreach($cashIns as $key => $cashIn)
                                        <option value="{{ $cashIn->id}}" 
                                            {{  $cashIn->id == $cashOut->cashin_id ? 'selected' : '' }}>
                                            {{ $cashIn -> title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cashin_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->
                        </div>

                        <!-- 按钮区域 -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.account-cash-outs.index') }}" class="btn btn-secondary ms-2">
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