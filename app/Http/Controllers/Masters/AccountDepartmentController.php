<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountDepartment; 
use Illuminate\Http\Request;

class AccountDepartmentController extends Controller
{
    /**
     * 部门列表
     */
    public function index(Request $request)
    {
        $query = AccountDepartment::query();
        
        // 搜索功能：仅搜索部门名称
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $perPage = 20; // 默认值
        $allowedPerPages = [20, 30, 50]; // 允许的选项
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 排序：默认按 ID 降序（新注册的在前），也可改为按 name 升序
        $departments = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // 保持分页链接中的查询参数
        $departments->appends(['search' => $request->search, 'per_page' => $perPage]);
        
        return view('masters.account-departments.index', compact('departments'));
    }

    /**
     * 显示创建表单
     */
    public function create()
    {
        return view('masters.account-departments.create');
    }

    /**
     * 存储新部门
     */
    public function store(Request $request)
    {
        // 验证规则：仅需验证 name
        $rules = [
            'name' => 'required|string|max:100|unique:account_departments,name',
        ];

        $messages = [
            'name.required' => '部門名称は必須です。',
            'name.unique'   => 'この部門名称は既に使用されています。',
            'name.max'      => '部門名称は100文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理：去除首尾空格
        $validated['name'] = trim($validated['name']);

        try {
            AccountDepartment::create($validated);
            
            return redirect()->route('masters.account-departments.index')
                ->with([
                    'success' => '部門情報を登録しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            // 可以在这里记录日志 Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    /**
     * 显示部门详情
     */
    public function show($id)
    {
        $department = AccountDepartment::findOrFail($id);
        return view('masters.account-departments.show', compact('department'));
    }

    /**
     * 显示编辑表单
     */
    public function edit($id)
    {
        $department = AccountDepartment::findOrFail($id);
        return view('masters.account-departments.edit', compact('department'));
    }

    /**
     * 更新部门信息
     */
    public function update(Request $request, $id)
    {
        $department = AccountDepartment::findOrFail($id);
        
        $rules = [
            // 更新时排除当前记录的 ID
            'name' => 'required|string|max:100|unique:account_departments,name,' . $department->id,
        ];

        $messages = [
            'name.required' => '部門名称は必須です。',
            'name.unique'   => 'この部門名称は既に使用されています。',
            'name.max'      => '部門名称は100文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理
        $validated['name'] = trim($validated['name']);

        try {
            $department->update($validated);
            
            return redirect()->route('masters.account-departments.index')
                ->with([
                    'success' => '部門情報を更新しました。',
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
     * 删除部门
     */
    public function destroy($id)
    {
        $department = AccountDepartment::findOrFail($id);
        
        try {
            // 可选：在此处添加检查，例如是否有交易记录关联了该部门
            // if ($department->transactions()->count() > 0) { ... }

            $department->delete();
            
            return redirect()->route('masters.account-departments.index')
                ->with([
                    'success' => '部門情報を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.account-departments.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}