<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'facility_code', 'category', 'facility_name', 'facility_kana',
        'postal_code', 'address', 'phone_number', 'fax_number',
        'bus_parking_available', 'parking_remarks'
    ];
}