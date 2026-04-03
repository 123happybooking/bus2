<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Driver;
use App\Models\Masters\Branch;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with('branch');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_kana', 'like', "%{$search}%")
                  ->orWhere('driver_code', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('license_type', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('branch_id') && $request->branch_id != '') {
            $query->where('branch_id', $request->branch_id);
        }
        
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }
        
        $drivers = $query->orderBy('display_order')->orderBy('driver_code')->paginate(20);
        
        $branches = Branch::orderBy('branch_code')->get(['id', 'branch_code', 'branch_name']);
        
        if ($request->has('search')) {
            $drivers->appends(['search' => $request->search]);
        }
        if ($request->has('branch_id')) {
            $drivers->appends(['branch_id' => $request->branch_id]);
        }
        if ($request->has('is_active')) {
            $drivers->appends(['is_active' => $request->is_active]);
        }
        if ($request->has('license_expiring')) {
            $drivers->appends(['license_expiring' => $request->license_expiring]);
        }
        
        return view('masters.drivers.index', compact('drivers', 'branches'));
    }

    public function create()
    {
        $branches = Branch::orderBy('branch_code')->get(['id', 'branch_code', 'branch_name']);
        return view('masters.drivers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'driver_code' => 'required|string|max:20|unique:drivers,driver_code',
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'hire_date' => 'required|date',
            'license_type' => 'required|string|max:50',
            'license_expiration_date' => 'required|date|after:today',
            'email' => 'nullable|email|max:100',
            'display_order' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        $messages = [
            'branch_id.required' => '支店は必須です。',
            'branch_id.exists' => '選択された支店は有効ではありません。',
            'driver_code.required' => 'ドライバーコードは必須です。',
            'driver_code.unique' => 'このドライバーコードは既に使用されています。',
            'driver_code.max' => 'ドライバーコードは20文字以内で入力してください。',
            'name.required' => '氏名は必須です。',
            'name.max' => '氏名は100文字以内で入力してください。',
            'name_kana.max' => '氏名（カナ）は100文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'birth_date.date' => '生年月日は有効な日付を入力してください。',
            'hire_date.required' => '入社日は必須です。',
            'hire_date.date' => '入社日は有効な日付を入力してください。',
            'license_type.required' => '免許種類は必須です。',
            'license_type.max' => '免許種類は50文字以内で入力してください。',
            'license_expiration_date.required' => '免許有効期限は必須です。',
            'license_expiration_date.date' => '免許有効期限は有効な日付を入力してください。',
            'license_expiration_date.after' => '免許有効期限は今日以降の日付を入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Driver::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        Driver::create($validated);
        
        return redirect()->route('masters.drivers.index')
            ->with([
                'success' => 'ドライバーを登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $driver = Driver::with('branch')->findOrFail($id);
        return view('masters.drivers.show', compact('driver'));
    }

    public function edit($id)
    {
        $driver = Driver::findOrFail($id);
        $branches = Branch::orderBy('branch_code')->get(['id', 'branch_code', 'branch_name']);
        return view('masters.drivers.edit', compact('driver', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'driver_code' => 'required|string|max:20|unique:drivers,driver_code,' . $id,
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'hire_date' => 'required|date',
            'license_type' => 'required|string|max:50',
            'license_expiration_date' => 'required|date',
            'email' => 'nullable|email|max:100',
            'display_order' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        $messages = [
            'branch_id.required' => '支店は必須です。',
            'branch_id.exists' => '選択された支店は有効ではありません。',
            'driver_code.required' => 'ドライバーコードは必須です。',
            'driver_code.unique' => 'このドライバーコードは既に使用されています。',
            'driver_code.max' => 'ドライバーコードは20文字以内で入力してください。',
            'name.required' => '氏名は必須です。',
            'name.max' => '氏名は100文字以内で入力してください。',
            'name_kana.max' => '氏名（カナ）は100文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'birth_date.date' => '生年月日は有効な日付を入力してください。',
            'hire_date.required' => '入社日は必須です。',
            'hire_date.date' => '入社日は有効な日付を入力してください。',
            'license_type.required' => '免許種類は必須です。',
            'license_type.max' => '免許種類は50文字以内で入力してください。',
            'license_expiration_date.required' => '免許有効期限は必須です。',
            'license_expiration_date.date' => '免許有効期限は有効な日付を入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $driver = Driver::findOrFail($id);
        $driver->update($validated);
        
        return redirect()->route('masters.drivers.index')
            ->with([
                'success' => 'ドライバーを更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->delete();
        
        return redirect()->route('masters.drivers.index')
            ->with([
                'success' => 'ドライバーを削除しました。',
                'alert-type' => 'success'
            ]);
    }
}