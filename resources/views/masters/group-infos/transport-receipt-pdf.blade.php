<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>運送引受書</title>
<style>
    @page {
        margin: 10mm 10mm 10mm 10mm;
    }
    body {
        font-family: 'msyh', 'genshin', sans-serif;
        font-size: 10pt;
        color: #000;
        margin: 0;
        padding: 0;
    }
    .title-block {
        text-align: center;
        margin: 0 0 14px 0;
    }
    .title {
        display: inline-block;
        font-size: 18pt;
        font-weight: bold;
        letter-spacing: 14px;
        line-height: 1;
    }
    .bessai {
        text-align: right;
        font-size: 9pt;
        line-height: 1;
        margin-bottom: 6px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    td, th {
        border: 1px solid #000;
        padding: 6px 8px;
        font-size: 10pt;
        line-height: 1.8;
        vertical-align: top;
    }
    .lbl {
        text-align: center;
        vertical-align: middle;
        font-size: 9.5pt;
        line-height: 1.5;
        background: #ddd;
    }
    .val {
        text-align: left;
    }

    .underline {
        display: inline-block;
        border-bottom: 1px solid #000;
        min-width: 260px;
        vertical-align: bottom;
        padding: 4px;
    }
    .underline-short {
        display: inline-block;
        border-bottom: 1px solid #000;
        min-width: 120px;
        vertical-align: bottom;
        padding: 4px;
    }
    .indent {
        padding-left: 28px;
    }

    .method-grid {
        width: 100%;
        border-collapse: collapse;
    }
    .method-grid td {
        border: 0;
        padding: 4px 6px;
        vertical-align: top;
        font-size: 10pt;
        line-height: 1.8;
    }
    .method-cell {
        display: inline-block;
        white-space: nowrap;
        margin-right: 20px;
    }
    .method-box {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 1px solid #000;
        text-align: center;
        line-height: 14px;
        font-size: 12pt;
        font-weight: bold;
        vertical-align: -2px;
        margin-right: 6px;
    }
    .method-paren {
        display: inline-block;
        border-bottom: 1px solid #000;
        min-width: 100px;
        padding-left: 4px;
        padding-right: 4px;
    }

    .footer-note {
        margin: 14px 0 8px 0;
        font-size: 10pt;
    }
    .footer-date-line {
        width: 50%;
        margin: 6px 0 10px 0;
        text-align: right;
        font-size: 10pt;
        padding-right: 40px;
    }
    .footer-date-line .u {
        display: inline-block;
        border-bottom: 1px solid #000;
        min-width: 90px;
        text-align: center;
        padding-left: 4px;
        padding-right: 4px;
    }

    .recipient-table {
        width: 60%;
        margin: 0 0 0 auto;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .recipient-table td {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 10pt;
        line-height: 1.6;
    }
    .recipient-table .lbl2 {
        width: 40%;
        text-align: center;
    }
</style>
</head>
<body>

    <div class="bessai">（別記１）</div>

    <div class="title-block">
        <span class="title">運送引受書</span>
    </div>

    <table>
        <colgroup>
            <col style="width: 22%;">
            <col style="width: 78%;">
        </colgroup>

        <tr>
            <td class="lbl" style="height: 100px;">運送の申込者</td>
            <td class="val">
                <div>氏名又は名称：<span class="underline">{{ $agencyName }}</span></div>
                <div style="margin-top: 4px;">電話番号　　：<span class="underline">{{ $agencyPhone }}</span></div>
                <div style="margin-top: 4px;">その他連絡先：<span class="underline">{{ $agencyOtherContact }}</span></div>
            </td>
        </tr>

        <tr>
            <td class="lbl" style="height: 110px;">申込方法</td>
            <td class="val">
                @php
                    $itemsPerRow = 3;
                    $methodList = $applicationMethods->values();
                    $methodRows = $methodList->chunk($itemsPerRow);
                    $colWidth = 100 / ($itemsPerRow * 2); // 每列 = 方块格 + 文字格
                @endphp
                
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <colgroup>
                        @for ($c = 0; $c < $itemsPerRow; $c++)
                            <col style="width: {{ $colWidth * 0.4 }}%;">
                            <col style="width: {{ $colWidth * 1.6 }}%;">
                        @endfor
                    </colgroup>
                
                    @foreach ($methodRows as $row)
                        <tr>
                            @foreach ($row as $method)
                                <td style="border: 0; padding: 4px 2px; vertical-align: middle; text-align: center;">
                                    <table style="width: 16px; height: 16px; border-collapse: collapse; margin: 0 auto;">
                                        <tr>
                                            <td style="width: 16px; height: 16px; border: 1px solid #000; padding: 0; text-align: center; vertical-align: middle; font-size: 10pt; line-height: 1;">{{ $method->id == $selectedMethodId ? '√' : '' }}</td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="border: 0; padding: 4px 2px; vertical-align: middle; font-size: 10pt;">{{ $method->method_name }}</td>
                            @endforeach
                
                            @for ($i = $row->count(); $i < $itemsPerRow; $i++)
                                <td style="border: 0; padding: 4px 2px;"></td>
                                <td style="border: 0; padding: 4px 2px;"></td>
                            @endfor
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>

        <tr>
            <td class="lbl" style="height: 120px;">運行の開始/終了<br>の<br>地点及び日時</td>
            <td class="val">
                【開始】地　点：<span class="underline">{{ $departureLocation }}</span><br>
                　　　　日　時：<span class="underline">{{ $departureDateTime }}</span><br><br>
                【終了】地　点：<span class="underline">{{ $arrivalLocation }}</span><br>
                　　　　日　時：<span class="underline">{{ $arrivalDateTime }}</span>
            </td>
        </tr>

        <tr>
            <td class="lbl" style="height: 120px;">乗車/降車の<br>地点及び日時</td>
            <td class="val">
                【乗車】地　点：<span class="underline">{{ $boardingLocation }}</span><br>
                　　　　日　時：<span class="underline">{{ $boardingDateTime }}</span><br><br>
                【降車】地　点：<span class="underline">{{ $alightingLocation }}</span><br>
                　　　　日　時：<span class="underline">{{ $alightingDateTime }}</span>
            </td>
        </tr>

        <tr>
            <td class="lbl" style="height: 110px;">運賃・料金</td>
            <td class="val">
                <div>
                    運　賃：<span class="underline-short">{{ $fareBase !== '' ? number_format($fareBase) : '' }}</span>円
                    　料　金：<span class="underline-short">{{ $fareCharge !== '' ? number_format($fareCharge) : '' }}</span>円
                </div>
                <div style="margin-top: 8px;">
                    料金の内訳：<span class="underline">{{ $fareBreakdown }}</span>
                </div>
                <div style="margin-top: 8px;">
                    合　計：<span class="underline-short">{{ $fareTotal !== '' ? number_format($fareTotal) : '' }}</span>円　（消費税等を含む。）
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">上記のとおり運送を引き受けます。</div>

    <div class="footer-date-line">
        <span class="u">{{ $today }}</span>
    </div>

    <table class="recipient-table">
        <tr>
            <td colspan="2" style="background:#aaa;">運送引受者：{{ $companyName }}</td>
        </tr>
        <tr>
            <td class="lbl lbl2" style="width: 40%;">住　　所</td>
            <td style="width: 60%;">{{ $companyAddress }}</td>
        </tr>
        <tr>
            <td class="lbl lbl2">電 話 番 号</td>
            <td>{{ $companyPhone }}</td>
        </tr>
        <tr>
            <td class="lbl lbl2">その他連絡先</td>
            <td>{{ $companyEmergency }}</td>
        </tr>
        <tr>
            <td class="lbl lbl2">営 業 区 域</td>
            <td>{{ $companyWorkArea }}</td>
        </tr>
    </table>

</body>
</html>