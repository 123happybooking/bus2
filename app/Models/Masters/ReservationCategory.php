<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class ReservationCategory extends Model
{
    protected $fillable = ['category_code', 'category_name', 'color_code', 'display_order', 'is_active'];
}