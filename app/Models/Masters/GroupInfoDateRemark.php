<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GroupInfoDateRemark extends Model
{
    protected $table = 'group_info_date_remarks';
    
    protected $fillable = [
        'date',
        'remark',
        'stop_order',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'date' => 'date',
        'stop_order' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $attributes = [
        'stop_order' => false,
    ];
    
    public static function isStopOrder($date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        return self::where('date', $dateStr)
                   ->where('stop_order', true)
                   ->exists();
    }
    
    public static function getRemark($date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        return self::where('date', $dateStr)->value('remark');
    }
    
    public static function getByDate($date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        return self::where('date', $dateStr)->first();
    }
    
    public static function getRemarksByDateRange($startDate, $endDate)
    {
        $startStr = $startDate instanceof Carbon ? $startDate->format('Y-m-d') : $startDate;
        $endStr = $endDate instanceof Carbon ? $endDate->format('Y-m-d') : $endDate;
        
        return self::whereBetween('date', [$startStr, $endStr])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });
    }
    
    public static function getStopOrderDates($startDate, $endDate)
    {
        $startStr = $startDate instanceof Carbon ? $startDate->format('Y-m-d') : $startDate;
        $endStr = $endDate instanceof Carbon ? $endDate->format('Y-m-d') : $endDate;
        
        return self::whereBetween('date', [$startStr, $endStr])
            ->where('stop_order', true)
            ->pluck('date')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            });
    }
    
    public static function hasStopOrderInRange($startDate, $endDate)
    {
        $startStr = $startDate instanceof Carbon ? $startDate->format('Y-m-d') : $startDate;
        $endStr = $endDate instanceof Carbon ? $endDate->format('Y-m-d') : $endDate;
        
        return self::whereBetween('date', [$startStr, $endStr])
            ->where('stop_order', true)
            ->exists();
    }
    
    public static function updateOrCreateRemark($date, $remark = null, $stopOrder = false, $userId = null)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        
        $data = [
            'remark' => $remark,
            'stop_order' => $stopOrder,
            'updated_at' => now(),
        ];
        
        if ($userId) {
            $data['updated_by'] = $userId;
        }
        
        $instance = self::updateOrCreate(
            ['date' => $dateStr],
            $data
        );
        
        if ($instance->wasRecentlyCreated && $userId) {
            $instance->created_by = $userId;
            $instance->save();
        }
        
        return $instance;
    }
    
    public static function deleteByDate($date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        return self::where('date', $dateStr)->delete();
    }
    
    public function getSummaryAttribute()
    {
        if (empty($this->remark)) {
            return '';
        }
        
        $length = 15;
        return mb_strlen($this->remark) > $length 
            ? mb_substr($this->remark, 0, $length) . '...' 
            : $this->remark;
    }
}