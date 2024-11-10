<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscOnlineExamQuestionAssign extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_online_exam_question_assigns';
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
    
    public function questionBank()
    {
        return $this->belongsTo('App\AramiscQuestionBank', 'question_bank_id', 'id');
    }
}
