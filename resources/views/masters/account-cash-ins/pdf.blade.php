<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>キャッシュフロー計算書</title>
    <style>
        /* --- 全局重置 (统一损益计算书风格) --- */
        body {
            margin: 0;
            padding: 0;
            font-family: "MS PGothic", "Yu Gothic", "Hiragino Kaku Gothic ProN", "游ゴシック", "ＭＳ ゴシック", sans-serif;
            background-color: #fff;
            color: #333;
        }

        /* --- 表头部分（保留原样，未动）--- */
        .header-group {
            text-align: center;
            margin-bottom: 2px;
        }
        .title {
            font-size: 24px;
            margin-bottom: 5px;
            margin-top: 0;
        }
        .period {
            margin-bottom: 5px;
            font-size: 14px;
        }
        .company-unit {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* --- 表格基础样式 (修改点：极小字体 + 极度紧凑 + 完全无内线) --- */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000; /* 仅保留外框线 */
        }
        .custom-table th, 
        .custom-table td {
            /* 极度紧凑：上下仅1px，左右10px */
            padding: 1px 10px;
            vertical-align: middle;
            /* 字体调小 */
            font-size: 12px;
            /* 彻底移除所有边框定义 */
        }
        .custom-table td.item-name {
            text-align: left;
            color: #444;
            font-weight: 500;
        }
        .custom-table th {
            background-color: #f8f9fa;
            text-align: center;
            font-weight: bold;
            color: #555;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        /* --- 表格特有样式 (颜色保留，去线) --- */
        .bs-section-title { background-color: #e9ecef; font-weight: bold; text-align: left; }
        .bs-subtotal { background-color: #f8f9fa; font-weight: bold; }
        .bs-total-row { background-color: #d4edda; font-weight: bold; }

        /* --- 数字格式 --- */
        .number {
            text-align: right;
            font-family: monospace;
            white-space: nowrap;
            font-size: 12px; /* 数字字体同步缩小 */
        }

        /* --- 新增：分割线样式 --- */
        .divider-line {
            border-top: 2px solid #000; /* 粗黑线 */
            height: 5px; /* 控制线条间距 */
            background-color: #fff; /* 确保背景白色，遮盖下方可能的边框 */
        }
        .divider-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            background-color: #fff;
            padding: 5px 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- =======================================================
             1. 保留头部标题和时间 (完全保留原样)
             ======================================================= -->
        <div class="header-group">
            <h5 class="title">キャッシュフロー計算書</h5>
            <div class="period">自 {{ $startDate }}<br>至 {{ $endDate }}</div>
            <div class="company-unit">
                <span>{{$company->company_name}}</span>
                <span>(単位:円)</span>
            </div>
        </div>

        <!-- =======================================================
             2. 合并后的单一表格
             ======================================================= -->
        <table class="custom-table bs-table mb-0">
            <tbody>

                <!-- ********************** 第一部分：貸借対照表 (B/S) ********************** -->
                <?php
                // --- PHP 计算逻辑 (保持原样) ---
                $totalAssetsCurrent = 0;
                $totalAssetsPrevious = 0;
                $totalLiabilitiesCurrent = 0;
                $totalLiabilitiesPrevious = 0;
                $totalCapitalCurrent = 0;
                $totalCapitalPrevious = 0;

                foreach($cashIns as $section):
                    $items = $section['items'];
                    $title = $section['title'];
                    $sumCurrent = 0;
                    $sumPrevious = 0;
                    foreach($items as $item):
                        $current = $item->cashInData?->current_amount ?? 0;
                        $previous = $item->cashInData?->previous_amount ?? 0;
                        $sumCurrent += $current;
                        $sumPrevious += $previous;
                    endforeach;
                    if($title == '流動資産' || $title == '固定資産' || $title == '繰延資產') {
                        $totalAssetsCurrent += $sumCurrent;
                        $totalAssetsPrevious += $sumPrevious;
                    } elseif($title == '流動負債' || $title == '固定負債') {
                        $totalLiabilitiesCurrent += $sumCurrent;
                        $totalLiabilitiesPrevious += $sumPrevious;
                    } elseif($title == '資本の部') {
                        $totalCapitalCurrent += $sumCurrent;
                        $totalCapitalPrevious += $sumPrevious;
                    }
                endforeach;

                $totalLiabilityCapitalCurrent = $totalLiabilitiesCurrent + $totalCapitalCurrent;
                $totalLiabilityCapitalPrevious = $totalLiabilitiesPrevious + $totalCapitalPrevious;

                $currentDiff = $totalAssetsCurrent - $totalLiabilityCapitalCurrent;
                $previousDiff = $totalAssetsPrevious - $totalLiabilityCapitalPrevious;
                $currentStatus = $currentDiff == 0 ? 'OK' : 'NG';
                $currentBadgeClass = $currentDiff == 0 ? 'bg-success' : 'bg-danger';
                $previousStatus = $previousDiff == 0 ? 'OK' : 'NG';
                $previousBadgeClass = $previousDiff == 0 ? 'bg-success' : 'bg-danger';
                ?>
                <tr>
                    <td colspan="4" class="divider-title">貸借対照表</td>
                </tr>
                <?php foreach($cashIns as $section): 
                    $items = $section['items'];
                    $title = $section['title'];
                    $sumCurrent = 0;
                    $sumPrevious = 0;
                    foreach($items as $item):
                        $current = $item->cashInData?->current_amount ?? 0;
                        $previous = $item->cashInData?->previous_amount ?? 0;
                        $sumCurrent += $current;
                        $sumPrevious += $previous;
                    endforeach;
                ?>


                    <tr class="section-title">
                        <td colspan="4" class="bs-section-title"><?php echo htmlspecialchars($title); ?></td>
                    </tr>
                    <?php foreach($items as $item): 
                        $currentAmount = $item->cashInData?->current_amount ?? 0;
                        $previousAmount = $item->cashInData?->previous_amount ?? 0;
                        $diff = $currentAmount - $previousAmount;
                    ?>
                    <tr>
                        <td class="item-name"><?php echo htmlspecialchars($item->title); ?></td>
                        <td class="number"><?php echo number_format($currentAmount); ?></td>
                        <td class="number"><?php echo number_format($previousAmount); ?></td>
                        <td class="number" style="color: <?php echo $diff >= 0 ? 'black' : 'red'; ?>;"><?php echo number_format($diff); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bs-subtotal">
                        <td class="text-end"><?php echo htmlspecialchars($title); ?>合計</td>
                        <td class="number"><?php echo number_format($sumCurrent); ?></td>
                        <td class="number"><?php echo number_format($sumPrevious); ?></td>
                        <td class="number"></td>
                    </tr>
                <?php 
                    // 合计行逻辑
                    $showTotalRow = false;
                    $totalLabel = '';
                    $finalCurrent = 0;
                    $finalPrevious = 0;
                    if($title == '繰延資產') {
                        $showTotalRow = true;
                        $totalLabel = '資産合計';
                        $finalCurrent = $totalAssetsCurrent;
                        $finalPrevious = $totalAssetsPrevious;
                    } elseif($title == '固定負債') {
                        $showTotalRow = true;
                        $totalLabel = '負債合計';
                        $finalCurrent = $totalLiabilitiesCurrent;
                        $finalPrevious = $totalLiabilitiesPrevious;
                    } elseif($title == '資本の部') {
                        $showTotalRow = true;
                        $totalLabel = '負債·資本合計';
                        $finalCurrent = $totalLiabilityCapitalCurrent;
                        $finalPrevious = $totalLiabilityCapitalPrevious;
                    }
                    if($showTotalRow):
                ?>
                    <tr class="bs-total-row">
                        <td class="text-end"><?php echo htmlspecialchars($totalLabel); ?></td>
                        <td class="number"><?php echo number_format($finalCurrent); ?></td>
                        <td class="number"><?php echo number_format($finalPrevious); ?></td>
                        <td class="number"></td>
                    </tr>
                <?php endif; ?>
                <?php endforeach; ?>

                <!-- 貸借差額 -->
                <tr class="table-active fw-bold">
                    <td class="text-end">貸借差額</td>
                    <td class="text-end">
                        <span class="badge <?php echo $currentBadgeClass; ?>"> <?php echo $currentStatus; ?> (<?php echo number_format($currentDiff); ?>) </span>
                    </td>
                    <td class="text-end">
                        <span class="badge <?php echo $previousBadgeClass; ?>"> <?php echo $previousStatus; ?> (<?php echo number_format($previousDiff); ?>) </span>
                    </td>
                    <td></td>
                </tr>

                <!-- ********************** 分割线 1：損益計算書开始 ********************** -->
                <!-- 这一行用来做视觉分割 -->
                <tr>
                    <td colspan="4" class="divider-line"></td>
                </tr>
                
                <!-- 損益計算書 标题行 -->
                <tr>
                    <td colspan="4" class="divider-title">損益計算書</td>
                </tr>

                <!-- ********************** 第二部分：損益計算書 (P/L) ********************** -->
                <?php foreach($cashSunIns as $item): 
                    $currentAmount = $item->cashInData?->current_amount ?? 0; 
                ?>
                <tr>
                    <td class="item-name"><?php echo htmlspecialchars($item->title); ?></td>
                    <td class="number"><?php echo number_format($currentAmount); ?></td>
                    <td class="number"></td> <!-- 占位，保持列数一致 -->
                    <td class="number"></td> <!-- 占位，保持列数一致 -->
                </tr>
                <?php endforeach; ?>


                <!-- ********************** 分割线 2：前期利益処分計算書开始 ********************** -->
                <tr>
                    <td colspan="4" class="divider-line"></td>
                </tr>
                
                <tr>
                    <td colspan="4" class="divider-title">前期利益処分計算書</td>
                </tr>


                <!-- ********************** 第三部分：前期利益処分計算書 ********************** -->
                <?php foreach($cashQianIns as $item): 
                    $currentAmount = $item->cashInData?->current_amount ?? 0; 
                ?>
                <tr>
                    <td class="item-name"><?php echo htmlspecialchars($item->title); ?></td>
                    <td class="number"><?php echo number_format($currentAmount); ?></td>
                    <td class="number"></td> <!-- 占位 -->
                    <td class="number"></td> <!-- 占位 -->
                </tr>
                <?php endforeach; ?>
                
            </tbody>
        </table>
    </div>
</body>
</html>