<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\LoginHistory;
use App\Models\Masters\Staff;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginHistory::with('staff');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('login_id', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('staff', function($staffQuery) use ($search) {
                      $staffQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('staff_id') && $request->staff_id != '') {
            $query->where('staff_id', $request->staff_id);
        }
        
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('logged_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('logged_at', '<=', $request->end_date);
        }
        
        $loginHistories = $query->orderBy('logged_at', 'desc')->paginate(20);
        $staffList = Staff::orderBy('name')->pluck('name', 'id');
        
        if ($request->hasAny(['search', 'staff_id', 'status', 'start_date', 'end_date'])) {
            $loginHistories->appends($request->all());
        }
        
        return view('masters.login-histories.index', compact('loginHistories', 'staffList'));
    }
}