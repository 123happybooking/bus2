<?php

namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\DailyItinerary;

class DriverOperationLog extends Model
{
    protected $table = 'driver_operation_logs';
    
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'itinerary_id',
        'action',
        'mileage',
        'latitude',
        'longitude',
        'address',
        'status',
        'logged_at',
    ];
    
    protected $casts = [
        'logged_at' => 'datetime',
    ];
    
    public function itinerary()
    {
        return $this->belongsTo(DailyItinerary::class, 'itinerary_id');
    }
}