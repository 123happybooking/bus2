@extends('layouts.app')

@section('title', '新規税区分登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-taxs.index') }}">税区分マスター</a></li>
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
                        <i class="bi bi-percent"></i> 新規税区分登録
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.account-taxs.store') }}" method="POST" id="taxForm">
                        @csrf
                        
                        <!-- 第一行：Code 与 名称 -->
                        <div class="row">
                            <!-- Code (唯一标识) -->
                            <div class="col-md-4 mb-3">
                                <label for="code" class="form-label required">税区分コード</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" 
                                       value="{{ old('code') }}" 
                                       required maxlength="10" placeholder="例：S10, P8, PX" autofocus>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 名称 -->
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label required">税区分名称</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name') }}" 
                                       required maxlength="100" placeholder="例：課税売上 10%, 不課税 (対象外)">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第二行：税率 与 税計算 -->
                        <div class="row">
                            <!-- 税率 -->
                            <div class="col-md-4 mb-3">
                                <label for="rate" class="form-label required">税率 (%)</label><span class="text-danger">*</span>
                                <select class="form-select @error('rate') is-invalid @enderror" 
                                        id="rate" name="rate" 
                                        required>
                                    <!-- 固定值：10% -->
                                    <option value="10" {{ old('rate') == '10' ? 'selected' : '' }}>10%</option>
                                    
                                    <!-- 固定值：8% -->
                                    <option value="8" {{ old('rate') == '8' ? 'selected' : '' }}>8%</option>
                                    
                                    <!-- 固定值：0% -->
                                    <option value="0" {{ old('rate') == '0' ? 'selected' : '' }}>0%</option>
                                </select>
                                @error('rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 税計算 (外税/内税/不課税) -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label required">税計算方法</label><span class="text-danger">*</span>
                                <div class="d-flex gap-3 mt-2 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input @error('calculation_type') is-invalid @enderror" 
                                               type="radio" name="calculation_type" id="type_extax" value="外税" 
                                               {{ old('calculation_type', '外税') === '外税' ? 'checked' : '' }} required>
                                        <label class="form-check-label fw-bold text-primary" for="type_extax">
                                            外税
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('calculation_type') is-invalid @enderror" 
                                               type="radio" name="calculation_type" id="type_intax" value="内税" 
                                               {{ old('calculation_type') === '内税' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-success" for="type_intax">
                                            内税
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('calculation_type') is-invalid @enderror" 
                                               type="radio" name="calculation_type" id="type_non" value="不課税" 
                                               {{ old('calculation_type') === '不課税' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-secondary" for="type_non">
                                            不課税
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('calculation_type') is-invalid @enderror" 
                                               type="radio" name="calculation_type" id="type_non" value="非課税" 
                                               {{ old('calculation_type') === '非課税' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-secondary" for="type_non">
                                            非課税
                                        </label>
                                    </div>
                                </div>
                                @error('calculation_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第三行：インボイス (適格/非対象) -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label required">インボイス制度対応</label><span class="text-danger">*</span>
                                <div class="card bg-light border-0 p-3">
                                    <div class="d-flex gap-4">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input @error('is_invoice_eligible') is-invalid @enderror" 
                                                   type="radio" name="is_invoice_eligible" id="invoice_yes" value="1" 
                                                   {{ old('is_invoice_eligible', '1') == '1' ? 'checked' : '' }} required>
                                            <label class="form-check-label fw-bold text-success" for="invoice_yes">
                                                <i class="bi bi-patch-check-fill me-1"></i> 適格
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input @error('is_invoice_eligible') is-invalid @enderror" 
                                                   type="radio" name="is_invoice_eligible" id="invoice_no" value="2" 
                                                   {{ old('is_invoice_eligible') === '2' ? 'checked' : '' }}>
                                            <label class="form-check-label text-danger" for="invoice_no">
                                                <i class="bi bi-slash-circle me-1"></i> 非対象
                                            </label>
                                        </div>
                                    </div>
                                    @error('is_invoice_eligible')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        

                        <!-- 按钮区域 -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <div>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-circle"></i> 登録する
                                </button>
                                <a href="{{ route('masters.account-taxs.index') }}" class="btn btn-secondary ms-2">
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

@endpush