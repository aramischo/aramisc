<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscBaseGroup extends Model
{
	use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_base_groups';
    public function baseSetups(){
		return $this->hasmany('App\AramiscBaseSetup', 'base_group_id');
	} 
}
