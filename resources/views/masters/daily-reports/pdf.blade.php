<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>運行日報</title>
    <style>
        /* 使用绝对路径直接引入字体 */
        @font-face {
            font-family: 'IPAexGothic';
            font-style: normal;
            font-weight: normal;
            src: url('file:///www/wwwroot/0_bus/storage/fonts/ipaexg.ttf') format('truetype');
        }
        @font-face {
            font-family: 'IPAexGothic';
            font-style: normal;
            font-weight: bold;
            src: url('file:///www/wwwroot/0_bus/storage/fonts/ipaexg.ttf') format('truetype');
        }
        
        body {
            font-family: 'IPAexGothic', 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }
        .content {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>運行日報</h2>
    </div>

    <div class="info-row">
        <span class="label">日付：</span>
        <span class="content">{{ \Carbon\Carbon::parse($report->date)->format('Y年m月d日') }}</span>
    </div>

    <div class="info-row">
        <span class="label">運転手名：</span>
        <span class="content">{{ $report->driver->name ?? '-' }}</span>
    </div>

    <div class="info-row">
        <span class="label">車両名：</span>
        <span class="content">{{ $report->vehicle->registration_number ?? '-' }}</span>
    </div>

    <div class="info-row">
        <span class="label">出庫時間：</span>
        <span class="content">{{ $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '-' }}</span>
    </div>

    <div class="info-row">
        <span class="label">出庫時メーター：</span>
        <span class="content">{{ number_format($report->start_mileage ?? 0) }} km</span>
    </div>

    <div class="info-row">
        <span class="label">帰庫時間：</span>
        <span class="content">{{ $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '-' }}</span>
    </div>

    <div class="info-row">
        <span class="label">帰庫時メーター：</span>
        <span class="content">{{ number_format($report->end_mileage ?? 0) }} km</span>
    </div>

    <div class="info-row">
        <span class="label">走行距離：</span>
        <span class="content">{{ number_format(($report->end_mileage ?? 0) - ($report->start_mileage ?? 0)) }} km</span>
    </div>

    <div class="info-row">
        <span class="label">作成日時：</span>
        <span class="content">{{ \Carbon\Carbon::parse($report->created_at)->format('Y年m月d日 H:i:s') }}</span>
    </div>
</body>
</html>