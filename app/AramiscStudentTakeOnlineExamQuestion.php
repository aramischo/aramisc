<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscStudentTakeOnlineExamQuestion extends Model
{
    use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = 'sm_student_takeOnline_exam_questions';
    public function questionBank(){
    	return $this->belongsTo('App\SmQuestionBank', 'question_bank_id', 'id');
    }

    public function takeQuestionMu(){
    	return $this->hasMany('App\AramiscStudentTakeOnlnExQuesOption', 'take_online_exam_question_id', 'id');
    }
}
