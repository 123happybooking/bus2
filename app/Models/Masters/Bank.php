<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'bank_name','bank_info','is_active','display_order','remarks'
    ];
}