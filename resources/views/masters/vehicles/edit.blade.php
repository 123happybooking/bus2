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
                    <form method="POST" action="{{ route('masters.vehicles.update', $vehicle) }}" id="vehicleForm" enctype="multipart/form-data">
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

                            <div class="col-md-6">
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
                        
                            <div class="col-md-6">
                                <label for="vehicle_model_id" class="form-label">モデル</label>
                                <select name="vehicle_model_id" id="vehicle_model_id" 
                                        class="form-select @error('vehicle_model_id') is-invalid @enderror" required>
                                    <option value="">先に車両種類を選択してください</option>
                                </select>
                                @error('vehicle_model_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
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
                        
                            <!--<div class="col-md-6">-->
                            <!--    <label for="vehicle_color" class="form-label">車両色</label>-->
                            <!--    <select name="vehicle_color" id="vehicle_color" class="form-select @error('vehicle_color') is-invalid @enderror">-->
                            <!--        <option value="">選択してください</option>-->
                            <!--        <option value="ホワイト" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'ホワイト' ? 'selected' : '' }}>ホワイト</option>-->
                            <!--        <option value="ブラック" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'ブラック' ? 'selected' : '' }}>ブラック</option>-->
                            <!--        <option value="シルバー" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'シルバー' ? 'selected' : '' }}>シルバー</option>-->
                            <!--        <option value="グレー" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'グレー' ? 'selected' : '' }}>グレー</option>-->
                            <!--        <option value="レッド" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'レッド' ? 'selected' : '' }}>レッド</option>-->
                            <!--        <option value="ブルー" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'ブルー' ? 'selected' : '' }}>ブルー</option>-->
                            <!--        <option value="グリーン" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'グリーン' ? 'selected' : '' }}>グリーン</option>-->
                            <!--        <option value="イエロー" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'イエロー' ? 'selected' : '' }}>イエロー</option>-->
                            <!--        <option value="オレンジ" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'オレンジ' ? 'selected' : '' }}>オレンジ</option>-->
                            <!--        <option value="ゴールド" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'ゴールド' ? 'selected' : '' }}>ゴールド</option>-->
                            <!--        <option value="その他" {{ old('vehicle_color', $vehicle->vehicle_color ?? '') == 'その他' ? 'selected' : '' }}>その他</option>-->
                            <!--    </select>-->
                            <!--    @error('vehicle_color')-->
                            <!--        <div class="invalid-feedback">{{ $message }}</div>-->
                            <!--    @enderror-->
                            <!--</div>-->

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
                            
                            <div class="col-md-6">
                                <label for="vehicle_image" class="form-label">車両画像</label>
                                
                                @if($vehicle->image_path)
                                <div class="mb-3" id="currentImageContainer">
                                    <div class="position-relative" style="display: inline-block;">
                                        <img src="{{ asset('storage/' . $vehicle->image_path) }}" 
                                             alt="{{ $vehicle->registration_number }}" 
                                             style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                                id="removeImageBtn" style="transform: translate(50%, -50%); border-radius: 50%;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="remove_image" id="removeImage" value="0">
                                </div>
                                @endif
                                
                                <input type="file" name="vehicle_image" id="vehicle_image" 
                                       class="form-control @error('vehicle_image') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/jpg,image/gif">
                                <small class="form-text text-muted">対応形式: JPG, JPEG, PNG, GIF (最大 5MB)</small>
                                @error('vehicle_image')
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
                            
                            
                            

                            
                            <div class="col-md-12 mt-3">
                                <hr>
                                <h6 class="mb-3"><i class="bi bi-share"></i> 共有設定</h6>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="is_share" id="is_share" value="1" 
                                           style="width: 40px; height: 20px; cursor: pointer;"
                                           {{ old('is_share', $vehicle->is_share) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2" for="is_share" style="font-weight: 500;">
                                        この車両を友達会社と共有する
                                    </label>
                                </div>
                                <small class="text-muted">※ 共有すると、友達会社がこの車両を予約できるようになります</small>
                            </div>
                            
                            <div class="col-md-12 mb-3" id="shareToContainer" style="{{ old('is_share', $vehicle->is_share) ? '' : 'display: none;' }}">
                                @php
                                    $shareMode = 'selected';
                                    $selectedFriendIds = [];
                                    
                                    if (old('share_mode')) {
                                        $shareMode = old('share_mode');
                                    } elseif ($vehicle->share_to == 'all') {
                                        $shareMode = 'all';
                                    } elseif ($vehicle->share_to) {
                                        $decoded = json_decode($vehicle->share_to, true);
                                        if (is_array($decoded)) {
                                            $selectedFriendIds = $decoded;
                                        }
                                    }
                                    
                                    if (old('share_to')) {
                                        $selectedFriendIds = old('share_to');
                                    }
                                @endphp
                                
                                <label class="form-label">共有先を選択</label>
                                
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="share_mode" id="share_mode_all" value="all"
                                               {{ $shareMode == 'all' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="share_mode_all">
                                            すべての友達会社と共有
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="share_mode" id="share_mode_selected" value="selected"
                                               {{ $shareMode == 'selected' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="share_mode_selected">
                                            特定の友達会社と共有（以下の会社を選択）
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="companySelectList" style="{{ $shareMode == 'selected' ? '' : 'display: none;' }}">
                                    <div class="friend-companies-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 10px;">
                                        @if(isset($friendCompanies) && count($friendCompanies) > 0)
                                            @foreach($friendCompanies as $friend)
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input friend-checkbox" 
                                                       name="share_to[]" value="{{ $friend->id }}" 
                                                       id="friend_{{ $friend->id }}"
                                                       {{ in_array($friend->id, $selectedFriendIds) ? 'checked' : '' }}
                                                       {{ $shareMode == 'all' ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="friend_{{ $friend->id }}">
                                                    {{ $friend->user_company_name }}
                                                </label>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="text-muted text-center py-3">
                                                <i class="bi bi-info-circle"></i> 友達会社がありません。<br>
                                                <a href="{{ route('masters.friends.index') }}">友達追加</a> から友達を追加してください。
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <small class="text-muted">※ チェックを入れた会社にのみ車両が共有されます</small>
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
#companySelectList {
    margin-top: 10px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleTypeSelect = document.getElementById('vehicle_type_id');
    const vehicleModelSelect = document.getElementById('vehicle_model_id');
    const currentModelId = {{ old('vehicle_model_id', $vehicle->vehicle_model_id) ?: 'null' }};
    
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





const vehicleImageInput = document.getElementById('vehicle_image');
if (vehicleImageInput) {
    vehicleImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const currentImageContainer = document.getElementById('currentImageContainer');
                if (currentImageContainer) {
                    const img = currentImageContainer.querySelector('img');
                    if (img) {
                        img.src = event.target.result;
                    } else {
                        const container = document.createElement('div');
                        container.className = 'mb-3';
                        container.id = 'currentImageContainer';
                        container.innerHTML = `
                            <div class="position-relative" style="display: inline-block;">
                                <img src="${event.target.result}" alt="プレビュー" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" id="newRemoveImageBtn" style="transform: translate(50%, -50%); border-radius: 50%;">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <input type="hidden" name="remove_image" id="removeImage" value="0">
                        `;
                        vehicleImageInput.parentNode.insertBefore(container, vehicleImageInput);
                        
                        const removeBtn = document.getElementById('newRemoveImageBtn');
                        if (removeBtn) {
                            removeBtn.addEventListener('click', function() {
                                container.remove();
                                vehicleImageInput.value = '';
                                document.getElementById('removeImage').value = '1';
                            });
                        }
                    }
                } else {
                    const container = document.createElement('div');
                    container.className = 'mb-3';
                    container.id = 'currentImageContainer';
                    container.innerHTML = `
                        <div class="position-relative" style="display: inline-block;">
                            <img src="${event.target.result}" alt="プレビュー" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" id="newRemoveImageBtn" style="transform: translate(50%, -50%); border-radius: 50%;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <input type="hidden" name="remove_image" id="removeImage" value="0">
                    `;
                    vehicleImageInput.parentNode.insertBefore(container, vehicleImageInput);
                    
                    const removeBtn = document.getElementById('newRemoveImageBtn');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', function() {
                            container.remove();
                            vehicleImageInput.value = '';
                            document.getElementById('removeImage').value = '1';
                        });
                    }
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

const removeImageBtn = document.getElementById('removeImageBtn');
if (removeImageBtn) {
    removeImageBtn.addEventListener('click', function() {
        if (confirm('画像を削除してもよろしいですか？')) {
            document.getElementById('removeImage').value = '1';
            const container = document.getElementById('currentImageContainer');
            if (container) {
                container.style.display = 'none';
            }
        }
    });
}



const isShareCheckbox = document.getElementById('is_share');
const shareToContainer = document.getElementById('shareToContainer');

if (isShareCheckbox) {
    isShareCheckbox.addEventListener('change', function() {
        if (shareToContainer) {
            shareToContainer.style.display = this.checked ? 'block' : 'none';
        }
        if (!this.checked && shareModeAll) {
            shareModeAll.checked = false;
            shareModeSelected.checked = false;
            if (companySelectList) {
                companySelectList.style.display = 'none';
            }
        }
    });
}


const shareModeAll = document.getElementById('share_mode_all');
const shareModeSelected = document.getElementById('share_mode_selected');
const companySelectList = document.getElementById('companySelectList');
const friendCheckboxes = document.querySelectorAll('.friend-checkbox');

if (shareModeAll && shareModeSelected) {
    shareModeAll.addEventListener('change', function() {
        if (this.checked) {
            companySelectList.style.display = 'none';
            friendCheckboxes.forEach(cb => {
                cb.disabled = true;
                cb.checked = false;
            });
        }
    });
    
    shareModeSelected.addEventListener('change', function() {
        if (this.checked) {
            companySelectList.style.display = 'block';
            friendCheckboxes.forEach(cb => {
                cb.disabled = false;
            });
        }
    });
}
</script>
@endpush