@extends('layouts.app')

@section('title', 'ホーム ')

@section('content')
<div class="container-fluid">
    <div class="row">
        @php
            $role = session('role', '');
            $isAdmin = $role === 'admin';
            $isOperationsManager = $role === 'operations_manager';
            $isCoordinator = $role === 'coordinator';
            $isManager = $role === 'manager';
            $isDriver = $role === 'driver';
            
            $canViewOperations = $isAdmin || $isOperationsManager || $isCoordinator || $isManager;
            $canViewSales = $isAdmin || $isOperationsManager || $isManager;
            $canViewResults = $isAdmin || $isOperationsManager || $isManager;
            $canViewMaster = $isAdmin || $isOperationsManager;
            $canViewPayment = $isAdmin || $isManager; 
            $canViewAccounting = $isAdmin || $isManager; 
        @endphp

        @if($canViewOperations)
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-truck me-1"></i>運行管理</h6>
                </div>
                <div class="card-body p-2">
                    @if($isAdmin || $isOperationsManager || $isCoordinator || $isManager)
                    <a href="{{ route('masters.operation-ledger.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">運行台帳</a>
                    <a href="{{ route('masters.driver-ledger.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">運転手台帳</a>
                    <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">運行一覧</a>
                    <a href="{{ route('masters.group-infos.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">予約一覧</a>
                    <a href="{{ route('masters.daily-reports.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">運行日報</a>
                    <!--<a href="{{ route('masters.daily-itineraries.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">日次一覧</a>-->
                    <a href="{{ route('masters.options.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">オプション</a>
                    <a href="{{ route('masters.driver-payment-methods.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">支払方法</a>
                    <a href="{{ route('masters.driver-expense-types.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">経費種別</a>
                    <a href="{{ route('masters.driver-compensation-types.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">報酬種別</a>
                    <a href="{{ route('masters.driver-compensations.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">運転手精算</a>
                    <a href="{{ route('masters.attendance-categories.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">運転手勤怠</a>
                    <a href="{{ route('masters.driver-vehicle-check-items.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">点検項目</a>
                    <a href="{{ route('masters.driver-operation-status.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">操作ステータス</a>
                    @endif
                </div>
            </div>
        </div>
        @endif
        
        @if($canViewPayment)
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-cash-stack me-1"></i>売上管理</h6>
                </div>
                <div class="card-body p-2">
                    @if($isAdmin || $isOperationsManager || $isManager)
                        <a class="btn btn-outline-secondary btn-sm w-100 mb-1" href="{{ route('masters.invoices.index') }}">請求一覧</a>
                        <a class="btn btn-outline-secondary btn-sm w-100 mb-1" href="{{ route('masters.payments.index') }}">入金管理</a>
                        <a class="btn btn-outline-secondary btn-sm w-100 mb-1" href="{{ route('masters.products.index') }}">請求項目設定</a>
                        <a class="btn btn-outline-secondary btn-sm w-100 mb-1" href="{{ route('masters.currencies.index') }}">為替レート</a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($canViewAccounting && session("enable_accounting") == 1)
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-calculator me-1"></i>会計システム</h6>
                </div>
                <div class="card-body p-2">
                    @if($canViewAccounting)
                        <a href="{{ route('masters.account-periods.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">周期</a>
                        <!-- 仕訳帳 -->
                        <a href="{{ route('masters.journal_entries.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">仕訳帳</a>
                        
                        <!-- 現金出納帳 -->
                        <a href="{{ route('masters.account-cash.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">現金出納帳</a>
                        
                        <!-- 預金出納帳 -->
                        <a href="{{ route('masters.account-deposit.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">預金出納帳</a>
                        
                        <!-- 売掛帳 -->
                        <a href="{{ route('masters.products.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">売掛帳</a>
                        
                        <!-- 買掛帳 -->
                        <a href="{{ route('masters.products.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">買掛帳</a>
                        
                        <!-- 勘定元帳 -->
                        <a href="{{ route('masters.account-ledgers.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">勘定元帳</a>

                        <a href="{{ route('masters.account-month-sums.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">月次決算</a>
                        <a href="{{ route('masters.account-sums.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">試算表</a>
                        
                        <!-- 貸借対照表 -->
                        <a href="{{ route('masters.account-bs.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">貸借対照表</a>
                        
                        <!-- 損益計算書 -->
                        <a href="{{ route('masters.account-pl.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">損益計算書</a>

                        <a href="{{ route('masters.account-cash-ins.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">現金入力</a>
                        <a href="{{ route('masters.account-cash-outs.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">現金出力</a>
                        
                        <!-- キャッシュ・フロー計算書 -->
                        <a href="{{ route('masters.products.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start">キャッシュ・フロー計算書</a>
                        
                        <!-- マスタ管理 (会计专用) -->
                                            <button class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start d-flex justify-content-between align-items-center" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#accountingMasterCollapse" 
                            aria-expanded="false" 
                            aria-controls="accountingMasterCollapse">
                        <span><i class="bi bi-folder2-open me-2"></i>マスタ管理</span>
                        <i class="bi bi-chevron-down small transition-icon"></i>
                    </button>

                    <!-- 2. 折叠内容区域 (默认隐藏) -->
                    <div class="collapse" id="accountingMasterCollapse">
                        <div class="ps-3 border-start ms-2 my-1">
                            <a href="{{ route('masters.account-categories.index') }}" class="btn btn-light btn-sm w-100 mb-1 text-start" style="font-size: 0.85rem; border: 1px solid #dee2e6;">
                                区分
                            </a>

                            <!-- 税区分 -->
                            <a href="{{ route('masters.account-taxs.index') }}" class="btn btn-light btn-sm w-100 mb-1 text-start" style="font-size: 0.85rem; border: 1px solid #dee2e6;">
                                税区分
                            </a>
                            <!-- 税区分 -->
                            <a href="{{ route('masters.account-departments.index') }}" class="btn btn-light btn-sm w-100 mb-1 text-start" style="font-size: 0.85rem; border: 1px solid #dee2e6;">
                                部门
                            </a>

                            <!-- 取引先 -->
                            <a href="{{ route('masters.account_partners.index') }}" class="btn btn-light btn-sm w-100 mb-1 text-start" style="font-size: 0.85rem; border: 1px solid #dee2e6;">
                                取引先
                            </a>
                            <!-- 勘定科目 -->
                            <a href="{{ route('masters.accounts.index') }}" class="btn btn-light btn-sm w-100 mb-1 text-start" style="font-size: 0.85rem; border: 1px solid #dee2e6;">
                                勘定科目
                            </a>
                            
                            <!-- 補助科目 -->
                            <a href="{{ route('masters.account-subs.index') }}" class="btn btn-light btn-sm w-100 mb-1 text-start" style="font-size: 0.85rem; border: 1px solid #dee2e6;">
                                補助科目
                            </a>

                            
                            <!-- 部署 -->
                            <!-- <a href="{{ route('masters.products.index') }}" class="btn btn-light btn-sm w-100 mb-1 text-start" style="font-size: 0.85rem; border: 1px solid #dee2e6;">
                                部署
                            </a> -->
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        
        @if($canViewResults)
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-graph-up-arrow me-1"></i>集計</h6>
                </div>
                <div class="card-body p-2">
                    @if($isAdmin || $isOperationsManager || $isManager)
                    <a href="{{ route('masters.vehicle-performance.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">車両実績</a>
                    <a href="{{ route('masters.driver-performance.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">運転手実績</a>
                    <a href="{{ route('masters.fees.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">売上集計</a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($canViewMaster)
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-database-gear me-1"></i>マスター管理</h6>
                </div>
                <div class="card-body p-2">
                    @if($isAdmin || $isOperationsManager)
                        <a href="{{ route('masters.user-company-info.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">基本情報</a>
                        <a href="{{ route('masters.branches.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">営業所</a>
                        <a href="{{ route('masters.staffs.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">スタッフ</a>
                        <a href="{{ route('masters.vehicles.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">車両</a>
                        <a href="{{ route('masters.drivers.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">運転手</a>
                        <a href="{{ route('masters.friends.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">友達</a>
                        <a href="{{ route('masters.guides.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">ガイド</a>
                        <a href="{{ route('masters.agencies.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">代理店</a>
                        <a href="{{ route('masters.partners.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">取引先</a>
                        <a href="{{ route('masters.itineraries.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">行程</a>
                        <a href="{{ route('masters.locations.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">場所施設</a>
                        <a href="{{ route('masters.reservation-categories.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">予約分類</a>
                        <a href="{{ route('masters.attendance-categories.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">勤怠分類</a>
                        <a href="{{ route('masters.remarks.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">備考</a>
                        <a href="{{ route('masters.banks.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">Bank</a>
                        <a href="{{ route('masters.vehicle-types.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">車両種類</a>
                        <a href="{{ route('masters.vehicle-grades.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-1">車両グレード</a>
                        
                        <div class="dropdown-divider my-1"></div>
                        <a href="{{ route('masters.login-histories.index') }}" class="btn btn-outline-secondary btn-sm w-100">ログイン履歴</a>
                    @endif
                </div>
            </div>
        </div>
        @endif
        
        @if($isDriver && !$canViewOperations && !$canViewSales && !$canViewResults && !$canViewMaster)
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 運転手メニューは現在準備中です。
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-outline-secondary.opacity-75 {
        opacity: 0.75;
    }
    .btn-outline-secondary.opacity-75:hover {
        opacity: 1;
    }
</style>
@endpush