<?php
namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;

class DriverExpenseType extends Model
{
    protected $table = 'driver_expense_types';
    
    protected $fillable = [
        'type_name',
        'category',
        'remark',
    ];
}