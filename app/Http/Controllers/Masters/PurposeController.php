<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Purpose;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurposeController extends Controller
{
    public function index(Request $request)
    {
        $query = Purpose::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('purpose_code', 'like', "%{$search}%")
                  ->orWhere('purpose_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        
        $purposes = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $purposes->appends(['search' => $request->search]);
        }
        
        return view('masters.purposes.index', compact('purposes'));
    }

    public function create()
    {
        return view('masters.purposes.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'purpose_code' => 'required|string|max:20|unique:purposes,purpose_code',
            'purpose_name' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'purpose_code.required' => '目的コードは必須です。',
            'purpose_code.unique' => 'この目的コードは既に使用されています。',
            'purpose_code.max' => '目的コードは20文字以内で入力してください。',
            'purpose_name.required' => '目的名は必須です。',
            'purpose_name.max' => '目的名は100文字以内で入力してください。',
            'category.max' => 'カテゴリは50文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Purpose::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        try {
            Purpose::create($validated);

            return redirect()->route('masters.purposes.index')
                ->with([
                    'success' => '目的を登録しました。',
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

    public function show(Purpose $purpose)
    {
        return view('masters.purposes.show', compact('purpose'));
    }

    public function edit(Purpose $purpose)
    {
        return view('masters.purposes.edit', compact('purpose'));
    }

    public function update(Request $request, Purpose $purpose)
    {
        $rules = [
            'purpose_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('purposes')->ignore($purpose->id),
            ],
            'purpose_name' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'purpose_code.required' => '目的コードは必須です。',
            'purpose_code.unique' => 'この目的コードは既に使用されています。',
            'purpose_code.max' => '目的コードは20文字以内で入力してください。',
            'purpose_name.required' => '目的名は必須です。',
            'purpose_name.max' => '目的名は100文字以内で入力してください。',
            'category.max' => 'カテゴリは50文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            $purpose->update($validated);

            return redirect()->route('masters.purposes.index')
                ->with([
                    'success' => '目的情報を更新しました。',
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

    public function destroy(Purpose $purpose)
    {
        try {
            $purpose->delete();

            return redirect()->route('masters.purposes.index')
                ->with([
                    'success' => '目的を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            return redirect()->route('masters.purposes.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}