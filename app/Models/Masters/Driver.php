<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'login_id','branch_id', 'driver_code', 'name', 'name_kana', 
        'phone_number', 'birth_date', 'hire_date', 
        'license_type', 'license_expiration_date', 'is_active', 
        'email', 'display_order', 'remarks',
        'license_image', 'seal_image'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getLicenseImageUrlAttribute()
    {
        if ($this->license_image) {
            return asset('storage/' . $this->license_image);
        }
        return null;
    }

    public function getSealImageUrlAttribute()
    {
        if ($this->seal_image) {
            return asset('storage/' . $this->seal_image);
        }
        return null;
    }
}