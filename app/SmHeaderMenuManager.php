<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmHeaderMenuManager extends Model
{
    use HasFactory;
    // Specify the table name explicitly
    protected $table = 'sm_header_menu_managers';
    protected $guarded = ['id'];

    public function childs(){
        return $this->hasMany(SmHeaderMenuManager::class,'parent_id','id')->with('childs')->orderBy('position');
    }

}
