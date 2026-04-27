<?php

namespace App\Models\Driver;

use Illuminate\Database\Eloquent\Model;

class DriverOperationStatus extends Model
{
    protected $table = 'driver_operation_status';
    
    protected $fillable = [
        'code',
        'name',
        'group',
        'description',
        'display_order',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'display_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}