<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\AramiscStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiAramiscStudentPanelController extends Controller
{
    public function studentTeacherApi(Request $request, $user_id, $record_id)
    {

        $student_id = AramiscStudent::where('user_id', $user_id)->value('id');
        $record = StudentRecord::where('id', $record_id)->where('student_id', $student_id)->first();
        $assignTeacher = DB::table('aramisc_assign_subjects')
            ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
            ->leftjoin('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_assign_subjects.teacher_id')
            ->select('aramisc_staffs.full_name', 'aramisc_staffs.email', 'aramisc_staffs.mobile')
            ->where('aramisc_assign_subjects.class_id', '=', $record->class_id)
            ->where('aramisc_assign_subjects.section_id', '=', $record->section_id)
            ->get();

        $class_teacher = DB::table('aramisc_class_teachers')
            ->join('aramisc_assign_class_teachers', 'aramisc_assign_class_teachers.id', '=', 'aramisc_class_teachers.assign_class_teacher_id')
            ->join('aramisc_staffs', 'aramisc_class_teachers.teacher_id', '=', 'aramisc_staffs.id')
            ->where('aramisc_assign_class_teachers.class_id', '=', $record->class_id)
            ->where('aramisc_assign_class_teachers.section_id', '=', $record->section_id)
            ->where('aramisc_assign_class_teachers.active_status', '=', 1)
            ->select('full_name')
            ->first();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['teacher_list'] = $assignTeacher->toArray();
            $data['class_teacher'] = $class_teacher;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_studentTeacherApi(Request $request, $school_id, $user_id, $record_id)
    {
        $student_id = AramiscStudent::where('user_id', $user_id)->where('school_id', $school_id)->value('id');
        $record = StudentRecord::where('id', $record_id)
        ->where('student_id', $student_id)
        ->where('school_id', $school_id)
        ->first();
        $assignTeacher = DB::table('aramisc_assign_subjects')
            ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
            ->leftjoin('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_assign_subjects.teacher_id')
            ->select('aramisc_staffs.full_name', 'aramisc_staffs.email', 'aramisc_staffs.mobile')
            ->where('aramisc_assign_subjects.class_id', '=', @$record->class_id)
            ->where('aramisc_assign_subjects.section_id', '=', @$record->section_id)
            ->get();

        $class_teacher = DB::table('aramisc_class_teachers')
            ->join('aramisc_assign_class_teachers', 'aramisc_assign_class_teachers.id', '=', 'aramisc_class_teachers.assign_class_teacher_id')
            ->join('aramisc_staffs', 'aramisc_class_teachers.teacher_id', '=', 'aramisc_staffs.id')
            ->where('aramisc_assign_class_teachers.class_id', '=', @$record->class_id)
            ->where('aramisc_assign_class_teachers.section_id', '=', @$record->section_id)
            ->where('aramisc_assign_class_teachers.active_status', '=', 1)
            ->select('full_name')
            ->first();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['teacher_list'] = $assignTeacher->toArray();
            $data['class_teacher'] = $class_teacher;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
}
