<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DriverAttendance extends Model
{
    protected $table = 'driver_attendance';
    
    protected $fillable = [
        'driver_id',
        'date',
        'attendance_category_id',
        'start_time',
        'end_time',
        'remarks',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    public function category()
    {
        return $this->belongsTo(AttendanceCategory::class, 'attendance_category_id');
    }
    
    public static function getByDriverAndDate($driverId, $date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        return self::where('driver_id', $driverId)
            ->where('date', $dateStr)
            ->first();
    }
    
    public static function getAttendanceByDateRange($driverIds, $startDate, $endDate)
    {
        $startStr = $startDate instanceof Carbon ? $startDate->format('Y-m-d') : $startDate;
        $endStr = $endDate instanceof Carbon ? $endDate->format('Y-m-d') : $endDate;
        
        $query = self::whereBetween('date', [$startStr, $endStr])
            ->with('category');
        
        if (is_array($driverIds) && !empty($driverIds)) {
            $query->whereIn('driver_id', $driverIds);
        }
        
        return $query->get()->keyBy(function ($item) {
            return $item->driver_id . '_' . $item->date->format('Y-m-d');
        });
    }
}