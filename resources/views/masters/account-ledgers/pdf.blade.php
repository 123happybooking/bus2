<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>総勘定元帳 - {{ $account_name ?? '未設定' }}</title>
    <style>
        body {
            font-family: "Meiryo", "Hiragino Kaku Gothic Pro", "游ゴシック", "Yu Gothic", sans-serif;
            font-size: 12px;
            color: #333;
            margin: 10px;
            background-color: #fff;
        }

        .header {
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .meta-info {
            margin-top: 10px;
            font-size: 12px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
            vertical-align: middle; /* 垂直居中 */
            white-space: nowrap;    /* 强制不换行 */
            overflow: hidden;       /* 超出隐藏 */
            text-overflow: ellipsis;/* 超出显示省略号 */
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            height: 20px;
        }

        /* 列宽控制 */
        .col-date { width: 10%; text-align: center; }
        .col-account { width: 15%; }
        .col-summary { width: 35%; }
        .col-debit { width: 15%; text-align: right; }
        .col-credit { width: 15%; text-align: right; }
        .col-balance { width: 10%; text-align: right; }

        .text-right { text-align: right; }
        .text-end { text-align: right; } /* 兼容旧版 */
        .fw-bold { font-weight: bold; }
        
        /* 汇总行样式 */
        .summary-row td {
            background-color: #e9ecef !important;
            font-weight: bold;
        }

        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        @media print {
            body { margin: 0; }
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
                <th class="col-date">日付</th>
                <th class="col-account">勘定科目</th>
                <th class="col-summary">補助科目 / 税区分</th>
                <th class="col-debit">借方</th>
                <th class="col-credit">貸方</th>
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
                    $currentMonthKey = "20{$parts[0]}-{$parts[1]}"; // 假设 YY/MM
                }

                // --- 3. 核心统计逻辑 ---
                // A. 先把当前行的金额累加到当月总额中
                $monthlyJieTotal += $jieVal;
                $monthlyDaiTotal += $daiVal;

                // B. 余额计算 (全局)
                $currentBalance += $jieVal - $daiVal;

                // C. 检测是否需要渲染“上个月”的汇总
                // 条件：不是第一行，且当前月份 != 上个月份
                $shouldRenderSummary = ($index > 0 && !empty($lastMonthKey) && $currentMonthKey !== $lastMonthKey);
            @endphp

            {{-- --- 视图渲染 --- --}}


            {{-- 1. 渲染上个月的汇总行 (如果需要) --}}
            @if($shouldRenderSummary)
                <tr class="summary-row">
                    <td colspan="3" class="text-end">当月合計 ({{ $lastMonthKey }})</td>
                    <td class="text-right">{{ number_format($monthlyJieTotal - $jieVal) }}</td>
                    <td class="text-right">{{ number_format($monthlyDaiTotal - $daiVal) }}</td>
                    <td></td>
                </tr>

                @php
                    // 修正：月份切换，总额归零（减去当前行，因为当前行属于新月份）
                    $monthlyJieTotal = $jieVal;
                    $monthlyDaiTotal = $daiVal;
                @endphp
            @endif

            @if($index === 0)
                <tr style="background-color: #f8f9fa;">
                    <td colspan="3" class="text-end fw-bold text-primary">前月繰越1</td>
                    <td class="text-right"></td>
                    <td class="text-right"></td>
                    <td class="text-right fw-bold">{{ $mark==1 ? number_format($initialOpeningBalance) : number_format(abs($initialOpeningBalance)) }}</td>
                </tr>
            @endif

            {{-- 逻辑：如果是新月份的第一行，在显示数据前，先显示期初余额 --}}
            @if($shouldRenderSummary)
                <tr style="background-color: #f8f9fa;">
                    <td colspan="3" class="text-end fw-bold text-primary">前月繰越2</td>
                    <td class="text-right"></td>
                    <td class="text-right"></td>
                    <td class="text-right fw-bold">{{ $mark==1 ? number_format($currentBalance - $jieVal + $daiVal) : number_format(abs($currentBalance - $jieVal + $daiVal)) }}</td>
                </tr>
            @endif

            {{-- 3. 渲染当前数据行 --}}
            <tr>
                <td class="text-center col-date">{{ $dateStr }}</td>
                <td class="col-account">{{ $row['account_name'] }}</td>
                <td class="col-summary">
                    {{ $row['sub_account_name'] ?? '' }}
                    @if(!empty($row['tax_category']))
                        {{ $row['tax_category'] }}
                    @endif
                </td>
                <td class="text-right col-debit">
                    @if($jieVal > 0) {{ number_format($jieVal) }} @endif
                </td>
                <td class="text-right col-credit">
                    @if($daiVal > 0) {{ number_format($daiVal) }} @endif
                </td>
                <td class="text-right col-balance">{{ $mark==1 ? number_format($currentBalance) : number_format(abs($currentBalance)) }}</td>
            </tr>

            @php
                // --- 4. 状态更新 (必须在渲染行之后) ---
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

        {{-- --- 5. 收尾：渲染最后一个月的汇总行 --- --}}
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