<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Invoice;
use App\Models\Masters\InvoiceItem;
use App\Models\Masters\Currency;
use App\Models\Masters\InvoiceTaxSummary;
use App\Models\Masters\Product;
use App\Models\Masters\Bank;
use App\Models\Masters\Agency;
use App\Models\Masters\Staff;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\DailyItinerary; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rules\In;
use Spatie\Browsershot\Browsershot;
use App\Jobs\GenerateRequestPdfJob;
use App\Models\Masters\PaymentDetail;
use App\Models\Masters\PaymentHeader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter; 
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use App\Models\Masters\Account;
use App\Models\Masters\AccountPartner;
use App\Models\Masters\AccountTax;
use App\Models\Masters\AccountDepartment;
use App\Models\Masters\AccountJournalEntry;
use App\Models\Masters\AccountJournalLine;
use App\Models\Masters\AccountConfig;
use App\Models\Masters\UserCompanyInfo;
use App\Exports\InvoicesExport; // 引入导出类
use Maatwebsite\Excel\Facades\Excel; // 引入 Excel 门面
use Mpdf\Mpdf;


class InvoiceController extends Controller
{
    // public function index(Request $request)
    // {
    //     $groupId = $request->query('group_id');

    //     if (! $groupId) {
    //         return redirect()->back()->with([
    //             'error' => 'グループIDが指定されていません。',
    //             'alert-type' => 'danger'
    //         ]);
    //     }

    //     // if (! auth()->user()->canAccessGroup($groupId)) {
    //     //     abort(403, 'アクセス権限がありません。');
    //     // }

    //     $query = Invoice::where('group_id', $groupId);
    //     if ($request->filled('agency_id') && $request->agency_id) {
    //         $query->where('agency_id', $request->agency_id);
    //     }

    //     if ($request->filled('staff_id') && $request->staff_id) {
    //         $query->where('staff_id', $request->staff_id);
    //     }

    //     if ($request->filled('payment_status') && count($request->payment_status)>0) {
    //         $query->wherein('payment_status', $request->payment_status);
    //     }

    //     if ($request->filled('target_date') && $request->target_date) {
    //         $end_date = date('Y-m-d',time());
    //         if($request->days > 0){
    //             $end_date = date('Y-m-d', strtotime($request->target_date . ' +'.$request->days.' day'));
    //         }
            
    //         if($request->date_type== "operation"){
    //             $query->where('operation_date','>=', $request->target_date)->where('operation_date','<=', $end_date);
    //         }elseif($request->date_type== "billing"){
    //             $query->where('invoice_date','>=', $request->target_date)->where('invoice_date','<=', $end_date);
    //         }else{
    //             $headers = PaymentHeader::where('group_id', $groupId)->where('payment_date','>=', $request->target_date)->where('payment_date','<=', $end_date)->pluck('id')->toArray();
    //             $ids = PaymentDetail::whereIn('payment_header_id', $headers)->pluck('invoice_id')->toArray();
    //             $query->wherein('id', $ids);

    //         }

    //     }

    //     $totalAmount = (clone $query)->where('type', 1)->sum('total_amount');
    //     $paidAmount  = (clone $query)->where('type', 1)->sum('paid_amount');

    //     $perPage = 20; // 默认值
    //     $allowedPerPages = [20, 30, 50]; // 允许的选项
        
    //     if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
    //         $perPage = (int)$request->per_page;
    //     }
    //     // --- [修改结束] ---

    //     $invoices = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
    //     // appends 确保分页链接中携带当前搜索条件和 per_page 设置
    //     $invoices->appends($request->only(['group_id', 'per_page','agency_id','staff_id']));

    //     $departments = AccountDepartment::get();
    //     $banks = Bank::where("is_active",1)->get();
    //     $agencies = Agency::where("is_active",1)->get();
    //     $staffs = Staff::where("is_active",1)->get();

    //     $accounts = Account::where('is_active', 1)->get();
    //     $partners = AccountPartner::get(); // 取引先
    //     $taxes = AccountTax::get();       // 税区分

    //     $accountConfig = AccountConfig::first();
    //     $receiveAccount = Account::where('id', $accountConfig->account_mgj_id)->where('is_active', 1)->first();
    //     $depositAccount = Account::where('id', $accountConfig->account_deposit_id)->where('is_active', 1)->first();
    //     $spmsgAccount = Account::where('id', $accountConfig->account_spmsg_id)->where('is_active', 1)->first();

    //     return view('masters.invoices.index', compact('invoices', 'groupId','banks','agencies','staffs','totalAmount','paidAmount','accounts','partners','taxes','departments','receiveAccount','depositAccount','spmsgAccount'));
    // }
    
    public function index(Request $request)
    {
        $query = Invoice::query();
        
        if ($request->filled('agency_id') && $request->agency_id) {
            $query->where('agency_id', $request->agency_id);
        }
    
        if ($request->filled('staff_id') && $request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }
    
        if ($request->filled('payment_status') && count($request->payment_status) > 0) {
            $query->whereIn('payment_status', $request->payment_status);
        }
    
        if ($request->filled('target_date') && $request->target_date) {
            $end_date = date('Y-m-d', time());
            if ($request->days > 0) {
                $end_date = date('Y-m-d', strtotime($request->target_date . ' +' . $request->days . ' day'));
            }
            
            if ($request->date_type == "operation") {
                $query->where('operation_date', '>=', $request->target_date)
                      ->where('operation_date', '<=', $end_date);
            } elseif ($request->date_type == "billing") {
                $query->where('invoice_date', '>=', $request->target_date)
                      ->where('invoice_date', '<=', $end_date);
            } else {
                $headers = PaymentHeader::where('payment_date', '>=', $request->target_date)
                                        ->where('payment_date', '<=', $end_date)
                                        ->pluck('id')->toArray();
                $ids = PaymentDetail::whereIn('payment_header_id', $headers)->pluck('invoice_id')->toArray();
                $query->whereIn('id', $ids);
            }
        }
    
        $totalAmount = (clone $query)->where('type', 1)->sum('total_amount');
        $paidAmount  = (clone $query)->where('type', 1)->sum('paid_amount');
    
        $perPage = 20;
        $allowedPerPages = [20, 30, 50];
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
    
        $invoices = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $invoices->appends($request->only(['per_page', 'agency_id', 'staff_id']));
    
        $departments = AccountDepartment::get();
        $banks = Bank::where("is_active", 1)->get();
        $agencies = Agency::where("is_active", 1)->get();
        $staffs = Staff::where("is_active", 1)->get();
    
        $accounts = Account::where('is_active', 1)->get();
        $partners = AccountPartner::get();
        $taxes = AccountTax::get();
    
        $accountConfig = AccountConfig::first();
        $receiveAccount = Account::where('id', $accountConfig->account_mgj_id)->where('is_active', 1)->first();
        $depositAccount = Account::where('id', $accountConfig->account_deposit_id)->where('is_active', 1)->first();
        $spmsgAccount = Account::where('id', $accountConfig->account_spmsg_id)->where('is_active', 1)->first();
    
        return view('masters.invoices.index', compact('invoices', 'banks', 'agencies', 'staffs', 'totalAmount', 'paidAmount', 'accounts', 'partners', 'taxes', 'departments', 'receiveAccount', 'depositAccount', 'spmsgAccount'));
    }

