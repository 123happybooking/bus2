<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountPlController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 初始化总计
        $totalRevenue = $totalCogs = $totalExpenses = 0;
        $totalOIncome = $totalOExpenses = 0;
        $totalSOIncome = $totalSOExpenses = 0;

        // 【修复点】在这里初始化利润变量，防止 compact 报错
        $operatingIncome = 0;
        $ordinaryIncome = 0;
        $profitBeforeTax = 0;

        // 初始化为空集合，防止 View 报错
        $groupedData = collect();

        if ($startDate && $endDate) {
            try {
                // 1. 查询数据：按科目分组，计算借贷方总额
                $results = DB::table('account_journal_lines as ajl')
                    ->select([
                        'acc.category_id',
                        'acc.name as account_name',
                        // 强制修正逻辑：明确指定 ajl.side 和 ajl.amount
                        DB::raw('SUM(CASE WHEN ajl.side = 2 THEN ajl.amount ELSE 0 END) AS credit'),
                        DB::raw('SUM(CASE WHEN ajl.side = 1 THEN ajl.amount ELSE 0 END) AS debit')
                    ])
                    ->join('accounts as acc', 'ajl.account_id', '=', 'acc.id')
                    ->join('account_journal_entries as aje', 'ajl.journal_entry_id', '=', 'aje.id')
                    ->whereIn('acc.category_id', [7, 8, 9, 10, 11, 12, 13])
                    ->whereDate('aje.posting_date', '>=', $startDate)
                    ->whereDate('aje.posting_date', '<=', $endDate)
                    ->whereNotNull('ajl.account_id')
                    ->groupBy('acc.category_id', 'acc.id', 'acc.name')
                    ->get();

                // 2. 数据重组
                foreach ($results as $row) {
                    $netAmount = 0;

                    // 根据分类ID决定取借方还是贷方
                    if (in_array($row->category_id, [7, 10, 12])) { // 收入类: 取贷方
                        $netAmount = $row->credit;
                    } elseif (in_array($row->category_id, [8, 9, 11, 13])) { // 费用类: 取借方
                        $netAmount = $row->debit;
                    }

                    // 累加总计
                    switch ($row->category_id) {
                        case 7: $totalRevenue += $netAmount; break;
                        case 8: $totalCogs += $netAmount; break;
                        case 9: $totalExpenses += $netAmount; break;
                        case 10: $totalOIncome += $netAmount; break;
                        case 11: $totalOExpenses += $netAmount; break;
                        case 12: $totalSOIncome += $netAmount; break;
                        case 13: $totalSOExpenses += $netAmount; break;
                    }

                    // 推入集合，供 View 显示明细
                    $groupedData->push([
                        'category_id' => $row->category_id,
                        'account_name' => $row->account_name,
                        'debit' => $row->debit,
                        'credit' => $row->credit,
                        'amount' => $netAmount,
                    ]);
                }

                // 3. 计算利润 (只有在有数据时才重新计算，覆盖初始的 0)
                $operatingIncome = $totalRevenue - $totalCogs - $totalExpenses;
                $ordinaryIncome = $operatingIncome + $totalOIncome - $totalOExpenses;
                $profitBeforeTax = $ordinaryIncome + $totalSOIncome - $totalSOExpenses;

            } catch (\Exception $e) {
                Log::error('PL Report Error: ' . $e->getMessage());
            }
        }

        return view('masters.account-pl.index', compact(
            'startDate', 'endDate', 'groupedData',
            'totalRevenue', 'totalCogs', 'totalExpenses',
            'totalOIncome', 'totalOExpenses',
            'totalSOIncome', 'totalSOExpenses',
            'operatingIncome', 'ordinaryIncome', 'profitBeforeTax'
        ));
    }
}