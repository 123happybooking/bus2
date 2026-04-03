<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マスターズ管理システム - ログイン</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            padding: 50px 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .login-header h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .input-group {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            font-size: 18px;
        }
        input[type="text"],
        input[type="password"],
        input[type="number"] {
            width: 100%;
            padding: 14px 20px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus {
            border-color: #3498db;
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        .remember-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        .remember-label {
            display: flex;
            align-items: center;
            color: #7f8c8d;
            font-size: 14px;
            cursor: pointer;
        }
        .remember-label input {
            margin-right: 8px;
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #3498db;
        }
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(52,152,219,0.3);
        }
        .error-message {
            background: #fef5f5;
            color: #e74c3c;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #e74c3c;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-message::before {
            content: "⚠️";
            margin-right: 8px;
            font-size: 16px;
        }
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            color: #95a5a6;
            font-size: 12px;
        }
        .field-error {
            color: #e74c3c;
            margin-top: 5px;
            display: block;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>マスターズ管理システム</h1>
        </div>

        @if($errors->has('login_id'))
            <div class="error-message">
                {{ $errors->first('login_id') }}
            </div>
        @endif

        <form method="POST" action="{{ route('masters.login') }}">
            @csrf
            <div class="form-group">
                <div class="input-group">
                    <span class="input-icon">🏢</span>
                    <input type="number" id="user_company_id" name="user_company_id" value="{{ old('user_company_id') }}" 
                           placeholder="運行会社IDを入力" required autofocus min="1" step="1">
                </div>
                @if($errors->has('user_company_id'))
                    <small class="field-error">{{ $errors->first('user_company_id') }}</small>
                @endif
            </div>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-icon">👤</span>
                    <input type="text" id="login_id" name="login_id" value="{{ old('login_id') }}" 
                           placeholder="ユーザID" required>
                </div>
                @if($errors->has('login_id'))
                    <small class="field-error">{{ $errors->first('login_id') }}</small>
                @endif
            </div>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password" 
                           placeholder="パスワード" required>
                </div>
            </div>

            <button type="submit">
                ログイン
            </button>
        </form>
    </div>
</body>
</html>