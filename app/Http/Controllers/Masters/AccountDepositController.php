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
use App\Models\Masters\AccountPartner;
use App\Models\Masters\AccountTax;
use App\Models\Masters\AccountSub;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use App\Models\Masters\UserCompanyInfo;
use App\Models\Masters\AccountConfig;

class AccountDepositController extends Controller
{
    public function index(Request $request)
    {
        $accountConfig = AccountConfig::first();
        $account = Account::where("id", $accountConfig->account_deposit_id)->first();
        $subAccounts = AccountSub::where("account_id", $account->id)->get();
        $sub_account_id = $request->sub_account_id ?? 0;

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


        $mark = $account->category->mark  == "借" ? 1 : 2;

        if ($sub_account_id) {
            $datas = $this->makeSubData($startDate, $endDate, $account,$sub_account_id);
        }else{
            $datas = $this->makeData($startDate, $endDate, $account);
        }
        
        $datas['mark'] = $mark;


        $accounts = Account::where('is_active', 1)->get();
        $partners = AccountPartner::get(); // 取引先
        $taxes = AccountTax::get();       // 税区分
        return view('masters.account-deposit.index', compact(
            'datas','periods','period_id','yearmonth','months','subAccounts','sub_account_id','accounts','partners','taxes'
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
                'id' => $line->id,
                'account_name' => $account_name,
                'sub_account_name' => $sub_account_name,
                'source_id' => $line->entry->source_id,
                'remark' => $line->entry->remark,
                'tax_category' => $tax_category,
                'jie_money' => $jie_money,
                'dai_money' => $dai_money,

                'curr_sub_account_name' => $line->subAccount->name ?? '',
                'curr_tax_category' => $line->taxType->name ?? '',

            ];
        }
        return $datas;
    }

