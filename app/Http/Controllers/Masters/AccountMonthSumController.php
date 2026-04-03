<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountMonthSum;
use App\Models\Masters\AccountMonthDetail;
use App\Models\Masters\AccountJournalEntry;
use App\Models\Masters\AccountJournalLine;
use Illuminate\Support\Str; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountMonthSumController extends Controller
{
    /**
     * 列表展示
     */
    public function index(Request $request)
    {
        $query = AccountMonthSum::query();

        // 搜索：年份或月份
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('year', 'like', "%{$search}%")
                  ->orWhere('month', 'like', "%{$search}%");
            });
        }

        $perPage = 20;
        if ($request->filled('per_page') && in_array((int)$request->per_page, [20, 30, 50])) {
            $perPage = (int)$request->per_page;
        }

        $sums = $query->orderBy('year', 'desc')->orderBy('month', 'desc')->paginate($perPage);
        $sums->appends($request->except('page'));

        return view('masters.account-month-sums.index', compact('sums'));
    }

    /**
     * 显示创建表单
     * 只有年份和月份
     */
    public function create()
    {
        $res = $this->makeData();

        DB::beginTransaction();
        
        try {
            if (empty($res['year_month'])) {
                throw new \Exception('没有需要保存的数据');
            }

            $now = now();
            // --- 2. 批量存入 account_month_sums (汇总表) ---
            // 需求：把所有的时间都存起来
            $sumData = [];
            foreach ($res['year_month'] as $ym) {
                $sumData[] = [
                    'year'       => $ym['year'],
                    'month'      => $ym['month'],
                    'created_by' => session('staff_name'),
                    'sn'         => (string) Str::uuid(), // 【修改点】手动生成 UUID，替代 Model 的 boot 逻辑
                    'created_at' => $now,                 // 【修改点】必须手动传入
                    'updated_at' => $now,                 // 【修改点】必须手动传入
                ];
            }
           // dump($sumData);exit;
            // 批量插入汇总表
            if (!empty($sumData)) {
                AccountMonthSum::insert($sumData);
            }

            // --- 3. 批量存入 account_month_details (明细表) ---
            $detailData = [];
            
            foreach ($res['rows'] as $accountRows) {
                foreach ($accountRows as $rowData) {
                    $dateParts = explode('-', $rowData['month']);
                    
                    $detailData[] = [
                        'account_id'   => $rowData['account_id'],
                        'year'         => $dateParts[0],
                        'month'        => $dateParts[1],
                        'money_start'  => $rowData['opening'],
                        'money_end'    => $rowData['closing'],
                        'money_jie'    => $rowData['jie_money'],
                        'money_dai'    => $rowData['dai_money'],
                        'sn'         => (string) Str::uuid(), // 【修改点】手动生成 UUID，替代 Model 的 boot 逻辑
                        'created_at' => $now,                 // 【修改点】必须手动传入
                        'updated_at' => $now,                 // 【修改点】必须手动传入
                    ];
                }
            }

            if (!empty($detailData)) {
                AccountMonthDetail::insert($detailData);
            }

            DB::commit();
            return redirect()->route('masters.account-month-sums.index')->with('success', '数据生成成功！');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('数据生成失败：' . $e->getMessage());
        }
    }

    function makeData()
    { 

        $list = AccountMonthSum::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        // 2. 确定 $endDate (现实时间上个月最后一天)
        // 无论哪种情况，结束时间都是固定的：上个月月底
        $endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        // 3. 确定 $startDate
        if (empty($list)) {
            // --- 情况 A: 数据库为空，从 2025-01-01 开始 ---
            $startDate = '2025-01-01';
        } else {
            // --- 情况 B: 数据库有值，从该数据的下个月1号开始 ---
            
            // 创建一个 Carbon 实例，基于数据库中的年份和月份 (日期设为1号)
            $lastDate = Carbon::createFromDate($list->year, $list->month, 1);
            
            // 加一个月
            $nextMonthDate = $lastDate->addMonth();
            
            // 格式化为 Y-m-d (即下个月1号)
            $startDate = $nextMonthDate->format('Y-m-d');
        }

       // dump($startDate."....".$endDate);exit;
        $datas=[];
        $account_ids = [121,122,123,131];
        $tempYearMonths = []; 

        for ($i = 0; $i < count($account_ids); $i++) {
            // --- 1. 计算期初余额 (Opening Balance) ---
            // 逻辑：查询 开始日期之前 的所有借贷差额
            // 使用 withSum 或者直接查询聚合，这里为了效率直接用 DB 查询
            $openingBalance = AccountJournalLine::join('account_journal_entries', 'account_journal_lines.journal_entry_id', '=', 'account_journal_entries.id')
                ->where('account_journal_lines.account_id', $account_ids[$i])
                ->where('account_journal_entries.posting_date', '<', $startDate) // 注意是小于
                ->selectRaw('
                    SUM(CASE WHEN account_journal_lines.side = 1 THEN account_journal_lines.amount ELSE 0 END) - 
                    SUM(CASE WHEN account_journal_lines.side = 2 THEN account_journal_lines.amount ELSE 0 END) as balance
                ')
                ->value('balance') ?? 0;


            // --- 2. 获取每月汇总数据 (Monthly Summary) ---
            // 逻辑：按月份分组，计算每月的借方总和、贷方总和
            $monthlyStats = AccountJournalLine::join('account_journal_entries', 'account_journal_lines.journal_entry_id', '=', 'account_journal_entries.id')
                ->where('account_journal_lines.account_id', $account_ids[$i])
                ->whereBetween('account_journal_entries.posting_date', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(account_journal_entries.posting_date, "%Y-%m") as month_key')
                ->selectRaw('SUM(CASE WHEN account_journal_lines.side = 1 THEN account_journal_lines.amount ELSE 0 END) as total_jie')
                ->selectRaw('SUM(CASE WHEN account_journal_lines.side = 2 THEN account_journal_lines.amount ELSE 0 END) as total_dai')
                ->groupBy('month_key')
                ->orderBy('month_key', 'asc')
                ->get();

            $rows = [];
            $currentBalance = $openingBalance;

            foreach ($monthlyStats as $k=>$stat) {
                $monthOpening = $currentBalance;
                // 本月期末 = 期初 + 本月借 - 本月贷
                $currentBalance = $currentBalance + $stat->total_jie - $stat->total_dai;
                $monthClosing = $currentBalance;

                $rows[$k] = [
                    'account_id'   => $account_ids[$i],
                    'month'        => $stat->month_key,
                    'jie_money'    => $stat->total_jie, // 本月借方合计
                    'dai_money'    => $stat->total_dai, // 本月贷方合计
                    'opening'      => $monthOpening,   // 月初金额
                    'closing'      => $monthClosing,   // 月底金额
                ];
                $y = substr($stat->month_key, 0, 4); // 截取年份 2025
                $m = substr($stat->month_key, 5, 2); // 截取月份 04
                
                // 存入数组，键名为 "2025-04"，这样相同的月份会自动覆盖，不会重复
                $tempYearMonths[$stat->month_key] = [
                    'year'  => $y,
                    'month' => $m
                ];
            }
            $datas['rows'][] = $rows;
        }
        $datas['year_month'] = array_values($tempYearMonths);
        return $datas;
    }


    /**
     * 显示详情
     */
    public function show($id)
    {
        $sum = AccountMonthSum::findOrFail($id);
        $detail = AccountMonthDetail::where('year', $sum->year)->where('month', $sum->month)->get();
        
        return view('masters.account-month-sums.show', compact('sum','detail'));
    }

    /**
     * 显示编辑表单
     * 注意：如果明细是自动计算的，通常不需要编辑明细，或者编辑后需重新计算
     */
    public function edit($id)
    {
        $sum = AccountMonthSum::with('details')->findOrFail($id);
        return view('masters.account-month-sums.edit', compact('sum'));
    }


    /**
     * 删除数据
     */
    public function destroy($id)
    {
        $sum = AccountMonthSum::findOrFail($id);

        try {
            DB::transaction(function () use ($sum) {
                $sum->details()->delete(); // 删除关联明细
                $sum->delete();            // 删除主表
            });

            return redirect()->route('masters.account-month-sums.index')
                ->with('success', '删除成功！');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '删除失败：' . $e->getMessage());
        }
    }
}