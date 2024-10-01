<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscTemporaryMeritlist extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_temporary_meritlists';
    public function class()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
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
