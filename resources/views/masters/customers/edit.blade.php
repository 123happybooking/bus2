@extends('layouts.app')

@section('title', '顧客編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.customers.index') }}">顧客管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">顧客編集</li>
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
                        <i class="bi bi-person-gear"></i> 顧客編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.customers.update', $customer) }}" method="POST" id="customerForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_code" class="form-label required">顧客コード</label>
                                <input type="text" class="form-control @error('customer_code') is-invalid @enderror" 
                                       id="customer_code" name="customer_code" 
                                       value="{{ old('customer_code', $customer->customer_code) }}" 
                                       required maxlength="20" placeholder="例: CUST001">
                                @error('customer_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 必須、20文字以内、他と重複不可</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label required">顧客名</label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                       id="customer_name" name="customer_name" 
                                       value="{{ old('customer_name', $customer->customer_name) }}" 
                                       required maxlength="100" placeholder="例: 株式会社サンプル">
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 必須、100文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name_kana" class="form-label">顧客名（カナ）</label>
                                <input type="text" class="form-control @error('customer_name_kana') is-invalid @enderror" 
                                       id="customer_name_kana" name="customer_name_kana" 
                                       value="{{ old('customer_name_kana', $customer->customer_name_kana) }}"
                                       maxlength="100" placeholder="例: カブシキガイシャ サンプル">
                                @error('customer_name_kana')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">100文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="customer_type" class="form-label">顧客タイプ</label>
                                <input type="text" class="form-control @error('customer_type') is-invalid @enderror" 
                                       id="customer_type" name="customer_type" 
                                       value="{{ old('customer_type', $customer->customer_type) }}"
                                       maxlength="20" placeholder="例: 一般顧客、取引先">
                                @error('customer_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">20文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label">郵便番号</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" 
                                       value="{{ old('postal_code', $customer->postal_code) }}"
                                       maxlength="10" placeholder="例: 100-0001">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">10文字以内</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="phone_number" class="form-label">電話番号</label>
                                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number', $customer->phone_number) }}"
                                       maxlength="20" placeholder="例: 03-1234-5678">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">20文字以内</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="fax_number" class="form-label">FAX番号</label>
                                <input type="tel" class="form-control @error('fax_number') is-invalid @enderror" 
                                       id="fax_number" name="fax_number" 
                                       value="{{ old('fax_number', $customer->fax_number) }}"
                                       maxlength="20" placeholder="例: 03-1234-5679">
                                @error('fax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">20文字以内</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">住所</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2"
                                      maxlength="200" placeholder="例: 東京都千代田区丸の内1-2-3 東京ビル5F">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">200文字以内（都道府県・市区町村・番地を含めて入力）</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="manager_name" class="form-label">担当者名</label>
                                <input type="text" class="form-control @error('manager_name') is-invalid @enderror" 
                                       id="manager_name" name="manager_name" 
                                       value="{{ old('manager_name', $customer->manager_name) }}"
                                       maxlength="50" placeholder="例: 山田 太郎">
                                @error('manager_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">50文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">メールアドレス</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" 
                                       value="{{ old('email', $customer->email) }}"
                                       maxlength="100" placeholder="例: yamada@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">100文字以内、有効なメールアドレス形式</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="closing_day" class="form-label">締め日</label>
                                <input type="text" class="form-control @error('closing_day') is-invalid @enderror" 
                                       id="closing_day" name="closing_day" 
                                       value="{{ old('closing_day', $customer->closing_day) }}"
                                       maxlength="10" placeholder="例: 月末、15日">
                                @error('closing_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">10文字以内</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="payment_method" class="form-label">支払方法</label>
                                <input type="text" class="form-control @error('payment_method') is-invalid @enderror" 
                                       id="payment_method" name="payment_method" 
                                       value="{{ old('payment_method', $customer->payment_method) }}"
                                       maxlength="20" placeholder="例: 月末払い、現金">
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">20文字以内</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">ステータス</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        有効（この顧客を利用可能にする）
                                    </label>
                                </div>
                                <small class="form-text text-muted">チェックを外すと顧客は無効になります</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">備考</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3"
                                      maxlength="500" placeholder="例: 特記事項、取引条件など">{{ old('remarks', $customer->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">500文字以内</small>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.customers.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="if(confirm('本当にこの顧客を削除しますか？\\nこの操作は元に戻せません。')) { document.getElementById('deleteForm').submit(); }">
                                    <i class="bi bi-trash"></i> 削除
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <form id="deleteForm" action="{{ route('masters.customers.destroy', $customer) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
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