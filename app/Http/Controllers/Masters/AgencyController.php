<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgencyController extends Controller
{
    public function index(Request $request)
    {
        $query = Agency::query();
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('agency_code', 'like', "%{$search}%")
                  ->orWhere('agency_name', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }
        
        $agencies = $query->orderBy('display_order')->paginate(20);
        
        return view('masters.agencies.index', compact('agencies'));
    }

    public function create()
    {
        return view('masters.agencies.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'agency_code' => 'required|unique:agencies|max:50',
            'agency_name' => 'required|max:100',
            'branch_name' => 'nullable|max:100',
            'postal_code' => 'nullable|max:10',
            'address' => 'nullable|max:255',
            'phone_number' => 'nullable|max:20',
            'fax_number' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'manager_name' => 'nullable|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'closing_day' => 'nullable|integer|min:1|max:31',
            'payment_day' => 'nullable|integer|min:0',
            'display_order' => 'nullable|integer|min:0|max:999',
            'country' => 'nullable|max:50',
            'type' => 'nullable|max:50',
            'is_active' => 'boolean',
            'remarks' => 'nullable'
        ];

        $messages = [
            'agency_code.required' => '代理店コードは必須です。',
            'agency_code.unique' => 'この代理店コードは既に使用されています。',
            'agency_code.max' => '代理店コードは50文字以内で入力してください。',
            'agency_name.required' => '代理店名は必須です。',
            'agency_name.max' => '代理店名は100文字以内で入力してください。',
            'branch_name.max' => '支店名は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は255文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'commission_rate.numeric' => '手数料率は数値で入力してください。',
            'commission_rate.min' => '手数料率は0以上で入力してください。',
            'commission_rate.max' => '手数料率は100以下で入力してください。',
            'closing_day.integer' => '締め日は整数で入力してください。',
            'closing_day.min' => '締め日は1以上で入力してください。',
            'closing_day.max' => '締め日は31以下で入力してください。',
            'payment_day.integer' => '支払日は整数で入力してください。',
            'payment_day.min' => '支払日は0以上で入力してください。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上で入力してください。',
            'display_order.max' => '表示順は999以下で入力してください。',
            'country.max' => '国名は50文字以内で入力してください。',
            'type.max' => 'タイプは50文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Agency::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        Agency::create($validated);
        
        return redirect()->route('masters.agencies.index')->with('success', '代理店を登録しました');
    }

    public function show($id)
    {
        $agency = Agency::findOrFail($id);
        return view('masters.agencies.show', compact('agency'));
    }

    public function edit($id)
    {
        $agency = Agency::findOrFail($id);
        return view('masters.agencies.edit', compact('agency'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'agency_code' => 'required|max:50|unique:agencies,agency_code,' . $id,
            'agency_name' => 'required|max:100',
            'branch_name' => 'nullable|max:100',
            'postal_code' => 'nullable|max:10',
            'address' => 'nullable|max:255',
            'phone_number' => 'nullable|max:20',
            'fax_number' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'manager_name' => 'nullable|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'closing_day' => 'nullable|integer|min:1|max:31',
            'payment_day' => 'nullable|integer|min:0',
            'display_order' => 'nullable|integer|min:0|max:999',
            'country' => 'nullable|max:50',
            'type' => 'nullable|max:50',
            'is_active' => 'boolean',
            'remarks' => 'nullable'
        ];

        $messages = [
            'agency_code.required' => '代理店コードは必須です。',
            'agency_code.unique' => 'この代理店コードは既に使用されています。',
            'agency_code.max' => '代理店コードは50文字以内で入力してください。',
            'agency_name.required' => '代理店名は必須です。',
            'agency_name.max' => '代理店名は100文字以内で入力してください。',
            'branch_name.max' => '支店名は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は255文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'commission_rate.numeric' => '手数料率は数値で入力してください。',
            'commission_rate.min' => '手数料率は0以上で入力してください。',
            'commission_rate.max' => '手数料率は100以下で入力してください。',
            'closing_day.integer' => '締め日は整数で入力してください。',
            'closing_day.min' => '締め日は1以上で入力してください。',
            'closing_day.max' => '締め日は31以下で入力してください。',
            'payment_day.integer' => '支払日は整数で入力してください。',
            'payment_day.min' => '支払日は0以上で入力してください。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上で入力してください。',
            'display_order.max' => '表示順は999以下で入力してください。',
            'country.max' => '国名は50文字以内で入力してください。',
            'type.max' => 'タイプは50文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $agency = Agency::findOrFail($id);
        $agency->update($validated);
        
        return redirect()->route('masters.agencies.index')->with('success', '代理店を更新しました');
    }

    public function destroy($id)
    {
        $agency = Agency::findOrFail($id);
        $agency->delete();
        
        return redirect()->route('masters.agencies.index')->with('success', '代理店を削除しました');
    }
}