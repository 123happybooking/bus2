<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class DriverOperationStatus extends Model
{
    protected $table = 'driver_operation_status';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'description',
        'display_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}