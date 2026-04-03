<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Masters\Account;
use App\Models\Masters\AccountCategory;
use App\Models\Masters\AccountTax; 
use Illuminate\Http\Request;
use Carbon\Carbon;

class AccountController extends Controller
{
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

        $perPage = 20; // 默认值
        $allowedPerPages = [20, 30, 50]; // 允许的选项
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 排序：默认按 ID 降序 (新注册的在后)，也可改为按 code 升序
        $accounts = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // 保留查询参数用于分页链接
        $accounts->appends(['search' => $request->search, 'is_active' => $request->is_active, 'per_page' => $perPage]);
        
        return view('masters.accounts.index', compact('accounts'));
    }

    public function create()
    {

        $categories = AccountCategory::all(); 
        $taxes = AccountTax::all();
        return view('masters.accounts.create', compact('categories', 'taxes'));
    }

    public function store(Request $request)
    {
        $rules = [
            'code'        => 'required|string|max:20|unique:accounts,code',
            'name'        => 'required|string|max:100',
            'category_id' => 'required|integer|min:1',
            'tax_id'      => 'nullable|integer|min:1',
            'is_active'   => 'boolean',
        ];

        $messages = [
            'code.required'        => '科目コードは必須です。',
            'code.unique'          => 'この科目コードは既に使用されています。',
            'code.max'             => '科目コードは20文字以内で入力してください。',
            'name.required'        => '科目名は必須です。',
            'name.max'             => '科目名は100文字以内で入力してください。',
            'category_id.required' => '区分（中分類）は必須です。',
            'category_id.integer'  => '区分IDは数値で入力してください。',
            'category_id.min'      => '区分IDは1以上で入力してください。',
            'tax_id.integer'       => '税区分IDは数値で入力してください。',
            'tax_id.min'           => '税区分IDは1以上で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理：类型转换
        $validated['category_id'] = (int)$validated['category_id'];
        
        if (isset($validated['tax_id']) && $validated['tax_id'] !== '') {
            $validated['tax_id'] = (int)$validated['tax_id'];
        } else {
            $validated['tax_id'] = null;
        }

        // is_active 复选框未勾选时为 false，勾选时为 true
        $validated['is_active'] = $request->has('is_active');

        try {
            Account::create($validated);
            return redirect()->route('masters.accounts.index')
                ->with([
                    'success' => '会計科目を登録しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            // Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function show($id)
    {
        $account = Account::findOrFail($id);
        return view('masters.accounts.show', compact('account'));
    }

    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $categories = AccountCategory::all(); 
        $taxes = AccountTax::all();
        return view('masters.accounts.edit', compact('account','categories','taxes'));
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        
        $rules = [
            'code'        => 'required|string|max:20|unique:accounts,code,' . $account->id,
            'name'        => 'required|string|max:100',
            'category_id' => 'required|integer|min:1',
            'tax_id'      => 'nullable|integer|min:1',
            'is_active'   => 'boolean',
        ];

        $messages = [
            'code.required'        => '科目コードは必須です。',
            'code.unique'          => 'この科目コードは既に使用されています。',
            'code.max'             => '科目コードは20文字以内で入力してください。',
            'name.required'        => '科目名は必須です。',
            'name.max'             => '科目名は100文字以内で入力してください。',
            'category_id.required' => '区分（中分類）は必須です。',
            'category_id.integer'  => '区分IDは数値で入力してください。',
            'category_id.min'      => '区分IDは1以上で入力してください。',
            'tax_id.integer'       => '税区分IDは数値で入力してください。',
            'tax_id.min'           => '税区分IDは1以上で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理
        $validated['category_id'] = (int)$validated['category_id'];
        
        if (isset($validated['tax_id']) && $validated['tax_id'] !== '') {
            $validated['tax_id'] = (int)$validated['tax_id'];
        } else {
            $validated['tax_id'] = null;
        }

        $validated['is_active'] = $request->has('is_active');

        try {
            $account->update($validated);
            
            return redirect()->route('masters.accounts.index')
                ->with([
                    'success' => '会計科目を更新しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '更新に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        try {
            // 可选：检查是否有交易记录关联
            // if ($account->transactions()->count() > 0) {
            //     return redirect()->back()->with('error', '取引履歴があるため削除できません。');
            // }

            $account->delete();
            
            return redirect()->route('masters.accounts.index')
                ->with([
                    'success' => '会計科目を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.accounts.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}