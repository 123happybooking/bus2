@extends('layouts.app')

@section('title', '運行会社情報編集')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-buildings me-2"></i>基本情報</h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('masters.user-company-info.update', $UserCompanyInfo->user_company_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="user_company_id" class="form-label required">運行会社ID</label>
                                <input type="text" class="form-control bg-light" 
                                       id="user_company_id" 
                                       value="{{ old('user_company_id', $UserCompanyInfo->user_company_id) }}" 
                                       readonly disabled>
                                <small class="form-text text-muted">※ 運行会社IDは編集できません</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="user_company_name" class="form-label required">運行会社名</label>
                                <input type="text" class="form-control bg-light" 
                                       id="user_company_name" 
                                       value="{{ old('user_company_name', $UserCompanyInfo->user_company_name) }}" 
                                       readonly disabled>
                                <small class="form-text text-muted">※ 運行会社名は編集できません</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="user_plan" class="form-label required">契約種類</label>
                                <input type="text" class="form-control bg-light" 
                                       id="user_plan" 
                                       value="{{ old('user_plan', $UserCompanyInfo->user_plan) }}" 
                                       readonly disabled>
                                <small class="form-text text-muted">※ 契約種類は編集できません</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_start_day" class="form-label">使用開始日</label>
                                <input type="date" class="form-control bg-light" 
                                       id="user_start_day" 
                                       value="{{ old('user_start_day', $UserCompanyInfo->user_start_day ? $UserCompanyInfo->user_start_day->format('Y-m-d') : '') }}" 
                                       readonly disabled>
                                <small class="form-text text-muted">※ 使用開始日は編集できません</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label required">会社名</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                       id="company_name" name="company_name" 
                                       value="{{ old('company_name', $UserCompanyInfo->company_name) }}" 
                                       required maxlength="255">
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label required">郵便番号</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" 
                                       value="{{ old('postal_code', $UserCompanyInfo->postal_code) }}" 
                                       required maxlength="255">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-8 mb-3">
                                <label for="address" class="form-label required">住所</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" 
                                       value="{{ old('address', $UserCompanyInfo->address) }}" 
                                       required maxlength="255">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label required">Tel</label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number', $UserCompanyInfo->phone_number) }}" 
                                       required maxlength="255">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="fax_number" class="form-label">Fax</label>
                                <input type="text" class="form-control @error('fax_number') is-invalid @enderror" 
                                       id="fax_number" name="fax_number" 
                                       value="{{ old('fax_number', $UserCompanyInfo->fax_number) }}" maxlength="255">
                                @error('fax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Mail</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" 
                                       value="{{ old('email', $UserCompanyInfo->email) }}" maxlength="255">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email_for_drv" class="form-label">業務用Mail</label>
                                <input type="email" class="form-control @error('email_for_drv') is-invalid @enderror" 
                                       id="email_for_drv" name="email_for_drv" 
                                       value="{{ old('email_for_drv', $UserCompanyInfo->email_for_drv) }}" maxlength="255">
                                @error('email_for_drv')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone_number_emergency" class="form-label required">緊急電話</label>
                                <input type="text" class="form-control @error('phone_number_emergency') is-invalid @enderror" 
                                       id="phone_number_emergency" name="phone_number_emergency" 
                                       value="{{ old('phone_number_emergency', $UserCompanyInfo->phone_number_emergency) }}" 
                                       required maxlength="255">
                                @error('phone_number_emergency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="work_license_area" class="form-label required">運行地域</label>
                                <input type="text" class="form-control @error('work_license_area') is-invalid @enderror" 
                                       id="work_license_area" name="work_license_area" 
                                       value="{{ old('work_license_area', $UserCompanyInfo->work_license_area) }}" 
                                       required maxlength="255">
                                @error('work_license_area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="work_license_number" class="form-label required">事業者情報</label>
                                <input type="text" class="form-control @error('work_license_number') is-invalid @enderror" 
                                       id="work_license_number" name="work_license_number" 
                                       value="{{ old('work_license_number', $UserCompanyInfo->work_license_number) }}" 
                                       required maxlength="255">
                                @error('work_license_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="work_license_day" class="form-label required">事業許可日</label>
                                <input type="date" class="form-control @error('work_license_day') is-invalid @enderror" 
                                       id="work_license_day" name="work_license_day" 
                                       value="{{ old('work_license_day', $UserCompanyInfo->work_license_day ? $UserCompanyInfo->work_license_day->format('Y-m-d') : '') }}" 
                                       required>
                                @error('work_license_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="president_name" class="form-label required">取締役</label>
                                <input type="text" class="form-control @error('president_name') is-invalid @enderror" 
                                       id="president_name" name="president_name" 
                                       value="{{ old('president_name', $UserCompanyInfo->president_name) }}" 
                                       required maxlength="255">
                                @error('president_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="work_manager_name_1st" class="form-label required">責任者01</label>
                                <input type="text" class="form-control @error('work_manager_name_1st') is-invalid @enderror" 
                                       id="work_manager_name_1st" name="work_manager_name_1st" 
                                       value="{{ old('work_manager_name_1st', $UserCompanyInfo->work_manager_name_1st) }}" 
                                       required maxlength="255">
                                @error('work_manager_name_1st')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="work_manager_name_2nd" class="form-label">責任者02</label>
                                <input type="text" class="form-control @error('work_manager_name_2nd') is-invalid @enderror" 
                                       id="work_manager_name_2nd" name="work_manager_name_2nd" 
                                       value="{{ old('work_manager_name_2nd', $UserCompanyInfo->work_manager_name_2nd) }}" maxlength="255">
                                @error('work_manager_name_2nd')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="work_manager_name_3rd" class="form-label">責任者03</label>
                                <input type="text" class="form-control @error('work_manager_name_3rd') is-invalid @enderror" 
                                       id="work_manager_name_3rd" name="work_manager_name_3rd" 
                                       value="{{ old('work_manager_name_3rd', $UserCompanyInfo->work_manager_name_3rd) }}" maxlength="255">
                                @error('work_manager_name_3rd')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="report_car_count" class="form-label">報告台数</label>
                                <input type="number" class="form-control @error('report_car_count') is-invalid @enderror" 
                                       id="report_car_count" name="report_car_count" 
                                       value="{{ old('report_car_count', $UserCompanyInfo->report_car_count) }}" min="0">
                                @error('report_car_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="report_employee_count" class="form-label">報告従業員数</label>
                                <input type="number" class="form-control @error('report_employee_count') is-invalid @enderror" 
                                       id="report_employee_count" name="report_employee_count" 
                                       value="{{ old('report_employee_count', $UserCompanyInfo->report_employee_count) }}" min="0">
                                @error('report_employee_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="report_drv_count" class="form-label">報告運転手数</label>
                                <input type="number" class="form-control @error('report_drv_count') is-invalid @enderror" 
                                       id="report_drv_count" name="report_drv_count" 
                                       value="{{ old('report_drv_count', $UserCompanyInfo->report_drv_count) }}" min="0">
                                @error('report_drv_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="accounting_manager_name" class="form-label">会計責任者名</label>
                                <input type="text" class="form-control @error('accounting_manager_name') is-invalid @enderror" 
                                       id="accounting_manager_name" name="accounting_manager_name" 
                                       value="{{ old('accounting_manager_name', $UserCompanyInfo->accounting_manager_name) }}" maxlength="255">
                                @error('accounting_manager_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="accounting_manager_department" class="form-label">会計責任者所属</label>
                                <input type="text" class="form-control @error('accounting_manager_department') is-invalid @enderror" 
                                       id="accounting_manager_department" name="accounting_manager_department" 
                                       value="{{ old('accounting_manager_department', $UserCompanyInfo->accounting_manager_department) }}" maxlength="255">
                                @error('accounting_manager_department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="optional_car_insurance" class="form-label">保険内容</label>
                                <textarea class="form-control @error('optional_car_insurance') is-invalid @enderror" 
                                          id="optional_car_insurance" name="optional_car_insurance" rows="3">{{ old('optional_car_insurance', $UserCompanyInfo->optional_car_insurance) }}</textarea>
                                @error('optional_car_insurance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="invoice_code" class="form-label">登録番号</label>
                                <input type="text" class="form-control @error('invoice_code') is-invalid @enderror" 
                                       id="invoice_code" name="invoice_code" 
                                       value="{{ old('invoice_code', $UserCompanyInfo->invoice_code) }}" maxlength="255">
                                @error('invoice_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="setup_start_time" class="form-label">開始時刻初期設定値</label>
                                <input type="time" class="form-control @error('setup_start_time') is-invalid @enderror" 
                                       id="setup_start_time" name="setup_start_time" 
                                       value="{{ old('setup_start_time', $UserCompanyInfo->setup_start_time ? \Carbon\Carbon::parse($UserCompanyInfo->setup_start_time)->format('H:i') : '') }}">
                                @error('setup_start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="setup_end_time" class="form-label">終了時刻初期設定値</label>
                                <input type="time" class="form-control @error('setup_end_time') is-invalid @enderror" 
                                       id="setup_end_time" name="setup_end_time" 
                                       value="{{ old('setup_end_time', $UserCompanyInfo->setup_end_time ? \Carbon\Carbon::parse($UserCompanyInfo->setup_end_time)->format('H:i') : '') }}">
                                @error('setup_end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="setup_bank_name" class="form-label">入金銀行初期設定値</label>
                                <input type="text" class="form-control @error('setup_bank_name') is-invalid @enderror" 
                                       id="setup_bank_name" name="setup_bank_name" 
                                       value="{{ old('setup_bank_name', $UserCompanyInfo->setup_bank_name) }}" maxlength="255">
                                @error('setup_bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="setup_company_seal" class="form-label">社印</label>
                                <div class="mb-2">
                                    @if($UserCompanyInfo->setup_company_seal)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $UserCompanyInfo->setup_company_seal) }}" alt="社印" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('setup_company_seal') is-invalid @enderror" 
                                           id="setup_company_seal" name="setup_company_seal" accept="image/*">
                                    <small class="form-text text-muted">※ 画像ファイル（JPG、PNG、GIFなど）をアップロードしてください</small>
                                </div>
                                @error('setup_company_seal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
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
.bg-light {
    background-color: #e9ecef !important;
}
</style>
@endpush