<?php


namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'branch_code', 'branch_name', 'postal_code', 
        'address', 'phone_number', 'fax_number', 
        'manager_name', 'display_order'
    ];
}