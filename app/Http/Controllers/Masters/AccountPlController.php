<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Masters\AccountPeriod;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class AccountPlController extends Controller
{
    public function index(Request $request)
    {

        $periods = AccountPeriod::orderBy('created_at','desc')->get();
        $period = AccountPeriod::orderBy('created_at','desc')->first();
        if($request->period_id){
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


        // 初始化总计
        $totalTaxes = 0; 
        $totalRevenue = $totalCogs = $totalExpenses = 0;
        $totalOIncome = $totalOExpenses = 0;
        $totalSOIncome = $totalSOExpenses = 0;

        // 【修复点】在这里初始化利润变量，防止 compact 报错
        $operatingIncome = 0;
        $ordinaryIncome = 0;
        $profitBeforeTax = 0;

        $netIncome = 0;  // 净利润

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
                    ->whereIn('acc.category_id', [7, 8, 9, 10, 11, 12, 13,14])
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
                    } elseif (in_array($row->category_id, [8, 9, 11, 13,14])) { // 费用类: 取借方
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
                        case 14: $totalTaxes += $netAmount; break;
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
                $netIncome = $profitBeforeTax - $totalTaxes;

            } catch (\Exception $e) {
                Log::error('PL Report Error: ' . $e->getMessage());
            }
        }


        return view('masters.account-pl.index', compact(
            'startDate', 'endDate', 'groupedData',
            'totalRevenue', 'totalCogs', 'totalExpenses',
            'totalOIncome', 'totalOExpenses',
            'totalSOIncome', 'totalSOExpenses',
            'operatingIncome', 'ordinaryIncome', 'profitBeforeTax',
            'totalTaxes','netIncome','periods','months','period_id','yearmonth'
        ));
    }

    public function generatePdf()
    {
        $period_id = request()->input('period_id');
        $yearmonth = request()->input('yearmonth');
        $period = AccountPeriod::find($period_id);


        if ($yearmonth == "13" ){
            $startDate = $period->start;
            $endDate = $period->end;
        }else{
            $startDate = date('Y-m-d', strtotime("first day of $yearmonth"));
            $endDate = date('Y-m-d', strtotime("last day of $yearmonth"));
        }

        // 初始化总计
        $totalTaxes = 0; 
        $totalRevenue = $totalCogs = $totalExpenses = 0;
        $totalOIncome = $totalOExpenses = 0;
        $totalSOIncome = $totalSOExpenses = 0;

        // 【修复点】在这里初始化利润变量，防止 compact 报错
        $operatingIncome = 0;
        $ordinaryIncome = 0;
        $profitBeforeTax = 0;

        $netIncome = 0;  // 净利润

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
                    ->whereIn('acc.category_id', [7, 8, 9, 10, 11, 12, 13,14])
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
                    } elseif (in_array($row->category_id, [8, 9, 11, 13,14])) { // 费用类: 取借方
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
                        case 14: $totalTaxes += $netAmount; break;
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
                $netIncome = $profitBeforeTax - $totalTaxes;

            } catch (\Exception $e) {
                Log::error('PL Report Error: ' . $e->getMessage());
            }
        }


        // return view('masters.account-pl.pdf', compact(
        //     'startDate', 'endDate', 'groupedData',
        //     'totalRevenue', 'totalCogs', 'totalExpenses',
        //     'totalOIncome', 'totalOExpenses',
        //     'totalSOIncome', 'totalSOExpenses',
        //     'operatingIncome', 'ordinaryIncome', 'profitBeforeTax',
        //     'totalTaxes','netIncome','period_id','yearmonth'
        // ));

        $datas = compact(
            'startDate', 'endDate', 'groupedData',
            'totalRevenue', 'totalCogs', 'totalExpenses',
            'totalOIncome', 'totalOExpenses',
            'totalSOIncome', 'totalSOExpenses',
            'operatingIncome', 'ordinaryIncome', 'profitBeforeTax',
            'totalTaxes','netIncome','period_id','yearmonth'
        );

        try {
            // 1. 渲染 HTML
            $html = View::make('masters.account-pl.pdf', $datas)->render();
            

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
            $filename = time(). '.pdf';

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