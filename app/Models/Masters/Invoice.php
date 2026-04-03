<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'group_id',
        'type',
        'invoice_number',
        'invoice_date',
        'due_date',
        'billing_title',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'tax_mode',
        'tax_rate_default',
        'currency_code',
        'exchange_rate',
        'language_code',
        'pdf_template_id',
        'pdf_file_path',
        'status',
        'notes',
        'pdf_generated_at',
        'agency_id',
        'agency_detail',
        'operation_date',
        'reservation_id'
        
    ];

    // 关联：客户
    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    // 关联：银行
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    // 关联：操作担当
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }


    // 关联：明细项
    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    // 关联：税率汇总
    public function taxSummaries()
    {
        return $this->hasMany(InvoiceTaxSummary::class, 'invoice_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}