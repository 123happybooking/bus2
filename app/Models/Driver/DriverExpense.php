<?php
namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Driver;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\DailyItinerary;
use Illuminate\Support\Facades\Storage;

class DriverExpense extends Model
{
    protected $table = 'driver_expenses';
    
    protected $fillable = [
        'bus_assignment_id',
        'itinerary_id',
        'driver_id',
        'expense_date',
        'amount',
        'type_id',
        'payment_method_id',
        'agency_flag',
        'remark',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'agency_flag' => 'boolean',
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
    
    public function expenseType()
    {
        return $this->belongsTo(DriverExpenseType::class, 'type_id');
    }
    
    public function paymentMethod()
    {
        return $this->belongsTo(DriverPaymentMethod::class, 'payment_method_id');
    }
    
    public function receipts()
    {
        return $this->hasMany(DriverExpensesReceipt::class, 'expense_id');
    }
}