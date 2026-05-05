<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Masters\AccountPeriod;

class AccountSumController extends Controller
{
public function index(Request $request)
{
    $periods = AccountPeriod::orderBy('created_at', 'desc')->get();
    $period = AccountPeriod::orderBy('created_at', 'desc')->first();
    if ($request->period_id) {
        $period = AccountPeriod::find($request->period_id);
    }

    $period_id = $period->id ?? 0;
    $period_start = $period->start;
    $start_year = explode('-', $period_start)[0];
    $moren_month = explode('-', $period_start)[1];

    $yearmonth = $request->yearmonth;

    $months = [];
    for ($i = 0; $i < 12; $i++) {
        $current_month = $moren_month + $i;
        $year = $start_year + floor(($current_month - 1) / 12);
        $month_num = (($current_month - 1) % 12) + 1;
        $key = sprintf('%02d', $month_num);
        $value = sprintf('%d-%02d', $year, $month_num);
        $months[$key] = $value;
    }
    $months["全期"] = "13";

    if ($yearmonth == "13") {
        $startDate = date('Y-m', strtotime($period->start));
        $endDate = date('Y-m', strtotime($period->end));
    } else {
        $startDate = date('Y-m', strtotime("first day of $yearmonth"));
        $endDate = date('Y-m', strtotime("last day of $yearmonth"));
    }

    $start = $startDate; // '2025-10'
    $end   = $endDate;   // '2025-12'

    // 简单校验格式，防止 SQL 注入 (确保格式为 YYYY-MM)
    if (!preg_match('/^\d{4}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}$/', $end)) {
        abort(400, '日期格式错误');
    }

    $data = DB::table('accounts as acc')
        // 1. 关联余额表（用于获取期末余额）
        ->leftJoin('account_month_details as amd_end', function ($join) use ($end) {
            $join->on('acc.id', '=', 'amd_end.account_id')
                ->where('amd_end.year_month', '=', $end)
                ->whereNull('amd_end.deleted_at')
                ->whereNull('amd_end.sub_account_id');
        })
        // 2. 关联明细表（用于计算区间合计）
        ->leftJoin('account_month_details as amd_sum', function ($join) use ($start, $end) {
            $join->on('acc.id', '=', 'amd_sum.account_id')
                ->whereBetween('amd_sum.year_month', [$start, $end])
                ->whereNull('amd_sum.deleted_at')
                ->whereNull('amd_sum.sub_account_id');
        })
        ->select([
            'acc.id',
            'acc.code',
            'acc.name',
            DB::raw('COALESCE(amd_end.money_end, 0) as ending_balance'),
            DB::raw('COALESCE(SUM(amd_sum.money_jie), 0) as total_jie'),
            DB::raw('COALESCE(SUM(amd_sum.money_dai), 0) as total_dai')
        ])

        ->groupBy('acc.id', 'acc.code', 'acc.name', 'amd_end.money_end')
        ->orderBy('acc.code')
        ->get();

    return view('masters.account-sums.index', compact(
        'data', 'periods', 'months', 'period_id', 'yearmonth'
    ));
}
}