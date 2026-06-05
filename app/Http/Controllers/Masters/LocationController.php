<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query();
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('area', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }
        
        $perPage = $request->input('per_page', 20);
        $locations = $query->orderBy('id', 'desc')->paginate($perPage);
        
        return view('masters.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('masters.locations.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'area' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'name' => 'required|string|max:200',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:500',
        ];

        $messages = [
            'name.required' => '施設名は必須です。',
            'name.max' => '施設名は200文字以内で入力してください。',
            'area.max' => '地区は100文字以内で入力してください。',
            'category.max' => '分類は100文字以内で入力してください。',
            'address.max' => '住所は500文字以内で入力してください。',
            'phone.max' => '電話番号は50文字以内で入力してください。',
            'remark.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $location = Location::create($validated);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '登録しました',
                    'location' => $location
                ]);
            }
            
            return redirect()->route('masters.locations.index')->with('success', '場所施設を登録しました。');
            
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '保存に失敗しました: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', '保存に失敗しました')->withInput();
        }
    }

    public function show($id)
    {
        $location = Location::findOrFail($id);
        return view('masters.locations.show', compact('location'));
    }

    public function edit($id)
    {
        $location = Location::findOrFail($id);
        return view('masters.locations.edit', compact('location'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'area' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'name' => 'required|string|max:200',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:500',
        ];

        $messages = [
            'name.required' => '施設名は必須です。',
            'name.max' => '施設名は200文字以内で入力してください。',
            'area.max' => '地区は100文字以内で入力してください。',
            'category.max' => '分類は100文字以内で入力してください。',
            'address.max' => '住所は500文字以内で入力してください。',
            'phone.max' => '電話番号は50文字以内で入力してください。',
            'remark.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $location = Location::findOrFail($id);
        $location->update($validated);

        return redirect()->route('masters.locations.index')->with('success', '場所施設を更新しました。');
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return redirect()->route('masters.locations.index')->with('success', '場所施設を削除しました。');
    }
    
    public function createWin()
    {
        return view('masters.locations.create_win');
    }
}