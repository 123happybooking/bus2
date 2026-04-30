<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DriverExpenseType;
use Illuminate\Http\Request;

class DriverExpenseTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverExpenseType::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('type_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }
        
        $perPage = $request->input('per_page', 20);
        $expenseTypes = $query->orderBy('id')->paginate($perPage);
        
        if ($request->has('search')) {
            $expenseTypes->appends(['search' => $request->search]);
        }
        
        return view('masters.driver-expense-types.index', compact('expenseTypes'));
    }

    public function create()
    {
        $categories = [
            'TRANSPORT' => 'TRANSPORT',
            'VEHICLE' => 'VEHICLE',
            'STAFF' => 'STAFF',
            'OTHER' => 'OTHER',
        ];
        return view('masters.driver-expense-types.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'type_name' => 'required|string|max:100',
            'category' => 'required|string|in:TRANSPORT,VEHICLE,STAFF,OTHER',
            'remark' => 'nullable|string|max:500',
        ];

        $messages = [
            'type_name.required' => '経費種別名は必須です。',
            'type_name.max' => '経費種別名は100文字以内で入力してください。',
            'category.required' => 'カテゴリーは必須です。',
            'category.in' => 'カテゴリーの値が不正です。',
            'remark.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        DriverExpenseType::create($validated);

        return redirect()->route('masters.driver-expense-types.index')
            ->with('success', '経費種別を登録しました。');
    }

    public function edit($id)
    {
        $expenseType = DriverExpenseType::findOrFail($id);
        $categories = [
            'TRANSPORT' => 'TRANSPORT',
            'VEHICLE' => 'VEHICLE',
            'STAFF' => 'STAFF',
            'OTHER' => 'OTHER',
        ];
        return view('masters.driver-expense-types.edit', compact('expenseType', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'type_name' => 'required|string|max:100',
            'category' => 'required|string|in:TRANSPORT,VEHICLE,STAFF,OTHER',
            'remark' => 'nullable|string|max:500',
        ];

        $messages = [
            'type_name.required' => '経費種別名は必須です。',
            'type_name.max' => '経費種別名は100文字以内で入力してください。',
            'category.required' => 'カテゴリーは必須です。',
            'category.in' => 'カテゴリーの値が不正です。',
            'remark.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $expenseType = DriverExpenseType::findOrFail($id);
        $expenseType->update($validated);

        return redirect()->route('masters.driver-expense-types.index')
            ->with('success', '経費種別を更新しました。');
    }

    public function destroy($id)
    {
        $expenseType = DriverExpenseType::findOrFail($id);
        $expenseType->delete();

        return redirect()->route('masters.driver-expense-types.index')
            ->with('success', '経費種別を削除しました。');
    }
}