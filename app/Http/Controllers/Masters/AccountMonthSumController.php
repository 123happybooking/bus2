<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountMonthSum;
use App\Models\Masters\AccountMonthDetail;
use App\Models\Masters\AccountSub;
use App\Models\Masters\AccountJournalLine;
use App\Models\Masters\Account;
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

        $id = AccountMonthSum::orderBy('year', 'desc')->orderBy('month', 'desc')->first()->id ?? 0;

        return view('masters.account-month-sums.index', compact('sums','id'));
    }


    public function create()
    {
        $res = $this->makeData();
        if (empty($res['year_month'])) {
            return redirect()->route('masters.account-month-sums.index')
            ->with([
                'success' => '数据已是最新！',
                'alert-type' => 'success'
            ]);
        }

        DB::beginTransaction();
        
        try {


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
         
            // 批量插入汇总表
            if (!empty($sumData)) {
                AccountMonthSum::insert($sumData);
            }

            // --- 3. 批量存入 account_month_details (明细表) ---
            $detailData = [];
            
            foreach ($res['account_rows'] as $accountRows) {
                foreach ($accountRows as $rowData) {
                    $dateParts = explode('-', $rowData['month']);
                    
                    $detailData[] = [
                        'account_id'   => $rowData['account_id'],
                        'year'         => $dateParts[0],
                        'month'        => $dateParts[1],
                        'year_month' => $dateParts[0]. '-' .$dateParts[1],
                        'money_start'  => $rowData['opening'],
                        'money_end'    => $rowData['closing'],
                        'money_jie'    => $rowData['jie_money'],
                        'money_dai'    => $rowData['dai_money'],
                        'sn'         => (string) Str::uuid(), 
                        'created_at' => $now,                 
                        'updated_at' => $now,                
                    ];
                }
            }

            if (!empty($detailData)) {
                AccountMonthDetail::insert($detailData);
            }

            $subdetailData = [];
            
            foreach ($res['sub_account_rows'] as $subaccountRows) {
                foreach ($subaccountRows as $rowData) {
                    $dateParts = explode('-', $rowData['month']);
                    $subAccount = AccountSub::find($rowData['sub_account_id']);
                    
                    $subdetailData[] = [
                        'account_id'   => $subAccount->account_id ?? 0,
                        'sub_account_id'   => $rowData['sub_account_id'],
                        'year'         => $dateParts[0],
                        'month'        => $dateParts[1],
                        'year_month' => $dateParts[0]. '-' .$dateParts[1],
                        'money_start'  => $rowData['opening'],
                        'money_end'    => $rowData['closing'],
                        'money_jie'    => $rowData['jie_money'],
                        'money_dai'    => $rowData['dai_money'],
                        'sn'         => (string) Str::uuid(), 
                        'created_at' => $now,                 
                        'updated_at' => $now,                
                    ];
                }
            }

            if (!empty($subdetailData)) {
                AccountMonthDetail::insert($subdetailData);
            }

            DB::commit();
            return redirect()->route('masters.account-month-sums.index')->with('success', '数据生成成功！');

        } catch (\Exception $e) {
            \Log::error($e);
            DB::rollBack();
            return back()->withErrors('数据生成失败：' . $e->getMessage());
        }
    }

    function makeData()
    {
        $list = AccountMonthSum::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        // 确定 $endDate (现实时间上个月最后一天)
        $endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        // 确定 $startDate
        if (empty($list)) {
            $startDate = '2024-01-01';
        } else {
            $lastDate = Carbon::createFromDate($list->year, $list->month, 1);
            $nextMonthDate = $lastDate->addMonth();
            $startDate = $nextMonthDate->format('Y-m-d');
        }

        $datas = [];
        $tempYearMonths = [];

        // 1. 获取主科目（account_id）的汇总数据
        $account_ids = Account::where('is_active', 1)->pluck('id')->toArray();
        $accountResult = $this->getAccountSummary($account_ids, $startDate, $endDate, 'account_id');
        
        // 2. 获取子科目（sub_account_id）的汇总数据
        $sub_account_ids = AccountJournalLine::distinct()->pluck('sub_account_id')->filter()->toArray();
        $subAccountResult = $this->getAccountSummary($sub_account_ids, $startDate, $endDate, 'sub_account_id');

        // 3. 组装返回数据
        $datas['account_rows'] = $accountResult['rows'];
        $datas['sub_account_rows'] = $subAccountResult['rows'];
        
        // 合并两个结果中的月份信息并去重
        $datas['year_month'] = array_values(array_unique(array_merge(
            $accountResult['year_month'], 
            $subAccountResult['year_month']
        ), SORT_REGULAR));

        return $datas;
    }


        /**
     * 提取的公共汇总逻辑方法
     */
    private function getAccountSummary($ids, $startDate, $endDate, $field)
    {
        // 1. 提前判空：如果 ID 数组为空，直接返回空结构，防止后续报错
        if (empty($ids)) {
            return [
                'rows' => [],
                'year_month' => []
            ];
        }

        $rows = [];
        $tempYearMonths = [];

        // 2. 改用 foreach 遍历，彻底规避 $ids[$i] 的索引越界风险
        foreach ($ids as $id) {
            // --- 1. 计算期初余额 ---
            $openingBalance = AccountJournalLine::join('account_journal_entries', 'account_journal_lines.journal_entry_id', '=', 'account_journal_entries.id')
                ->where('account_journal_lines.' . $field, $id) // 直接使用 $id
                ->where('account_journal_entries.posting_date', '<', $startDate)
                ->selectRaw('
                    SUM(CASE WHEN account_journal_lines.side = 1 THEN account_journal_lines.amount ELSE 0 END) - 
                    SUM(CASE WHEN account_journal_lines.side = 2 THEN account_journal_lines.amount ELSE 0 END) as balance
                ')
                ->value('balance') ?? 0;

            // --- 2. 获取每月汇总数据 ---
            $monthlyStats = AccountJournalLine::join('account_journal_entries', 'account_journal_lines.journal_entry_id', '=', 'account_journal_entries.id')
                ->where('account_journal_lines.' . $field, $id) // 直接使用 $id
                ->whereBetween('account_journal_entries.posting_date', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(account_journal_entries.posting_date, "%Y-%m") as month_key')
                ->selectRaw('SUM(CASE WHEN account_journal_lines.side = 1 THEN account_journal_lines.amount ELSE 0 END) as total_jie')
                ->selectRaw('SUM(CASE WHEN account_journal_lines.side = 2 THEN account_journal_lines.amount ELSE 0 END) as total_dai')
                ->groupBy('month_key')
                ->orderBy('month_key', 'asc')
                ->get();

            $accountRows = [];
            $currentBalance = $openingBalance;

            foreach ($monthlyStats as $k => $stat) {
                $monthOpening = $currentBalance;
                $currentBalance = $currentBalance + $stat->total_jie - $stat->total_dai;
                $monthClosing = $currentBalance;

                $accountRows[$k] = [
                    $field => $id, // 直接使用 $id
                    'month'        => $stat->month_key,
                    'jie_money'    => $stat->total_jie,
                    'dai_money'    => $stat->total_dai,
                    'opening'      => $monthOpening,
                    'closing'      => $monthClosing,
                ];

                $y = substr($stat->month_key, 0, 4);
                $m = substr($stat->month_key, 5, 2);
                
                $tempYearMonths[$stat->month_key] = [
                    'year'  => $y,
                    'month' => $m
                ];
            }
            $rows[] = $accountRows;
        }

        return [
            'rows' => $rows,
            'year_month' => array_values($tempYearMonths)
        ];
    }

    /**
     * 显示详情
     */
    public function show($id)
    {
        $sum = AccountMonthSum::findOrFail($id);
        $detail = AccountMonthDetail::where('year', $sum->year)->where('month', $sum->month)->whereNull('sub_account_id')->get();
        $subdetail = AccountMonthDetail::where('year', $sum->year)->where('month', $sum->month)->where('sub_account_id','>',0)->get();
        return view('masters.account-month-sums.show', compact('sum','detail','subdetail'));
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