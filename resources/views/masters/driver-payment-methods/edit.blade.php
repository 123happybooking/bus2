@extends('layouts.app')

@section('title', '支払方法編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.driver-payment-methods.index') }}">支払方法</a></li>
                    <li class="breadcrumb-item active" aria-current="page">編集</li>
                </ol>
            </nav>
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> 入力エラーがあります</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="card shadow-sm card-edit">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> 支払方法編集</h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.driver-payment-methods.update', $paymentMethod) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-12">
                                <label for="method_name" class="form-label required">支払方法名</label>
                                <input type="text" class="form-control @error('method_name') is-invalid @enderror" 
                                       id="method_name" name="method_name" value="{{ old('method_name', $paymentMethod->method_name) }}" 
                                       required maxlength="100">
                                @error('method_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <label for="remark" class="form-label">備考</label>
                                <textarea class="form-control @error('remark') is-invalid @enderror" 
                                          id="remark" name="remark" rows="3" maxlength="500">{{ old('remark', $paymentMethod->remark) }}</textarea>
                                @error('remark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input @error('is_reimbursable') is-invalid @enderror" 
                                           type="checkbox" id="is_reimbursable" name="is_reimbursable" value="1" 
                                           {{ old('is_reimbursable', $paymentMethod->is_reimbursable) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_reimbursable">
                                        精算対象
                                    </label>
                                    @error('is_reimbursable')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle"></i> 更新する
                            </button>
                            <a href="{{ route('masters.driver-payment-methods.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> キャンセル
                            </a>
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