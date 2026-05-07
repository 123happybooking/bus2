<?php

namespace App\Services;

class PermissionService
{
    protected array $permissions = [];
    protected string $role;
    protected array $roleNames;
    
    public function __construct()
    {
        $this->role = session('role', '');
        $this->roleNames = [
            'admin' => '管理者',
            'operations_manager' => '運行管理者',
            'coordinator' => '運行手配',
            'manager' => '経理',
            'driver' => '運転手',
        ];
        $this->loadPermissions();
    }
    
    protected function loadPermissions(): void
    {
        $allResources = [
            'basicinfo',
            'branches',
            'staffs',
            'vehicles',
            'drivers',
            'guides',
            'agencies',
            'customers',
            'partners',
            'itineraries',
            'facilities',
            'locations',
            'purposes',
            'reservation-categories',
            'attendance-categories',
            'remarks',
            'fees',
            'banks',
            'vehicle-types',
            'vehicle-models',
            'user-company-info',
            'login-histories',
        ];
        
        $allActions = ['view', 'show', 'create', 'edit', 'delete'];
        
        $this->permissions['admin'] = [];
        foreach ($allResources as $resource) {
            $this->permissions['admin'][$resource] = $allActions;
        }
        
        $this->permissions['operations_manager'] = [];
        foreach ($allResources as $resource) {
            $this->permissions['operations_manager'][$resource] = ['view', 'show', 'create', 'edit'];
        }
        
        $this->permissions['coordinator'] = [
            'itineraries' => ['view', 'show', 'create', 'edit'],
            'vehicles' => ['view', 'show'],
            'drivers' => ['view', 'show'],
            'customers' => ['view', 'show'],
            'facilities' => ['view', 'show'],
            'locations' => ['view', 'show'],
        ];
        
        $this->permissions['manager'] = [
            'itineraries' => ['view', 'show'],
            'vehicles' => ['view', 'show'],
            'drivers' => ['view', 'show'],
            'customers' => ['view', 'show'],
            'fees' => ['view', 'show'],
            'login-histories' => ['view', 'show'],
        ];
        
        $this->permissions['driver'] = [
            'drivers' => ['view', 'show'],
            'vehicles' => ['view', 'show'],
        ];
        
        $this->permissions['default'] = [];
    }
    
    public function can(string $resource, string $action): bool
    {
        if (empty($this->role)) {
            return false;
        }
        
        $rolePermissions = $this->permissions[$this->role] ?? $this->permissions['default'];
        
        if (!isset($rolePermissions[$resource])) {
            return false;
        }
        
        return in_array($action, $rolePermissions[$resource]);
    }
    
    public function canView(string $resource): bool
    {
        return $this->can($resource, 'view');
    }
    
    public function canShow(string $resource): bool
    {
        return $this->can($resource, 'show');
    }
    
    public function canCreate(string $resource): bool
    {
        return $this->can($resource, 'create');
    }
    
    public function canEdit(string $resource): bool
    {
        return $this->can($resource, 'edit');
    }
    
    public function canDelete(string $resource): bool
    {
        return $this->can($resource, 'delete');
    }
    
    public function canAny(string $resource, array $actions): bool
    {
        foreach ($actions as $action) {
            if ($this->can($resource, $action)) {
                return true;
            }
        }
        return false;
    }
    
    public function canAll(string $resource, array $actions): bool
    {
        foreach ($actions as $action) {
            if (!$this->can($resource, $action)) {
                return false;
            }
        }
        return true;
    }
    
    public function getAvailableActions(string $resource): array
    {
        if (empty($this->role)) {
            return [];
        }
        
        $rolePermissions = $this->permissions[$this->role] ?? $this->permissions['default'];
        return $rolePermissions[$resource] ?? [];
    }
    
    public function getAccessibleResources(): array
    {
        if (empty($this->role)) {
            return [];
        }
        
        $rolePermissions = $this->permissions[$this->role] ?? $this->permissions['default'];
        return array_keys($rolePermissions);
    }
    
    public function applyQueryFilter($query, string $resource)
    {
        if (in_array($this->role, ['admin', 'operations_manager'])) {
            return $query;
        }
        
        if ($this->role === 'driver' && in_array($resource, ['vehicles', 'drivers'])) {
            return $query->where('is_active', true);
        }
        
        return $query;
    }
    
    public function isResourceAccessible(string $resource): bool
    {
        return $this->canAny($resource, ['view', 'show', 'create', 'edit', 'delete']);
    }
    
    public function getRoleName(): string
    {
        return $this->roleNames[$this->role] ?? '不明な権限';
    }
    
    public function getRole(): string
    {
        return $this->role;
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    public function isOperationsManager(): bool
    {
        return $this->role === 'operations_manager';
    }
    
    public function isCoordinator(): bool
    {
        return $this->role === 'coordinator';
    }
    
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }
    
    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }
    
    public function getAccessLevelDescription(): string
    {
        $descriptions = [
            'admin' => 'フルアクセス（全ての操作が可能）',
            'operations_manager' => '高アクセスレベル（削除以外の操作が可能）',
            'coordinator' => '中アクセスレベル（旅程管理を中心にアクセス可能）',
            'manager' => '閲覧中心のアクセスレベル',
            'driver' => '制限付きアクセス（車両と運転手のみ閲覧可能）',
        ];
        
        return $descriptions[$this->role] ?? 'アクセス権限がありません';
    }
}