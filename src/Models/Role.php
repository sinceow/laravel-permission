<?php

namespace Jobsys\Permission\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected $guarded = [];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function active_permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')->where('is_active', true);
    }

}
