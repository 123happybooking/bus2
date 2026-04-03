<?php

namespace App\Http\Middleware;

use App\Services\DatabaseConnectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetUserDatabase
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            DatabaseConnectionService::connectToUserDatabase($user->id);
        }
        
        return $next($request);
    }
}