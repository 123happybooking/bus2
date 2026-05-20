<?php

namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverVehicleCheckCategory extends Model
{
    use HasFactory;

    protected $table = 'driver_vehicle_check_categories';

    protected $fillable = [
        'category_name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function checkItems()
    {
        return $this->hasMany(DriverVehicleCheckItems::class, 'category', 'category_name');
    }
}