<?php

namespace App;

use App\Models\StudentRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscExamAttendanceChild extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_exam_attendance_childs';
    protected $guarded = ['id'];
    public function studentInfo()
    {
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id')->with('class', 'section');
    }
    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id', 'id');
    }
}
