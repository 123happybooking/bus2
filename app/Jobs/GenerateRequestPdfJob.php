<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Spatie\Browsershot\Browsershot;
use App\Models\Masters\Invoice;
use App\Models\Masters\InvoiceItem;
use App\Models\Masters\InvoiceTaxSummary;
use App\Models\Masters\Bank;
use App\Models\Masters\UserCompanyInfo;
use Carbon\Carbon;
use Throwable;
use Exception;

class GenerateRequestPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoiceId;
    public $tenantId;

    // ================= 队列重试配置 =================
    public $tries = 3;
    public $backoff = [10, 30, 60];
    public $timeout = 300; 
    // ==============================================

    /**
     * Create a new job instance.
     */
    public function __construct($invoiceId, $tenantId)
    {
        $this->invoiceId = $invoiceId;
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info("【PDF 任务开始】Invoice ID: {$this->invoiceId}, 尝试次数: {$this->attempts()}, 系统: " . PHP_OS_FAMILY);
        $connectionName = 'bus_user_' . $this->tenantId;

        $dbConfig = $this->getTenantDbConfig($this->tenantId); 
        
        if (!$dbConfig) {
            Log::error("找不到租户配置", ['tenantId' => $this->tenantId]);
            return;
        }

        Config::set("database.connections.{$connectionName}", [
            'driver' => 'mysql',
            'host' => $dbConfig['host'],
            'database' => $dbConfig['database'],
            'username' => $dbConfig['username'],
            'password' => $dbConfig['password'],
            // ... 其他配置
        ]);
        // ==========================================
        // 1. 【核心修复】跨平台自动检测 Chrome 路径
        // ==========================================
        $foundChromePath = null;
        $isWindows = (PHP_OS_FAMILY === 'Windows');

        if ($isWindows) {
            // Windows 常见路径
            $winPaths = [
                'D:/Google/Chrome/Application/chrome.exe',
                'C:/Program Files/Google/Chrome/Application/chrome.exe',
                'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
                'C:/Users/' . getenv('USERNAME') . '/AppData/Local/Google/Chrome/Application/chrome.exe',
            ];
            
            foreach ($winPaths as $path) {
                if (file_exists($path)) {
                    $foundChromePath = $path;
                    break;
                }
            }
        } else {
            // Linux 常见路径 (按优先级排序)
            $linuxPaths = [
                '/usr/bin/google-chrome-stable',
                '/usr/bin/google-chrome',
                '/usr/bin/chromium-browser',
                '/usr/bin/chromium',
                '/snap/bin/chromium',
                '/snap/google-chrome/current/usr/lib/chromium-browser/chrome',
            ];

            foreach ($linuxPaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    $foundChromePath = $path;
                    break;
                }
            }
            
            // 如果特定路径都没找到，尝试依赖系统 PATH (which google-chrome)
            if (!$foundChromePath) {
                $output = shell_exec('which google-chrome-stable 2>/dev/null || which google-chrome 2>/dev/null || which chromium-browser 2>/dev/null');
                if ($output) {
                    $foundChromePath = trim($output);
                }
            }
        }

        if (!$foundChromePath) {
            $errorMsg = "❌ 致命错误：在 " . ($isWindows ? 'Windows' : 'Linux') . " 环境下未找到 Chrome/Chromium 浏览器。请确认已安装并检查路径。";
            Log::error($errorMsg);
            throw new Exception($errorMsg);
        }

        // 强制注入环境变量 (跨平台有效)
        putenv("PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true");
        putenv("PUPPETEER_EXECUTABLE_PATH={$foundChromePath}");
        
        Log::info("✅ 已检测到 Chrome 并设置环境变量：{$foundChromePath}");

        // 2. 获取数据
        $invoice = Invoice::on($connectionName)->find($this->invoiceId);
        
        if (!$invoice) {
            Log::error("【PDF 任务失败】找不到发票记录，ID: {$this->invoiceId}");
            return; 
        }

        // 重新查询关联数据
        $items = $invoice->items;
        $summary_10 = InvoiceTaxSummary::on($connectionName)->where('invoice_id', $invoice->id)->where('tax_rate', 10)->first();
        $summary_8 = InvoiceTaxSummary::on($connectionName)->where('invoice_id', $invoice->id)->where('tax_rate', 8)->first();
        $non_taxable = InvoiceItem::on($connectionName)->where('invoice_id', $invoice->id)->where('tax_rate','<', 0)->sum('amount');
        $bank = Bank::on($connectionName)->where('id', $invoice->bank_id)->first();
        $company_info = UserCompanyInfo::on($connectionName)->where('user_company_id', $this->tenantId)->first();

        // 3. 准备数据
        $data = [
            'invoice' => (object)[
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'invoice_number' => $invoice->invoice_number,
                'notes'=> $invoice->notes,
                'subtotal_amount'=> $invoice->subtotal_amount,
                'tax_amount'=> $invoice->tax_amount,
                'total_amount'=> $invoice->total_amount,
                'tax_mode'=> $invoice->tax_mode,
                'currency_code'=> $invoice->currency_code,
                'non_taxable'=> $non_taxable,
            ],
            'summary_10' => $summary_10,
            'summary_8' => $summary_8,
            'items' => $items,
            'bank' => preg_split('/\r\n|\r|\n/', $bank->bank_info),
            'customer'=> preg_split('/\r\n|\r|\n/', $invoice->agency_detail),
            'company' => (object)[
                'name' => $company_info->company_name,
                'postal_code' => $company_info->postal_code,
                'address' => $company_info->address,
                'phone' => $company_info->phone_number,
                'fax' => $company_info->fax_number,
                'contact' => $invoice->staff->name ?? '',
            ]
        ];

        try {
            // 4. 渲染 HTML
            $viewName = ($invoice->language == 1) ? 'masters.invoices.template_ja' : 'masters.invoices.template_en';
            
            if (!View::exists($viewName)) {
                throw new Exception("视图文件不存在：{$viewName}");
            }

            $html = View::make($viewName, $data)->render();

            // ==========================================
            // 5. 【调试功能】跨平台保存 HTML
            // ==========================================
            // Windows: C:/temp, Linux: /tmp
            $debugDir = $isWindows ? 'C:/temp' : '/tmp';
            if (!is_dir($debugDir)) {
                @mkdir($debugDir, 0777, true);
            }
            $debugHtmlPath = $debugDir . '/debug_invoice_latest.html';
            file_put_contents($debugHtmlPath, $html);
            Log::info("调试 HTML 已保存至：{$debugHtmlPath} (大小：" . strlen($html) . " 字节)");
            // ==========================================

            // 6. 初始化 Browsershot
            $browsershot = Browsershot::html($html);

            // 7. 配置 Browsershot
            $browsershot->setChromePath($foundChromePath);

            // 构建通用参数
            $chromeArgs = [
                '--disable-gpu',
                '--no-first-run',
                '--no-zygote',
                '--ignore-certificate-errors',
            ];

            // Linux 特有参数 (必须)
            if (!$isWindows) {
                $chromeArgs[] = '--no-sandbox';
                $chromeArgs[] = '--disable-setuid-sandbox';
                $chromeArgs[] = '--disable-dev-shm-usage'; // 防止 Docker 内存溢出
            } else {
                // Windows 可选优化
                $chromeArgs[] = '--disable-setuid-sandbox'; 
            }

            $browsershot
                ->paperSize(210, 297, 'mm') // A4
                ->margins(15, 15, 15, 15) // mm
                ->setOption('printBackground', true)
                ->setOption('args', $chromeArgs)
                ->waitUntilNetworkIdle()
                ->timeout(30000); 

            // ==========================================
            // 8. 生成 PDF：使用 savePdf + file_get_contents
            // ==========================================
            Log::info("正在调用 Chrome 生成 PDF (临时文件模式)...");

            $tempPdfPath = tempnam(sys_get_temp_dir(), 'browsershot_') . '.pdf';
            Log::info("临时 PDF 路径：{$tempPdfPath}");

            try {
                $browsershot->savePdf($tempPdfPath);
                
                if (!file_exists($tempPdfPath)) {
                    throw new Exception("savePdf 执行完成，但临时文件未生成：{$tempPdfPath}。Chrome 可能已崩溃。");
                }

                $fileSize = filesize($tempPdfPath);
                Log::info("临时 PDF 文件生成成功，大小：{$fileSize} 字节");

                if ($fileSize === 0) {
                    throw new Exception("生成的 PDF 文件大小为 0 字节。可能是 HTML/CSS 错误或 Chrome 渲染崩溃。请检查调试 HTML 文件：{$debugHtmlPath}");
                }

                $pdfContent = file_get_contents($tempPdfPath);
                
                if ($pdfContent === false) {
                    throw new Exception("无法读取临时 PDF 文件内容：{$tempPdfPath}");
                }

            } finally {
                if (file_exists($tempPdfPath)) {
                    @unlink($tempPdfPath);
                    Log::info("临时文件已清理：{$tempPdfPath}");
                }
            }
            // ==========================================

            // 9. 最终验证
            if (!is_string($pdfContent) || empty($pdfContent)) {
                throw new Exception('最终验证失败：PDF 内容为空或类型错误 (' . gettype($pdfContent) . ')。');
            }

            Log::info("✅ PDF 二进制流准备成功，大小：" . strlen($pdfContent) . " 字节");

            // 10. 构建保存路径 (YYYY/MMDD)
            $now = Carbon::now();
            $year = $now->format('Y');
            $monthDay = $now->format('md'); 
            
            $directory = "files/pdf/{$year}/{$monthDay}";
            $filename = 'invoice_' . $data['invoice']->invoice_number . '.pdf';
            $relativePath = "{$directory}/{$filename}";

            // 11. 保存文件到 storage/app/public
            $saved = Storage::disk('public')->put($relativePath, $pdfContent);

            if (!$saved) {
                throw new Exception('文件保存失败，请检查 storage/app/public 目录权限');
            }

            // 12. 更新数据库
            $invoice->update([
                'pdf_file_path' => $relativePath,
                'pdf_generated_at' => now(),
            ]);

            Log::info("【PDF 任务成功】文件已保存：{$relativePath}", ['invoice_id' => $invoice->id]);

        } catch (Exception $e) {
            Log::error('【PDF 任务异常】生成失败：' . $e->getMessage(), [
                'invoice_id' => $this->invoiceId,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'os' => PHP_OS_FAMILY,
                'chrome_path' => $foundChromePath ?? 'Not Set',
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * 当所有重试次数用尽后调用的方法
     */
    public function failed(Throwable $exception)
    {
        Log::critical("【PDF 任务彻底失败】已耗尽所有重试次数 ({$this->tries} 次)", [
            'invoice_id' => $this->invoiceId,
            'os' => PHP_OS_FAMILY,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

        private function getTenantDbConfig($tenantId)
    {
        // 这里写你的逻辑，返回一个包含 host, database, username, password 的数组
        // 例如：
        // return DB::connection('mysql')->table('tenants')->find($tenantId);
        return [
            'host' => '127.0.0.1',
            'database' => 'bus_user_' . $tenantId,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];
    }
}