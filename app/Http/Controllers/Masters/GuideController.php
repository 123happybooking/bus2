<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Guide;
use App\Models\Masters\Branch;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index(Request $request)
    {
        $query = Guide::with('branch');
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('guide_code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('name_kana', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
        }
        
        $guides = $query->orderBy('display_order')->orderBy('guide_code')->paginate(20);
        
        return view('masters.guides.index', compact('guides'));
    }

    public function create()
    {
        $branches = Branch::orderBy('branch_code')->get();
        return view('masters.guides.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'guide_code' => 'required|string|max:20|unique:guides,guide_code',
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'employment_type' => 'required|in:自社,契約,業務委託',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'branch_id.required' => '所属営業所は必須です。',
            'branch_id.exists' => '選択された営業所は存在しません。',
            'guide_code.required' => 'ガイドコードは必須です。',
            'guide_code.max' => 'ガイドコードは20文字以内で入力してください。',
            'guide_code.unique' => 'このガイドコードは既に使用されています。',
            'name.required' => 'ガイド名は必須です。',
            'name.max' => 'ガイド名は100文字以内で入力してください。',
            'name_kana.max' => 'ガイド名（カナ）は100文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'employment_type.required' => '雇用形態は必須です。',
            'employment_type.in' => '指定された雇用形態は無効です。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Guide::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        Guide::create($validated);

        return redirect()->route('masters.guides.index')->with('success', 'ガイドを登録しました。');
    }

    public function show($id)
    {
        $guide = Guide::with('branch')->findOrFail($id);
        return view('masters.guides.show', compact('guide'));
    }

    public function edit($id)
    {
        $guide = Guide::findOrFail($id);
        $branches = Branch::orderBy('branch_code')->get();
        return view('masters.guides.edit', compact('guide', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'guide_code' => 'required|string|max:20|unique:guides,guide_code,' . $id,
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'employment_type' => 'required|in:自社,契約,業務委託',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'branch_id.required' => '所属営業所は必須です。',
            'branch_id.exists' => '選択された営業所は存在しません。',
            'guide_code.required' => 'ガイドコードは必須です。',
            'guide_code.max' => 'ガイドコードは20文字以内で入力してください。',
            'guide_code.unique' => 'このガイドコードは既に使用されています。',
            'name.required' => 'ガイド名は必須です。',
            'name.max' => 'ガイド名は100文字以内で入力してください。',
            'name_kana.max' => 'ガイド名（カナ）は100文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'employment_type.required' => '雇用形態は必須です。',
            'employment_type.in' => '指定された雇用形態は無効です。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $guide = Guide::findOrFail($id);
        $guide->update($validated);

        return redirect()->route('masters.guides.index')->with('success', 'ガイドを更新しました。');
    }

    public function destroy($id)
    {
        $guide = Guide::findOrFail($id);
        $guide->delete();

        return redirect()->route('masters.guides.index')->with('success', 'ガイドを削除しました。');
    }
}