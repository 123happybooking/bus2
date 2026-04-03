<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\ReservationCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ReservationCategory::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category_code', 'like', "%{$search}%")
                  ->orWhere('category_name', 'like', "%{$search}%");
            });
        }
        
        $categories = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $categories->appends(['search' => $request->search]);
        }
        
        return view('masters.reservation-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('masters.reservation-categories.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'category_code' => 'required|string|max:20|unique:reservation_categories,category_code',
            'category_name' => 'required|string|max:100',
            'color_code' => 'required|string|max:7',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'category_code.required' => 'カテゴリコードは必須です。',
            'category_code.unique' => 'このカテゴリコードは既に使用されています。',
            'category_code.max' => 'カテゴリコードは20文字以内で入力してください。',
            'category_name.required' => '分類名は必須です。',
            'category_name.max' => '分類名は100文字以内で入力してください。',
            'color_code.required' => 'カラーコードは必須です。',
            'color_code.max' => 'カラーコードは7文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = ReservationCategory::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        ReservationCategory::create($validated);

        return redirect()->route('masters.reservation-categories.index')
            ->with([
                'success' => '予約カテゴリを登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $reservationCategory = ReservationCategory::findOrFail($id);
        return view('masters.reservation-categories.show', compact('reservationCategory'));
    }

    public function edit($id)
    {
        $reservationCategory = ReservationCategory::findOrFail($id);
        return view('masters.reservation-categories.edit', compact('reservationCategory'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'category_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('reservation_categories')->ignore($id),
            ],
            'category_name' => 'required|string|max:100',
            'color_code' => 'required|string|max:7',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'category_code.required' => 'カテゴリコードは必須です。',
            'category_code.unique' => 'このカテゴリコードは既に使用されています。',
            'category_code.max' => 'カテゴリコードは20文字以内で入力してください。',
            'category_name.required' => '分類名は必須です。',
            'category_name.max' => '分類名は100文字以内で入力してください。',
            'color_code.required' => 'カラーコードは必須です。',
            'color_code.max' => 'カラーコードは7文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $reservationCategory = ReservationCategory::findOrFail($id);
        $reservationCategory->update($validated);

        return redirect()->route('masters.reservation-categories.index')
            ->with([
                'success' => '予約カテゴリ情報を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $reservationCategory = ReservationCategory::findOrFail($id);
        $reservationCategory->delete();

        return redirect()->route('masters.reservation-categories.index')
            ->with([
                'success' => '予約カテゴリを削除しました。',
                'alert-type' => 'success'
            ]);
    }
}