<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Driver;
use App\Models\Masters\Staff;
use App\Models\Masters\Branch;
use App\Models\Masters\DriverLicenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        
        $perPage = $request->input('per_page', 20);
        $drivers = $query->orderBy('display_order')->orderBy('driver_code')->paginate($perPage);
        
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
        $licenseTypes = DriverLicenseType::where('is_active', true)->orderBy('display_order')->pluck('type_name', 'type_name');
        return view('masters.drivers.create', compact('branches', 'licenseTypes'));
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
            'login_id' => 'required|string|max:255|unique:drivers,login_id|unique:staffs,login_id',
            'password' => 'required|string|min:8|max:255|confirmed',
            'license_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'seal_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'login_id.required' => 'ログインIDは必須です。',
            'login_id.unique' => 'このログインIDは既に使用されています。',
            'password.required' => 'パスワードは必須です。',
            'password.min' => 'パスワードは6文字以上で入力してください。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
            'license_image.image' => '免許証は画像ファイルをアップロードしてください。',
            'license_image.mimes' => '免許証はjpeg、png、jpg、gif形式のファイルをアップロードしてください。',
            'license_image.max' => '免許証のファイルサイズは2MB以内でアップロードしてください。',
            'seal_image.image' => '印鑑は画像ファイルをアップロードしてください。',
            'seal_image.mimes' => '印鑑はjpeg、png、jpg、gif形式のファイルをアップロードしてください。',
            'seal_image.max' => '印鑑のファイルサイズは2MB以内でアップロードしてください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Driver::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }
        
        $driverData = [
            'login_id' => $validated['login_id'],
            'branch_id' => $validated['branch_id'],
            'driver_code' => $validated['driver_code'],
            'name' => $validated['name'],
            'name_kana' => $validated['name_kana'],
            'phone_number' => $validated['phone_number'],
            'birth_date' => $validated['birth_date'],
            'hire_date' => $validated['hire_date'],
            'license_type' => $validated['license_type'],
            'license_expiration_date' => $validated['license_expiration_date'],
            'email' => $validated['email'],
            'display_order' => $validated['display_order'],
            'remarks' => $validated['remarks'],
            'is_active' => $validated['is_active'],
        ];
        
        if ($request->hasFile('license_image') && $request->file('license_image')->isValid()) {
            $file = $request->file('license_image');
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '_license.' . $extension;
            $path = $file->storeAs('driver_licenses', $fileName, 'public');
            if ($path) {
                $driverData['license_image'] = $path;
            }
        }
        
        if ($request->hasFile('seal_image') && $request->file('seal_image')->isValid()) {
            $file = $request->file('seal_image');
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '_seal.' . $extension;
            $path = $file->storeAs('driver_seals', $fileName, 'public');
            if ($path) {
                $driverData['seal_image'] = $path;
            }
        }
        
        $driver = Driver::create($driverData);
        
        Staff::create([
            'user_company_id' => 0,
            'branch_id' => $validated['branch_id'],
            'staff_code' => $validated['driver_code'],
            'name' => $validated['name'],
            'login_id' => $validated['login_id'],
            'password' => Hash::make($validated['password']),
            'role' => 'driver',
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'display_order' => $validated['display_order'],
            'is_active' => $validated['is_active'],
        ]);
        
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
        $licenseTypes = DriverLicenseType::where('is_active', true)->orderBy('display_order')->pluck('type_name', 'type_name');
        return view('masters.drivers.edit', compact('driver', 'branches', 'licenseTypes'));
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
            'password' => 'nullable|string|min:8|max:255|confirmed',
            'license_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'seal_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'password.min' => 'パスワードは6文字以上で入力してください。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
            'license_image.image' => '免許証は画像ファイルをアップロードしてください。',
            'license_image.mimes' => '免許証はjpeg、png、jpg、gif形式のファイルをアップロードしてください。',
            'license_image.max' => '免許証のファイルサイズは2MB以内でアップロードしてください。',
            'seal_image.image' => '印鑑は画像ファイルをアップロードしてください。',
            'seal_image.mimes' => '印鑑はjpeg、png、jpg、gif形式のファイルをアップロードしてください。',
            'seal_image.max' => '印鑑のファイルサイズは2MB以内でアップロードしてください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $driver = Driver::findOrFail($id);
        
        $driverData = [
            'branch_id' => $validated['branch_id'],
            'driver_code' => $validated['driver_code'],
            'name' => $validated['name'],
            'name_kana' => $validated['name_kana'],
            'phone_number' => $validated['phone_number'],
            'birth_date' => $validated['birth_date'],
            'hire_date' => $validated['hire_date'],
            'license_type' => $validated['license_type'],
            'license_expiration_date' => $validated['license_expiration_date'],
            'email' => $validated['email'],
            'display_order' => $validated['display_order'],
            'remarks' => $validated['remarks'],
            'is_active' => $validated['is_active'],
        ];
        
        if ($request->hasFile('license_image') && $request->file('license_image')->isValid()) {
            if ($driver->license_image) {
                Storage::disk('public')->delete($driver->license_image);
            }
            $file = $request->file('license_image');
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '_license.' . $extension;
            $path = $file->storeAs('driver_licenses', $fileName, 'public');
            if ($path) {
                $driverData['license_image'] = $path;
            }
        }
        
        if ($request->hasFile('seal_image') && $request->file('seal_image')->isValid()) {
            if ($driver->seal_image) {
                Storage::disk('public')->delete($driver->seal_image);
            }
            $file = $request->file('seal_image');
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '_seal.' . $extension;
            $path = $file->storeAs('driver_seals', $fileName, 'public');
            if ($path) {
                $driverData['seal_image'] = $path;
            }
        }
        
        $driver->update($driverData);
        
        $staffData = [
            'branch_id' => $validated['branch_id'],
            'staff_code' => $validated['driver_code'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'display_order' => $validated['display_order'],
            'is_active' => $validated['is_active'],
        ];
        if (!empty($validated['password'])) {
            $staffData['password'] = Hash::make($validated['password']);
        }
        Staff::updateOrCreate(
            ['login_id' => $driver->login_id, 'role' => 'driver'],
            $staffData
        );
        
        return redirect()->route('masters.drivers.index')
            ->with([
                'success' => 'ドライバーを更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);
        if ($driver->license_image) {
            Storage::disk('public')->delete($driver->license_image);
        }
        if ($driver->seal_image) {
            Storage::disk('public')->delete($driver->seal_image);
        }
        $driver->delete();
        
        return redirect()->route('masters.drivers.index')
            ->with([
                'success' => 'ドライバーを削除しました。',
                'alert-type' => 'success'
            ]);
    }
}