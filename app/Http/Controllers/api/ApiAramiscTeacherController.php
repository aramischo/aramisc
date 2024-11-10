<?php

namespace App\Http\Controllers\api;

use App\User;
use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscStudent;
use App\ApiBaseMethod;
use App\AramiscContentType;
use App\AramiscAcademicYear;
use App\AramiscAssignSubject;
use Illuminate\Http\Request;
use App\AramiscTeacherUploadContent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class ApiAramiscTeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');

    }
    public function contentList(Request $request)
    {

        $content_list = DB::table('aramisc_teacher_upload_contents')
            ->where('available_for_admin', '<>', 0)
            ->get();
        $type = "as assignment, st study material, sy sullabus, ot others download";
        $data = [];
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['content_list'] = $content_list->toArray();
            $data['type'] = $type;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_contentList(Request $request, $school_id)
    {
        $content_list = DB::table('aramisc_teacher_upload_contents')
            ->where('available_for_admin', '<>', 0)
            ->where('school_id', $school_id)->get();
        $type = "as assignment, st study material, sy sullabus, ot others download";
        $data = [];
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['content_list'] = $content_list->toArray();
            $data['type'] = $type;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saasUploadContentList(Request $request, $school_id)
    {
        try {

            $uploadContents = AramiscTeacherUploadContent::where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))->where('school_id', $school_id)->get();
            $contents = [];
            $type = "as assignment, st study material, sy sullabus, ot others download";

            foreach ($uploadContents as $data) {
                $d['id'] = $data->id;
                $d['title'] = $data->content_title;

                if ($data->content_type == 'as') {
                    $d['type'] = 'assignment';
                } elseif ($data->content_type == 'st') {
                    $d['type'] = 'syllabus';
                } else {
                    $d['type'] = 'Other Download';
                }

                if ($data->available_for_admin == 1) {
                    $d['available_for'] = 'all admins';
                }
                if ($data->available_for_all_classes == 1) {
                    $d['available_for'] = 'all classes student';
                }
                if ($data->classes != "" && $data->sections != "") {
                    $d['available_for'] = 'All Students Of (' . $data->classes->class_name . '->' . @$data->sections->section_name . ')';
                }
                $d['upload_date'] = $data->upload_date;
                $d['description'] = $data->description;
                $d['upload_file'] = $data->upload_file;
                $d['created_by'] = $data->users->full_name;
                $d['source_url'] = $data->source_url;

                $contents[] = $d;

            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['uploadContents'] = $contents;
                $data['type'] = $type;
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {

        }
    }

    public function uploadContentList(Request $request)
    {
        try {

            $uploadContents = AramiscTeacherUploadContent::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id', 1)
                ->get();
            $contents = [];
            foreach ($uploadContents as $data) {
                $d['id'] = $data->id;
                $d['title'] = $data->content_title;

                if ($data->content_type == 'as') {
                    $d['type'] = 'assignment';
                } elseif ($data->content_type == 'st') {
                    $d['type'] = 'Study Material';
                }elseif ($data->content_type == 'sy') {
                    $d['type'] = 'Syllabus';
                } else {
                    $d['type'] = 'Other Download';
                }

                if ($data->available_for_admin == 1) {
                    $d['available_for'] = 'all admins';
                }
                if ($data->available_for_all_classes == 1) {
                    $d['available_for'] = 'all classes student';
                }
                if ($data->classes != "" && $data->sections != "") {
                    $d['available_for'] = 'All Students Of (' . $data->classes->class_name . '->' . @$data->sections->section_name . ')';
                }
                $d['upload_date'] = $data->upload_date;
                $d['description'] = $data->description;
                $d['upload_file'] = $data->upload_file;
                $d['created_by'] = $data->users->full_name;
                $d['source_url'] = $data->source_url;

                $contents[] = $d;

            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['uploadContents'] = $contents;
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {

        }
    }

    public function uploadContentListByUser(Request $request, $user_id)
    {
        try {
            $user = User::select('full_name', 'role_id', 'id')->find($user_id);
            $contentTypes = AramiscContentType::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id', 1)->get();

            if ($user->role_id == 4) {
                $uploadContents = AramiscTeacherUploadContent::where(function ($q) use ($user_id) {
                    $q->where('created_by', $user_id)->orWhere('available_for_admin', 1);
                })
                    ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('school_id', 1)
                    ->get();
            } elseif ($user->role_id == 5) {
                $uploadContents = AramiscTeacherUploadContent::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('school_id', 1)
                    ->get();
            } elseif ($user->role_id != 2) {
                $student = AramiscStudent::select('class_id', 'section_id')->find($user->id);
                $uploadContents = AramiscTeacherUploadContent::where('created_by', $user->id)
                    ->orwhere('available_for_admin', 1)
                    ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('school_id', 1)
                    ->get();
            }

            $uploadContents = AramiscTeacherUploadContent::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id', 1)
                ->get();
            $contents = [];
            foreach ($uploadContents as $data) {
                $d['id'] = $data->id;
                $d['title'] = $data->content_title;
                if ($data->content_type == 'as') {
                    $d['type'] = 'assignment';
                } elseif ($data->content_type == 'st') {
                    $d['type'] = 'Study Material';
                }elseif ($data->content_type == 'sy') {
                    $d['type'] = 'Syllabus';
                } else {
                    $d['type'] = 'Other Download';
                }

                if ($data->available_for_admin == 1) {
                    $d['available_for'] = 'all admins';
                }
                if ($data->available_for_all_classes == 1) {
                    $d['available_for'] = 'all classes student';
                }
                if ($data->classes != "" && $data->sections != "") {
                    $d['available_for'] = 'All Students Of (' . $data->classes->class_name . '->' . @$data->sections->section_name . ')';
                }
                $d['upload_date'] = $data->upload_date;
                $d['description'] = $data->description;
                $d['upload_file'] = $data->upload_file;
                $d['created_by'] = $data->users->full_name;
                $d['source_url'] = $data->source_url;
                $contents[] = $d;

            }

            if ($user->role_id == 4) {
                $teacher_info = AramiscStaff::where('user_id', $user->id)->first();
                $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
                    ->where('aramisc_assign_subjects.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('aramisc_assign_subjects.active_status', 1)
                    ->where('aramisc_assign_subjects.school_id', 1)
                    ->select('aramisc_classes.id', 'class_name')
                    ->distinct('aramisc_classes.id')
                    ->get();
            } elseif ($user->role_id == 5 || $user->role_id == 1) {
                $classes = AramiscClass::where('active_status', 1)
                    ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('school_id', 1)
                    ->select('aramisc_classes.id', 'class_name')
                    ->get();
            } else {
                $classes = [];
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['contentTypes'] = $contentTypes->toArray();
                $data['uploadContents'] = $contents;
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function viewContent(Request $request, $id)
    {

        $uploadContent = AramiscTeacherUploadContent::where('id', $id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
            ->where('school_id', 1)
            ->first();

        $d['id'] = $uploadContent->id;
        $d['title'] = $uploadContent->content_title;

        if ($uploadContent->content_type == 'as') {
            $d['type'] = 'assignment';
        } elseif ($uploadContent->content_type == 'st') {
            $d['type'] = 'syllabus';
        } else {
            $d['type'] = 'Other Download';
        }

        if ($uploadContent->available_for_admin == 1) {
            $d['available_for'] = 'all admins';
        }
        if ($uploadContent->available_for_all_classes == 1) {
            $d['available_for'] = 'all classes student';
        }
        if ($uploadContent->classes != "" && $uploadContent->sections != "") {
            $d['available_for'] = 'All Students Of (' . $uploadContent->classes->class_name . '->' . @$uploadContents->sections->section_name . ')';
        }
        $d['upload_date'] = $uploadContent->upload_date;
        $d['description'] = $uploadContent->description;
        $d['upload_file'] = $uploadContent->upload_file;
        $d['created_by'] = $uploadContent->users->full_name;
        $d['source_url'] = $uploadContent->source_url;
        $content = $d;

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $data['uploadContent'] = $content;

            return ApiBaseMethod::sendResponse($data, 'Content uploaded successfully.');
        }
    }
    public function deleteContent(Request $request, $id)
    {
        $content = DB::table('aramisc_teacher_upload_contents')->where('id', $id)->delete();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = '';
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_deleteContent(Request $request, $school_id, $id)
    {
        $content = DB::table('aramisc_teacher_upload_contents')->where('id', $id)->where('school_id', $school_id)->delete();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = '';
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
}
