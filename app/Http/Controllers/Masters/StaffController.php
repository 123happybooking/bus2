<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Staff;
use App\Models\Masters\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('staff_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('login_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }
        
        $staffs = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $staffs->appends(['search' => $request->search]);
        }
        
        return view('masters.staffs.index', compact('staffs'));
    }

    public function create()
    {
        $branches = Branch::orderBy('display_order')->get();
        return view('masters.staffs.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'staff_code' => 'required|string|max:20|unique:staffs,staff_code',
            'name' => 'required|string|max:100',
            'login_id' => 'required|string|max:50|unique:staffs,login_id',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'is_active' => 'nullable|boolean',
            'email' => 'nullable|email|max:100|unique:staffs,email',
            'phone_number' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'branch_id.required' => '所属営業所は必須です。',
            'branch_id.exists' => '選択された営業所は存在しません。',
            'staff_code.required' => 'スタッフコードは必須です。',
            'staff_code.unique' => 'このスタッフコードは既に使用されています。',
            'staff_code.max' => 'スタッフコードは20文字以内で入力してください。',
            'name.required' => 'スタッフ名は必須です。',
            'name.max' => 'スタッフ名は100文字以内で入力してください。',
            'login_id.required' => 'ログインIDは必須です。',
            'login_id.unique' => 'このログインIDは既に使用されています。',
            'login_id.max' => 'ログインIDは50文字以内で入力してください。',
            'password.required' => 'パスワードは必須です。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワード確認が一致しません。',
            'role.required' => '権限は必須です。',
            'role.in' => '指定された権限は無効です。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['password'] = Hash::make($validated['password']);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Staff::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        Staff::create($validated);

        return redirect()->route('masters.staffs.index')
            ->with([
                'success' => 'スタッフを登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $staff = Staff::with('branch')->findOrFail($id);
        return view('masters.staffs.show', compact('staff'));
    }

    public function edit($id)
    {
        $staff = Staff::findOrFail($id);
        $branches = Branch::orderBy('display_order')->get();
        return view('masters.staffs.edit', compact('staff', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'staff_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('staffs')->ignore($id),
            ],
            'name' => 'required|string|max:100',
            'login_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('staffs')->ignore($id),
            ],
            'role' => 'required|string',
            'is_active' => 'nullable|boolean',
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('staffs')->ignore($id),
            ],
            'phone_number' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer|min:0',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        $messages = [
            'staff_code.required' => 'スタッフコードは必須です。',
            'staff_code.unique' => 'このスタッフコードは既に使用されています。',
            'login_id.unique' => 'このログインIDは既に使用されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワード確認が一致しません。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $staff = Staff::findOrFail($id);
        $staff->update($validated);

        return redirect()->route('masters.staffs.index')
            ->with([
                'success' => 'スタッフ情報を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return redirect()->route('masters.staffs.index')
            ->with([
                'success' => 'スタッフを削除しました。',
                'alert-type' => 'success'
            ]);
    }
}