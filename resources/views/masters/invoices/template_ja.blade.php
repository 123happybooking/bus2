<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>請求書 (Colgroup 强制列宽版)</title>
    <style>
        @page { size: A4; margin: 15mm 15mm; }
        body { font-family: "MS Mincho", "Yu Mincho", "Hiragino Mincho Pro", serif; font-size: 10pt; margin: 0; line-height: 1.3; color: #000; }
        .no-break { page-break-inside: avoid; }
        
        /* 头部与公司样式 (保持不变) */
        .header-section { display: flex; justify-content: space-between; margin-bottom: 0px; align-items: flex-start; }
        .main-title { font-size: 24pt; font-weight: bold; color: #3b5998; margin: 0; letter-spacing: 0; }
        .meta-info { text-align: right; font-size: 9pt; line-height: 1.4; }
        .top-section { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .client-name { width: 45%; font-size: 11pt; font-weight: bold; color: #333; line-height: 1.4; min-height: 80px; padding: 10px 0; box-sizing: border-box; }
        .company-info { width: 50%; text-align: right; font-size: 8.5pt; line-height: 1.4; margin-top: 15px; }
        .company-name { font-size: 10.5pt; font-weight: bold; margin-bottom: 2px; }
        .greeting { margin: 8px 0; font-size: 10pt; }
        .total-hint { text-align: right; margin-top: 15px; font-size: 14pt; line-height: 1; border-bottom: 2px solid #333; padding-bottom: 0; margin-bottom: 10px; display: inline-block; vertical-align: bottom; }
        .total-hint strong { font-size: 15pt; }

        /* ================= 核心表格样式 ================= */
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
        }

        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        
        .summary-row td { background-color: #f9f9f9; font-weight: bold; }
        .total-final-row td { background-color: #eef2f7; font-size: 10pt; }

        .footer-section { margin-top: 10px; font-size: 9pt; line-height: 1.4; }
        .payment-deadline { margin-bottom: 8px; font-size: 10pt; font-weight: bold; }
        .bank-info { width: 100%; line-height: 1.4; }
        div.bank-title { font-weight: bold; margin-bottom: 4px; display: block; font-size: 10pt; text-decoration: none !important; border-bottom: none !important; }
        .bank-row { display: flex; margin-bottom: 2px; }
        .bank-label { width: 80px; font-weight: bold; }
    </style>
</head>
<body>

    <!-- 1. 标题 -->
    <div class="header-section no-break">
        <h1 class="main-title">請求書</h1>
        <div class="meta-info">
            <div>請求日：{{ $invoice->invoice_date }}</div>
            <div>請求番号：{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <!-- 2. 客户与公司 -->
    <div class="top-section no-break">
        <div class="client-name">
            @foreach($customer as $customerLine)
                <div class="bank-line">{{ $customerLine }}</div>
            @endforeach
        </div>
        <div class="company-info">
            <div class="company-name">{{ $company->name }}</div>
            <div>〒{{ $company->postal_code }}</div>
            <div>{{ $company->address }}</div>
            <div>Tel: {{ $company->phone }} / Fax: {{ $company->fax }}</div>
            <div>担当：{{ $company->contact }}</div>
        </div>
    </div>

    <div class="greeting">下記の通りご請求申し上げます。</div>
    <div class="total-hint">
        ご請求金額({{$invoice->currency_code}})：<strong>{{ number_format($invoice->total_amount) }}</strong> 
        @if($invoice->tax_mode == 1)(内税)@else(外税)@endif
    </div>

    <!-- ================= 主表格 (使用 colgroup 强制列宽) ================= -->
    <table class="main-table">
        <!-- 【关键修复】在此处定义列宽，优先级最高，无视任何 CSS 冲突 -->
        <!-- 总和必须严格等于 100%: 5+11+17+15+17+5+12+10+8 = 100 -->
        <colgroup>
            <col style="width: 5%;">   <!-- c1: No -->
            <col style="width: 11%;">  <!-- c2: 摘要 part 1 -->
            <col style="width: 17%;">  <!-- c3: 摘要 part 2 -->
            <col style="width: 15%;">  <!-- c4: 摘要 part 3 -->
            <col style="width: 15%;">  <!-- c5: 摘要 part 4 -->
            <col style="width: 6%;">   <!-- c6: 数量 -->
            <col style="width: 10%;">  <!-- c7: 单价 -->
            <col style="width: 12%;">  <!-- c8: 金额 -->
            <col style="width: 9%;">   <!-- c9: 税率 -->
        </colgroup>

        <thead>
            <tr>
                <th class="c1">No.</th>
                <!-- 摘要：跨 4 列，不再需要 style="width"，colgroup 会自动处理 -->
                <th class="text-left" colspan="4" style="padding-left: 5px;">摘要</th>
                <th class="c6">数量</th>
                <th class="c7">単価</th>
                <th class="c8">金額</th>
                <th class="c9">税率</th>
            </tr>
        </thead>
        <tbody>
            {{-- A. 商品明细 --}}
            @foreach($items as $index => $item)
            <tr>
                <td class="c1">{{ $index + 1 }}</td>
                <td class="text-left" colspan="4" style="padding-left: 5px;">
                    {{ $item->description }}
                    @if(isset($item->period) && $item->period)
                        <br><span style="font-size:8pt;color:#666;">{{ $item->period }}</span>
                    @endif
                </td>
                <td class="c6">{{ $item->quantity }}</td>
                <td class="c7">{{ number_format($item->unit_price) }}</td>
                <td class="c8">{{ number_format($item->amount) }}</td>
                <td class="c9">
                    @if ($item->tax_rate == -1)
                    免税
                    @elseif ($item->tax_rate == -2)
                    非課税
                    @else
                    {{ number_format($item->tax_rate) }}%
                    @endif
                </td>
            </tr>
            @endforeach
            
            {{-- B. 补全空行 --}}
            @php 
                $detailCount = count($items);
                $summaryRows = 3; 
                $targetTotalRows = 15; 
                $remaining = max(0, $targetTotalRows - $detailCount - $summaryRows); 
            @endphp
            
            @for($i = 0; $i < $remaining; $i++)
            <tr>
                <td class="c1">&nbsp;</td>
                <td colspan="4"></td>
                <td class="c6"></td>
                <td class="c7"></td>
                <td class="c8"></td>
                <td class="c9"></td>
            </tr>
            @endfor

            {{-- C. 底部统计行 --}}
            {{-- 行 1: 10% 统计 --}}
            <tr class="summary-row">
                <td class="c1 text-left" colspan="2" style="padding-left: 5px; font-weight: normal;">10％対象</td>
                <td class="c3 text-right">{{ number_format($summary_10->subtotal ?? 0) }}</td>
                <td class="c4 text-right" style="font-weight: normal; font-size: 8.5pt;">消費税</td>
                <td class="c5 text-right">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
                <td class="c6 text-right" colspan="2" style="text-align: right;">小計</td>
                <td class="c8 text-right font-bold" colspan="2">
                    @if($invoice->tax_mode == 1){{ number_format($invoice->total_amount) }}@else{{ number_format($invoice->subtotal_amount) }}@endif
                </td>
            </tr>

            {{-- 行 2: 8% 统计 --}}
            <tr class="summary-row">
                <td class="c1 text-left" colspan="2" style="padding-left: 5px; font-weight: normal;">8％対象</td>
                <td class="c3 text-right">{{ number_format($summary_8->subtotal ?? 0) }}</td>
                <td class="c4 text-right" style="font-weight: normal; font-size: 8.5pt;">消費税</td>
                <td class="c5 text-right">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
                <td class="c6 text-right" colspan="2" style="text-align: right;">消費税{{ $invoice->tax_mode==1 ?"(内税)":"" }}</td>
                @if($invoice->tax_mode==1)
                <td class="c8 text-right font-bold" colspan="2">({{number_format($invoice->tax_amount)}})</td>
                @else
                <td class="c8 text-right font-bold" colspan="2">{{number_format($invoice->tax_amount)}}</td>
                @endif
            </tr>

            {{-- 行 3: 合计 --}}
            <tr class="summary-row">
                <td class="c1 text-left" colspan="2" style="padding-left: 5px; font-weight: normal;">非課税/免税</td>
                <td class="c3 text-right">{{ number_format($invoice->non_taxable) }}</td>
                <td class="c4" colspan="2"></td>
                <td class="c6 text-right" colspan="2" style="text-align: right;">請求合計</td>
                <td class="c8 text-right font-bold" colspan="2">{{ number_format($invoice->total_amount) }}</td>
            </tr>

        </tbody>
    </table>

    <!-- 5. 支付信息与备注 -->
    <div class="footer-section no-break">
        <div class="payment-deadline">
            お支払いは :{{ $invoice->due_date }} までに下記指定口座へお振込お願いします。
        </div>

        @if(!empty($invoice->notes))
        <div class="bank-title">【備考】</div>
        <div class="bank-info">{{ $invoice->notes }}</div>
        @endif

        <div class="bank-info">
            <div class="bank-title">【振込先】</div>
            <div class="bank-content">
                @foreach($bank as $line)
                    <div class="bank-line">{{ $line }}</div>
                @endforeach
            </div>
        </div>
    </div>

</body>
</html>