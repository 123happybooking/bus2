<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = ['fee_code', 'fee_name', 'fee_category', 'tax_rate', 'default_amount', 'display_order', 'is_active'];
}