<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class AramiscUserLog extends Model
{
    
    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function role(){
    	return $this->belongsTo('Modules\RolePermission\Entities\InfixRole', 'role_id', 'id');
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_user_logs';
}