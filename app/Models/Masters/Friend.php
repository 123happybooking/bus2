<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    protected $table = 'friends';
    
    protected $fillable = [
        'friend_company_id',
        'status',
        'is_sender',
    ];
    
    protected $casts = [
        'is_sender' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => '申請中',
            self::STATUS_ACCEPTED => '承認済',
            self::STATUS_REJECTED => '拒否',
        ];
    }
    
    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }
    
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }
    
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }
    
    public function isSender()
    {
        return $this->is_sender == 1;
    }
    
    public function isReceiver()
    {
        return $this->is_sender == 0;
    }
    
    public function accept()
    {
        $this->status = self::STATUS_ACCEPTED;
        return $this->save();
    }
    
    public function reject()
    {
        $this->status = self::STATUS_REJECTED;
        return $this->save();
    }
    
    public function getFriendCompanyNameAttribute()
    {
        $company = \App\Models\Masters\User::on('mysql')->find($this->friend_company_id);
        return $company ? ($company->user_company_name ?: $company->name) : '不明';
    }
}