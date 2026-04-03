<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $table = 'login_histories';
    public $timestamps = false;

    protected $fillable = ['staff_id', 'login_id', 'ip_address', 'user_agent', 'status', 'logged_at'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}