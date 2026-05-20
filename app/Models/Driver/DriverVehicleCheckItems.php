<?php

namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverVehicleCheckItems extends Model
{
    use HasFactory;

    protected $table = 'driver_vehicle_check_items';

    protected $fillable = [
        'category',
        'item_name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function getActiveGroupedByCategory()
    {
        return self::where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->groupBy('category');
    }

    public static function getCategoryList()
    {
        return self::where('is_active', true)
            ->orderBy('display_order')
            ->pluck('category')
            ->unique()
            ->values();
    }

    public function checks()
    {
        return $this->hasMany(DriverVehicleCheck::class, 'driver_vehicle_check_items_id');
    }
}