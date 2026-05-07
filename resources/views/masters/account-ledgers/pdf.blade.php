<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>総勘定元帳 - {{ $account_name ?? '未設定' }}</title>
<style>
    /* --- 基础样式 (屏幕显示用) --- */
    body {
        font-family: "Meiryo", "Hiragino Kaku Gothic Pro", "游ゴシック", "Yu Gothic", sans-serif;
        font-size: 11px; /* 稍微调小字体以适应A4宽度 */
        color: #333;
        margin: 0; /* 屏幕上去除默认边距，打印时由@page控制 */
        background-color: #fff;
        -webkit-print-color-adjust: exact; /* 强制打印背景色 */
        print-color-adjust: exact;
    }

    .header {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #333; /* 加个底边线更清晰 */
    }

    .header h1 {
        margin: 0 0 10px 0;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        letter-spacing: 2px;
    }

    .meta-info {
        font-size: 12px;
        color: #555;
        padding: 0 10px;
    }

    table {
        width: 100%; 
        border-collapse: collapse;
        table-layout: fixed; /* 固定表格布局，防止内容撑开 */
    }

    th, td {
        border: 1px solid #333;
        padding: 5px 4px;
        text-align: left;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
        height: 24px;
    }

    /* --- 列宽控制 --- */
    /* 打印时这些百分比会自动适应A4宽度 */
    .col-date { width: 18%; text-align: center; }
    .col-account { width: 15%; }
    .col-remark { width: 15%; }
    .col-debit { width: 15%; text-align: right; }
    .col-credit { width: 15%; text-align: right; }
    .col-balance { width: 10%; text-align: right; }

    .text-right { text-align: right; }
    .text-end { text-align: right; }
    .fw-bold { font-weight: bold; }
    
    .summary-row td {
        background-color: #e9ecef !important;
        font-weight: bold;
    }

    /* --- 【关键】打印专用样式 --- */
    @page {
        size: A4;          /* 强制设置为A4尺寸 */
        margin: 15mm;      /* 上下左右边距设为15mm (可根据打印机能力调整为10mm) */
    }

    @media print {
        body {
            margin: 0;
            background-color: #fff;
        }

        /* 确保表格宽度填满 (A4宽 - 左右边距) */
        table {
            width: 100% !important; 
            margin: 0 auto;
        }

        /* 防止分页时表格行被切断 */
        tr {
            page-break-inside: avoid;
        }
        
        /* 头部信息强制打印颜色 */
        .header {
            border-bottom: 2px solid #000;
        }
    }
