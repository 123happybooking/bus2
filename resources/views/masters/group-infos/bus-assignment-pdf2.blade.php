<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>運行指示書</title>
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
    .bessai {
        text-align: right;
        font-size: 9pt;
        line-height: 1;
        margin-bottom: 6px;
    }
    .title {
        display: inline-block;
        font-size: 18pt;
        font-weight: bold;
        letter-spacing: 14px;
        line-height: 1;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom:25px;
    }
    td, th {
        vertical-align: top;
        border: 1px solid #000;
        padding: 5px 6px;
        font-size: 10pt;
        line-height: 1.8;
    }
    .lbl {
        text-align: center;
        width: 20%;
        font-size: 9.5pt;
        background: #ddd;
    }
    .val {
        text-align: left;
        width: 80%;
    }
    .lbl-1 {
        width: 15%;
    }
    .val-1 {
        width: 35%;
    }

    .blank {
        display: inline-block;
        border-bottom: 1px solid #000;
        min-width: 300px;
        vertical-align: bottom;
    }
    .blank-short {
        display: inline-block;
        border-bottom: 1px solid #000;
        min-width: 160px;
        vertical-align: bottom;
    }

    .indent {
        padding-left: 22px;
    }
    .section-gap {
        height: 6px;
    }

    .footer-heading {
        font-weight: bold;
        font-size: 11pt;
        margin: 0 0 10px 0;
    }
    
    .underline {
        text-decoration: underline;
    }
</style>
</head>
<body>

    <div class="bessai">（別記２）</div>

    <div class="title-block">
        <span class="title">運行指示書</span>
    </div>

    <table>
        <tr>
            <td class="lbl lbl-1">事業者名</td>
            <td class="val val-1">{{ $companyName }}</td>
            <td class="lbl lbl-1">営業所名</td>
            <td class="val val-1">{{ $branchName }}</td>
        </tr>
        <tr>
            <td class="lbl lbl-1">運行管理者</td>
            <td class="val val-1">{{ $workManager }}</td>
            <td class="lbl lbl-1">作成年月日</td>
            <td class="val val-1">{{ $createdDate }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="lbl" style="vertical-align: middle;">運行の開始/終了の<br>地点及び日時</td>
            <td class="val">
                【開始】地　点：<span class="underline">{{ $departureLocation }}</span><br>
                　　　　日　時：<span class="underline">{{ $departureDateTime }}</span><br><br>
                【終了】地　点：<span class="underline">{{ $arrivalLocation }}</span><br>
                　　　　日　時：<span class="underline">{{ $arrivalDateTime }}</span>
            </td>
        </tr>

        <tr>
            <td class="lbl" style="vertical-align: middle;">運転者の氏名</td>
            <td class="val">{{ $driverName }}</td>
        </tr>

        <tr>
            <td class="lbl" style="vertical-align: middle;">車両の自動車登録番号<br>（ナンバープレート）</td>
            <td class="val">{{ $vehicleNumber }}</td>
        </tr>

        <tr>
            <td class="lbl" style="vertical-align: middle;">運送契約の相手方</td>
            <td class="val">
                <div>氏名又は名称：{{ $agencyName }}</div>
                <div style="margin-top: 4px;">連　絡　先　：{{ $agencyContact }}</div>
            </td>
        </tr>

        <tr>
            <td class="lbl" rowspan="2" style="vertical-align: middle;">
                その他運行の安全を<br>確保するために<br>必要な事項
            </td>
            <td class="val">
                乗車/降車の地点及び日時<br>
                【乗車】地　点：<span class="underline">{{ $boardingLocation }}</span><br>
                　　　　日　時：<span class="underline">{{ $boardingDateTime }}</span><br><br>
                【降車】地　点：<span class="underline">{{ $alightingLocation }}</span><br>
                　　　　日　時：<span class="underline">{{ $alightingDateTime }}</span>
            </td>
        </tr>
        
        <tr>
            <td class="val">
                <div>配車依頼方法、立寄り地、待機場所、注意箇所、その他指示事項</div>
                <div style="margin-top: 6px;">{{ $operationRemarks }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-heading">【指示・確認欄】</div>

    <table>
        <tr>
            <td class="lbl lbl-1">運行管理者<br>指示（印）</td>
            <td class="val val-1"></td>
            <td class="lbl lbl-1">運転者<br>確認（印）</td>
            <td class="val val-1"></td>
        </tr>
        <tr>
            <td class="lbl lbl-1">指示年月日</td>
            <td class="val val-1">{{ $instructionDate }}</td>
            <td class="lbl lbl-1">確認年月日</td>
            <td class="val val-1"></td>
        </tr>
    </table>

</body>
</html>