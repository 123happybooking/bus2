<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. メンテナンスモードの確認
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// 2. オートローダーの登録（Composerが生成したファイルを読み込む）
require __DIR__.'/../vendor/autoload.php';

// 3. Laravelアプリケーションの初期化（Appのインスタンス作成）
$app = require_once __DIR__.'/../bootstrap/app.php';

// 4. リクエストを受け取り、処理を開始（Kernelを通じて実行）
$handle = function (Request $request) use ($app) {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle($request);

    $response->send();

    $kernel->terminate($request, $response);
};

$handle(Request::capture());