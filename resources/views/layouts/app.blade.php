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
    <link rel="icon" type="image/svg+xml" href="/images/favicon.svg">
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
        
        .bg-primary {
            background-color: #141c28 !important;
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
            z-index: 1001;
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
            max-height: 1000px;
        }

        .sidebar.expanded .submenu a {
            padding: 6px 10px;
        }

        .sidebar.collapsed .submenu {
            position: fixed;
            left: 54px;
            top: 0;
            margin-top: -1px;
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
            top: 20px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 6px 8px 6px 0;
            border-color: transparent #1e293b transparent transparent;
            z-index: 1002;
            pointer-events: none;
        }

        .submenu .submenu-level2 {
            list-style: none;
            padding-left: 20px;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .submenu .submenu-level2.show {
            max-height: 300px;
            overflow: visible !important;
        }

        .submenu .submenu-level2 li {
            margin-bottom: 2px;
        }

        .submenu .submenu-level2 a {
            padding: 5px 10px !important;
            font-size: 11px;
        }

        .submenu .has-submenu {
            position: relative;
        }

        .submenu .has-submenu > a {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .submenu .has-submenu > a i {
            margin-right: 8px;
        }

        .submenu .has-submenu > a::after {
            content: '\f282';
            font-family: 'bootstrap-icons';
            font-size: 10px;
            transition: transform 0.2s;
            margin-left: auto;
        }

        .submenu .has-submenu.open > a::after {
            transform: rotate(90deg);
        }

        .sidebar.collapsed .submenu .submenu-level2 {
            position: fixed;
            left: 268px !important;
            background: #1e293b;
            border-radius: 8px;
            padding: 8px 0;
            min-width: 180px;
            max-height: calc(100vh - 20px);
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1002;
            display: none;
        }
        
        .sidebar.collapsed .submenu .submenu-level2.show {
            display: block;
        }
        
        .sidebar.collapsed .submenu .submenu-level2 a {
            white-space: nowrap;
            padding: 8px 16px;
        }
        
        .sidebar.collapsed .submenu .submenu-level2 a i {
            margin-right: 8px;
        }
        
        .sidebar.collapsed .submenu .submenu-level2::before {
            content: '';
            position: absolute;
            left: -8px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 6px 8px 6px 0;
            border-color: transparent #1e293b transparent transparent;
            z-index: 1003;
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
            background: #e2e8f0;
            border: none;
            border-radius: 4px;
            color: #666;
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
            justify-content: space-between;
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
            padding: 20px 0;
        }

        .mobile-menu-toggle {
            display: none;
        }
        
        
        
        
        

        .flatpickr-calendar {
            border: 1px solid #ddd !important;
            border-radius: 6px !important;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12) !important;
            font-family: inherit !important;
            font-size: 11px !important;
            overflow: hidden !important;
        }
        
        .flatpickr-calendar.multiMonth {
            width: 516px !important;
            max-width: 95vw !important;
        }
        
        .flatpickr-calendar.multiMonth .flatpickr-innerContainer {
            width: 100% !important;
        }
        
        .flatpickr-calendar.multiMonth .flatpickr-months {
            display: flex !important;
        }
        
        .flatpickr-calendar.multiMonth .flatpickr-month {
            flex: 1 !important;
        }
        
        .flatpickr-calendar.multiMonth .flatpickr-month:not(:last-child) {
            border-right: 1px solid #e9ecef !important;
        }
        
        .flatpickr-months {
            background: linear-gradient(135deg, #1f3241 0%, #2d4a5e 100%) !important;
            border-radius: 6px 6px 0 0 !important;
            display: flex !important;
        }
        
        .flatpickr-month {
            height: 28px !important;
            padding-right: 0 !important;
        }
        
        .flatpickr-current-month {
            padding: 3px 0 0 0 !important;
        }
        
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            font-weight: 600 !important;
            color: #fff !important;
            font-size: 11px !important;
        }
        
        .flatpickr-current-month .numInputWrapper span {
            color: #fff !important;
        }
        
        .flatpickr-current-month input.cur-year {
            color: #fff !important;
            font-weight: 600 !important;
            font-size: 11px !important;
        }
        
        .flatpickr-months .flatpickr-month,
        .flatpickr-months .flatpickr-next-month,
        .flatpickr-months .flatpickr-prev-month {
            color: #fff !important;
            fill: #fff !important;
        }
        
        .flatpickr-months .flatpickr-next-month:hover svg,
        .flatpickr-months .flatpickr-prev-month:hover svg {
            fill: #ffc107 !important;
        }
        
        .flatpickr-months .flatpickr-next-month,
        .flatpickr-months .flatpickr-prev-month {
            width: 20px !important;
            height: 20px !important;
            padding: 2px !important;
        }
        
        .flatpickr-weekdays {
            background: #f8f9fa !important;
            border-bottom: 1px solid #e9ecef !important;
            margin: 0 !important;
        }
        
        .flatpickr-weekday {
            color: #495057 !important;
            font-weight: 600 !important;
            font-size: 10px !important;
            padding: 1px 0 !important;
        }
        
        .flatpickr-days {
            border: none !important;
            padding: 0 !important;
        }
        
        .flatpickr-day {
            color: #374151 !important;
            border-radius: 2px !important;
            margin: 0 !important;
            border: 1px solid transparent !important;
            max-width: 24px !important;
            width: 24px !important;
            height: 22px !important;
            line-height: 20px !important;
            font-size: 10px !important;
        }
        
        .flatpickr-day:hover {
            background: #e0f2fe !important;
            border-color: #2563eb !important;
            color: #2563eb !important;
        }
        
        .flatpickr-day.selected {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
            font-weight: 600 !important;
        }
        
        .flatpickr-day.selected:hover {
            background: #1d4ed8 !important;
        }
        
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }
        
        .flatpickr-day.inRange {
            background: #dbeafe !important;
            border-color: transparent !important;
            color: #1e40af !important;
        }
        
        .flatpickr-day.today {
            border-color: #ffc107 !important;
            background: #fffbeb !important;
            color: #374151 !important;
        }
        
        .flatpickr-day.today:hover {
            background: #fef3c7 !important;
            border-color: #f59e0b !important;
            color: #374151 !important;
        }
        
        .flatpickr-months .flatpickr-month {
            background: transparent !important;
        }
        
        span.flatpickr-weekday {
            background: #f8f9fa !important;
        }
        
        .flatpickr-calendar.showTimeInput.hasTime .flatpickr-time {
            border-top: 1px solid #e9ecef !important;
        }
        
        .flatpickr-calendar.multiMonth .dayContainer {
            width: 168px !important;
            min-width: 168px !important;
            max-width: 168px !important;
            position: relative !important;
        }
        
        .month-wrapper {
            flex: 1 !important;
            position: relative !important;
            padding: 2px !important;
            height: 135px !important;
        }
        
        .month-wrapper:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 1px;
            background-color: #e9ecef;
        }
        
        .flatpickr-calendar.multiMonth .flatpickr-days {
            display: flex !important;
            position: relative;
            width: 514px !important;
        }
        
        .flatpickr-calendar.multiMonth .flatpickr-days .dayContainer {
            padding: 0 !important;
        }
        
        .flatpickr-calendar.multiMonth .flatpickr-rContainer {
            width: 514px !important;
        }
        
        
        .flatpickr-day.start-range-highlight,
        .flatpickr-day.start-range-highlight.flatpickr-disabled,
        .flatpickr-day.start-range-highlight.today {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
            font-weight: 600 !important;
            border-radius: 4px !important;
        }
        
        .flatpickr-day.end-range-highlight,
        .flatpickr-day.end-range-highlight.flatpickr-disabled,
        .flatpickr-day.end-range-highlight.today {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
            font-weight: 600 !important;
            border-radius: 4px !important;
        }
        
        
        .table-list {
            border: 1px solid #E5E7EB !important;
        }
        
        .table-list th, .table-list td {
            padding: 0.2rem 0.2rem !important;
            vertical-align: middle;
            border-color: #E5E7EB;
            color: #111827;
            font-size: 0.8rem;
        }
        
        .table-list thead th {
            border-bottom-width: 1px;
            font-weight: 500;
            background-color: #F3F4F6;
            color: #374151;
            white-space: nowrap;
        }
        
        .table-list tr th,
        .table-list tr td{
            text-align: center !important;
        }
        
        .table-list tr th div,
        .table-list tr td div {
            justify-content: center!important;
        }
        
        .table-list tr th:first-child,
        .table-list tr td:first-child {
            width: 60px;
        }
        
        .table-list tr th:last-child,
        .table-list tr td:last-child {
            width: 150px;
        }
        
        .table-list>tbody>tr:nth-of-type(odd)>* {
            --bs-table-color-type: white;
            --bs-table-bg-type: white;
        }
        .table-list>tbody>tr:nth-of-type(even)>* {
            --bs-table-color-type: #F3F4F6;
            --bs-table-bg-type: #F3F4F6;
        }
        
        .table-list tbody tr:hover td,
        .table-list tbody tr:hover th {
            --bs-table-color-type: #d8e1e9 !important;
            --bs-table-bg-type: #d8e1e9 !important;
            cursor: pointer !important;
            position: relative !important;
            z-index: 1 !important;
            font-weight: 500 !important;
        }
        
        .table-list>.btn-group-sm>.btn, .btn-sm {
            --bs-btn-padding-y: 0.1rem !important;
            --bs-btn-padding-x: 0.3rem !important;
        }
        
        .pagination {
            margin-bottom: 0;
            gap: 2px;
        }
        
        .pagination .page-link {
            color: #374151;
            border-color: #E5E7EB;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #2563eb;
            color: white;
        }
        
        
        
        .card-edit {
            font-size: 0.85rem;
            background-color: #F9FAFB;
        }
        .card-edit input,
        .card-edit select,
        .card-edit textarea {
            padding: 0.1rem 0.3rem;
            font-size: 0.85rem;
        }
        .card-edit input:focus,
        .card-edit textarea:focus {
            border-color: #0d6efd;
            box-shadow: none;
        }
        .card-edit .form-label {
            margin-bottom: 0.1rem;
        }
        .card-edit .row,
        .card-edit .col-md-6 {
            margin-bottom: 4px;
        }
        .card-edit .input-group-text {
            padding: 0 10px;
            font-size: 0.85rem;
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
                justify-content: flex-end;
            }
            
            .top-bar .btn-list {
                display: none !important;
            }
            
            
            .logout-btn span {
                display: none;
            }
            
            .logout-btn i {
                font-size: 18px;
                margin: 0;
            }
            
            .logout-btn {
                padding: 6px 10px;
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
            <img src="/images/logo.svg" alt="Logo" id="logoImg">
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
                $canViewAccounting = $isAdmin || $isManager; 
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
                    <li><a href="{{ route('masters.group-infos.index') }}" data-route="group-infos">予約一覧</a></li>
                    <li><a href="{{ route('masters.bus-assignments.index') }}" data-route="bus-assignments">運行一覧</a></li>
                    <li><a href="{{ route('masters.daily-reports.index') }}" data-route="daily-reports">运行日报</a></li>
                    <li><a href="{{ route('masters.drivers.index') }}" data-route="drivers-operations">乗務指示一覧</a></li>
                    <li><a href="{{ route('masters.daily-itineraries.index') }}" data-route="daily-itineraries">日次一覧</a></li>
                    <li><a href="{{ route('masters.attendance-categories.index') }}" data-route="attendance-categories-operations">運転手勤怠</a></li>
                    <li><a href="{{ route('masters.options.index') }}" data-route="options">オプション</a></li>
                    <li><a href="{{ route('masters.driver-payment-methods.index') }}" data-route="driver-payment-methods">支払方法</a></li>
                    <li><a href="{{ route('masters.driver-expense-types.index') }}" data-route="driver-expense-types">経費種別</a></li>
                    <li><a href="{{ route('masters.driver-compensation-types.index') }}" data-route="driver-compensation-types">報酬種別</a></li>
                    <li><a href="{{ route('masters.driver-compensations.index') }}" data-route="driver-compensations">ドライバー報酬</a></li>
                    <li><a href="{{ route('masters.driver-operation-status.index') }}" data-route="driver-operation-status">操作ステータス</a></li>
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
                    <li><a href="{{ route('masters.invoices.index', ['group_id' => 12]) }}" data-route="invoices">請求一覧</a></li>
                    <li><a href="{{ route('masters.payments.index') }}" data-route="payments">入金管理</a></li>
                    <li><a href="{{ route('masters.products.index') }}" data-route="products-sales">請求項目設定</a></li>
                    <li><a href="{{ route('masters.currencies.index') }}" data-route="currencies">為替レート</a></li>
                </ul>
            </div>
            @endif

            @if($canViewResults)
            <div class="nav-item" data-menu="results">
                <div class="nav-header" data-title="実績管理">
                    <i class="bi bi-graph-up-arrow menu-icon"></i>
                    <span class="menu-title">実績管理</span>
                    <i class="bi bi-chevron-down menu-arrow"></i>
                </div>
                <ul class="submenu submenu-s">
                    <li><a href="{{ route('masters.basicinfo.index') }}" data-route="basicinfo-performance">運行実績</a></li>
                    <li><a href="{{ route('masters.drivers.index') }}" data-route="drivers-performance">乗務実績</a></li>
                    <li><a href="{{ route('masters.fees.index') }}" data-route="fees">売上集計</a></li>
                </ul>
            </div>
            @endif

            @if($canViewMaster)
            <div class="nav-item" data-menu="master">
                <div class="nav-header" data-title="マスタ管理">
                    <i class="bi bi-database-gear menu-icon"></i>
                    <span class="menu-title">マスタ管理</span>
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
                    <li><a href="{{ route('masters.partners.index') }}" data-route="partners">取引先</a></li>
                    <li><a href="{{ route('masters.itineraries.index') }}" data-route="itineraries">行程</a></li>
                    <li><a href="{{ route('masters.reservation-categories.index') }}" data-route="reservation-categories">予約分類</a></li>
                    <li><a href="{{ route('masters.attendance-categories.index') }}" data-route="attendance-categories-master">勤怠分類</a></li>
                    <li><a href="{{ route('masters.remarks.index') }}" data-route="remarks">備考</a></li>
                    <li><a href="{{ route('masters.banks.index') }}" data-route="banks">銀行</a></li>
                    <li><a href="{{ route('masters.vehicle-types.index') }}" data-route="vehicle-types">車両種類</a></li>
                    <li><a href="{{ route('masters.vehicle-grades.index') }}" data-route="vehicle-grade">車両グレード</a></li>
                    <li><a href="{{ route('masters.login-histories.index') }}" data-route="login-histories">ログイン履歴</a></li>
                </ul>
            </div>
            @endif

            @if($canViewAccounting)
            <div class="nav-item" data-menu="accounting">
                <div class="nav-header" data-title="会計システム">
                    <i class="bi bi-calculator menu-icon"></i>
                    <span class="menu-title">会計システム</span>
                    <i class="bi bi-chevron-down menu-arrow"></i>
                </div>
                <ul class="submenu submenu-s">
                    <li><a href="{{ route('masters.account-periods.index') }}" data-route="account-periods">周期</a></li>
                    <li><a href="{{ route('masters.journal_entries.index') }}" data-route="journal-entries">仕訳帳</a></li>
                    <li><a href="{{ route('masters.account-cash.index') }}" data-route="account-cash">現金出納帳</a></li>
                    <li><a href="{{ route('masters.account-deposit.index') }}" data-route="account-deposit">預金出納帳</a></li>
                    <li><a href="{{ route('masters.products.index') }}" data-route="products-receivable">売掛帳</a></li>
                    <li><a href="{{ route('masters.products.index') }}" data-route="products-payable">買掛帳</a></li>
                    <li><a href="{{ route('masters.account-ledgers.index') }}" data-route="account-ledgers">勘定元帳</a></li>
                    <li><a href="{{ route('masters.account-month-sums.index') }}" data-route="account-month-sums">月次決算</a></li>
                    <li><a href="{{ route('masters.account-sums.index') }}" data-route="account-sums">試算表</a></li>
                    <li><a href="{{ route('masters.account-bs.index') }}" data-route="account-bs">貸借対照表</a></li>
                    <li><a href="{{ route('masters.account-pl.index') }}" data-route="account-pl">損益計算書</a></li>
                    <li><a href="{{ route('masters.products.index') }}" data-route="products-cashflow">キャッシュフロー計算書</a></li>
                    <li class="has-submenu">
                        <a href="#"><i class="bi bi-folder2-open"></i>各種マスタ</a>
                        <ul class="submenu-level2">
                            <li><a href="{{ route('masters.account-categories.index') }}" data-route="account-categories">区分</a></li>	
                            <li><a href="{{ route('masters.account-taxs.index') }}" data-route="account-taxs">税区分</a></li>	
                            <li><a href="{{ route('masters.account-departments.index') }}" data-route="account-departments">部門</a></li>	
                            <li><a href="{{ route('masters.account_partners.index') }}" data-route="account-partners">取引先</a></li>	
                            <li><a href="{{ route('masters.accounts.index') }}" data-route="accounts">勘定科目</a></li>	
                            <li><a href="{{ route('masters.account-subs.index') }}" data-route="account-subs">補助科目</a></li>
                        </ul>
                    </li>
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

            <div class="d-flex gap-2 ms-5 btn-list">
                <a href="{{ route('masters.operation-ledger.index') }}" class="btn btn-outline-primary btn-sm px-2">
                    運行台帳
                </a>
                <a href="{{ route('masters.driver-ledger.index') }}" class="btn btn-outline-primary btn-sm px-2">
                    運転手台帳
                </a>
                <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-outline-primary btn-sm px-2">
                    運転一覧
                </a>
                <a href="{{ route('masters.group-infos.index') }}" class="btn btn-outline-primary btn-sm px-2">
                    予約一覧
                </a>
                <a href="#" class="btn btn-outline-primary btn-sm px-2">
                    乘務指示書
                </a>
            </div>
            
            
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
    let isMouseOnSubmenu = false;
    let isMouseOnLevel2 = false;
    
    function updateLogo() {
        if (sidebar.classList.contains('collapsed')) {
            logoImg.src = '/images/logo_s.svg';
        } else {
            logoImg.src = '/images/logo.svg';
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
            document.querySelectorAll('.has-submenu.open').forEach(item => {
                item.classList.remove('open');
                const level2 = item.querySelector('.submenu-level2');
                if (level2) {
                    level2.classList.remove('show');
                    level2.style.display = 'none';
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
            
            document.querySelectorAll('.has-submenu.open').forEach(item => {
                item.classList.remove('open');
                const level2 = item.querySelector('.submenu-level2');
                if (level2) {
                    level2.classList.remove('show');
                }
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
    
    function showFloatingSubmenuLevel2(submenuLevel2, clickedElement) {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
        submenuLevel2.style.display = 'block';
        submenuLevel2.style.visibility = 'hidden';
        const rect = clickedElement.getBoundingClientRect();
        const submenuHeight = submenuLevel2.offsetHeight;
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
        
        const styleId = 'level2-arrow-position-style';
        let styleEl = document.getElementById(styleId);
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = styleId;
            document.head.appendChild(styleEl);
        }
        styleEl.textContent = `.sidebar.collapsed .submenu .submenu-level2.show::before { top: ${arrowTop}px; }`;
        
        submenuLevel2.style.top = topPosition + 'px';
        submenuLevel2.style.left = (rect.right + 8) + 'px';
        submenuLevel2.style.visibility = 'visible';
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
                item.querySelectorAll('.has-submenu.open').forEach(hasSub => {
                    hasSub.classList.remove('open');
                    const level2 = hasSub.querySelector('.submenu-level2');
                    if (level2) {
                        level2.classList.remove('show');
                        level2.style.display = 'none';
                        level2.style.visibility = '';
                    }
                });
            });
        }
        isMouseOnSubmenu = false;
        isMouseOnLevel2 = false;
    }
    
    function scheduleHideSubmenus() {
        if (closeTimer) {
            clearTimeout(closeTimer);
        }
        closeTimer = setTimeout(function() {
            if (!isMouseOnSubmenu && !isMouseOnLevel2) {
                const hoveredElement = document.querySelector(':hover');
                const isHoveringNavHeader = hoveredElement && hoveredElement.closest('.nav-header');
                if (!isHoveringNavHeader) {
                    hideAllFloatingSubmenus();
                }
            }
            closeTimer = null;
        }, 1000);
    }
    
    function highlightCurrentMenu() {
        const allLinks = document.querySelectorAll('.submenu a, .submenu-level2 a');
        const isCollapsed = sidebar.classList.contains('collapsed');
        const currentDataRoute = sessionStorage.getItem('currentMenuRoute');
        
        if (!currentDataRoute) {
            allLinks.forEach(link => {
                link.classList.remove('active');
            });
            return;
        }
        
        allLinks.forEach(link => {
            const href = link.getAttribute('href');
            const dataRoute = link.getAttribute('data-route');
            
            if (href && href !== '#' && href !== '') {
                let isMatch = false;
                
                if (dataRoute && dataRoute === currentDataRoute) {
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
                        const parentHasSubmenu = link.closest('.has-submenu');
                        if (parentHasSubmenu) {
                            parentHasSubmenu.classList.add('open');
                            const level2 = parentHasSubmenu.querySelector('.submenu-level2');
                            if (level2) {
                                level2.classList.add('show');
                            }
                        }
                    }
                } else {
                    link.classList.remove('active');
                }
            }
        });
    }
    
    function bindMenuClickTracking() {
        const allMenuLinks = document.querySelectorAll('.submenu a, .submenu-level2 a');
        
        allMenuLinks.forEach(link => {
            link.removeEventListener('click', link._trackHandler);
            
            const trackHandler = function(e) {
                const dataRoute = this.getAttribute('data-route');
                if (dataRoute) {
                    sessionStorage.setItem('currentMenuRoute', dataRoute);
                }
            };
            
            link.addEventListener('click', trackHandler);
            link._trackHandler = trackHandler;
        });
    }
    
    function initMenuHighlight() {
        bindMenuClickTracking();
        highlightCurrentMenu();
    }
    
    function rebindNavHeaders() {
        const navHeaders = document.querySelectorAll('.nav-header');
        const hasSubmenuItems = document.querySelectorAll('.has-submenu');
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
                        parentItem.querySelectorAll('.has-submenu.open').forEach(item => {
                            item.classList.remove('open');
                            const level2 = item.querySelector('.submenu-level2');
                            if (level2) {
                                level2.classList.remove('show');
                                level2.style.display = 'none';
                            }
                        });
                    } else {
                        document.querySelectorAll('.nav-item.open').forEach(item => {
                            if (item !== parentItem) {
                                item.classList.remove('open');
                                const otherSubmenu = item.querySelector('.submenu');
                                if (otherSubmenu) {
                                    otherSubmenu.classList.remove('show');
                                    otherSubmenu.style.display = 'none';
                                }
                                item.querySelectorAll('.has-submenu.open').forEach(subItem => {
                                    subItem.classList.remove('open');
                                    const level2 = subItem.querySelector('.submenu-level2');
                                    if (level2) {
                                        level2.classList.remove('show');
                                        level2.style.display = 'none';
                                    }
                                });
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
                        parentItem.querySelectorAll('.has-submenu.open').forEach(item => {
                            item.classList.remove('open');
                            const level2 = item.querySelector('.submenu-level2');
                            if (level2) {
                                level2.classList.remove('show');
                            }
                        });
                    } else {
                        document.querySelectorAll('.nav-item.open').forEach(item => {
                            if (item !== parentItem) {
                                item.classList.remove('open');
                                const otherSubmenu = item.querySelector('.submenu');
                                if (otherSubmenu) {
                                    otherSubmenu.classList.remove('show');
                                }
                                item.querySelectorAll('.has-submenu.open').forEach(subItem => {
                                    subItem.classList.remove('open');
                                    const level2 = subItem.querySelector('.submenu-level2');
                                    if (level2) {
                                        level2.classList.remove('show');
                                    }
                                });
                            }
                        });
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
        
        hasSubmenuItems.forEach(item => {
            item.removeEventListener('click', item._clickHandler);
            item.removeEventListener('mouseenter', item._mouseEnterHandler);
            item.removeEventListener('mouseleave', item._mouseLeaveHandler);
            
            const clickHandler = function(e) {
                e.stopPropagation();
                
                const clickedLink = this.querySelector('a');
                const isParentLink = e.target === clickedLink || 
                                    (e.target.tagName === 'I' && e.target.parentElement === clickedLink) ||
                                    (e.target.tagName === 'SPAN' && e.target.parentElement === clickedLink);
                
                if (isParentLink) {
                    e.preventDefault();
                    
                    const linkElement = this.querySelector('a');
                    if (linkElement) {
                        const dataRoute = linkElement.getAttribute('data-route');
                        if (dataRoute) {
                            sessionStorage.setItem('currentMenuRoute', dataRoute);
                        }
                    }
                    
                    const isOpen = this.classList.contains('open');
                    const submenuLevel2 = this.querySelector('.submenu-level2');
                    
                    if (sidebar.classList.contains('collapsed')) {
                        if (isOpen) {
                            this.classList.remove('open');
                            if (submenuLevel2) {
                                submenuLevel2.classList.remove('show');
                                submenuLevel2.style.display = 'none';
                            }
                        } else {
                            const parentSubmenu = this.closest('.submenu');
                            if (parentSubmenu) {
                                parentSubmenu.querySelectorAll('.has-submenu.open').forEach(other => {
                                    if (other !== this) {
                                        other.classList.remove('open');
                                        const otherSubmenu = other.querySelector('.submenu-level2');
                                        if (otherSubmenu) {
                                            otherSubmenu.classList.remove('show');
                                            otherSubmenu.style.display = 'none';
                                        }
                                    }
                                });
                            }
                            this.classList.add('open');
                            if (submenuLevel2) {
                                submenuLevel2.classList.add('show');
                                showFloatingSubmenuLevel2(submenuLevel2, this);
                            }
                        }
                    } else {
                        if (isOpen) {
                            this.classList.remove('open');
                            if (submenuLevel2) {
                                submenuLevel2.classList.remove('show');
                            }
                        } else {
                            const parentSubmenu = this.closest('.submenu');
                            if (parentSubmenu) {
                                parentSubmenu.querySelectorAll('.has-submenu.open').forEach(other => {
                                    if (other !== this) {
                                        other.classList.remove('open');
                                        const otherSubmenu = other.querySelector('.submenu-level2');
                                        if (otherSubmenu) {
                                            otherSubmenu.classList.remove('show');
                                        }
                                    }
                                });
                            }
                            this.classList.add('open');
                            if (submenuLevel2) {
                                submenuLevel2.classList.add('show');
                            }
                        }
                    }
                }
            };
            
            item.addEventListener('click', clickHandler);
            item._clickHandler = clickHandler;
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
                    isMouseOnSubmenu = true;
                };
                
                const leaveHandler = function() {
                    isMouseOnSubmenu = false;
                    scheduleHideSubmenus();
                };
                
                submenu.addEventListener('mouseenter', enterHandler);
                submenu.addEventListener('mouseleave', leaveHandler);
                submenu._enterHandler = enterHandler;
                submenu._leaveHandler = leaveHandler;
            });
            
            document.querySelectorAll('.sidebar.collapsed .submenu-level2').forEach(level2 => {
                level2.removeEventListener('mouseenter', level2._enterHandler);
                level2.removeEventListener('mouseleave', level2._leaveHandler);
                
                const enterHandler = function() {
                    if (closeTimer) {
                        clearTimeout(closeTimer);
                        closeTimer = null;
                    }
                    isMouseOnLevel2 = true;
                };
                
                const leaveHandler = function() {
                    isMouseOnLevel2 = false;
                    scheduleHideSubmenus();
                };
                
                level2.addEventListener('mouseenter', enterHandler);
                level2.addEventListener('mouseleave', leaveHandler);
                level2._enterHandler = enterHandler;
                level2._leaveHandler = leaveHandler;
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
    initMenuHighlight();
    
    sidebar.addEventListener('mouseleave', function(e) {
        if (sidebar.classList.contains('collapsed')) {
            const relatedTarget = e.relatedTarget;
            const isLeavingToSubmenu = relatedTarget && relatedTarget.closest && relatedTarget.closest('.submenu');
            const isLeavingToLevel2 = relatedTarget && relatedTarget.closest && relatedTarget.closest('.submenu-level2');
            if (!isLeavingToSubmenu && !isLeavingToLevel2) {
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