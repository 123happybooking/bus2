<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Masters\Account;
use App\Models\Masters\AccountCategory;
use App\Models\Masters\AccountJournalEntry;
use App\Models\Masters\AccountJournalLine;
use App\Models\Masters\AccountMonthDetail;
use App\Models\Masters\AccountPeriod;

class AccountCashController extends Controller
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

        if ($yearmonth == "13" ){
            $startDate = $period->start;
            $endDate = $period->end;
        }else{
            $startDate = date('Y-m-d', strtotime("first day of $yearmonth"));
            $endDate = date('Y-m-d', strtotime("last day of $yearmonth"));
        }

        $account = Account::where("code", 111)->first();
        $mark = $account->category->mark  == "借" ? 1 : 2;
        $datas = $this->makeData($startDate, $endDate, $account);
        $datas['mark'] = $mark;

        return view('masters.account-cash.index', compact(
            'datas','periods','period_id','yearmonth','months'
        ));
    }

    function makeData($startDate, $endDate, $account)
    { 
        
        $datas = [];
        $datas['account_name'] = $account->name ?? '';
        $datas['start_date'] = $startDate;
        $datas['end_date'] = $endDate;
        $datas['opening_balance'] = 0;
        if($startDate){
            $yearmonth = explode("-",  $startDate);

            $start_data = AccountMonthDetail::where('account_id', $account->id)->where('year',$yearmonth[0])->where('month',$yearmonth[1])->first();
            $datas['opening_balance'] = $start_data->money_start ?? 0;
        }

        $query = AccountJournalEntry::query();
        if ($startDate) {
            $query->where('posting_date','>=',$startDate);
        }
        if ($endDate) {
            $query->where('posting_date','<=',$endDate);
        }
        $entry_id = $query->orderBy("posting_date",'asc')->pluck('id')->toArray();
        if (empty($entry_id)) {
            // 如果 ID 列表为空，直接返回空集合或者处理异常
            $lines = collect(); 
            // 或者抛出异常
            // throw new \Exception('Entry ID list is empty');
        } else {
            $lines = AccountJournalLine::whereIn('journal_entry_id', $entry_id)
                ->where('account_id', $account->id)
                ->orderByRaw('FIELD(journal_entry_id, ' . implode(',', $entry_id) . ')')
                ->get();
        }
        
        foreach ($lines as $line) { 
            $account_name = "";
            $sub_account_name = "";
            $tax_category = "";
            $otherlineCount = AccountJournalLine::where('journal_entry_id', $line->journal_entry_id)->where('id','!=',$line->id)->count();
            if ($otherlineCount == 1) {
                $otherline = AccountJournalLine::where('journal_entry_id', $line->journal_entry_id)->where('id','!=',$line->id)->first();
                $account_name = $otherline->account->name ?? '';
                $sub_account_name = $otherline->subAccount->name ?? '';
                $tax_category = $otherline->taxType->name ?? '';
                
            }
            if($line->side ==2 ){
                $jie_money = "";
                $dai_money = $line->amount;

            }else{
                $jie_money = $line->amount;
                $dai_money = "";
            }
            $datas['account_name'] = $account->name ?? '';
            $datas['rows'][] = [
                'date' => $line->entry->posting_date->format('Y-m-d'),
                'account_name' => $account_name,
                'sub_account_name' => $sub_account_name,
                'tax_category' => $tax_category,
                'jie_money' => $jie_money,
                'dai_money' => $dai_money,
            ];
        }
        return $datas;
    }
}