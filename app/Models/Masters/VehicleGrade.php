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
        'display_order',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];
    
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_grade_id');
    }
}