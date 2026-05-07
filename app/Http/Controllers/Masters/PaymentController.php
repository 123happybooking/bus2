<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Invoice;
use App\Models\Masters\PaymentHeader;
use App\Models\Masters\PaymentDetail;
use App\Models\Masters\Staff;
use App\Models\Masters\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * 显示核销列表页 (可选，如果不需要独立列表页可删除)
     */
    public function index(Request $request)
    {

        $query = PaymentHeader::where('is_deleted', 0);
        // 搜索逻辑
        if ($request->filled('search')) {
            $query->where('batch_token', 'like', "%{$request->search}%");
        }

        if ($request->filled('payment_date')) {
            $query->whereDate('payment_date', $request->payment_date);
        }


        $payments = $query->orderBy('created_at', 'desc')->paginate(20);
        $payments->appends($request->except('page'));

        return view('masters.payments.index', compact('payments'));
    }

    /**
     * 执行批量核销存储 (核心逻辑)
     * 接收来自 Modal 的 POST 请求
     */
public function store(Request $request)
{
    // ==========================================
    // 1. 定义验证规则 (Rules)
    // ==========================================
    $rules = [
        'group_id' => 'required|integer', 
        'return_url' => 'required|string',
        'mode'     => 'required',
        'bank_id'  => 'nullable|integer', 
        'staff_id'  => 'nullable|integer', 
        
        // Detail 模式特有字段
        'payment_date' => 'required_if:mode,detail|date',
        'remark'       => 'nullable|string|max:255',
        
        // 明细列表验证
        'items' => 'required|array|min:1',
        
        // 明细项验证
        'items.*.invoice_id'     => 'required|integer',
        'items.*.payment_amount' => 'required|numeric|min:0.01', 
        // 注意：这里只能校验格式，"不能超过余额"的逻辑需要在循环中手动校验
    ];

    // ==========================================
    // 2. 定义错误消息 (Messages - 日文)
    // ==========================================
    $messages = [
        'mode.required'              => 'モードの選択は必須です。',
        'mode.in'                    => '無効なモードが選択されています。',
        
        'payment_date.required_if'   => '入金日は必須です。',
        'payment_date.date'          => '入金日は有効な日付形式で入力してください。',
        
        'remark.max'                 => '備考は255文字以内で入力してください。',
        
        'items.required'             => '明細データが少なくとも1件必要です。',
        'items.array'                => '明細データの形式が無効です。',
        'items.min'                  => '明細データが少なくとも1件必要です。',
        
        'items.*.invoice_id.required' => '請求書IDは必須です。',
        'items.*.invoice_id.integer'  => '請求書IDは整数である必要があります。',
        'items.*.invoice_id.exists'   => '指定された請求書が存在しません。',
        
        'items.*.payment_amount.required' => '入金額は必須です。',
        'items.*.payment_amount.numeric'  => '入金額は数値で入力してください。',
        'items.*.payment_amount.min'      => '入金額は0.01以上で入力してください。',
    ];
    // ==========================================
    // 3. 执行验证
    // ==========================================

    //$validated = $request->validate($rules, $messages);
    $validated = $request->all();

    $groupId    = $validated['group_id'];
    $bank_id       = $validated['bank_id'];
    $items      = $validated['items'];
    $batchToken = 'BATCH-' . date('YmdHis') . '-' . rand(1000, 9999);
    $return_url = $validated['return_url'];

    // 计算总金额 (使用 bcmath 防止浮点误差)
    $totalAmount = '0';
    foreach ($items as $item) {
        $totalAmount = bcadd($totalAmount, (string)$item['payment_amount'], 2);
    }

    $paymentDate = $validated['payment_date'] ?? now()->toDateString();
    $remark      = $validated['remark'] ?? '';
    $notesValue  = "{$batchToken}|" . count($items) . "|{$totalAmount}";
    DB::beginTransaction();
    try {
        // ==========================================
        // 4. 业务逻辑预检查 (Pre-checks)
        // ==========================================
        $firstInvoice = null;
        $processedData = [];

        foreach ($items as $index => $item) {
            // 锁定发票行 (防止并发超付)
            $invoice = Invoice::lockForUpdate()->findOrFail($item['invoice_id']);

            // 检查发票是否被删除
            if ($invoice->is_deleted) {
                // 手动抛出异常，带自定义消息
                throw new \Exception("請求書 #{$invoice->invoice_no} は削除されているため、入金できません。");
            }

            // 计算当前余额
            $currentBalance = bcsub((string)$invoice->total_amount, (string)$invoice->paid_amount, 2);
            $payAmount      = (string)$item['payment_amount'];

            // 【核心校验】入金额 > 余额 ?
            // bccomp 返回 1 表示左边大于右边
            if (bccomp($payAmount, $currentBalance, 2) > 0) {
                throw new \Exception(
                    "請求書 #{$invoice->invoice_no} の入金額エラー：\n" .
                    "入力額 ({$payAmount}) が未入金残高 ({$currentBalance}) を超えています。"
                );
            }

            // 校验客户一致性 (可选，但推荐)
            if ($index === 0) {
                $firstInvoice = $invoice;
            } else {
                if ($invoice->customer_id !== $firstInvoice->customer_id) {
                    throw new \Exception(
                        "一括入金エラー：\n" .
                        "異なる顧客の請求書を混合して入金することはできません。\n" .
                        "(対象：請求書 #{$invoice->invoice_no})"
                    );
                }
            }

            $processedData[] = [
                'invoice' => $invoice,
                'amount'  => $payAmount
            ];
        }

        if (!$firstInvoice) {
            throw new \Exception("有効な請求書データが見つかりません。");
        }

        $staff = Staff::Where("user_company_id", Auth::user()->id)->first();
        // ==========================================
        // 5. 创建主表 (PaymentHeader)
        // ==========================================
        $payment = PaymentHeader::create([
            'group_id'       => $groupId,
            'bank_id'        => $bank_id,
            'batch_token'    => $batchToken,
            'payment_date'   => $paymentDate,
            'total_amount'   => $totalAmount,
            'remark'         => $remark,
            'staff_id'     => $staff->id ?? 0,
            'created_by'     => 1,
            'is_deleted'     => 0,
            'notes'          => $notesValue,
        ]);

        // ==========================================
        // 6. 创建明细 & 更新发票状态
        // ==========================================
        $detailsData = [];
        foreach ($processedData as $data) {
            $invoice   = $data['invoice'];
            $payAmount = $data['amount'];

            $detailsData[] = [
                'payment_header_id' => $payment->id,
                'invoice_id'        => $invoice->id,
                'write_off_amount'  => $payAmount,
                'is_deleted'        => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // 原子操作更新已付金额
            $invoice->increment('paid_amount', $payAmount);

            // 更新状态
            $newBalance = bcsub((string)$invoice->total_amount, (string)$invoice->paid_amount, 2);
            if (bccomp($newBalance, '0', 2) <= 0) {
                Invoice::where('id', $invoice->id)->update(['payment_status' => '3']);
            } else {
                Invoice::where('id', $invoice->id)->update(['payment_status' => '2']);
            }
        }

        // 批量插入明细
        PaymentDetail::insert($detailsData);

        DB::commit();

        if(!$return_url){
            $return_url = "masters/invoices?group_id=".$groupId;
        }
        return redirect($return_url)->with('success', '登録完了：' . $batchToken);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Payment Store Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

        // 如果是自定义的业务异常，直接显示；否则显示通用错误
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, '請求書') === false && strpos($errorMsg, '一括入金') === false) {
            $errorMsg = 'システムエラーが発生しました。管理者にお問い合わせください。';
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $errorMsg);
    }
}


    /**
     * 查看入金详情 (可选)
     */
    public function show(Request $request, $id)
    {
        $payment = PaymentHeader::findOrFail($id);

        if ($payment->is_deleted) {
            return redirect()->route('masters.payments.index')
                ->with('error', 'この入金記録はすでに取消されています。');
        }
        $details = $payment->details; 
        $staffs = Staff::where("is_active",1)->get();
        $banks = Bank::where("is_active",1)->get();
        return view('masters.payments.show', compact('payment','details','staffs','banks'));
    }


    public function edit(Request $request, $id)
    {
        $payment = PaymentHeader::findOrFail($id);
        if ($payment->is_deleted) {
            return redirect()->route('masters.payments.index')
                ->with('error', 'この入金記録はすでに取消されています。');
        }

        $details = $payment->details;
        $staffs = Staff::where("is_active",1)->get();
        $banks = Bank::where("is_active",1)->get();
        return view('masters.payments.edit', compact('payment','details','staffs','banks'));
    }

    /**
     * 更新逻辑 (仅更新备注)
     */
    public function update(Request $request, $id)
    {
        $payment = PaymentHeader::findOrFail($id);
        // 验证
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'staff_id'   => 'nullable|integer',
            'bank_id'   => 'nullable|integer',
            'remark'       => 'nullable|string|max:500',
            // 不允许修改金额或明细，所以这里不验证它们
        ]);

        // 更新
        $payment->update([
            'payment_date' => $validated['payment_date'],
            'staff_id'   => $validated['staff_id'],
            'bank_id'   => $validated['bank_id'],
            'remark'       => $validated['remark'] ?? null,
        ]);

        return redirect()->route('masters.payments.index', ['payment' => $payment])->with('success', '入金情報を更新しました。');
    }

    /**
     * 撤销/删除入金 (软删除)
     * 需要同时恢复 Invoice 的状态
     */
    public function destroy(Request $request, $id)
    {
        $payment = PaymentHeader::findOrFail($id);
        $groupId = $request->query('group_id');

        if ($payment->group_id != $groupId) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // 1. 获取所有明细
            $details = PaymentDetail::where('payment_header_id', $payment->id)
                ->where('is_deleted', 0)
                ->get();

            foreach ($details as $detail) {
                $invoice = Invoice::lockForUpdate()->find($detail->invoice_id);
                if ($invoice) {
                    // 回滚 Invoice 金额
                    $newPaid = ($invoice->paid_amount ?? 0) - $detail->write_off_amount;
                    if ($newPaid < 0) $newPaid = 0;

                    $newStatus = 'unpaid';
                    if ($newPaid >= ($invoice->total_amount - 0.01)) {
                        $newStatus = 'paid';
                    } elseif ($newPaid > 0) {
                        $newStatus = 'partial';
                    }

                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'payment_status' => $newStatus,
                        'updated_at' => now(),
                    ]);
                }

                // 软删除明细
                $detail->update([
                    'is_deleted' => 1,
                    'deleted_at' => now(),
                ]);
            }

            // 2. 软删除主表 (Model 中已定义 boot 事件级联删除明细，但为了显式控制 Invoice 回滚，上面手动处理了)
            // 如果 Model boot 事件已经处理了 detail 的软删除，这里只需 delete header
            $payment->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', '入金記録を取消しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('入金取消エラー: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', '取消処理に失敗しました。');
        }
    }
}