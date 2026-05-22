<?php
namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Driver;
use App\Models\Masters\Vehicle;

class DriverVehicleCheckRemark extends Model
{
    protected $table = 'driver_vehicle_check_remark';
    
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'date',
        'remark',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'date' => 'date',
    ];
    
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}