<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>損益計算書</title>
    <style>
        /* --- 全局重置 --- */
        body {
            margin: 0;
            font-family: "MS PGothic", "Yu Gothic", "Hiragino Kaku Gothic ProN", "游ゴシック", "ＭＳ ゴシック", sans-serif;
            background-color: #fff;
            color: #333;
        }

        /* --- 头部样式 (完全保留) --- */
        .header-group {
            text-align: center;
            margin-bottom: 2px;
        }
        .title {
            font-size: 24px;
            margin: 0 0 5px 0;
        }
        .period {
            margin: 0 0 5px 0;
            font-size: 14px;
        }
        .company-unit {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        /* --- 表格核心样式 (应用你的极致紧凑要求) --- */
        .custom-table {
            width: 100%;
            border-collapse: collapse; /* 合并边框 */
            margin-bottom: 20px;
            border: 1px solid #000; /* 仅保留最外层边框 */
            table-layout: fixed; /* 锁定列宽，防止错位 */
        }

        .custom-table th, 
        .custom-table td {
            padding: 1px 10px; /* 极度紧凑：上下仅1px */
            vertical-align: top; /* 统一顶部对齐，解决幽灵边距问题 */
            font-size: 12px; /* 字体调小 */
            line-height: 1.2;
            border: none; /* 彻底移除内部所有线条 */
        }

        /* 表头特定样式 */
        .custom-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
            /* 注意：这里不再加 border-bottom，确保完全无内线 */
        }

        /* 内容特定样式 */
        .custom-table td.item-name {
            text-align: left;
            color: #444;
            font-weight: 500;
            padding-left: 30px; 
        }

        /* 数字格式 */
        .number {
            text-align: right;
            font-family: monospace; /* 等宽字体方便对账 */
            white-space: nowrap;
        }

        /* 特殊行背景与样式 */
        .bs-section-title {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: left;
        }
        .bs-subtotal {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- 头部信息 -->
    <div class="header-group">
        <h5 class="title">キャッシュフロー計算書</h5>
        <div class="period">自 {{ $startDate }}<br>至 {{ $endDate }}</div>
        <div class="company-unit">
            <span>{{$company->company_name}}</span>
            <span>(単位:円)</span>
        </div>
    </div>

    <!-- 表格主体 -->
    <table class="custom-table bs-table mb-0">
        <thead>
            <!-- <tr>
                <th style="width: 60%; text-align: left !important;">項目</th>
                <th style="width: 40%;">金額</th>
            </tr> -->
        </thead>
        <tbody>
            <?php
            
            $totalNetCash = 0; // 期末現金及現金等價物餘額合計

            foreach($cashOuts as $section):
                $items = $section['items'];
                $title = $section['title'];
                $sumCurrent = 0;

                // 计算小计
                foreach($items as $item):
                    $current = $item->cashOutData?->current_amount ?? 0;
                    $sumCurrent += $current;
                endforeach;

                // 判断是否需要加总到最终合计
                if(!in_array($title, ['現金及約當現金期末餘額', '期末現金及現金等價物餘額'])) {
                    $totalNetCash += $sumCurrent;
                }
            ?>

            <!-- 分组标题 -->
            <tr class="section-title">
                <td colspan="2" class="bs-section-title"><?php echo htmlspecialchars($title); ?></td>
            </tr>

            <?php foreach($items as $item): 
                $currentAmount = $item->cashOutData?->current_amount ?? 0;
                $diff = $currentAmount;
            ?>
            <tr>
                <td class="item-name"><?php echo htmlspecialchars($item->title); ?></td>
                <td class="number" style="color: <?php echo $diff >= 0 ? 'black' : 'red'; ?>;">
                    <?php echo number_format($currentAmount); ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <!-- 分组小计 -->
            <tr class="bs-subtotal">
                <td class="text-end" style="padding-left:200px;"><?php echo htmlspecialchars($title); ?>小計</td>
                <td class="number" style="color: <?php echo $sumCurrent >= 0 ? 'black' : 'red'; ?>;">
                    <?php echo number_format($sumCurrent); ?>
                </td>
            </tr>

            <?php 
            endforeach; 
            ?>

        </tbody>
    </table>

</body>
</html>