<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Services\DatabaseConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Masters\User;
use App\Models\Masters\LoginHistory;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('masters.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login_id' => 'required|string',
            'user_company_id' => 'required|integer|min:1',
            'password' => 'required'
        ]);

        $companyId = $credentials['user_company_id'];

        try {
            $dbExists = DatabaseConnectionService::checkUserDatabaseExists($companyId);
            
            if (!$dbExists) {
                return back()
                    ->withInput($request->only('login_id', 'user_company_id'))
                    ->withErrors([
                        'login_id' => 'ログインIDまたはパスワードが正しくありません。'
                    ]);
            }
            
            DatabaseConnectionService::connectToDefaultDatabase();
            
            try {
                DatabaseConnectionService::connectToUserDatabase($companyId);
            } catch (\Exception $e) {
                throw $e;
            }
            
            try {
                $staff = DB::connection()->table('staffs')
                    ->where('login_id', $credentials['login_id'])
                    ->where('is_active', 1)
                    ->first();
            } catch (\Exception $e) {
                throw $e;
            }

            if (!$staff) {
                DatabaseConnectionService::connectToDefaultDatabase();
                return back()
                    ->withInput($request->only('login_id', 'user_company_id'))
                    ->withErrors([
                        'login_id' => 'ログインIDまたはパスワードが正しくありません。'
                    ]);
            }

            $passwordValid = Hash::check($credentials['password'], $staff->password);
            
            if (!$passwordValid) {
                DatabaseConnectionService::connectToDefaultDatabase();
                return back()
                    ->withInput($request->only('login_id', 'user_company_id'))
                    ->withErrors([
                        'login_id' => 'ログインIDまたはパスワードが正しくありません。'
                    ]);
            }

            DatabaseConnectionService::connectToDefaultDatabase();
            
            try {
                $user = User::on('mysql')->updateOrCreate(
                    ['id' => $companyId],
                    [
                        'login_id' => $credentials['login_id'],
                        'name' => $staff->name,
                        'password' => Hash::make($credentials['password']),
                        'last_login_at' => now(),
                    ]
                );
            } catch (\Exception $e) {
                $user = new User();
                $user->id = $companyId;
            }

            $request->session()->put('staff_id', $staff->id);
            $request->session()->put('staff_name', $staff->name);
            $request->session()->put('staff_login_id', $staff->login_id);
            $request->session()->put('company_id', $companyId);
            $request->session()->put('user_database', 'bus_user_' . $companyId);
            $request->session()->put('user_id', $companyId);
            $request->session()->put('user_name', $staff->name);
            $request->session()->put('role', $staff->role);
            
            DatabaseConnectionService::connectToDefaultDatabase();
            
            Auth::guard('masters')->login($user, $request->boolean('remember'));
            
            $request->session()->regenerate();
            
            try {
                LoginHistory::create([
                    'staff_id' => $staff->id,
                    'login_id' => $staff->login_id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'success',
                    'logged_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to record login history: ' . $e->getMessage());
            }
            
            DatabaseConnectionService::connectToDefaultDatabase();
            
            return redirect()->route('masters.home');

        } catch (\Exception $e) {
            try {
                DatabaseConnectionService::connectToDefaultDatabase();
                LoginHistory::create([
                    'staff_id' => null,
                    'login_id' => $credentials['login_id'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'failed',
                    'logged_at' => now(),
                ]);
            } catch (\Exception $logEx) {
            }
            
            try {
                DatabaseConnectionService::connectToDefaultDatabase();
            } catch (\Exception $dbEx) {
            }
            
            return back()
                ->withInput($request->only('login_id', 'user_company_id'))
                ->withErrors([
                    'login_id' => 'ログイン処理中にエラーが発生しました。'
                ]);
        }
    }
    
    public function logout(Request $request)
    {
        Auth::guard('masters')->logout();
        
        $request->session()->forget([
            'user_id', 
            'user_name', 
            'company_id', 
            'user_database',
            'staff_id',
            'staff_name',
            'staff_login_id',
            'role'
        ]);
        
        return redirect()->route('masters.login');
    }
}