<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    protected $table = 'account_categories';

    // 允许批量赋值的字段
    protected $fillable = [
        'name',           // 区分_Name (例: 流動資産)
        'mark',           // 貸借_Mark (例: 借, 貸)
        'type',
        'level',          // 层级 (例: 1, 2)
    ];


    protected $dates = [
        'created_at',
        'updated_at',

    ];


}