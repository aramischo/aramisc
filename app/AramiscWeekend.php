<?php

namespace App;

use App\AramiscStaff;
use App\AramiscStudent;
use App\Scopes\SchoolScope;
use App\AramiscClassRoutineUpdate;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscWeekend extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_weekends';
    public function classRoutine()
    {
        return $this->hasMany('App\AramiscClassRoutineUpdate', 'day', 'id');
    }

    public function studentClassRoutine()
    {
        $student = AramiscStudent::where('user_id', auth()->user()->id)->first();         
        return $this->hasMany('App\AramiscClassRoutineUpdate', 'day', 'id')
        ->where('class_id', $student->class_id)
        ->where('section_id', $student->section_id)
        ->where('academic_id', getAcademicId())
        ->where('school_id', auth()->user()->school_id)->oderBy('start_time');
    }
    public static function studentClassRoutineFromRecord($class_id, $section_id, $day_id)
    {
         
        $routine = AramiscClassRoutineUpdate::where('day', $day_id)
                                    ->where('class_id', $class_id)
                                    ->where('section_id', $section_id)
                                    ->where('academic_id', getAcademicId())
                                    ->where('school_id', auth()->user()->school_id)
                                    ->with('subject','classRoom','teacherDetail')
                                    ->get();
        return  $routine;
    }
    
    public static function studentClassRoutineFromRecordUniversity($un_academic_id, $un_semester_label_id, $day_id)
    {
         
        $routine = AramiscClassRoutineUpdate::where('day', $day_id)
                                    ->where('un_academic_id', $un_academic_id)
                                    ->where('un_semester_label_id', $un_semester_label_id)
                                    ->where('academic_id', getAcademicId())
                                    ->where('school_id', auth()->user()->school_id)->get();
        return  $routine;
    }
    public function teacherClassRoutine()
    {
        $teacher_id = AramiscStaff::where('user_id', auth()->user()->id)
        ->where(function($q) {
            $q->where('role_id', 4)->orWhere('previous_role_id', 4);
        })
        ->first()->id;
         
        return $this->hasMany('App\AramiscClassRoutineUpdate', 'day', 'id')
        ->where('teacher_id', $teacher_id)
        ->where('academic_id', getAcademicId())
        ->where('school_id', auth()->user()->school_id);
    }

    public function teacherClassRoutineAdmin()
    {
        return $this->hasMany('App\AramiscClassRoutineUpdate', 'day', 'id')
        ->where('teacher_id', request()->teacher)
        ->where('academic_id', getAcademicId())
        ->where('school_id', auth()->user()->school_id);
    }

    public static function teacherClassRoutineById($day, $teacher_id)
    {

        return AramiscClassRoutineUpdate::where('day', $day)->where('teacher_id', $teacher_id)
            ->where('academic_id', getAcademicId())
            ->where('school_id', auth()->user()->school_id)->get();
    }

    public static function unTeacherClassRoutineById($day, $teacher_id)
    {
        return AramiscClassRoutineUpdate::where('day', $day)->where('teacher_id', $teacher_id)
            ->where('un_academic_id', getAcademicId())
            ->where('school_id', auth()->user()->school_id)->get();
    }

    public static function parentClassRoutine($day, $student_id)
    {
        $student = AramiscStudent::find($student_id);

        return AramiscClassRoutineUpdate::where('day', $day)->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('academic_id', getAcademicId())
            ->where('school_id', auth()->user()->school_id)->get();
    }



    public static function universityStudentClassRoutine($un_semester_label_id, $section_id, $day_id)
    {
         
        $routine = AramiscClassRoutineUpdate::where('day', $day_id)
                                    ->where('un_semester_label_id', $un_semester_label_id)
                                    ->where('un_section_id', $section_id)
                                    ->where('school_id', auth()->user()->school_id)->get();
        return  $routine;
    }

    public static function saasClassRoutine($class_id, $section_id, $day_id)
    {
        $routine = AramiscClassRoutineUpdate::where('day', $day_id)
                                    ->where('class_id', $class_id)
                                    ->where('section_id', $section_id)
                                    ->withoutGlobalScope(StatusAcademicSchoolScope::class)
                                    ->get();
        return  $routine;
    }

}
