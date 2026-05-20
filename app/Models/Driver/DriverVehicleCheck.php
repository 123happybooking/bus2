<?php

namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverVehicleCheck extends Model
{
    use HasFactory;

    protected $table = 'driver_vehicle_check';

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'driver_vehicle_check_items_id',
        'is_ok',
        'date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'driver_id' => 'integer',
        'vehicle_id' => 'integer',
        'driver_vehicle_check_items_id' => 'integer',
        'is_ok' => 'boolean',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function checkItem()
    {
        return $this->belongsTo(DriverVehicleCheckItems::class, 'driver_vehicle_check_items_id');
    }

    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Staff::class, 'updated_by');
    }

    public function getIsOkTextAttribute()
    {
        if (is_null($this->is_ok)) {
            return '未回答';
        }
        return $this->is_ok ? '✓ 正常' : '✗ 異常';
    }

    public function getIsOkClassAttribute()
    {
        if (is_null($this->is_ok)) {
            return 'text-secondary';
        }
        return $this->is_ok ? 'text-success' : 'text-danger';
    }

    public function scopeByDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}