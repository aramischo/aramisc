<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscPostalDispatch extends Model
{
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new AcademicSchoolScope);
    }
    //
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_postal_dispatches';
}
