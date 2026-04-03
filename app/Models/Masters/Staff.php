<?php


namespace App\Models\Masters;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    protected $table = 'staffs';

    protected $fillable = [
        'user_company_id','branch_id', 'staff_code', 'name', 'login_id', 'password', 'role', 'is_active', 'email', 'phone_number', 'display_order'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}