<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountTax; // 假设模型名为 AccountTax
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountTaxController extends Controller
{
    /**
     * 一覧表示 (Index)
     * 对应图片中的列表展示
     */
    public function index(Request $request)
    {
        $query = AccountTax::query();

        // 1. 搜索功能：名称 (name) 或 代码 (code)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // 2. 筛选功能：计算类型 (calculation_type: 外税/内税/不課税)
        if ($request->filled('calculation_type')) {
            $query->where('calculation_type', $request->calculation_type);
        }

        // 3. 筛选功能：发票资格 (is_invoice_eligible: 2 或 1)
        if ($request->filled('is_invoice_eligible')) {
            $query->where('is_invoice_eligible', (int)$request->is_invoice_eligible);
        }

        // 4. 分页设置
        $perPage = 20;
        $allowedPerPages = [20, 30, 50];
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }

        // 5. 排序：默认按 ID 升序，也可以支持按税率 (rate) 排序
        $sortField = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_dir', 'asc');
        
        // 防止非法排序字段
        if (!in_array($sortField, ['id', 'code', 'rate', 'name'])) {
            $sortField = 'id';
        }

        $taxs = $query->orderBy($sortField, $sortDirection)->paginate($perPage);

        // 保持分页链接中的查询参数
        $taxs->appends([
            'search' => $request->search, 
            'calculation_type' => $request->calculation_type,
            'is_invoice_eligible' => $request->is_invoice_eligible,
            'sort_by' => $sortField,
            'sort_dir' => $sortDirection
        ]);

        return view('masters.account-taxs.index', compact('taxs'));
    }

    /**
     * 作成画面表示 (Create)
     */
    public function create()
    {
        // 准备下拉框选项 (可选)
        $calculationTypes = [
            '外税' => '外税',
            '内税' => '内税',
            '不課税' => '不課税'
        ];
        
        return view('masters.account-taxs.create', compact('calculationTypes'));
    }

    /**
     * 登録処理 (Store)
     */
    public function store(Request $request)
    {
        // 验证规则：严格对应图片字段
        $rules = [
            'code'                 => 'required|string|max:10',
            'name'                 => 'required|string|max:100',
            'rate'                 => 'required|numeric|min:0|max:100', // 税率 0-100
            'calculation_type'     => 'required',
            'is_invoice_eligible'  => 'required|integer', // 0 或 1
        ];

        $messages = [
            'code.required'            => 'コードは必須です。',
            'code.unique'              => 'このコードは既に使用されています。',
            'name.required'            => '名称は必須です。',
            'name.unique'              => 'この名称は既に使用されています。',
            'rate.required'            => '税率は必須です。',
            'rate.numeric'             => '税率は数値で入力してください。',
            'calculation_type.required'=> '計算タイプは必須です。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理
        $validated['rate'] = (float)$validated['rate'];
        $validated['is_invoice_eligible'] = (int)$validated['is_invoice_eligible']; // 确保存为 tinyint(1)

        try {
            AccountTax::create($validated);

            return redirect()->route('masters.account-taxs.index')
                ->with(['success' => '税率設定を登録しました。', 'alert-type' => 'success']);
                
        } catch (\Exception $e) {
            Log::error('AccountTax Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with(['error' => '登録に失敗しました。', 'alert-type' => 'danger']);
        }
    }

    /**
     * 詳細画面表示 (Show)
     */
    public function show($id)
    {
        $tax = AccountTax::findOrFail($id);
        return view('masters.account-taxs.show', compact('tax'));
    }

    /**
     * 編集画面表示 (Edit)
     */
    public function edit($id)
    {
        $tax = AccountTax::findOrFail($id);
        
        $calculationTypes = [
            '外税' => '外税',
            '内税' => '内税',
            '不課税' => '不課税',
            '非課税' => '非課税'
        ];

        return view('masters.account-taxs.edit', compact('tax', 'calculationTypes'));
    }

    /**
     * 更新処理 (Update)
     */
    public function update(Request $request, $id)
    {
        $AccountTax = AccountTax::findOrFail($id);
        
        $rules = [
            'code'                 => 'required|string|max:10,' . $AccountTax->id,
            'name'                 => 'required|string|max:100,' . $AccountTax->id,
            'rate'                 => 'required|numeric|min:0|max:100',
            'calculation_type'     => 'required',
            'is_invoice_eligible'  => 'required|integer',
        ];

        $messages = [
            'code.unique' => 'このコードは既に使用されています。',
            'name.unique' => 'この名称は既に使用されています。',
            // 其他消息同上...
            'rate.numeric' => '税率は数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理
        $validated['rate'] = (float)$validated['rate'];
        $validated['is_invoice_eligible'] = (int)$validated['is_invoice_eligible'];

        try {
            $AccountTax->update($validated);
            
            return redirect()->route('masters.account-taxs.index')
                ->with(['success' => '税率設定を更新しました。', 'alert-type' => 'success']);
                
        } catch (\Exception $e) {
            Log::error('AccountTax Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with(['error' => '更新に失敗しました。', 'alert-type' => 'danger']);
        }
    }

    /**
     * 削除処理 (Destroy)
     * 注意：实际项目中需检查是否有交易数据引用了此税率
     */
    public function destroy($id)
    {
        $AccountTax = AccountTax::findOrFail($id);
        
        try {
            // 【重要】在此处添加检查逻辑，例如：
            // if ($AccountTax->invoices()->count() > 0) { ... }
            
            $AccountTax->delete();
            
            return redirect()->route('masters.account-taxs.index')
                ->with(['success' => '税率設定を削除しました。', 'alert-type' => 'success']);
                
        } catch (\Exception $e) {
            Log::error('AccountTax Delete Error: ' . $e->getMessage());
            return redirect()->back()
                ->with(['error' => '削除に失敗しました。使用中の可能性があります。', 'alert-type' => 'danger']);
        }
    }
}