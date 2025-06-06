<?php

namespace Modules\Lesson\Entities;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscLesson extends Model
{
    use HasFactory;
    protected $fillable = [];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }

    public function class()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }
    public function subject()
    {
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
    }

    public function lessons()
    {
        return $this->hasMany('Modules\Lesson\Entities\AramiscLessonDetails', 'lesson_id', 'id');
    }
    public static function lessonName($class, $section, $subject)
    {
        return AramiscLesson::where('class_id', $class)->where('section_id', $section)
            ->where('subject_id', $subject)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();
    }
    public function scopeStatusCheck($query)
    {
        return $query->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->where('active_status', 1);
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
        return $this->belongsTo('Modules\University\Entities\UnSemesterLabel', 'un_semester_label_id', 'id')->withDefault();
    }
    public function unSubject()
    {
        return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id')->withDefault();
    }
}
