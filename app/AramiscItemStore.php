<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscItemStore extends Model
{
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_item_stores';
}
