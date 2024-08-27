<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AramiscModule extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_modules';
    public function moduleLink(){
    	return $this->hasMany('App\AramiscModuleLink', 'module_id', 'id');
    }
}
