<?php

namespace App\Models\Masters;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'login_id',
        'password',
        'user_company_name',
        'user_plan',
        'user_start_day',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'id' => 'integer',
        'password' => 'hashed',
        'user_start_day' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = [
        'user_start_day',
        'created_at',
        'updated_at',
    ];

    public function scopeFindByLoginId($query, $loginId)
    {
        return $query->where('login_id', $loginId);
    }

    public function getPlanAttribute()
    {
        return $this->user_plan;
    }

    public function getCompanyNameAttribute()
    {
        return $this->user_company_name;
    }

    public function getAuthIdentifierName()
    {
        return 'login_id';
    }
}