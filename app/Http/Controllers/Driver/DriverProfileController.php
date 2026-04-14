<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Masters\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DriverProfileController extends Controller
{
    public function showChangePasswordForm()
    {
        return view('driver.password');
    }

    public function updatePassword(Request $request)
    {
        $driverId = session('driver_id');
        $staffLoginId = session('staff_login_id');
        
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ];

        $messages = [
            'current_password.required' => '現在のパスワードを入力してください。',
            'new_password.required' => '新しいパスワードを入力してください。',
            'new_password.min' => '新しいパスワードは8文字以上で入力してください。',
            'new_password.confirmed' => '新しいパスワードと確認用パスワードが一致しません。',
        ];
        
        $request->validate($rules, $messages);
        
        $staff = DB::connection()->table('staffs')
            ->where('login_id', $staffLoginId)
            ->first();
        
        if (!Hash::check($request->current_password, $staff->password)) {
            return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。']);
        }
        
        DB::connection()->table('staffs')
            ->where('login_id', $staffLoginId)
            ->update(['password' => Hash::make($request->new_password)]);
        
        return redirect()->route('driver.dashboard')->with('success', 'パスワードを変更しました。');
    }

    public function editProfile()
    {
        $driverId = session('driver_id');
        $driver = Driver::findOrFail($driverId);
        
        return view('driver.profile', compact('driver'));
    }

    public function updateProfile(Request $request)
    {
        $driverId = session('driver_id');
        $staffLoginId = session('staff_login_id');
        
        $rules = [
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ];

        $messages = [
            'name.required' => '氏名を入力してください。',
            'name.max' => '氏名は100文字以内で入力してください。',
            'name_kana.max' => '氏名（カナ）は100文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
        ];
        
        $request->validate($rules, $messages);
        
        $driver = Driver::findOrFail($driverId);
        $driver->update([
            'name' => $request->name,
            'name_kana' => $request->name_kana,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
        ]);
        
        DB::connection()->table('staffs')
            ->where('login_id', $staffLoginId)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
            ]);
        
        session(['staff_name' => $request->name]);
        
        return redirect()->route('driver.dashboard')->with('success', '個人情報を更新しました。');
    }
}