<?php
namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Driver;
use App\Models\Masters\DriverCompensationType;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\DailyItinerary;

class DriverCompensation extends Model
{
    protected $table = 'driver_compensations';
    
    protected $fillable = [
        'group_info_id',
        'bus_assignment_id',
        'driver_id',
        'comp_id',
        'target_date',
        'price',
        'qty',
        'amount',
        'remark',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'qty' => 'decimal:2',
        'amount' => 'decimal:2',
    ];
    
    public function busAssignment()
    {
        return $this->belongsTo(BusAssignment::class, 'bus_assignment_id');
    }
    
    public function itinerary()
    {
        return $this->belongsTo(DailyItinerary::class, 'itinerary_id');
    }
    
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    public function compensationType()
    {
        return $this->belongsTo(DriverCompensationType::class, 'comp_id');
    }
}