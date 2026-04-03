@extends('layouts.app')

@section('title', '取引先情報編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account_partners.index') }}">取引先マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">取引先情報編集</li>
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
                        <i class="bi bi-people"></i> 取引先情報編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.account_partners.update', $partner) }}" method="POST" id="partnerForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- 取引先名 -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required">取引先名</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name', $partner->name) }}" 
                                       required maxlength="100" placeholder="例：ヤマダ商店、株式会社サンプル">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 分類 -->
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">分類</label>
                                <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                       id="category" name="category" 
                                       value="{{ old('category', $partner->category) }}" 
                                       maxlength="50" placeholder="例：仕入先、顧客、その他">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第二行：会社名、登録番号 -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="company_name" class="form-label">会社名</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                       id="company_name" name="company_name" 
                                       value="{{ old('company_name', $partner->company_name) }}" 
                                       maxlength="100" placeholder="例：株式会社ヤマダ">
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="registration_number" class="form-label">登録番号</label>
                                <input type="text" class="form-control @error('registration_number') is-invalid @enderror" 
                                       id="registration_number" name="registration_number" 
                                       value="{{ old('registration_number', $partner->registration_number) }}" 
                                       maxlength="20" placeholder="例：T1234567890123">
                                @error('registration_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第三行：住所 (全宽) -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">住所</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" 
                                          rows="3" 
                                          placeholder="例：東京都渋谷区...">{{ old('address', $partner->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第四行：電話、責任者 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">電話</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" 
                                       value="{{ old('phone', $partner->phone) }}" 
                                       maxlength="20" placeholder="例：03-1234-5678">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="person_in_charge" class="form-label">責任者</label>
                                <input type="text" class="form-control @error('person_in_charge') is-invalid @enderror" 
                                       id="person_in_charge" name="person_in_charge" 
                                       value="{{ old('person_in_charge', $partner->person_in_charge) }}" 
                                       maxlength="50" placeholder="例：山田 太郎">
                                @error('person_in_charge')
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
                                <a href="{{ route('masters.account_partners.index') }}" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.account_partners.show', $partner) }}" class="btn btn-info text-white">
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
    // 这里可以添加特定的前端交互，例如地址自动补全等
    // 目前保持与 Currency 编辑页面一致的简洁结构
    console.log('Edit Partner Form Loaded for ID: {{ $partner->id }}');
});
</script>
@endpush