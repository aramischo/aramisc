<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\RolePermission\Entities\AramiscPermissionAssign;

class Role extends Model
{
    //
    public function permissions()
    {
        return $this->hasMany(AramiscPermissionAssign::class, 'role_id', 'id');
    }
}
