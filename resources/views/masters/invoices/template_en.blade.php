<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>INVOICE</title>
    <style>
        @page { size: A4; margin: 15mm 15mm; }
        body { 
            font-family: "Helvetica", "Arial", "MS Mincho", sans-serif; 
            font-size: 10pt; 
            margin: 0; 
            line-height: 1.3; 
            color: #000; 
        }
        .no-break { page-break-inside: avoid; }
        
        /* 头部与公司 */
        .header-section { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 0px; 
            align-items: flex-start; 
        }
        .main-title { 
            font-size: 24pt; 
            font-weight: bold; 
            color: #3b5998; 
            margin: 0; 
            letter-spacing: 1px; 
            text-transform: uppercase; 
        }
        .meta-info { text-align: right; font-size: 9pt; line-height: 1.4; }
        .top-section { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .client-name { 
            width: 45%; 
            font-size: 11pt; 
            font-weight: bold; 
            color: #333; 
            line-height: 1.4; 
            min-height: 80px;       
            padding: 10px 0;        
            box-sizing: border-box; 
        }
        .company-info { 
            width: 50%; 
            text-align: right; 
            font-size: 8.5pt; 
            line-height: 1.4; 
            margin-top: 15px; 
        }
        .company-name { font-size: 10.5pt; font-weight: bold; margin-bottom: 2px; }
        .greeting { margin: 8px 0; font-size: 10pt; font-style: italic; color: #555; }
        
        .total-hint {
            text-align: right;
            margin-top: 15px;
            font-size: 14pt; 
            line-height: 1; 
            border-bottom: 2px solid #333;
            padding-bottom: 0; 
            margin-bottom: 10px; 
            display: inline-block; 
            vertical-align: bottom; 
        }
        .total-hint strong { font-size: 15pt; }

        /* ================= 核心表格样式 (已应用日文版修复逻辑) ================= */
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; 
            font-size: 9pt; 
            margin-bottom: 0; 
        }
        .main-table th, .main-table td { 
            border: 1px solid #333333; 
            padding: 4px 2px; 
            text-align: center; 
            vertical-align: middle;
            overflow: hidden;
            word-wrap: break-word;
        }
        
        /* 【关键修复】强制最后一列显示双线边框，完全复刻日文版逻辑 */
        .main-table tr > th:last-child,
        .main-table tr > td:last-child {
            border-right: 2px double #333333 !important;
        }

        .main-table thead th { 
            background-color: #3b5998; 
            color: #fff; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
            font-size: 9pt;
            padding: 5px 2px;
            text-transform: uppercase;
        }

        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        
        .summary-row td {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .total-final-row td {
            background-color: #eef2f7;
            font-size: 10pt;
        }

        /* 底部信息 */
        .footer-section { margin-top: 10px; font-size: 9pt; line-height: 1.4; }
        .payment-deadline { margin-bottom: 8px; font-size: 10pt; font-weight: bold; color: #3b5998; text-transform: uppercase; }
        .bank-info { width: 100%; line-height: 1.4; }
        div.bank-title { font-weight: bold; margin-bottom: 4px; display: block; font-size: 10pt; text-decoration: none !important; border-bottom: none !important; color: #3b5998; text-transform: uppercase; }
        .bank-row { display: flex; margin-bottom: 2px; }
        .bank-label { width: 100px; font-weight: bold; }
    </style>
</head>
<body>

    <!-- 1. Header -->
    <div class="header-section no-break">
        <h1 class="main-title">INVOICE</h1>
        <div class="meta-info">
            <div><strong>Date:</strong> {{ $invoice->invoice_date }}</div>
            <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <!-- 2. Client & Company -->
    <div class="top-section no-break">
        <div class="client-name">
            @foreach($customer as $customerLine)
                <div class="bank-line">{{ $customerLine }}</div>
            @endforeach
        </div>
        <div class="company-info">
            <div class="company-name">{{ $company->name }}</div>
            <div>{{ $company->address }}</div>
            @if($company->postal_code)
                <div>{{ $company->postal_code }}</div>
            @endif
            <div>Tel: {{ $company->phone }} | Fax: {{ $company->fax }}</div>
            @if($company->contact)
                <div>Attn: {{ $company->contact }}</div>
            @endif
        </div>
    </div>
    
    <div class="total-hint">
        Total Amount ({{ $invoice->currency_code }}): <strong>{{ number_format($invoice->total_amount) }}</strong> 
        @if($invoice->tax_mode == 1)(Inc. Tax)@else(Excl. Tax)@endif
    </div>

    <!-- ================= Main Table ================= -->
    <table class="main-table">
        <!-- 列宽定义 (保持总和 100%) -->
        <colgroup>
            <col style="width: 5%;">   <!-- No. -->
            <col style="width: 11%;">  <!-- Desc Part 1 -->
            <col style="width: 17%;">  <!-- Desc Part 2 -->
            <col style="width: 15%;">  <!-- Desc Part 3 -->
            <col style="width: 15%;">  <!-- Desc Part 4 -->
            <col style="width: 6%;">   <!-- Qty -->
            <col style="width: 10%;">  <!-- Unit Price -->
            <col style="width: 12%;">  <!-- Amount -->
            <col style="width: 9%;">   <!-- Tax Rate -->
        </colgroup>

        <thead>
            <tr>
                <th>No.</th>
                <th class="text-left" colspan="4" style="padding-left: 5px;">Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Amount</th>
                <th>Tax Rate</th>
            </tr>
        </thead>
        <tbody>
            {{-- A. Items --}}
            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left" colspan="4" style="padding-left: 5px;">
                    {{ $item->description }}
                    @if(isset($item->period) && $item->period)
                        <br><span style="font-size:8pt;color:#666;">{{ $item->period }}</span>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td class="text-center">{{ number_format($item->unit_price) }}</td>
                <td class="text-center">{{ number_format($item->amount) }}</td>
                <td>
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
            
            {{-- B. Empty Rows --}}
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

            {{-- C. Summary Rows --}}
            
            {{-- Row 1: 10% Taxable --}}
            <tr class="summary-row">
                <td class="text-left" colspan="2" style="padding-left: 5px; font-weight: normal;">10% Taxable</td>
                <td class="text-right">{{ number_format($summary_10->total_with_tax ?? 0) }}</td>
                <td class="text-right" style="font-weight: normal; font-size: 8.5pt;">Tax</td>
                <td class="text-right">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
                <td class="text-right" colspan="2" style="text-align: right;">Subtotal</td>
                <td class="text-right font-bold" colspan="2">
                    @if($invoice->tax_mode == 1){{ number_format($invoice->total_amount) }}@else{{ number_format($invoice->subtotal_amount) }}@endif
                </td>
            </tr>

            {{-- Row 2: 8% Taxable --}}
            <tr class="summary-row">
                <td class="text-left" colspan="2" style="padding-left: 5px; font-weight: normal;">8% Taxable</td>
                <td class="text-right">{{ number_format($summary_8->total_with_tax ?? 0) }}</td>
                <td class="text-right" style="font-weight: normal; font-size: 8.5pt;">Tax</td>
                <td class="text-right">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
                <td class="text-right" colspan="2" style="text-align: right;">Total Tax</td>
                @if($invoice->tax_mode==1)
                <td class="text-right font-bold" colspan="2">({{ number_format($invoice->tax_amount) }})</td>
                @else
                <td class="text-right font-bold" colspan="2">{{ number_format($invoice->tax_amount) }}</td>
                @endif
            </tr>

            {{-- Row 3: Non-Taxable & Total Tax --}}
            <tr class="summary-row">
                <td class="text-left" colspan="2" style="padding-left: 5px; font-weight: normal;">Non-Taxable</td>
                <td class="text-right">{{ number_format($invoice->non_taxable) }}</td>
                <td colspan="2"></td>
                <td class="text-right" colspan="2" style="text-align: right;">GRAND TOTAL</td>
                <td class="text-right font-bold" colspan="2">{{ number_format($invoice->total_amount) }}</td>
            </tr>

        </tbody>
    </table>

    <!-- 5. Payment Info -->
    <div class="footer-section no-break">
        <div class="payment-deadline">
            Payment Due By: {{ $invoice->due_date }}
        </div>

        @if(!empty($invoice->notes))
        <div class="bank-title">Remarks</div>
        <div class="bank-info" style="margin-bottom: 8px;">{{ $invoice->notes }}</div>
        @endif

        <div class="bank-info">
            <!-- 已修复拼写错误: Bank Details/div> -> Bank Details</div> -->
            <div class="bank-title">Bank Details</div>
            <div class="bank-content">
                @foreach($bank as $line)
                    <div class="bank-line">{{ $line }}</div>
                @endforeach
            </div>
        </div>
    </div>

</body>
</html>