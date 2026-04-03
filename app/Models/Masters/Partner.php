<?php


namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'partner_code', 'partner_name', 'branch_name', 'postal_code', 
        'address', 'phone_number', 'fax_number', 'manager_name', 
        'invoice_number', 'closing_day', 'payment_month', 'payment_day', 
        'is_active', 'remarks'
    ];
}