<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AccountMonthSum extends Model
{
    use SoftDeletes;

    protected $table = 'account_month_sums';

    // 允许批量赋值的字段
    protected $fillable = [
        'year',              // 年份
        'month',             // 月份
        'created_by',        // 操作人
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

}

