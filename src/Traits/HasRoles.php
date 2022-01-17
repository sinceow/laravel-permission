<?php

namespace Jobsys\Permission\Traits;


use Illuminate\Support\Collection;
use Jobsys\Permission\Models\Role;

trait HasRoles
{
    use HasPermissions;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->map->id
            ->all();

        $this->roles()->sync($roles, false);
        $this->load('roles');

        return $this;
    }

    public function removeRole($role)
    {
        $this->roles()->detach($this->getStoredRole($role));

        $this->load('roles');

        return $this;
    }

    public function syncRoles(...$roles)
    {
        $this->roles()->detach();

        return $this->assignRole($roles);
    }

    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if (is_int($roles)) {
            return $this->roles->contains('id', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        return $roles->intersect($this->roles)->isNotEmpty();
    }

    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    public function hasAllRoles($roles): bool
    {

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->getRoleNames()) == $roles;
    }

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    public function getRoleKeys(): Collection
    {
        return $this->roles->pluck('key');
    }

    public function isSuper()
    {
        return $this->getRoleKeys()->intersect(config('permission.presets'))->isNotEmpty();
    }

    protected function getStoredRole($role): Role
    {

        if (is_numeric($role)) {
            return Role::where('id', $role)->first();
        }

        if (is_string($role)) {
            return Role::where('name', $role)->first();
        }

        return $role;
    }
}
