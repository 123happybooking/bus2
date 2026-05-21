<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class DriverLicenseType extends Model
{
    protected $table = 'driver_license_types';

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