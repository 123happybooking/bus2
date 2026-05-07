<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountCashIn extends Model
{
    // 指定表名
    protected $table = 'account_cash_ins';

    /**
     * 可以被批量赋值的字段
     */
    protected $fillable = [
        'mode',
        'type_id',
        'title',
        'sort',
    ];

    /**
     * 类型转换
     */
    protected $casts = [
        'mode' => 'integer',
        'type_id' => 'integer',
        'sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $type = [
        '1' => '流動資産',
        '2' => '固定資産',
        '3' => '繰延資產',
        '4' => '流動負債',
        '5' => '固定負債',
        '6' => '資本の部',
    ];

}