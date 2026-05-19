<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>請求書マスター</title>
    <style>
        /* --- 基础与字体样式 (仿照貸借対照表) --- */
        body {
            font-family: "Meiryo", "MS Gothic", sans-serif;
            background-color: #f0f0f0;
            margin: 0;
        }
        .container {
            background: white;
            width: 100%;
            max-width: 1400px; /* 限制最大宽度，保持美观 */
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        /* --- 头部样式 --- */
        .main-title {
            text-align: center;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        .date-section {
            text-align: center;
            font-size: 14px;

        }
        .header-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 14px;
        }
        .company-name {
            font-family: "Courier New", monospace;
            font-weight: normal;
        }

        /* --- 表格通用样式 --- */
        .bs-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #000; 
            font-size: 14px;
        }
        .bs-table th, .bs-table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            vertical-align: middle;
        }

        /* --- 表头样式 --- */
        .table-header th {
            background-color: #e9ecef;
            text-align: center;
            font-size: 13px;
        }

        /* --- 内容行样式 --- */
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-muted { color: #6c757d; }
        .text-primary { color: #007bff;}
        .font-monospace { font-family: "Courier New", monospace; }
        
        /* 悬停效果 */
        .bs-table tbody tr:hover td {
            background-color: #e9ecef !important;
            cursor: pointer;
            font-weight: 500;
        }

        /* --- 合计与小计行样式 --- */
        .subtotal-row {
            background-color: #f8f9fa;
        }
        .grand-total-row {
            background-color: #e2e6ea;
        }

        /* --- 打印样式 --- */
        @media print {
            body { background-color: #fff; padding: 0; }
            .container {
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }
            .bs-table { width: 100% !important; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- ================= 表格区域 ================= -->
    <div class="table-responsive">
        <table class="bs-table">
            <thead class="table-header">
                <tr>
                    <th style="width: 120px;">請求先</th>
                    <th style="width: 100px;">合計金額</th>
                    <th style="width: 100px;">未入金</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentAgencyId = null;
                    $agencyTotalAmount = 0;
                    $agencyPaidAmount = 0;
                    $grandTotalAmount = 0; // 总合计
                    $grandPaidAmount = 0;  // 总合计
                @endphp
                @forelse($invoices as $invoice)
                @php
                    // 如果 Agency 切换，或者这是第一条数据
                    $isFirstRow = $currentAgencyId === null;
                    $isAgencyChanged = $currentAgencyId !== $invoice->agency_id;

                    if ($isFirstRow || $isAgencyChanged) {
                        // 如果不是第一行，先输出上一个 Agency 的小计
                        if (!$isFirstRow) {
                            echo '<tr class="subtotal-row">';
                            echo '<td class="text-center">'.$currentAgencyName.'</td>';
                            echo '<td class="text-center font-monospace">'.number_format($agencyTotalAmount).'</td>';
                            echo '<td class="text-center font-monospace">'.number_format($agencyTotalAmount - $agencyPaidAmount).'</td>';
                            echo '</tr>';
                        }

                        // 重置当前 Agency 的统计
                        $currentAgencyId = $invoice->agency_id;
                        $currentAgencyName = $invoice->agency->agency_name ?? '無名請求先';
                        $agencyTotalAmount = 0;
                        $agencyPaidAmount = 0;
                    }

                    // 累加金额
                    $agencyTotalAmount += $invoice->total_amount;
                    $agencyPaidAmount += $invoice->paid_amount;

                    // 累加全局合计
                    $grandTotalAmount += $invoice->total_amount;
                    $grandPaidAmount += $invoice->paid_amount;
                @endphp

                @empty
                @endforelse

                @if($currentAgencyId !== null)
                    <tr class="subtotal-row">
                        <td  class="text-center">{{ $currentAgencyName }}</td>
                        <td class="text-center font-monospace">{{ number_format($agencyTotalAmount) }}</td>
                        <td class="text-center font-monospace">{{ number_format($agencyTotalAmount - $agencyPaidAmount) }}</td>
                    </tr>
                @endif

                <tr class="grand-total-row">
                    <td class="text-end">合計：</td>
                    <td class="text-center font-monospace">{{ number_format($grandTotalAmount) }}</td>
                    <td class="text-center font-monospace">{{ number_format($grandTotalAmount - $grandPaidAmount) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>