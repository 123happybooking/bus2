<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Account;
use App\Models\Masters\AccountSub; 
use Illuminate\Http\Request;

class AccountSubController extends Controller
{
    /**
     * 显示辅助科目列表
     */
    public function index(Request $request)
    {
        $query = AccountSub::with('account'); // 预加载关联的主科目

        // 搜索功能：辅助科目名称、或所属主科目的代码/名称
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('account', function($subQ) use ($search) {
                      $subQ->where('code', 'like', "%{$search}%")
                           ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // 筛选：所属主科目 (下拉框筛选)
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // 筛选：有效状态
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = 20;
        $allowedPerPages = [20, 30, 50];
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 排序：默认按 ID 降序
        $subsidiaries = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // 保留查询参数
        $subsidiaries->appends([
            'search' => $request->search, 
            'account_id' => $request->account_id, 
            'is_active' => $request->is_active, 
            'per_page' => $perPage
        ]);

        // 获取所有主科目用于搜索下拉框
        $accounts = Account::orderBy('code')->get();
        
        return view('masters.account-subs.index', compact('subsidiaries', 'accounts'));
    }

    /**
     * 显示创建表单
     */
    public function create()
    {
        // 获取所有有效的主科目供选择
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        
        return view('masters.account-subs.create', compact('accounts'));
    }

    /**
     * 存储新数据
     */
    public function store(Request $request)
    {
        $rules = [
            'account_id'  => 'required|integer|exists:accounts,id', // 必须关联存在的主科目
            'name'        => 'required|string|max:100',
            'is_active'   => 'boolean',
            // 如果需要防止同一主科目下名称重复，可取消下面注释：
            // 'name'      => 'required|string|max:100|unique:account_subs,name,NULL,id,account_id,' . $request->account_id,
        ];

        $messages = [
            'account_id.required' => '勘定科目（親）は必須です。',
            'account_id.exists'   => '選択された勘定科目が存在しません。',
            'name.required'       => '補助科目名は必須です。',
            'name.max'            => '補助科目名は100文字以内で入力してください。',
            // 'name.unique'         => 'この勘定科目下に同名の補助科目が既に存在します。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理
        $validated['account_id'] = (int)$validated['account_id'];
        $validated['is_active'] = $request->has('is_active');

        try {
            AccountSub::create($validated);
            
            return redirect()->route('masters.account-subs.index')
                ->with([
                    'success' => '補助科目を登録しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            // \Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    /**
     * 显示详情 (可选)
     */
    public function show($id)
    {
        $sub = AccountSub::with('account')->findOrFail($id);
        return view('masters.account-subs.show', compact('sub'));
    }

    /**
     * 显示编辑表单
     */
    public function edit($id)
    {
        $sub = AccountSub::findOrFail($id);
        // 获取所有主科目（包含无效的，以防当前关联的是无效科目）
        $accounts = Account::orderBy('code')->get();
        
        return view('masters.account-subs.edit', compact('sub', 'accounts'));
    }

    /**
     * 更新数据
     */
    public function update(Request $request, $id)
    {
        $sub = AccountSub::findOrFail($id);
        
        $rules = [
            'account_id'  => 'required|integer|exists:accounts,id',
            'name'        => 'required|string|max:100',
            'is_active'   => 'boolean',
            // 更新时的唯一性验证，排除当前记录
            // 'name'      => 'required|string|max:100|unique:account_subs,name,' . $sub->id . ',id,account_id,' . $request->account_id,
        ];

        $messages = [
            'account_id.required' => '勘定科目（親）は必須です。',
            'account_id.exists'   => '選択された勘定科目が存在しません。',
            'name.required'       => '補助科目名は必須です。',
            'name.max'            => '補助科目名は100文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理
        $validated['account_id'] = (int)$validated['account_id'];
        $validated['is_active'] = $request->has('is_active');

        try {
            $sub->update($validated);
            
            return redirect()->route('masters.account-subs.index')
                ->with([
                    'success' => '補助科目を更新しました。',
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

    /**
     * 删除数据
     */
    public function destroy($id)
    {
        $sub = AccountSub::findOrFail($id);
        
        try {
            // 如果有业务逻辑限制删除（例如已被凭证使用），可在此处检查
            // if ($sub->transactions()->count() > 0) { ... }

            $sub->delete(); // 软删除
            
            return redirect()->route('masters.account-subs.index')
                ->with([
                    'success' => '補助科目を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.account-subs.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}