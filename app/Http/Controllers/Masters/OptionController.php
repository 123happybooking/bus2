<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Option;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Option::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        
        $options = $query->orderBy('display_order')->orderBy('id')->paginate(20);
        
        if ($request->has('search')) {
            $options->appends(['search' => $request->search]);
        }
        
        return view('masters.options.index', compact('options'));
    }

    public function create()
    {
        return view('masters.options.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'name.required' => 'オプション名は必須です。',
            'name.max' => 'オプション名は100文字以内で入力してください。',
            'category.required' => 'カテゴリは必須です。',
            'category.max' => 'カテゴリは50文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Option::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        Option::create($validated);

        return redirect()->route('masters.options.index')
            ->with('success', 'オプションを登録しました。');
    }

    public function edit($id)
    {
        $option = Option::findOrFail($id);
        return view('masters.options.edit', compact('option'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'name.required' => 'オプション名は必須です。',
            'name.max' => 'オプション名は100文字以内で入力してください。',
            'category.required' => 'カテゴリは必須です。',
            'category.max' => 'カテゴリは50文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $option = Option::findOrFail($id);
        $option->update($validated);

        return redirect()->route('masters.options.index')
            ->with('success', 'オプションを更新しました。');
    }

    public function show($id)
    {
    }

    public function destroy($id)
    {
        $option = Option::findOrFail($id);
        $option->delete();

        return redirect()->route('masters.options.index')
            ->with('success', 'オプションを削除しました。');
    }
}