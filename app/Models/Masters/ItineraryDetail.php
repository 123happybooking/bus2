<?php
namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class ItineraryDetail extends Model
{
    protected $fillable = [
        'itinerary_id',
        'display_order',
        'arrival_time',
        'departure_time',
        'description',
        'remark'
    ];

    protected $casts = [
        'arrival_time' => 'datetime:H:i',
        'departure_time' => 'datetime:H:i',
    ];

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }
}