<!-- メニュー -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">運行管理システム</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">運行管理</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">運行台帳</a></li>
                            <li><a class="dropdown-item" href="#">運転手台帳</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">運行一覧</a></li>
                            <li><a class="dropdown-item" href="#">日次一覧</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">売上管理</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">請求管理</a></li>
                            <li><a class="dropdown-item" href="#">入金管理</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">マスター管理</a>
                        <ul class="dropdown-menu border-0 shadow" style="min-width: 250px;">
                            <div class="row g-0">
                                <div class="col-6">
                                    <li><a class="dropdown-item small" href="#">スタッフ</a></li>
                                    <li><a class="dropdown-item small" href="#">営業所</a></li>
                                    <li><a class="dropdown-item small" href="#">車両</a></li>
                                    <li><a class="dropdown-item small" href="#">運転手</a></li>
                                    <li><a class="dropdown-item small" href="#">代理店</a></li>
                                </div>
                                <div class="col-6 border-start">
                                    <li><a class="dropdown-item small" href="#">行程</a></li>
                                    <li><a class="dropdown-item small" href="#">施設</a></li>
                                    <li><a class="dropdown-item small" href="#">地名</a></li>
                                    <li><a class="dropdown-item small" href="#">備考</a></li>
                                    <li><a class="dropdown-item small text-danger" href="#">ログイン履歴</a></li>
                                </div>
                            </div>
                        </ul>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="/">ログアウト</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
