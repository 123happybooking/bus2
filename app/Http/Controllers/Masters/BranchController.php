<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Branch;
use App\Models\Masters\Driver;
use App\Models\Masters\Staff;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Guide;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('branch_code', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }
        
        $branches = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $branches->appends(['search' => $request->search]);
        }
        
        return view('masters.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('masters.branches.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'branch_code' => 'required|string|max:20|unique:branches,branch_code',
            'branch_name' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0|max:999',
        ];

        $messages = [
            'branch_code.required' => '営業所コードは必須です。',
            'branch_code.unique' => 'この営業所コードは既に使用されています。',
            'branch_code.max' => '営業所コードは20文字以内で入力してください。',
            'branch_name.required' => '営業所名は必須です。',
            'branch_name.max' => '営業所名は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上で入力してください。',
            'display_order.max' => '表示順は999以下で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === '' || $validated['display_order'] === null) {
            $validated['display_order'] = 0;
        } else {
            $validated['display_order'] = (int)$validated['display_order'];
        }

        Branch::create($validated);
        
        return redirect()->route('masters.branches.index')
            ->with([
                'success' => '営業所を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $branch = Branch::findOrFail($id);
        return view('masters.branches.show', compact('branch'));
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('masters.branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'branch_code' => 'required|string|max:20|unique:branches,branch_code,' . $id,
            'branch_name' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0|max:999',
        ];

        $messages = [
            'branch_code.required' => '営業所コードは必須です。',
            'branch_code.unique' => 'この営業所コードは既に使用されています。',
            'branch_code.max' => '営業所コードは20文字以内で入力してください。',
            'branch_name.required' => '営業所名は必須です。',
            'branch_name.max' => '営業所名は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上で入力してください。',
            'display_order.max' => '表示順は999以下で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === '' || $validated['display_order'] === null) {
            $validated['display_order'] = 0;
        } else {
            $validated['display_order'] = (int)$validated['display_order'];
        }

        $branch = Branch::findOrFail($id);
        $branch->update($validated);
        
        return redirect()->route('masters.branches.index')
            ->with([
                'success' => '営業所を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $vehiclesCount = Vehicle::where('branch_id', $id)->count();
        if ($vehiclesCount > 0) {
            return redirect()->back()
                ->with('error', 'この営業所は車両マスタで使用されているため削除できません。');
        }
        
        $driversCount = Driver::where('branch_id', $id)->count();
        if ($driversCount > 0) {
            return redirect()->back()
                ->with('error', 'この営業所はドライバーマスタで使用されているため削除できません。');
        }
        
        $staffCount = Staff::where('branch_id', $id)->count();
        if ($staffCount > 0) {
            return redirect()->back()
                ->with('error', 'この営業所はスタッフマスタで使用されているため削除できません。');
        }
        
        $guidesCount = Guide::where('branch_id', $id)->count();
        if ($guidesCount > 0) {
            return redirect()->back()
                ->with('error', 'この営業所はガイドマスタで使用されているため削除できません。');
        }
        
        $branch = Branch::findOrFail($id);
        $branch->delete();
        
        return redirect()->route('masters.branches.index')
            ->with([
                'success' => '営業所を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}