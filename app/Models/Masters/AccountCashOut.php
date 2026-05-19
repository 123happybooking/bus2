<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountCashOut extends Model
{
    // 指定表名
    protected $table = 'account_cash_outs';

    /**
     * 可以被批量赋值的字段
     * 注意：id 和时间戳字段通常不需要包含在 fillable 中
     */
    protected $fillable = [
        'type_id',
        'cashin_id',
        'title',
        'sort',
    ];

    /**
     * 类型转换
     */
    protected $casts = [
        'type_id' => 'integer',
        'cashin_id' => 'integer',
        'sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $type = [
        '1' => '営業活動',
        '2' => '投資活動',
        '3' => '財務活動',
        '4' => '現預金残高の增加',
        '5' => '期首現預金残高',
        '6' => '期末現預金残高',
    ];

    public function cashOutData()
    {
        return $this->hasOne(AccountCashoutData::class, 'cashout_id');
    }

}