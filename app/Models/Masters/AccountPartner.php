<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountPartner extends Model
{

    protected $table = 'account_partners';
    /**
     * 可批量赋值的属性
     * 严格对应图片中的字段，未添加额外字段
     */
    protected $fillable = [
        'name',
        'category',
        'company_name',
        'address',
        'registration_number',
        'phone',
        'person_in_charge',
    ];

    /**
     * 需要转换为原生类型的属性
     */


    protected $dates = [
        'created_at',
        'updated_at',

    ];
}