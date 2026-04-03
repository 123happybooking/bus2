<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountTax extends Model
{

    protected $table = 'account_taxs';

    protected $fillable = [
        'code',
        'name', 
        'rate',
        'calculation_type',
        'is_invoice_eligible',
    ];

    protected $dates = [
        'created_at',
        'updated_at',

    ];
}