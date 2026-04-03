<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン - 運行管理システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; }
        .login-form { width: 100%; max-width: 400px; padding: 15px; margin: auto; }
    </style>
</head>
<body>
    <main class="login-form">
        <div class="card shadow">
            <div class="card-body">
                <h1 class="h3 mb-3 fw-normal text-center">運行管理システム</h1>
                <form action="{{ route('/home') }}" method="GET">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="loginId" placeholder="ID">
                        <label for="loginId">ログインID</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" placeholder="Password">
                        <label for="password">パスワード</label>
                    </div>
                    <button class="w-100 btn btn-lg btn-primary" type="submit">ログイン</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>