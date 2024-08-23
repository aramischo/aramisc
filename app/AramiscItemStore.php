<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscItemStore extends Model
{
	// Spécifiez le nom de la table explicitement
    protected $table = "sm_item_stores";
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
    use HasFactory;
}
