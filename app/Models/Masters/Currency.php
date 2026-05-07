<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Currency  extends Model
{
    protected $fillable = [
        'currency_code',
        'currency_name',
        'symbol',
        'decimal_digits',
        'rate_to_jpy',
        'rate_valid_from',
        'rate_valid_to',
        'sort',
    ];
        
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}