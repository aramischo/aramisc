<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class AramiscQuestionBank extends Model
{
    use HasFactory;
     // Spécifiez le nom de la table explicitement
     protected $table = 'aramisc_question_banks';
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }


    public function questionGroup()
    {
        return $this->belongsTo('App\AramiscQuestionGroup', 'q_group_id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }
    public function questionLevel()
    {
        return $this->belongsTo('App\AramiscQuestionLevel', 'question_level_id');
    }
    public function class()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }
    public function section()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo('App\AramiscSection', 'un_section_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
        } else {
            return $this->belongsTo('App\AramiscSection', 'section_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
        }
    }
    public function unSemesterLabel()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemesterLabel', 'un_semester_label_id', 'id')->withDefault();
    }

    public function questionMu()
    {
        return $this->hasMany('App\AramiscQuestionBankMuOption', 'question_bank_id', 'id');
    }
}
