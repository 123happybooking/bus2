<?php
namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $table = 'import_histories';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'file_name',
        'file_path',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'imported_data',
        'error_log',
        'imported_by',
        'imported_by_name',
        'imported_at',
        'updated_at',
    ];

    protected $casts = [
        'imported_data' => 'array',
        'error_log' => 'array',
        'imported_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}