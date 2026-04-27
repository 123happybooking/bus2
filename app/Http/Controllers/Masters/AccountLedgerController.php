<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Account;
use App\Models\Masters\AccountCategory;
use App\Models\Masters\AccountJournalEntry;
use App\Models\Masters\AccountJournalLine;
use App\Models\Masters\AccountMonthDetail;
use App\Models\Masters\AccountPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class AccountLedgerController extends Controller
{
    /**
     * Display the ledger (勘定元帳) index page.
     */
    public function index(Request $request)
    {
        $query = Account::query();
        
        // 搜索功能：科目代码、科目名称
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // 筛选：有效状态 (可选功能)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('category_name')) {
            $category_id = AccountCategory::where('name', $request->category_name)->first()->id ?? 0;
            $query->where('category_id', $category_id);
        }

        $perPage = 20; // 默认值
        $allowedPerPages = [20, 30, 50]; // 允许的选项
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 排序：默认按 ID 降序 (新注册的在后)，也可改为按 code 升序
        $accounts = $query->orderBy('id', 'asc')->paginate($perPage);
        
        // 保留查询参数用于分页链接
        $accounts->appends(['search' => $request->search, 'is_active' => $request->is_active, 'category_name' => $request->category_name, 'start_date' => $request->start_date, 'end_date' => $request->end_date,'per_page' => $perPage]);
        $categories = AccountCategory::get();

        $periods = AccountPeriod::orderBy('created_at','desc')->get();
        $period = AccountPeriod::orderBy('created_at','desc')->first();
        if($request->period_id){
            $period = AccountPeriod::find($request->period_id);
        }

        $period_id = $period->id ?? 0;
        $period_start = $period->start;
        $start_year = explode('-', $period_start)[0];
        $start_month = explode('-', $period_start)[1];
        $moren_month = explode('-', $period_start)[1];

        if (!$request->filled('yearmonth')) {
            $yearmonth = $start_year . '-' . $start_month;
        }else{
            if ($request->yearmonth == "13") {
                $yearmonth = $request->yearmonth;
            }else{
                $start_year = substr($request->yearmonth, 0, 4);
                $start_month = substr($request->yearmonth, 5, 2);
                $yearmonth = $request->yearmonth;
            }
        }


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
        
        return view('masters.account-ledgers.index', compact('accounts','categories','periods','months','period_id','yearmonth'));
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
            $query->where('posting_date','>=',$startDate."-01");
        }
        if ($endDate) {
            $query->where('posting_date','<=',$endDate."-31");
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
            $account_name = "XXX";
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

    public function generate($id){
        $period_id = request()->input('period_id');
        $yearmonth = request()->input('yearmonth');
        $period = AccountPeriod::find($period_id);
        $period_start = $period->start;
        $start_year = explode('-', $period_start)[0];
        $start_month = explode('-', $period_start)[1];

        if ($yearmonth == "13" ){
            $tmp = $start_year."-".$start_month;
            $startDate = date('Y-m', strtotime("first day of $tmp"));
            $endDate = date('Y-m', strtotime("+1 year -1 month", strtotime($startDate)));
        }else{
            $startDate = date('Y-m', strtotime("first day of $yearmonth"));
            $endDate = date('Y-m', strtotime("first day of $yearmonth"));
        }

        $account = Account::findOrFail($id);
        $datas = $this->makeData($startDate, $endDate, $account);
         return response()->json($datas);

    }

    public function generatePdf()
    {
        $period_id = request()->input('period_id');
        $yearmonth = request()->input('yearmonth');
        $period = AccountPeriod::find($period_id);
        $period_start = $period->start;
        $start_year = explode('-', $period_start)[0];
        $start_month = explode('-', $period_start)[1];

        if ($yearmonth == "13" ){
            $tmp = $start_year."-".$start_month;
            $startDate = date('Y-m', strtotime("first day of $tmp"));
            $endDate = date('Y-m', strtotime("+1 year -1 month", strtotime($startDate)));
        }else{
            $startDate = date('Y-m', strtotime("first day of $yearmonth"));
            $endDate = date('Y-m', strtotime("first day of $yearmonth"));
        }
        $account = Account::findOrFail( request()->input('id'));
        $datas = $this->makeData($startDate, $endDate, $account);


        try {
            // 1. 渲染 HTML
            $html = View::make('masters.account-ledgers.pdf', $datas)->render();
            

            // 2. 初始化 Browsershot
            // D:\Google\Chrome\Application
            $browsershot = Browsershot::html($html)
                ->paperSize(210, 297, 'mm')
                ->margins(15, 15, 15, 15) // 使用推荐的 margins 方法
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
            $filename = $account->name. '.pdf';

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