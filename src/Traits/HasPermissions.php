<?php

namespace Jobsys\Permission\Traits;


use Illuminate\Support\Collection;
use Jobsys\Permission\Models\Permission;
use Jobsys\Permission\Models\UserPermission;
use Jobsys\Permission\WildcardPermission;

trait HasPermissions
{
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    public function hasPermissionTo($permission): bool
    {
        if (config('permission.enable_wildcard_permission', false)) {
            try {
                return $this->hasWildcardPermission($permission);
            } catch (\Exception $e) {
                return false;
            }
        }

        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->first();
        }

        if (is_int($permission)) {
            $permission = Permission::where('id', $permission)->first();
        }

        if (!$permission instanceof Permission) {
            throw new \Exception("Permission not exist.");
        }

        return $this->hasDirectPermission($permission);
    }

    public function checkPermissionTo($permission): bool
    {
        try {
            return $this->hasPermissionTo($permission);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function hasAnyPermission(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if ($this->checkPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    public function hasDirectPermission($permission): bool
    {

        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->first();
        }

        if (is_int($permission)) {
            $permission = Permission::where('id', $permission)->first();
        }

        if (!$permission instanceof Permission) {
            throw new \Exception("Permission not exist.");
        }


        return $this->permissions->contains('id', $permission->id);
    }

    protected function hasWildcardPermission($permission): bool
    {

        if (is_int($permission)) {
            $permission = Permission::where('id', $permission)->first();
        }

        if ($permission instanceof Permission) {
            $permission = $permission->key;
        }

        if (!is_string($permission)) {
            throw new \Exception('Permission is not a wildcard string.');
        }

        foreach ($this->permissions as $userPermission) {

            $userPermission = new WildcardPermission($userPermission->key);

            if ($userPermission->implies($permission)) {
                return true;
            }
        }

        return false;
    }

    public function givePermissionTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }

                return $this->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->map->id
            ->all();


        $this->permissions()->sync($permissions, false);
        $this->load('permissions');

        return $this;
    }

    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->load('permissions');

        return $this;
    }

    public function getPermissionNames(): Collection
    {
        return $this->permissions->pluck('name');
    }

    public function getPermissionKey(): Collection
    {
        return $this->permissions->pluck('key');
    }

    protected function getStoredPermission($permissions)
    {

        if (is_string($permissions)) {
            return Permission::where('key', $permissions)->first();
        }

        if (is_numeric($permissions)) {
            return Permission::where('id', $permissions)->first();
        }


        if (is_array($permissions)) {
            return Permission::whereIn('key', $permissions)->get();
        }

        return $permissions;
    }

    public function hasPermissionViaRole($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->first();
        }

        if (is_int($permission)) {
            $permission = Permission::where('id', $permission)->first();
        }
        return $this->hasRole($permission->roles);
    }

    public function getPermissionsViaRoles(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;

        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }

}
