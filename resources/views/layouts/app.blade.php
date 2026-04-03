<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        運行管理システム
        @if(View::hasSection('title'))
            - @yield('title')
        @endif
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    
    <script src="{{ asset('js/date-range-picker.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/datepicker.css') }}">

    <style>
        .navbar {
            background-color: #212529;
        }
        
        .navbar-collapse {
            justify-content: center;
        }

        .nav-item {
            margin: 0 10px;
        }

        .nav-link {
            font-weight: 500;
            white-space: nowrap;
        }

        .mega-menu {
            min-width: 450px;
            padding: 1.5rem;
            left: 50% !important;
            transform: translateX(-50%) !important;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-bg-type: transparent;
        }
        .table-striped > tbody > tr:nth-of-type(even) > * {
            --bs-table-bg-type: #f1f1f1;
        }
        .table-hover-f1 > tbody > tr:hover > * {
            background-color: #f1f1f1 !important;
        }
        .card-header:first-child {
            background-color: #1f3241 !important;
        }
        .user-info {
            color: #fff;
            margin-right: 15px;
            padding: 5px 10px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        .user-info i {
            margin-right: 5px;
            color: #ffc107;
        }
        .role-badge {
            background-color: #0dcaf0;
            color: #000;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
            font-weight: normal;
        }
        .nav-home {
            margin: 0 10px;
        }
        .nav-home .nav-link {
            color: rgba(255,255,255,.55);
            padding: 0.5rem 1rem;
            text-decoration: none;
            background: transparent;
            border: none;
            font-weight: 500;
            white-space: nowrap;
        }
        .nav-home .nav-link:hover {
            color: rgba(255,255,255,.75);
        }
        .nav-home .nav-link i {
            margin-right: 0.25rem;
        }
    </style>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/ja.min.js"></script>
    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-xl navbar-dark shadow">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-xl-none" href="{{ route('masters.home') }}">運行管理</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNavbar">
                <div class="nav-home d-flex align-items-center">
                    <a class="nav-link" href="{{ route('masters.home') }}">
                        <i class="bi bi-house-door"></i> ホーム
                    </a>
                </div>
                
                <ul class="navbar-nav">
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
                    @endphp

                    @if($canViewOperations)
                    <li class="nav-item dropdown text-center">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-truck me-1"></i>運行管理
                        </a>
                        <ul class="dropdown-menu shadow">
                            @if($isAdmin || $isOperationsManager || $isCoordinator || $isManager)
                            <li><a class="dropdown-item" href="{{ route('masters.operation-ledger.index') }}">運行台帳</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager || $isCoordinator || $isManager)
                            <li><a class="dropdown-item" href="{{ route('masters.driver-ledger.index') }}">運転手台帳</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager || $isCoordinator || $isManager)
                            <li><a class="dropdown-item" href="{{ route('masters.bus-assignments.index') }}">運行一覧</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager || $isCoordinator || $isManager)
                            <li><a class="dropdown-item" href="{{ route('masters.group-infos.index') }}">予約一覧</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager || $isCoordinator || $isManager)
                            <li><a class="dropdown-item" href="{{ route('masters.daily-itineraries.index') }}">日次一覧</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager || $isCoordinator || $isManager)
                            <li><a class="dropdown-item" href="{{ route('masters.drivers.index') }}">乗務指示一覧</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager || $isCoordinator || $isManager)
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('masters.basicinfo.index') }}">デジタコデータアップロード</a></li>
                            <li><a class="dropdown-item" href="{{ route('masters.basicinfo.index') }}">アップロード履歴</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                    
                    @if($canViewSales)
                    <li class="nav-item dropdown text-center">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-cash-stack me-1"></i>売上管理
                        </a>
                        <ul class="dropdown-menu shadow">
                            @if($isAdmin || $isOperationsManager || $isManager)
                            <li><a class="dropdown-item" href="{{ route('masters.products.index') }}">品名</a></li>
                            <li><a class="dropdown-item" href="{{ route('masters.currencies.index') }}">货币汇率</a></li>
                            <li><a class="dropdown-item" href="{{ route('masters.invoices.index', ['group_id' => 12]) }}">請求管理</a></li>
                            <li><a class="dropdown-item" href="{{ route('masters.payments.index') }}">入金管理</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                    
                    @if($canViewResults)
                    <li class="nav-item dropdown text-center">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-graph-up-arrow me-1"></i>実績集計
                        </a>
                        <ul class="dropdown-menu shadow">
                    @if($isAdmin || $isOperationsManager || $isManager)
                            <li><a class="dropdown-item" href="{{ route('masters.basicinfo.index') }}">輸送実績一覧</a></li>
                            <li><a class="dropdown-item" href="{{ route('masters.drivers.index') }}">乗務実績一覧</a></li>
                            <li><a class="dropdown-item" href="{{ route('masters.fees.index') }}">売上集計</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if($canViewMaster)
                    <li class="nav-item dropdown text-center">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-database-gear me-1"></i>マスター管理
                        </a>
                        <ul class="dropdown-menu shadow">
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.user-company-info.index') }}">基本情報</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.branches.index') }}">営業所</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.staffs.index') }}">スタッフ</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.vehicles.index') }}">車両</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.drivers.index') }}">運転手</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.guides.index') }}">ガイド</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.agencies.index') }}">代理店</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.partners.index') }}">取引先(傭車先)</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.itineraries.index') }}">行程</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.reservation-categories.index') }}">予約分類</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.attendance-categories.index') }}">勤怠分類</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.remarks.index') }}">備考</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.banks.index') }}">Bank</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <li><a class="dropdown-item py-1" href="{{ route('masters.vehicle-types.index') }}">車両種類</a></li>
                            @endif
                            @if($isAdmin || $isOperationsManager)
                            <hr class="dropdown-divider">
                            <li><a class="dropdown-item py-1 small" href="{{ route('masters.login-histories.index') }}">ログイン履歴</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                </ul>
                
                <div class="d-xl-flex align-items-center ms-auto">
                    <div class="user-info d-flex align-items-center">
                        <i class="bi bi-person-circle"></i>
                        <span>{{ session('staff_name', 'ゲスト') }}</span>
                        <span class="role-badge">
                            @php
                                $roleNames = [
                                    'admin' => '管理者',
                                    'operations_manager' => '運行管理者',
                                    'coordinator' => '運行手配',
                                    'manager' => '経理',
                                    'driver' => '運転手',
                                    'staff' => '一般スタッフ'
                                ];
                            @endphp
                            {{ $roleNames[$role] ?? $role }}
                        </span>
                    </div>
                    
                    <form method="POST" action="{{ route('masters.logout') }}" class="logout-form" onsubmit="return confirm('ログアウトしますか？');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-box-arrow-right"></i> ログアウト
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 mb-4 px-4">
        @yield('content')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>