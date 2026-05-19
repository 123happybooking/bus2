<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\AccountCashIn;
use App\Models\Masters\AccountCashinData;
use App\Models\Masters\AccountCashOut;

class AccountCashoutData extends Model
{
    // 指定表名
    protected $table = 'account_cashout_datas';

    /**
     * 可以被批量赋值的字段
     */
    protected $fillable = [
        'period_id',
        'type_id',
        'cashout_id',
        'current_amount',

    ];

    /**
     * 类型转换
     */
    protected $casts = [
        'period_id' => 'integer',
        'type_id' => 'integer',
        'cashout_id' => 'integer',
        'current_amount' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    //处理每一条数据
    public function dealData($period_id)
    {
        AccountCashoutData::where('period_id',$period_id)->delete();
        $batchData = [];
        $cashOut = AccountCashOut::get();
        foreach ($cashOut as  $value) {
            if ($value->id == 5) {//税引前当期純利益
                $amount = AccountCashinData::where('period_id',$period_id)->where('cashin_id',36)->value('current_amount');
            }
            if ($value->id == 6) {//減価償却費
                $amount = -(AccountCashinData::where('period_id',$period_id)->where('cashin_id',38)->value('current_amount'));
            }
            if ($value->id == 7) {//受取利息・受取配当金
                $amount = -(AccountCashinData::where('period_id',$period_id)->where('cashin_id',39)->value('current_amount'));
            }
            if ($value->id == 8) {//支払利息
                $amount = AccountCashinData::where('period_id',$period_id)->where('cashin_id',40)->value('current_amount');
            }
            if ($value->id == 9) {//有価証券売却損益・評価損
                $amount = AccountCashinData::where('period_id',$period_id)->where('cashin_id',42)->value('current_amount') - AccountCashinData::where('period_id',$period_id)->where('cashin_id',41)->value('current_amount');
            }
            if ($value->id == 10) {//固定資産売却損益・廃棄損
                $amount = AccountCashinData::where('period_id',$period_id)->where('cashin_id',44)->value('current_amount') - AccountCashinData::where('period_id',$period_id)->where('cashin_id',43)->value('current_amount');
            }
            if ($value->id == 11) {//売上債権減少（△増加）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',14)->first();
                $amount = -($cashinData->current_amount - $cashinData->previous_amount);
            }
            if ($value->id == 12) {//棚卸資産減少（△増加）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',15)->first();
                $amount = -($cashinData->current_amount - $cashinData->previous_amount);
            }
            if ($value->id == 13) {//その他の流動資産減少（△増加）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',19)->first();
                $amount = -($cashinData->current_amount - $cashinData->previous_amount);
            }
            if ($value->id == 14) {//繰延資産減少（△増加）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',25)->first();
                $amount = -($cashinData->current_amount - $cashinData->previous_amount);
            }
            if ($value->id == 15) {//仕入債務増加（△減少）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',26)->first();
                $amount = $cashinData->current_amount - $cashinData->previous_amount;
            }
            if ($value->id == 16) {//その他の流動負債増加（△減少）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',30)->first();
                $amount = $cashinData->current_amount - $cashinData->previous_amount;
            }
            if ($value->id == 17) {//その他の固定負債増加（△減少）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',33)->first();
                $amount = $cashinData->current_amount - $cashinData->previous_amount;
            }
            if ($value->id == 18) {//役員賞与
                $amount = -(AccountCashinData::where('period_id',$period_id)->where('cashin_id',46)->value('current_amount'));
            }
            if ($value->id == 19) {//利息及び配当金の受取額
                $amount = AccountCashinData::where('period_id',$period_id)->where('cashin_id',39)->value('current_amount');
            }
            if ($value->id == 20) {//利息の支払額
                $amount = -(AccountCashinData::where('period_id',$period_id)->where('cashin_id',40)->value('current_amount'));
            }
            if ($value->id == 21) {//法人税等の支払額
                $amount1 = AccountCashinData::where('period_id',$period_id)->where('cashin_id',37)->value('current_amount');
                $amount2 = AccountCashinData::where('period_id',$period_id)->where('cashin_id',36)->value('current_amount');
                $amount3_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',18)->first();
                $amount3 = $amount3_data->current_amount - $amount3_data->previous_amount;
                $amount4_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',24)->first();
                $amount4 = $amount4_data->current_amount - $amount4_data->previous_amount;
                $amount5_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',29)->first();
                $amount5 = $amount5_data->current_amount - $amount5_data->previous_amount;
                $amount6_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',32)->first();
                $amount6 = $amount6_data->current_amount - $amount6_data->previous_amount;
                $amount7_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',28)->first();
                $amount7 = $amount7_data->current_amount - $amount7_data->previous_amount;
                
                $amount = $amount1-$amount2-$amount3-$amount4+$amount5+$amount6+$amount7;
            }


            if ($value->id == 22) {//有形固定資産の減少（△増加）
                $amount1_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',20)->first();
                $amount1 = $amount1_data->current_amount - $amount1_data->previous_amount;
                $amount2 = AccountCashinData::where('period_id',$period_id)->where('cashin_id',43)->value('current_amount');
                $amount3 = AccountCashinData::where('period_id',$period_id)->where('cashin_id',44)->value('current_amount');
                $amount4 = AccountCashinData::where('period_id',$period_id)->where('cashin_id',38)->value('current_amount');
                $amount = -$amount1+$amount2-$amount3-$amount4;

            }
            if ($value->id == 23) {//有価証券の減少（△増加）
                $amount1_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',16)->first();
                $amount1 = $amount1_data->current_amount - $amount1_data->previous_amount;
                $amount2_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',21)->first();
                $amount2 = $amount2_data->current_amount - $amount2_data->previous_amount;
                $amount3 = AccountCashinData::where('period_id',$period_id)->where('cashin_id',41)->value('current_amount');
                $amount4 = AccountCashinData::where('period_id',$period_id)->where('cashin_id',42)->value('current_amount');
                $amount = -$amount1-$amount2+$amount3-$amount4;
            }
            if ($value->id == 24) {//貸付金の減少(△増加）
                $amount1_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',17)->first();
                $amount1 = $amount1_data->current_amount - $amount1_data->previous_amount;
                $amount2_data = AccountCashinData::where('period_id',$period_id)->where('cashin_id',22)->first();
                $amount2 = $amount2_data->current_amount - $amount2_data->previous_amount;
                $amount = -$amount1-$amount2;
            }
            if ($value->id == 25) {//その他の固定資産の減少（△増加）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',23)->first();
                $amount = -($cashinData->current_amount - $cashinData->previous_amount);
            }

            if ($value->id == 26) {//短期借入金の増加（△減少）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',27)->first();
                $amount = $cashinData->current_amount - $cashinData->previous_amount;
            }
            if ($value->id == 27) {//長期借入金･社債の増加（△減少）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',31)->first();
                $amount = $cashinData->current_amount - $cashinData->previous_amount;
            }
            if ($value->id == 28) {//増資
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',34)->first();
                $amount = $cashinData->current_amount - $cashinData->previous_amount;
            }
            if ($value->id == 29) {//支払配当金
                $amount = -(AccountCashinData::where('period_id',$period_id)->where('cashin_id',45)->value('current_amount'));
            }

            if ($value->id == 30) {//現預金残高の増加（△減少）
                $cashinData = AccountCashinData::where('period_id',$period_id)->where('cashin_id',13)->first();
                $amount = $cashinData->current_amount - $cashinData->previous_amount;
            }
            if ($value->id == 31) {//期首現預金残高
                $amount = AccountCashinData::where('period_id',$period_id)->where('cashin_id',13)->value('previous_amount');
            }
            if ($value->id == 32) {//期末現預金残高
                $amount = AccountCashinData::where('period_id',$period_id)->where('cashin_id',13)->value('current_amount');
            }
    

            $batchData[] = [
                'period_id' => $period_id,
                'type_id' => $value->type_id,
                'cashout_id' => $value->id,
                'current_amount' => $amount,
                'created_at' => now(), 
                'updated_at' => now(),
            ];

        }
        if (!empty($batchData)) {
            AccountCashoutData::insert($batchData);
        }
        return true;
    }

}