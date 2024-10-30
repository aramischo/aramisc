<?php

namespace App;

use App\AramiscStudent;
use App\AramiscExamSetup;
use App\AramiscMarkStore;
use App\AramiscMarksGrade;
use App\AramiscOptionalSubjectAssign;
use Illuminate\Support\Facades\DB;
use App\Scopes\GlobalAcademicScope;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscAssignSubject extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    use HasFactory;
     // Spécifiez le nom de la table explicitement
    protected $table = 'sm_assign_class_subjects';
    protected $casts = [
        'class_id' => 'integer',
        'section_id' => 'integer',
        'active_status' => 'integer',
        'subject_id' => 'integer',
    ];
    
    public function subject()
    {
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    public function results()
    {
        return $this->hasMany(AramiscResultStore::class, 'subject_id', 'subject_id');
    }
    public function resultBySubject()
    {
        return $this->hasMany(AramiscResultStore::class, 'subject_id', 'subject_id')->where('section_id', $this->section_id)
            ->where('class_id', $this->class_id);
    }

    public function class()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo('App\AramiscStaff', 'teacher_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }

    public function examSetups()
    {
        return $this->hasMany(AramiscExamSetup::class, 'class_id', 'class_id')->where('class_id', $this->class_id)
            ->where('section_id', $this->section_id);
    }

    public function markBySubject()
    {
        return $this->hasMany(AramiscMarkStore::class, 'subject_id', 'subject_id')->where('section_id', $this->section_id)
            ->where('class_id', $this->class_id);
    }
    public function exam()
    {
        return $this->hasOne(AramiscExam::class, 'subject_id', 'subject_id');
    }

    public function examSchedule()
    {
        return $this->hasMany(AramiscExamSchedule::class, 'subject_id', 'subject_id')
            ->where('class_id', $this->class_id)->where('section_id', $this->section_id);
    }

    public static function getNumberOfPart($subject_id, $class_id = null, $section_id, $exam_term_id)
    {
        try {
            $results = AramiscExamSetup::where([
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
            return $results;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function un_getNumberOfPart($subject_id, $exam_type, $request)
    {
        try {
            $AramiscExamSetup = AramiscExamSetup::query();
            $results = universityFilter($AramiscExamSetup, $request)
                ->where([
                    ['un_subject_id', $subject_id],
                    ['exam_term_id', $exam_type],
                ])
                ->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getNumberOfPartStudent($subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            $results = AramiscExamSetup::where([
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id]
            ])->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getMarksOfPart($student_id, $subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            $results = AramiscMarkStore::where([
                ['student_id', $student_id],
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function un_getMarksOfPart($student_id, $subject_id, $request, $exam_term_id)
    {
        try {
            $AramiscMarkStore = AramiscMarkStore::query();
            $results = universityFilter($AramiscMarkStore, $request)
                ->where([
                    ['student_id', $student_id],
                    ['un_subject_id', $subject_id],
                    ['exam_term_id', $exam_term_id],
                ])->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getSumMark($student_id, $subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            $results = AramiscMarkStore::where([
                ['student_id', $student_id],
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->sum('total_marks');
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function un_getSumMark($student_id, $subject_id, $request, $exam_term_id)
    {
        try {
            $AramiscMarkStore = AramiscMarkStore::query();
            $results = universityFilter($AramiscMarkStore, $request)
                ->where([
                    ['student_id', $student_id],
                    ['un_subject_id', $subject_id],
                    ['exam_term_id', $exam_term_id],
                ])->sum('total_marks');
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getHighestMark($subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            $results = DB::table('sm_mark_stores')
                ->select('student_id', DB::raw('SUM(total_marks) as total_amount'))
                ->where([
                    ['class_id', $class_id],
                    ['subject_id', $subject_id],
                    ['section_id', $section_id],
                    ['exam_term_id', $exam_term_id]
                ])
                ->distinct('student_id')
                ->get();
            $totalMark = [];
            foreach ($results as $result) {
                $totalMark[] = $result->total_amount;
            }
            return max($totalMark);
            $results = AramiscMarkStore::distinct('student_id')
                ->selectRaw('sum(total_marks) as sum, student_id')
                ->where([
                    ['class_id', $class_id],
                    ['subject_id', $subject_id],
                    ['section_id', $section_id],
                    ['exam_term_id', $exam_term_id],
                ])
                ->select('sum', 'student_id');
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getSubjectMark($subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            $results = AramiscExamSetup::where([
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->sum('exam_mark');
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }


    public static function get_student_result($student_id, $subject_id, $class_id, $section_id, $exam_term_id, $optional_subject_id, $optional_subject_setup)
    {
        try {
            $this_student_failed = 0;
            $total_gpa_point = 0;
            $student_info = AramiscStudent::where('id', '=', $student_id)->first();
            $optional_subject = AramiscOptionalSubjectAssign::where('student_id', '=', $student_info->id)->where('session_id', '=', $student_info->session_id)->first();
            $subjects = AramiscAssignSubject::where([['class_id', $class_id], ['section_id', $section_id]])->get();
            $assign_subjects = AramiscAssignSubject::where([['class_id', $class_id], ['section_id', $section_id]])->get();
            foreach ($subjects as $row) {
                $subject_id = $row->subject_id;
                $total_mark = AramiscAssignSubject::getSumMark($student_id, $subject_id, $class_id, $section_id, $exam_term_id);
                $mark_grade = AramiscMarksGrade::where([['percent_from', '<=', $total_mark], ['percent_upto', '>=', $total_mark]])->first();
                $optional_subject_id = '';
                if (!empty($optional_subject)) {
                    $optional_subject_id = $optional_subject->subject_id;
                }
                if ($subject_id == $optional_subject_id) {

                    // return $optional_subject_id;
                    if ($mark_grade->gpa < $optional_subject_setup->gpa_above) {
                        $total_gpa_point = $total_gpa_point + 0;
                        if ($mark_grade->gpa < 1) {
                            $this_student_failed = 1;
                        }
                    } else {
                        $optional_mark_grade = $mark_grade->gpa - $optional_subject_setup->gpa_above;
                        $total_gpa_point = $total_gpa_point + $optional_mark_grade;
                        if ($mark_grade->gpa < 1) {
                            $this_student_failed = 1;
                        }
                    }
                } else {
                    $total_gpa_point = $total_gpa_point + $mark_grade->gpa;
                    if ($mark_grade->gpa < 1) {
                        $this_student_failed = 1;
                    }
                }
            }
            if ($this_student_failed != 1) {
                if ($optional_subject_id != '') {
                    $number_of_subject = count($assign_subjects);
                    $number_of_subject = $number_of_subject - 1;
                    if ($total_gpa_point != 0 && $number_of_subject != "") {
                        $final_result = number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                        return $final_result;
                    } else {
                        return '0.00';
                    }
                } else {
                    $number_of_subject = count($assign_subjects);

                    if ($total_gpa_point != 0 && $number_of_subject != "") {
                        $final_result = number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                        return $final_result;
                    } else {
                        return '0.00';
                    }
                }
            } else {
                return '0.00';
            }
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function get_student_result_without_optional($student_id, $subject_id, $class_id, $section_id, $exam_term_id, $optional_subject_id, $optional_subject_setup)
    {
        try {
            $this_student_failed = 0;
            $total_gpa_point = 0;
            $student_info = AramiscStudent::where('id', '=', $student_id)->first();
            $optional_subject = AramiscOptionalSubjectAssign::where('student_id', '=', $student_info->id)->where('session_id', '=', $student_info->session_id)->first();

            $subjects = AramiscAssignSubject::where([['class_id', $class_id], ['section_id', $section_id]])->get();
            $assign_subjects = AramiscAssignSubject::where([['class_id', $class_id], ['section_id', $section_id]])->get();
            foreach ($subjects as $row) {
                $subject_id = $row->subject_id;
                $total_mark = AramiscAssignSubject::getSumMark($student_id, $subject_id, $class_id, $section_id, $exam_term_id);
                $mark_grade = AramiscMarksGrade::where([['percent_from', '<=', $total_mark], ['percent_upto', '>=', $total_mark]])->first();
                $optional_subject_id = '';
                if (!empty($optional_subject)) {
                    $optional_subject_id = $optional_subject->subject_id;
                }
                $total_gpa_point = $total_gpa_point + $mark_grade->gpa;
                if ($mark_grade->gpa < 1) {
                    $this_student_failed = 1;
                }
            }
            if ($this_student_failed != 1) {
                if ($optional_subject_id != '') {

                    $number_of_subject = count($assign_subjects);
                    if ($total_gpa_point != 0 && $number_of_subject != "") {
                        $final_result = number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                        return $final_result;
                    } else {
                        return '0.00';
                    }
                } else {
                    $number_of_subject = count($assign_subjects);

                    if ($total_gpa_point != 0 && $number_of_subject != "") {
                        $final_result = number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                        return $final_result;
                    } else {
                        return '0.00';
                    }
                }
            } else {
                return '0.00';
            }
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function subjectPosition($subject_id, $class_id, $custom_result)
    {

        $students = AramiscStudent::where('class_id', $class_id)->get();

        $subject_mark_array = [];
        foreach ($students as $student) {
            $subject_marks = 0;

            $first_exam_mark = AramiscMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id1)->sum('total_marks');

            $subject_marks = $subject_marks + $first_exam_mark / 100 * $custom_result->percentage1;

            $second_exam_mark = AramiscMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id2)->sum('total_marks');

            $subject_marks = $subject_marks + $second_exam_mark / 100 * $custom_result->percentage2;

            $third_exam_mark = AramiscMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id3)->sum('total_marks');

            $subject_marks = $subject_marks + $third_exam_mark / 100 * $custom_result->percentage3;

            $subject_mark_array[] = round($subject_marks);
        }

        arsort($subject_mark_array);

        $position_array = [];
        foreach ($subject_mark_array as $position_mark) {
            $position_array[] = $position_mark;
        }


        return $position_array;
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id);
    }
    public function teacherEvaluation()
    {
        return $this->hasMany(TeacherEvaluation::class, 'record_id', 'id');
    }
}
