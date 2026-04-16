<?php

namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Driver as MasterDriver;
use App\Models\Masters\Vehicle;

class DriverDailyReport extends Model
{
    protected $table = 'driver_daily_reports';
    
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'date',
        'start_time',
        'start_mileage',
        'end_time',
        'end_mileage',
    ];
    
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    
    public function driver()
    {
        return $this->belongsTo(MasterDriver::class, 'driver_id');
    }
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    
    public function getDistanceAttribute()
    {
        if ($this->start_mileage && $this->end_mileage) {
            return $this->end_mileage - $this->start_mileage;
        }
        return null;
    }
}