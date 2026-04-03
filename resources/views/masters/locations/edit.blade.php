@extends('layouts.app')

@section('title', '地域編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.locations.index') }}">地域管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">地域編集</li>
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
                        <i class="bi bi-geo-alt-gear"></i> 地域編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.locations.update', $location) }}" method="POST" id="locationForm">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="mb-3 border-bottom pb-2">基本情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location_code" class="form-label required">地域コード</label>
                                <input type="text" class="form-control @error('location_code') is-invalid @enderror" 
                                       id="location_code" name="location_code" 
                                       value="{{ old('location_code', $location->location_code) }}" 
                                       required maxlength="20" placeholder="例: LOC001">
                                @error('location_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内、他と重複不可</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location_name" class="form-label required">地域名</label>
                                <input type="text" class="form-control @error('location_name') is-invalid @enderror" 
                                       id="location_name" name="location_name" 
                                       value="{{ old('location_name', $location->location_name) }}" 
                                       required maxlength="100" placeholder="例: 東京地区">
                                @error('location_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 100文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location_kana" class="form-label">地域名（カナ）</label>
                                <input type="text" class="form-control @error('location_kana') is-invalid @enderror" 
                                       id="location_kana" name="location_kana" 
                                       value="{{ old('location_kana', $location->location_kana) }}" 
                                       maxlength="100" placeholder="例: トウキョウチク">
                                @error('location_kana')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 100文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">表示順</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" 
                                       value="{{ old('display_order', $location->display_order) }}" 
                                       min="0" placeholder="例: 10">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 数値を入力</small>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 border-bottom pb-2 mt-4">地域情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prefecture" class="form-label required">都道府県</label>
                                <input type="text" class="form-control @error('prefecture') is-invalid @enderror" 
                                       id="prefecture" name="prefecture" 
                                       value="{{ old('prefecture', $location->prefecture) }}" 
                                       required maxlength="10" placeholder="例: 東京都">
                                @error('prefecture')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 10文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="area_type" class="form-label required">エリアタイプ</label>
                                <input type="text" class="form-control @error('area_type') is-invalid @enderror" 
                                       id="area_type" name="area_type" 
                                       value="{{ old('area_type', $location->area_type) }}" 
                                       required maxlength="20" placeholder="例: 首都圏">
                                @error('area_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.locations.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.locations.show', $location) }}" class="btn btn-info">
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