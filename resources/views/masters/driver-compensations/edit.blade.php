@extends('layouts.app')

@section('title', 'ドライバー報酬編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.driver-compensations.index') }}">ドライバー報酬</a></li>
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
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> 報酬編集</h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.driver-compensations.update', $compensation) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="bus_assignment_id" class="form-label">運行ID</label>
                                <input type="number" class="form-control @error('bus_assignment_id') is-invalid @enderror" 
                                       id="bus_assignment_id" name="bus_assignment_id" value="{{ old('bus_assignment_id', $compensation->bus_assignment_id ?? '') }}" min="0">
                                @error('bus_assignment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="itinerary_id" class="form-label">行程ID</label>
                                <input type="number" class="form-control @error('itinerary_id') is-invalid @enderror" 
                                       id="itinerary_id" name="itinerary_id" value="{{ old('itinerary_id', $compensation->itinerary_id) }}" min="0">
                                @error('itinerary_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="driver_id" class="form-label required">ドライバー</label>
                                <select class="form-control @error('driver_id') is-invalid @enderror" 
                                        id="driver_id" name="driver_id" required>
                                    <option value="">選択してください</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ old('driver_id', $compensation->driver_id) == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('driver_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="comp_id" class="form-label required">報酬種別</label>
                                <select class="form-control @error('comp_id') is-invalid @enderror" 
                                        id="comp_id" name="comp_id" required>
                                    <option value="">選択してください</option>
                                    @foreach($compensationTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('comp_id', $compensation->comp_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->comp_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('comp_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="target_date" class="form-label required">対象日</label>
                                <input type="date" class="form-control @error('target_date') is-invalid @enderror" 
                                       id="target_date" name="target_date" value="{{ old('target_date', $compensation->target_date) }}" required>
                                @error('target_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="price" class="form-label required">単価</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $compensation->price) }}" required min="0">
                                    <span class="input-group-text">円</span>
                                </div>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="qty" class="form-label required">数量</label>
                                <input type="number" step="0.01" class="form-control @error('qty') is-invalid @enderror" 
                                       id="qty" name="qty" value="{{ old('qty', $compensation->qty) }}" required min="0">
                                @error('qty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    金額: <strong id="amountDisplay">{{ number_format($compensation->amount, 2) }}</strong> 円
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <label for="remark" class="form-label">備考</label>
                                <textarea class="form-control @error('remark') is-invalid @enderror" 
                                          id="remark" name="remark" rows="3" maxlength="500">{{ old('remark', $compensation->remark) }}</textarea>
                                @error('remark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle"></i> 更新する
                            </button>
                            <a href="{{ route('masters.driver-compensations.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const priceInput = document.getElementById('price');
const qtyInput = document.getElementById('qty');
const amountDisplay = document.getElementById('amountDisplay');

function calculateAmount() {
    const price = parseFloat(priceInput.value) || 0;
    const qty = parseFloat(qtyInput.value) || 0;
    const amount = price * qty;
    amountDisplay.innerText = amount.toFixed(2);
}

priceInput.addEventListener('input', calculateAmount);
qtyInput.addEventListener('input', calculateAmount);
</script>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
</style>
@endpush