<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_code', 'customer_name', 'customer_name_kana', 
        'customer_type', 'postal_code', 'address', 'phone_number', 
        'fax_number', 'manager_name', 'email', 'closing_day', 
        'payment_method', 'is_active', 'remarks'
    ];
        
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}