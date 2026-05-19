<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>合計残高試算表</title>
<style>
    /* --- 基础设置 --- */
    body {
        font-family: "Meiryo", "MS Gothic", sans-serif;
        background-color: #f0f0f0;
        display: flex;
        justify-content: center;
        padding: 20px;
        margin: 0;
    }
    .container {
        background: white;
        width: 210mm; /* A4 宽度 */
        padding: 20px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        box-sizing: border-box;
    }

    /* --- 头部样式 --- */
    .main-title {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin: 0 0 10px 0;
        letter-spacing: 2px;
        text-decoration: underline;
    }
    .date-section {
        text-align: center;
        font-size: 14px;
        margin-bottom: 10px; /* 增加日期下方的间距 */
    }
    .header-info {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        font-size: 14px;
    }

    /* --- 表格样式重构 --- */
    .bs-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0; /* 【关键修复1】强制去除表格上方的默认间距，紧贴标题 */
        font-size: 13px;
        page-break-inside: auto; /* 【关键修复2】允许表格内部跨页，不要整个表格挤到下一页 */
    }

    /* 单元格通用边框 */
    .bs-table th, 
    .bs-table td {
        border: 1px solid #000;
        padding: 6px 4px;       /* 稍微调小内边距，防止第一行太高被挤下去 */
        vertical-align: middle;
    }

    /* 列宽控制 */
    .col-bal-debit   { width: 15%; }
    .col-tot-debit   { width: 15%; }
    .col-subject     { width: 25%; }
    .col-tot-credit  { width: 15%; }
    .col-bal-credit  { width: 15%; }

    /* 表头样式 */
    .bs-table th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: center;
    }

    /* 内容行样式 */
    .bs-table td {
        background-color: #fff;
    }

    /* 合计行样式 */
    .total-row td {
        font-weight: bold;
        background-color: #fafafa;
    }

    /* 文字对齐辅助类 */
    .text-left   { text-align: left; padding-left: 10px; }
    .text-center { text-align: center; }
    .text-right  { text-align: right; padding-right: 10px; }
    
    /* 暂无数据样式 */
    .text-muted { color: #666; }
    .py-4 { padding: 20px 0; }
    .fs-2 { font-size: 2rem; }
    .d-block { display: block; }
    .mb-2 { margin-bottom: 0.5rem; }

    /* --- 打印样式 (核心修复区域) --- */
    @media print {
        body { 
            background-color: #fff; 
            padding: 0; 
            display: block; 
            -webkit-print-color-adjust: exact; /* 强制打印背景色 */
        }
        .container {
            width: 100%;
            box-shadow: none;
            padding: 0; /* 【关键修复3】打印时移除容器内边距，最大化利用 A4 空间 */
            margin: 0;
        }
        /* 确保表格行不会被切断 */
        .bs-table tr {
            page-break-inside: avoid;
        }
    }
</style>
</head>
<body>
<div class="container">
    <!-- ================= 头部区域 ================= -->
    <div class="title-section">
        <h1 class="main-title">合計残高試算表</h1>
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
        <thead>
            <tr>
                <th class="col-bal-debit">借方残高</th>
                <th class="col-tot-debit">借方合計</th>
                <th class="col-subject">勘定科目</th>
                <th class="col-tot-credit">貸方合計</th>
                <th class="col-bal-credit">貸方残高</th>
            </tr>
        </thead>

        <tbody>
            @php
                $grandTotalJie = 0;
                $grandTotalDai = 0;
                $grandTotalBalanceJie = 0;
                $grandTotalBalanceDai = 0;
            @endphp

            @forelse($datas as $row)
                @php
                    // 计算逻辑保持不变
                    $balance = $row->total_jie - $row->total_dai;
                    
                    $balanceJie = $balance > 0 ? $balance : 0;
                    $balanceDai = $balance < 0 ? abs($balance) : 0;

                    // 累计求和
                    $grandTotalJie += $row->total_jie;
                    $grandTotalDai += $row->total_dai;
                    $grandTotalBalanceJie += $balanceJie;
                    $grandTotalBalanceDai += $balanceDai;
                @endphp
                <tr>
                    <!-- 借方残高 -->
                    <td class="text-right">
                        {{ $balanceJie > 0 ? number_format($balanceJie, 0) : '0' }}
                    </td>
                    <!-- 借方合計 -->
                    <td class="text-right">
                        {{ number_format($row->total_jie, 0) > 0 ? number_format($row->total_jie, 0) : '' }}
                    </td>
                    <!-- 勘定科目 -->
                    <td class="text-center">{{ $row->name }}</td>
                    <!-- 貸方合計 -->
                    <td class="text-right">
                        {{ number_format($row->total_dai, 0)>0 ? number_format($row->total_dai, 0) : '' }}
                    </td>
                    <!-- 貸方残高 -->
                    <td class="text-right">
                        {{ $balanceDai > 0 ? number_format($balanceDai, 0) : '0' }}
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        暂无数据
                    </td>
                </tr>
            @endforelse
            
            <!-- 合计行 -->
            <tr class="total-row">
                <td class="text-right">{{ number_format($grandTotalBalanceJie, 0) }}</td>
                <td class="text-right">{{ number_format($grandTotalJie, 0) }}</td>
                <td class="text-center">合計</td>
                <td class="text-right">{{ number_format($grandTotalDai, 0) }}</td>
                <td class="text-right">{{ number_format($grandTotalBalanceDai, 0) }}</td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>