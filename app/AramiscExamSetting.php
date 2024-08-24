<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscExamSetting extends Model
{
    use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = "sm_exam_settings";
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    
    public function examName(){
        return $this->belongsTo('App\AramiscExamType', 'exam_type', 'id');
    }

}
