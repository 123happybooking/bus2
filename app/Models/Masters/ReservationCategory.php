<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class ReservationCategory extends Model
{
    protected $table = 'reservation_categories';
    
    protected $fillable = [
        'category_code',
        'category_name',
        'color_code',
        'display_order',
        'is_active',
        'vehicle_workload',
        'driver_workload',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'vehicle_workload' => 'decimal:2',
        'driver_workload' => 'decimal:2',
        'display_order' => 'integer',
    ];
}