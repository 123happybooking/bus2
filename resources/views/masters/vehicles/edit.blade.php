@extends('layouts.app')

@section('title', '車両編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.vehicles.index') }}">車両管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">車両編集</li>
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
                        <i class="bi bi-car-front"></i> 車両編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('masters.vehicles.update', $vehicle) }}" id="vehicleForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <label for="branch_id" class="form-label">所属営業所</label>
                                <select name="branch_id" id="branch_id" 
                                        class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">選択してください</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" 
                                            {{ old('branch_id', $vehicle->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->branch_code }} - {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="vehicle_code" class="form-label">車両コード</label>
                                <input type="text" name="vehicle_code" id="vehicle_code" 
                                       class="form-control @error('vehicle_code') is-invalid @enderror"
                                       value="{{ old('vehicle_code', $vehicle->vehicle_code) }}" 
                                       required maxlength="20" placeholder="例: V001">
                                @error('vehicle_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <label for="vehicle_type_id" class="form-label">車両種類</label>
                                    <select name="vehicle_type_id" id="vehicle_type_id" 
                                            class="form-select @error('vehicle_type_id') is-invalid @enderror" required>
                                        <option value="">選択してください</option>
                                        @foreach($vehicleTypes as $type)
                                            <option value="{{ $type->id }}" 
                                                {{ old('vehicle_type_id', $vehicle->vehicle_type_id) == $type->id ? 'selected' : '' }}
                                                data-models='@json($type->models)'>
                                                {{ $type->type_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            
                                <div class="col-md-4">
                                    <label for="vehicle_model_id" class="form-label">モデル</label>
                                    <select name="vehicle_model_id" id="vehicle_model_id" 
                                            class="form-select @error('vehicle_model_id') is-invalid @enderror" required>
                                        <option value="">先に車両種類を選択してください</option>
                                    </select>
                                    @error('vehicle_model_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            
                                <div class="col-md-4">
                                    <label for="vehicle_grade_id" class="form-label">車両等級</label>
                                    <select name="vehicle_grade_id" id="vehicle_grade_id" 
                                            class="form-select @error('vehicle_grade_id') is-invalid @enderror" required>
                                        <option value="">選択してください</option>
                                        @foreach($vehicleGrades as $grade)
                                            <option value="{{ $grade->id }}" 
                                                {{ old('vehicle_grade_id', $vehicle->vehicle_grade_id) == $grade->id ? 'selected' : '' }}>
                                                {{ $grade->description }} ({{ $grade->grade_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_grade_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="registration_number" class="form-label">登録番号</label>
                                <input type="text" name="registration_number" id="registration_number" 
                                       class="form-control @error('registration_number') is-invalid @enderror"
                                       value="{{ old('registration_number', $vehicle->registration_number) }}" 
                                       required maxlength="20" placeholder="例: 品川300あ1234">
                                @error('registration_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="seating_capacity" class="form-label">乗車定員</label>
                                <div class="input-group">
                                    <input type="number" name="seating_capacity" id="seating_capacity" 
                                           class="form-control @error('seating_capacity') is-invalid @enderror"
                                           value="{{ old('seating_capacity', $vehicle->seating_capacity) }}" 
                                           required min="1" max="100">
                                    <span class="input-group-text">名</span>
                                </div>
                                @error('seating_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="ownership_type" class="form-label">所有形態</label>
                                <select name="ownership_type" id="ownership_type" 
                                        class="form-select @error('ownership_type') is-invalid @enderror" required>
                                    <option value="">選択してください</option>
                                    <option value="own" {{ old('ownership_type', $vehicle->ownership_type) == 'own' ? 'selected' : '' }}>自社</option>
                                    <option value="reservable" {{ old('ownership_type', $vehicle->ownership_type) == 'reservable' ? 'selected' : '' }}>予約用</option>
                                    <option value="rental" {{ old('ownership_type', $vehicle->ownership_type) == 'rental' ? 'selected' : '' }}>傭車</option>
                                </select>
                                @error('ownership_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="inspection_expiration_date" class="form-label">車検満了日</label>
                                <input type="date" name="inspection_expiration_date" id="inspection_expiration_date" 
                                       class="form-control @error('inspection_expiration_date') is-invalid @enderror"
                                       value="{{ old('inspection_expiration_date', $vehicle->inspection_expiration_date) }}" required>
                                @error('inspection_expiration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="display_order" class="form-label">表示順序</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" 
                                       value="{{ old('display_order', $vehicle->display_order) }}" 
                                       min="0" placeholder="例: 10">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        
                            <div class="col-md-12">
                                <label for="remarks" class="form-label">備考</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          id="remarks" name="remarks" 
                                          rows="3" maxlength="500">{{ old('remarks', $vehicle->remarks) }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_active" id="is_active_true" 
                                               value="1" {{ old('is_active', $vehicle->is_active) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active_true">有効</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_active" id="is_active_false" 
                                               value="0" {{ old('is_active', $vehicle->is_active) == 0 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active_false">無効</label>
                                    </div>
                                </div>
                                @error('is_active')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.vehicles.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.vehicles.show', $vehicle) }}" class="btn btn-info">
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
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleTypeSelect = document.getElementById('vehicle_type_id');
    const vehicleModelSelect = document.getElementById('vehicle_model_id');
    const currentModelId = {{ old('vehicle_model_id', $vehicle->vehicle_model_id ?? 'null') }};
    
    function updateModelDropdown(selectedTypeId = null) {
        const selectedOption = vehicleTypeSelect.options[vehicleTypeSelect.selectedIndex];
        vehicleModelSelect.innerHTML = '';
        
        if (!selectedOption || selectedOption.value === '') {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '先に車両種類を選択してください';
            vehicleModelSelect.appendChild(defaultOption);
            vehicleModelSelect.disabled = true;
            return;
        }
        
        let models = [];
        try {
            const modelsData = selectedOption.getAttribute('data-models');
            if (modelsData) {
                models = JSON.parse(modelsData);
            }
        } catch (e) {
            console.error('モデルデータの解析に失敗しました:', e);
        }
        
        if (models.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'モデルが登録されていません';
            vehicleModelSelect.appendChild(option);
            vehicleModelSelect.disabled = true;
            return;
        }
        
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'モデルを選択してください';
        vehicleModelSelect.appendChild(defaultOption);
        
        models.sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
        
        models.forEach(model => {
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.model_name + (model.maker ? ` (${model.maker})` : '');
            
            if (model.remarks) {
                option.title = model.remarks;
            }
            
            vehicleModelSelect.appendChild(option);
        });
        
        vehicleModelSelect.disabled = false;
        
        if (currentModelId && selectedTypeId) {
            const modelExists = models.some(model => model.id == currentModelId);
            if (modelExists) {
                vehicleModelSelect.value = currentModelId;
            }
        }
    }
    
    vehicleTypeSelect.addEventListener('change', function() {
        updateModelDropdown(this.value);
    });
    
    if (vehicleTypeSelect.value) {
        updateModelDropdown(vehicleTypeSelect.value);
    } else {
        vehicleModelSelect.disabled = true;
    }
});
</script>
@endpush