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
            letter-spacing: 2px;
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


        /* 2. 针对打印场景优化容器和内容 */
       @media print {
            body { 
                background-color: #fff; 
                padding: 0; 
                margin: 0;
                -webkit-print-color-adjust: exact;
            }
            .container {
                box-shadow: none;
                width: 210mm !important;
                max-width: 210mm !important;
                margin: 0 auto;
                box-sizing: border-box;
            }
            
            /* 为每一页添加顶部间距 */
            @page {
                size: A4;
            }
            /* --- 核心修改开始 --- */
            
            /* 1. 允许大表格跨页，而不是把整个表格挤到下一页 */
            .bs-table { 
                width: 100% !important; 
                page-break-inside: auto; 
                break-inside: auto;
            }

            /* 2. 尽量保证每一行(tr)的完整性，不要在行中间切断 */
            .bs-table tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* 3. 让 <thead> 在每一页顶部自动重复显示（必须配合正确的HTML结构） */
            .bs-table thead {
                display: table-header-group;
            }
            
            /* 4. 防止小计和合计行被切断 */
            .subtotal-row, .grand-total-row {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            
            /* --- 核心修改结束 --- */
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
                    <th style="width: 120px;">タイトル</th>
                    <th style="width: 100px;">運行日</th>
                    <th style="width: 100px;">請求日</th>
                    <th style="width: 100px;">合計金額</th>
                    <th style="width: 100px;">未入金</th>
                    <th style="width: 80px;">タイプ</th>
                    <th style="width: 80px;">請求担当</th>
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
                            echo '<td colspan="4" class="text-end">【'.$currentAgencyName.'】 小計：</td>';
                            echo '<td class="text-end font-monospace">'.number_format($agencyTotalAmount).'</td>';
                            echo '<td class="text-end font-monospace">'.number_format($agencyTotalAmount - $agencyPaidAmount).'</td>';
                            echo '<td colspan="2"></td>';
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
                <tr>
                    <td>{{ $invoice->agency->agency_name ?? ''}}</td>
                    <td class="text-center">{{ $invoice->billing_title }}</td>
                    <td class="text-center">
                        {{ $invoice->operation_date?->format('Y/m/d') }}
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') }}
                    </td>
                    <td class="text-end font-monospace">
                        {{ number_format($invoice->total_amount, 0) }}
                    </td>
                    <td class="text-end font-monospace">
                        {{ number_format($invoice->total_amount - $invoice->paid_amount, 0) }}
                    </td>
                    <td class="text-center">
                        {{ $invoice->type == 1 ? '正式' : '臨時' }}
                    </td>
                    <td class="text-center">{{ $invoice->staff->name ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 30px;">
                        <div class="text-muted">
                            <p class="mb-0">データが見つかりませんでした</p>
                            <p class="small">検索条件を変更してお試しください</p>
                        </div>
                    </td>
                </tr>
                @endforelse

                @if($currentAgencyId !== null)
                    <tr class="subtotal-row">
                        <td colspan="4" class="text-end">【{{ $currentAgencyName }}】 小計：</td>
                        <td class="text-end font-monospace">{{ number_format($agencyTotalAmount) }}</td>
                        <td class="text-end font-monospace">{{ number_format($agencyTotalAmount - $agencyPaidAmount) }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endif

                <tr class="grand-total-row">
                    <td colspan="4" class="text-end">合計：</td>
                    <td class="text-end font-monospace">{{ number_format($grandTotalAmount) }}</td>
                    <td class="text-end font-monospace">{{ number_format($grandTotalAmount - $grandPaidAmount) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>