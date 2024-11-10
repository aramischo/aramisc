<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscSupplier extends Model
{
   protected static function boot(){
       parent::boot();
       static::addGlobalScope(new ActiveStatusSchoolScope);
   }
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_suppliers';
}
