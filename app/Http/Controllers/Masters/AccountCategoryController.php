<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountCategory; 
use Illuminate\Http\Request;

class AccountCategoryController extends Controller
{
    /**
     * 一覧表示 (Index)
     */
    public function index(Request $request)
    {
        $query = AccountCategory::query();
        
        // 搜索功能：科目名称
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // 筛选功能：层级 (Level)
        if ($request->filled('level')) {
            $query->where('level', (int)$request->level);
        }

        // 筛选功能：借贷方向 (Mark)
        if ($request->filled('mark')) {
            $query->where('mark', $request->mark);
        }

        $perPage = 20; // 默认值
        $allowedPerPages = [20, 30, 50]; // 允许的选项
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 排序：先按 level 升序，再按 sort (如果有) 或 id 升序
        // 假设表中有 sort 字段，如果没有请去掉 'sort'
        $categories = $query->orderBy('level', 'asc')->orderBy('id', 'asc')->paginate($perPage);
        
        // 保持分页链接中的查询参数
        $categories->appends(['search' => $request->search, 'level' => $request->level, 'mark' => $request->mark]);
        
        return view('masters.account-categories.index', compact('categories'));
    }

    /**
     * 作成画面表示 (Create)
     */
    public function create()
    {
        return view('masters.account-categories.create');
    }

    /**
     * 登録処理 (Store)
     */
    public function store(Request $request)
    {
        $rules = [
            'name'      => 'required|string|max:50|unique:account_categories,name',
            'mark'      => 'required|in:借,貸',
            'level'     => 'nullable|integer',
            'type'     => 'nullable|string|max:50',
        ];

        $messages = [
            'name.required'      => '区分名称は必須です。',
            'name.unique'        => 'この区分名称は既に使用されています。',
            'name.max'           => '区分名称は50文字以内で入力してください。',
            'mark.required'      => '貸借区分は必須です。',
            'mark.in'            => '貸借区分は「借」または「貸」を選択してください。',
        ];

        $validated = $request->validate($rules, $messages);
        

        try {
            AccountCategory::create($validated);
            return redirect()->route('masters.account-categories.index')
                ->with([
                    'success' => '勘定科目区分を登録しました。',
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

    /**
     * 詳細画面表示 (Show)
     */
    public function show($id)
    {
        $category = AccountCategory::findOrFail($id);
        return view('masters.account-categories.show', compact('category'));
    }

    /**
     * 編集画面表示 (Edit)
     */
    public function edit($id)
    {
        $category = AccountCategory::findOrFail($id);
        return view('masters.account-categories.edit', compact('category'));
    }

    /**
     * 更新処理 (Update)
     */
    public function update(Request $request, $id)
    {
        $category = AccountCategory::findOrFail($id);
        
        $rules = [
            'name'      => 'required|string|max:50|unique:account_categories,name,' . $category->id,
            'mark'      => 'required|in:借,貸',
            'level'     => 'nullable|integer',
            'type'     => 'nullable|string|max:50',
        ];

        $messages = [
            'name.required'      => '区分名称は必須です。',
            'name.unique'        => 'この区分名称は既に使用されています。',
            'name.max'           => '区分名称は50文字以内で入力してください。',
            'mark.required'      => '貸借区分は必須です。',
            'mark.in'            => '貸借区分は「借」または「貸」を選択してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理
        $validated['level'] = (int)$validated['level'];
        

        try {
            $category->update($validated);
            
            return redirect()->route('masters.account-categories.index')
                ->with([
                    'success' => '勘定科目区分を更新しました。',
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
     * 削除処理 (Destroy)
     */
    public function destroy($id)
    {
        $category = AccountCategory::findOrFail($id);
        
        try {
            // 【重要】实际项目中建议在此处检查是否有关联的会计科目 (accounts)
            // 例如：if ($category->accounts()->count() > 0) { throw new \Exception('使用中のため削除できません'); }
            
            $category->delete();
            
            return redirect()->route('masters.account-categories.index')
                ->with([
                    'success' => '勘定科目区分を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.account-categories.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}