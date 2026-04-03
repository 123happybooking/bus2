<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class InvoiceTaxSummary extends Model
{
    protected $table = 'invoice_tax_summary';


    protected $fillable = [
        'invoice_id',
        'tax_rate',
        'subtotal',
        'tax_amount',
        'total_with_tax',
        // created_at / updated_at 不填入
    ];

    // 关联：所属发票
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}