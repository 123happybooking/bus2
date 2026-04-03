@extends('layouts.app')

@section('title', '基本情報編集: ' . $basicInfo->company_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.basicinfo.index') }}">基本情報設定</a></li>
                    <li class="breadcrumb-item active" aria-current="page">基本情報編集</li>
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
                        <i class="bi bi-building-gear"></i> 基本情報編集: {{ $basicInfo->company_name }}
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.basicinfo.update', $basicInfo) }}" method="POST" id="basicInfoForm">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="mb-3 border-bottom pb-2">契約情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contract_company_name" class="form-label required">契約会社名</label>
                                <input type="text" class="form-control @error('contract_company_name') is-invalid @enderror" 
                                       id="contract_company_name" name="contract_company_name" 
                                       value="{{ old('contract_company_name', $basicInfo->contract_company_name) }}" 
                                       required maxlength="255" placeholder="例: 株式会社〇〇">
                                @error('contract_company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 255文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contract_plan" class="form-label required">契約プラン</label>
                                <input type="text" class="form-control @error('contract_plan') is-invalid @enderror" 
                                       id="contract_plan" name="contract_plan" 
                                       value="{{ old('contract_plan', $basicInfo->contract_plan) }}" 
                                       required maxlength="100" placeholder="例: スタンダードプラン">
                                @error('contract_plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 100文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contract_start_date" class="form-label required">契約開始日</label>
                                <input type="date" class="form-control @error('contract_start_date') is-invalid @enderror" 
                                       id="contract_start_date" name="contract_start_date" 
                                       value="{{ old('contract_start_date', $basicInfo->contract_start_date) }}" required>
                                @error('contract_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contract_end_date" class="form-label">契約終了日</label>
                                <input type="date" class="form-control @error('contract_end_date') is-invalid @enderror" 
                                       id="contract_end_date" name="contract_end_date" 
                                       value="{{ old('contract_end_date', $basicInfo->contract_end_date) }}">
                                @error('contract_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <h6 class="mb-3 border-bottom pb-2 mt-4">会社情報</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label required">会社名</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                       id="company_name" name="company_name" 
                                       value="{{ old('company_name', $basicInfo->company_name) }}" 
                                       required maxlength="255" placeholder="例: 株式会社〇〇運輸">
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 255文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">郵便番号</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" 
                                       value="{{ old('postal_code', $basicInfo->postal_code) }}" 
                                       maxlength="10" placeholder="例: 100-0001">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 10文字以内</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label required">住所</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                   id="address" name="address" 
                                   value="{{ old('address', $basicInfo->address) }}" 
                                   required maxlength="500" placeholder="例: 東京都千代田区〇〇1-1-1">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">※ 500文字以内</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label required">電話番号</label>
                                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number', $basicInfo->phone_number) }}" 
                                       required maxlength="20" placeholder="例: 03-1234-5678">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="fax_number" class="form-label">FAX番号</label>
                                <input type="tel" class="form-control @error('fax_number') is-invalid @enderror" 
                                       id="fax_number" name="fax_number" 
                                       value="{{ old('fax_number', $basicInfo->fax_number) }}" 
                                       maxlength="20" placeholder="例: 03-1234-5679">
                                @error('fax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 20文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email_address" class="form-label required">メールアドレス</label>
                                <input type="email" class="form-control @error('email_address') is-invalid @enderror" 
                                       id="email_address" name="email_address" 
                                       value="{{ old('email_address', $basicInfo->email_address) }}" 
                                       required maxlength="255" placeholder="例: info@example.com">
                                @error('email_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 255文字以内</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="admin_email_address" class="form-label">管理者メールアドレス</label>
                                <input type="email" class="form-control @error('admin_email_address') is-invalid @enderror" 
                                       id="admin_email_address" name="admin_email_address" 
                                       value="{{ old('admin_email_address', $basicInfo->admin_email_address) }}" 
                                       maxlength="255" placeholder="例: admin@example.com">
                                @error('admin_email_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 255文字以内</small>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 border-bottom pb-2 mt-4">経営情報</h6>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="representative_director" class="form-label">代表取締役</label>
                                <input type="text" class="form-control @error('representative_director') is-invalid @enderror" 
                                       id="representative_director" name="representative_director" 
                                       value="{{ old('representative_director', $basicInfo->representative_director) }}" 
                                       maxlength="255" placeholder="例: 山田 太郎">
                                @error('representative_director')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 255文字以内</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="accounting_person" class="form-label">経理担当者</label>
                                <input type="text" class="form-control @error('accounting_person') is-invalid @enderror" 
                                       id="accounting_person" name="accounting_person" 
                                       value="{{ old('accounting_person', $basicInfo->accounting_person) }}" 
                                       maxlength="255" placeholder="例: 鈴木 一郎">
                                @error('accounting_person')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 255文字以内</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="business_vehicle_count" class="form-label">営業車両数</label>
                                <input type="number" class="form-control @error('business_vehicle_count') is-invalid @enderror" 
                                       id="business_vehicle_count" name="business_vehicle_count" 
                                       value="{{ old('business_vehicle_count', $basicInfo->business_vehicle_count) }}" 
                                       min="0" placeholder="例: 10">
                                @error('business_vehicle_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 0以上の整数</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="employee_count" class="form-label">従業員数</label>
                                <input type="number" class="form-control @error('employee_count') is-invalid @enderror" 
                                       id="employee_count" name="employee_count" 
                                       value="{{ old('employee_count', $basicInfo->employee_count) }}" 
                                       min="0" placeholder="例: 20">
                                @error('employee_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 0以上の整数</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="operation_closing_day" class="form-label">営業締日</label>
                                <input type="number" class="form-control @error('operation_closing_day') is-invalid @enderror" 
                                       id="operation_closing_day" name="operation_closing_day" 
                                       value="{{ old('operation_closing_day', $basicInfo->operation_closing_day) }}" 
                                       min="1" max="31" placeholder="例: 25">
                                @error('operation_closing_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 1-31の整数</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="safety_cost_rate" class="form-label">安全コスト率 (%)</label>
                                <input type="number" class="form-control @error('safety_cost_rate') is-invalid @enderror" 
                                       id="safety_cost_rate" name="safety_cost_rate" 
                                       value="{{ old('safety_cost_rate', $basicInfo->safety_cost_rate) }}" 
                                       min="0" max="100" step="0.01" placeholder="例: 5.0">
                                @error('safety_cost_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 0-100、小数点第2位まで</small>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 border-bottom pb-2 mt-4">システム設定</h6>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('driver_duplicate_check') is-invalid @enderror" 
                                           type="checkbox" id="driver_duplicate_check" name="driver_duplicate_check" value="1" 
                                           {{ old('driver_duplicate_check', $basicInfo->driver_duplicate_check) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="driver_duplicate_check">
                                        ドライバー重複チェック
                                    </label>
                                    @error('driver_duplicate_check')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('use_common_location_master') is-invalid @enderror" 
                                           type="checkbox" id="use_common_location_master" name="use_common_location_master" value="1" 
                                           {{ old('use_common_location_master', $basicInfo->use_common_location_master) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="use_common_location_master">
                                        共通場所マスタ使用
                                    </label>
                                    @error('use_common_location_master')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('operation_caution_alert') is-invalid @enderror" 
                                           type="checkbox" id="operation_caution_alert" name="operation_caution_alert" value="1" 
                                           {{ old('operation_caution_alert', $basicInfo->operation_caution_alert) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="operation_caution_alert">
                                        運行注意アラート
                                    </label>
                                    @error('operation_caution_alert')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('continuous_driving_warning') is-invalid @enderror" 
                                           type="checkbox" id="continuous_driving_warning" name="continuous_driving_warning" value="1" 
                                           {{ old('continuous_driving_warning', $basicInfo->continuous_driving_warning) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="continuous_driving_warning">
                                        連続運転警告
                                    </label>
                                    @error('continuous_driving_warning')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('finalized_status_after_operation') is-invalid @enderror" 
                                           type="checkbox" id="finalized_status_after_operation" name="finalized_status_after_operation" value="1" 
                                           {{ old('finalized_status_after_operation', $basicInfo->finalized_status_after_operation) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="finalized_status_after_operation">
                                        運行後確定ステータス
                                    </label>
                                    @error('finalized_status_after_operation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('company_operation_division') is-invalid @enderror" 
                                           type="checkbox" id="company_operation_division" name="company_operation_division" value="1" 
                                           {{ old('company_operation_division', $basicInfo->company_operation_division) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="company_operation_division">
                                        会社別運行区分
                                    </label>
                                    @error('company_operation_division')
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
                                <a href="{{ route('masters.basicinfo.index') }}" class="btn btn-secondary">
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