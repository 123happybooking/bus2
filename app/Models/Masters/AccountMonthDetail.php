<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AccountMonthDetail extends Model
{
    use SoftDeletes;

    protected $table = 'account_month_details';

    // 允许批量赋值的字段
    protected $fillable = [
        'month_sum_id',      // 关联的汇总ID
        'account_id',        // 账户ID
        'year',              // 年份
        'month',             // 月份
        'money_start',       // 期初金额
        'money_jie',          // 金额借方',
        'money_dai',          // 金额贷方',
        'money_end',         // 期末金额
        'sn',                // 序列号/UUID
    ];

    // 日期类型字段
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * 在创建模型时自动生成 SN (UUID)
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // 如果 sn 字段为空，则自动生成 UUID
            if (empty($model->sn)) {
                $model->sn = (string) Str::uuid();
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function subAccount()
    {
        return $this->belongsTo(AccountSub::class, 'sub_account_id');
    }

}