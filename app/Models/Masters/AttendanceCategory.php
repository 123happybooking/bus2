<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AttendanceCategory extends Model
{
    protected $table = 'attendance_categories';
    
    protected $fillable = [
        'attendance_code',
        'attendance_name',
        'is_work_day',
        'color_code',
        'display_order',
    ];
    
    public function driverAttendances()
    {
        return $this->hasMany(DriverAttendance::class, 'attendance_category_id');
    }
}