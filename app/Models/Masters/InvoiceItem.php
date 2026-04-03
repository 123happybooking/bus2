<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'line_number',
        'item_description',
        'quantity',
        'unit',
        'unit_price',
        'amount',
        'tax_rate',
        'tax_included',
        // created_at / updated_at 不填入
    ];

    // 关联：所属发票
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}