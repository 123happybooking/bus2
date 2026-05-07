@extends('admin.layouts.app')

@section('title', '新規ユーザー登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">ユーザー管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">新規登録</li>
                </ol>
            </nav>
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

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
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> 新規ユーザー登録
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST" id="userForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required">名前</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name') }}" 
                                       required maxlength="255" placeholder="例: 山田 太郎">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">255文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="login_id" class="form-label required">ログインID</label>
                                <input type="text" class="form-control @error('login_id') is-invalid @enderror" 
                                       id="login_id" name="login_id" 
                                       value="{{ old('login_id') }}" 
                                       required maxlength="255" placeholder="例: taro.yamada">
                                @error('login_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">255文字以内、他と重複不可</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label required">パスワード</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" 
                                       required minlength="8">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">8文字以上</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label required">パスワード（確認）</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                                <small class="form-text text-muted">確認のためもう一度入力してください</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_company_name" class="form-label required">会社名</label>
                                <input type="text" class="form-control @error('user_company_name') is-invalid @enderror" 
                                       id="user_company_name" name="user_company_name" 
                                       value="{{ old('user_company_name') }}" 
                                       required maxlength="255" placeholder="例: 株式会社〇〇">
                                @error('user_company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">255文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_plan" class="form-label required">プラン</label>
                                <select class="form-control @error('user_plan') is-invalid @enderror" 
                                        id="user_plan" name="user_plan" required>
                                    <option value="">選択してください</option>
                                    <option value="basic" {{ old('user_plan') == 'basic' ? 'selected' : '' }}>ベーシック</option>
                                    <option value="premium" {{ old('user_plan') == 'premium' ? 'selected' : '' }}>プレミアム</option>
                                    <option value="enterprise" {{ old('user_plan') == 'enterprise' ? 'selected' : '' }}>エンタープライズ</option>
                                </select>
                                @error('user_plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">ユーザーの契約プランを選択</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_start_day" class="form-label required">契約開始日</label>
                                <input type="date" class="form-control @error('user_start_day') is-invalid @enderror" 
                                       id="user_start_day" name="user_start_day" 
                                       value="{{ old('user_start_day') }}" required>
                                @error('user_start_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">契約開始日を選択</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 登録する
                                </button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
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

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
</style>
@endpush