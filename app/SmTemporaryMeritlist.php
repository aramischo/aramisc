<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmTemporaryMeritlist extends Model
{
    use HasFactory;
    public function class()
    {
        return $this->belongsTo('App\SmClass', 'class_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }
    public function studentinfo()
    {
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }

    public function exam()
    {
        return $this->belongsTo('App\AramiscExam', 'exam_id', 'id');
    }
}
