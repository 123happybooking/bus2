<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Fee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Fee::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fee_code', 'like', "%{$search}%")
                  ->orWhere('fee_name', 'like', "%{$search}%")
                  ->orWhere('fee_category', 'like', "%{$search}%");
            });
        }
        
        $fees = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $fees->appends(['search' => $request->search]);
        }
        
        return view('masters.fees.index', compact('fees'));
    }

    public function create()
    {
        return view('masters.fees.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'fee_code' => 'required|string|max:20|unique:fees,fee_code',
            'fee_name' => 'required|string|max:100',
            'fee_category' => 'required|string|max:50',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'default_amount' => 'required|numeric|min:0',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'fee_code.required' => '料金コードは必須です。',
            'fee_code.unique' => 'この料金コードは既に使用されています。',
            'fee_code.max' => '料金コードは20文字以内で入力してください。',
            'fee_name.required' => '項目名は必須です。',
            'fee_name.max' => '項目名は100文字以内で入力してください。',
            'fee_category.required' => '区分は必須です。',
            'fee_category.max' => '区分は50文字以内で入力してください。',
            'tax_rate.required' => '税率は必須です。',
            'tax_rate.numeric' => '税率は数値で入力してください。',
            'tax_rate.min' => '税率は0以上で入力してください。',
            'tax_rate.max' => '税率は100以下で入力してください。',
            'default_amount.required' => '標準単価は必須です。',
            'default_amount.numeric' => '標準単価は数値で入力してください。',
            'default_amount.min' => '標準単価は0以上で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Fee::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        try {
            Fee::create($validated);

            return redirect()->route('masters.fees.index')
                ->with([
                    'success' => '料金を登録しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function show(Fee $fee)
    {
        return view('masters.fees.show', compact('fee'));
    }

    public function edit(Fee $fee)
    {
        return view('masters.fees.edit', compact('fee'));
    }

    public function update(Request $request, Fee $fee)
    {
        $rules = [
            'fee_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('fees')->ignore($fee->id),
            ],
            'fee_name' => 'required|string|max:100',
            'fee_category' => 'required|string|max:50',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'default_amount' => 'required|numeric|min:0',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'fee_code.required' => '料金コードは必須です。',
            'fee_code.unique' => 'この料金コードは既に使用されています。',
            'fee_code.max' => '料金コードは20文字以内で入力してください。',
            'fee_name.required' => '項目名は必須です。',
            'fee_name.max' => '項目名は100文字以内で入力してください。',
            'fee_category.required' => '区分は必須です。',
            'fee_category.max' => '区分は50文字以内で入力してください。',
            'tax_rate.required' => '税率は必須です。',
            'tax_rate.numeric' => '税率は数値で入力してください。',
            'tax_rate.min' => '税率は0以上で入力してください。',
            'tax_rate.max' => '税率は100以下で入力してください。',
            'default_amount.required' => '標準単価は必須です。',
            'default_amount.numeric' => '標準単価は数値で入力してください。',
            'default_amount.min' => '標準単価は0以上で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            $fee->update($validated);

            return redirect()->route('masters.fees.index')
                ->with([
                    'success' => '料金情報を更新しました。',
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

    public function destroy(Fee $fee)
    {
        try {
            $fee->delete();

            return redirect()->route('masters.fees.index')
                ->with([
                    'success' => '料金を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            return redirect()->route('masters.fees.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}