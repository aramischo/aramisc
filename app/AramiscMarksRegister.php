<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AramiscMarksRegister extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_marks_registers';
    public function marksRegisterChilds(){
    	return $this->hasMany('App\AramiscMarksRegisterChild', 'marks_register_id', 'id');
    }

    public static function marksRegisterChild($student, $exam, $class, $section){
    	
        try {
            $marks_register_id = AramiscMarksRegister::where('student_id', $student)->where('exam_id', $exam)->where('class_id', $class)->where('section_id', $section)->first();
                if($marks_register_id != ""){
                    return AramiscMarksRegisterChild::where('marks_register_id', $marks_register_id->id)->get();
                }
                return array();
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public function studentInfo(){
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }

    public static function subjectDetails($exam, $class, $section, $subject){
    	
        try {
            $exam_schedule = AramiscExamSchedule::where('exam_id', $exam)->where('class_id', $class)->where('section_id', $section)->first();
            return AramiscExamScheduleSubject::where('exam_schedule_id', $exam_schedule->id)->where('subject_id', $subject)->first();
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function highestMark($exam_id, $subject_id, $section_id, $class_id){
        
        try {
            $highest_mark = DB::table('sm_result_stores')
                                    ->where('section_id', $section_id)
                                    ->where('class_id', $class_id)
                                    ->where('exam_type_id', $exam_id)
                                    ->where('subject_id', $subject_id)
                                    ->max('total_marks');

                return $highest_mark;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }                  
    }


    public static function is_absent_check($exam_id, $class_id, $section_id, $subject_id, $student_id, $record_id)
    {
            $exam = AramiscExam::where('exam_type_id', $exam_id)
                    ->where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('subject_id', $subject_id)
                    ->first();

            $exam_attendance = AramiscExamAttendance::where('exam_id', $exam->id)->where('class_id', $class_id)->where('section_id', $section_id)->where('subject_id', $subject_id)->first();
            if ($exam_attendance) {
                $exam_attendance_child = AramiscExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->where('student_id', $student_id)->where('student_record_id', $record_id)->first();
                return $exam_attendance_child;
            }
            return null;
    }

    public static function un_is_absent_check($exam_id, $request, $subject_id, $student_id, $record_id)
    {
        $AramiscExamAttendance = AramiscExamAttendance::query();
        $exam_attendance = universityFilter($AramiscExamAttendance, $request)
                            ->where('exam_id', $exam_id)
                            ->where('un_subject_id', $subject_id)
                            ->orWhereNull('un_section_id')
                            ->first();
                          
       
            if ($exam_attendance) {
                return AramiscExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)
                    ->where('student_id', $student_id)
                    ->where('student_record_id', $record_id)
                    ->first();
            }
            return null;
    }
}
