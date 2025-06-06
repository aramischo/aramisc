<?php

namespace App;

use App\YearCheck;
use App\Scopes\SchoolScope;
use Illuminate\Support\Facades\Auth;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscClassRoutineUpdate extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_class_routine_updates';
    public static function assingedClassRoutine($class_time, $day, $class_id, $section_id)
    {
        try {

            return AramiscClassRoutineUpdate::where('class_period_id', $class_time)
            ->where('day', $day)
            ->where('class_id', $class_id)->where('section_id', $section_id)
            ->first();
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function teacherAssingedClassRoutine($class_time, $day, $teacher_id)
    {
        try {
            return AramiscClassRoutineUpdate::where('class_period_id', $class_time)->where('day', $day)->where('class_period_id', $class_time)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->where('teacher_id', $teacher_id)->first();
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public function subject()
    {
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id')->withDefault();
    }

    public function subjectApi()
    {
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    public function saasSubject()
    {
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    
    public function class(){
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }

    public function classApi(){
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function classRoom()
    {
        return $this->belongsTo('App\AramiscClassRoom', 'room_id', 'id')->withDefault();
    }
    public function classRoomApi()
    {
        return $this->belongsTo('App\AramiscClassRoom', 'room_id', 'id')->withDefault()->withOutGlobalScope(ActiveStatusSchoolScope::class);
    }

    public function teacherDetail()
    {
        return $this->belongsTo('App\AramiscStaff', 'teacher_id', 'id')->withDefault();
    }

    public function teacherDetailApi()
    {
        return $this->belongsTo('App\AramiscStaff', 'teacher_id', 'id')->withOutGlobalScope(ActiveStatusSchoolScope::class)->withDefault();
    }
   

    public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }

    public function sectionApi()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }
    public function classTime()
    {
        return $this->belongsTo(AramiscClassTime::class, 'class_period_id');
    }

    public function saasClassTime()
    {
        return $this->belongsTo(AramiscClassTime::class, 'class_period_id');
    }

    public function weekend()
    {
        return $this->belongsTo(AramiscWeekend::class, 'day');
    }

    public function weekendApi()
    {
        return $this->belongsTo(AramiscWeekend::class, 'day')->withOutGlobalScope(SchoolScope::class);
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
    public function unSemesterLabel()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemesterLabel', 'un_semester_label_id', 'id')
        ->withDefault();
    }
    public function unSubject()
    {
        return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id')->withDefault();
    }
}
