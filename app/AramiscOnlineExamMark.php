<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscOnlineExamMark extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_online_exam_marks';
    public function studentInfo()
    {
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }
}
