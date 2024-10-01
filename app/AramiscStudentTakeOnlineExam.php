<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\AramiscStudentTakeOnlineExamQuestion;
class AramiscStudentTakeOnlineExam extends Model
{
    use HasFactory;
     // Spécifiez le nom de la table explicitement
     protected $table = 'sm_student_take_online_exams';
    public static function submittedAnswer($exam_id, $s_id)
    {
        try {
            return AramiscStudentTakeOnlineExam::where('online_exam_id', $exam_id)->where('student_id', $s_id)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public function answeredQuestions()
    {
        return $this->hasMany('App\AramiscStudentTakeOnlineExamQuestion', 'take_online_exam_id', 'id');
    }

    public function onlineExam()
    {
        return $this->belongsTo('App\AramiscOnlineExam', 'online_exam_id', 'id');
    }
}
