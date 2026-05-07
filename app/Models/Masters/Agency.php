<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $fillable = [
        'agency_code', 'agency_name', 'branch_name', 'postal_code', 
        'address', 'phone_number', 'fax_number', 'manager_name', 
        'commission_rate', 'closing_day', 'payment_day', 'is_active', 'remarks',
        'email', 'display_order', 'country', 'type'
    ];
}