<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>INVOICE</title>
    <style>
        body { 
            font-family: "Helvetica", "Arial", "MS Mincho", sans-serif; 
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
        
        
        
        .footer-section { margin-top: 10px; font-size: 9pt; line-height: 1.4; }
        .payment-deadline { margin-bottom: 8px; font-size: 10pt; font-weight: bold; color: #3b5998; text-transform: uppercase; }
        .bank-info { width: 100%; line-height: 1.4; }
        div.bank-title { font-weight: bold; margin-bottom: 4px; display: block; font-size: 10pt; text-decoration: none !important; border-bottom: none !important; color: #3b5998; text-transform: uppercase; }
        .bank-row { display: flex; margin-bottom: 2px; }
        .bank-label { width: 100px; font-weight: bold; }
    </style>
</head>
<body>
    <div style="margin: 0 20pt 0 0;">
        <table class="layout-table no-break">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <h1 style="font-size: 26pt; color: #3b5998; margin: 0; padding: 0; line-height: 26pt;"><strong>INVOICE</strong></h1>
                    
                    <br>
                    
                    <div>{!! nl2br(e($company->bill_to)) !!}</div>
                    
                    <br><br>
                </td>
                <td rowspan="2" style="width: 40%; vertical-align: top; text-align: right;">
                    <table class="layout-table no-break" style="width: auto; display: inline-block;">
                        <tr>
                            <td style="text-align: left;">
                                <div><strong>Date:</strong> {{ $invoice->invoice_date }}</div>
                                <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
                                
                                <br>
                                <br>
                                
                                <div style="font-size: 11pt;">{{ $company->name }}</div>
                                @if($company->invoice_code)
                                    <div>{{ $company->invoice_code }}</div>
                                @endif
                                <div>{{ $company->address }}</div>
                                @if($company->postal_code)
                                    <div>{{ $company->postal_code }}</div>
                                @endif
                                <div>Tel: {{ $company->phone }} | Fax: {{ $company->fax }}</div>
                                @if($company->contact)
                                    <div>Attn: {{ $company->contact }}</div>
                                @endif
                            </td>
                        </tr>
                        @if($company->setup_company_seal)
                        <tr>
                            <td stlye="text-align: right;">
                                <img src="{{ $company->setup_company_seal }}" style="height: 100pt; width: 100pt; margin: -80pt -20pt 0 0;">
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width: 60%; vertical-align: bottom;">
                    @if($invoice->billing_title)
                        <div>{{ $invoice->billing_title }}</div>
                    @endif
                    @if($invoice->operation_date)
                        <div>Operation Date: {{ $invoice->operation_date }}</div>
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
                                Total Amount ({{ $invoice->currency_code }}): <strong>{{ number_format($invoice->total_amount) }}</strong> 
                                @if($invoice->tax_mode == 1)(Inc. Tax)@else(Excl. Tax)@endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%; font-size: 10pt; text-align: right; vertical-align: bottom;">
                    @if($invoice->reservation_id)
                        Reservation ID: {{ $invoice->reservation_id }}
                    @endif
                </td>
            </tr>
        </table>
        
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">No.</th>
                    <th style="width: 58%; text-align: left; padding-left: 5px;" colspan="4">Description</th>
                    <th style="width: 6%; text-align: center;">Qty</th>
                    <th style="width: 10%; text-align: center;">Unit Price</th>
                    <th style="width: 10%; text-align: center;">Amount</th>
                    <th style="width: 10%; text-align: center;">Tax Rate</th>
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
                    <td style="text-align: center;">{{ number_format($item->unit_price) }}</td>
                    <td style="text-align: center;">{{ number_format($item->amount) }}</td>
                    <td style="text-align: center;">
                        @if ($item->tax_rate == -1)
                            Tax Exempt
                        @elseif ($item->tax_rate == -2)
                            Non-Taxable
                        @else
                            {{ number_format($item->tax_rate) }}%
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
                    <td colspan="2" style="text-align: left;">10% Taxable</td>
                    <td style="font-weight: bold;">{{ number_format($summary_10->total_with_tax ?? 0) }}</td>
                    <td>Tax</td>
                    <td style="font-weight: bold;">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
                    <td colspan="2" style="font-weight: bold;">Subtotal</td>
                    <td colspan="2" style="font-weight: bold;">
                        @if($invoice->tax_mode == 1){{ number_format($invoice->total_amount) }}@else{{ number_format($invoice->subtotal_amount) }}@endif
                    </td>
                </tr>
                <tr class="summary-row">
                    <td colspan="2" style="text-align: left;">8% Taxable</td>
                    <td style="font-weight: bold;">{{ number_format($summary_8->total_with_tax ?? 0) }}</td>
                    <td>Tax</td>
                    <td style="font-weight: bold;">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
                    <td colspan="2" style="font-weight: bold;">Total Tax</td>
                    <td colspan="2" style="font-weight: bold;">
                        @if($invoice->tax_mode==1)
                            ({{number_format($invoice->tax_amount)}})
                        @else
                            {{number_format($invoice->tax_amount)}}
                        @endif
                    </td>
                </tr>
                <tr class="summary-row">
                    <td colspan="2" style="text-align: left;">Non-Taxable</td>
                    <td style="font-weight: bold;">{{ number_format($invoice->non_taxable) }}</td>
                    <td colspan="2"></td>
                    <td colspan="2" style="font-weight: bold;">GRAND TOTAL</td>
                    <td colspan="2" style="font-weight: bold;">{{ number_format($invoice->total_amount) }}</td>
                </tr>
            </tbody>
        </table>
    
        <div class="footer-section no-break">
            <div class="payment-deadline">
                Payment Due By: {{ $invoice->due_date }}
            </div>
    
            @if(!empty($invoice->notes))
            <div class="bank-title">Remarks</div>
            <div class="bank-info" style="margin-bottom: 8px;">{!! nl2br(e($invoice->notes)) !!}</div>
            @endif
    
            <div class="bank-info">
                <div class="bank-title">Bank Details</div>
                <div class="bank-content">
                    @foreach($bank as $line)
                        <div class="bank-line">{{ $line }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</body>
</html>