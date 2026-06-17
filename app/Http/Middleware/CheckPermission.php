<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $role = session('role', '');
        
        $permissionMap = [
            'operations' => ['admin', 'operations_manager', 'coordinator', 'manager'],
            'sales' => ['admin', 'operations_manager', 'manager'],
            'results' => ['admin', 'operations_manager', 'manager'],
            'master' => ['admin', 'operations_manager'],
            'accounting' => ['admin', 'manager'],
        ];
        
        $allowedRoles = $permissionMap[$permission] ?? [];
        
        if (!in_array($role, $allowedRoles)) {
            abort(403, 'アクセス権限がありません。');
        }
        
        if ($permission === 'accounting' && session('enable_accounting') != 1) {
            abort(403, '会計機能は無効になっています。');
        }
        
        return $next($request);
    }
}