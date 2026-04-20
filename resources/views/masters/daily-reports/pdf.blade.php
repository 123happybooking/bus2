<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>運行日報</title>
    <style>
        * { margin: 0; padding: 0; text-indent: 0; box-sizing: border-box; }
        body { background-color: #fff; font-family: 'Helvetica Neue', 'Noto Sans JP', 'Meiryo', 'MS Gothic', sans-serif; }
        table { border-collapse: collapse; margin: 0 auto; width: 100%;}
        td { text-align: center; padding: 3px; border: 1px solid #000; vertical-align: middle; font-size: 11px; line-height: 100%; word-wrap: break-word; word-break: break-all; white-space: normal; }
        .t-l { text-align: left !important;}
        .t-r { text-align: right !important;}
    </style>
</head>
<body>
<table style="width: 100%;">
    <tr>
        <td colspan="40" style="width: 40%; font-size: 24pt; letter-spacing: 6pt; font-weight: bold; padding: 10px 5px;">運転日報</td>
        <td colspan="20" style="width: 20%;">No.{{ $itinerary->busAssignment->groupInfo->id ?? '' }}-{{ $itinerary->bus_assignment_id ?? '' }}</td>
        <td colspan="40" style="width: 40%;">{{ $companyInfo['name'] }} {{ $companyInfo['branch'] }}<br>TEL:{{ $companyInfo['tel'] }} / FAX:{{ $companyInfo['fax'] }}</td>
    </tr>
    <tr>
        <td colspan="10">乗務日</td>
        <td colspan="30" style="font-size: 13pt;">{{ $date }}</td>
        <td colspan="10">天気</td>
        <td colspan="10">{{ $weather ?? '' }}</td>
        <td colspan="20">号車</td>
        <td colspan="20">{{ $report->vehicle->vehicle_code ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="10">ツアー名</td>
        <td colspan="90" class="t-l">&nbsp;{{ $itinerary->busAssignment->groupInfo->group_name ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="10">旅客名</td>
        <td colspan="60" class="t-l">&nbsp;{{ $itinerary->busAssignment->representative ?? '' }} {{ $itinerary->busAssignment->representative_phone ?? '' }}</td>
        <td colspan="15">車両番号</td>
        <td colspan="15">{{ $report->vehicle->registration_number ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="10" rowspan="2">終業時<br>メーター</td>
        <td colspan="25" rowspan="2" class="t-r">{{ $report->end_mileage ?? '' }} Km</td>
        <td colspan="10" rowspan="2">着地<br>メーター</td>
        <td colspan="25" rowspan="2" class="t-r">{{ $report->start_mileage ?? '' }} Km</td>
        <td colspan="15">始業時刻</td>
        <td colspan="15">{{ $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '' }}</td>
    </tr>
    <tr>
        <td colspan="15">出庫時刻</td>
        <td colspan="15">{{ $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '' }}</td>
    </tr>
    <tr>
        <td colspan="10" rowspan="2">始業時<br>メーター</td>
        <td colspan="25" rowspan="2" class="t-r">{{ $report->start_mileage ?? '' }} Km</td>
        <td colspan="10" rowspan="2">発 地<br>メーター</td>
        <td colspan="25" rowspan="2" class="t-r">{{ $report->end_mileage ?? '' }} Km</td>
        <td colspan="15">帰庫時刻</td>
        <td colspan="15">{{ $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '' }}</td>
    </tr>
    <tr>
        <td colspan="15">終業時刻</td>
        <td colspan="15">{{ $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '' }}</td>
    </tr>
    <tr>
        <td colspan="10">総走行キロ</td>
        <td colspan="25" class="t-r">{{ $distance }} Km</td>
        <td colspan="10">実車走行キロ</td>
        <td colspan="25" class="t-r">{{ $distance }} Km</td>
        <td colspan="15">空車走行キロ</td>
        <td colspan="15" class="t-r">{{ $distance }} Km</td>
    </tr>

    <tr>
        <td colspan="50">運転士(1・2)</td>
        <td colspan="50">ガイド・他</td>
    </tr>
    <tr>
        <td colspan="25">{{ $report->driver->name ?? '' }}</td>
        <td colspan="25">{{ $driverName2 ?? '' }}</td>
        <td colspan="25">{{ $itinerary->guide ?? '' }}</td>
        <td colspan="25">{{ $guideName2 ?? '' }}</td>
    </tr>

    <tr>
        <td colspan="16">発着地名<br>(主な経由地)</td>
        <td colspan="2"></td>
        <td colspan="8">時刻</td>
        <td colspan="8">待機時間<br>休憩時間</td>
        <td colspan="8">乗務時間</td>
        <td colspan="8">メーター<br>km</td>
        <td colspan="8">実車<br>km</td>
        <td colspan="8">空車<br>km</td>
        <td colspan="8">正</td>
        <td colspan="8">副</td>
        
        <td colspan="2" rowspan="4">乗<br>車<br>人<br>数</td>
        <td colspan="8" rowspan="2">乗 客</td>
        <td colspan="8" rowspan="2" class="t-r">{{ $totalPassengers }}名</td>
    </tr>

    <tr>
        <td colspan="16" rowspan="2">{{ $itineraryRows[0]['location'] ?? '' }}</td>
        <td colspan="2">発</td>
        <td colspan="8">{{ $itineraryRows[0]['start_time'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['wait_time'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['work_time'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['start_mileage'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['actual_km'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['empty_km'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['positive'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['negative'] ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="2">着</td>
        <td colspan="8">{{ $itineraryRows[0]['end_time'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['wait_time2'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['work_time2'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['end_mileage'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['actual_km2'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['empty_km2'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['positive2'] ?? '' }}</td>
        <td colspan="8">{{ $itineraryRows[0]['negative2'] ?? '' }}</td>
        
        <td colspan="8" rowspan="2">その他</td>
        <td colspan="8" rowspan="2" class="t-r">{{ $adultCount }}名</td>
    </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[1]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[1]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['negative'] ?? '' }}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[1]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[1]['negative2'] ?? '' }}</td>
            
            <td colspan="2" rowspan="4">勤<br>務<br>時<br>間</td>
            <td colspan="8">運転士1</td>
            <td colspan="8">{053}</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[2]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[2]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['negative'] ?? '' }}</td>
            
            <td colspan="8">運転士2</td>
            <td colspan="8">{054}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[2]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[2]['negative2'] ?? '' }}</td>
            
            <td colspan="8">ガイド</td>
            <td colspan="8">{055}</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[3]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[3]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['negative'] ?? '' }}</td>
            
            <td colspan="8">他</td>
            <td colspan="8">{056}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[3]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[3]['negative2'] ?? '' }}</td>
            
            <td colspan="2" rowspan="4">乗<br>務<br>時<br>間</td>
            <td colspan="8">運転士1</td>
            <td colspan="8">{057}</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[4]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[4]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['negative'] ?? '' }}</td>
            
            <td colspan="8">運転士2</td>
            <td colspan="8">{058}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[4]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[4]['negative2'] ?? '' }}</td>
            
            <td colspan="8">ガイド</td>
            <td colspan="8">{059}</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[5]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[5]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['negative'] ?? '' }}</td>
            
            <td colspan="8">他</td>
            <td colspan="8">{060}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[5]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[5]['negative2'] ?? '' }}</td>
            
            <td colspan="2" rowspan="4">休<br>憩<br>時<br>間</td>
            <td colspan="8">運転士1</td>
            <td colspan="8">{061}</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[6]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[6]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['negative'] ?? '' }}</td>
            
            <td colspan="8">運転士2</td>
            <td colspan="8">{062}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[6]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[6]['negative2'] ?? '' }}</td>
            
            <td colspan="8">ガイド</td>
            <td colspan="8">{063}</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[7]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[7]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['negative'] ?? '' }}</td>
            
            <td colspan="8">他</td>
            <td colspan="8">{064}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[7]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[7]['negative2'] ?? '' }}</td>
            
            <td colspan="2" rowspan="4">待<br>機<br>時<br>間</td>
            <td colspan="8">運転士1</td>
            <td colspan="8">{065}</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[8]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[8]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['negative'] ?? '' }}</td>
            
            <td colspan="8">運転士2</td>
            <td colspan="8">{066}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[8]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[8]['negative2'] ?? '' }}</td>
            
            <td colspan="8">ガイド</td>
            <td colspan="8">{067}</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[9]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[9]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['negative'] ?? '' }}</td>
            
            <td colspan="8">他</td>
            <td colspan="8">{068}</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[9]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[9]['negative2'] ?? '' }}</td>
            
            <td colspan="18">料金立替</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[10]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[10]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['negative'] ?? '' }}</td>
            
            <td colspan="9">高速料金</td>
            <td colspan="9" class="t-r">{069}円</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[10]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[10]['negative2'] ?? '' }}</td>
            
            <td colspan="9">有料道路</td>
            <td colspan="9" class="t-r">{070}円</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[11]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[11]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['negative'] ?? '' }}</td>
            
            <td colspan="9">駐車場</td>
            <td colspan="9" class="t-r">{071}円</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[11]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[11]['negative2'] ?? '' }}</td>
            
            <td colspan="9">乗務員宿泊</td>
            <td colspan="9" class="t-r">{072}円</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[12]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[12]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['negative'] ?? '' }}</td>
            
            <td colspan="9">合計</td>
            <td colspan="9" class="t-r">{073}円</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[12]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[12]['negative2'] ?? '' }}</td>
            
            <td colspan="18">燃料</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[13]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[13]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['negative'] ?? '' }}</td>
            
            <td colspan="9">軽油(社内)</td>
            <td colspan="9" class="t-r">{074}L</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[13]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[13]['negative2'] ?? '' }}</td>
            
            <td colspan="9">軽油(社外)</td>
            <td colspan="9" class="t-r">{075}L</td>
        </tr>
        
        <tr>
            <td colspan="16" rowspan="2">{{ $itineraryRows[14]['location'] ?? '' }}</td>
            <td colspan="2">発</td>
            <td colspan="8">{{ $itineraryRows[14]['start_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['wait_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['work_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['start_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['actual_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['empty_km'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['positive'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['negative'] ?? '' }}</td>
            
            <td colspan="9">燃料(メイン)</td>
            <td colspan="9" class="t-r">{076}L</td>
        </tr>
        
        <tr>
            <td colspan="2">着</td>
            <td colspan="8">{{ $itineraryRows[14]['end_time'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['wait_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['work_time2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['end_mileage'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['actual_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['empty_km2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['positive2'] ?? '' }}</td>
            <td colspan="8">{{ $itineraryRows[14]['negative2'] ?? '' }}</td>
            
            <td colspan="9">燃料(メイン)</td>
            <td colspan="9" class="t-r">{077}L</td>
        </tr>
        
        <tr>
            <td colspan="18">総合計</td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            
            <td colspan="9">オイル</td>
            <td colspan="9" class="t-r">{078}L</td>
        </tr>
        
        <tr>
            <td colspan="18" rowspan="2">正副運転士別合計</td>
            <td colspan="8">正</td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            
            <td colspan="9" style="width: 9%;">管理者</td>
            <td colspan="9" style="width: 9%;">補助者</td>
        </tr>
        
        <tr>
            <td colspan="8">副</td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            <td colspan="8"></td>
            
            <td colspan="9" rowspan="2">{079}</td>
            <td colspan="9" rowspan="2">{080}</td>
        </tr>
        
        <tr>
            <td colspan="82" style="width: 82%; height: 60px; word-wrap: break-word; word-break: break-all; white-space: normal;">
                備考(事故・故障・宿泊・その他)<br>
                =====================================<br>
                说明：页面上的 {080} 这些内容为占位符，用来区别哪些位置填写什么内容，<br>
                等到最后所有数据都确定好的时候，我会都清理掉的
            </td>
        </tr>
    </table>
</body>
</html>