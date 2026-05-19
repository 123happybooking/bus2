<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountConfig extends Model
{
    protected $table = 'account_configs';

    protected $fillable = [
        'account_cash_id',
        'account_deposit_id',
        'account_mgj_id',
        'account_spmsg_id',
    ];

    /**
     * 类型转换
     * 将 is_active 自动转换为布尔值
     * 将日期字段自动转换为 Carbon 实例
     */
    protected $casts = [
        'account_cash_id' => 'integer',
        'account_deposit_id' => 'integer',
        'account_mgj_id' => 'integer',
        'account_spmsg_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    
}