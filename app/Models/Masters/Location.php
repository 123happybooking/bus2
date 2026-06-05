<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    
    protected $fillable = [
        'area',
        'category',
        'name',
        'address',
        'phone',
        'remark',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}