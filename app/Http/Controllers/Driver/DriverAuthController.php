<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverAuthController extends Controller
{
    public function logout(Request $request)
    {
        Auth::guard('masters')->logout();
        
        $request->session()->forget([
            'driver_id', 
            'driver_name', 
            'user_id', 
            'user_name', 
            'company_id', 
            'user_database',
            'staff_id',
            'staff_name',
            'staff_login_id',
            'role'
        ]);
        
        return redirect()->route('driver.login');
    }
    
    public function settings()
    {
        return view('driver.settings');
    }
}