    public function sumInvoice(Request $request)
    {
        $groupId = $request->query('group_id');

        if (! $groupId) {
            return redirect()->back()->with([
                'error' => 'グループIDが指定されていません。',
                'alert-type' => 'danger'
            ]);
        }

        // if (! auth()->user()->canAccessGroup($groupId)) {
        //     abort(403, 'アクセス権限がありません。');
        // }

        $query = Invoice::where('group_id', $groupId);
        if ($request->filled('agency_id') && $request->agency_id) {
            $query->where('agency_id', $request->agency_id);
        }

        if ($request->filled('staff_id') && $request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('payment_status') && count($request->payment_status)>0) {
            $query->wherein('payment_status', $request->payment_status);
        }

        if ($request->filled('target_date') && $request->target_date) {
            $end_date = date('Y-m-d',time());
            if($request->days > 0){
                $end_date = date('Y-m-d', strtotime($request->target_date . ' +'.$request->days.' day'));
            }
            
            if($request->date_type== "operation"){
                $query->where('operation_date','>=', $request->target_date)->where('operation_date','<=', $end_date);
            }elseif($request->date_type== "billing"){
                $query->where('invoice_date','>=', $request->target_date)->where('invoice_date','<=', $end_date);
            }else{
                $headers = PaymentHeader::where('group_id', $groupId)->where('payment_date','>=', $request->target_date)->where('payment_date','<=', $end_date)->pluck('id')->toArray();
                $ids = PaymentDetail::whereIn('payment_header_id', $headers)->pluck('invoice_id')->toArray();
                $query->wherein('id', $ids);

            }

        }

        $totalAmount = (clone $query)->where('type', 1)->sum('total_amount');
        $paidAmount  = (clone $query)->where('type', 1)->sum('paid_amount');


        $invoices = $query->orderBy('agency_id', 'desc')->orderBy('invoice_date', 'desc')->get();
    

        $departments = AccountDepartment::get();
        $banks = Bank::where("is_active",1)->get();
        $agencies = Agency::where("is_active",1)->get();
        $staffs = Staff::where("is_active",1)->get();

        $accounts = Account::where('is_active', 1)->get();
        $partners = AccountPartner::get(); // 取引先
        $taxes = AccountTax::get();       // 税区分

        return view('masters.invoices.sum', compact('invoices', 'groupId','banks','agencies','staffs','totalAmount','paidAmount','accounts','partners','taxes','departments'));
    }

