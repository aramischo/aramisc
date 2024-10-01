<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscHoliday extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_holidays';
    protected static function boot (){
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
    
}
