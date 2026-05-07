@extends('layouts.app')

@section('title', '新規代理店登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.agencies.index') }}">代理店管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">新規登録</li>
                </ol>
            </nav>
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
            </div>
            @endif
            
            <div class="card shadow-sm card-edit">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building-add"></i> 新規代理店登録
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.agencies.store') }}" method="POST" id="agencyForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="agency_code" class="form-label required">代理店コード</label>
                                <input type="text" class="form-control @error('agency_code') is-invalid @enderror" 
                                       id="agency_code" name="agency_code" 
                                       value="{{ old('agency_code') }}" 
                                       required maxlength="50" placeholder="例: AG001">
                                @error('agency_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="agency_name" class="form-label required">代理店名</label>
                                <input type="text" class="form-control @error('agency_name') is-invalid @enderror" 
                                       id="agency_name" name="agency_name" 
                                       value="{{ old('agency_name') }}" 
                                       required maxlength="100" placeholder="例: 株式会社〇〇">
                                @error('agency_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="branch_name" class="form-label">支店名</label>
                                <input type="text" class="form-control @error('branch_name') is-invalid @enderror" 
                                       id="branch_name" name="branch_name" 
                                       value="{{ old('branch_name') }}"
                                       maxlength="100" placeholder="例: 東京支店">
                                @error('branch_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="type" class="form-label">種類</label>
                                <input type="text" class="form-control @error('type') is-invalid @enderror" 
                                       id="type" name="type" 
                                       value="{{ old('type') }}"
                                       maxlength="50" placeholder="例: 一般代理店">
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="country" class="form-label">国</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" 
                                       value="{{ old('country') }}"
                                       maxlength="50" placeholder="例: 日本">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="display_order" class="form-label">表示順</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" 
                                       value="{{ old('display_order', 0) }}"
                                       min="0" max="999" step="1">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">郵便番号</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" 
                                       value="{{ old('postal_code') }}"
                                       maxlength="10" placeholder="例: 100-0001">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">電話番号</label>
                                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number') }}"
                                       maxlength="20" placeholder="例: 03-1234-5678">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="fax_number" class="form-label">FAX番号</label>
                                <input type="tel" class="form-control @error('fax_number') is-invalid @enderror" 
                                       id="fax_number" name="fax_number" 
                                       value="{{ old('fax_number') }}"
                                       maxlength="20" placeholder="例: 03-1234-5679">
                                @error('fax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">メールアドレス</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" 
                                       value="{{ old('email') }}"
                                       maxlength="100" placeholder="例: info@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="manager_name" class="form-label">責任者名</label>
                                <input type="text" class="form-control @error('manager_name') is-invalid @enderror" 
                                       id="manager_name" name="manager_name" 
                                       value="{{ old('manager_name') }}"
                                       maxlength="50" placeholder="例: 山田 太郎">
                                @error('manager_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check mt-4 pt-2">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                           type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        取引状態（チェックで取引中）
                                    </label>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">住所</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2"
                                      maxlength="255" placeholder="例: 東京都千代田区丸の内1-2-3 東京ビル5F">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <label for="commission_rate" class="form-label">手数料率 (%)</label>
                                <input type="number" step="0.01" min="0" max="100" 
                                       class="form-control @error('commission_rate') is-invalid @enderror" 
                                       id="commission_rate" name="commission_rate" 
                                       value="{{ old('commission_rate') }}">
                                @error('commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="closing_day" class="form-label">締日</label>
                                <input type="number" min="1" max="31" 
                                       class="form-control @error('closing_day') is-invalid @enderror" 
                                       id="closing_day" name="closing_day" 
                                       value="{{ old('closing_day') }}">
                                @error('closing_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="payment_day" class="form-label">支払日</label>
                                <input type="number" min="0" 
                                       class="form-control @error('payment_day') is-invalid @enderror" 
                                       id="payment_day" name="payment_day" 
                                       value="{{ old('payment_day') }}">
                                @error('payment_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">備考</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3"
                                      maxlength="500" placeholder="特記事項があれば入力">{{ old('remarks') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 登録する
                                </button>
                                <a href="{{ route('masters.agencies.index') }}" class="btn btn-secondary">
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