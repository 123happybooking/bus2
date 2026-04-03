<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Bank;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $query = Bank::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('bank_info', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }
        
        $banks = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $banks->appends(['search' => $request->search]);
        }
        
        return view('masters.banks.index', compact('banks'));
    }

    public function create()
    {
        return view('masters.banks.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'bank_name' => 'required|string|max:255|unique:banks,bank_name',
            'bank_info' => 'nullable|string',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
        ];

        $messages = [
            'bank_name.required' => '銀行名は必須です。',
            'bank_name.unique' => 'この銀行名は既に使用されています。',
            'bank_name.max' => '銀行名は255文字以内で入力してください。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === '' || $validated['display_order'] === null) {
            $validated['display_order'] = 0;
        } else {
            $validated['display_order'] = (int)$validated['display_order'];
        }

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = (bool)$validated['is_active'];
        }

        Bank::create($validated);
        
        return redirect()->route('masters.banks.index')
            ->with([
                'success' => '銀行を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $bank = Bank::findOrFail($id);
        return view('masters.banks.show', compact('bank'));
    }

    public function edit($id)
    {
        $bank = Bank::findOrFail($id);
        return view('masters.banks.edit', compact('bank'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'bank_name' => 'required|string|max:255|unique:banks,bank_name,' . $id,
            'bank_info' => 'nullable|string',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
        ];

        $messages = [
            'bank_name.required' => '銀行名は必須です。',
            'bank_name.unique' => 'この銀行名は既に使用されています。',
            'bank_name.max' => '銀行名は255文字以内で入力してください。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === '' || $validated['display_order'] === null) {
            $validated['display_order'] = 0;
        } else {
            $validated['display_order'] = (int)$validated['display_order'];
        }

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = (bool)$validated['is_active'];
        }

        $bank = Bank::findOrFail($id);
        $bank->update($validated);
        
        return redirect()->route('masters.banks.index')
            ->with([
                'success' => '銀行を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $bank = Bank::findOrFail($id);
        $bank->delete();
        
        return redirect()->route('masters.banks.index')
            ->with([
                'success' => '銀行を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}