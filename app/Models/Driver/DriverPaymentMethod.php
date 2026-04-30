<?php
namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;

class DriverPaymentMethod extends Model
{
    protected $table = 'driver_payment_methods';
    
    protected $fillable = [
        'method_name',
        'is_reimbursable',
        'remark',
    ];
}