</style>
</head>
<body>

    <div class="header">
        <h1>勘定元帳</h1>
        <div class="meta-info">
            <strong>勘定科目：</strong> {{ $account_name }}
            @if(!empty($start_date) || !empty($end_date))
                <br><strong>期間：</strong> 
                {{ $start_date ?? '開始日未設定' }} ～ {{ $end_date ?? '終了日未設定' }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-date">
                    <div>日付</div>
                    <div>伝票ID</div>
                    <div>生成元</div>
                </th>
                <th class="col-account">
                    <div>相手勘定科目</div>
                    <div>相手補助科目</div>
                </th>
                <th class="col-remark">摘要</th>
                <th class="col-debit">
                    <div>補助科目</div>
                    <div>税区分</div>
                    <div>借方金額</div>
                </th>
                <th class="col-credit">
                    <div>相手税区分</div>
                    <div>貸方金額</div>
                </th>
                <th class="col-balance">残高</th>
            </tr>
        </thead>
        <tbody>
            @if(empty($rows))
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">
                        該当するデータがありません。
                    </td>
                </tr>
            @else
                @php
                    $currentBalance = $opening_balance;
                    $monthlyJieTotal = 0;
                    $monthlyDaiTotal = 0;
                    $lastMonthKey = '';
                    $rowCount = 0;
                    $initialOpeningBalance = $opening_balance;
                @endphp

                @forelse($rows as $index => $row)
                    @php
                        // --- 1. 数据清洗 ---
                        $dateStr = $row['date'];
                        $jieVal = (int) round((float) str_replace(',', '', $row['jie_money'] ?? '') ?: 0);
                        $daiVal = (int) round((float) str_replace(',', '', $row['dai_money'] ?? '') ?: 0);

                        // --- 2. 提取月份 Key ---
                        $currentMonthKey = '';
                        if (strpos($dateStr, '-') !== false) {
                            $currentMonthKey = substr($dateStr, 0, 7); // YYYY-MM
                        } elseif (strpos($dateStr, '/') !== false) {
                            $parts = explode('/', $dateStr);
                            $currentMonthKey = "20{$parts[0]}-{$parts[1]}";
                        }

                        // --- 3. 核心统计逻辑 ---
                        $monthlyJieTotal += $jieVal;
                        $monthlyDaiTotal += $daiVal;
                        $currentBalance += $jieVal - $daiVal;

                        $shouldRenderSummary = ($index > 0 && !empty($lastMonthKey) && $currentMonthKey !== $lastMonthKey);
                    @endphp

                    {{-- 1. 渲染上个月的汇总行 --}}
                    @if($shouldRenderSummary)
                        <tr class="summary-row">
                            <td colspan="3" class="text-end">当月合計 ({{ $lastMonthKey }})</td>
                            <td class="text-right">{{ number_format($monthlyJieTotal - $jieVal) }}</td>
                            <td class="text-right">{{ number_format($monthlyDaiTotal - $daiVal) }}</td>
                            <td></td>
                        </tr>

                        @php
                            $monthlyJieTotal = $jieVal;
                            $monthlyDaiTotal = $daiVal;
                        @endphp
                    @endif

                    {{-- 2. 期初余额行 --}}
                    @if($index === 0)
                        <tr style="background-color: #f8f9fa;">
                            <td colspan="3" class="text-end fw-bold text-primary">前月繰越</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right fw-bold">{{ $mark==1 ? number_format($initialOpeningBalance) : number_format(abs($initialOpeningBalance)) }}</td>
                        </tr>
                    @elseif($shouldRenderSummary)
                        <tr style="background-color: #f8f9fa;">
                            <td colspan="3" class="text-end fw-bold text-primary">前月繰越</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right fw-bold">{{ $mark==1 ? number_format($currentBalance - $jieVal + $daiVal) : number_format(abs($currentBalance - $jieVal + $daiVal)) }}</td>
                        </tr>
                    @endif

                    {{-- 3. 数据行 --}}
                    <tr>
                        <td class="text-center col-date">
                            <div>{{ $dateStr }}</div>
                            <div>{{ $row['source_id'] }}</div>
                            <div></div>

                        </td>
                        <td class="col-account">
                            <div>{{ $row['account_name'] }}</div>
                            <div>{{ $row['sub_account_name'] }}</div>
                            
                        </td>
                        <td class="col-account">
                            <div>{{ $row['remark'] }}</div>          
                        </td>
                        <td class="text-right col-debit">
                            <div>{{ $row['curr_sub_account_name'] }}</div>
                            <div>{{ $row['curr_tax_category'] }}</div>
                            @if($jieVal > 0)<div> {{ number_format($jieVal) }}</div> @endif
                        </td>
                        <td class="text-right col-credit">
                            <div>{{ $row['tax_category'] }}</div>
                            @if($daiVal > 0) <div>{{ number_format($daiVal) }}</div> @endif
                        </td>
                        <td class="text-right col-balance">{{ $mark==1 ? number_format($currentBalance) : number_format(abs($currentBalance)) }}</td>
                    </tr>

                    @php
                        $lastMonthKey = $currentMonthKey;
                        $rowCount++;
                    @endphp
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 20px;">
                            該当するデータがありません。
                        </td>
                    </tr>
                @endforelse

                {{-- 5. 最后一个月的汇总 --}}
                @if($rowCount > 0)
                    <tr class="summary-row">
                        <td colspan="3" class="text-end">当月合計 ({{ $lastMonthKey }})</td>
                        <td class="text-right">{{ number_format($monthlyJieTotal) }}</td>
                        <td class="text-right">{{ number_format($monthlyDaiTotal) }}</td>
                        <td></td>
                    </tr>
                @endif
            @endif
        </tbody>
    </table>

</body>
</html>