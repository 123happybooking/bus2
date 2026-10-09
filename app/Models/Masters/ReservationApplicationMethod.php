<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class ReservationApplicationMethod extends Model
{
    protected $table = 'reservation_application_methods';

    protected $fillable = [
        'method_name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];
}