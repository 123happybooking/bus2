<?php

namespace App\Http\Controllers\Masters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Masters\AccountPeriod;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class AccountBsController extends Controller
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
                WHERE aje.posting_date BETWEEN ? AND ?
                GROUP BY ajl.account_id
            ) sub ON acc.id = sub.account_id
            WHERE ac.id IN (1, 2, 3, 4, 5, 6) 
            AND acc.is_active = 1
            ORDER BY ac.id, acc.code
        ", [$startDate, $endDate]);

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

        $netIncome = 0;
        
        $baseQuery = DB::table('account_journal_lines as ajl')
            ->join('accounts as acc', 'ajl.account_id', '=', 'acc.id')
            ->join('account_journal_entries as aje', 'ajl.journal_entry_id', '=', 'aje.id')
            // 【修复点 1】补全了分类 ID 数组
            ->whereIn('acc.category_id', [7, 8, 9, 10, 11, 12, 13, 14])
            ->whereDate('aje.posting_date', '>=', $startDate)
            ->whereDate('aje.posting_date', '<=', $endDate)
            ->whereNotNull('ajl.account_id');

        // 营业收入 (7) - 贷方
        $rev_7 = (clone $baseQuery)->where('ajl.side', 2)->where('acc.category_id', 7)->sum('ajl.amount');
        
        // 营业成本 (8) - 借方
        $exp_8 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 8)->sum('ajl.amount');
        
        // 营业费用 (9) - 借方
        $exp_9 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 9)->sum('ajl.amount');
        
        // 营业外收入 (10) - 贷方
        $rev_10 = (clone $baseQuery)->where('ajl.side', 2)->where('acc.category_id', 10)->sum('ajl.amount');
        
        // 营业外支出 (11) - 借方
        $exp_11 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 11)->sum('ajl.amount');
        
        // 其他收入 (12) - 贷方
        $rev_12 = (clone $baseQuery)->where('ajl.side', 2)->where('acc.category_id', 12)->sum('ajl.amount');
        
        // 其他支出 (13) - 借方
        $exp_13 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 13)->sum('ajl.amount');
        
        // 税金 (14) - 借方
        $exp_14 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 14)->sum('ajl.amount');

        // 3. 按照原逻辑计算净利润
        $operatingIncome = $rev_7 - $exp_8 - $exp_9;
        $ordinaryIncome = $operatingIncome + $rev_10 - $exp_11;
        $profitBeforeTax = $ordinaryIncome + $rev_12 - $exp_13;
        
        $netIncome = $profitBeforeTax - $exp_14;


        $totalLiabilities+=$netIncome;

        // 6. 传递数据到视图
        // 注意：这里传递的是 'date'，而不是 'current'
        return view('masters.account-bs.index', compact(

            'assets', 
            'liabilities', 
            'totalAssets', 
            'totalLiabilities',
            'assetOrder',
            'liabilityOrder',
            'netIncome',
            'periods','months','period_id','yearmonth'
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
                WHERE aje.posting_date BETWEEN ? AND ?
                GROUP BY ajl.account_id
            ) sub ON acc.id = sub.account_id
            WHERE ac.id IN (1, 2, 3, 4, 5, 6) 
            AND acc.is_active = 1
            ORDER BY ac.id, acc.code
        ", [$startDate, $endDate]);

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

        $netIncome = 0;
        
        $baseQuery = DB::table('account_journal_lines as ajl')
            ->join('accounts as acc', 'ajl.account_id', '=', 'acc.id')
            ->join('account_journal_entries as aje', 'ajl.journal_entry_id', '=', 'aje.id')
            // 【修复点 1】补全了分类 ID 数组
            ->whereIn('acc.category_id', [7, 8, 9, 10, 11, 12, 13, 14])
            ->whereDate('aje.posting_date', '>=', $startDate)
            ->whereDate('aje.posting_date', '<=', $endDate)
            ->whereNotNull('ajl.account_id');

        // 营业收入 (7) - 贷方
        $rev_7 = (clone $baseQuery)->where('ajl.side', 2)->where('acc.category_id', 7)->sum('ajl.amount');
        
        // 营业成本 (8) - 借方
        $exp_8 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 8)->sum('ajl.amount');
        
        // 营业费用 (9) - 借方
        $exp_9 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 9)->sum('ajl.amount');
        
        // 营业外收入 (10) - 贷方
        $rev_10 = (clone $baseQuery)->where('ajl.side', 2)->where('acc.category_id', 10)->sum('ajl.amount');
        
        // 营业外支出 (11) - 借方
        $exp_11 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 11)->sum('ajl.amount');
        
        // 其他收入 (12) - 贷方
        $rev_12 = (clone $baseQuery)->where('ajl.side', 2)->where('acc.category_id', 12)->sum('ajl.amount');
        
        // 其他支出 (13) - 借方
        $exp_13 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 13)->sum('ajl.amount');
        
        // 税金 (14) - 借方
        $exp_14 = (clone $baseQuery)->where('ajl.side', 1)->where('acc.category_id', 14)->sum('ajl.amount');

        // 3. 按照原逻辑计算净利润
        $operatingIncome = $rev_7 - $exp_8 - $exp_9;
        $ordinaryIncome = $operatingIncome + $rev_10 - $exp_11;
        $profitBeforeTax = $ordinaryIncome + $rev_12 - $exp_13;
        
        $netIncome = $profitBeforeTax - $exp_14;


        $totalLiabilities+=$netIncome;

        // 6. 传递数据到视图
        // 注意：这里传递的是 'date'，而不是 'current'
        // return view('masters.account-bs.pdf', compact(
        //     'assets', 
        //     'liabilities', 
        //     'totalAssets', 
        //     'totalLiabilities',
        //     'assetOrder',
        //     'liabilityOrder',
        //     'netIncome',
        //    'period_id','yearmonth'
        // ));

        $datas = [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'assetOrder' => $assetOrder,
            'liabilityOrder' => $liabilityOrder,
            'netIncome' => $netIncome,
            'period_id' => $period_id,
            'yearmonth' => $yearmonth
        ];


        try {
            // 1. 渲染 HTML
            $html = View::make('masters.account-bs.pdf', $datas)->render();
            

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
            \Log::error('PDF Generation Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
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