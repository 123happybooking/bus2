<?php
namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'option';
    
    protected $fillable = [
        'name',
        'category',
        'description',
        'is_active',
        'display_order',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];
    
    public $timestamps = true;
}