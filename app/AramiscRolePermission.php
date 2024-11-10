<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscRolePermission extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_role_permissions';
    public function moduleLink()
    {
        return $this->belongsTo('App\AramiscModuleLink', 'module_link_id', 'id');
    }
}
