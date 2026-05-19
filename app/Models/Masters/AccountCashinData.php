<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountCashinData extends Model
{
    // 指定表名
    protected $table = 'account_cashin_datas';

    /**
     * 可以被批量赋值的字段
     */
    protected $fillable = [
        'period_id',
        'mod',
        'cashin_id',
        'current_amount',
        'previous_amount',

    ];

    /**
     * 类型转换
     */
    protected $casts = [
        'period_id' => 'integer',
        'mod' => 'integer',
        'cashin_id' => 'integer',
        'current_amount' => 'integer',
        'previous_amount' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


}