<?php
namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class GroupInfo extends Model
{
    protected $table = 'group_info';
    protected $primaryKey = 'id';
    
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'group_name',
        'agency',
        'agency_contact_name',
        'agency_country',
        'reservation_status',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'vehicle_type_selection',
        'remarks',
        'itinerary_id',
        'business_category',
        'reservation_categories_id',
        'itinerary_name',
        'adult_count',
        'child_count',
        'other_count',
        'luggage_count',
        'reservation_channel',
        'reservation_categories',
        'vehicle_type',
        'vehicle_model',
        'vehicle',
        'vehicle_number',
        'driver',
        'guide',
        'vehicle_branch',
        'guide_count',
        'guide_id',
        'copy_new_start_date',
        'agt_tour_code',
        'agt_tour_id',
        'ignore_operation',
        'ignore_attendance',
        'reception_contact',
        'reception_office',
        'created_tag',
        'lock_arrangement',
        'operation_count',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'ignore_operation' => 'boolean',
        'ignore_attendance' => 'boolean',
        'lock_arrangement' => 'boolean',
        'operation_count' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function busAssignments()
    {
        return $this->hasMany(BusAssignment::class, 'group_info_id', 'id');
    }
    
    public function dailyItineraries()
    {
        return $this->hasMany(DailyItinerary::class, 'group_info_id', 'id');
    }
    
    public function guide()
    {
        return $this->belongsTo(Guide::class, 'guide_id', 'id');
    }
    
    public function agencyInfo()
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'id');
    }
    
    
    public function getReservationCategoriesIdAttribute($value)
    {
        return $value ?? 0;
    }
    
    public function setReservationCategoriesIdAttribute($value)
    {
        $this->attributes['reservation_categories_id'] = $value == 0 ? null : $value;
    }
    
    public function reservationCategory()
    {
        return $this->belongsTo(ReservationCategory::class, 'reservation_categories_id');
    }
}