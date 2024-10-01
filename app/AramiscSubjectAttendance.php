<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscSubjectAttendance extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_subject_attendances';
    protected $guarded = ['id'];

    protected $casts = [
        'subject_id' => 'integer',
        'attendance_type' => 'string',
        'attendance_date' => 'string',
    ];

    public function student()
    {
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }

    public function recordDetail(){
        return $this->belongsTo('App\Models\StudentRecord', 'student_record_id', 'id');
    }

    public function subject(){
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
    }

    public static function getAbsentSubjectList($recored_id, $schoolId){
        $subjectLists = [];
        $subjects = AramiscSubjectAttendance::where('attendance_type','A')
                    ->where('student_record_id', $recored_id)
                    ->where('school_id', $schoolId)
                    ->where('attendance_date', date('Y-m-d'))
                    ->where('notify', 0)
                    ->get();
        foreach($subjects as $subject){
            $subjectLists [] = $subject->subject->subject_name;
        }
        return $subjectLists;
    }


}
