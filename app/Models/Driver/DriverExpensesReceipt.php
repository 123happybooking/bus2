<?php

namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Driver;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\DailyItinerary;

class DriverExpensesReceipt extends Model
{
    protected $table = 'driver_expenses_receipt';
    
    protected $fillable = [
        'bus_assignment_id',
        'itinerary_id',
        'driver_id',
        'expense_date',
        'image_path',
    ];
    
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    public function busAssignment()
    {
        return $this->belongsTo(BusAssignment::class, 'bus_assignment_id');
    }
    
    public function itinerary()
    {
        return $this->belongsTo(DailyItinerary::class, 'itinerary_id');
    }
}