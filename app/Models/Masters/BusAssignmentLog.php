<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class BusAssignmentLog extends Model
{
    protected $table = 'bus_assignment_logs';
    
    public $timestamps = false;
    
    protected $fillable = [
        'bus_assignment_id',
        'group_info_id',
        'field_name',
        'operation_type',
        'old_value',
        'new_value',
        'action_description',
        'user_id',
        'username',
        'created_at',
    ];
    
    protected $casts = [
        'bus_assignment_id' => 'integer',
        'group_info_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];
    
    public function busAssignment()
    {
        return $this->belongsTo(BusAssignment::class, 'bus_assignment_id', 'id');
    }
    
    public function groupInfo()
    {
        return $this->belongsTo(GroupInfo::class, 'group_info_id', 'id');
    }
    
    public static function log($busAssignmentId, $groupId, $fieldName, $operationType, $oldValue, $newValue, $actionDescription, $userId = null, $username = null)
    {
        return self::create([
            'bus_assignment_id' => $busAssignmentId,
            'group_info_id' => $groupId,
            'field_name' => $fieldName,
            'operation_type' => $operationType,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'action_description' => $actionDescription,
            'user_id' => $userId,
            'username' => $username,
            'created_at' => now(),
        ]);
    }
}