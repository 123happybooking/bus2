@extends('layouts.app')

@section('title', '代理店編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.agencies.index') }}">代理店管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">代理店編集</li>
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
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building-gear"></i> 代理店編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.agencies.update', $agency) }}" method="POST" id="agencyForm">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="agency_code" class="form-label required">代理店コード</label>
                                <input type="text" class="form-control @error('agency_code') is-invalid @enderror" 
                                       id="agency_code" name="agency_code" 
                                       value="{{ old('agency_code', $agency->agency_code) }}" 
                                       required maxlength="50" placeholder="例: AG001">
                                @error('agency_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 必須、50文字以内、他と重複不可</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="agency_name" class="form-label required">代理店名</label>
                                <input type="text" class="form-control @error('agency_name') is-invalid @enderror" 
                                       id="agency_name" name="agency_name" 
                                       value="{{ old('agency_name', $agency->agency_name) }}" 
                                       required maxlength="100" placeholder="例: 株式会社〇〇">
                                @error('agency_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 必須、100文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="branch_name" class="form-label">支店名</label>
                                <input type="text" class="form-control @error('branch_name') is-invalid @enderror" 
                                       id="branch_name" name="branch_name" 
                                       value="{{ old('branch_name', $agency->branch_name) }}"
                                       maxlength="100" placeholder="例: 東京支店">
                                @error('branch_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">100文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">種類</label>
                                <input type="text" class="form-control @error('type') is-invalid @enderror" 
                                       id="type" name="type" 
                                       value="{{ old('type', $agency->type) }}"
                                       maxlength="50" placeholder="例: 一般代理店">
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">50文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">国</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" 
                                       value="{{ old('country', $agency->country) }}"
                                       maxlength="50" placeholder="例: 日本">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">50文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">表示順</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" 
                                       value="{{ old('display_order', $agency->display_order) }}"
                                       min="0" max="999" step="1">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">0-999、数字が小さいほど上位に表示</small>
                            </div>
                        </div>
                        
                        <h6 class="border-bottom pb-2 mb-3 mt-4">連絡先情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">郵便番号</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" 
                                       value="{{ old('postal_code', $agency->postal_code) }}"
                                       maxlength="10" placeholder="例: 100-0001">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">10文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label">電話番号</label>
                                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number', $agency->phone_number) }}"
                                       maxlength="20" placeholder="例: 03-1234-5678">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">20文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fax_number" class="form-label">FAX番号</label>
                                <input type="tel" class="form-control @error('fax_number') is-invalid @enderror" 
                                       id="fax_number" name="fax_number" 
                                       value="{{ old('fax_number', $agency->fax_number) }}"
                                       maxlength="20" placeholder="例: 03-1234-5679">
                                @error('fax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">20文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">メールアドレス</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" 
                                       value="{{ old('email', $agency->email) }}"
                                       maxlength="100" placeholder="例: info@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">100文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="manager_name" class="form-label">責任者名</label>
                                <input type="text" class="form-control @error('manager_name') is-invalid @enderror" 
                                       id="manager_name" name="manager_name" 
                                       value="{{ old('manager_name', $agency->manager_name) }}"
                                       maxlength="50" placeholder="例: 山田 太郎">
                                @error('manager_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">50文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4 pt-2">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                           type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $agency->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        取引状態（チェックで取引中）
                                    </label>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted d-block">
                                        ※ チェックを外すと停止状態になります
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">住所</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2"
                                      maxlength="255" placeholder="例: 東京都千代田区丸の内1-2-3 東京ビル5F">{{ old('address', $agency->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">255文字以内（都道府県・市区町村・番地を含めて入力）</small>
                        </div>
                        
                        <h6 class="border-bottom pb-2 mb-3 mt-4">契約情報</h6>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="commission_rate" class="form-label">手数料率 (%)</label>
                                <input type="number" step="0.01" min="0" max="100" 
                                       class="form-control @error('commission_rate') is-invalid @enderror" 
                                       id="commission_rate" name="commission_rate" 
                                       value="{{ old('commission_rate', $agency->commission_rate) }}">
                                @error('commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">0〜100、小数点2桁まで</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="closing_day" class="form-label">締日</label>
                                <input type="number" min="1" max="31" 
                                       class="form-control @error('closing_day') is-invalid @enderror" 
                                       id="closing_day" name="closing_day" 
                                       value="{{ old('closing_day', $agency->closing_day) }}">
                                @error('closing_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">1〜31</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="payment_day" class="form-label">支払日</label>
                                <input type="number" min="0" 
                                       class="form-control @error('payment_day') is-invalid @enderror" 
                                       id="payment_day" name="payment_day" 
                                       value="{{ old('payment_day', $agency->payment_day) }}">
                                @error('payment_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">締日からの日数、0以上の整数</small>
                            </div>
                        </div>
                        
                        <h6 class="border-bottom pb-2 mb-3 mt-4">その他情報</h6>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">備考</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3"
                                      maxlength="500" placeholder="特記事項があれば入力">{{ old('remarks', $agency->remarks) }}</textarea>
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
                                <a href="{{ route('masters.agencies.show', $agency) }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.agencies.show', $agency) }}" class="btn btn-info">
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

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
.card.border-danger {
    border-width: 2px;
}
</style>
@endpush