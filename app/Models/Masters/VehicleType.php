<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleType extends Model
{
    protected $fillable = [
        'type_name'
    ];

    public function models(): HasMany
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_type_id');
    }
}