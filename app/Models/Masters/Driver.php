<?php


namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'branch_id', 'driver_code', 'name', 'name_kana', 
        'phone_number', 'birth_date', 'hire_date', 
        'license_type', 'license_expiration_date', 'is_active', 
        'email', 'display_order', 'remarks'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}