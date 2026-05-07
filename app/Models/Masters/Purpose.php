<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    protected $fillable = ['purpose_code', 'purpose_name', 'category', 'display_order', 'is_active'];
}