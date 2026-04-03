<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class AccountJournalEntry extends Model
{
    // 指定对应的数据表名
    protected $table = 'account_journal_entries';

    // 允许批量赋值的字段
    protected $fillable = [
        'posting_date',
        'description',
        'department_id',
        'source_type',
        'source_id',
        'created_by',
        'updated_by',
        'remark'
        // created_at 和 updated_at 通常由框架自动管理，不需要在 fillable 中列出，除非手动赋值
    ];

    /**
     * 类型转换
     * 将日期字段自动转换为 Carbon 实例
     * 将整数字段转换为 integer
     */
    protected $casts = [
        'posting_date' => 'date',
        'department_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 如果表不使用标准的 created_at/updated_at 字段，可在此关闭
    // public $timestamps = true; 

    /**
     * 关联：一个传票包含多个明细行 (One to Many)
     */
    public function lines()
    {
        return $this->hasMany(AccountJournalLine::class, 'journal_entry_id');
    }

    /**
     * 关联：所属部门 (Belongs To)
     * 假设部门模型在 App\Models\Masters\Department
     */
    public function department()
    {
        return $this->belongsTo(AccountDepartment::class, 'department_id');
    }
    
    /**
     * 关联：创建者 (Belongs To)
     * 假设用户模型在 App\Models\User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * [访问器] 格式化借方详情 HTML
     * 返回格式：科目名 (辅助/取引先/税) - 金额 <br> ...
     */
    public function getDebitDetailsHtmlAttribute()
    {
        return $this->formatLinesHtml(1);
    }

    /**
     * [访问器] 格式化贷方详情 HTML
     */
    public function getCreditDetailsHtmlAttribute()
    {
        return $this->formatLinesHtml(2);
    }

    private function formatLinesHtml(string $side)
    {
        $lines = $this->lines->where('side', $side);
        
        if ($lines->isEmpty()) {
            return '<span class="text-muted">-</span>';
        }

        $html = '';
        foreach ($lines as $line) {
            // 构建科目显示名
            $accName = $line->account ? "{$line->account->name}" : '未设定';
            
            // 构建辅助信息 (辅助科目 / 取引先 / 税)
            $subs = [];
            if ($line->sub_account_id && $line->subAccount) {
                $subs[] = $line->subAccount->name; 
            } elseif ($line->account_sub_name) {
                $subs[] = $line->account_sub_name; 
            }

            if ($line->partner_id && $line->partner) {
                $subs[] = $line->partner->name;
            } elseif ($line->partner_name) {
                $subs[] = $line->partner_name; 
            }

            if ($line->tax_type_id && $line->taxType) {
                $subs[] = $line->taxType->name;
            }

            $subText = !empty($subs) ? ' <small class="text-muted">(' . implode('/', $subs) . ')</small>' : '';
            
            // 金额格式化
            $formattedAmount = number_format($line->amount);

            // 拼接一行 HTML：内容与金额在同一行
            // 使用 flexbox 或 float 来实现左右布局
            $html .= "<div class='d-flex justify-content-between align-items-start' style='font-size:0.75rem; line-height:1.3;'>";
            
            // 左侧：科目名 + 辅助信息
            $html .= "<div class='fw-bold'>{$accName}{$subText}</div>";
            
            // 右侧：金额 (使用 text-primary 和 fw-bold 可选)
            $html .= "<div class='text-primary fw-bold'>{$formattedAmount}</div>";
            
            $html .= "</div>";
        }

        return $html;
    }
}

