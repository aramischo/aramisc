<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class AramiscQuestionLevel extends Model
{
    use HasFactory;
     // Spécifiez le nom de la table explicitement
     protected $table = 'aramisc_question_levels';
     
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    
}
