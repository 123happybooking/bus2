<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>貸借対照表</title>
    <style>
        body {
            font-family: "Meiryo", "MS Gothic", sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            padding: 10px;
        }
        .container {
            background: white;
            padding: 10px;
            width: 100%; /* 调整宽度以适应A4视觉效果 */
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* --- 头部样式 (严格按照PDF) --- */
        .title-section {
            margin-bottom: 15px;
        }
        /* 主标题 */
        .main-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 2px;
        }
        /* 日期部分 (居中) */
        .date-section {
            text-align: center;
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        /* 公司名(左) 和 单位(右) 的容器 */
        .header-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 14px;
        }
        .company-name {
            font-family: "Courier New", monospace; /* 英文字体调整 */
            font-weight: normal;
        }
        .unit {
            font-weight: bold;
        }

        /* --- 表格样式 --- */
        .bs-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        /* 左右分栏的边框 */
        .col-left, .col-right {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .col-left { border-right: 1px solid #000; }

        /* 内部表格 */
        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }
        /* 表头行：科目 | 金额 */
        .table-header td {
            border-bottom: 1px solid #000;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .table-header .col-subject { text-align: left; }
        .table-header .col-amount { text-align: right; }

        /* 内容行 */
        .item-row td {
            padding: 5px 10px;
            font-size: 14px;
        }
        .subject { text-align: left; }
        .amount { 
            text-align: right; 
            font-family: "MS Gothic", monospace; /* 数字对齐 */
        }

        /* 部门小标题 (資産の部 / 負債の部) */
        .section-header td {
            padding: 10px 10px 5px 10px;
            font-weight: bold;
        }
        td.section-header {
            font-weight: bold;
            padding-top: 10px;
            border-bottom: 1px solid #000; /* 添加模块分割线 */
        }
        .section-title-left { text-align: center; }
        .section-title-right { text-align: center; }

        /* 负数颜色 */
        .negative { color: red; }
        
        /* 合计线 */
        .total-line {
            border-top: 1px solid  #000;
        }
        @media print {
        body {
            background-color: #fff; /* 打印时去除灰色背景 */
        }

        .container {
            max-width: 100% !important; /* 强制解除 800px 限制 */
            width: 100% !important;     /* 强制占满宽度 */
            margin: 0 !important;       /* 强制去除外边距 */
            padding: 0 !important;      /* 可选：去除内边距 */
            box-shadow: none !important;/* 打印时去除阴影，更清爽 */
            border: none !important;    /* 打印时去除容器边框（如果不需要的话） */
        }

        /* 优化表格在A4纸上的显示 */
        table {
            width: 100% !important;
            page-break-inside: avoid; /* 尽量避免表格在中间被切断 */
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
            <div>自 令和7年02月01日</div>
            <div>至 令和8年01月31日</div>
        </div>

        <div class="header-info">
            <div class="company-name">Travel Investment 株式会社</div>
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
                        <td colspan="2" class="section-title-left">（資産の部）</td>
                    </tr>

                    <!-- 流动资产 -->
                    <tr><td colspan="2" style="padding-left:15px; font-weight:bold;">【流動資産】</td></tr>
                    <tr class="item-row">
                        <td class="subject">　現　金</td>
                        <td class="amount negative">▲51,675</td>
                    </tr>
                    <tr class="item-row">
                        <td class="subject">　普通預金</td>
                        <td class="amount">645,040</td>
                    </tr>
                                        <tr class="item-row">
                        <td class="subject">　普通預金</td>
                        <td class="amount">645,040</td>
                    </tr>
                    <tr class="item-row"><td class="subject">　売掛金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　立替金</td><td class="amount"></td></tr>
                    <!-- 其他空行 -->
                    <tr class="item-row"><td class="subject">　仮払金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　前受金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　未収金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　受取手形</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　前払金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　未収入金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　その他流動資産</td><td class="amount"></td></tr>

                    <tr class="total-line"><td class="subject"></td><td class="amount"></td></tr>
                    <!-- 固定资产 -->
                    <tr><td colspan="2" style="padding-left:15px; padding-top:10px; font-weight:bold;">【固定資産】</td></tr>
                    <tr class="item-row"><td class="subject">　備　品</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　敷　金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　差入保証</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　長期前払</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　その他固定資産</td><td class="amount"></td></tr>

                    <tr class="total-line"><td class="subject"></td><td class="amount"></td></tr>

                    <!-- 递延资产 -->
                    <tr><td colspan="2" style="padding-left:15px; padding-top:10px; font-weight:bold;">【繰延資産】</td></tr>
                    <tr class="item-row"><td class="subject">　繰延資産</td><td class="amount"></td></tr>

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
                        <td colspan="2" class="section-title-right">（負債の部）</td>
                    </tr>

                    <!-- 流动负债 -->
                    <tr><td colspan="2" style="padding-left:15px; font-weight:bold;">【流動負債】</td></tr>
                    <tr class="item-row"><td class="subject">　買掛金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　預り金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　預り源泉所得税</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　未払金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　前受金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　未払費用</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　仮受消費税</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　短期借入金</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　未払法人税等</td><td class="amount"></td></tr>
                    <tr class="item-row"><td class="subject">　その他流動負債</td><td class="amount"></td></tr>

                    <tr class="total-line"><td class="subject"></td><td class="amount"></td></tr>
                    <!-- 固定负债 -->
                    <tr><td colspan="2" style="padding-left:15px; padding-top:10px; font-weight:bold;">【固定負債】</td></tr>
                    <tr class="item-row"><td class="subject">　長期借入金</td><td class="amount"></td></tr>

                    <!-- 负债合计 -->
                    <tr class="item-row total-line">
                        <td class="subject" style="text-align:right; padding-right:10px;">負債合計</td>
                        <td class="amount"></td>
                    </tr>
                    <tr class="total-line"><td class="subject"></td><td class="amount"></td></tr>
                    <!-- 纯资产 -->
                    <tr class="section-header">
                        <td colspan="2" class="section-title-right">（負債の部）</td>
                    </tr>

                    <tr><td colspan="2" style="padding-left:15px; font-weight:bold;">【純資産】</td></tr>
                    <tr class="item-row">
                        <td class="subject">　資本金</td>
                        <td class="amount">593,365</td>
                    </tr>
                    <tr class="item-row">
                        <td class="subject">　繰越利益剰余金</td>
                        <td class="amount">593,365</td> <!-- 注意：PDF中此处似乎重复显示了总额，此处按PDF填写 -->
                    </tr>

                    <!-- 纯资产合计 -->
                    <tr class="item-row total-line">
                        <td class="subject" style="text-align:right; padding-right:10px;">純資産合計</td>
                        <td class="amount">593,365</td>
                    </tr>

                </table>
            </td>
        </tr>
        <tr  class="item-row total-line">
            <!-- 左侧：资产 -->
            <td class="col-left" style="text-align: right;">
                <span class="subject" style="float: left;padding-left:15px;">資産合計</span>
                <span>593,365</span>
            </td>

            <!-- 右侧：负债/纯资产 -->
            <td class="col-right" style="text-align: right;">
                <span class="subject" style="float: left;padding-left:15px;">負債及び純資産合計</span>
                <span>593,365</span>
            </td>
        </tr>
    </table>

</div>

</body>
</html>