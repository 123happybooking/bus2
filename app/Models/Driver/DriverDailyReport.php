<?php

namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Driver as MasterDriver;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Staff;

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
        'start_work_time',
        'end_work_time',
        'weather',
        'remark',
        'allow_edit',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'start_mileage' => 'integer',
        'end_mileage' => 'integer',
        'allow_edit' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
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
    
    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }
    
    public function updater()
    {
        return $this->belongsTo(Staff::class, 'updated_by');
    }
}