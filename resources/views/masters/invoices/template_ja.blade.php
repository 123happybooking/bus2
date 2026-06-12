<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>請求書</title>
    <style>
        body, table, td, th { 
            font-size: 10pt; 
            line-height: 1.3; 
            color: #000; 
            margin: 0;
            padding: 0;
        }
        
        .layout-table {
            width: 100%;
            border: 0;
            margin: 0 0 10px 0;
            padding: 0;
            border-spacing: 0;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9pt;
            margin-bottom: 0;
            border: 1px solid #333;
        }
        
        .main-table td, .main-table th {
            border: 1px solid #333;
            padding: 4px 2px;
            text-align: right;
            vertical-align: middle;
            overflow: hidden;
            word-wrap: break-word;
        }
        
        .main-table thead th {
            background-color: #3b5998;
            color: #fff;
            font-weight: bold;
            padding: 3px 2px;
        }
        
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        
        .desc-cell {
            padding-left: 5px;
        }
        
        .bank-row {
            margin-bottom: 2px;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
        
        .summary-row td {
            background-color: #f9f9f9;
            font-size: 9pt;
        }
        
    </style>
</head>
<body>

    <div style="margin: 0 20pt 0 0;">
        <table class="layout-table no-break">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <h1 style="font-size: 26pt; color: #3b5998; margin: 0; padding: 0; line-height: 26pt;"><strong>請求書</strong></h1>
                    
                    <br>
                    
                    <div>{!! nl2br(e($company->bill_to)) !!}</div>
                    
                    <br><br>
                </td>
                <td rowspan="2" style="width: 40%; vertical-align: top; text-align: right;">
                    <table class="layout-table no-break" style="width: auto; display: inline-block;">
                        <tr>
                            <td style="text-align: left;">
                                <div>請求日：{{ $invoice->invoice_date }}</div>
                                <div>請求番号：{{ $invoice->invoice_number }}</div>
                                
                                <br>
                                <br>
                                
                                <div>{{ $company->name }}</div>
                                @if($company->invoice_code)
                                    <div>{{ $company->invoice_code }}</div>
                                @endif
                                @if($company->postal_code)
                                    <div>〒{{ $company->postal_code }}</div>
                                @endif
                                <div>{{ $company->address }}</div>
                                <div>Tel: {{ $company->phone }} / Fax: {{ $company->fax }}</div>
                                @if($company->contact)
                                    <div>担当：{{ $company->contact }}</div>
                                @endif
                            </td>
                        </tr>
                        @if($company->setup_company_seal)
                        <tr>
                            <td stlye="text-align: right;">
                                <img src="{{ $company->setup_company_seal }}" style="height: 80pt; width: 80pt; margin: -60pt -20pt 0 0;">
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width: 60%; vertical-align: bottom;">
                    <div>下記の通りご請求申し上げます。</div>
                    @if($invoice->billing_title)
                        <div>{{ $invoice->billing_title }}</div>
                    @endif
                    @if($invoice->operation_date)
                        <div>
                            運行日：{{ $invoice->operation_date }}
                            @if($invoice->operation_days)
                                - {{ $invoice->operation_days }}
                            @endif
                        </div>
                    @endif
                </td>
            </tr>
        </table>
        
        <table class="no-break" style="width: 100%; margin: 5pt 0; border-spacing: 0;">
            <tr>
                <td style="width: 70%;">
                    <table class="no-break" style="border-spacing: 0;">
                        <tr>
                            <td style="white-space: nowrap; font-size: 14pt; border-bottom: 2px solid #333;">
                                ご請求金額({{$invoice->currency_code}})：<strong style="font-size: 15pt; font-weight: bold;">{{ number_format($invoice->total_amount) }}</strong>
                                @if($invoice->tax_mode == 1)(税込)@else(外税)@endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%; font-size: 10pt; text-align: right; vertical-align: bottom;">
                    @if($invoice->reservation_id)
                        予約ID：{{ $invoice->reservation_id }}
                    @endif
                </td>
            </tr>
        </table>
    
        <table class="main-table" style="margin: 0 0 5pt 0;">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">No.</th>
                    <th style="width: 58%; text-align: left; padding-left: 5px;" colspan="4">摘要</th>
                    <th style="width: 6%; text-align: center;">数量</th>
                    <th style="width: 12%; text-align: center;">単価</th>
                    <th style="width: 12%; text-align: center;">金額</th>
                    <th style="width: 6%; text-align: center;">税率</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: left; padding-left: 5px;" colspan="4">
                        {{ $item->description }}
                        @if(isset($item->period) && $item->period)
                            <br><span style="font-size:8pt;color:#666;">{{ $item->period }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price) }}</td>
                    <td style="text-align: right;">{{ number_format($item->amount) }}</td>
                    <td style="text-align: center;">
                        @if ($item->tax_rate == -1) 免税
                        @elseif ($item->tax_rate == -2) 非課税
                        @else {{ number_format($item->tax_rate) }}%
                        @endif
                    </td>
                </tr>
                @endforeach
    
                @php 
                    $detailCount = count($items); 
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
                    <td style="font-weight: bold;">{{ number_format($summary_10->subtotal ?? 0) }}</td>
                    <td>消費税</td>
                    <td style="font-weight: bold;">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
                    <td colspan="2" style="font-weight: bold;">小計</td>
                    <td colspan="2" style="font-weight: bold;">
                        @if($invoice->tax_mode == 1)
                            {{ number_format($invoice->total_amount) }}
                        @else
                            {{ number_format($invoice->subtotal_amount) }}
                        @endif
                    </td>
                </tr>
                <tr class="summary-row">
                    <td colspan="2" style="text-align: left;">8％対象</td>
                    <td style="font-weight: bold;">{{ number_format($summary_8->subtotal ?? 0) }}</td>
                    <td>消費税</td>
                    <td style="font-weight: bold;">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
                    <td colspan="2" style="font-weight: bold;">消費税{{ $invoice->tax_mode==1 ?"(内税)":"" }}</td>
                    <td colspan="2" style="font-weight: bold;">
                        @if($invoice->tax_mode==1)
                            ({{number_format($invoice->tax_amount)}})
                        @else
                            {{number_format($invoice->tax_amount)}}
                        @endif
                    </td>
                </tr>
                <tr class="summary-row">
                    <td colspan="2" style="text-align: left;">非課税/免税</td>
                    <td style="font-weight: bold;">{{ number_format($invoice->non_taxable) }}</td>
                    <td colspan="2"></td>
                    <td colspan="2" style="font-weight: bold;">請求合計</td>
                    <td colspan="2" style="font-weight: bold;">{{ number_format($invoice->total_amount) }}</td>
                </tr>
            </tbody>
        </table>
        
        <div style="font-size: 9pt; line-height: 1.4;">
            <div style="font-size: 11pt; font-weight: bold; margin-bottom: 8px;">
                <strong>
                    お支払いは {{ $invoice->due_date }} までに下記指定口座へお振込お願いします。
                    <br>
                    恐れ入りますが、振込手数料は貴社（お客様）にてご負担をお願い申し上げます。
                </strong>
            </div>
            
            @if(!empty($invoice->notes))
            <div style="font-weight: bold; margin-bottom: 4px; font-size: 11pt;">【備考】</div>
            <div style="line-height: 1.4; margin-bottom: 10px;">{!! nl2br(e($invoice->notes)) !!}</div>
            @endif
    
            <div style="font-weight: bold; margin-bottom: 4px; font-size: 11pt;">【振込先】</div>
            <div style="line-height: 1.4;">
                @foreach($bank as $line)
                    <div style="margin: 2px 0;">{{ $line }}</div>
                @endforeach
            </div>
        </div>
    </div>

</body>
</html>