<?php

namespace App\Http\Controllers\api;

use App\AramiscStudent;
use App\ApiBaseMethod;
use App\Scopes\SchoolScope;
use App\AramiscAcademicYear;
use Illuminate\Http\Request;
use App\AramiscTeacherUploadContent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;

class ApiSmStudyMaterialController extends Controller
{



    /**
     *studentAssignmentApi
     * @response {
     *"success": true,
     *"data": {
     *    "student_detail": {
     *    "id": 2,
     *    "full_name": "Genevieve Wiggins",
     *    "admission_no": 898,
     *    "email": "wybefo@mailinator.com",
     *    "mobile": "+1 (906) 497-2761",
     *    "class_id": 42,
     *    "section_id": 1
     *    },
     *    "uploadContents": [
     *    {
     *        "content_title": "Assignment",
     *        "upload_date": "2021-04-05",
     *        "description": "Hello",
     *        "upload_file": "public/uploads/upload_contents/5b5ec23c13ae51891c941d0d00b5d011.jpg"
     *    }
     *    ]
     *},

     */
    public function studentAssignmentApi(Request $request, $user_id, $record_id)
    {

        $student_detail = AramiscStudent::where('user_id', $user_id)->first();
        $record = StudentRecord::where('id', $record_id)->where('student_id', $student_detail->id)->first();
        $uploadContents = AramiscTeacherUploadContent::where('content_type', 'as')
            ->select('content_title', 'upload_date', 'description', 'upload_file', 'source_url')
            ->where(function ($query) use ($record) {
                $query->where('available_for_all_classes', 1)
                    ->orWhere([['class', $record->class_id], ['section', $record->section_id]]);
            })->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();
            $data['uploadContents'] = $uploadContents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }


    /**
     *studentSyllabusApi
     * @response {
     *"success": true,
     *"data": {
     *    "student_detail": {
     *    "id": 2,
     *    "full_name": "Genevieve Wiggins",
     *    "admission_no": 898,
     *    "email": "wybefo@mailinator.com",
     *    "mobile": "+1 (906) 497-2761",
     *    "class_id": 42,
     *    "section_id": 1
     *    },
     *    "uploadContents": [
     *    {
     *        "content_title": "Syllabus",
     *        "upload_date": "2021-04-05",
     *        "description": "Hello",
     *        "upload_file": "public/uploads/upload_contents/5b5ec23c13ae51891c941d0d00b5d011.jpg"
     *    }
     *    ]
     *},

     */
    public function studentSyllabusApi(Request $request, $user_id, $record_id)
    {
        $student_detail = AramiscStudent::where('user_id', $user_id)->first(['id','full_name','admission_no','email','mobile','class_id','section_id']);

        $record = StudentRecord::where('id', $record_id)->first();
        
        $uploadContents = AramiscTeacherUploadContent::where('content_type', 'sy')->where('academic_id',AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                                ->where(function ($que) use ($record) {
                                    return $que->where('class', $record->class_id)
                                        ->orWhereNull('class');
                                })
                                ->where(function ($que) use ($record) {
                                    return $que->where('section', $record->section_id)
                                        ->orWhereNull('section');
                                })->select('content_title', 'upload_date', 'description', 'upload_file', 'source_url','content_type','academic_id')
                            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();
            $data['uploadContents'] = $uploadContents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }

    /**
     *studentOtherDownloadsApi
     * @response {
     *"success": true,
     *"data": {
     *    "student_detail": {
     *    "id": 2,
     *    "full_name": "Genevieve Wiggins",
     *    "admission_no": 898,
     *    "email": "wybefo@mailinator.com",
     *    "mobile": "+1 (906) 497-2761",
     *    "class_id": 42,
     *    "section_id": 1
     *    },
     *    "uploadContents": [
     *    {
     *        "content_title": "Other Download",
     *        "upload_date": "2021-04-05",
     *        "description": "Hello",
     *        "upload_file": "public/uploads/upload_contents/5b5ec23c13ae51891c941d0d00b5d011.jpg"
     *    }
     *    ]
     *},

     */
    public function studentOtherDownloadsApi(Request $request, $user_id, $record_id)
    {

        $student_detail = AramiscStudent::where('user_id', $user_id)->first(['id','full_name','admission_no','email','mobile','class_id','section_id']);

        $record = StudentRecord::where('id', $record_id)->where('student_id', $student_detail->id)->first();
        $uploadContents = AramiscTeacherUploadContent::where('content_type', 'ot')
            ->select('content_title', 'upload_date', 'description', 'upload_file', 'source_url')
            ->where(function ($query) use ($record) {
                $query->where('available_for_all_classes', 1)
                    ->orWhere([['class', $record->class_id], ['section', $record->section_id]]);
            })->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();
            $data['uploadContents'] = $uploadContents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_studentAssignmentApi(Request $request, $school_id, $user_id, $record_id)
    {
      
        $student_detail = AramiscStudent::withoutGlobalScope(SchoolScope::class)->where('user_id', $user_id)->where('school_id', $school_id)->first();
         $record = StudentRecord::where('id', $record_id)
        ->where('student_id', $student_detail->id)
        ->where('school_id', $school_id)
        ->first();
        $uploadContents = AramiscTeacherUploadContent::where('content_type', 'as')
            ->select('content_title', 'upload_date', 'description', 'upload_file', 'source_url')
            ->where(function ($query) use ($record) {
                $query->where('available_for_all_classes', 1)
                    ->orWhere([['class', @$record->class_id], ['section', @$record->section_id]]);
            })->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))->where('school_id', $school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = @$student_detail->toArray();
            $data['uploadContents'] = @$uploadContents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_studentSyllabusApi(Request $request, $school_id, $user_id, $record_id)
    {

        $student_detail = AramiscStudent::withoutGlobalScope(SchoolScope::class)->where('user_id', $user_id)->where('school_id', $school_id)
        ->first(['id', 'full_name', 'admission_no', 'email', 'mobile', 'class_id', 'section_id']);
        $record = StudentRecord::where('id', $record_id)
        ->where('student_id', $student_detail->id)
        ->where('school_id', $school_id)
        ->first();
        if (!$student_detail) {
            $data = [];
            $data['student_detail'] = [];
            $data['uploadContents'] = [];
            return ApiBaseMethod::sendResponse($data, null);
        }
        $uploadContents = AramiscTeacherUploadContent::where('content_type', 'sy')
            ->select('content_title', 'upload_date', 'description', 'upload_file', 'source_url')
            ->where(function ($query) use ($record) {
                $query->where('available_for_all_classes', 1)
                    ->orWhere([['class', $record->class_id], ['section', $record->section_id]]);
            })->where('school_id', $school_id)->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();
            $data['uploadContents'] = $uploadContents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_studentOtherDownloadsApi(Request $request, $school_id, $user_id, $record_id)
    {

        $student_detail = AramiscStudent::withoutGlobalScope(SchoolScope::class)->where('user_id', $user_id)->where('school_id', $school_id)
        ->first(['id','full_name','admission_no','email','mobile','class_id','section_id']);
        $record = StudentRecord::where('id', $record_id)->where('student_id', $student_detail->id)->where('school_id', $school_id)->first();
        $uploadContents = AramiscTeacherUploadContent::where('content_type', 'ot')
            ->select('content_title', 'upload_date', 'description', 'upload_file','source_url')
            ->where(function ($query) use ($record) {
                $query->where('available_for_all_classes', 1)
                    ->orWhere([['class', $record->class_id], ['section', $record->section_id]]);
            })->where('school_id', $school_id)
            ->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();
            $data['uploadContents'] = $uploadContents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
}
