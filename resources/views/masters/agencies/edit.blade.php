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
            
            <div class="card shadow-sm card-edit">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building-gear"></i> 代理店編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.agencies.update', $agency) }}" method="POST" id="agencyForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="agency_code" class="form-label required">代理店コード</label>
                                <input type="text" class="form-control @error('agency_code') is-invalid @enderror" 
                                       id="agency_code" name="agency_code" 
                                       value="{{ old('agency_code', $agency->agency_code) }}" 
                                       required maxlength="50" placeholder="例: AG001">
                                @error('agency_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="agency_name" class="form-label required">代理店名</label>
                                <input type="text" class="form-control @error('agency_name') is-invalid @enderror" 
                                       id="agency_name" name="agency_name" 
                                       value="{{ old('agency_name', $agency->agency_name) }}" 
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
                                       value="{{ old('branch_name', $agency->branch_name) }}"
                                       maxlength="100" placeholder="例: 東京支店">
                                @error('branch_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="type" class="form-label" style="margin-bottom: 2px;">種類</label>
                                    <button type="button" class="btn btn-sm p-0 border-0" style="color: #2563eb; background: transparent; line-height: 100%;" data-bs-toggle="modal" data-bs-target="#agencyTypeModal">
                                        <i class="bi bi-gear"></i> 管理
                                    </button>
                                </div>
                                <div style="position: relative;">
                                    <input type="text" class="form-control @error('type') is-invalid @enderror" 
                                           id="type" name="type" 
                                           value="{{ old('type', $agency->type) }}"
                                           maxlength="50" placeholder="例: 一般代理店"
                                           autocomplete="off">
                                    <div id="agencyTypeDropdown" class="dropdown-menu" style="width: 100%; max-height: 200px; overflow-y: auto; top: 100%; left: 0; font-size: 14px;">
                                        @foreach($agencyTypes as $agencyType)
                                            <a href="#" class="dropdown-item agency-type-option" style="font-size: 14px;" data-value="{{ $agencyType }}">{{ $agencyType }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="country" class="form-label" style="margin-bottom: 0;">国</label>
                                    <button type="button" class="btn btn-sm p-0 border-0" style="color: #2563eb; background: transparent;" data-bs-toggle="modal" data-bs-target="#countryModal">
                                        <i class="bi bi-gear"></i> 管理
                                    </button>
                                </div>
                                <div style="position: relative;">
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                           id="country" name="country" 
                                           value="{{ old('country', $agency->country) }}"
                                           maxlength="50" placeholder="例: 日本"
                                           autocomplete="off">
                                    <div id="countryDropdown" class="dropdown-menu" style="width: 100%; max-height: 200px; overflow-y: auto; top: 100%; left: 0; font-size: 14px;">
                                        @foreach($countries as $country)
                                            <a href="#" class="dropdown-item country-option" style="font-size: 14px;" data-value="{{ $country }}">{{ $country }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="display_order" class="form-label">表示順</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" 
                                       value="{{ old('display_order', $agency->display_order) }}"
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
                                       value="{{ old('postal_code', $agency->postal_code) }}"
                                       maxlength="10" placeholder="例: 100-0001">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">電話番号</label>
                                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number', $agency->phone_number) }}"
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
                                       value="{{ old('fax_number', $agency->fax_number) }}"
                                       maxlength="20" placeholder="例: 03-1234-5679">
                                @error('fax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">メールアドレス</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" 
                                       value="{{ old('email', $agency->email) }}"
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
                                       value="{{ old('manager_name', $agency->manager_name) }}"
                                       maxlength="50" placeholder="例: 山田 太郎">
                                @error('manager_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
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
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <label for="commission_rate" class="form-label">手数料率 (%)</label>
                                <input type="number" step="0.01" min="0" max="100" 
                                       class="form-control @error('commission_rate') is-invalid @enderror" 
                                       id="commission_rate" name="commission_rate" 
                                       value="{{ old('commission_rate', $agency->commission_rate) }}">
                                @error('commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="closing_day" class="form-label">締日</label>
                                <input type="number" min="1" max="31" 
                                       class="form-control @error('closing_day') is-invalid @enderror" 
                                       id="closing_day" name="closing_day" 
                                       value="{{ old('closing_day', $agency->closing_day) }}">
                                @error('closing_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="payment_day" class="form-label">支払日</label>
                                <input type="number" min="0" 
                                       class="form-control @error('payment_day') is-invalid @enderror" 
                                       id="payment_day" name="payment_day" 
                                       value="{{ old('payment_day', $agency->payment_day) }}">
                                @error('payment_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bill_to" class="form-label">請求先</label>
                            <textarea class="form-control @error('bill_to') is-invalid @enderror" 
                                      id="bill_to" name="bill_to" rows="3"
                                      maxlength="500" placeholder="〒000-0000&#10;東京都○○区○○町0-0-0&#10;○○株式会社 御中">{{ old('bill_to', $agency->bill_to ?? '') }}</textarea>
                            @error('bill_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">備考</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3"
                                      maxlength="500" placeholder="特記事項があれば入力">{{ old('remarks', $agency->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

<div class="modal fade" id="countryModal" tabindex="-1" aria-labelledby="countryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="countryModalLabel">
                    <i class="bi bi-gear"></i> 国管理
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small" style="font-size: 14px;">1行に1つの国名を入力してください。</p>
                <textarea id="countryTextarea" class="form-control" rows="15" style="font-size: 14px;">@foreach($countries as $country){{ $country }}
@endforeach</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="saveCountriesBtn">保存する</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="agencyTypeModal" tabindex="-1" aria-labelledby="agencyTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="agencyTypeModalLabel">
                    <i class="bi bi-gear"></i> 代理店種類管理
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small" style="font-size: 14px;">1行に1つの種類を入力してください。</p>
                <textarea id="agencyTypeTextarea" class="form-control" rows="15" style="font-size: 14px;">@foreach($agencyTypes as $agencyType){{ $agencyType }}
@endforeach</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="saveAgencyTypesBtn">保存する</button>
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
var countryInput = document.getElementById('country');
var countryDropdown = document.getElementById('countryDropdown');

if (countryInput) {
    countryInput.addEventListener('click', function() {
        countryDropdown.classList.add('show');
    });

    countryInput.addEventListener('blur', function() {
        setTimeout(function() {
            countryDropdown.classList.remove('show');
        }, 200);
    });

    countryInput.addEventListener('input', function() {
        var filter = this.value.toLowerCase();
        var options = countryDropdown.querySelectorAll('.country-option');
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
            countryDropdown.classList.add('show');
        }
    });

    document.querySelectorAll('.country-option').forEach(function(option) {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            countryInput.value = this.getAttribute('data-value');
            countryDropdown.classList.remove('show');
            countryInput.focus();
        });
    });
}

var agencyTypeInput = document.getElementById('type');
var agencyTypeDropdown = document.getElementById('agencyTypeDropdown');

if (agencyTypeInput) {
    agencyTypeInput.addEventListener('click', function() {
        agencyTypeDropdown.classList.add('show');
    });

    agencyTypeInput.addEventListener('blur', function() {
        setTimeout(function() {
            agencyTypeDropdown.classList.remove('show');
        }, 200);
    });

    agencyTypeInput.addEventListener('input', function() {
        var filter = this.value.toLowerCase();
        var options = agencyTypeDropdown.querySelectorAll('.agency-type-option');
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
            agencyTypeDropdown.classList.add('show');
        }
    });

    document.querySelectorAll('.agency-type-option').forEach(function(option) {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            agencyTypeInput.value = this.getAttribute('data-value');
            agencyTypeDropdown.classList.remove('show');
            agencyTypeInput.focus();
        });
    });
}

document.getElementById('saveCountriesBtn').addEventListener('click', function() {
    var types = document.getElementById('countryTextarea').value;
    
    fetch('{{ route("masters.countries.save") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ countries: types })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            alert('国を保存しました。');
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

document.getElementById('saveAgencyTypesBtn').addEventListener('click', function() {
    var types = document.getElementById('agencyTypeTextarea').value;
    
    fetch('{{ route("masters.agency-types.save") }}', {
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
            alert('代理店種類を保存しました。');
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