<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Product  extends Model
{
    protected $fillable = [
        'name',
        'language'
    ];
        
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}