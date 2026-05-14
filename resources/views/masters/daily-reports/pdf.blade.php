<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>運行日報</title>
<style>
    body {
        font-size: 11pt;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 11pt;
    }
    th, td {
        text-align: center; 
        border: 1px solid #000;
        padding: 3px;
        vertical-align: middle;
        font-size: 11pt;
        line-height: 150%;
        height: 20pt;
        word-break: break-all;
        word-wrap: break-word;
    }
    th {
        background-color: #f0f0f0;
        font-weight: bold;
    }
    .bg-gray {
        background-color: #f5f5f5;
    }
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    .table-no-border th,
    .table-no-border td {
        border: 0;
    }
    .table-list th,
    .table-list td {
        text-align: left;
        font-size: 9pt;
        padding: 0 8pt;
        height: 9pt;
        word-break: break-all;
        word-wrap: break-word;
    }
    .table-list td.text-right {
        text-align: right;
    }
    .header {
        width: 100%;
        margin: 0 0 4pt 0;
        overflow: hidden;
    }
    .header .c1 {
        float: left;
        width: 50%;
        font-size: 24pt;
        font-weight: bold;
    }
    .header .c2 {
        text-align: right;
        float: left;
        width: 20%;
    }
    .header .c3 {
        text-align: right;
        float: left;
        width: 30%;
    }
    .clearfix {
        clear: both;
    }
    .remark { text-align: left; vertical-align: top; height: 60pt;}
</style>
</head>
<body>

<div class="header">
    <div class="c1">運転日報</div>
    <div class="c2">
        @if(isset($companyLogo) && $companyLogo)
            <img src="{{ $companyLogo }}" height="30pt" max-width="80%">
        @endif
    </div>
    <div class="c3">
        {{ $companyInfo['name'] ?? '会社名' }}<br>
        {{ $companyInfo['tel'] ?? '電話' }}
    </div>
</div>
<div class="clearfix"></div>

<table>
    <tr>
        <th style="width: 15%;">日報ID</th>
        <td style="width: 50%;" colspan="3">{{ \Carbon\Carbon::parse($report->date)->format('Ymd') }}-{{ $report->vehicle_id ?? '' }}-{{ $report->driver_id ?? '' }}</td>
        <th style="width: 15%;">運転手</th>
        <td style="width: 20%;">{{ $report->driver->name ?? '' }} / {{ $report->driver->phone_number ?? '' }}</td>
    </tr>
    <tr>
        <th>運行日</th>
        <td colspan="3">{{ $date ?? '' }}</td>
        <th>天気</th>
        <td>{{ $weather ?? '' }}</td>
    </tr>
    <tr>
        <th>車両名</th>
        <td colspan="3">{{ $report->vehicle->vehicle_code ?? '' }} / {{ $report->vehicle->vehicle_color ?? '' }}</td>
        <th>車両No.</th>
        <td>{{ $report->vehicle->registration_number ?? '' }}</td>
    </tr>
    <tr>
        <th>始業時刻</th>
        <td>{{ $report->start_work_time ? \Carbon\Carbon::parse($report->start_work_time)->format('H:i') : '' }}</td>
        <th style="width: 15%;">帰庫時刻</th>
        <td>{{ $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '' }}</td>
        <th>終走行</th>
        <td>{{ $report->end_mileage ?? '' }} km</td>
    </tr>
    <tr>
        <th>出庫時刻</th>
        <td>{{ $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '' }}</td>
        <th>帰庫メーター</th>
        <td>{{ $report->end_mileage ?? '' }} km</td>
        <th>実写走行</th>
        <td>{{ $report->actual_distance ?? '' }} km</td>
    </tr>
    <tr>
        <th>出庫メーター</th>
        <td>{{ $report->start_mileage ?? '' }} km</td>
        <th>終業時刻</th>
        <td>{{ $report->end_work_time ? \Carbon\Carbon::parse($report->end_work_time)->format('H:i') : '' }}</td>
        <th>空車走行</th>
        <td>{{ $report->empty_distance ?? '' }} km</td>
    </tr>
    <tr>
        <td colspan="6">
            <table class="table-no-border table-list">
            @foreach($completedItineraries as $itinerary)
                <tr>
                    <td colspan="6"><b>{{ $itinerary['reservation_id'] ?? '' }}</b></td>
                </tr>
                <tr>
                    <td style="width: 15%;">時刻</td>
                    <td style="width: 40%;">地名</td>
                    <td style="width: 15%;" class="text-right">メーター</td>
                    <td style="width: 15%;" class="text-right">内容</td>
                    <td style="width: 15%;" colspan="2">備考</td>
                </tr>
                
                @foreach($itinerary['logs'] as $log)
                <tr>
                    <td>{{ $log['time'] ?? '' }}</td>
                    <td>{{ $log['location'] ?? '' }}</td>
                    <td class="text-right">{{ $log['meter'] ?? '' }}</td>
                    <td class="text-right">{{ $log['content'] ?? '' }}</td>
                    <td colspan="2">{{ $log['remark'] ?? '' }}</td>
                </tr>
                @endforeach
            @endforeach
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="6" class="remark">
            立替
            <table class="table-no-border table-list">
                @foreach($completedItineraries as $itinerary)
                    <tr>
                        <td colspan="6">
                            <b>{{ $itinerary['reservation_id'] ?? '' }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 15%;">日付</th>
                        <td>内容</th>
                        <td style="width: 15%;" class="text-right">金額</td>
                        <td style="width: 15%;">支払方法</td>
                        <td style="width: 15%;">請求対象</td>
                        <td>備考</th>
                    </tr>
                    
                    @foreach($itinerary['expenses'] as $expense)
                    <tr>
                        <td>{{ $expense['expense_date'] ?? '' }}</td>
                        <td>{{ $expense['type_name'] ?? '' }}</td>
                        <td class="text-right">{{ number_format($expense['amount'] ?? 0) }} 円</td>
                        <td>{{ $expense['payment_method_name'] ?? '' }}</td>
                        <td>{{ $expense['agency_flag'] ? '有り' : '' }}</td>
                        <td>{{ $expense['remark'] ?? '' }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="6" class="remark">備考<br>{{ $report->remark ?? '--' }}</td>
    </tr>
</table>

</body>
</html>