    public function excel(Request $request)
    {
        $groupId = $request->query('group_id');

        if (! $groupId) {
            return redirect()->back()->with([
                'error' => 'グループIDが指定されていません。',
                'alert-type' => 'danger'
            ]);
        }

        $query = Invoice::where('group_id', $groupId);
        if ($request->filled('agency_id') && $request->agency_id) {
            $query->where('agency_id', $request->agency_id);
        }

        if ($request->filled('staff_id') && $request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('payment_status') && count($request->payment_status)>0) {
            $query->wherein('payment_status', $request->payment_status);
        }

        if ($request->filled('target_date') && $request->target_date) {
            $end_date = date('Y-m-d',time());
            if($request->days > 0){
                $end_date = date('Y-m-d', strtotime($request->target_date . ' +'.$request->days.' day'));
            }
            
            if($request->date_type== "operation"){
                $query->where('operation_date','>=', $request->target_date)->where('operation_date','<=', $end_date);
            }elseif($request->date_type== "billing"){
                $query->where('invoice_date','>=', $request->target_date)->where('invoice_date','<=', $end_date);
            }else{
                $headers = PaymentHeader::where('group_id', $groupId)->where('payment_date','>=', $request->target_date)->where('payment_date','<=', $end_date)->pluck('id')->toArray();
                $ids = PaymentDetail::whereIn('payment_header_id', $headers)->pluck('invoice_id')->toArray();
                $query->wherein('id', $ids);

            }

        }

        $totalAmount = (clone $query)->where('type', 1)->sum('total_amount');
        $paidAmount  = (clone $query)->where('type', 1)->sum('paid_amount');
        $company = UserCompanyInfo::first();


        $invoices = $query->orderBy('agency_id', 'desc')->orderBy('invoice_date', 'desc')->get();
        $datas = [
            'invoices' => $invoices,
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'company' => $company,
            'group_id' => $groupId,
        ];
    
        $filename = "請求書マスター_" . date('YmdHis') . ".xlsx";
        if($request->sum==1){
            $view = 'masters.invoices.excel-sum';
            
        }else{
            $view = 'masters.invoices.excel';
        }
        return Excel::download(new InvoicesExport($datas,$view), $filename);



        try {
            // 1. 渲染 HTML
            $html = View::make($view, $datas)->render();
            

            // 2. 初始化 Browsershot
            // D:\Google\Chrome\Application
            $browsershot = Browsershot::html($html)
                ->paperSize(210, 297, 'mm')
                ->margins(10, 15, 15, 15) // 使用推荐的 margins 方法
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
            $filename = "貸借対照表". '.pdf';

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

    public function create(Request $request)
    {
        $groupId = $request->query('group_id');

        // if (! $groupId || ! auth()->user()->canAccessGroup($groupId)) {
        //     abort(403);
        // }
        $products = Product::get();
        $banks = Bank::where("is_active",1)->get();
        $currencies = Currency::select('currency_code', 'id')->distinct()->orderBy('currency_code')->get(); 
        $agencies = Agency::where("is_active",1)->get();
        $staffs = Staff::where("is_active",1)->get();
        
        $autoFillData = [
            'reservation_id' => $groupId,
            'agency_id' => null,
            'agency_detail' => '',
            'operation_date' => null,
            'group_name' => '',
        ];
        if ($groupId) {
            $groupInfo = GroupInfo::find($groupId);
            if ($groupInfo) {
                $autoFillData['reservation_id'] = $groupInfo->id;
                
                $autoFillData['group_name'] = $groupInfo->group_name ?? '';
                
                if ($groupInfo->agency) {
                    $agency = Agency::where('agency_name', $groupInfo->agency)->first();
                    if ($agency) {
                        $autoFillData['agency_id'] = $agency->id;
                        if ($agency->bill_to) {
                            $autoFillData['agency_detail'] = $agency->bill_to;
                        }
                    } else {
                        $autoFillData['agency_detail'] = $groupInfo->agency;
                    }
                }
                
                if ($groupInfo->start_date) {
                    $autoFillData['operation_date'] = $groupInfo->start_date instanceof Carbon 
                        ? $groupInfo->start_date->format('Y-m-d') 
                        : $groupInfo->start_date;
                } else {
                    $firstItinerary = DailyItinerary::where('group_info_id', $groupId)
                        ->orderBy('date', 'asc')
                        ->first();
                    if ($firstItinerary) {
                        $autoFillData['operation_date'] = $firstItinerary->date;
                    }
                }
            }
        }
        
        return view('masters.invoices.create', compact('groupId','currencies','products','banks','agencies','staffs','autoFillData'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'group_id' => 'nullable|integer',
        'staff_id' => 'nullable|integer',
        'reservation_id' => 'nullable|integer',
        'is_locked' => 'nullable|integer',
        'agency_id' => 'nullable|integer',
        'agency_detail' => 'required|string|max:250',
        'operation_date' => 'nullable|date',
        'bank_id' => 'required|integer',
        'billing_title' => 'nullable|string|max:200',
        'tax_mode' => 'required|in:1,2',
        'language' => 'required|in:1,2',
        'type' => 'required|in:1,2',
        'currency_code' => 'required|string|max:50',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:invoice_date',
        'notes' => 'nullable|string|max:65535',
        
        // 基础结构验证：items 必须存在且是数组
        'items' => 'required|array',
        
        // 注意：这里暂时移除 min:1，因为我们稍后要过滤空行
        // 对每个子项进行类型验证，但暂时不强制 required (除了 description 用于判断是否存在)
        'items.*.description' => 'nullable|string|max:300', 
        'items.*.quantity' => 'nullable|numeric|min:0', // 允许空，稍后逻辑处理
        'items.*.unit_price' => 'nullable|numeric|min:0',
        'items.*.tax_rate' => 'nullable|numeric|in:-1,-2,8,10',
        'items.*.display_order' => 'nullable|integer',
    ], [
        'due_date.after_or_equal' => '支払期日は請求日以降にしてください。',
        'items.*.tax_rate.in' => '税率は 免税、非課税、8、10 のいずれかにしてください。',
        // 自定义错误：如果最终有效行为 0，我们会手动抛出这个异常
    ]);

    if (isset($validated['items']) && is_array($validated['items'])) {
        $validItems = [];
        
        foreach ($validated['items'] as $item) {
            // 判断标准：如果 description 为空或只包含空格，则视为无效行，直接跳过
            if (empty(trim($item['description'] ?? ''))) {
                continue; 
            }

            // 如果 description 有值，则强制检查其他关键字段是否也有值
            if (empty($item['quantity']) || empty($item['unit_price'])) {
                // 如果品名有值，但数量或单价缺失，返回具体错误
                // 注意：这里需要知道是第几行报错比较困难，通常直接抛出一个通用错误
                return back()->withErrors(['items' => '品目名が入力されている場合、数量と単価も必須です。'])
                            ->withInput();
            }

            // 将有效行加入新数组
            $validItems[] = $item;
        }



        // 3. 检查是否至少有一行有效数据
        if (count($validItems) === 0) {
            return back()->withErrors(['items' => '明細は最低1行必要です。'])
                        ->withInput();
        }

        // 4. 覆盖 validated 数组中的 items 为过滤后的干净数据
        $validated['items'] = $validItems;
        
        // 可选：重新索引数组键名 (0, 1, 2...)，防止提交时带有稀疏数组键名导致问题
        $validated['items'] = array_values($validated['items']);
    } else {
        // 如果 items 完全不存在或为空数组
        return back()->withErrors(['items' => '明細は最低1行必要です。'])
                    ->withInput();
    }


    // 重置索引，避免跳号
    $validated['items'] = array_values($validated['items']);

    //查询$validated['invoice_date']在currencies表中rate_valid_from之后，在rate_valid_to之前的数据
    $currency = Currency::where('currency_code', $validated['currency_code']) // 通常还需要匹配币种代码
    ->where('rate_valid_from', '<=', $validated['invoice_date'])
    ->where('rate_valid_to', '>=', $validated['invoice_date'])
    ->orderBy('rate_valid_from', 'desc') // 如果有重叠，取最新生效的
    ->first();
    if (!$currency) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => '指定の通貨に対する指定の日付の為替レートが見つかりません。']);
    }

