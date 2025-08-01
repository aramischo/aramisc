<?php

namespace App\Http\Controllers\api;

use App\Services\FirebasePushService;
use App\User;
use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscParent;
use App\AramiscStudent;
use App\AramiscHomework;
use App\ApiBaseMethod;
use App\AramiscAcademicYear;
use App\AramiscNotification;
use App\AramiscAssignSubject;
use App\AramiscHomeworkStudent;
use App\Scopes\SchoolScope;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscUploadHomeworkContent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Admin\StudentInfo\AramiscStudentReportController;

class ApiAramiscHomeWorkController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }
    public function homeworkList(Request $request, $user_id)
    {
        try {
            set_time_limit(900);
            $user = User::select('id', 'role_id')->find($user_id);
            if ($user->role_id == 1 || $user->role_id == 5) {
                $homeworkLists = AramiscHomework::orderBy('homework_date','desc')->where('aramisc_homeworks.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_homeworks.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_homeworks.section_id')
                    ->join('users', 'users.id', '=', 'aramisc_homeworks.created_by')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                    ->where('aramisc_homeworks.school_id', 1)
                    ->select('aramisc_homeworks.id', 'aramisc_homeworks.class_id', 'aramisc_homeworks.section_id', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'users.full_name', 'aramisc_classes.class_name', 'aramisc_sections.section_name', 'aramisc_subjects.subject_name', 'aramisc_homeworks.marks', 'aramisc_homeworks.file', 'aramisc_homeworks.description')
                    ->get();

                $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            } else {
                $homeworkLists = AramiscHomework::orderBy('homework_date','desc')->where('aramisc_homeworks.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_homeworks.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_homeworks.section_id')
                    ->join('users', 'users.id', '=', 'aramisc_homeworks.created_by')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                    ->where('aramisc_homeworks.school_id', 1)
                    ->where('aramisc_homeworks.created_by', $user->id)
                    ->select('aramisc_homeworks.id', 'aramisc_homeworks.class_id', 'aramisc_homeworks.section_id', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'users.full_name', 'aramisc_classes.class_name', 'aramisc_sections.section_name', 'aramisc_subjects.subject_name', 'aramisc_homeworks.marks', 'aramisc_homeworks.file', 'aramisc_homeworks.description')
                    ->get();

                $teacher_info = AramiscStaff::where('user_id', $user->id)->first();

                $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)
                    ->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
                    ->where('aramisc_assign_subjects.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('aramisc_assign_subjects.active_status', 1)
                    ->where('aramisc_assign_subjects.school_id', 1)
                    ->select('aramisc_classes.id', 'class_name')
                    ->distinct('aramisc_classes.id')
                    ->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['homeworkLists'] = $homeworkLists->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_homeworkList(Request $request, $school_id)
    {
        try {
            $user_id = Auth::id();
            set_time_limit(900);
            $user = User::select('id', 'role_id')->find($user_id);
            if ($user->role_id == 1 || $user->role_id == 5) {
                $homeworkLists = AramiscHomework::where('aramisc_homeworks.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_homeworks.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_homeworks.section_id')
                    ->join('users', 'users.id', '=', 'aramisc_homeworks.created_by')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                    ->where('aramisc_homeworks.school_id', $school_id)
                    ->select('aramisc_homeworks.id', 'aramisc_homeworks.class_id', 'aramisc_homeworks.section_id', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'users.full_name', 'aramisc_classes.class_name', 'aramisc_sections.section_name', 'aramisc_subjects.subject_name', 'aramisc_homeworks.marks', 'aramisc_homeworks.file', 'aramisc_homeworks.description')
                    ->get();

                $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            } else {
                $homeworkLists = AramiscHomework::where('aramisc_homeworks.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_homeworks.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_homeworks.section_id')
                    ->join('users', 'users.id', '=', 'aramisc_homeworks.created_by')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                    ->where('aramisc_homeworks.school_id', $school_id)
                    ->where('aramisc_homeworks.created_by', $user->id)
                    ->select('aramisc_homeworks.id', 'aramisc_homeworks.class_id', 'aramisc_homeworks.section_id', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'users.full_name', 'aramisc_classes.class_name', 'aramisc_sections.section_name', 'aramisc_subjects.subject_name', 'aramisc_homeworks.marks', 'aramisc_homeworks.file', 'aramisc_homeworks.description')
                    ->get();

                $teacher_info = AramiscStaff::where('user_id', $user->id)->first();

                $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)
                    ->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
                    ->where('aramisc_assign_subjects.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('aramisc_assign_subjects.active_status', 1)
                    ->where('aramisc_assign_subjects.school_id', $school_id)
                    ->select('aramisc_classes.id', 'class_name')
                    ->distinct('aramisc_classes.id')
                    ->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['homeworkLists'] = $homeworkLists->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function saas_homework_List_Teacher(Request $request, $school_id, $user_id)
    {
        try {
            set_time_limit(900);
            $user = User::select('id', 'role_id')->find($user_id);
            if ($user->role_id == 1 || $user->role_id == 5) {
                $homeworkLists = AramiscHomework::where('aramisc_homeworks.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_homeworks.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_homeworks.section_id')
                    ->join('users', 'users.id', '=', 'aramisc_homeworks.created_by')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                    ->where('aramisc_homeworks.school_id', $school_id)
                    ->select('aramisc_homeworks.id', 'aramisc_homeworks.class_id', 'aramisc_homeworks.section_id', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'users.full_name', 'aramisc_classes.class_name', 'aramisc_sections.section_name', 'aramisc_subjects.subject_name', 'aramisc_homeworks.marks', 'aramisc_homeworks.file', 'aramisc_homeworks.description')
                    ->get();

                $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            } else {
                $homeworkLists = AramiscHomework::where('aramisc_homeworks.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_homeworks.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_homeworks.section_id')
                    ->join('users', 'users.id', '=', 'aramisc_homeworks.created_by')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                    ->where('aramisc_homeworks.school_id', $school_id)
                    ->where('aramisc_homeworks.created_by', $user->id)
                    ->select('aramisc_homeworks.id', 'aramisc_homeworks.class_id', 'aramisc_homeworks.section_id', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'users.full_name', 'aramisc_classes.class_name', 'aramisc_sections.section_name', 'aramisc_subjects.subject_name', 'aramisc_homeworks.marks', 'aramisc_homeworks.file', 'aramisc_homeworks.description')
                    ->get();

                $teacher_info = AramiscStaff::where('user_id', $user->id)->first();

                $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)
                    ->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
                    ->where('aramisc_assign_subjects.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('aramisc_assign_subjects.active_status', 1)
                    ->where('aramisc_assign_subjects.school_id', $school_id)
                    ->select('aramisc_classes.id', 'class_name')
                    ->distinct('aramisc_classes.id')
                    ->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['homeworkLists'] = $homeworkLists->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function saasSaveHomeworkEvaluationData(Request $request, $school_id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $input = $request->all();
                $validator = Validator::make($input, [
                    'student_id' => "required",
                    'login_id' => "required",
                    'homework_id' => "required",

                ]);

            }
            $user = User::select('id', 'role_id')->find($request->login_id);
            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
            }
            if (!$request->student_id) {
                return ApiBaseMethod::sendError('please Select Student Id.', $validator->errors());
            } else {
                $student_idd = count($request->student_id);
                if ($student_idd > 0) {
                    for ($i = 0; $i < $student_idd; $i++) {
                        if ($user->role_id == 1 || $user->role_id == 5) {
                            AramiscHomeworkStudent::where('student_id', $request->student_id[$i])
                                ->where('homework_id', $request->homework_id)
                                ->delete();
                        } else {
                            AramiscHomeworkStudent::where('student_id', $request->student_id[$i])
                                ->where('homework_id', $request->homework_id)
                                ->where('school_id', $school_id)
                                ->delete();
                        }
                        $homeworkstudent = new AramiscHomeworkStudent();
                        $homeworkstudent->homework_id = $request->homework_id;
                        $homeworkstudent->student_id = $request->student_id[$i];
                        $homeworkstudent->marks = $request->marks[$i];
                        $homeworkstudent->teacher_comments = $request->teacher_comments[$request->student_id[$i]];
                        $homeworkstudent->complete_status = $request->homework_status[$request->student_id[$i]];
                        $homeworkstudent->created_by = $request->login_id;
                        $homeworkstudent->school_id = $school_id;
                        $homeworkstudent->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                        $results = $homeworkstudent->save();
                    }
                    $homeworks = AramiscHomework::find($request->homework_id);
                    $homeworks->evaluation_date = date('Y-m-d', strtotime($request->evaluation_date));
                    $homeworks->evaluated_by = $request->login_id;
                    $result = $homeworks->update();
                }
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($result) {
                        return ApiBaseMethod::sendResponse(null, 'Homework Evaluation successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again');
                    }
                }
            }

        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function saveHomeworkEvaluationData(Request $request)
    {
        $school_id = 1;
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $input = $request->all();
                $validator = Validator::make($input, [
                    'student_id' => "required",
                    'login_id' => "required",
                    'homework_id' => "required",

                ]);

            }
            $user = User::select('id', 'role_id')->find($request->login_id);
            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
            }
            if (!$request->student_id) {
                return ApiBaseMethod::sendError('please Select Student Id.', $validator->errors());
            } else {
                $student_idd = count($request->student_id);
                if ($student_idd > 0) {
                    for ($i = 0; $i < $student_idd; $i++) {
                        if ($user->role_id == 1 || $user->role_id == 5) {
                            AramiscHomeworkStudent::where('student_id', $request->student_id[$i])
                                ->where('homework_id', $request->homework_id)
                                ->delete();
                        } else {
                            AramiscHomeworkStudent::where('student_id', $request->student_id[$i])
                                ->where('homework_id', $request->homework_id)
                                ->where('school_id', $school_id)
                                ->delete();
                        }
                        $homeworkstudent = new AramiscHomeworkStudent();
                        $homeworkstudent->homework_id = $request->homework_id;
                        $homeworkstudent->student_id = $request->student_id[$i];
                        $homeworkstudent->marks = $request->marks[$i];
                        $homeworkstudent->teacher_comments = $request->teacher_comments[$request->student_id[$i]];
                        $homeworkstudent->complete_status = $request->homework_status[$request->student_id[$i]];
                        $homeworkstudent->created_by = $request->login_id;
                        $homeworkstudent->school_id = $school_id;
                        $homeworkstudent->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                        $results = $homeworkstudent->save();
                    }
                    $homeworks = AramiscHomework::find($request->homework_id);
                    $homeworks->evaluation_date = date('Y-m-d', strtotime($request->evaluation_date));
                    $homeworks->evaluated_by = $request->login_id;
                    $result = $homeworks->update();
                }
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($result) {
                        return ApiBaseMethod::sendResponse(null, 'Homework Evaluation successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again');
                    }
                }
            }

        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function addHomework(Request $request)
    {

        if (teacherAccess()) {
            $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
            $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)
                ->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
                ->where('aramisc_assign_subjects.academic_id', getAcademicId())
                ->where('aramisc_assign_subjects.active_status', 1)
                ->where('aramisc_assign_subjects.school_id', Auth::user()->school_id)
                ->select('aramisc_classes.id', 'class_name')
                ->distinct('aramisc_classes.id')
                ->get();
        } else {
            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
        }
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['classes'] = $classes->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
        return view('backEnd.homework.addHomework', compact('classes'));

    }
    public function saveHomeworkData(Request $request)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'class_id' => "required",
                'section_id' => "required",
                'subject_id' => "required",
                'homework_date' => "required",
                'submission_date' => "required",
                'marks' => "required|integer|min:0",
                'description' => "required",
                'created_by' => "required",
                // 'homework_file' => "sometimes|nullable|mimes:pdf,doc,docx,txt,jpg,jpeg,png,mp4,ogx,oga,ogv,ogg,webm,mp3,",
            ]);

        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        try {
            $fileName = "";
            if ($request->file('homework_file') != "") {
                $file = $request->file('homework_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/homework/', $fileName);
                $fileName = 'public/uploads/homework/' . $fileName;
            }

            $homeworks = new AramiscHomework();
            $homeworks->class_id = $request->class_id;
            $homeworks->section_id = $request->section_id;
            $homeworks->subject_id = $request->subject_id;
            $homeworks->homework_date = date('Y-m-d', strtotime($request->homework_date));
            $homeworks->submission_date = date('Y-m-d', strtotime($request->submission_date));
            $homeworks->marks = $request->marks;
            $homeworks->description = $request->description;
            $homeworks->file = $fileName;
            $homeworks->created_by = $request->created_by;
            $homeworks->school_id = auth()->user()->school_id;
            $homeworks->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $results = $homeworks->save();

            $students = AramiscStudent::where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            foreach ($students as $student) {
                $notification = new AramiscNotification;
                $notification->user_id = $student->user_id;
                $notification->role_id = 2;
                $notification->date = date('Y-m-d');
                $notification->message = 'New Homework assigned';
                $notification->school_id = 1;
                $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                $notification->save();

                $parent = AramiscParent::find($student->parent_id);
                $notidication = new AramiscNotification();
                $notidication->role_id = 3;
                $notidication->message = "New homework assigned for your child";
                $notidication->date = date('Y-m-d');
                $notidication->user_id = $parent->user_id;
                $notidication->url = "homework-list";
                $notidication->school_id = 1;
                $notidication->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                $notidication->save();
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'New homework has been added successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            }
        } catch (\Throwable$th) {

        }

    }

    public function saas_addHomework(Request $request)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'class_id' => "required",
                'section_id' => "required",
                'subject_id' => "required",
                'homework_date' => "required",
                'submission_date' => "required",
                'marks' => "required|integer|min:0",
                'description' => "required",
                'school_id' => "required",
                'created_by' => "required",
                // 'homework_file' => "sometimes|nullable|mimes:pdf,doc,docx,txt,jpg,jpeg,png,mp4,ogx,oga,ogv,ogg,webm,mp3,",
            ]);

        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        try {
            $fileName = "";
            if ($request->file('homework_file') != "") {
                $file = $request->file('homework_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/homework/', $fileName);
                $fileName = 'public/uploads/homework/' . $fileName;
            }

            $homeworks = new AramiscHomework();
            $homeworks->class_id = $request->class_id;
            $homeworks->section_id = $request->section_id;
            $homeworks->subject_id = $request->subject_id;
            $homeworks->homework_date = date('Y-m-d', strtotime($request->homework_date));
            $homeworks->submission_date = date('Y-m-d', strtotime($request->submission_date));
            $homeworks->marks = $request->marks;
            $homeworks->description = $request->description;
            $homeworks->file = $fileName;
            $homeworks->created_by = $request->created_by;
            $homeworks->school_id = $request->school_id;
            $homeworks->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR($request->school_id);
            $results = $homeworks->save();

            $students = AramiscStudent::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($request->school_id))
                ->where('school_id', $request->school_id)
                ->get();
            foreach ($students as $student) {
                $notification = new AramiscNotification;
                $notification->user_id = $student->user_id;
                $notification->role_id = 2;
                $notification->date = date('Y-m-d');
                $notification->message = 'New Homework assigned';
                $notification->school_id = $request->school_id;
                $notification->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR($request->school_id);
                $notification->save();

                $parent = AramiscParent::find($student->parent_id);
                $notidication = new AramiscNotification();
                $notidication->role_id = 3;
                $notidication->message = "New homework assigned for your child";
                $notidication->date = date('Y-m-d');
                $notidication->user_id = $parent->user_id;
                $notidication->url = "homework-list";
                $notidication->school_id = $request->school_id;
                $notidication->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR($request->school_id);
                $notidication->save();
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'New homework has been added successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            }
        } catch (\Throwable$th) {

        }

    }

    public function saas_studentHomework(Request $request, $school_id, $user_id, $record_id)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $user = User::select('full_name', 'id', 'role_id')->find($user_id);
                if ($user->role_id != 2) {
                    return ApiBaseMethod::sendError('Invalid Student ID');
                }
            }
            $student_detail = AramiscStudent::withOutGlobalScope(SchoolScope::class)->where('user_id', $user_id)->with('homeworks')->first();

            if (!$student_detail) {
                $data = [];
                return ApiBaseMethod::sendResponse($data, null);
            }
            $student = AramiscStudent::withOutGlobalScope(SchoolScope::class)->where('user_id', $user_id)->first();
            $record = StudentRecord::where('school_id', $school_id)
                ->where('student_id', $student->id)
                ->where('id', $record_id)
                ->with('homework')
                ->first();
            $homeworkLists = AramiscHomework::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('class_id', $record->class_id)
                ->where('section_id', $record->section_id)
                 ->where('aramisc_homeworks.academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
                ->where('school_id', $school_id)
                ->with('saasclass','saassection','saassubject')
                ->get();
            $student_homeworks = [];

            foreach ($homeworkLists as $s_homework) {

                $student_result = $record->homework->where('homework_id', $s_homework->id)->first();
                $uploadedContent = $student_detail->homeworkContents->where('homework_id', $s_homework->id)->first();
                

                $d['id'] = $s_homework->id;
                $d['homework_date'] = $s_homework->homework_date;
                $d['submission_date'] = $s_homework->submission_date;
                $d['created_by'] = $s_homework->saasusers->full_name;
                $d['class_name'] = $s_homework->saasclass->class_name;
                $d['section_name'] = $s_homework->saassection->section_name;
                $d['subject_name'] = $s_homework->saassubject->subject_name;
                $d['marks'] = $s_homework->marks;
                $d['file'] = $s_homework->file;
                $d['description'] = $s_homework->description;
                $d['obtained_marks'] = $student_result != "" ? $student_result->marks : '';
                if ($student_result != "") {
                    if ($student_result->complete_status == "C") {
                        $d['status'] = 'Completed';
                    } else {
                        $d['status'] = 'incompleted';
                    }
                } else {
                    $d['status'] = 'incompleted';
                }
                $student_homeworks[] = $d;

            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = $student_homeworks;
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function studentHomework(Request $request, $user_id, $record_id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $user = User::select('full_name', 'id', 'role_id')->find($user_id);
                if ($user->role_id != 2) {
                    return ApiBaseMethod::sendError('Invalid Student ID');
                }
            }
            $student = AramiscStudent::where('user_id', $user_id)->first();
            $record = StudentRecord::where('school_id', auth()->user()->school_id)
                ->where('student_id', $student->id)
                ->where('id', $record_id)
                ->first();
            $homeworkLists = AramiscHomework::orderBy('homework_date','desc')->where('class_id', $record->class_id)
                ->where('section_id', $record->section_id)
                ->where('aramisc_homeworks.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id', 1)
                ->get();
            $student_homeworks = [];

            foreach ($homeworkLists as $s_homework) {
                $student_result = $student->homeworks->where('homework_id', $s_homework->id)->first();
                $d['id'] = $s_homework->id;
                $d['homework_date'] = $s_homework->homework_date;
                $d['submission_date'] = $s_homework->submission_date;
                $d['created_by'] = $s_homework->users->full_name;
                $d['class_name'] = $s_homework->classes->class_name;
                $d['section_name'] = $s_homework->sections->section_name;
                $d['subject_name'] = $s_homework->subjects->subject_name;
                $d['marks'] =  (string) $s_homework->marks;
                $d['file'] = $s_homework->file;
                $d['description'] = $s_homework->description;
                $d['obtained_marks'] = $student_result != "" ? $student_result->marks : '';
                if ($student_result != "") {
                    if ($student_result->complete_status == "C") {
                        $d['status'] = 'Completed';
                    } else {
                        $d['status'] = 'incompleted';
                    }
                } else {
                    $d['status'] = 'incompleted';
                }
                $student_homeworks[] = $d;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = $student_homeworks;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function studentUploadHomework(Request $request)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'user_id' => "required|integer|min:0",
                'files' => "required",
                'homework_id' => "required",



            ]);

        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        try {
            $user = User::find($request->user_id);
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $data = [];
            foreach ($request->file('files') as $key => $file) {
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/homeworkcontent/', $fileName);
                $fileName = 'public/uploads/homeworkcontent/' . $fileName;
                $data[$key] = $fileName;
            }
            $all_filename = json_encode($data);
            $content = new AramiscUploadHomeworkContent();
            $content->file = $all_filename;
            $content->student_id = $student_detail->id;
            $content->homework_id = $request->homework_id;
            $content->school_id = 1;
            $content->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $result = $content->save();

            $homework_info = AramiscHomeWork::find($request->homework_id);
            $teacher_info = $teacher_info = User::find($homework_info->created_by);

            $notification = new AramiscNotification;
            $notification->user_id = $teacher_info->id;
            $notification->role_id = $teacher_info->role_id;
            $notification->date = date('Y-m-d');
            $notification->message = $student_detail->full_name . ' Submit Homework ';
            $notification->school_id = 1;
            $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $notification->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Homework Upload successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            }

        } catch (\Exception$e) {

            return redirect()->back();
        }
    }

    public function saas_studentUploadHomework(Request $request, $school_id)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'user_id' => "required|integer|min:0",
                'files' => "required",
                'homework_id' => "required",

            ]);

        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        try {
            $user = User::find($request->user_id);
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $data = [];
            foreach ($request->file('files') as $key => $file) {
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/homeworkcontent/', $fileName);
                $fileName = 'public/uploads/homeworkcontent/' . $fileName;
                $data[$key] = $fileName;
            }
            $all_filename = json_encode($data);
            $content = new AramiscUploadHomeworkContent();
            $content->file = $all_filename;
            $content->student_id = $student_detail->id;
            $content->homework_id = $request->homework_id;
            $content->school_id = 1;
            $content->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $result = $content->save();

            $homework_info = AramiscHomeWork::find($request->homework_id);
            $teacher_info = $teacher_info = User::find($homework_info->created_by);

            $notification = new AramiscNotification;
            $notification->user_id = $teacher_info->id;
            $notification->role_id = $teacher_info->role_id;
            $notification->date = date('Y-m-d');
            $notification->message = $student_detail->full_name . ' Submit Homework ';
            $notification->school_id = 1;
            $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $notification->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Homework Upload successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            }

        } catch (\Exception$e) {

            return redirect()->back();
        }
    }

    public function evaluationHomework(Request $request, $class_id, $section_id, $homework_id)
    {
        try {
            $homeworkDetail = AramiscHomework::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('id', $homework_id)
                ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->first();

            $d['id'] = $homeworkDetail->id;
            $d['homework_date'] = $homeworkDetail->homework_date;
            $d['submission_date'] = $homeworkDetail->submission_date;
            $d['evaluation_date'] = $homeworkDetail->evaluation_date;
            $d['created_by'] = $homeworkDetail->users->full_name;
            $d['class'] = $homeworkDetail->class->class_name;
            $d['section'] = $homeworkDetail->section->section_name;
            $d['class_id'] = $homeworkDetail->class->id;
            $d['section_id'] = $homeworkDetail->section->id;
            $d['subject_name'] = $homeworkDetail->subjects->subject_name;
            $d['marks'] = $homeworkDetail->marks;
            $d['file'] = $homeworkDetail->file;
            $d['description'] = $homeworkDetail->description;

            $homework[] = $d;

            $studentIds = AramiscStudentReportController::classSectionStudent($request->merge([
                'class'=>$class_id,
                'section'=>$section_id,
            ]));

            $students = AramiscStudent::whereIn('id', $studentIds)->where('school_id', auth()->user()->school_id)->get();

            $homeworkSubmit = AramiscHomeworkStudent::whereIn('student_id', $studentIds)->where('homework_id', $homework_id)->get();
            $student_homeworks = [];

            foreach ($students as $student) {

                @$uploadedContent = AramiscHomework::uploadedContent(@$student->id, $homeworkDetail->id);

                $file_paths = [];
                foreach ($uploadedContent as $key => $files_row) {
                    $only_files = json_decode($files_row->file);
                    foreach ($only_files as $second_key => $upload_file_path) {
                        $file_paths[] = $upload_file_path;
                    }
                }

                $files_ext = [];
                foreach ($file_paths as $key => $file) {
                    $files_ext[] = pathinfo($file, PATHINFO_EXTENSION);
                }

                $student_result = AramiscHomework::evaluationHomework($student->id, $homeworkDetail->id);

                $d_h_s['id'] = $student->id;
                $d_h_s['student_id'] = $student->id;
                $d_h_s['student_name'] = $student->full_name;
                $d_h_s['admission_no'] = $student->admission_no;
                $d_h_s['homework_id'] = $homeworkDetail->id;
                $d_h_s['marks'] = $student_result != '' ? $student_result->marks : null;
                $d_h_s['teacher_comments'] = $student_result != '' ? $student_result->teacher_comments : 'NG';
                $d_h_s['complete_status'] = $student_result != '' ? $student_result->complete_status : 'NC';
                $d_h_s['evalutaion_status'] = $student_result != '' ? 'Yes' : 'No';
                $d_h_s['file'] = $file_paths;

                $student_homeworks[] = $d_h_s;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['homeworkDetails'] = $homework;
                $data['student_homeworks'] = $student_homeworks;
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error .', $e->getMessage());
        }
    }

    public function saas_evaluationHomework(Request $request, $school_id, $class_id, $section_id, $homework_id)
    {
        try {
            $homeworkDetail = AramiscHomework::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('id', $homework_id)
                ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->first();

            $d['id'] = $homeworkDetail->id;
            $d['homework_date'] = $homeworkDetail->homework_date;
            $d['submission_date'] = $homeworkDetail->submission_date;
            $d['evaluation_date'] = $homeworkDetail->evaluation_date;
            $d['created_by'] = $homeworkDetail->users->full_name;
            $d['class'] = $homeworkDetail->class->class_name;
            $d['section'] = $homeworkDetail->section->section_name;
            $d['subject_name'] = $homeworkDetail->subjects->subject_name;
            $d['marks'] = $homeworkDetail->marks;
            $d['file'] = $homeworkDetail->file;
            $d['description'] = $homeworkDetail->description;

            $homework[] = $d;

            $studentIds = AramiscStudentReportController::classSectionStudent($request->merge([
                'class'=>$class_id,
                'section'=>$section_id,
            ]));

            $students = AramiscStudent::whereIn('id', $studentIds)->where('school_id', auth()->user()->school_id)->get();

            $homeworkSubmit = AramiscHomeworkStudent::whereIn('student_id', $studentIds)->where('homework_id', $homework_id)->get();
            $student_homeworks = [];

            foreach ($students as $student) {

                @$uploadedContent = AramiscHomework::uploadedContent(@$student->id, $homeworkDetail->id);

                $file_paths = [];
                foreach ($uploadedContent as $key => $files_row) {
                    $only_files = json_decode($files_row->file);
                    foreach ($only_files as $second_key => $upload_file_path) {
                        $file_paths[] = $upload_file_path;
                    }
                }

                $files_ext = [];
                foreach ($file_paths as $key => $file) {
                    $files_ext[] = pathinfo($file, PATHINFO_EXTENSION);
                }

                $student_result = AramiscHomework::evaluationHomework($student->id, $homeworkDetail->id);

                $d_h_s['id'] = $student->id;
                $d_h_s['student_id'] = $student->id;
                $d_h_s['student_name'] = $student->full_name;
                $d_h_s['admission_no'] = $student->admission_no;
                $d_h_s['homework_id'] = $homeworkDetail->id;
                $d_h_s['marks'] = $student_result != '' ? $student_result->marks : null;
                $d_h_s['teacher_comments'] = $student_result != '' ? $student_result->teacher_comments : 'NG';
                $d_h_s['complete_status'] = $student_result != '' ? $student_result->complete_status : 'NC';
                $d_h_s['evalutaion_status'] = $student_result != '' ? 'Yes' : 'No';
                $d_h_s['file'] = $file_paths;

                $student_homeworks[] = $d_h_s;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['homeworkDetails'] = $homework;
                $data['student_homeworks'] = $student_homeworks;
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error .', $e->getMessage());
        }
    }
    public function HomeWorkNotification(Request $request)
    {
        try {
            $student_ids = StudentRecord::when($request->class_id, function ($query) use ($request) {
                $query->where('class_id', $request->id);
            })
            ->when($request->section, function ($query) use ($request) {
                $query->where('section_id', $request->section_id);
            })
            ->where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)
            ->pluck('student_id')->unique();
            $students = AramiscStudent::whereIn('id', $student_ids)->get();

            foreach ($students as $student) {
                $user = User::where('id', $student->id)->first();

                // SEND PUSHUP NOTIFICATION
                if ($user->device_token != ''){
                    $firebaseService = new FirebasePushService();
                    $firebaseService->sendToToken($user->device_token, $_REQUEST['title'], $_REQUEST['body']);
                }

                if ($user->notificationToken != '') {

                    //echo 'Aramisc Edu';
//                    define('API_ACCESS_KEY', 'AAAAFyQhhks:APA91bGJqDLCpuPgjodspo7Wvp1S4yl3jYwzzSxet_sYQH9Q6t13CtdB_EiwD6xlVhNBa6RcHQbBKCHJ2vE452bMAbmdABsdPriJy_Pr9YvaM90yEeOCQ6VF7JEQ501Prhnu_2bGCPNp');
                    define('API_ACCESS_KEY', Cache::get('firebase_access_token'));
                    //   $registrationIds = ;
                    #prep the bundle
                    $msg = array(
                        'body'     => $_REQUEST['body'],
                        'title'    => $_REQUEST['title'],

                    );
                    $fields = array(
                        'to'        => $user->notificationToken,
                        'notification'    => $msg
                    );


                    $headers = array(
                        'Authorization: key=' . API_ACCESS_KEY,
                        'Content-Type: application/json'
                    );
                    #Send Reponse To FireBase Server
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $result = curl_exec($ch);
                    echo $result;
                    curl_close($ch);
                }
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendResponse($data, null);
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $e = '';
                    return ApiBaseMethod::sendError($e);
                }
            }
        } catch (\Exception $e) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError($e);
            }
        }
    }

}
