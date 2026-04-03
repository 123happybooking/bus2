<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'tax_id',
        'is_active',
    ];

    /**
     * 类型转换
     * 将 is_active 自动转换为布尔值
     * 将日期字段自动转换为 Carbon 实例
     */
    protected $casts = [
        'is_active' => 'boolean',
        'category_id' => 'integer',
        'tax_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }

    public function tax()
    {
        return $this->belongsTo(AccountTax::class, 'tax_id');
    }
}