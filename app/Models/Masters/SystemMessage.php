<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class SystemMessage extends Model
{
    protected $table = 'system_messages';

    protected $fillable = [
        'staff_id',
        'content',
        'images',
        'is_pinned',
    ];

    protected $casts = [
        'images' => 'array',
        'is_pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}