    DB::beginTransaction();
    try {
        // === 第一步：按税率分组原始输入金额（不分舍入）===
        $groups = [];
        $subtotalAmount = 0;
        $totalTaxAmount = 0;
        $taxGroups = []; // 用于 invoice_tax_summary
        foreach ($validated['items'] as $item) {
            $quantity = (float)$item['quantity'];
            $unitPrice = (float)$item['unit_price'];
            $taxRate = (float)$item['tax_rate'];
            $rateKey = number_format($taxRate, 2, '.', '');

            if (!isset($groups[$rateKey])) {
                $groups[$rateKey] = [
                    'tax_rate' => $taxRate,
                    'total_input_raw' => 0, // 原始 quantity * unit_price 总和（未取整）
                ];
            }
            $groups[$rateKey]['total_input_raw'] += $quantity * $unitPrice;
        }



        foreach ($groups as $rateKey => $group) {
            $taxRate = $group['tax_rate'];
            $totalInputRaw = $group['total_input_raw'];

            if ($validated['tax_mode'] == 1) {//税入
            
                $totalIncl = round($totalInputRaw);
                if ($taxRate > 0) {
                    $baseAmount = $totalIncl / (1 + $taxRate / 100);
                    $totalExcl = (int)ceil($baseAmount); // ✅ 向上取整
                    $taxAmount = $totalIncl - $totalExcl; // 保证 totalIncl = totalExcl + taxAmount
                }else{
                    $taxAmount = 0;
                } 
                $subtotalAmount += $totalIncl;
                $totalTaxAmount += $taxAmount;

                $taxGroups[$rateKey] = [
                    'subtotal' => $totalIncl,
                    'tax_amount' => $taxAmount,
                    'total_with_tax' => $totalIncl,
                ];
            } else {//税别
                $totalExcl = round($totalInputRaw);
                $taxAmount = ($taxRate > 0) ? (int)round($totalExcl * ($taxRate / 100)) : 0;
                // 累加到全局
                $subtotalAmount += $totalExcl;
                $totalTaxAmount += $taxAmount;

                $taxGroups[$rateKey] = [
                    'subtotal' => $totalExcl,
                    'tax_amount' => $taxAmount,
                    'total_with_tax' => $totalExcl + $taxAmount,
                ];
            }


        }

        // === 第四步：插入 invoices 主表 ===
        $invoiceId = DB::table('invoices')->insertGetId([
            'group_id' => $validated['group_id'],
            'bank_id' => $validated['bank_id'],
            'is_locked' => $validated['is_locked'],
            'invoice_number' => $this->generateInvoiceNumber(), // 确保该方法存在
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'billing_title' => $validated['billing_title'],
            'subtotal_amount' => $subtotalAmount,
            'tax_amount' => $totalTaxAmount,
            'total_amount' => $validated['tax_mode']==2 ? $subtotalAmount+$totalTaxAmount : $subtotalAmount,
            'tax_mode' => $validated['tax_mode'],
            'language' => $validated['language'],
            'currency_code' => $validated['currency_code'],
            'exchange_rate' => $currency->rate_to_jpy,
            'pdf_template_id' => null,
            'pdf_file_path' => null,
            'type' => $validated['type'],
            'notes' => $validated['notes'],
            'created_at' => now(),
            'updated_at' => now(),
            'staff_id' => $validated['staff_id'],

            'agency_id' => $validated['agency_id'],
            'agency_detail' => $validated['agency_detail'],
            'operation_date' => $validated['operation_date'],
            'reservation_id' => $validated['reservation_id'],
        ]);

        // === 第五步：插入 invoice_items（仅存原始输入，不参与税务计算）===
        $itemsToInsert = [];
        foreach ($validated['items'] as $index => $item) {
            Product::firstOrCreate(
                ['name' => $item['description']], 
                ['language' => $validated['language']] 
            );
            $itemsToInsert[] = [
                'invoice_id' => $invoiceId,
                'line_number' => $index + 1,
                'description' => $item['description'],
                'quantity' => (float)$item['quantity'],
                'unit_price' => (float)$item['unit_price'],
                'amount' => round((float)$item['quantity'] * (float)$item['unit_price'], 2),
                'tax_rate' => (float)$item['tax_rate'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('invoice_items')->insert($itemsToInsert);

        // === 第六步：插入 invoice_tax_summary（只存 8% 和 10%，过滤 0%）===
        $summaryToInsert = [];
        foreach ($taxGroups as $taxRate => $data) {
            if ((float)$taxRate > 0) { // ✅ 只保留应税项目
                $summaryToInsert[] = [
                    'invoice_id' => $invoiceId,
                    'tax_rate' => $taxRate,
                    'subtotal' => (int)round($data['subtotal']),
                    'tax_amount' => (int)round($data['tax_amount']),
                    'total_with_tax' => (int)round($data['total_with_tax']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($summaryToInsert)) {
            DB::table('invoice_tax_summary')->insert($summaryToInsert);
        }

        DB::commit();
        DB::setDefaultConnection('mysql'); 
        // GenerateRequestPdfJob::dispatch($invoiceId,auth()->user()->id);
        return redirect()->route('masters.invoices.edit', [
                'invoice' => $invoiceId, // 假设你的路由参数名是 {invoice} 或 {id}
                'group_id' => $validated['group_id'] // 保留 group_id 参数，防止筛选条件丢失
            ])
            ->with('success', '請求書を登録しました。');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('請求書作成エラー: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'システムエラーが発生しました。管理者にお問い合わせください。']);
    }
}

    /**
     * 生成请求书编号（示例：INV-20260309-001）
     */
    private function generateInvoiceNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $lastInvoice = DB::table('invoices')
            ->where('invoice_number', 'like', "INV-{$dateStr}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNum = (int)substr($lastInvoice->invoice_number, -3);
            $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNum = '001';
        }

        return "INV-{$dateStr}-{$newNum}";
    }

    public function show(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $groupId = $request->query('group_id');
        $items = InvoiceItem::where('invoice_id', $invoice->id)->get();
        $taxSummary = DB::table('invoice_tax_summary')->where('invoice_id', $invoice->id)->get();
        $banks = Bank::where("is_active",1)->get();
        $agencies = Agency::where("is_active",1)->get();
        $staffs = Staff::where("is_active",1)->get();
        return view('masters.invoices.show', compact('invoice', 'groupId','items','banks','agencies','staffs'));
    }

    public function edit(Request $request, $id)
    {
        $groupId = $request->query('group_id');

        $invoice = Invoice::findOrFail($id);
        $items = InvoiceItem::where('invoice_id', $invoice->id)->get();
        $currencies = Currency::select('currency_code', 'id')->distinct()->orderBy('currency_code')->get();
        $products = Product::get();
        $banks = Bank::where("is_active",1)->get();
        $agencies = Agency::where("is_active",1)->get();
        $staffs = Staff::where("is_active",1)->get();
        $summary_10 = InvoiceTaxSummary::where('invoice_id', $invoice->id)->where('tax_rate', 10)->first();
        $summary_8 = InvoiceTaxSummary::where('invoice_id', $invoice->id)->where('tax_rate', 8)->first();
        $non_taxable = InvoiceItem::where('invoice_id', $invoice->id)->where('tax_rate','<', 0)->sum('amount');

        $details = PaymentDetail::where('invoice_id', $invoice->id)->orderBy("created_at","desc")->get();

        return view('masters.invoices.edit', compact('invoice', 'groupId','items','currencies','products','banks','agencies','staffs','summary_10','summary_8','non_taxable','details'));
    }

    public function update(Request $request, int $id)
    {
        $invoice = Invoice::findOrFail($id);
        if($invoice->is_locked){
            return redirect()->route('masters.invoices.show', ['invoice' => $invoice, 'group_id' => request('group_id')])
                ->with('error', 'この請求書は編集できません。');
        }

        
        // === 1. 验证输入（与 store 一致）===
        $validated = $request->validate([
            'reservation_id' => 'nullable|integer',
            'agency_id' => 'nullable|integer',
            'staff_id' => 'nullable|integer',
            'agency_detail' => 'required|string|max:250',
            'operation_date' => 'nullable|date',
            'group_id' => 'nullable|integer',
            'bank_id' => 'required|integer',
            
            'billing_title' => 'nullable|string|max:200',
            'tax_mode' => 'required|in:1,2', // 1=内税, 2=外税
            'language' => 'required|in:1,2', // 1=日语, 2=英语
            'type' => 'required|in:1,2',
            'currency_code' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:65535',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:300',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|in:-1,-2,8,10',
            'items.*.display_order' => 'required|integer|min:1',
        ], [
            'items.required' => '明細は最低1行必要です。',
            'due_date.after_or_equal' => '支払期日は請求日以降にしてください。',
        ]);

        $validated['items'] = array_values($validated['items']); // 重置索引

        if($validated['type'] == 2){
            if($invoice->paid_amount>0){
                return redirect()->back()
                ->withInput()
                ->withErrors(['error' => '入金済みデータは仮請求書に変更できません']);
            }
        }

        //查询$validated['invoice_date']在currencies表中rate_valid_from之后，在rate_valid_to之前的数据
        $currency = Currency::where('currency_code', $validated['currency_code']) // 通常还需要匹配币种代码
        ->where('rate_valid_from', '<=', $validated['invoice_date'])
        ->where('rate_valid_to', '>=', $validated['invoice_date'])
        ->orderBy('rate_valid_from', 'desc') // 如果有重叠，取最新生效的
        ->first();
        if (!$currency) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => '指定の通貨に対する指定の日付の為替レートが見つかりません。']);
        }

        DB::beginTransaction();
        try {
            // === 2. 校验发票是否存在 ===
            if (!$invoice) {
                throw new \Exception('請求書が見つかりません。');
            }

            // === 3. 按税率分组原始输入金额（不分舍入）===
        $groups = [];
        $subtotalAmount = 0;
        $totalTaxAmount = 0;
        $taxGroups = []; // 用于 invoice_tax_summary
        foreach ($validated['items'] as $item) {
            $quantity = (float)$item['quantity'];
            $unitPrice = (float)$item['unit_price'];
            $taxRate = (float)$item['tax_rate'];
            $rateKey = number_format($taxRate, 2, '.', '');

            if (!isset($groups[$rateKey])) {
                $groups[$rateKey] = [
                    'tax_rate' => $taxRate,
                    'total_input_raw' => 0, // 原始 quantity * unit_price 总和（未取整）
                ];
            }
            $groups[$rateKey]['total_input_raw'] += $quantity * $unitPrice;
        }



        foreach ($groups as $rateKey => $group) {
            $taxRate = $group['tax_rate'];
            $totalInputRaw = $group['total_input_raw'];

            if ($validated['tax_mode'] == 1) {//税入
            
                $totalIncl = round($totalInputRaw);
                if ($taxRate > 0) {
                    $baseAmount = $totalIncl / (1 + $taxRate / 100);
                    $totalExcl = (int)ceil($baseAmount); // ✅ 向上取整
                    $taxAmount = $totalIncl - $totalExcl; // 保证 totalIncl = totalExcl + taxAmount
                }else{
                    $taxAmount = 0;
                } 
                $subtotalAmount += $totalIncl;
                $totalTaxAmount += $taxAmount;

                $taxGroups[$rateKey] = [
                    'subtotal' => $totalIncl,
                    'tax_amount' => $taxAmount,
                    'total_with_tax' => $totalIncl,
                ];
            } else {//税别
                $totalExcl = round($totalInputRaw);
                $taxAmount = ($taxRate > 0) ? (int)round($totalExcl * ($taxRate / 100)) : 0;
                // 累加到全局
                $subtotalAmount += $totalExcl;
                $totalTaxAmount += $taxAmount;

                $taxGroups[$rateKey] = [
                    'subtotal' => $totalExcl,
                    'tax_amount' => $taxAmount,
                    'total_with_tax' => $totalExcl + $taxAmount,
                ];
            }


        }


            // === 6. 更新 invoices 主表 ===
            DB::table('invoices')->where('id', $id)->update([
                'group_id' => $validated['group_id'],
                'bank_id' => $validated['bank_id'],
                'type' => $validated['type'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'billing_title' => $validated['billing_title'],
                'subtotal_amount' => $subtotalAmount,
                'tax_amount' => $totalTaxAmount,
                'total_amount' => $validated['tax_mode']==2 ? $subtotalAmount+$totalTaxAmount : $subtotalAmount,
                'tax_mode' => $validated['tax_mode'],
                'language' => $validated['language'],
                'currency_code' => $validated['currency_code'],
                'exchange_rate' => $currency->rate_to_jpy,
                'pdf_file_path' => '',
                'notes' => $validated['notes'],
                'updated_at' => now(),
                'reservation_id' => $validated['reservation_id'],
                'agency_id' => $validated['agency_id'],
                'agency_detail' => $validated['agency_detail'],
                'operation_date' => $validated['operation_date'],
                'staff_id' => $validated['staff_id'],
            ]);

            // === 7. 删除旧的明细和汇总 ===
            DB::table('invoice_items')->where('invoice_id', $id)->delete();
            DB::table('invoice_tax_summary')->where('invoice_id', $id)->delete();

            // === 8. 插入新的 invoice_items（仅原始输入）===
            $itemsToInsert = [];
            foreach ($validated['items'] as $index => $item) {
                Product::firstOrCreate(
                    ['name' => $item['description']], 
                    ['language' => $validated['language']] 
                );
                $itemsToInsert[] = [
                    'invoice_id' => $id,
                    'line_number' => $index + 1,
                    'description' => $item['description'],
                    'quantity' => (float)$item['quantity'],
                    // 'unit' => $item['unit'] ?? null,
                    'unit_price' => (float)$item['unit_price'],
                    'amount' => round((float)$item['quantity'] * (float)$item['unit_price'], 2),
                    'tax_rate' => (float)$item['tax_rate'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($itemsToInsert)) {
                DB::table('invoice_items')->insert($itemsToInsert);
            }

            // === 9. 插入新的 invoice_tax_summary（仅 8% 和 10%）===
            $summaryToInsert = [];
            foreach ($taxGroups as $taxRate => $data) {
                if ((float)$taxRate > 0) { // ✅ 过滤掉 0%
                    $summaryToInsert[] = [
                        'invoice_id' => $id,
                        'tax_rate' => $taxRate,
                        'subtotal' => (int)round($data['subtotal']),
                        'tax_amount' => (int)round($data['tax_amount']),
                        'total_with_tax' => (int)round($data['total_with_tax']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($summaryToInsert)) {
                DB::table('invoice_tax_summary')->insert($summaryToInsert);
            }

            DB::commit();
            DB::setDefaultConnection('mysql'); 
            // GenerateRequestPdfJob::dispatch($id,auth()->user()->id);
            return redirect()->route('masters.invoices.edit', [
                'invoice' => $invoice->id, // 假设你的路由参数名是 {invoice} 或 {id}
                'group_id' => $validated['group_id'] // 保留 group_id 参数，防止筛选条件丢失
            ])
            ->with('success', '請求書を更新しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('請求書更新エラー (ID: ' . $id . '): ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => '更新中にエラーが発生しました。']);
        }
    }

    public function destroy(Request $request, $id)
    {

        $invoice = Invoice::findOrFail($id);
        $groupId = $request->query('group_id');

        // if (! $groupId || $invoice->group_id != $groupId) {
        //     abort(403);
        // }

        if($invoice->paid_amount>0){
            return redirect()->back()
            ->withInput()
            ->withErrors(['error' => '入金済みデータは削除できません']);
        }

        $invoice->delete();

        return redirect()
            ->route('masters.invoices.index', ['group_id' => $groupId])
            ->with([
                'success' => '請求書を削除しました。',
                'alert-type' => 'success'
            ]);
    }

    public function generatePdf(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $path = $invoice->pdf_file_path;

        // 1. 检查文件是否存在 (防御性编程)
        if (!$path || !Storage::disk('public')->exists($path)) {
            return redirect()->back()->with([
                'error' => 'PDF はバックグラウンドで生成中です。完了まで 5〜10 秒ほどかかる見込みですので、しばらくしてから再度開いてください。',
                'alert-type' => 'danger'
            ]);
        }

        

        // 2. 直接下载
        // 第二个参数可以自定义下载时的文件名，如果不传则使用原文件名
        $fileName = basename($path); 
        
        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk('public');

        return $storage->download($path, $fileName);
    }

    /*
        composer require spatie/browsershot
        npm install puppeteer
    */
    public function generatePdf_tmp(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $items = $invoice->items;
        $summary_10 = InvoiceTaxSummary::where('invoice_id', $invoice->id)->where('tax_rate', 10)->first();
        $symmary_8 = InvoiceTaxSummary::where('invoice_id', $invoice->id)->where('tax_rate', 8)->first();
        $non_taxable = InvoiceItem::where('invoice_id', $invoice->id)->where('tax_rate', 0)->sum('amount');
        // 1. 准备数据 (保持你原有的数据结构不变)
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
            'summary_8' => $symmary_8,
            'items' => $items,

            'bank' => (object)[
                'bank_name' => '●●●●銀行',
                'branch_name' => '●●●●●●支店',
                'account_number' => '●●●●●●●●',
                'account_holder' => '●●●●●●●●',
            ],
            'company' => (object)[
                'name' => '株式会社〇〇〇',
                'postal_code' => '123-4567',
                'address' => '〇〇県〇〇市〇〇町1－2－3',
                'phone' => '03-1234-5678',
                'fax' => '09-1234-5679',
                'contact' => '△△△△',
            ],
            'customer' => (object)[
                'name' => '〇〇〇〇〇〇株式会社株式会社株式会社株式会社株式会社株式会社株式会社
                株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社
                株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社株式会社
                株式会社株式会社株式会社株式会社株式会社株式会社株式会社',   
            ]

        ];

        try {
            // 1. 渲染 HTML
            if($invoice->language == 1){
                $html = View::make('masters.invoices.template_ja', $data)->render();
            }else{
                $html = View::make('masters.invoices.template_en', $data)->render();
            }
            

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
                 $browsershot->noSandbox();
                // [Linux/生产环境] 取消下面这行的注释
                // $browsershot->addChromiumArguments(['--no-sandbox', '--disable-setuid-sandbox']);
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
            $filename = 'invoice_' . $data['invoice']->invoice_number . '.pdf';

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

    public function toggleLock($id)
    {
        $invoice = Invoice::findOrFail($id);
        // 切换状态
        $invoice->is_locked = !$invoice->is_locked;
        $invoice->locked_user = session('staff_name', '未ログイン');
        $invoice->locked_at = now();
        $invoice->save();

        return response()->json([
            'success' => true,
            'is_locked' => $invoice->is_locked,
            'message' => $invoice->is_locked ? 'ロックしました' : 'ロックを解除しました'
        ]);
    }

    public function bulkToggleLock(Request $request)
    {
        // 1. 验证输入
        $validated = $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'required|integer|exists:invoices,id',
            'locked' => 'required|boolean', // 1 = 锁定，0 = 解锁
        ]);

        $ids = $validated['invoice_ids'];
        $lockState = (bool) $validated['locked'];
        $groupId = $request->query('group_id'); // 虽然前端没传，但为了安全最好校验

        try {
            // 2. 执行批量更新
            // 注意：这里直接操作 DB 表以确保效率，也可以使用 Model::whereIn(...)->update(...)
            $affectedCount = DB::table('invoices')
                ->whereIn('id', $ids)
                ->update([
                    'is_locked' => $lockState ? 1 : 0,
                    'updated_at' => now(),
                    'locked_user_id' => session('staff_name', '未ログイン'),
                    'locked_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => $lockState ? '選択した請求書をロックしました。' : '選択した請求書のロックを解除しました。',
                'count' => $affectedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk Toggle Lock Error: ' . $e->getMessage(), ['ids' => $ids]);
            return response()->json([
                'success' => false,
                'message' => '処理中にエラーが発生しました。'
            ], 500);
        }
    }

      /**
     * 批量下载 PDF (打包为 ZIP)
     */
    public function bulkPdf(Request $request)
    {
        // 1. 验证输入
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'required|integer|exists:invoices,id',
            //'group_id' => 'nullable|integer|exists:groups,id', // 假设你有 groups 表
        ]);

        $invoiceIds = $request->input('invoice_ids');
        $groupId = $request->input('group_id');

        // 2. 查询数据库 (加上 group_id 限制以确保数据安全)
        $query = Invoice::whereIn('id', $invoiceIds);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        // 如果是普通用户，通常还需要限制只能查自己的数据
        // $query->where('user_id', auth()->id()); 

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', '選択された請求書が見つかりませんでした。');
        }

        // 3. 优化体验：如果只选了 1 个，直接跳转下载，不打包
        if ($invoices->count() === 1) {
            $invoice = $invoices->first();
            if (!$invoice->pdf_file_path || !Storage::disk('public')->exists($invoice->pdf_file_path)) {
                return back()->with('error', 'PDF ファイルが生成されていません。');
            }
            // 重定向到单个下载路由
            return redirect()->route('masters.invoices.pdf', [
                'invoice' => $invoice,
                'group_id' => $groupId
            ]);
        }

        // 4. 多个文件：生成 ZIP 包
        return $this->createZipResponse($invoices, $groupId);
    }

    /**
     * 创建 ZIP 流响应
     */
    private function createZipResponse($invoices, $groupId)
    {
        $zipFileName = 'invoices_batch_' . date('Ymd_His') . '.zip';

        return new StreamedResponse(function () use ($invoices, $zipFileName) {
            // 创建临时 ZIP 文件
            $zipPath = storage_path('app/temp/' . $zipFileName);
            
            // 确保 temp 目录存在
            if (!Storage::disk('local')->exists('temp')) {
                Storage::disk('local')->makeDirectory('temp');
            }
            // 兼容本地路径操作
            $fullZipPath = storage_path('app/temp/' . $zipFileName);
            if (!file_exists(dirname($fullZipPath))) {
                mkdir(dirname($fullZipPath), 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                abort(500, 'ZIP ファイルの作成に失敗しました。');
            }

            $addedCount = 0;

            foreach ($invoices as $invoice) {
                if (!$invoice->pdf_file_path) {
                    continue; // 跳过没有 PDF 的发票
                }

                $storagePath = $invoice->pdf_file_path;
                
                // 检查文件是否存在
                if (!Storage::disk('public')->exists($storagePath)) {
                    continue; // 或者记录日志，跳过缺失文件
                }

                // 构建 ZIP 内部的文件名 (例如: INV-2026-001_株式会社テスト.pdf)
                // 清理文件名中的非法字符
                $safeName = preg_replace('/[\/\\\\:*?"<>|]/', '_', $invoice->invoice_number);
                $internalName = "{$safeName}.pdf";

                // 获取文件内容并添加到 ZIP
                // 注意：如果文件非常大，这里可能会消耗内存。
                // 对于超大文件，需要使用 addFile 配合 stream，但 Storage::path 需要 public disk 映射正确
                $fileContent = Storage::disk('public')->get($storagePath);
                
                if ($fileContent !== false) {
                    $zip->addFromString($internalName, $fileContent);
                    $addedCount++;
                }
            }

            $zip->close();

            // 输出文件流
            if (file_exists($fullZipPath)) {
                // 读取并输出
                readfile($fullZipPath);
                
                // 删除临时文件
                unlink($fullZipPath);
            } else {
                abort(500, '一時ファイルの読み込みに失敗しました。');
            }

        }, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }


    public function checkPdfStatus($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // 检查文件是否真的存在于磁盘上 (双重确认)
        $hasFile = !empty($invoice->pdf_file_path) && \Storage::disk('public')->exists($invoice->pdf_file_path);

        if ($hasFile) {
            return response()->json([
                'ready' => true,
                'url' => '/storage/' . $invoice->pdf_file_path
            ]);
        }

        return response()->json([
            'ready' => false
        ]);
    }

    public function duplicate(Request $request, $id)
    {
        // 1. 执行事务，并将结果赋值给 $result 变量
        $newInvoice=DB::transaction(function () use ($id, $request) {
            // 获取原始数据
            $originalInvoice = Invoice::findOrFail($id);
            $originalItems = InvoiceItem::where('invoice_id', $originalInvoice->id)->get();
            $originalTaxSummaries = InvoiceTaxSummary::where('invoice_id', $originalInvoice->id)->get();

            // 复制主表
            $newInvoice = $originalInvoice->replicate();
            
            // 修改字段
            $newInvoice->invoice_number = $this->generateInvoiceNumber();
            $newInvoice->paid_amount = 0;
            $newInvoice->is_locked = 0;
            $newInvoice->payment_status = 1;
            
            $newInvoice->save();

            // 复制明细
            foreach ($originalItems as $item) {
                $newItem = $item->replicate();
                $newItem->invoice_id = $newInvoice->id; 
                $newItem->save();
            }

            // 复制税务汇总
            foreach ($originalTaxSummaries as $tax) {
                $newTax = $tax->replicate();
                $newTax->invoice_id = $newInvoice->id; 
                $newTax->save();
            }
            return $newInvoice; 

        });

        // 2.由控制器方法返回事务的结果
        return redirect()->route('masters.invoices.edit', [
                'invoice' => $newInvoice->id, // 假设你的路由参数名是 {invoice} 或 {id}
                'group_id' => $newInvoice->group_id // 保留 group_id 参数，防止筛选条件丢失
            ]) ->with([
                'success' => '請求書のコピーが完了し、新規データとして保存されました。',
                'alert-type' => 'success'
            ]);
           
    }


    public function storeJournal(Request $request){ 

        DB::beginTransaction();
        try {

            $journal = new AccountJournalEntry();
            // A. 创建主表
            $entry = AccountJournalEntry::create([
                'posting_date'  => $request->date,
                'department_id' => $request->department,
                'source_type'   => $request->source_type,
                'remark'   => $request->summary,
                'source_id'     => $journal->generateAccountNumber(),
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
            ]);


            $debit = $request->debit;
            $linesData[] = [
                'journal_entry_id' => $entry->id,
                'side'             => 1, // 动态决定方向
                'account_id'       => $debit['account_id'],
                'sub_account_id'   => $debit['sub_subject_id'] ?? 0,
                'partner_id'       => $debit['partner_id'] ?? 0,
                'amount'           => $debit['amount'],
                'deal_date'           => $debit['deal_date'],
                'tax_type_id'      => $debit['tax_id'] ?? 0,
                'invoice_id'      => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $credit = $request->credit_lines;
           
            foreach ($credit as $creditLine) {
                $linesData[] = [
                    'journal_entry_id' => $entry->id,
                    'side'             => 2, // 动态决定方向
                    'account_id'       => $creditLine['account_id'],
                    'sub_account_id'   => $creditLine['sub_subject_id'] ?? 0,
                    'partner_id'       => $creditLine['partner_id'] ?? 0,
                    'amount'           => $creditLine['amount'],
                    'deal_date'           => $creditLine['deal_date'],
                    'tax_type_id'      => $creditLine['tax_id'] ?? 0,
                    'invoice_id'      => $creditLine['invoice_id'] ?? 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }


            AccountJournalLine::insert($linesData);

            DB::commit();


            return response()->json(['success' => 1]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('仕訳伝票作成エラー: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'システムエラーが発生しました。管理者にお問い合わせください。']);
        }
    }

    public function getJournal(Request $request){ 
        $journal = AccountJournalEntry::find($request->invoice_id);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $journal->id,
                'date' =>$journal->posting_date?->format('Y/m/d'),
                'source_type' => $journal->source_type,
                'summary' => $journal->remark,
                'department_id' => $journal->department_id,
                'department_name' => $journal->department->name ?? '-',
                'debit' => [
                    'account_name' => $journal->debit->account->name ?? '-',
                    'tax_name' => $journal->debit->taxType->name ?? '-',
                    'amount' => $journal->debit->amount,
                    'sub_subject_name' => $journal->debit->subAccount->name ?? '-',
                    'partner_name' => $journal->debit->partner->name ?? '-',
                    'deal_date' => $journal->debit->deal_date,
                ],
                'credit_lines' => $journal->creditLines->map(function ($line) {
                    return [
                        'account_name' => $line->account->name,
                        'tax_name' => $line->taxType->name,
                        'amount' => $line->amount,
                        'sub_subject_name' => $line->subAccount->name ?? '-',
                        'partner_name' => $line->partner->name ?? '-',
                        'deal_date' => $line->deal_date,
                    ];
                })
            ]
        ]);
    }
    
    
    
    
    
    
        
        
        
        
    public function batchExportPdf(Request $request)
    {
        $invoiceIds = $request->input('invoice_ids', []);
        
        if (empty($invoiceIds)) {
            return response()->json(['success' => false, 'message' => '請求書を選択してください。'], 400);
        }
        
        $invoices = Invoice::with(['items', 'bank', 'agency', 'staff'])
            ->whereIn('id', $invoiceIds)
            ->get();
        
        if ($invoices->isEmpty()) {
            return response()->json(['success' => false, 'message' => '請求書が見つかりません。'], 400);
        }
        
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_footer' => 10,
            'tempDir' => sys_get_temp_dir(),
            'fontDir' => [
                base_path('vendor/mpdf/mpdf/ttfonts'),
                storage_path('fonts'),
            ],
            'fontdata' => [
                'ipaexgothic' => ['R' => 'ipaexgothic.ttf', 'useOTL' => 0x80],
                'ipaexmincho' => ['R' => 'ipaexmincho.ttf', 'useOTL' => 0x80],
            ],
            'default_font' => 'ipaexgothic',
        ]);
        
        $mpdf->shrink_tables_to_fit = 0;
        $mpdf->keep_table_proportions = true;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        
        $firstPage = true;
        
        foreach ($invoices as $invoice) {
            $data = $this->preparePdfData($invoice);
            
            if ($invoice->language == 1) {
                $html = view('masters.invoices.template_ja', $data)->render();
            } else {
                $html = view('masters.invoices.template_en', $data)->render();
            }
            
            if ($firstPage) {
                $mpdf->WriteHTML($html);
                $firstPage = false;
            } else {
                $mpdf->AddPage();
                $mpdf->WriteHTML($html);
            }
        }
        
        $filename = 'invoices_' . date('Ymd_His') . '.pdf';
        return $mpdf->Output($filename, 'D');
    }
    
    public function generatePdfMpdf($id)
    {
        $invoice = Invoice::with(['items', 'bank', 'agency', 'staff'])->findOrFail($id);
        
        $data = $this->preparePdfData($invoice);
        
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,      // 左边距 15mm
            'margin_right' => 10,     // 右边距 15mm
            'margin_top' => 10,       // 上边距 20mm
            'margin_bottom' => 10,    // 下边距 20mm
            'margin_header' => 5,     // 页眉边距 5mm
            'margin_footer' => 10,    // 页脚边距 10mm
            'margin_footer' => 10,
            'tempDir' => sys_get_temp_dir(),
            'fontDir' => [
                storage_path('fonts'),
            ],
            'fontdata' => [
                'msyh' => [
                    'R' => 'msyh.ttf',
                    'B' => 'msyhbd.ttf',
                ],
                'genshin' => [
                    'R' => 'GenShinGothic-Normal.ttf',
                    'B' => 'GenShinGothic-Bold.ttf',
                ],
            ],
            'default_font' => 'msyh',
            
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'useSubstitutions'  => true,
        ]);
        
        
        // $mpdf = new Mpdf([
        //     'mode' => 'utf-8',
        //     'format' => 'A4',
        //     'margin_footer' => 10,
        //     'tempDir' => sys_get_temp_dir(),
        //     'fontDir' => [
        //         base_path('vendor/mpdf/mpdf/ttfonts'),
        //         storage_path('fonts'),
        //     ],
        //     'fontdata' => [
        //         'ipaexgothic' => [
        //             'R' => 'ipaexgothic.ttf',
        //             'B' => 'ipaexgothic.ttf',
        //             'useOTL' => 0x80,
        //         ],
        //         'ipaexmincho' => [
        //             'R' => 'ipaexmincho.ttf',
        //             'B' => 'ipaexmincho.ttf',
        //             'useOTL' => 0x80,
        //         ],
        //     ],
        //     'default_font' => 'ipaexgothic',
        // ]);
        
        $mpdf->shrink_tables_to_fit = 0;
        $mpdf->keep_table_proportions = true;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        
        if ($invoice->language == 1) {
            $html = view('masters.invoices.template_ja', $data)->render();
        } else {
            $html = view('masters.invoices.template_en', $data)->render();
        }
        
        $mpdf->WriteHTML($html);
        
        $filename = 'invoice_' . $invoice->invoice_number . '.pdf';
        return $mpdf->Output($filename, 'D');
    }
    
    private function preparePdfData($invoice)
    {
        $items = $invoice->items;
        $summary_10 = InvoiceTaxSummary::where('invoice_id', $invoice->id)->where('tax_rate', 10)->first();
        $summary_8 = InvoiceTaxSummary::where('invoice_id', $invoice->id)->where('tax_rate', 8)->first();
        $non_taxable = InvoiceItem::where('invoice_id', $invoice->id)->where('tax_rate', '<', 0)->sum('amount');
        
        $bankLines = [];
        if ($invoice->bank) {
            $bankLines = preg_split('/\r\n|\r|\n/', $invoice->bank->bank_info ?? '');
        }
        
        $customerLines = preg_split('/\r\n|\r|\n/', $invoice->agency_detail ?? '');
        
        $companyInfo = UserCompanyInfo::first();
        
        $setup_company_seal = '';
        if (!empty($companyInfo->setup_company_seal)) {
            $setup_company_seal = url('/storage/' . $companyInfo->setup_company_seal);
        }
        
        $staffName = '';
        if ($invoice->staff_id) {
            $staff = DB::table('staffs')->where('id', $invoice->staff_id)->first();
            $staffName = $staff->name ?? '';
        }
        
        $billTo = '';
        if ($invoice->agency && $invoice->agency->bill_to) {
            $billTo = $invoice->agency->bill_to;
        }
        
        $groupName = '';
        if ($invoice->reservation_id) {
            $groupInfo = DB::table('group_info')->where('id', $invoice->reservation_id)->first();
            $groupName = $groupInfo->group_name ?? '';
        }
        
        return [
            'invoice' => (object)[
                'billing_title' => $invoice->billing_title,
                'operation_date' => $invoice->operation_date ? date('Y-m-d', strtotime($invoice->operation_date)) : '',
                'reservation_id' => $invoice->reservation_id,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'invoice_number' => $invoice->invoice_number,
                'notes' => $invoice->notes,
                'subtotal_amount' => $invoice->subtotal_amount,
                'tax_amount' => $invoice->tax_amount,
                'total_amount' => $invoice->total_amount,
                'tax_mode' => $invoice->tax_mode,
                'currency_code' => $invoice->currency_code,
                'non_taxable' => $non_taxable,
                'group_name' => $groupName,
            ],
            'summary_10' => $summary_10,
            'summary_8' => $summary_8,
            'items' => $items,
            'bank' => $bankLines,
            'customer' => $customerLines,
            'company' => (object)[
                'name' => $companyInfo->company_name ?? '',
                'postal_code' => $companyInfo->postal_code ?? '',
                'address' => $companyInfo->address ?? '',
                'phone' => $companyInfo->phone_number ?? '',
                'fax' => $companyInfo->fax_number ?? '',
                'invoice_code' => $companyInfo->invoice_code ?? '',
                'bill_to' => $billTo,
                'setup_company_seal' => $setup_company_seal ?? '',
                'contact' => $staffName,
            ],
        ];
    }
    
}

