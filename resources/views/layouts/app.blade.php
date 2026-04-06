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
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/ja.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            overflow-x: hidden;
            background-color: #f5f7fa;
        }

        .no-transition,
        .no-transition * {
            transition: none !important;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: linear-gradient(180deg, #1a2530 0%, #0f1720 100%);
            color: #e2e8f0;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-x: hidden;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #2d3748;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 4px;
        }

        .sidebar.expanded {
            width: 180px;
        }

        .sidebar.collapsed {
            width: 54px;
        }

        .sidebar-logo {
            height: 60px;
            padding: 10px;
            border-bottom: 1px solid #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-logo img {
            max-width: 100%;
            max-height: 40px;
            object-fit: contain;
        }

        .sidebar.expanded .sidebar-logo img {
            width: auto;
        }

        .sidebar.collapsed .sidebar-logo img {
            width: 34px;
        }

        .sidebar-nav {
            padding: 0 12px;
        }
        
        .sidebar-nav a { text-decoration: none; }

        .sidebar.collapsed .sidebar-nav {
            padding: 0 8px;
        }

        .nav-item {
            margin-bottom: 4px;
        }

        .nav-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s;
            color: #cbd5e1;
        }
        
        .nav-item-home {
            margin-bottom: 20px;
        }
        
        .nav-header-home {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #cbd5e1;
            transition: all 0.2s;
            border-radius: 8px;
        }
        
        .sidebar.expanded .nav-header-home {
            padding: 8px 10px;
        }
        
        .sidebar.collapsed .nav-header-home {
            padding: 8px 8px;
            justify-content: center;
        }
        
        .nav-header-home:hover {
            background-color: #2d3748;
            color: #fff;
        }
        
        .nav-header-home.active {
            background-color: #2563eb;
            color: #fff;
        }
        
        .nav-header-home .menu-icon {
            font-size: 18px;
            min-width: 24px;
            text-align: center;
        }
        
        .nav-header-home .menu-title {
            margin-left: 10px;
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .sidebar.collapsed .nav-header-home .menu-title {
            display: none;
        }

        .sidebar.expanded .nav-header {
            padding: 8px 10px;
            border-radius: 8px;
        }

        .sidebar.collapsed .nav-header {
            padding: 8px 8px;
            border-radius: 8px;
        }

        .nav-header:hover {
            background-color: #2d3748;
            color: #fff;
        }

        .nav-header.active {
            background-color: #2563eb;
            color: #fff;
        }

        .nav-header .menu-icon {
            font-size: 18px;
            min-width: 24px;
            text-align: center;
        }

        .nav-header .menu-title {
            flex: 1;
            margin-left: 10px;
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-header .menu-title {
            display: none;
        }

        .nav-header .menu-arrow {
            font-size: 11px;
            transition: transform 0.2s;
        }

        .sidebar.collapsed .nav-header .menu-arrow {
            display: none;
        }

        .nav-header.open .menu-arrow {
            transform: rotate(180deg);
        }

        .submenu {
            list-style: none;
            max-height: 0;
            transition: max-height 0.3s ease;
            overflow: hidden;
            display: block;
        }

        .submenu li {
            margin-bottom: 2px;
        }

        .submenu a {
            display: flex;
            align-items: center;
            border-radius: 6px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.2s;
        }

        .submenu a:hover {
            background-color: #2d3748;
            color: #fff;
        }

        .submenu a.active {
            background-color: #2563eb;
            color: #fff;
        }

        .sidebar.expanded .submenu {
            padding-left: 28px;
            overflow: hidden;
            display: block;
        }

        .sidebar.expanded .submenu.show {
            max-height: 500px;
        }

        .sidebar.expanded .submenu a {
            padding: 6px 10px;
        }

        .sidebar.collapsed .submenu {
            position: fixed;
            left: 62px;
            background: #1e293b;
            border-radius: 8px;
            padding: 8px 0;
            min-width: 200px;
            max-height: calc(100vh - 20px);
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1001;
            display: none;
        }

        .sidebar.collapsed .submenu-s {
            padding: 8px !important;
            overflow: visible !important;
        }

        .sidebar.collapsed .submenu.show {
            display: block;
        }

        .sidebar.collapsed .submenu a {
            white-space: nowrap;
            padding: 8px 16px;
        }

        .sidebar.collapsed .submenu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar.collapsed .submenu::-webkit-scrollbar-track {
            background: #2d3748;
            border-radius: 3px;
            margin: 10px 0;
        }

        .sidebar.collapsed .submenu::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 3px;
        }

        .sidebar.collapsed .submenu::-webkit-scrollbar-thumb:hover {
            background: #60a5fa;
        }

        .sidebar.collapsed .submenu {
            scrollbar-width: thin;
            scrollbar-color: #4a5568 #2d3748;
            padding-right: 10px;
        }

        .sidebar.collapsed .submenu::before {
            content: '';
            position: absolute;
            left: -8px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 6px 8px 6px 0;
            border-color: transparent #1e293b transparent transparent;
            z-index: 1002;
            pointer-events: none;
        }

        .tooltip-arrow {
            position: fixed;
            background: #f1f5f9;
            color: #1e293b;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 1002;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            pointer-events: none;
        }

        .tooltip-arrow::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 5px 6px 5px 0;
            border-color: transparent #f1f5f9 transparent transparent;
        }

        .sidebar-toggle-outer {
            position: fixed;
            top: 12px;
            left: 186px;
            width: 24px;
            height: 24px;
            background: rgba(100, 116, 139, 0.5);
            border: none;
            border-radius: 4px;
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1001;
            backdrop-filter: blur(4px);
        }

        .sidebar-toggle-outer:hover {
            background: #475569;
            color: #fff;
            transform: scale(1.05);
        }

        .sidebar.collapsed ~ .sidebar-toggle-outer {
            left: 60px;
        }

        .sidebar-toggle-outer i {
            font-size: 12px;
        }

        .main-content {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        .main-content.expanded {
            margin-left: 180px;
        }

        .main-content.collapsed {
            margin-left: 54px;
        }

        .top-bar {
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info .user-name {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #334155;
        }

        .user-info .user-name i {
            font-size: 18px;
            color: #60a5fa;
        }

        .role-badge {
            background-color: #e2e8f0;
            color: #475569;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: normal;
        }

        .logout-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background-color: #fee2e2;
        }

        .content-wrapper {
            padding: 20px 24px;
        }

        .mobile-menu-toggle {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            .sidebar-toggle-outer {
                display: none;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            .mobile-menu-toggle {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1001;
                background: #2563eb;
                border: none;
                color: white;
                width: 40px;
                height: 40px;
                border-radius: 8px;
                font-size: 20px;
            }
            .top-bar {
                padding-left: 70px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="app-container">
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="/images/logo.jpg" alt="Logo" id="logoImg">
        </div>

        <nav class="sidebar-nav" id="sidebarNav">
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
            
            <div class="nav-item" data-menu="home">
                <div class="nav-header" data-title="ホーム" data-url="{{ route('masters.home') }}">
                    <i class="bi bi-house-door menu-icon"></i>
                    <span class="menu-title">ホーム</span>
                    <i class="bi bi-chevron-down menu-arrow" style="visibility: hidden;"></i>
                </div>
            </div>

            @if($canViewOperations)
            <div class="nav-item" data-menu="operations">
                <div class="nav-header" data-title="運行管理">
                    <i class="bi bi-truck menu-icon"></i>
                    <span class="menu-title">運行管理</span>
                    <i class="bi bi-chevron-down menu-arrow"></i>
                </div>
                <ul class="submenu submenu-s">
                    <li><a href="{{ route('masters.operation-ledger.index') }}" data-route="operation-ledger">運行台帳</a></li>
                    <li><a href="{{ route('masters.driver-ledger.index') }}" data-route="driver-ledger">運転手台帳</a></li>
                    <li><a href="{{ route('masters.bus-assignments.index') }}" data-route="bus-assignments">運行一覧</a></li>
                    <li><a href="{{ route('masters.group-infos.index') }}" data-route="group-infos">予約一覧</a></li>
                    <li><a href="{{ route('masters.daily-itineraries.index') }}" data-route="daily-itineraries">日次一覧</a></li>
                    <li><a href="{{ route('masters.drivers.index') }}" data-route="drivers">乗務指示一覧</a></li>
                    <li><hr class="dropdown-divider" style="margin: 8px 0;"></li>
                    <li><a href="{{ route('masters.basicinfo.index') }}" data-route="basicinfo">デジタコデータアップロード</a></li>
                    <li><a href="{{ route('masters.basicinfo.index') }}" data-route="basicinfo-history">アップロード履歴</a></li>
                </ul>
            </div>
            @endif

            @if($canViewSales)
            <div class="nav-item" data-menu="sales">
                <div class="nav-header" data-title="売上管理">
                    <i class="bi bi-cash-stack menu-icon"></i>
                    <span class="menu-title">売上管理</span>
                    <i class="bi bi-chevron-down menu-arrow"></i>
                </div>
                <ul class="submenu submenu-s">
                    <li><a href="{{ route('masters.products.index') }}" data-route="products">品名</a></li>
                    <li><a href="{{ route('masters.currencies.index') }}" data-route="currencies">货币汇率</a></li>
                    <li><a href="{{ route('masters.invoices.index', ['group_id' => 12]) }}" data-route="invoices">請求管理</a></li>
                    <li><a href="{{ route('masters.payments.index') }}" data-route="payments">入金管理</a></li>
                </ul>
            </div>
            @endif

            @if($canViewResults)
            <div class="nav-item" data-menu="results">
                <div class="nav-header" data-title="実績集計">
                    <i class="bi bi-graph-up-arrow menu-icon"></i>
                    <span class="menu-title">実績集計</span>
                    <i class="bi bi-chevron-down menu-arrow"></i>
                </div>
                <ul class="submenu submenu-s">
                    <li><a href="{{ route('masters.basicinfo.index') }}" data-route="basicinfo-performance">輸送実績一覧</a></li>
                    <li><a href="{{ route('masters.drivers.index') }}" data-route="drivers-performance">乗務実績一覧</a></li>
                    <li><a href="{{ route('masters.fees.index') }}" data-route="fees">売上集計</a></li>
                </ul>
            </div>
            @endif

            @if($canViewMaster)
            <div class="nav-item" data-menu="master">
                <div class="nav-header" data-title="マスター管理">
                    <i class="bi bi-database-gear menu-icon"></i>
                    <span class="menu-title">マスター管理</span>
                    <i class="bi bi-chevron-down menu-arrow"></i>
                </div>
                <ul class="submenu submenu-s">
                    <li><a href="{{ route('masters.user-company-info.index') }}" data-route="user-company-info">基本情報</a></li>
                    <li><a href="{{ route('masters.branches.index') }}" data-route="branches">営業所</a></li>
                    <li><a href="{{ route('masters.staffs.index') }}" data-route="staffs">スタッフ</a></li>
                    <li><a href="{{ route('masters.vehicles.index') }}" data-route="vehicles">車両</a></li>
                    <li><a href="{{ route('masters.drivers.index') }}" data-route="drivers-master">運転手</a></li>
                    <li><a href="{{ route('masters.guides.index') }}" data-route="guides">ガイド</a></li>
                    <li><a href="{{ route('masters.agencies.index') }}" data-route="agencies">代理店</a></li>
                    <li><a href="{{ route('masters.partners.index') }}" data-route="partners">取引先(傭車先)</a></li>
                    <li><a href="{{ route('masters.itineraries.index') }}" data-route="itineraries">行程</a></li>
                    <li><a href="{{ route('masters.reservation-categories.index') }}" data-route="reservation-categories">予約分類</a></li>
                    <li><a href="{{ route('masters.attendance-categories.index') }}" data-route="attendance-categories">勤怠分類</a></li>
                    <li><a href="{{ route('masters.remarks.index') }}" data-route="remarks">備考</a></li>
                    <li><a href="{{ route('masters.banks.index') }}" data-route="banks">Bank</a></li>
                    <li><a href="{{ route('masters.vehicle-types.index') }}" data-route="vehicle-types">車両種類</a></li>
                    <li><hr class="dropdown-divider" style="margin: 8px 0;"></li>
                    <li><a href="{{ route('masters.login-histories.index') }}" data-route="login-histories">ログイン履歴</a></li>
                </ul>
            </div>
            @endif
        </nav>
    </aside>

    <button class="sidebar-toggle-outer" id="sidebarToggleBtn">
        <i class="bi bi-chevron-left" id="toggleIcon"></i>
    </button>

    <main class="main-content" id="mainContent">
        <div class="top-bar">
            <div class="user-info">
                <div class="user-name">
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
                <form method="POST" action="{{ route('masters.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn" onclick="return confirm('ログアウトしますか？');">
                        <i class="bi bi-box-arrow-right"></i> <span>ログアウト</span>
                    </button>
                </form>
            </div>
        </div>
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>
</div>

<div class="mobile-overlay" id="mobileOverlay" style="display: none;"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
document.documentElement.classList.add('no-transition');

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const toggleIcon = document.getElementById('toggleIcon');
    const logoImg = document.getElementById('logoImg');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileOverlay = document.getElementById('mobileOverlay');
    
    let currentTooltip = null;
    let closeTimer = null;
    
    function updateLogo() {
        if (sidebar.classList.contains('collapsed')) {
            logoImg.src = '/images/logo_s.jpg';
        } else {
            logoImg.src = '/images/logo.jpg';
        }
    }
    
    function updateToggleButton() {
        if (sidebar.classList.contains('collapsed')) {
            toggleIcon.className = 'bi bi-chevron-right';
            sidebarToggleBtn.style.left = '60px';
        } else {
            toggleIcon.className = 'bi bi-chevron-left';
            sidebarToggleBtn.style.left = '186px';
        }
    }
    
    function toggleSidebar() {
        if (sidebar.classList.contains('expanded')) {
            sidebar.classList.remove('expanded');
            sidebar.classList.add('collapsed');
            mainContent.classList.remove('expanded');
            mainContent.classList.add('collapsed');
            localStorage.setItem('sidebarState', 'collapsed');
            document.querySelectorAll('.nav-item.open').forEach(item => {
                item.classList.remove('open');
                const submenu = item.querySelector('.submenu');
                if (submenu) {
                    submenu.classList.remove('show');
                    submenu.style.display = 'none';
                }
            });
            hideTooltip();
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('expanded');
            mainContent.classList.remove('collapsed');
            mainContent.classList.add('expanded');
            localStorage.setItem('sidebarState', 'expanded');
            
            document.querySelectorAll('.nav-item.open').forEach(item => {
                item.classList.remove('open');
                const submenu = item.querySelector('.submenu');
                if (submenu) {
                    submenu.classList.remove('show');
                    submenu.style.display = '';
                    submenu.style.visibility = '';
                }
            });
            
            document.querySelectorAll('.submenu').forEach(submenu => {
                submenu.style.display = '';
                submenu.style.visibility = '';
            });
            
            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }
            hideTooltip();
        }
        updateLogo();
        updateToggleButton();
        rebindNavHeaders();
        highlightCurrentMenu();
    }
    
    function showTooltip(element, text) {
        if (!sidebar.classList.contains('collapsed')) return;
        const parentItem = element.closest('.nav-item');
        if (parentItem && parentItem.classList.contains('open')) return;
        hideTooltip();
        const rect = element.getBoundingClientRect();
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip-arrow';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);
        const tooltipRect = tooltip.getBoundingClientRect();
        let left = rect.right + 12;
        let top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
        if (top + tooltipRect.height + 10 > window.innerHeight) {
            top = window.innerHeight - tooltipRect.height - 10;
        }
        if (top < 10) {
            top = 10;
        }
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
        currentTooltip = tooltip;
    }
    
    function hideTooltip() {
        if (currentTooltip) {
            currentTooltip.remove();
            currentTooltip = null;
        }
    }
    
    function showFloatingSubmenu(submenu, clickedElement) {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
        submenu.style.display = 'block';
        submenu.style.visibility = 'hidden';
        const rect = clickedElement.getBoundingClientRect();
        const submenuHeight = submenu.offsetHeight;
        const windowHeight = window.innerHeight;
        const margin = 10;
        let topPosition = rect.top;
        if (topPosition + submenuHeight + margin > windowHeight) {
            topPosition = windowHeight - submenuHeight - margin;
        }
        if (topPosition < margin) {
            topPosition = margin;
        }
        let arrowTop = rect.top - topPosition + 20;
        arrowTop = Math.max(arrowTop, 12);
        arrowTop = Math.min(arrowTop, submenuHeight - 12);
        const styleId = 'arrow-position-style';
        let styleEl = document.getElementById(styleId);
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = styleId;
            document.head.appendChild(styleEl);
        }
        styleEl.textContent = `.sidebar.collapsed .submenu.show::before { top: ${arrowTop}px; }`;
        submenu.style.top = topPosition + 'px';
        submenu.style.left = '62px';
        submenu.style.visibility = 'visible';
        hideTooltip();
    }
    
    function hideAllFloatingSubmenus() {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
        if (sidebar.classList.contains('collapsed')) {
            document.querySelectorAll('.nav-item.open').forEach(item => {
                item.classList.remove('open');
                const submenu = item.querySelector('.submenu');
                if (submenu) {
                    submenu.classList.remove('show');
                    submenu.style.display = 'none';
                    submenu.style.visibility = '';
                }
            });
        }
    }
    
    function scheduleHideSubmenus() {
        if (closeTimer) {
            clearTimeout(closeTimer);
        }
        closeTimer = setTimeout(function() {
            const activeSubmenu = document.querySelector('.sidebar.collapsed .submenu.show');
            if (activeSubmenu) {
                const hoveredElement = document.querySelector(':hover');
                const isHoveringSubmenu = activeSubmenu.contains(hoveredElement);
                const isHoveringNavHeader = hoveredElement && hoveredElement.closest('.nav-header');
                if (!isHoveringSubmenu && !isHoveringNavHeader) {
                    hideAllFloatingSubmenus();
                }
            }
            closeTimer = null;
        }, 150);
    }
    
    function highlightCurrentMenu() {
        const allLinks = document.querySelectorAll('.submenu a');
        const isCollapsed = sidebar.classList.contains('collapsed');
        const currentPath = window.location.pathname;
        const currentFullPath = window.location.href;
        const pathParts = currentPath.split('/').filter(p => p);
        let currentBasePath = currentPath;
        if (pathParts.length >= 3) {
            currentBasePath = '/' + pathParts.slice(0, 2).join('/');
        }
        
        allLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href !== '#' && href !== '') {
                let isMatch = false;
                let linkPath = href;
                if (href.startsWith('http')) {
                    try {
                        const url = new URL(href);
                        linkPath = url.pathname;
                    } catch(e) {
                        linkPath = href;
                    }
                }
                if (!linkPath.startsWith('/') && !linkPath.startsWith('http')) {
                    linkPath = '/' + linkPath;
                }
                const linkParts = linkPath.split('/').filter(p => p);
                let linkBasePath = linkPath;
                if (linkParts.length >= 3) {
                    linkBasePath = '/' + linkParts.slice(0, 2).join('/');
                }
                if (currentPath === linkPath) {
                    isMatch = true;
                } else if (currentBasePath === linkBasePath && linkBasePath !== '/') {
                    isMatch = true;
                } else if (linkPath !== '/' && currentPath.startsWith(linkPath + '/')) {
                    isMatch = true;
                } else if (currentFullPath === href) {
                    isMatch = true;
                }
                if (isMatch) {
                    link.classList.add('active');
                    if (!isCollapsed) {
                        const parentItem = link.closest('.nav-item');
                        if (parentItem) {
                            parentItem.classList.add('open');
                            const submenu = parentItem.querySelector('.submenu');
                            if (submenu) {
                                submenu.classList.add('show');
                            }
                        }
                    }
                } else {
                    link.classList.remove('active');
                }
            }
        });
    }
    
    function rebindNavHeaders() {
        const navHeaders = document.querySelectorAll('.nav-header');
        const isCollapsed = sidebar.classList.contains('collapsed');
        
        navHeaders.forEach(header => {
            header.removeEventListener('click', header._clickHandler);
            header.removeEventListener('mouseenter', header._mouseEnterHandler);
            header.removeEventListener('mouseleave', header._mouseLeaveHandler);
            
            const clickHandler = function(e) {
                e.stopPropagation();
                
                const url = this.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                    return;
                }
                
                const parentItem = this.closest('.nav-item');
                const submenu = parentItem.querySelector('.submenu');
                const isOpen = parentItem.classList.contains('open');
                
                if (sidebar.classList.contains('collapsed')) {
                    if (closeTimer) {
                        clearTimeout(closeTimer);
                        closeTimer = null;
                    }
                    if (isOpen) {
                        parentItem.classList.remove('open');
                        submenu.classList.remove('show');
                        submenu.style.display = 'none';
                    } else {
                        document.querySelectorAll('.nav-item.open').forEach(item => {
                            if (item !== parentItem) {
                                item.classList.remove('open');
                                const otherSubmenu = item.querySelector('.submenu');
                                if (otherSubmenu) {
                                    otherSubmenu.classList.remove('show');
                                    otherSubmenu.style.display = 'none';
                                }
                            }
                        });
                        parentItem.classList.add('open');
                        submenu.classList.add('show');
                        showFloatingSubmenu(submenu, this);
                    }
                } else {
                    if (isOpen) {
                        parentItem.classList.remove('open');
                        submenu.classList.remove('show');
                    } else {
                        parentItem.classList.add('open');
                        submenu.classList.add('show');
                    }
                    submenu.style.display = '';
                    submenu.style.visibility = '';
                }
            };
            
            header.addEventListener('click', clickHandler);
            header._clickHandler = clickHandler;
            
            if (isCollapsed) {
                const mouseEnterHandler = function(e) {
                    if (sidebar.classList.contains('collapsed')) {
                        if (closeTimer) {
                            clearTimeout(closeTimer);
                            closeTimer = null;
                        }
                        const parentItem = this.closest('.nav-item');
                        if (parentItem && !parentItem.classList.contains('open')) {
                            const title = this.getAttribute('data-title');
                            if (title) {
                                showTooltip(this, title);
                            }
                        }
                    }
                };
                
                const mouseLeaveHandler = function(e) {
                    if (sidebar.classList.contains('collapsed')) {
                        hideTooltip();
                        scheduleHideSubmenus();
                    }
                };
                
                header.addEventListener('mouseenter', mouseEnterHandler);
                header.addEventListener('mouseleave', mouseLeaveHandler);
                header._mouseEnterHandler = mouseEnterHandler;
                header._mouseLeaveHandler = mouseLeaveHandler;
            } else {
                if (header._mouseEnterHandler) {
                    header.removeEventListener('mouseenter', header._mouseEnterHandler);
                    header._mouseEnterHandler = null;
                }
                if (header._mouseLeaveHandler) {
                    header.removeEventListener('mouseleave', header._mouseLeaveHandler);
                    header._mouseLeaveHandler = null;
                }
                const parentItem = header.closest('.nav-item');
                const submenu = parentItem?.querySelector('.submenu');
                if (submenu) {
                    submenu.style.display = '';
                    submenu.style.visibility = '';
                }
            }
        });
        
        if (isCollapsed) {
            document.querySelectorAll('.sidebar.collapsed .submenu').forEach(submenu => {
                submenu.removeEventListener('mouseenter', submenu._enterHandler);
                submenu.removeEventListener('mouseleave', submenu._leaveHandler);
                
                const enterHandler = function() {
                    if (closeTimer) {
                        clearTimeout(closeTimer);
                        closeTimer = null;
                    }
                };
                
                const leaveHandler = function() {
                    scheduleHideSubmenus();
                };
                
                submenu.addEventListener('mouseenter', enterHandler);
                submenu.addEventListener('mouseleave', leaveHandler);
                submenu._enterHandler = enterHandler;
                submenu._leaveHandler = leaveHandler;
            });
        }
    }
    
    sidebarToggleBtn.addEventListener('click', toggleSidebar);
    
    const savedState = localStorage.getItem('sidebarState');
    if (savedState === 'collapsed') {
        sidebar.classList.add('collapsed');
        sidebar.classList.remove('expanded');
        mainContent.classList.add('collapsed');
        mainContent.classList.remove('expanded');
    } else {
        sidebar.classList.add('expanded');
        sidebar.classList.remove('collapsed');
        mainContent.classList.add('expanded');
        mainContent.classList.remove('collapsed');
    }
    updateLogo();
    updateToggleButton();
    
    setTimeout(function() {
        document.documentElement.classList.remove('no-transition');
    }, 50);
    
    rebindNavHeaders();
    highlightCurrentMenu();
    
    sidebar.addEventListener('mouseleave', function(e) {
        if (sidebar.classList.contains('collapsed')) {
            const relatedTarget = e.relatedTarget;
            const isLeavingToSubmenu = relatedTarget && relatedTarget.closest && relatedTarget.closest('.submenu');
            if (!isLeavingToSubmenu) {
                scheduleHideSubmenus();
            }
        }
    });
    
    document.addEventListener('click', function(e) {
        if (sidebar.classList.contains('collapsed')) {
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickOnToggleBtn = sidebarToggleBtn.contains(e.target);
            if (!isClickInsideSidebar && !isClickOnToggleBtn) {
                hideAllFloatingSubmenus();
                hideTooltip();
            }
        }
    });
    
    window.addEventListener('resize', function() {
        if (sidebar.classList.contains('collapsed')) {
            document.querySelectorAll('.nav-item.open .submenu').forEach(submenu => {
                const parentItem = submenu.closest('.nav-item');
                if (parentItem) {
                    const parentHeader = parentItem.querySelector('.nav-header');
                    if (parentHeader && submenu.classList.contains('show')) {
                        showFloatingSubmenu(submenu, parentHeader);
                    }
                }
            });
            hideTooltip();
        }
    });
    
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('message.processed', () => {
            setTimeout(highlightCurrentMenu, 100);
        });
    }
    
    document.addEventListener('turbolinks:load', function() {
        setTimeout(highlightCurrentMenu, 100);
    });
    
    const bodyObserver = new MutationObserver(function() {
        highlightCurrentMenu();
    });
    bodyObserver.observe(document.body, { childList: true, subtree: true });
    
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                if (sidebar.classList.contains('collapsed')) {
                    document.querySelectorAll('.nav-item.open .submenu').forEach(submenu => {
                        const parentHeader = submenu.closest('.nav-item').querySelector('.nav-header');
                        if (parentHeader && submenu.classList.contains('show')) {
                            showFloatingSubmenu(submenu, parentHeader);
                        }
                    });
                }
                updateLogo();
                updateToggleButton();
            }
        });
    });
    
    observer.observe(sidebar, { attributes: true });
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-open');
            if (mobileOverlay) mobileOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
            mobileOverlay.style.display = sidebar.classList.contains('mobile-open') ? 'block' : 'none';
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        });
    }
    
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.style.display = 'none';
            document.body.style.overflow = '';
        });
    }
});
</script>
@stack('scripts')
</body>
</html>