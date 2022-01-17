<?php

namespace Jobsys\Permission\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{

    protected $guarded = [];
    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

}
