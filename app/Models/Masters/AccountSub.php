<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSub extends Model
{
    use HasFactory, SoftDeletes;

    // 显式指定表名
    protected $table = 'account_subs';

    protected $fillable = [
        'name',
        'account_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 关联到主科目 (Account)
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
