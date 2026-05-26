@extends('layouts.app')

@section('title', '運転手編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.drivers.index') }}">運転手管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">運転手編集</li>
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
                        <i class="bi bi-person-badge"></i> 運転手編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('masters.drivers.update', $driver) }}" enctype="multipart/form-data" id="driverForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="driver_code" class="form-label">運転手コード</label>
                                <input type="text" class="form-control @error('driver_code') is-invalid @enderror" 
                                       id="driver_code" name="driver_code" 
                                       value="{{ old('driver_code', $driver->driver_code) }}" 
                                       maxlength="20" required placeholder="例: DR001">
                                @error('driver_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="branch_id" class="form-label">支店</label>
                                <select class="form-select @error('branch_id') is-invalid @enderror" 
                                        id="branch_id" name="branch_id" required>
                                    <option value="">選択してください</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $driver->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->branch_code }} - {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">氏名</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name', $driver->name) }}" maxlength="100" required placeholder="例: 山田 太郎">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name_kana" class="form-label">氏名（カナ）</label>
                                <input type="text" class="form-control @error('name_kana') is-invalid @enderror" 
                                       id="name_kana" name="name_kana" 
                                       value="{{ old('name_kana', $driver->name_kana) }}" 
                                       maxlength="100" placeholder="例: ヤマダ タロウ">
                                @error('name_kana')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">電話番号</label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number', $driver->phone_number) }}" 
                                       maxlength="20" placeholder="例: 090-1234-5678">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">メールアドレス</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" 
                                       value="{{ old('email', $driver->email) }}" 
                                       maxlength="100" placeholder="例: yamada@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="login_id" class="form-label">ログインID</label>
                                <input type="text" class="form-control @error('login_id') is-invalid @enderror" 
                                       id="login_id" name="login_id" 
                                       value="{{ old('login_id', $driver->login_id) }}" 
                                       maxlength="255" readonly>
                                @error('login_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">パスワード</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" 
                                       maxlength="255" placeholder="変更する場合のみ入力">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">パスワード（確認）</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" 
                                       maxlength="255" placeholder="パスワードを再入力">
                            </div>
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label">生年月日</label>
                                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                       id="birth_date" name="birth_date" 
                                       value="{{ old('birth_date', $driver->birth_date ? \Carbon\Carbon::parse($driver->birth_date)->format('Y-m-d') : '') }}">
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="hire_date" class="form-label">入社日</label>
                                <input type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                       id="hire_date" name="hire_date" 
                                       value="{{ old('hire_date', $driver->hire_date ? \Carbon\Carbon::parse($driver->hire_date)->format('Y-m-d') : '') }}" required>
                                @error('hire_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="license_type" class="form-label" style="margin-bottom: 0;">免許種類</label>
                                    <button type="button" class="btn btn-sm p-0 border-0" style="color: #2563eb; background: transparent;" data-bs-toggle="modal" data-bs-target="#licenseTypeModal">
                                        <i class="bi bi-gear"></i> 管理
                                    </button>
                                </div>
                                <div style="position: relative;">
                                    <input type="text" class="form-control @error('license_type') is-invalid @enderror" 
                                           id="license_type" name="license_type" 
                                           value="{{ old('license_type', $driver->license_type) }}" 
                                           maxlength="50" required placeholder="例: 普通自動車第一種"
                                           autocomplete="off">
                                    <div id="licenseTypeDropdown" class="dropdown-menu" style="width: 100%; max-height: 200px; overflow-y: auto; top: 100%; left: 0; font-size: 14px;">
                                        @foreach($licenseTypes as $type)
                                            <a href="#" class="dropdown-item license-type-option" style="font-size: 14px;" data-value="{{ $type }}">{{ $type }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                @error('license_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="license_expiration_date" class="form-label">免許有効期限</label>
                                <input type="date" class="form-control @error('license_expiration_date') is-invalid @enderror" 
                                       id="license_expiration_date" name="license_expiration_date" 
                                       value="{{ old('license_expiration_date', $driver->license_expiration_date ? \Carbon\Carbon::parse($driver->license_expiration_date)->format('Y-m-d') : '') }}" required>
                                @error('license_expiration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="display_order" class="form-label">表示順序</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" 
                                       value="{{ old('display_order', $driver->display_order) }}" 
                                       min="0" placeholder="例: 10">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <label for="remarks" class="form-label">備考</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          id="remarks" name="remarks" 
                                          rows="3" maxlength="500" 
                                          placeholder="備考を入力してください">{{ old('remarks', $driver->remarks) }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="license_image" class="form-label">免許証</label>
                                <div class="mb-2">
                                    <div class="image-preview-container mb-2" id="licensePreview">
                                        @if($driver->license_image)
                                            <img src="{{ asset('storage/' . $driver->license_image) }}" alt="免許証" style="height: 120px; border: 1px solid #ddd; padding: 5px;">
                                        @else
                                            <div class="text-center d-flex align-items-center justify-content-center" style="height: 120px; width: 160px; border: 1px solid #ddd; background-color: #f8f9fa;">
                                                <i class="bi bi-card-image" style="font-size: 32px; color: #999;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" class="form-control @error('license_image') is-invalid @enderror" 
                                           id="license_image" name="license_image" accept="image/*"
                                           onchange="previewImage(this, 'licensePreview')">
                                    <small class="text-muted">対応形式: JPEG, PNG, JPG, GIF (最大2MB)</small>
                                    @error('license_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="seal_image" class="form-label">印鑑</label>
                                <div class="mb-2">
                                    <div class="image-preview-container mb-2" id="sealPreview">
                                        @if($driver->seal_image)
                                            <img src="{{ asset('storage/' . $driver->seal_image) }}" alt="印鑑" style="height: 120px; border: 1px solid #ddd; padding: 5px;">
                                        @else
                                            <div class="text-center d-flex align-items-center justify-content-center" style="height: 120px; width: 160px; border: 1px solid #ddd; background-color: #f8f9fa;">
                                                <i class="bi bi-card-image" style="font-size: 32px; color: #999;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" class="form-control @error('seal_image') is-invalid @enderror" 
                                           id="seal_image" name="seal_image" accept="image/*"
                                           onchange="previewImage(this, 'sealPreview')">
                                    <small class="text-muted">対応形式: JPEG, PNG, JPG, GIF (最大2MB)</small>
                                    @error('seal_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" 
                                           id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $driver->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        有効状態
                                    </label>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.drivers.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.drivers.show', $driver) }}" class="btn btn-info">
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

<div class="modal fade" id="licenseTypeModal" tabindex="-1" aria-labelledby="licenseTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="licenseTypeModalLabel">
                    <i class="bi bi-gear"></i> 免許種類管理
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small" style="font-size: 14px;">1行に1つの種類を入力してください。</p>
                <textarea id="licenseTypesTextarea" class="form-control" rows="15" style="font-size: 14px;">@foreach($licenseTypes as $type){{ $type }}
@endforeach</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="saveLicenseTypesBtn">保存する</button>
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
.dropdown-menu {
    display: none;
    position: absolute;
    z-index: 1000;
}
.dropdown-menu.show {
    display: block;
}
</style>
@endpush

@push('scripts')
<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const previewContainer = document.getElementById(previewId);
        
        reader.onload = function(e) {
            previewContainer.innerHTML = `<img src="${e.target.result}" style="height: 120px; border: 1px solid #ddd; padding: 5px;">`;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

var licenseInput = document.getElementById('license_type');
var licenseDropdown = document.getElementById('licenseTypeDropdown');

licenseInput.addEventListener('click', function() {
    licenseDropdown.classList.add('show');
});

licenseInput.addEventListener('blur', function() {
    setTimeout(function() {
        licenseDropdown.classList.remove('show');
    }, 200);
});

licenseInput.addEventListener('input', function() {
    var filter = this.value.toLowerCase();
    var options = licenseDropdown.querySelectorAll('.license-type-option');
    var hasVisible = false;
    options.forEach(function(option) {
        var text = option.textContent.toLowerCase();
        if (text.indexOf(filter) > -1) {
            option.style.display = 'block';
            hasVisible = true;
        } else {
            option.style.display = 'none';
        }
    });
    if (hasVisible) {
        licenseDropdown.classList.add('show');
    }
});

document.querySelectorAll('.license-type-option').forEach(function(option) {
    option.addEventListener('click', function(e) {
        e.preventDefault();
        licenseInput.value = this.getAttribute('data-value');
        licenseDropdown.classList.remove('show');
        licenseInput.focus();
    });
});

document.getElementById('saveLicenseTypesBtn').addEventListener('click', function() {
    var types = document.getElementById('licenseTypesTextarea').value;
    
    fetch('{{ route("masters.driver-license-types.save") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ types: types })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            alert('免許種類を保存しました。');
            location.reload();
        } else {
            alert('保存に失敗しました。');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('エラーが発生しました。');
    });
});
</script>
@endpush