    function makeSubData($startDate, $endDate, $account,$sub_account_id)
    { 
        $datas = [];
        $datas['account_name'] = $account->name ?? '';
        $datas['start_date'] = $startDate;
        $datas['end_date'] = $endDate;
        $datas['opening_balance'] = 0;
        if($startDate){
            $yearmonth = explode("-",  $startDate);

            $start_data = AccountMonthDetail::where('account_id', $account->id)->where('sub_account_id',$sub_account_id)->where('year',$yearmonth[0])->where('month',$yearmonth[1])->first();
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
                ->where('sub_account_id',$sub_account_id)
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
                'id' => $line->id,
                'account_name' => $account_name,
                'sub_account_name' => $sub_account_name,
                'source_id' => $line->entry->source_id,
                'remark' => $line->entry->remark,
                'tax_category' => $tax_category,
                'jie_money' => $jie_money,
                'dai_money' => $dai_money,

                'curr_sub_account_name' => $line->subAccount->name ?? '',
                'curr_tax_category' => $line->taxType->name ?? '',

            ];
        }
        return $datas;
    }

    public function destroy($id)
    {

        try {
            $line = AccountJournalLine::findOrFail($id);
            AccountJournalLine::where('journal_entry_id', $line->journal_entry_id)->delete();

            return redirect(route('masters.account-deposit.index'))
                ->with([
                    'success' => '仕訳伝票を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            Log::error('Journal line delete error: ' . $e->getMessage());
            return redirect()
                ->route('masters.account-deposit.index')
                ->with([
                    'error' => '削除に失敗しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }


    public function storeJournal(Request $request){ 
        DB::beginTransaction();
        try {
            $accountConfig = AccountConfig::first();
            $depositAccount = Account::where("id", $accountConfig->account_deposit_id)->first();

            $journal = new AccountJournalEntry();
            // A. 创建主表
            $entry = AccountJournalEntry::create([
                'posting_date'  => $request->transaction_date,
                'department_id' => 0,
                'source_type'   => 0,
                'remark'   => $request->description,
                'source_id'     => $journal->generateAccountNumber(),
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
            ]);


            $isExpense = ($request->transaction_type == "expense");

            $linesData[] = [
                'journal_entry_id' => $entry->id,
                'side'             => $isExpense ? 1 : 2, // 动态决定方向
                'account_id'       => $request->account_id,
                'sub_account_id'   => $request->sub_subject_id  ?? 0,
                'partner_id'       => $request->partner_id ?? 0,
                'amount'           => $request->amount,
                'tax_type_id'      => $request->tax_type ?? 0,
                'deal_date'         => $request->deal_date,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $linesData[] = [
                'journal_entry_id' => $entry->id,
                'side'             => $isExpense ? 2 : 1, // 现金方向相反
                'account_id'       => $depositAccount->id,
                'sub_account_id'   => $request->deposit_sub_account_id,
                'partner_id'       => 0,
                'amount'           => $request->amount,
                'deal_date'         => $request->deal_date,
                'tax_type_id'      => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            AccountJournalLine::insert($linesData);

            DB::commit();


            return redirect()->route('masters.account-deposit.index')
                ->with('success', '仕訳伝票を登録しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('仕訳伝票作成エラー: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'システムエラーが発生しました。',
                    'errors' => ['general' => ['管理者にお問い合わせください。']]
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'システムエラーが発生しました。管理者にお問い合わせください。']);
        }
    }


    public function generatePdf()
    {
        $period_id = request()->input('period_id');
        $yearmonth = request()->input('yearmonth');
        $period = AccountPeriod::find($period_id);
        $sub_account_id = request()->input('sub_account_id');

        if ($yearmonth == "13" ){
            $startDate = $period->start;
            $endDate = $period->end;
        }else{
            $startDate = date('Y-m-d', strtotime("first day of $yearmonth"));
            $endDate = date('Y-m-d', strtotime("last day of $yearmonth"));
        }
        $accountConfig = AccountConfig::first();
        $account = Account::where('id', $accountConfig->account_deposit_id)->first();
        $mark = $account->category->mark  == "借" ? 1 : 2;

        if ($sub_account_id!="0"){
            $datas = $this->makeSubData($startDate, $endDate, $account,$sub_account_id);
        }else{
            $datas = $this->makeData($startDate, $endDate, $account);
        }
        $datas['mark'] = $mark;
        $datas['company'] = UserCompanyInfo::first();
        $datas['subAccountName'] = AccountSub::find($sub_account_id)->name ?? '';


        try {
            // 1. 渲染 HTML
            $html = View::make('masters.account-deposit.pdf', $datas)->render();
            

            // 2. 初始化 Browsershot
            // D:\Google\Chrome\Application
            $browsershot = Browsershot::html($html)
                ->paperSize(210, 297, 'mm')
                ->margins(10, 15, 5, 15) // 使用推荐的 margins 方法
                ->setOption('printBackground', true)
                ->waitUntilNetworkIdle()
                ->timeout(30000);

            // 2. 根据操作系统设置 Chrome 路径（仅在 Windows 下需要指定）
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows 环境：指定 chrome.exe 路径
                $browsershot->setChromePath('D:\Google\Chrome\Application\chrome.exe');
            } else {
                $browsershot->setNodePath('/usr/local/nodejs/bin/node');
                
                // 如果上面只指定 node 还不行，可以尝试加上 npm 路径
                $browsershot->setNpmPath('/usr/local/nodejs/bin/npm');
                $browsershot->setChromePath('/usr/local/chrome/chrome');
                // [Linux/生产环境] 取消下面这行的注释
                $browsershot->addChromiumArguments(['--no-sandbox', '--disable-setuid-sandbox']);
            }


            // 3. 【关键修改】获取 PDF 内容
            // 方法 A (推荐): 直接获取二进制字符串 (适用于大多数新版本)
            $pdfContent = $browsershot->getPdf();

            // 防御性检查：如果 getPdf() 返回的不是字符串（比如返回了对象或路径）
            if (!is_string($pdfContent)) {
                // 如果返回的是对象，尝试保存为临时文件再读取
                $tempFile = tempnam(sys_get_temp_dir(), 'invoice_') . '.pdf';
                $browsershot->savePdf($tempFile);
                $pdfContent = file_get_contents($tempFile);
                unlink($tempFile); // 立即删除临时文件
                
                // 如果还是不对，抛出异常以便调试
                if (!is_string($pdfContent)) {
                    throw new \Exception('Failed to get PDF content as string. Got: ' . gettype($pdfContent));
                }
            }

            // 4. 生成文件名
            if($datas['subAccountName']){
                $filename = $account->name.'-'.$datas['subAccountName'].'出纳账'. '.pdf';
            }else{
                $filename = $account->name. '出纳账'.'.pdf';
            }
            

            // 5. 返回响应 (现在 strlen 接收的肯定是字符串了)
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdfContent), 
            ]);

        } catch (\Exception $e) {
            Log::error('PDF Generation Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            if (app()->environment('local')) {
                return response()->json([
                    'message' => 'PDF 生成失败',
                    'error' => $e->getMessage(),
                    'type' => gettype($e), // 显示错误类型
                ], 500);
            }
            return response()->view('errors.500', [], 500);
        }
    }

}