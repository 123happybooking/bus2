<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>損益計算書</title>
    <style>
        body {
            font-family: "MS PGothic", "Yu Gothic", "Hiragino Kaku Gothic ProN", "游ゴシック", "ＭＳ ゴシック", sans-serif;
            font-size: 18px;
            margin: 0;
            color: #000;
        }

        /* 容器：模拟纸张，居中显示 */
        .container {
            width: 100%;      /* 将 850px 改为 100% */ /* 固定宽度，模拟A4纸内容的宽度 */
            margin: 0 auto;
        }

        /* --- 表头部分（在框外） --- */
        .header-group {
            text-align: center;
            margin-bottom: 2px;
        }
        .title {
            font-size: 24px;
            margin-bottom: 5px;
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

        /* --- 表格主体（有框） --- */
        .statement-box {
            border: 1px solid #000;
            padding: 10px 10px;
        }

        /* 通用行样式 */
        .row {
            overflow: hidden; /* 清除浮动 */
            margin-bottom: 4px;
            min-height: 20px;
        }

        /* 左侧文字 */
        .label {
            float: left;
            padding-left: 10px;
        }

        /* 右侧数字 */
        .value {
            float: right;
            text-align: right;
            padding-right: 10px;
            font-family: monospace; /* 等宽字体，数字对齐更好看 */
        }

        .accsum { 
            margin-left: 200px;
            font-size: 14px;

        }

        /* 缩进样式 */
        .indent-1 { 
            margin-left: 20px; 
            font-size: 14px;
            font-weight:;
        }
        .indent-2 { 
            margin-left: 40px;
            font-size: 14px; 
        }

        .value {
            font-size: 14px; 
        }

        .accsum .value {
            text-decoration: underline;    /* 文字下划线 */
            display: inline-block;         /* 让线宽跟随文字宽度 */
        }

        /* --- 线条样式 --- */
        .border-top { border-top: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }
        .double-border-bottom { border-bottom: 3px double #000; }

        /* 辅助类：加粗 */
        .bold { font-weight:; }
    </style>
</head>
<body>

    <div class="container">

        <!-- 表头区域 (在框外) -->
        <div class="header-group">
            <div class="title">損益計算書</div>
            <div class="period">自 {{ $startDate }}<br>至 {{ $endDate }}</div>
            <div class="company-unit">
                <span>{{$company->company_name}}</span>
                <span>(単位:円)</span>
            </div>
        </div>

        <!-- 表格区域 (在框内) -->
        <div class="statement-box">

            <!-- 1. 売上高 -->
            <div class="row">
                <div class="label indent-1">売上高</div>
                <div class="value"></div>
            </div>
                @foreach($groupedData as $row)
                    @if($row['category_id'] == 7)
                    <div class="row">
                        <div class="label indent-2">{{ $row['account_name'] }}</div>
                        <div class="value">{{ number_format($row['credit']) }}</div>
                    </div>
                    @endif
                @endforeach
            <!-- <div class="row accsum">
                <div class="label">売上高 合計</div>
                <div class="value">{{ number_format($totalRevenue) }}</div>
            </div> -->

            <div class="row">
                <div class="label indent-1">売上原価</div>
                <div class="value"></div>
            </div>
                @foreach($groupedData as $row)
                    @if($row['category_id'] == 8)
                    <div class="row">
                        <div class="label indent-2">{{ $row['account_name'] }}</div>
                        <div class="value">{{ number_format($row['debit']) }}</div>
                    </div>
                    @endif
                @endforeach

            <!-- <div class="row accsum">
                <div class="label indent-1">売上原価 合計</div>
                <div class="value">{{ number_format($totalCogs) }}</div>
            </div> -->

            <div class="row accsum">売上総利益</div>
                <div class="value">{{ number_format($totalRevenue-$totalCogs) }}</div>
            </div>

            <!-- 3. 販管費明细 -->
            <div class="row">
                <div class="label indent-1">販売費及び一般管理費</div>
                <div class="value"></div>
            </div>
                @foreach($groupedData as $row)
                    @if($row['category_id'] == 9)
                    <div class="row">
                        <div class="label indent-2">{{ $row['account_name'] }}</div>
                        <div class="value">{{ number_format($row['debit']) }}</div>
                    </div>
                    @endif
                @endforeach
            <!-- 4. 販管費合計 -->
            <div class="row accsum">
                <div class="label indent-1">販売費及び一般管理費 合計</div>
                <div class="value">{{ number_format($totalExpenses) }}</div>
            </div>

            <div class="row accsum">
                <div class="label indent-1">営業利益</div>
                <div class="value">{{ number_format($operatingIncome) }}</div>
            </div>

            <!-- 6. 営業外収益 -->
            <div class="row">
                <div class="label indent-1">営業外収益</div>
                <div class="value"></div>
            </div>
                @foreach($groupedData as $row)
                    @if($row['category_id'] == 10)
                    <div class="row">
                        <div class="label indent-2">{{ $row['account_name'] }}</div>
                        <div class="value">{{ number_format($row['credit']) }}</div>
                    </div>
                    @endif
                @endforeach
            <!-- <div class="row accsum">
                <div class="label indent-1">営業外収益 合計</div>
                <div class="value">{{ number_format($totalOIncome) }}</div>
            </div> -->


            <!-- 7. 営業外費用 -->
            <div class="row">
                <div class="label indent-1">営業外費用</div>
                <div class="value"></div>
            </div>
                @foreach($groupedData as $row)
                    @if($row['category_id'] == 11)
                    <div class="row">
                        <div class="label indent-2">{{ $row['account_name'] }}</div>
                        <div class="value">{{ number_format($row['debit']) }}</div>
                    </div>
                    @endif
                @endforeach
            <!-- <div class="row accsum">
                <div class="label indent-1">営業外費用 合計</div>
                <div class="value">{{ number_format($totalOExpenses) }}</div>
            </div> -->


            <!-- 8. 経常利益 -->
            <div class="row accsum">
                <div class="label indent-1">経常利益</div>
                <div class="value">{{ number_format($ordinaryIncome) }}</div>
            </div>

            <!-- 9. 特別利益 -->
            <div class="row">
                <div class="label indent-1">特別利益</div>
                <div class="value"></div>
            </div>
                @foreach($groupedData as $row)
                    @if($row['category_id'] == 12)
                    <div class="row">
                        <div class="label indent-2">{{ $row['account_name'] }}</div>
                        <div class="value">{{ number_format($row['credit']) }}</div>
                    </div>
                    @endif
                @endforeach
            <!-- <div class="row accsum">
                <div class="label indent-1">特別利益 合計</div>
                <div class="value">{{ number_format($totalSOIncome) }}</div>
            </div> -->


            <!-- 10. 特別損失 -->
            <div class="row">
                <div class="label indent-1">特別損失</div>
                <div class="value"></div>
            </div>
                @foreach($groupedData as $row)
                    @if($row['category_id'] == 13)
                    <div class="row">
                        <div class="label indent-2">{{ $row['account_name'] }}</div>
                        <div class="value">{{ number_format($row['debit']) }}</div>
                    </div>
                    @endif
                @endforeach
            <!-- <div class="row accsum">
                <div class="label indent-1">特別損失 合計</div>
                <div class="value">{{ number_format($totalSOExpenses) }}</div>
            </div> -->

            <!-- 11. 税引前当期純利益 -->
            <div class="row accsum">
                <div class="label indent-1">税引前当期純利益</div>
                <div class="value">{{ number_format($profitBeforeTax) }}</div>
            </div>

            <!-- 12. 法人税等 -->
            <!-- <div class="row">
                <div class="label indent-1">税等</div>
                <div class="value"></div>
            </div>
                @foreach($groupedData as $row)
                    @if($row['category_id'] == 14)
                    <div class="row">
                        <div class="label indent-2">{{ $row['account_name'] }}</div>
                        <div class="value">{{ number_format($row['debit']) }}</div>
                    </div>
                    @endif
                @endforeach -->
            <div class="row accsum">
                <div class="label indent-1">法人税·住民税及び事業税</div>
                <div class="value">{{ number_format($totalTaxes) }}</div>
            </div>

            <!-- 13. 当期純利益 (最终结果) -->
            <div class="row accsum">
                <div class="label indent-1">当期純利益</div>
                <div class="value">{{ number_format($netIncome) }}</div>
            </div>

        </div>
    </div>
</body>
</html>