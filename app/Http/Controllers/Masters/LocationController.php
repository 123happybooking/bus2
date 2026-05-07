<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('location_code', 'like', "%{$search}%")
                  ->orWhere('location_name', 'like', "%{$search}%")
                  ->orWhere('location_kana', 'like', "%{$search}%")
                  ->orWhere('prefecture', 'like', "%{$search}%")
                  ->orWhere('area_type', 'like', "%{$search}%");
            });
        }
        
        $locations = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $locations->appends(['search' => $request->search]);
        }
        
        return view('masters.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('masters.locations.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'location_code' => 'required|string|max:20|unique:locations,location_code',
            'location_name' => 'required|string|max:100',
            'location_kana' => 'nullable|string|max:100',
            'prefecture' => 'required|string|max:10',
            'area_type' => 'required|string|max:20',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'location_code.required' => '地域コードは必須です。',
            'location_code.unique' => 'この地域コードは既に使用されています。',
            'location_code.max' => '地域コードは20文字以内で入力してください。',
            'location_name.required' => '地域名は必須です。',
            'location_name.max' => '地域名は100文字以内で入力してください。',
            'location_kana.max' => '地域名（カナ）は100文字以内で入力してください。',
            'prefecture.required' => '都道府県は必須です。',
            'prefecture.max' => '都道府県は10文字以内で入力してください。',
            'area_type.required' => 'エリアタイプは必須です。',
            'area_type.max' => 'エリアタイプは20文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Location::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        try {
            Location::create($validated);

            return redirect()->route('masters.locations.index')
                ->with([
                    'success' => '地域を登録しました。',
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

    public function show(Location $location)
    {
        return view('masters.locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        return view('masters.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $rules = [
            'location_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('locations')->ignore($location->id),
            ],
            'location_name' => 'required|string|max:100',
            'location_kana' => 'nullable|string|max:100',
            'prefecture' => 'required|string|max:10',
            'area_type' => 'required|string|max:20',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'location_code.required' => '地域コードは必須です。',
            'location_code.unique' => 'この地域コードは既に使用されています。',
            'location_code.max' => '地域コードは20文字以内で入力してください。',
            'location_name.required' => '地域名は必須です。',
            'location_name.max' => '地域名は100文字以内で入力してください。',
            'location_kana.max' => '地域名（カナ）は100文字以内で入力してください。',
            'prefecture.required' => '都道府県は必須です。',
            'prefecture.max' => '都道府県は10文字以内で入力してください。',
            'area_type.required' => 'エリアタイプは必須です。',
            'area_type.max' => 'エリアタイプは20文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $location->update($validated);

            return redirect()->route('masters.locations.index')
                ->with([
                    'success' => '地域情報を更新しました。',
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

    public function destroy(Location $location)
    {
        try {
            $location->delete();

            return redirect()->route('masters.locations.index')
                ->with([
                    'success' => '地域を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            return redirect()->route('masters.locations.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}