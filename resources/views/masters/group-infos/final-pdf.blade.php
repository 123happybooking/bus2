<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>最終確認書</title>
    <style>
        body {
            font-family: msyh, sans-serif;
            font-size: 10pt;
            margin: 20pt;
            padding: 0;
        }
        
        .header-no-border {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        
        .header-no-border td {
            vertical-align: top;
            border: none;
        }
        
        .agency-info {
            width: 50%;
            border: 1px solid #333;
            padding: 8px;
        }
        
        .company-info {
            width: 50%;
            border: 1px solid #333;
            padding: 8px;
            text-align: right;
        }
        
        .title {
            font-size: 22pt;
            font-weight: bold;
            display: inline-block;
            width: 100%;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20pt;
            border: 1px solid #333;
        }
        
        .info-table th, .info-table td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .info-table th {
            background-color: #f0f0f0;
            width: 120px;
        }
        
        .table-border {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        
        .table-border th, .table-border td {
            border: 1px solid #333;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
        }
        
        .table-border th {
            background-color: #eee;
            font-weight: 500;
        }
        
        .table-border .text-left {
            text-align: left;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20pt;
            font-size: 9pt;
        }
        
        .invoice-table th, .invoice-table td {
            border: 1px solid #333;
            padding: 4px 6px;
            text-align: right;
            vertical-align: middle;
        }
        
        .invoice-table th {
            background-color: #3b5998;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .invoice-table .text-left {
            text-align: left;
        }
        
        .summary-row td {
            background-color: #f5f5f5;
        }
        
        .vehicle-info {
            font-size: 10pt;
            margin-bottom: 10pt;
        }
        
        .agency-contact {
            margin-top: 20pt;
            padding: 8pt;
            border-top: 1px solid #ccc;
            font-size: 10pt;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <table class="header-no-border no-break">
        <tr>
            <td style="width: 60%; line-height: 150%;">
                <div>{{ $groupInfo->agency ?? '' }} 御中</div>
                
                <br>
                
                <div class="title no-break">最終確認書</div>
                
                <br>
                <br>
                
                <div>団体名：{{ $groupInfo->group_name ?? '' }}</div>
                <div>人数：大：{{ $totalAdult ?? 0 }}  小：{{ $totalChild ?? 0 }}</div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <table class="layout-table no-break" style="width: auto; display: inline-block;">
                    <tr>
                        <td style="text-align: left; line-height: 150%;">
                            <br>
                            <div>{{ $companyInfo->company_name ?? '' }}</div>
                            <div>{{ $companyInfo->address ?? '' }}</div>
                            <div>TEL：{{ $companyInfo->phone_number ?? '' }}</div>
                            <div>担当：{{ $companyInfo->contact ?? '' }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br>
    @foreach($groupInfo->busAssignments->sortBy('vehicle_index') as $busIndex => $busAssignment)
        @php
            $busId = $busAssignment->id;
            $busNumber = $busAssignment->vehicle_number ?? '';
            
            $vehicleInfo = '';
            if ($busAssignment->vehicle_grade_id) {
                $vehicleGrade = \App\Models\Masters\VehicleGrade::find($busAssignment->vehicle_grade_id);
                $vehicleInfo = $vehicleGrade->grade_name ?? '';
            }
            
            $busItineraries = $itineraries->where('bus_assignment_id', $busAssignment->id)
                ->sortBy('date')
                ->sortBy('time_start')
                ->values();
        @endphp
        
        <table class="header-no-border no-break" style="margin: 0 0 5px 0;">
            <tr>
                <td style="width: 50%; text-align: left;">
                    車種：{{ $vehicleInfo }} {{ $busNumber }}
                </td>
                <td style="width: 50%; text-align: right;">
                    {{ $groupInfo->id ?? '' }}-{{ $busId ?? '' }}
                </td>
            </tr>
            <tr>
                <td style="width: 50%; text-align: left;">
                    運転手：{{ $busAssignment->driver->name ?? '' }} / {{ $busAssignment->driver->phone_number ?? '--' }}
                </td>
                <td style="width: 50%; text-align: right;">
                    {{ $busAssignment->vehicle->registration_number ?? '' }}
                </td>
            </tr>
        </table>
    
        <table class="table-border">
            <thead>
                <tr>
                    <th style="width: 10%;">日付</th>
                    <th style="width: 10%;">開始</th>
                    <th style="width: 35%;" colspan="4">開始場所</th>
                    <th style="width: 10%;"></th>
                    <th style="width: 10%;">終了</th>
                    <th style="width: 25%;" colspan="2">終了場所</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                @endphp
                @forelse($busItineraries as $row)
                @php
                    $date = \Carbon\Carbon::parse($row->date);
                    $dayLabel = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
                @endphp
                <tr>
                    <td style="width:10%; text-align: center; vertical-align: middle;" rowspan="2">{{ $dayLabel }}</td>
                    <td style="width:10%;">{{ substr($row->time_start, 0, 5) }}</td>
                    <td style="width:35%; text-align: left;" colspan="4">{{ $row->start_location ?? '' }}</td>
                    <td style="width:10%; text-align: center;">--></td>
                    <td style="width:10%;">{{ substr($row->time_end, 0, 5) }}</td>
                    <td style="width:25%; text-align: left;" colspan="2">{{ $row->end_location ?? '' }}</td>
                </tr>
                <tr>
                    <td style="width:90%; text-align: left;" colspan="9"><pre style="margin: 0; font-family: inherit; font-size: 9pt; white-space: pre-wrap;">{{ $row->itinerary ?? '' }}</pre></td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center;">行程データがありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <br>
    @endforeach

    <table class="table-border no-break">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">No.</th>
                <th style="width: 54%; text-align: left; padding-left: 5px;" colspan="4">摘要</th>
                <th style="width: 6%; text-align: center;">数量</th>
                <th style="width: 12%; text-align: center;">単価</th>
                <th style="width: 12%; text-align: center;">金額</th>
                <th style="width: 10%; text-align: center;">税率</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoiceItems as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: left; padding-left: 5px;" colspan="4">
                    {{ $item->description ?? $item['description'] ?? '' }}
                    @if(isset($item->period) && $item->period)
                        <br><span style="font-size:8pt;color:#666;">{{ $item->period }}</span>
                    @endif
                </td>
                <td style="text-align: right;">{{ $item->quantity ?? $item['quantity'] ?? '' }}</td>
                <td style="text-align: right;">{{ number_format($item->unit_price ?? $item['unit_price'] ?? 0) }}</td>
                <td style="text-align: right;">{{ number_format($item->amount ?? $item['amount'] ?? 0) }}</td>
                <td style="text-align: center;">
                    @php 
                        $taxRate = $item->tax_rate ?? $item['tax_rate'] ?? 0;
                    @endphp
                    @if ($taxRate == -1) 免税
                    @elseif ($taxRate == -2) 非課税
                    @else {{ number_format($taxRate) }}%
                    @endif
                </td>
            </tr>
            @endforeach
    
            @php 
                $detailCount = count($invoiceItems); 
                $summaryRows = 3; 
                $targetTotalRows = 15; 
                $remaining = max(0, $targetTotalRows - $detailCount - $summaryRows); 
            @endphp
            @for($i = 0; $i < $remaining; $i++)
            <tr>
                <td>&nbsp;</td>
                <td colspan="4"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endfor
    
            <tr class="summary-row">
                <td colspan="2" style="text-align: left;">10％対象</td>
                <td style="font-weight: bold; text-align: right;">{{ number_format($summary_10->subtotal ?? 0) }}</td>
                <td>消費税</td>
                <td style="font-weight: bold; text-align: right;">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
                <td colspan="2" style="font-weight: bold;">小計</td>
                <td colspan="2" style="font-weight: bold; text-align: right;">
                    @if($taxMode == 1)
                        {{ number_format($totalAmount) }}
                    @else
                        {{ number_format($subtotalAmount) }}
                    @endif
                </td>
            </tr>
    
            <tr class="summary-row">
                <td colspan="2" style="text-align: left;">8％対象</td>
                <td style="font-weight: bold; text-align: right;">{{ number_format($summary_8->subtotal ?? 0) }}</td>
                <td>消費税</td>
                <td style="font-weight: bold; text-align: right;">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
                <td colspan="2" style="font-weight: bold;">消費税{{ $taxMode == 1 ? "(内税)" : "" }}</td>
                <td colspan="2" style="font-weight: bold; text-align: right;">
                    @if($taxMode == 1)
                        ({{ number_format($taxAmount) }})
                    @else
                        {{ number_format($taxAmount) }}
                    @endif
                </td>
            </tr>
    
            <tr class="summary-row">
                <td colspan="2" style="text-align: left;">非課税/免税</td>
                <td style="font-weight: bold; text-align: right;">{{ number_format($nonTaxable) }}</td>
                <td colspan="2"></td>
                <td colspan="2" style="font-weight: bold;">請求合計</td>
                <td colspan="2" style="font-weight: bold; text-align: right;">{{ number_format($totalAmount) }}</td>
            </tr>
        </tbody>
    </table>

    @if($groupInfo->agency_contact)
        <div style="margin: 10px 0 0 0;">{!! nl2br(e($groupInfo->agency_contact)) !!}</div>
    @endif

</body>
</html>