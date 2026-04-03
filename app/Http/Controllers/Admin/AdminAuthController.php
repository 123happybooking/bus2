<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        session()->regenerateToken();
        
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            
            $admin = Auth::guard('admin')->user();
            
            if (isset($admin->is_active) && !$admin->is_active) {
                Auth::guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'このアカウントは無効になっています。',
                ])->onlyInput('email');
            }
            
            $request->session()->regenerate();
            
            $request->session()->put([
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_email' => $admin->email,
                'admin_role' => $admin->role ?? 'admin',
                'admin_logged_in_at' => now()->toDateTimeString(),
            ]);
            
            $admin->update(['last_login_at' => now()]);
            
            return redirect()->route('admin.dashboard');
        }
    
        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        
        $request->session()->forget([
            'admin_id',
            'admin_name',
            'admin_email',
            'admin_role',
            'admin_logged_in_at',
        ]);
        
        return redirect()->route('admin.login');
    }
}