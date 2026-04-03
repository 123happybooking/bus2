<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
// 如果 Customer 和 Bank 模型存在，请取消下面两行的注释并修正命名空间
// use App\Models\masters\Customer; 
// use App\Models\masters\Bank; 

class PaymentHeader extends Model
{
    use SoftDeletes;

    protected $table = 'payment_headers';

    /**
     * 可批量赋值的字段
     * 注意：id, created_at, updated_at, deleted_at, is_deleted 由框架或数据库自动处理，通常不放入 fillable
     */
    protected $fillable = [
        'group_id',
        'currency_code',
        'total_amount',
        'bank_id',
        'payment_date',
        'staff_id',
        'remark',
        'batch_token',
        'created_by',
        'notes',
    ];

    /**
     * 需要转换为原生类型的字段
     */
    protected $casts = [
        'id' => 'integer',
        'group_id' => 'integer',
        'bank_id' => 'integer',
        'total_amount' => 'decimal:2',
        'payment_date' => 'date',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function invocie()
    {
        // 请根据实际 Customer 模型的路径调整
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function Staff()
    {
        // 请根据实际 Customer 模型的路径调整
        return $this->belongsTo(Staff::class, 'staff_id');
    }


    /**
     * 关联：入金明细列表 (一对多)
     */
    public function details(): HasMany
    {
        // 请根据实际 PaymentDetail 模型的路径调整
        return $this->hasMany(PaymentDetail::class, 'payment_header_id', 'id');
    }
    

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }
}