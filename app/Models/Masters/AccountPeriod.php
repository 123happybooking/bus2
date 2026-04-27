<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountPeriod extends Model
{
    protected $table = 'account_periods';

    protected $fillable = [
        'title',
        'start'
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}