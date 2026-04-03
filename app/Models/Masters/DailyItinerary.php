<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class DailyItinerary extends Model
{
    protected $table = 'daily_itinerary';
    
    protected $fillable = [
        'group_info_id',
        'bus_assignment_id',
        'date',
        'time_start',
        'time_end',
        'itinerary',
        'start_location',
        'end_location',
        'accommodation',
        'vehicle',
        'vehicle_id',
        'driver',
        'driver_id',
        'guide',
        'guide_id',
        'remarks',
        'bus_ass_uuid',
        'display_order',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'date' => 'date',
        'accommodation' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    public function guide()
    {
        return $this->belongsTo(Guide::class, 'guide_id');
    }
    
    public function groupInfo()
    {
        return $this->belongsTo(GroupInfo::class, 'group_info_id', 'id');
    }

    public function busAssignment()
    {
        return $this->belongsTo(BusAssignment::class, 'bus_assignment_id', 'id');
    }
    
    public function busAssignments()
    {
        return $this->hasMany(BusAssignment::class, 'daily_itinerary_id', 'id');
    }
}