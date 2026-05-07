<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountDepartment extends Model
{
    protected $table = 'account_departments';

    // 允许批量赋值的字段
    protected $fillable = [
        'name'
    ];


    protected $dates = [
        'created_at',
        'updated_at',

    ];


}