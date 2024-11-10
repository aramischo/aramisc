<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AramiscOnlineExamQuestion extends Model
{
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_online_exam_questions';
    public function multipleOptions()
    {
        return $this->hasMany('App\AramiscOnlineExamQuestionMuOption', 'online_exam_question_id', 'id');
    }
}
