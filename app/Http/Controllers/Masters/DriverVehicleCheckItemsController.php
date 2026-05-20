<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverVehicleCheckItems;
use App\Models\Driver\DriverVehicleCheckCategory;
use Illuminate\Http\Request;

class DriverVehicleCheckItemsController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverVehicleCheckItems::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%");
            });
        }
        
        $perPage = $request->input('per_page', 20);
        $checkItems = $query->orderBy('display_order')->orderBy('id')->paginate($perPage);
        
        if ($request->has('search')) {
            $checkItems->appends(['search' => $request->search]);
        }
        
        return view('masters.driver-vehicle-check-items.index', compact('checkItems'));
    }

    public function create()
    {
        $categories = DriverVehicleCheckCategory::where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->pluck('category_name', 'category_name')
            ->toArray();
        
        return view('masters.driver-vehicle-check-items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'category' => 'required|string|max:100|exists:driver_vehicle_check_categories,category_name',
            'item_name' => 'required|string|max:200',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];

        $messages = [
            'category.required' => 'カテゴリーは必須です。',
            'category.exists' => '選択されたカテゴリーは無効です。',
            'item_name.required' => '点検項目名は必須です。',
            'item_name.max' => '点検項目名は200文字以内で入力してください。',
            'display_order.required' => '表示順は必須です。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上の値を入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        DriverVehicleCheckItems::create($validated);

        return redirect()->route('masters.driver-vehicle-check-items.index')
            ->with('success', '点検項目を登録しました。');
    }

    public function edit($id)
    {
        $checkItem = DriverVehicleCheckItems::findOrFail($id);
        $categories = DriverVehicleCheckCategory::where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->pluck('category_name', 'category_name')
            ->toArray();
        
        return view('masters.driver-vehicle-check-items.edit', compact('checkItem', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'category' => 'required|string|max:100|exists:driver_vehicle_check_categories,category_name',
            'item_name' => 'required|string|max:200',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];

        $messages = [
            'category.required' => 'カテゴリーは必須です。',
            'category.exists' => '選択されたカテゴリーは無効です。',
            'item_name.required' => '点検項目名は必須です。',
            'item_name.max' => '点検項目名は200文字以内で入力してください。',
            'display_order.required' => '表示順は必須です。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上の値を入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $checkItem = DriverVehicleCheckItems::findOrFail($id);
        $checkItem->update($validated);

        return redirect()->route('masters.driver-vehicle-check-items.index')
            ->with('success', '点検項目を更新しました。');
    }

    public function destroy($id)
    {
        $checkItem = DriverVehicleCheckItems::findOrFail($id);
        $checkItem->delete();

        return redirect()->route('masters.driver-vehicle-check-items.index')
            ->with('success', '点検項目を削除しました。');
    }
}