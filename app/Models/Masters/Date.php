<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    protected $table = 'dates';

    protected $fillable = [
        'date',
        'description'
    ];

    protected $casts = [
        'date' => 'date'
    ];
}