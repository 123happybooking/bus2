<?php

namespace App\Http\Controllers\Masters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AccountBsController extends Controller
{
    public function index(Request $request)
    {
        // 1. 获取日期参数，默认为今天
        // 使用 ?? 确保即使输入为空，也不会报错
        $date = $request->input('date') ?? date('Y-m-d');

        // 2. 定义页面布局所需的分类顺序和名称
        $assetOrder = [1 => '流動資産', 3 => '固定資産', 5 => '繰延資産'];
        $liabilityOrder = [2 => '流動負債', 4 => '固定負債', 6 => '純資産'];

        // 3. 初始化嵌套数据结构
        $assets = [];
        $liabilities = [];

        // 初始化总计
        $totalAssets = 0;
        $totalLiabilities = 0;

        // 4. 查询所有科目余额
        $rawData = DB::select("
            SELECT 
                ac.id AS category_id,
                ac.name AS category_name,
                acc.id AS account_id,
                acc.name AS account_name,
                acc.code AS account_code,
                acc.category_id AS account_category_id,
                COALESCE(sub.balance, 0) AS balance
            FROM accounts acc
            LEFT JOIN account_categories ac ON acc.category_id = ac.id
            -- 核心修改：使用子查询预先计算余额
            LEFT JOIN (
                SELECT 
                    ajl.account_id,
                    SUM(CASE WHEN ajl.side = 1 THEN ajl.amount ELSE 0 END) - 
                    SUM(CASE WHEN ajl.side = 2 THEN ajl.amount ELSE 0 END) AS balance
                FROM account_journal_lines ajl
                INNER JOIN account_journal_entries aje 
                    ON ajl.journal_entry_id = aje.id
                WHERE aje.posting_date <= ?  -- 这里的日期过滤是强制性的
                GROUP BY ajl.account_id
            ) sub ON acc.id = sub.account_id
            WHERE ac.id IN (1, 2, 3, 4, 5, 6) 
            AND acc.is_active = 1
            ORDER BY ac.id, acc.code
        ", [$date]);

        // 5. 数据重组逻辑（核心）
        foreach ($rawData as $row) {
            $amount = (float)$row->balance;
            $absAmount = abs($amount);

            // --- 资产侧 (左侧) ---
            if (in_array($row->category_id, [1, 3, 5])) {
                $categoryName = $assetOrder[$row->category_id] ?? $row->category_name;

                if (!isset($assets[$categoryName])) {
                    $assets[$categoryName] = [
                        'total' => 0,
                        'accounts' => []
                    ];
                }

                if ($amount >= 0) {
                    $assets[$categoryName]['accounts'][] = [
                        'name' => $row->account_name,
                        'code' => $row->account_code,
                        'amount' => $absAmount,
                        'is_negative' => false
                    ];
                    $assets[$categoryName]['total'] += $absAmount;
                    $totalAssets += $absAmount;
                } else {
                    $assets[$categoryName]['accounts'][] = [
                        'name' => '【調整】' . $row->account_name,
                        'code' => $row->account_code,
                        'amount' => $absAmount,
                        'is_negative' => true
                    ];
                }
            }

            // --- 负债与权益侧 (右侧) ---
            elseif (in_array($row->category_id, [2, 4, 6])) {
                $categoryName = $liabilityOrder[$row->category_id] ?? $row->category_name;

                if (!isset($liabilities[$categoryName])) {
                    $liabilities[$categoryName] = [
                        'total' => 0,
                        'accounts' => []
                    ];
                }

                if ($amount <= 0) {
                    $liabilities[$categoryName]['accounts'][] = [
                        'name' => $row->account_name,
                        'code' => $row->account_code,
                        'amount' => $absAmount,
                        'is_negative' => false
                    ];
                    $liabilities[$categoryName]['total'] += $absAmount;
                    $totalLiabilities += $absAmount;
                } else {
                    $liabilities[$categoryName]['accounts'][] = [
                        'name' => '【調整】' . $row->account_name,
                        'code' => $row->account_code,
                        'amount' => $absAmount,
                        'is_negative' => true
                    ];
                    $liabilities[$categoryName]['total'] += $absAmount;//默认没有
                    $totalLiabilities += $absAmount;//默认没有
                }
            }
        }

        // 6. 传递数据到视图
        // 注意：这里传递的是 'date'，而不是 'current'
        return view('masters.account-bs.index', compact(
            'date', 
            'assets', 
            'liabilities', 
            'totalAssets', 
            'totalLiabilities',
            'assetOrder',
            'liabilityOrder'
        ));
    }
}