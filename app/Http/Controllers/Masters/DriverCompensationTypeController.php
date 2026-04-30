<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DriverCompensationType;
use Illuminate\Http\Request;

class DriverCompensationTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverCompensationType::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('comp_name', 'like', "%{$search}%");
        }
        
        $perPage = $request->input('per_page', 20);
        $compensationTypes = $query->orderBy('display_order')->orderBy('id')->paginate($perPage);
        
        if ($request->has('search')) {
            $compensationTypes->appends(['search' => $request->search]);
        }
        
        return view('masters.driver-compensation-types.index', compact('compensationTypes'));
    }

    public function create()
    {
        return view('masters.driver-compensation-types.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'comp_name' => 'required|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'comp_name.required' => '手当名称は必須です。',
            'comp_name.max' => '手当名称は100文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = DriverCompensationType::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        DriverCompensationType::create($validated);

        return redirect()->route('masters.driver-compensation-types.index')
            ->with('success', '手当種別を登録しました。');
    }

    public function edit($id)
    {
        $compensationType = DriverCompensationType::findOrFail($id);
        return view('masters.driver-compensation-types.edit', compact('compensationType'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'comp_name' => 'required|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'comp_name.required' => '手当名称は必須です。',
            'comp_name.max' => '手当名称は100文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $compensationType = DriverCompensationType::findOrFail($id);
        $compensationType->update($validated);

        return redirect()->route('masters.driver-compensation-types.index')
            ->with('success', '手当種別を更新しました。');
    }

    public function destroy($id)
    {
        $compensationType = DriverCompensationType::findOrFail($id);
        $compensationType->delete();

        return redirect()->route('masters.driver-compensation-types.index')
            ->with('success', '手当種別を削除しました。');
    }
}