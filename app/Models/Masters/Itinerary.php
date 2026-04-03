<?php
namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    protected $fillable = ['itinerary_code', 'itinerary_name', 'category', 'remarks'];
    
    public function details()
    {
        return $this->hasMany(ItineraryDetail::class)->orderBy('display_order');
    }
}