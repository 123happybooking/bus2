<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $role = session('role', '');
        $isAdmin = in_array($role, ['admin', 'administrator', 'manager']);
        $staffId = session('staff_id');

        return view('masters.home.index', compact('isAdmin', 'staffId'));
    }
}