<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class DepartureArrivalLocation extends Model
{
    protected $table = 'departure_arrival_locations';

    protected $fillable = [
        'name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}