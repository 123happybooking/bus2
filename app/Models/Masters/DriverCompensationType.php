<?php
namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class DriverCompensationType extends Model
{
    protected $table = 'driver_compensation_types';
    
    protected $fillable = [
        'comp_name',
        'display_order',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
}