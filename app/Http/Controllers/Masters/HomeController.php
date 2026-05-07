<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view("masters.home.index");
    }
}