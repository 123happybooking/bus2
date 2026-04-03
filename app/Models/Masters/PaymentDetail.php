<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// 如果 Invoice 模型存在，请取消下面注释并修正命名空间
// use App\Models\masters\Invoice; 

class PaymentDetail extends Model
{
    use SoftDeletes;

    protected $table = 'payment_details';

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'payment_header_id',
        'invoice_id',
        'write_off_amount',
        // 如果需要在明细层单独存储备注或日期（虽然当前设计是公共的，但预留字段是个好习惯）
        // 'remark', 
        // 'payment_date',
        // 'bank_id',
    ];

    /**
     * 需要转换为原生类型的字段
     */
    protected $casts = [
        'id' => 'integer',
        'payment_header_id' => 'integer',
        'invoice_id' => 'integer',
        'write_off_amount' => 'decimal:2',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联：所属入金主表
     * 注意：这是逻辑外键，数据库层面无强制约束
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(PaymentHeader::class, 'payment_header_id');
    }

    /**
     * 关联：所属请求书 (Invoice)
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * 作用域：仅查询未删除的数据
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }
}