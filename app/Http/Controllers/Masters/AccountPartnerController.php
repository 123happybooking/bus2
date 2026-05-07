<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountPartner; 
use Illuminate\Http\Request;

class AccountPartnerController extends Controller
{
    /**
     * 一覧画面表示
     */
    public function index(Request $request)
    {
        $query = AccountPartner::query();
        
        // 搜索功能：取引先名、会社名、責任者
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('person_in_charge', 'like', "%{$search}%");
            });
        }

        // 分類筛选 (可选功能，如果前端有下拉框)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $perPage = 20; // 默认值
        $allowedPerPages = [20, 30, 50]; // 允许的选项
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 排序：默认按 ID 降序（新注册的在前），也可改为按名称升序
        $partners = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // 保持搜索参数在分页链接中
        $partners->appends(['search' => $request->search, 'category' => $request->category, 'per_page' => $perPage]);
        
        return view('masters.account_partners.index', compact('partners'));
    }

    /**
     * 登録画面表示
     */
    public function create()
    {
        return view('masters.account_partners.create');
    }

    /**
     * 登録処理
     */
    public function store(Request $request)
    {
        $rules = [
            'name'              => 'required|string|max:100',
            'category'          => 'nullable|string|max:50',
            'company_name'      => 'nullable|string|max:100',
            'address'           => 'nullable|string',
            'registration_number'=> 'nullable|string|max:20',
            'phone'             => 'nullable|string|max:20',
            'person_in_charge'  => 'nullable|string|max:50',
        ];

        $messages = [
            'name.required'         => '取引先名は必須です。',
            'name.max'              => '取引先名は100文字以内で入力してください。',
            'category.max'          => '分類は50文字以内で入力してください。',
            'company_name.max'      => '会社名は100文字以内で入力してください。',
            'registration_number.max'=> '登録番号は20文字以内で入力してください。',
            'phone.max'             => '電話は20文字以内で入力してください。',
            'person_in_charge.max'  => '責任者は50文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理：空字符串转为 null (针对 nullable 字段)
        $validated['category']       = $validated['category'] ?: null;
        $validated['company_name']   = $validated['company_name'] ?: null;
        $validated['address']        = $validated['address'] ?: null;
        $validated['registration_number'] = $validated['registration_number'] ?: null;
        $validated['phone']          = $validated['phone'] ?: null;
        $validated['person_in_charge'] = $validated['person_in_charge'] ?: null;

        try {
            AccountPartner::create($validated);
            return redirect()->route('masters.account_partners.index')
                ->with([
                    'success' => '取引先情報を登録しました。',
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
     * 詳細画面表示
     */
    public function show($id)
    {
        $partner = AccountPartner::findOrFail($id);
        return view('masters.account_partners.show', compact('partner'));
    }

    /**
     * 編集画面表示
     */
    public function edit($id)
    {
        $partner = AccountPartner::findOrFail($id);
        return view('masters.account_partners.edit', compact('partner'));
    }

    /**
     * 更新処理
     */
    public function update(Request $request, $id)
    {
        $partner = AccountPartner::findOrFail($id);
        
        $rules = [
            'name'              => 'required|string|max:100',
            'category'          => 'nullable|string|max:50',
            'company_name'      => 'nullable|string|max:100',
            'address'           => 'nullable|string',
            'registration_number'=> 'nullable|string|max:20',
            'phone'             => 'nullable|string|max:20',
            'person_in_charge'  => 'nullable|string|max:50',
        ];

        $messages = [
            'name.required'         => '取引先名は必須です。',
            'name.max'              => '取引先名は100文字以内で入力してください。',
            'category.max'          => '分類は50文字以内で入力してください。',
            'company_name.max'      => '会社名は100文字以内で入力してください。',
            'registration_number.max'=> '登録番号は20文字以内で入力してください。',
            'phone.max'             => '電話は20文字以内で入力してください。',
            'person_in_charge.max'  => '責任者は50文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理：空字符串转为 null
        $validated['category']       = $validated['category'] ?: null;
        $validated['company_name']   = $validated['company_name'] ?: null;
        $validated['address']        = $validated['address'] ?: null;
        $validated['registration_number'] = $validated['registration_number'] ?: null;
        $validated['phone']          = $validated['phone'] ?: null;
        $validated['person_in_charge'] = $validated['person_in_charge'] ?: null;

        try {
            $partner->update($validated);
            
            return redirect()->route('masters.account_partners.index')
                ->with([
                    'success' => '取引先情報を更新しました。',
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
     * 削除処理
     */
    public function destroy($id)
    {
        $partner = AccountPartner::findOrFail($id);
        try {
            // 可选：在此处添加检查，例如是否有关联的交易记录
            // if ($partner->transactions()->count() > 0) { ... }

            $partner->delete();
            
            return redirect()->route('masters.account_partners.index')
                ->with([
                    'success' => '取引先情報を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.account_partners.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}