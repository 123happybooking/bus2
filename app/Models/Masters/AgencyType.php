<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AgencyType extends Model
{
    protected $table = 'agency_types';

    protected $fillable = [
        'type_name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];
}