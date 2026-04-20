<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class VehicleGrade extends Model
{
    protected $table = 'vehicle_grades';
    
    protected $fillable = [
        'code',
        'grade_name',
        'description',
    ];
    
    public $timestamps = false;
    
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_grade_id');
    }
}