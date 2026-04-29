<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountJournalEntry;
use App\Models\Masters\AccountJournalLine;
use App\Models\Masters\Account; 
use App\Models\Masters\AccountSub; 
use App\Models\Masters\AccountDepartment;
use App\Models\Masters\AccountPartner; 
use App\Models\Masters\AccountTax; 
use App\Models\Masters\AccountPeriod; 
use App\Models\Masters\Staff; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Illuminate\Support\Facades\Cache; 

class AccountJournalEntryController extends Controller
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
        $inputs = $request->all();
        if(count($inputs)==1){
            $filters = session(auth()->user()->id.'.filters');
            if($filters){
                $inputs = $filters;
            }
        }

        $start_year = explode('-', $period_start)[0];
        $start_month = explode('-', $period_start)[1];
        $moren_month = explode('-', $period_start)[1];

        

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

        $start_year = $request->start_year ?? $start_year;
        $start_month = $request->start_month ?? $start_month;
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

        $inputs = $request->all();

        if(count($inputs)==1){
            $filters = session(auth()->user()->id.'.filters');
            if($filters){
                $start_year = $filters['start_year'];
                $start_month = $filters['start_month'];
                $yearmonth = $filters['yearmonth'];
                $period_id = $filters['period_id'];
                $period = AccountPeriod::find($period_id);
                $period_start = $period->start;
                $start_year = explode('-', $period_start)[0];
                $start_month = explode('-', $period_start)[1];
                $moren_month = explode('-', $period_start)[1];
                if($filters['yearmonth'] == "13"){
                    $yearmonth = "13";
                }

            }
        }

        $account_id = $request->account_id;

        $inputs = [
            'start_year' => $start_year,
            'start_month' => $start_month,
            'yearmonth' => $yearmonth,
            'account_id' => $account_id,
            'period_id' => $period_id,
        ];
        session([auth()->user()->id.'.filters' => $inputs]);

        if ($yearmonth == "13" ){
            $firstDay = $period->start;
            $lastDay =  $period->end;
        }else{
            $firstDay = date('Y-m-d', strtotime("first day of $yearmonth"));
            $lastDay = date('Y-m-d', strtotime("last day of $yearmonth"));
        }


        $sortOrder = $request->input('sort_order') ?? 'desc'; 
        $query = AccountJournalEntry::query();
        
        // 如果有部门隔离需求，可以在这里加 where('department_id', ...)
        // 这里暂时只按日期范围或搜索过滤

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('lines', function($ql) use ($search) {
                      // 关联查询明细中的备注
                      $ql->where('remark', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('posting_date')) {
            $query->where('posting_date', '=', $request->posting_date);
        }


        $query->where('posting_date', '>=', $firstDay)->where('posting_date', '<=', $lastDay);

        if ($request->filled('account_id')) {
            $entry_ids = AccountJournalLine::where('account_id',$request->account_id)->pluck("journal_entry_id");
            $query->whereIn('id', $entry_ids);
        }

        $perPage = 20;
        $allowedPerPages = [20, 30, 50];
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }

        $entries = $query->orderBy('posting_date', $sortOrder)->paginate($perPage);
        $entries->appends($request->only(['search', 'posting_date',  'account_id', 'period_id','yearmonth','per_page']));

        // 预加载一些基础数据用于筛选下拉框 (可选)
        $departments = AccountDepartment::get();
        $staffs = Staff::where("is_active", 1)->get();

        $accounts = Account::where('is_active', 1)->get();
        $partners = AccountPartner::get(); // 交易伙伴
        $taxes = AccountTax::get();       // 税区分

        $accountsJie = Account::where('is_active', 1)
            ->whereHas('category', function ($query) {
                $query->where('mark', '借');
            })->get();
        $accountsDai = Account::where('is_active', 1)
            ->whereHas('category', function ($query) {
                $query->where('mark', '貸');
            })->get();

        return view('masters.journal_entries.index', compact('entries',  'departments', 'staffs','accounts','partners','taxes','accountsJie','accountsDai','start_year','start_month','months','yearmonth','periods','period_id'));
    }

    public function create(Request $request)
    {
        $groupId = $request->query('group_id');
        
        $accounts = Account::where('is_active', 1)->orderBy('code')->get();
        $departments = AccountDepartment::where('is_active', 1)->get();
        $partners = AccountPartner::where('is_active', 1)->get(); // 交易伙伴
        $taxes = AccountTax::where('is_active', 1)->get();       // 税区分
        $staffs = Staff::where('is_active', 1)->get();

        return view('masters.journal_entries.create', compact('groupId', 'accounts', 'departments', 'partners', 'taxes', 'staffs'));
    }

    public function getAccountSubs($accountId)
    {
        // 查询 account_subs 表，where account_id = $accountId
        $subs = AccountSub::where('account_id', $accountId)
            ->where("is_active",1)
            ->select('id', 'name') // 根据需要选择字段
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'display' => $item->name,
                ];
            });

        return response()->json($subs);
    }

    public function store(Request $request)
    {
        // 1. 验证数据
        // 注意：side 必须严格限制为 '借' 或 '貸'，不能 nullable，否则前端传空也会过
        $validated = $request->validate([
            'posting_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'department_id' => 'nullable',
            'department_name' => 'nullable|string',
            'source_type' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:100',
            'source_id' => 'nullable|integer',
            
            'lines' => 'required|array|min:1', // 至少有一行
            'lines.*.account_id' => 'required|integer|exists:accounts,id', // 强制要求科目ID
            'lines.*.side' => 'required', // 【关键】强制验证，必须是这两个字
            'lines.*.amount' => 'required|numeric|min:0.01', // 金额必须大于0
            'lines.*.account_sub_id' => 'nullable', // 允许为空
            'lines.*.account_sub_name' => 'nullable|string|max:255',      // 新增：允许接收文本
            'lines.*.partner_id' => 'nullable',        // 允许为空
            'lines.*.partner_name' => 'nullable|string|max:255',          // 新增：允许接收文本
            'lines.*.tax_type_id' => 'nullable|integer|exists:account_taxs,id',
            'lines.*.remark' => 'nullable|string|max:255',
        ], [
            // 自定义错误消息 (可选，Laravel 默认也有)
            //'lines.*.side.in' => '借貸方向は「借」または「貸」にしてください。',
            'lines.*.amount.min' => '金額は 0 より大きい値にしてください。',
            'lines.*.account_id.exists' => '無効な科目が選択されています。',
        ]);

        // 2. 借贷平衡校验 (核心逻辑)
        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($validated['lines'] as $line) {
            $amount = (float)$line['amount'];
            if ($line['side'] === '借') {
                $debitTotal += $amount;
            } elseif ($line['side'] === '貸') {
                $creditTotal += $amount;
            }
        }

        // 严格平衡检查 (允许 0.01 的浮点数误差，或者严格为 0)
        if (abs($debitTotal - $creditTotal) > 0.01) {
            // 如果是 AJAX 请求，返回 JSON 错误
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '借貸合計が一致しません。',
                    'errors' => [
                        'lines' => [
                            "借貸合計が一致しません。<br>借方合計: " . number_format($debitTotal, 2) . "<br>貸方合計: " . number_format($creditTotal, 2)
                        ]
                    ]
                ], 422);
            }
            // 如果是普通表单提交
            return back()->withErrors([
                'lines' => "借貸合計が一致しません。<br>借方合計: " . number_format($debitTotal, 2) . "<br>貸方合計: " . number_format($creditTotal, 2)
            ])->withInput();
        }

        DB::beginTransaction();
        try {

            $department_id = 0;
            if($validated['department_name']){
                $department = AccountDepartment::where("name",$validated['department_name'])->first();
                if($department){
                    $department_id = $department->id;
                }else{
                    $newDepartment = AccountDepartment::create([
                        'name' => $validated['department_name'],
                    ]);
                    $department_id = $newDepartment->id;

                }
            }

 
            // A. 创建主表
            $entry = AccountJournalEntry::create([
                'posting_date'  => $validated['posting_date'],
                'department_id' => $department_id,
                'source_type'   => $validated['source_type'],
                'remark'   => $validated['remark'],
                'source_id'     => $this->generateAccountNumber(),
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
            ]);

            // B. 批量插入明细
            $linesData = [];
            foreach ($validated['lines'] as $line) {

                $account_sub_id = 0;
                $partner_id = 0;

                if($line['account_sub_name']){
                    $account_sub = AccountSub::where("account_id",$line['account_id'])->where("name",$line['account_sub_name'])->first();
                    if($account_sub){
                        $account_sub_id = $account_sub->id;
                    }else{
                        $newSub = AccountSub::create([
                            'name' => $validated['department_name'],
                            'is_active'=>1,
                            'account_id'=>$line['account_id']
                        ]);
                        $account_sub_id = $newSub->id;

                    }
                }

                if($line['partner_name']){
                    $partner = AccountPartner::where("name",$line['partner_name'])->first();
                    if($partner){
                        $partner_id = $partner->id;
                    }else{
                        $newPartner = AccountPartner::create([
                            'name' => $line['partner_name']
                        ]);
                        $partner_id = $newPartner->id;

                    }
                }


                $linesData[] = [
                    'journal_entry_id' => $entry->id,
                    'side'             => $line['side'],
                    'account_id'       => (int)$line['account_id'],
                    'sub_account_id'   => $account_sub_id,
                    'partner_id'       => $partner_id,
                    'amount'           => (float)$line['amount'],
                    'tax_type_id'      => $line['tax_type_id'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            
            AccountJournalLine::insert($linesData);

            DB::commit();

            // 【关键修改】根据请求类型返回不同格式
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '仕訳伝票を登録しました。',
                    'data' => [
                        'id' => $entry->id,
                        'redirect_url' => route('masters.journal_entries.index')
                    ]
                ]);
            }

            return redirect()->route('masters.journal_entries.index')
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

    public function show($id)
    {
        $entry = AccountJournalEntry::with([
            'department',
            'lines' => function ($query) {
                $query->with(['account', 'subAccount', 'partner', 'taxType']);
            }
        ])->findOrFail($id);

        // 如果是 AJAX 请求，返回 JSON
        if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'id' => $entry->id,
                'posting_date' => $entry->posting_date->format('Y-m-d'),
                'source_type' => $entry->source_type,
                'remark' => $entry->remark,
                'description' => $entry->description, // 如果有描述字段
                'department_id' => $entry->department_id,
                'department' => $entry->department ? ['id' => $entry->department->id, 'name' => $entry->department->name] : null,
                'lines' => $entry->lines->map(function ($line) {
                    return [
                        'id' => $line->id,
                        'side' => $line->side,
                        'amount' => $line->amount,
                        'account_id' => $line->account_id,
                        // 关键：返回格式化好的名称，方便前端直接赋值
                        'account_full_text' => $line->account ? $line->account->code . ' - ' . $line->account->name : '', 
                        
                        'account_sub_id' => $line->account_sub_id,
                        'account_sub' => $line->subAccount ? [
                            'id' => $line->subAccount->id ?? '',
                            'name' => $line->subAccount->name ?? '',
                            'display_name' => $line->subAccount->name ?? '',
                        ] : null,

                        'partner_id' => $line->partner_id,
                        'partner' => $line->partner ? ['id' => $line->partner->id, 'name' => $line->partner->name] : null,

                        'tax_type_id' => $line->tax_type_id,
                        'tax_type' => $line->taxType ? ['id' => $line->taxType->id, 'name' => $line->taxType->name] : null,
                    ];
                })
            ]);
        }

        // 如果不是 AJAX，可以返回视图（如果需要单独详情页），或者 abort 404
        // 既然你是单页应用模式，这里可以直接 abort 或者返回空视图
        return abort(404); 
    }


    public function update(Request $request, int $id)
    {
        // 1. 查找记录
        $entry = AccountJournalEntry::find($id);
        if (!$entry) {
            if ($request->wantsJson()) {
                return response()->json(['message' => '伝票が見つかりません。'], 404);
            }
            abort(404);
        }

        // 2. 验证数据 (与 store 基本一致)
        $validated = $request->validate([
            'posting_date' => 'required|date',
            'department_id' => 'nullable',
            'department_name' => 'nullable|string',
            'source_type' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:100',
            
            'lines' => 'required|array|min:1',
            'lines.*.account_id' => 'required|integer|exists:accounts,id',
            'lines.*.side' => 'required', // 依然验证 '借' 或 '贷'
            'lines.*.amount' => 'required|numeric|min:0.01',
            'lines.*.account_sub_id' => 'nullable',
            'lines.*.account_sub_name' => 'nullable|string|max:255',
            'lines.*.partner_id' => 'nullable',
            'lines.*.partner_name' => 'nullable|string|max:255',
            'lines.*.tax_type_id' => 'required|integer|exists:account_taxs,id',
        ], [
            'lines.*.amount.min' => '金額は 0 より大きい値にしてください。',
        ]);

        // 3. 借贷平衡校验
        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($validated['lines'] as $line) {
            $amount = (float)$line['amount'];
            if ($line['side'] === '借') {
                $debitTotal += $amount;
            } elseif ($line['side'] === '贷') { // 注意：如果前端传的是汉字
                $creditTotal += $amount;
            }
        }

        if (abs($debitTotal - $creditTotal) > 0.01) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '借貸合計が一致しません。',
                    'errors' => ['lines' => ["借方: " . number_format($debitTotal, 2) . " / 貸方: " . number_format($creditTotal, 2)]]
                ], 422);
            }
            return back()->withErrors(['lines' => '借貸合計が一致しません。'])->withInput();
        }

        DB::beginTransaction();
        try {
            // 4. 处理部门逻辑 (查找或创建)
            $department_id = 0;
            if ($validated['department_name']) {
                $department = AccountDepartment::where("name", $validated['department_name'])->first();
                if ($department) {
                    $department_id = $department->id;
                } else {
                    $newDepartment = AccountDepartment::create(['name' => $validated['department_name']]);
                    $department_id = $newDepartment->id;
                }
            }

            // 5. 更新主表
            $entry->update([
                'posting_date'  => $validated['posting_date'],
                'department_id' => $department_id,
                'source_type'   => $validated['source_type'],
                'remark'   => $validated['remark'],
                'updated_by'    => Auth::id(),
                'updated_at'    => now(),
            ]);

            // 6. 处理明细 (先删后增策略)
            // 删除该凭证下原有的所有明细行
            AccountJournalLine::where('journal_entry_id', $entry->id)->delete();

            $linesData = [];
            foreach ($validated['lines'] as $line) {
                $account_sub_id = 0;
                $partner_id = 0;

                // 处理辅助科目
                if (!empty($line['account_sub_name'])) {
                    $account_sub = AccountSub::where("account_id", $line['account_id'])
                        ->where("name", $line['account_sub_name'])
                        ->first();
                    
                    if ($account_sub) {
                        $account_sub_id = $account_sub->id;
                    } else {
                        $newSub = AccountSub::create([
                            'name' => $line['account_sub_name'], // 注意这里是用 line 里的名字
                            'is_active' => 1,
                            'account_id' => $line['account_id']
                        ]);
                        $account_sub_id = $newSub->id;
                    }
                }

                // 处理往来单位
                if (!empty($line['partner_name'])) {
                    $partner = AccountPartner::where("name", $line['partner_name'])->first();
                    if ($partner) {
                        $partner_id = $partner->id;
                    } else {
                        $newPartner = AccountPartner::create(['name' => $line['partner_name']]);
                        $partner_id = $newPartner->id;
                    }
                }

                $linesData[] = [
                    'journal_entry_id' => $entry->id,
                    'side'             => $line['side'],
                    'account_id'       => (int)$line['account_id'],
                    'sub_account_id'   => $account_sub_id,
                    'partner_id'       => $partner_id,
                    'amount'           => (float)$line['amount'],
                    'tax_type_id'      => $line['tax_type_id'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            AccountJournalLine::insert($linesData);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '仕訳伝票を更新しました。',
                    'data' => ['id' => $entry->id]
                ]);
            }

            return redirect()->route('masters.journal_entries.index')
                ->with('success', '仕訳伝票を更新しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('仕訳伝票更新エラー: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'システムエラーが発生しました。'], 500);
            }
            return back()->withErrors(['error' => 'システムエラーが発生しました。'])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $entry = AccountJournalEntry::findOrFail($id);
        $groupId = $request->query('group_id');

        // 检查是否允许删除 (例如：已过账的凭证不能删除)
        // if ($entry->is_posted) { ... }

        try {
            // 关联的明细会因为外键约束 (ON DELETE CASCADE) 自动删除，或者手动删除
            // 如果没设级联删除，需手动：$entry->lines()->delete();
            
            $entry->delete();

            return redirect()
                ->route('masters.journal_entries.index' ,request()->only(['date_from', 'date_to', 'account_id']))
                ->with([
                    'success' => '仕訳伝票を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            Log::error('Journal Entry delete error: ' . $e->getMessage());
            return redirect()
                ->route('masters.journal_entries.index',request()->only(['date_from', 'date_to', 'account_id']))
                ->with([
                    'error' => '削除に失敗しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function duplicate(Request $request, $id)
    {
        $newEntry = DB::transaction(function () use ($id) {
            $original = AccountJournalEntry::with('lines')->findOrFail($id);
            
            $newEntry = $original->replicate();
            // 重置某些字段
            $newEntry->description = $original->description . ' (コピー)';
            $newEntry->source_type = 'copy';
            $newEntry->source_id = null; 
            // 不复制 created_by/updated_by，让新记录记录当前用户
            $newEntry->created_by = Auth::id();
            $newEntry->updated_by = Auth::id();
            
            $newEntry->save();

            foreach ($original->lines as $line) {
                $newLine = $line->replicate();
                $newLine->journal_entry_id = $newEntry->id;
                $newLine->save();
            }

            return $newEntry;
        });

        return redirect()->route('masters.journal-entries.edit', [
            'entry' => $newEntry->id,
            'group_id' => $request->query('group_id')
        ])->with([
            'success' => '仕訳伝票のコピーが完了しました。',
            'alert-type' => 'success'
        ]);
    }

    private function generateAccountNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $lastaccount = DB::table('account_journal_entries')
            ->where('source_id', 'like', "ACC-{$dateStr}-%")
            ->orderBy('source_id', 'desc')
            ->first();

        if ($lastaccount) {
            $lastNum = (int)substr($lastaccount->source_id, -3);
            $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNum = '001';
        }

        return "ACC-{$dateStr}-{$newNum}";
    }
}