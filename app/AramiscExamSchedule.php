<?php

namespace App;

use App\AramiscExamType;
use App\Scopes\AcademicSchoolScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscExamSchedule extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_exam_schedules';
    protected $fillable = [];
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
    public function examSchedule()
    {
        return $this->hasMany('App\AramiscExamScheduleSubject', 'exam_schedule_id', 'id');
    }
    

    public function examType()
    {
        return $this->belongsTo('App\AramiscExamType', 'exam_term_id', 'id');
    }

    public function exam()
    {
        return $this->belongsTo('App\AramiscExam', 'exam_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }
    public function section()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo('App\AramiscSection', 'un_section_id', 'id');
        } else {
            return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
        }  
    }

    public function unSemesterLabel()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemesterLabel', 'un_semester_label_id', 'id')->withDefault();
    }
    public function classRoom()
    {
        return $this->belongsTo('App\AramiscClassRoom', 'room_id', 'id');
    }

    public function subject()
    {
        if(moduleStatusCheck('University')){
            return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id');
        }else{
            return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
        }
    }
    public function teacher()
    {
        return $this->belongsTo('App\AramiscStaff', 'teacher_id', 'id');
    }

    public static function assignedRoutine($class_id, $section_id, $exam_id, $subject_id, $exam_period_id)
    {
        try {
            return AramiscExamSchedule::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('exam_term_id', $exam_id)
                ->where('subject_id', $subject_id)
                ->where('exam_period_id', $exam_period_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function assignedRoutineSubject($class_id, $section_id, $exam_id, $subject_id)
    {
        try {
            return AramiscExamSchedule::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('exam_term_id', $exam_id)
                ->where('subject_id', $subject_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function assigned_date_wise_exams($exam_period_id, $exam_term_id, $date)
    {
        try {
            return AramiscExamSchedule::where('exam_period_id', $exam_period_id)->where('date', $date)->where('exam_term_id', $exam_term_id)->get();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function assignedRoutineSubjectStudent($class_id, $section_id, $exam_id, $subject_id, $exam_period_id)
    {

        try {
            return AramiscExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)->where('exam_term_id', $exam_id)->where('subject_id', $subject_id)->where('exam_period_id', $exam_period_id)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function examScheduleSubject($class_id, $section_id, $exam_id, $exam_period_id, $date)
    {
        try {
            return AramiscExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)
                ->where('exam_term_id', $exam_id)->where('exam_period_id', $exam_period_id)
                ->where('date', $date)
                ->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public function subjectDetails(){
        return $this->belongsTo('Modules\University\Entities\UnSubject','un_subject_id','id')->withDefault();
    }

    public static function getAllExams($class_id, $section_id, $exam_type_id)
    {
        try {
            return AramiscExamSchedule::where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('exam_term_id', $exam_type_id)
                    ->orderBy('date', 'ASC')
                    ->get();
        } catch (\Exception $e) {
            return '';
        }
    }
}
