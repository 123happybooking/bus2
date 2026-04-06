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
use Symfony\Component\Process\Exception\ProcessFailedException; // 引入这个异常类
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
    public $backoff = [10, 30, 60]; // 失败后分别等待 10s, 30s, 60s 重试
    public $timeout = 300; // 5分钟超时
    // ==============================================

    public function __construct($invoiceId, $tenantId)
    {
        $this->invoiceId = $invoiceId;
        $this->tenantId = $tenantId;
    }

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
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        // 1. 获取数据
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

        // 2. 准备数据
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

        $tempPdfPath = null;

        try {
            // 3. 渲染 HTML
            $viewName = ($invoice->language == 1) ? 'masters.invoices.template_ja' : 'masters.invoices.template_en';
            
            if (!View::exists($viewName)) {
                throw new Exception("视图文件不存在：{$viewName}");
            }

            $html = View::make($viewName, $data)->render();

            // 4. 初始化 Browsershot
            $browsershot = Browsershot::html($html);

            // 5. 环境配置 (核心修复部分)
            if (PHP_OS_FAMILY === 'Windows') {
                $browsershot->setChromePath('D:\Google\Chrome\Application\chrome.exe');
            } else {
                // Linux 环境
                // 建议：先在服务器执行 `which node` 确认路径
                $nodePath = '/usr/local/nodejs/bin/node'; 
                $chromePath = '/usr/local/chrome/chrome'; 

                if (file_exists($nodePath)) {
                    $browsershot->setNodePath($nodePath);
                }
                
                if (file_exists($chromePath)) {
                    $browsershot->setChromePath($chromePath);
                }

                // 【关键修复】添加必要的参数，解决 Zygote 和 权限问题
                $browsershot->addChromiumArguments([
                    '--no-sandbox', 
                    '--disable-setuid-sandbox', 
                    '--disable-dev-shm-usage', // 解决共享内存不足
                    '--no-zygote'             // 解决 Zygote 报错
                ]);
            }

            // 6. 配置 PDF 选项
            $browsershot
                ->paperSize(210, 297, 'mm')
                ->margins(15, 15, 15, 15)
                ->setOption('printBackground', true)
                ->waitUntilNetworkIdle()
                ->timeout(60000); // 增加超时时间到 60秒

            // 7. 生成 PDF (临时文件模式)
            Log::info("正在调用 Chrome 生成 PDF...");
            $tempPdfPath = tempnam(sys_get_temp_dir(), 'browsershot_') . '.pdf';
            
            $browsershot->savePdf($tempPdfPath);
            
            if (!file_exists($tempPdfPath)) {
                throw new Exception("savePdf 执行完成，但临时文件未生成。Chrome 可能已崩溃。");
            }

            $pdfContent = file_get_contents($tempPdfPath);
            
            if ($pdfContent === false || empty($pdfContent)) {
                throw new Exception("无法读取临时 PDF 文件内容或内容为空。");
            }

            Log::info("✅ PDF 二进制流准备成功，大小：" . strlen($pdfContent) . " 字节");

            // 8. 保存文件
            $now = Carbon::now();
            $directory = "files/pdf/{$now->format('Y')}/{$now->format('md')}";
            $filename = 'invoice_' . $data['invoice']->invoice_number . '.pdf';
            $relativePath = "{$directory}/{$filename}";

            $saved = Storage::disk('public')->put($relativePath, $pdfContent);

            if (!$saved) {
                throw new Exception('文件保存失败，请检查 storage/app/public 目录权限');
            }

            // 9. 更新数据库
            $invoice->update([
                'pdf_file_path' => $relativePath,
                'pdf_generated_at' => now(),
            ]);

            Log::info("【PDF 任务成功】文件已保存：{$relativePath}", ['invoice_id' => $invoice->id]);

        } catch (ProcessFailedException $exception) {
            // 【关键】专门捕获 Chrome 进程失败的异常
            Log::error('【PDF 任务异常】Chrome 进程失败：' . $exception->getMessage());
            Log::error('Chrome Error Output: ' . $exception->getProcess()->getErrorOutput());
            Log::error('Chrome Output: ' . $exception->getProcess()->getOutput());
            
            throw $exception; // 抛出异常以触发重试

        } catch (Exception $e) {
            Log::error('【PDF 任务异常】生成失败：' . $e->getMessage(), [
                'invoice_id' => $this->invoiceId,
                'attempt' => $this->attempts(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        } finally {
            // 10. 清理临时文件
            if ($tempPdfPath && file_exists($tempPdfPath)) {
                @unlink($tempPdfPath);
                Log::info("临时文件已清理：{$tempPdfPath}");
            }
        }
    }

    public function failed(Throwable $exception)
    {
        Log::critical("【PDF 任务彻底失败】已耗尽所有重试次数", [
            'invoice_id' => $this->invoiceId,
            'error' => $exception->getMessage()
        ]);
    }

    private function getTenantDbConfig($tenantId)
    {
        return [
            'host' => '127.0.0.1',
            'database' => 'bus_user_' . $tenantId,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];
    }
}