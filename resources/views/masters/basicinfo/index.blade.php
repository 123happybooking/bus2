@extends('layouts.app')

@section('title', '基本情報')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-building"></i>基本情報</h4>
            </div>
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @forelse($basicInfos as $info)
            <div class="mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i> {{ $info->company_name }}
                        </h5>
                        <div>
                            <a href="{{ route('masters.basicinfo.edit', $info) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-pencil"></i> 編集
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">契約情報</h6>
                                <dl class="row">
                                    <dt class="col-sm-4">契約会社名</dt>
                                    <dd class="col-sm-8">{{ $info->contract_company_name }}</dd>
                                    
                                    <dt class="col-sm-4">契約プラン</dt>
                                    <dd class="col-sm-8">
                                        @if($info->contract_plan)
                                            <span class="badge bg-info">{{ $info->contract_plan }}</span>
                                        @else
                                            <span class="text-muted">未設定</span>
                                        @endif
                                    </dd>
                                    
                                    <dt class="col-sm-4">契約期間</dt>
                                    <dd class="col-sm-8">
                                        @if($info->contract_start_date)
                                            {{ date('Y/m/d', strtotime($info->contract_start_date)) }}
                                        @endif
                                        @if($info->contract_end_date)
                                            ~ {{ date('Y/m/d', strtotime($info->contract_end_date)) }}
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">会社情報</h6>
                                <dl class="row">
                                    <dt class="col-sm-4">会社名</dt>
                                    <dd class="col-sm-8">{{ $info->company_name }}</dd>
                                    
                                    <dt class="col-sm-4">住所</dt>
                                    <dd class="col-sm-8">{{ $info->address }}</dd>
                                    
                                    <dt class="col-sm-4">電話番号</dt>
                                    <dd class="col-sm-8">{{ $info->phone_number }}</dd>
                                    
                                    <dt class="col-sm-4">メールアドレス</dt>
                                    <dd class="col-sm-8">{{ $info->email_address }}</dd>
                                </dl>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">経営情報</h6>
                                <dl class="row">
                                    <dt class="col-sm-4">代表取締役</dt>
                                    <dd class="col-sm-8">{{ $info->representative_director ?: '未設定' }}</dd>
                                    
                                    <dt class="col-sm-4">経理担当者</dt>
                                    <dd class="col-sm-8">{{ $info->accounting_person ?: '未設定' }}</dd>
                                    
                                    <dt class="col-sm-4">営業車両数</dt>
                                    <dd class="col-sm-8">{{ $info->business_vehicle_count ?: '0' }}台</dd>
                                    
                                    <dt class="col-sm-4">従業員数</dt>
                                    <dd class="col-sm-8">{{ $info->employee_count ?: '0' }}名</dd>
                                </dl>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">システム設定</h6>
                                <dl class="row">
                                    <dt class="col-sm-4">安全コスト率</dt>
                                    <dd class="col-sm-8">
                                        @if($info->safety_cost_rate)
                                            <span class="badge bg-primary">{{ number_format($info->safety_cost_rate, 1) }}%</span>
                                        @else
                                            <span class="text-muted">未設定</span>
                                        @endif
                                    </dd>
                                    
                                    <dt class="col-sm-4">営業締日</dt>
                                    <dd class="col-sm-8">
                                        @if($info->operation_closing_day)
                                            <span class="badge bg-info">{{ $info->operation_closing_day }}日</span>
                                        @else
                                            <span class="text-muted">未設定</span>
                                        @endif
                                    </dd>
                                    
                                    <dt class="col-sm-4">ドライバー重複チェック</dt>
                                    <dd class="col-sm-8">
                                        @if($info->driver_duplicate_check)
                                            <span class="badge bg-success">有効</span>
                                        @else
                                            <span class="badge bg-secondary">無効</span>
                                        @endif
                                    </dd>
                                    
                                    <dt class="col-sm-4">運行注意アラート</dt>
                                    <dd class="col-sm-8">
                                        @if($info->operation_caution_alert)
                                            <span class="badge bg-success">有効</span>
                                        @else
                                            <span class="badge bg-secondary">無効</span>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="{{ route('masters.basicinfo.edit', $info) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 基本情報を編集
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-building display-6 mb-2"></i>
                    <p class="mb-0">基本情報が登録されていません</p>
                    <p class="small">管理者にご連絡ください</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection