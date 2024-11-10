<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscHeaderMenuManager extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_header_menu_managers';
    protected $guarded = ['id'];

    public function childs(){
        return $this->hasMany(AramiscHeaderMenuManager::class,'parent_id','id')->with('childs')->orderBy('position');
    }

}
