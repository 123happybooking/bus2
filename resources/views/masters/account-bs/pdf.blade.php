<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>貸借対照表</title>
<style>
    /* --- 基础样式 --- */
    body {
        font-family: "Meiryo", "MS Gothic", sans-serif;
        background-color: #f0f0f0;
        display: flex;
        justify-content: center;
    }
    .container {
        background: white;
        width: 100%;
    }

    /* --- 头部样式 --- */
    .title-section { margin-bottom: 2px; }
    .main-title {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin: 0;
        letter-spacing: 2px;
    }
    .date-section {
        text-align: center;
        font-size: 14px;
        margin-top: 5px;
        margin-bottom: 15px;
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
    .bs-table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
    .col-left { width: 50%; vertical-align: top; padding: 0; border-right: 1px solid #000; }
    .col-right { width: 50%;vertical-align: top; padding: 0; }
    .inner-table { width: 100%; border-collapse: collapse; }

    /* --- 表头样式 --- */
    .table-header td {
        border-bottom: 1px solid #000;
        padding: 5px 10px;
        font-weight: bold;
        font-size: 14px;
    }
    .col-subject { text-align: left; }
    .col-amount { text-align: right; }

    /* --- 内容行样式 --- */
    .item-row td {
        padding: 5px 10px;
        font-size: 14px;
    }
    .subject { text-align: left; }
    .amount {
        text-align: right;
        padding: 5px 10px;
        font-size: 14px;
    }

    /* --- 标题与分类样式 --- */
    .section-header td {
        padding: 10px 10px 5px 10px;
        font-weight: bold;
        font-size: 14px;
        /* border-bottom: 1px solid #000; */
    }
    .section-title-left { 
        text-align: center;
    }
    .section-title-right { 
        text-align: center;
    }

    /* --- 自定义提取的类 (替代行内样式) --- */
    /* 用于大分类标题（如：流動資産、固定資産） */
    .category-title {
        padding-left: 15px;
        font-weight: bold;
        font-size: 14px;
    }
    /* 用于大分类对应的金额 */
    .category-amount {
        text-align: right;
        padding: 5px 10px;
        font-weight: bold;
        font-size: 14px;
    }
    /* 用于合计行 */
    .total-line { border-top: 1px solid #000; }
    /* 用于表格底部的最终合计 */
    .final-total {
        text-align: right;
        font-weight: bold;
    }
    .final-total .subject {
        float: left;
        padding-left: 15px;
    }

    /* --- 负数颜色 --- */
    .negative { color: red; }

    /* --- 打印样式 --- */
    @media print {
        body { background-color: #fff; }
        .container {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        table {
            width: 100% !important;
            page-break-inside: avoid;
        }
    }
</style>
</head>
<body>
<div class="container">
    <!-- ================= 头部区域 ================= -->
    <div class="title-section">
        <h1 class="main-title">貸借対照表</h1>
        <div class="date-section">
            <div>自 {{ $startDate }}</div>
            <div>至 {{ $endDate }}</div>
        </div>
        <div class="header-info">
            <div class="company-name">{{ $company->company_name }}</div>
            <div class="unit">(単位:円)</div>
        </div>
    </div>

    <!-- ================= 表格区域 ================= -->
    <table class="bs-table">
        <tr>
            <!-- 左侧：资产 -->
            <td class="col-left">
                <table class="inner-table">
                    <!-- 表头 -->
                    <tr class="table-header">
                        <td class="col-subject">科　目</td>
                        <td class="col-amount">金　額</td>
                    </tr>
                    <!-- 资产的小标题 -->
                    <tr class="section-header">
                        <td colspan="2" class="section-title-left">(資産の部)</td>
                    </tr>

                    @foreach ($assetOrder as $catId => $categoryName)
                        @if (isset($assets[$categoryName]))
                            <!-- 大分类标题 -->
                            <tr>
                                <td class="category-title">{{ $categoryName }}</td>
                                <td class="category-amount">{{ number_format($assets[$categoryName]['total']) }}</td>
                            </tr>
                            <!-- 明细科目 -->
                            @foreach ($assets[$categoryName]['accounts'] as $account)
                                <tr class="item-row">
                                    <td class="subject">　{{ $account['name'] }}</td>
                                    <td class="amount">{{ number_format($account['amount']) }}</td>
                                </tr>
                            @endforeach
                            @if (!$loop->last)
                                <tr class="total-line"><td class="subject"></td><td class="amount"></td></tr>
                            @endif
                        @endif
                    @endforeach
                </table>
            </td>

            <!-- 右侧：负债/纯资产 -->
            <td class="col-right">
                <table class="inner-table">
                    <!-- 表头 -->
                    <tr class="table-header">
                        <td class="col-subject">科　目</td>
                        <td class="col-amount">金　額</td>
                    </tr>
                    <!-- 负债的小标题 -->
                    <tr class="section-header">
                        <td colspan="2" class="section-title-right">(負債の部)</td>
                    </tr>

                    @foreach ($liabilityOrder as $catId => $categoryName)
                        @if($categoryName == '純資産')
                            <tr class="section-header">
                                <td colspan="2" class="section-title-right">(純資産の部)</td>
                            </tr>
                        @endif

                        @if (isset($liabilities[$categoryName]))
                            <!-- 大分类标题 -->
                            <tr>
                                <td class="category-title">{{ $categoryName }}</td>
                                <td class="category-amount">{{ number_format($liabilities[$categoryName]['total']) }}</td>
                            </tr>
                            <!-- 明细科目 -->
                            @foreach ($liabilities[$categoryName]['accounts'] as $account)
                                <tr class="item-row">
                                    <td class="subject">　{{ $account['name'] }}</td>
                                    <td class="amount">{{ number_format($account['amount']) }}</td>
                                </tr>
                            @endforeach

                            <!-- 纯资产部分的特殊处理 -->
                            @if($categoryName == '純資産')
                                <tr class="item-row">
                                    <td class="subject">　繰越利益剰余金</td>
                                    <td class="amount">{{ number_format($netIncome) }}</td>
                                </tr>
                                <tr class="item-row total-line" style="font-weight: bold;">
                                    <td class="subject" style="text-align:right; padding-right:10px;">{{ $categoryName }} 合計</td>
                                    <td class="amount">{{ number_format($liabilities[$categoryName]['total'] + $netIncome) }}</td>
                                </tr>
                            @elseif($categoryName == '固定負債')
                                <tr class="item-row total-line" style="font-weight: bold;">
                                    <td class="subject" style="text-align:right; padding-right:10px;">负债 合計</td>
                                    <td class="amount">{{ number_format($liabilities[$categoryName]['total']+$liabilities['流動負債']['total'] ) }}</td>
                                </tr>
                            @endif

                            @if (!$loop->last)
                                <tr class="total-line"><td class="subject"></td><td class="amount"></td></tr>
                            @endif
                        @endif
                    @endforeach
                </table>
            </td>
        </tr>

        <!-- 底部合计行 (由于样式特殊，保留部分行内样式或可进一步拆分为 .final-left/.final-right) -->
        <tr class="item-row total-line">
            <!-- 左侧：资产 -->
            <td class="col-left final-total">
                <span class="subject">資産合計</span>
                <span class="amount">{{ number_format($totalAssets) }}</span>
            </td>
            <!-- 右侧：负债/纯资产 -->
            <td class="col-right final-total">
                <span class="subject">負債及び純資産合計</span>
                <span class="amount">{{ number_format($totalLiabilities) }}</span>
            </td>
        </tr>
    </table>
</div>
</body>
</html>