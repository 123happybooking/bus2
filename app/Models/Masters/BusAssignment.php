<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BusAssignment extends Model
{
    use HasFactory;

    protected $table = 'bus_assignment';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'group_info_id',
        'daily_itinerary_id',
        'vehicle_id',
        'driver_id',
        'guide_id',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'lock_arrangement',
        'status_sent',
        'status_finalized',
        'count_daily',
        'created_by',
        'updated_by',
        'vehicle_number',
        'step_car',
        'adult_count',
        'child_count',
        'guide_count',
        'other_count',
        'luggage_count',
        'vehicle_type_spec_check',
        'temporary_driver',
        'representative',
        'representative_phone',
        'attention',
        'operation_remarks',
        'operation_memo',
        'operation_basic_remarks',
        'doc_remarks',
        'history_remarks',
        'vehicle_index',
        'ignore_operation',
        'ignore_driver',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'lock_arrangement' => 'boolean',
        'status_sent' => 'boolean',
        'status_finalized' => 'boolean',
        'count_daily' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'vehicle_type_spec_check' => 'boolean',
        'temporary_driver' => 'boolean',
        'adult_count' => 'integer',
        'child_count' => 'integer',
        'guide_count' => 'integer',
        'other_count' => 'integer',
        'luggage_count' => 'integer',
        'ignore_operation' => 'boolean',
        'ignore_driver' => 'boolean',
    ];

    public function groupInfo(): BelongsTo
    {
        return $this->belongsTo(GroupInfo::class, 'group_info_id', 'id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class, 'guide_id', 'id');
    }

    public function dailyItinerary(): BelongsTo
    {
        return $this->belongsTo(DailyItinerary::class, 'daily_itinerary_id', 'id');
    }

    public function dailyItineraries(): HasMany
    {
        return $this->hasMany(DailyItinerary::class, 'bus_assignment_id', 'id')
                    ->orderBy('date', 'asc')
                    ->orderBy('time_start', 'asc');
    }

    public function getStatusDisplayAttribute(): string
    {
        if ($this->status_finalized) {
            return '最終確定';
        } elseif ($this->status_sent) {
            return '送信済';
        } elseif ($this->lock_arrangement) {
            return 'ロック中';
        }
        return '未確定';
    }

    public function getPeriodDisplayAttribute(): string
    {
        $start = $this->start_date
            ? (is_string($this->start_date) ? date('m/d', strtotime($this->start_date)) : $this->start_date->format('m/d'))
            : '';
        $end = $this->end_date
            ? (is_string($this->end_date) ? date('m/d', strtotime($this->end_date)) : $this->end_date->format('m/d'))
            : '';

        if ($start && $end) {
            return $start . ' ' . ($this->start_time ? substr($this->start_time, 0, 5) : '') . "\n" . $end . ' ' . ($this->end_time ? substr($this->end_time, 0, 5) : '');
        } elseif ($start) {
            return $start . ' ' . ($this->start_time ? substr($this->start_time, 0, 5) : '') . "\n---";
        } elseif ($end) {
            return "---\n" . $end . ' ' . ($this->end_time ? substr($this->end_time, 0, 5) : '');
        }
        return "---\n---";
    }

    public function getVehicleDisplayAttribute(): string
    {
        if ($this->vehicle) {
            $modelName = $this->vehicle->vehicleModel ? $this->vehicle->vehicleModel->model_name : '';
            $icon = $this->vehicle_type_spec_check ? '⭐ ' : '';
            return $icon . $this->vehicle->registration_number . ($modelName ? "\n" . $modelName : '');
        }
        return $this->vehicle_number ? '号車 ' . $this->vehicle_number : '---';
    }

    public function getDriverDisplayAttribute(): string
    {
        return $this->driver?->name ?? '---';
    }

    public function getIdDisplayAttribute(): string
    {
        return '運行:' . $this->id;
    }

    public function getStartInfoAttribute(): string
    {
        $firstDay = $this->dailyItineraries->first();
        if ($firstDay) {
            $date = $firstDay->date instanceof \Carbon\Carbon 
                ? $firstDay->date->format('m/d') 
                : date('m/d', strtotime($firstDay->date));
            $time = $firstDay->time_start ? substr($firstDay->time_start, 0, 5) : '';
            return $date . ' ' . $time . "\n" . ($firstDay->start_location ?? '---');
        }
        
        $startDate = $this->start_date
            ? (is_string($this->start_date) ? date('m/d', strtotime($this->start_date)) : $this->start_date->format('m/d'))
            : '---';
        $startTime = $this->start_time ? substr($this->start_time, 0, 5) : '';
        return $startDate . ' ' . $startTime . "\n---";
    }

    public function getGroupStickerAttribute(): string
    {
        $groupName = $this->groupInfo?->group_name ?? '---';
        $sticker = $this->step_car ?? '---';
        return $groupName . "\n" . $sticker;
    }

    public function getAgencyCountryAttribute(): string
    {
        $agency = $this->groupInfo?->agency ?? '---';
        $country = $this->groupInfo?->agency_country ?? '---';
        return $agency . "\n" . $country;
    }

    public function getBusinessItineraryAttribute(): string
    {
        $business = $this->groupInfo?->business_category ?? '---';
        $itinerary = $this->groupInfo?->itinerary_name ?? '---';
        return $business . "\n" . $itinerary;
    }

    public function getReservationStatusDisplayAttribute(): string
    {
        return $this->groupInfo?->reservation_status ?? '---';
    }

    public function getBillingDisplayAttribute(): string
    {
        return "--\n--";
    }

    public function getAdvanceDisplayAttribute(): string
    {
        return '--';
    }

    public function getFormattedVehicleIndexAttribute(): string
    {
        return sprintf('%02d', $this->vehicle_index ?? 1);
    }

    public function getRemarksArrayAttribute(): array
    {
        return [
            'basic' => $this->operation_basic_remarks,
            'doc' => $this->doc_remarks,
            'history' => $this->history_remarks,
        ];
    }

    public function getTotalPassengersAttribute(): int
    {
        return ($this->adult_count ?? 0) + 
               ($this->child_count ?? 0) + 
               ($this->guide_count ?? 0) + 
               ($this->other_count ?? 0);
    }

    public function scopeOrderByVehicleIndex($query)
    {
        return $query->orderBy('vehicle_index', 'asc');
    }

    public function scopeForGroup($query, $groupInfoId)
    {
        return $query->where('group_info_id', $groupInfoId)
                     ->orderBy('vehicle_index', 'asc');
    }
    
    public static function isVehicleAvailable($vehicleId, $startDate, $startTime, $endDate, $endTime, $excludeId = null)
    {
        if (empty($vehicleId)) {
            return true;
        }

        $query = self::where('vehicle_id', $vehicleId)
            ->where(function($q) use ($startDate, $endDate) {
                $q->where(function($sub) use ($startDate, $endDate) {
                    $sub->where('start_date', '<=', $endDate)
                         ->where('end_date', '>=', $startDate);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() === 0;
    }

    public static function isDriverAvailable($driverId, $startDate, $startTime, $endDate, $endTime, $excludeId = null)
    {
        if (empty($driverId)) {
            return true;
        }

        $query = self::where('driver_id', $driverId)
            ->where(function($q) use ($startDate, $endDate) {
                $q->where(function($sub) use ($startDate, $endDate) {
                    $sub->where('start_date', '<=', $endDate)
                         ->where('end_date', '>=', $startDate);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() === 0;
    }

    public static function checkAvailability($data, $excludeId = null)
    {
        $result = [
            'vehicle' => true,
            'driver' => true,
            'message' => ''
        ];

        if (!empty($data['ignore_operation']) && $data['ignore_operation'] == true) {
            return $result;
        }

        if (!empty($data['vehicle_id'])) {
            $vehicleAvailable = self::isVehicleAvailable(
                $data['vehicle_id'],
                $data['start_date'],
                $data['start_time'] ?? null,
                $data['end_date'],
                $data['end_time'] ?? null,
                $excludeId
            );
            
            if (!$vehicleAvailable) {
                $result['vehicle'] = false;
                $vehicle = Vehicle::find($data['vehicle_id']);
                $vehicleName = $vehicle ? $vehicle->registration_number : '#' . $data['vehicle_id'];
                $result['message'] .= "車両 {$vehicleName} は指定された期間ですでに予約されています。";
            }
        }

        if (!empty($data['driver_id'])) {
            $driverAvailable = self::isDriverAvailable(
                $data['driver_id'],
                $data['start_date'],
                $data['start_time'] ?? null,
                $data['end_date'],
                $data['end_time'] ?? null,
                $excludeId
            );
            
            if (!$driverAvailable) {
                $result['driver'] = false;
                $driver = Driver::find($data['driver_id']);
                $driverName = $driver ? $driver->name : '#' . $data['driver_id'];
                $result['message'] .= ($result['message'] ? ' ' : '') . "ドライバー {$driverName} は指定された期間ですでに予約されています。";
            }
        }

        return $result;
    }
}