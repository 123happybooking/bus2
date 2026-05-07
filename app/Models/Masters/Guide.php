<?php


namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    protected $fillable = [
        'branch_id', 'guide_code', 'name', 
        'name_kana', 'phone_number', 'employment_type', 
        'is_active', 'remarks', 'created_at', 'updated_at', 
        'email', 'display_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}