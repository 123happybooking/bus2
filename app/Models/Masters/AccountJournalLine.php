<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountJournalLine extends Model
{
    // 指定对应的数据表名
    protected $table = 'account_journal_lines';

    // 允许批量赋值的字段
    protected $fillable = [
        'journal_entry_id',
        'side',
        'account_id',
        'sub_account_id',
        'partner_id',
        'amount',
        'tax_type_id',
        'remark',
    ];

    /**
     * 类型转换
     * 将金额转换为浮点数 (或保留字符串以防精度丢失，视业务需求而定，这里用 decimal 对应的 string 或 float)
     * 将日期和整数进行转换
     */
    protected $casts = [
        'journal_entry_id' => 'integer',
        'account_id' => 'integer',
        'sub_account_id' => 'integer',
        'partner_id' => 'integer',
        'amount' => 'decimal:2', // 保留2位小数
        'tax_type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * 关联：所属传票 (Belongs To)
     */
    public function entry()
    {
        return $this->belongsTo(AccountJournalEntry::class, 'journal_entry_id');
    }

    /**
     * 关联：会计科目 (Belongs To)
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * 关联：辅助科目 (Belongs To)
     * 假设有 AccountSub 模型
     */
    public function subAccount()
    {
        return $this->belongsTo(AccountSub::class, 'sub_account_id');
    }

    /**
     * 关联：交易伙伴 (Belongs To)
     * 假设有 AccountPartner 模型
     */
    public function partner()
    {
        return $this->belongsTo(AccountPartner::class, 'partner_id');
    }
    
    /**
     * 关联：税区分 (Belongs To)
     */
    public function taxType()
    {
        return $this->belongsTo(AccountTax::class, 'tax_type_id');
    }
}