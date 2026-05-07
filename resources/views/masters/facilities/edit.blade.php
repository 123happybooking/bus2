@extends('layouts.app')

@section('title', '施設編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.facilities.index') }}">施設管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">施設編集</li>
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
                        <i class="bi bi-building-gear"></i> 施設編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.facilities.update', $facility) }}" method="POST" id="facilityForm">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="mb-3 border-bottom pb-2">基本情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="facility_code" class="form-label required">施設コード</label>
                                <input type="text" class="form-control @error('facility_code') is-invalid @enderror" 
                                       id="facility_code" name="facility_code" 
                                       value="{{ old('facility_code', $facility->facility_code) }}" 
                                       required maxlength="20" placeholder="例: FAC001">
                                @error('facility_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内、他と重複不可</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">カテゴリ</label>
                                <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                       id="category" name="category" 
                                       value="{{ old('category', $facility->category) }}" 
                                       maxlength="50" placeholder="例: 公共施設">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 50文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="facility_name" class="form-label required">施設名</label>
                                <input type="text" class="form-control @error('facility_name') is-invalid @enderror" 
                                       id="facility_name" name="facility_name" 
                                       value="{{ old('facility_name', $facility->facility_name) }}" 
                                       required maxlength="100" placeholder="例: 東京文化会館">
                                @error('facility_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 100文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="facility_kana" class="form-label">施設名（カナ）</label>
                                <input type="text" class="form-control @error('facility_kana') is-invalid @enderror" 
                                       id="facility_kana" name="facility_kana" 
                                       value="{{ old('facility_kana', $facility->facility_kana) }}" 
                                       maxlength="100" placeholder="例: トウキョウブンカカイカン">
                                @error('facility_kana')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 100文字以内</small>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 border-bottom pb-2 mt-4">連絡先情報</h6>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label">郵便番号</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" 
                                       value="{{ old('postal_code', $facility->postal_code) }}" 
                                       maxlength="10" placeholder="例: 100-0001">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 10文字以内</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="phone_number" class="form-label">電話番号</label>
                                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number', $facility->phone_number) }}" 
                                       maxlength="20" placeholder="例: 03-1234-5678">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="fax_number" class="form-label">FAX番号</label>
                                <input type="tel" class="form-control @error('fax_number') is-invalid @enderror" 
                                       id="fax_number" name="fax_number" 
                                       value="{{ old('fax_number', $facility->fax_number) }}" 
                                       maxlength="20" placeholder="例: 03-1234-5679">
                                @error('fax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">住所</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2" maxlength="200" 
                                      placeholder="例: 東京都千代田区丸の内1-2-3">{{ old('address', $facility->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">※ 200文字以内</small>
                        </div>
                        
                        <h6 class="mb-3 border-bottom pb-2 mt-4">駐車場情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-2">
                                    <input class="form-check-input @error('bus_parking_available') is-invalid @enderror" 
                                           type="checkbox" id="bus_parking_available" name="bus_parking_available" value="1" 
                                           {{ old('bus_parking_available', $facility->bus_parking_available) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bus_parking_available">
                                        バス駐車可
                                    </label>
                                    @error('bus_parking_available')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="parking_remarks" class="form-label">駐車場備考</label>
                            <textarea class="form-control @error('parking_remarks') is-invalid @enderror" 
                                      id="parking_remarks" name="parking_remarks" rows="2" maxlength="500" 
                                      placeholder="例: 大型バス3台駐車可能">{{ old('parking_remarks', $facility->parking_remarks) }}</textarea>
                            @error('parking_remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">※ 500文字以内</small>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.facilities.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.facilities.show', $facility) }}" class="btn btn-info">
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
</style>
@endpush