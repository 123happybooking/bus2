<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '管理パネル') - 管理パネル</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
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
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-xl navbar-dark shadow">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">管理パネル</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users"></i> ユーザー管理
                        </a>
                    </li>
                </ul>
                
                <div class="d-xl-flex align-items-center ms-auto">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-sign-out-alt"></i> ログアウト
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
    @yield('scripts')
</body>
</html>