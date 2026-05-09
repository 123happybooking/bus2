<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>運行指示書</title>
<style>
table { width: 100%; border: 0; border-collapse: collapse; }
td { text-align: center; vertical-align: middle; background-color: #fff; padding: 3px; border: 1px solid #000; font-size: 11pt; line-height: 150%; height: 20pt; word-break: break-all; word-wrap: break-word;}
.remark { height: 60pt;}
.bg-gray { background-color: #F2F2F2; }

.header { width: 100%; margin: 0 0 4pt 0;}
.header .c1 { float: left; width: 50%; font-size: 24pt; display: block; }
.header .c2 { text-align: right; float: left; width: 20%; display: block;}
.header .c3 { text-align: right; float: left; width: 30%; display: block; }

.footer { width: 100%; font-size: 9pt; margin: 4pt 0;}
.footer .c1 { float: left; width: 50%; display: block; }
.footer .c2 { text-align: right; float: left; width: 50%; display: block;}
</style>
</head>
<body>

<div class="header">
    <div class="c1">運行指示書</div>
    <div class="c2">
      @if(isset($companyLogo) && $companyLogo)
          <img src="{{ $companyLogo }}" height="30pt" max-width="80%">
      @endif
    </div>
    <div class="c3">
        {{ $companyInfo['name'] ?? '会社名' }}<br>
        {{ $companyInfo['tel'] ?? '電話' }}
    </div>
</div>

<table>
 <tr>
  <td style="width:12%;" class="bg-gray">ID</td>
  <td style="width:40%;" colspan="4">{{ $busAssignment->group_info_id ?? '' }}-{{ $busAssignment->id ?? '' }}</td>
  <td style="width:12%;" class="bg-gray">業務分類</td>
  <td style="width:12%;" colspan="2">{{ $businessCategoryName ?? '' }}</td>
  <td style="width:12%;" class="bg-gray">担当</td>
  <td style="width:12%;">{{ $manager ?? '' }}</td>
 </tr>

 <tr>
  <td class="bg-gray">運行日</td>
  <td colspan="4">{{ $formattedDate ?? '' }}</td>
  <td class="bg-gray">行程</td>
  <td colspan="2">{{ $route ?? '' }}</td>
  <td class="bg-gray">営業所</td>
  <td>{{ $office ?? '' }}</td>
 </tr>

 <tr>
  <td class="bg-gray">車両名</td>
  <td colspan="4">{{ $vehicleName ?? '' }}{{ $vehicleColor ? '/' . $vehicleColor : '' }}</td>
  <td class="bg-gray">運転手</td>
  <td colspan="4">{{ $driver->name ?? '' }}{{ ($driver->phone ?? $driver->mobile_phone ?? '') ? '/' . ($driver->phone ?? $driver->mobile_phone) : '' }}</td>
 </tr>

 <tr>
  <td class="bg-gray">車両No.</td>
  <td colspan="4">{{ $vehicleNumber ?? '' }}</td>
  <td class="bg-gray">添乗員</td>
  <td colspan="4">{{ $guide->name ?? '' }}</td>
 </tr>

 <tr>
  <td class="bg-gray">団体名</td>
  <td colspan="2">{{ $busAssignment->step_car ?? '' }}</td>
  <td style="width:10%;" class="bg-gray">号車</td>
  <td style="width:10%;">{{ $busNumber ?? '' }}</td>
  <td rowspan="2" class="bg-gray">代表者</td>
  <td colspan="4" rowspan="2">
    {{ $representativeName ?? '' }}{{ $representativeContact ? '/' . $representativeContact : '' }}
    @if(($groupInfo->agt_tour_id ?? '') || ($groupInfo->agency_country ?? ''))
        <br>{{ $groupInfo->agt_tour_id ?? '' }} {{ $groupInfo->agency_country ?? '' }}
    @endif
  </td>
 </tr>
 <tr>
  <td class="bg-gray">人数</td>
  <td colspan="2">{{ $personnelCount ?? '' }}</td>
  <td class="bg-gray">荷物</td>
  <td>{{ $luggage ?? '' }}</td>
 </tr>

 @foreach($itineraryRows as $row)
 <tr>
  <td rowspan="2" class="bg-gray">{{ $row['day'] }}</td>
  <td style="width:10%;">{{ $row['start_time'] }}</td>
  <td colspan="3">{{ $row['start_location'] }}</td>
  <td>--></td>
  <td style="width:10%;">{{ $row['end_time'] }}</td>
  <td colspan="3">{{ $row['end_location'] }}</td>
 </tr>
 <tr>
  <td colspan="9">{{ $row['description'] }}</td>
 </tr>
 @endforeach

 <tr>
  <td colspan="8" class="remark">変更/理由</td>
  <td class="remark">時刻</td>
  <td class="remark">指示</td>
 </tr>

 <tr>
  <td colspan="10" class="remark">注意：{{ $optionsNames ?? '--' }}</td>
 </tr>

 <tr>
  <td colspan="10" class="remark">備考：{{ $busAssignment->operation_remarks ?? '--' }}</td>
 </tr>

 <tr>
  <td colspan="5" class="remark">立替</td>
  <td colspan="5" class="remark">会社精算</td>
 </tr>

 <tr>
  <td colspan="7">集金</td>
  <td>責任者</td>
  <td>確認</td>
  <td>担当</td>

 </tr>
</table>

<!--<div class="footer">-->
<!--    <div class="c1">{PAGENO} / {nbpg}</div>-->
<!--    <div class="c2">-->
<!--        発行日 {{ $issueDate ?? '--' }}　　-->
<!--        発行担当 {{ $issueBy ?? '--' }}-->
<!--    </div>-->
<!--</div>-->

</body>
</html>