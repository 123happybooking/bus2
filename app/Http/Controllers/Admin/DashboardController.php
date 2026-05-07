<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Masters\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // dump(session()->all());
        
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();

        return view('admin.dashboard', compact(
            'totalUsers', 
            'newUsersToday'
        ));
    }
}