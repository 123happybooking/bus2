<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function index()
    {
        return view("login");
    }
}