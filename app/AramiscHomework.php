<?php

namespace App;

use App\AramiscClass;
use App\Models\StudentRecord;
use App\Scopes\GlobalAcademicScope;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\University\Entities\UnSubject;
use Modules\University\Entities\UnSemesterLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\University\Entities\UnSemesterLabelAssignSection;

class AramiscHomework extends Model
{
    use HasFactory;
    protected $table = "sm_homeworks";
    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'created_by', 'evaluated_by',
    ];
    protected $appends=['HomeworkPercentage'];

    protected $casts = [
        'id'                => 'integer',
        'homework_date'     => 'string',
        'submission_date'   => 'string',
        'evaluation_date'   => 'string',
        'file'              => 'string',
        'marks'             => 'double',
        'description'       => 'string',
        'active_status'     => 'integer',
        'created_at'        => 'string',
        'updated_at'        => 'string',
        'evaluated_by'      => 'integer',
        'class_id'          => 'integer',
        'record_id'         => 'integer',
        'section_id'        => 'integer',
        'subject_id'        => 'integer',
        'created_by'        => 'integer',
        'updated_by'        => 'integer',
        'school_id'         => 'integer',
        'academic_id'       => 'integer',
        'course_id'         => 'integer',
        'lesson_id'         => 'integer',
        'chapter_id'        => 'integer',
    ];

    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    public function classes(){
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }


    public function class()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(UnSemesterLabel::class, 'un_semester_label_id', 'id');
        } else {
            return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
        }
    }

    public function saasclass()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class, GlobalAcademicScope::class);
    }

    public function sections()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }
    public function unSection(){
        return $this->belongsTo(UnSemesterLabelAssignSection::class, 'un_section_id', 'id');
    }

    public function saassection()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class,GlobalAcademicScope::class);
    }

    public function homeworkCompleted()
    {
        return $this->hasMany('App\AramiscHomeworkStudent', 'homework_id', 'id')->where('complete_status', 'C');
    }

    public function lmsHomeworkCompleted()
    {
        return $this->hasOne('App\AramiscHomeworkStudent', 'homework_id','id');
    }


    public function subjects()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(UnSubject::class, 'un_subject_id', 'id');
        } else {
            return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
        }
    }

    public function saassubject()
    {
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class,GlobalAcademicScope::class);
    }

    public function users()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    public function saasusers()
    {
        return $this->belongsTo('App\User', 'created_by', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function evaluatedBy()
    {
        return $this->belongsTo('App\User', 'evaluated_by', 'id');
    }

    public static function getHomeworkPercentage($class_id, $section_id, $homework_id)
    {
        try {
            $totalStudents = StudentRecord::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->count();
            $totalHomeworkCompleted = AramiscHomeworkStudent::select('id')
                ->where('homework_id', $homework_id)
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->where('complete_status', 'C')
                ->count();



            if (isset($totalStudents)) {
                $homeworks = array(
                    'totalStudents' => $totalStudents,
                    'totalHomeworkCompleted' => $totalHomeworkCompleted,

                );
                return $homeworks;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getHomeworkPercentageAttribute()
    {
        try {
            $totalStudents = AramiscStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->select('id')
                ->where('class_id', $this->class_id)
                ->where('section_id', $this->section_id)
                ->where('school_id', auth()->user()->school_id)
              
                ->count();

            $totalHomeworkCompleted = AramiscHomeworkStudent::select('id')
                ->where('homework_id', $this->homework_id)
                ->where('academic_id', getAcademicId())
                ->where('complete_status', 'C')
                ->count();

            if (isset($totalStudents)) {
                $homeworks = array(
                    'totalStudents' => $totalStudents,
                    'totalHomeworkCompleted' => $totalHomeworkCompleted,

                );
                return $homeworks;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function evaluations()
    {
        return $this->hasMany('App\AramiscHomeworkStudent', 'homework_id', 'id');
    }

    public function contents()
    {
        return $this->hasMany('App\AramiscUploadHomeworkContent', 'homework_id', 'id');
    }

    public static function evaluationHomework($s_id, $h_id)
    {

        try {
            $abc = AramiscHomeworkStudent::where('homework_id', $h_id)->where('student_id', $s_id)->first();
            return $abc;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function uploadedContent($s_id, $h_id)
    {
        try {
            $abc = AramiscUploadHomeworkContent::where('homework_id', $h_id)->where('student_id', $s_id)->get();
            return $abc;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public function unSession()
    {
        return $this->belongsTo('Modules\University\Entities\UnSession', 'un_session_id', 'id')->withDefault();
    }
    public function unFaculty()
    {
        return $this->belongsTo('Modules\University\Entities\UnFaculty', 'un_faculty_id', 'id')->withDefault();
    }
    public function unDepartment()
    {
        return $this->belongsTo('Modules\University\Entities\UnDepartment', 'un_department_id', 'id')->withDefault();
    }
    public function unAcademic()
    {
        return $this->belongsTo('Modules\University\Entities\UnAcademicYear', 'un_academic_id', 'id')->withDefault();
    }
    public function unSemester()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemester', 'un_semester_id', 'id')->withDefault();
    }


    public function semester()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemester', 'un_semester_id', 'id')->withDefault();
    }

    public function semesterLabel()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemesterLabel', 'un_semester_label_id', 'id')->withDefault();
    }

    public function unSubject()
    {
        return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id')->withDefault();
    }

    // public function records()
    // {
    //     return $this->hasManyThrough(StudentRecord::class, AramiscClass::class, 'id', 'class_id', 'id');
    // }
}
