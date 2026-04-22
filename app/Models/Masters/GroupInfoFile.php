<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupInfoFile extends Model
{
    use HasFactory;

    protected $table = 'group_info_files';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'group_info_id',
        'bus_assignment_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'file_extension',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function groupInfo(): BelongsTo
    {
        return $this->belongsTo(GroupInfo::class, 'group_info_id', 'id');
    }
    
    public function busAssignment()
    {
        return $this->belongsTo(BusAssignment::class);
    }

    public function getSizeForHumansAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        return round($bytes / 1024, 2) . ' KB';
    }

    public function getIconAttribute(): string
    {
        $ext = strtolower($this->file_extension);
        $icons = [
            'pdf' => 'bi-file-pdf',
            'doc' => 'bi-file-word',
            'docx' => 'bi-file-word',
            'xls' => 'bi-file-excel',
            'xlsx' => 'bi-file-excel',
            'zip' => 'bi-file-zip',
            'rar' => 'bi-file-zip',
            'jpg' => 'bi-file-image',
            'jpeg' => 'bi-file-image',
            'png' => 'bi-file-image',
            'gif' => 'bi-file-image',
            'txt' => 'bi-file-text',
        ];
        return $icons[$ext] ?? 'bi-file-earmark';
    }
}