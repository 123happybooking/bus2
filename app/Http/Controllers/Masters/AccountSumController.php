<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Masters\AccountPeriod;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use App\Models\Masters\UserCompanyInfo;

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


        $data = $this->makeData($startDate, $endDate);


        return view('masters.account-sums.index', compact(
            'data', 'periods', 'months', 'period_id', 'yearmonth'
        ));
    }

    function makeData($startDate, $endDate){
        $data = DB::table('accounts as acc')
            // 1. 关联余额表（用于获取期末余额）
            ->leftJoin('account_month_details as amd_end', function ($join) use ($endDate) {
                $join->on('acc.id', '=', 'amd_end.account_id')
                    ->where('amd_end.year_month', '=', $endDate)
                    ->whereNull('amd_end.deleted_at')
                    ->whereNull('amd_end.sub_account_id');
            })
            // 2. 关联明细表（用于计算区间合计）
            ->leftJoin('account_month_details as amd_sum', function ($join) use ($startDate, $endDate) {
                $join->on('acc.id', '=', 'amd_sum.account_id')
                    ->whereBetween('amd_sum.year_month', [$startDate, $endDate])
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
            return $data;

    }

    public function generatePdf()
    {
        $period_id = request()->input('period_id');
        $yearmonth = request()->input('yearmonth');
        $period = AccountPeriod::find($period_id);


        if ($yearmonth == "13" ){
            $startDate = date('Y-m', strtotime($period->start));
            $endDate = date('Y-m', strtotime($period->end));
        }else{
            $startDate = date('Y-m', strtotime("first day of $yearmonth"));
            $endDate = date('Y-m', strtotime("last day of $yearmonth"));
        }


        $datas = $this->makeData($startDate, $endDate);
        $company = UserCompanyInfo::first();


        // return view('masters.account-sums.pdf', compact(
        //     'datas', 'startDate','endDate','company'
        // ));

        $datas =  compact(
            'datas', 'startDate','endDate','company'
        );

        try {
            // 1. 渲染 HTML
            $html = View::make('masters.account-sums.pdf', $datas)->render();
            

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
            $filename = "試算表" . '.pdf';

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