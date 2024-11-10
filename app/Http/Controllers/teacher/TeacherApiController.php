<?php

namespace App\Http\Controllers\teacher;

use App\Role;
use App\AramiscClass;
use App\AramiscGeneralSettings;
use App\AramiscStaff;
use App\AramiscStudent;
use App\AramiscWeekend;
use App\YearCheck;
use App\AramiscHomework;
use App\AramiscClassTime;
use App\ApiBaseMethod;
use App\AramiscLeaveRequest;
use App\AramiscNotification;
use App\AramiscAssignSubject;
use App\AramiscStaffAttendence;
use Illuminate\Http\Request;
use App\AramiscTeacherUploadContent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\RolePermission\Entities\AramiscRole;

class TeacherApiController extends Controller
{

    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

    public function viewTeacherRoutine()
    {
        try {
            $user = Auth::user();

            $class_times = AramiscClassTime::all();
            $teacher_id = $user->staff->id;

            $aramisc_weekends = AramiscWeekend::orderBy('order', 'ASC')->where('active_status', 1)->get();
            $teachers = AramiscStaff::select('id', 'full_name')->where('active_status', 1)->get();

            return view('backEnd.teacherPanel.view_class_routine', compact('class_times', 'teacher_id', 'aramisc_weekends', 'teachers'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function searchStudent(Request $request)
    {
        try {
            $class_id = $request->class;
            $section_id = $request->section;
            $name = $request->name;
            $roll_no = $request->roll_no;
            $students = '';
            $msg = '';
            if (!empty($request->class) && !empty($request->section)) {
                $students = DB::table('aramisc_students')
                    ->select('student_photo', 'full_name', 'roll_no', 'class_name', 'section_name', 'user_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')
                    ->where('aramisc_students.class_id', $request->class)
                    ->where('aramisc_students.section_id', $request->section)
                    ->get();
                $msg = "Student Found";
            } elseif (!empty($request->class)) {
                $students = DB::table('aramisc_students')
                    ->select('student_photo', 'full_name', 'roll_no', 'class_name', 'section_name', 'user_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')
                    ->where('aramisc_students.class_id', $class_id)
                    // ->where('section_id',$section_id)
                    ->get();
                $msg = "Student Found";
            } elseif ($request->name != "") {
                $students = DB::table('aramisc_students')
                    ->select('student_photo', 'full_name', 'roll_no', 'class_name', 'section_name', 'user_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')
                    ->where('full_name', 'like', '%' . $request->name . '%')
                    ->first();
                $msg = "Student Found";
            } elseif ($request->roll_no != "") {
                $students = DB::table('aramisc_students')
                    ->select('student_photo', 'full_name', 'roll_no', 'class_name', 'section_name', 'user_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')
                    ->where('roll_no', 'like', '%' . $request->roll_no . '%')
                    ->first();
                $msg = "Student Found";
            } else {

                $msg = "Student Not Found";
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students;

                return ApiBaseMethod::sendResponse($data, $msg);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function myRoutine(Request $request, $id)
    {
        try {
            $teacher = DB::table('aramisc_staffs')
                ->where('user_id', '=', $id)
                ->first();
            $teacher_id = $teacher->id;
            $aramisc_weekends = AramiscWeekend::orderBy('order', 'ASC')->where('active_status', 1)->get();
            $class_times = AramiscClassTime::where('type', 'class')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $weekenD = AramiscWeekend::all();
                foreach ($weekenD as $row) {
                    $data[$row->name] = DB::table('aramisc_class_routine_updates')
                        ->select('class_id', 'class_name', 'section_id', 'section_name', 'aramisc_class_times.period', 'aramisc_class_times.start_time', 'aramisc_class_times.end_time', 'aramisc_subjects.subject_name', 'aramisc_class_rooms.room_no')
                        ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_class_routine_updates.class_id')
                        ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_class_routine_updates.section_id')
                        ->join('aramisc_class_times', 'aramisc_class_times.id', '=', 'aramisc_class_routine_updates.class_period_id')
                        ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_class_routine_updates.subject_id')
                        ->join('aramisc_class_rooms', 'aramisc_class_rooms.id', '=', 'aramisc_class_routine_updates.room_id')

                        ->where([
                            ['aramisc_class_routine_updates.teacher_id', $teacher_id], ['aramisc_class_routine_updates.day', $row->id],
                        ])->get();
                }

                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function sectionRoutine(Request $request, $id, $class, $section)
    {
        try {
            $teacher = DB::table('aramisc_staffs')
                ->where('user_id', '=', $id)
                ->first();
            $teacher_id = $teacher->id;
            $aramisc_weekends = AramiscWeekend::orderBy('order', 'ASC')->where('active_status', 1)->get();
            $class_times = AramiscClassTime::where('type', 'class')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $weekenD = AramiscWeekend::all();
                foreach ($weekenD as $row) {
                    $data[$row->name] = DB::table('aramisc_class_routine_updates')
                        ->select('aramisc_class_times.period', 'aramisc_class_times.start_time', 'aramisc_class_times.end_time', 'aramisc_subjects.subject_name', 'aramisc_class_rooms.room_no')
                        ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_class_routine_updates.class_id')
                        ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_class_routine_updates.section_id')
                        ->join('aramisc_class_times', 'aramisc_class_times.id', '=', 'aramisc_class_routine_updates.class_period_id')
                        ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_class_routine_updates.subject_id')
                        ->join('aramisc_class_rooms', 'aramisc_class_rooms.id', '=', 'aramisc_class_routine_updates.room_id')

                        ->where([
                            ['aramisc_class_routine_updates.teacher_id', $teacher_id],
                            ['aramisc_class_routine_updates.class_id', $class],
                            ['aramisc_class_routine_updates.section_id', $section],
                            ['aramisc_class_routine_updates.day', $row->id],
                        ])->get();
                }
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function classSection(Request $request, $id)
    {
        try {
            $teacher = DB::table('aramisc_staffs')
                ->where('user_id', '=', $id)
                ->first();
            $teacher_id = $teacher->id;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $teacher_classes = DB::table('aramisc_assign_subjects')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                    ->distinct('class_id')

                    ->where('teacher_id', $teacher_id)
                    ->get();

                // return  $teacher_classes;
                foreach ($teacher_classes as $class) {
                    $data[$class->class_name] = DB::table('aramisc_assign_subjects')
                        ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
                        ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                        ->select('section_name', 'subject_name')
                        ->distinct('section_id')
                        ->where([
                            ['aramisc_assign_subjects.class_id', $class->id],
                            ['aramisc_assign_subjects.teacher_id', $teacher_id],
                        ])->get();
                }

                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function teacherClassList(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => "required",

        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $teacher = DB::table('aramisc_staffs')
                ->where('user_id', '=', $request->id)
                ->first();
            $role_id = $teacher->role_id;
            $teacher_id = $teacher->id;
            if ($role_id != 1) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $teacher_classes = DB::table('aramisc_assign_subjects')
                        ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                        ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                        ->distinct('class_id')
                        ->select('class_id', 'class_name')
                        ->where('teacher_id', $teacher_id)
                        ->get();
                    $data['teacher_classes'] = $teacher_classes->toArray();
                    return ApiBaseMethod::sendResponse($data, null);
                }
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $teacher_classes = DB::table('aramisc_classes')
                        ->select('id as class_id', 'class_name')
                        ->get();
                    $data['teacher_classes'] = $teacher_classes->toArray();
                    return ApiBaseMethod::sendResponse($data, null);
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function teacherSectionList(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => "required",
            'class' => "required",

        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $teacher = DB::table('aramisc_staffs')
                ->where('user_id', '=', $request->id)
                ->first();
            $teacher_id = $teacher->id;
            $role_id = $teacher->role_id;
            if ($role_id != 1) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $teacher_classes = DB::table('aramisc_assign_subjects')
                        ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                        ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                        ->distinct('section_id')
                        ->select('section_id', 'section_name')
                        ->where('teacher_id', $teacher_id)
                        ->where('class_id', $request->class)
                        ->get();
                    $data['teacher_sections'] = $teacher_classes->toArray();
                    return ApiBaseMethod::sendResponse($data, null);
                }
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $teacher_classes = DB::table('aramisc_class_sections')
                        ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_class_sections.class_id')
                        ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_class_sections.section_id')
                        ->where('aramisc_class_sections.class_id', $request->class)
                        ->select('section_id', 'section_name')
                        ->get();
                    $data['teacher_sections'] = $teacher_classes->toArray();
                    return ApiBaseMethod::sendResponse($data, null);
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    //Some Changes
    public function subjectsName(Request $request, $id)
    {
        try {
            $teacher = DB::table('aramisc_staffs')
                ->where('user_id', '=', $id)
                ->first();
            $teacher_id = $teacher->id;

            $subjectsName = DB::table('aramisc_assign_subjects')
                ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
                ->select('subject_id', 'subject_name', 'subject_code', 'subject_type')
                ->where('aramisc_assign_subjects.active_status', 1)
                ->where('teacher_id', $teacher_id)
                ->distinct('subject_id')
                ->get();
            $subject_type = 'T=Theory, P=Practical';
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['subjectsName'] = $subjectsName->toArray();
                $data['subject_type'] = $subject_type;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function addHomework(Request $request)
    {
        
        $request->validate([
            'class' => "required",
            'section' => "required",
            'subject' => "required",
            'assign_date' => "required",
            'submission_date' => "required",
            'description' => "required",
            'marks' => "required"
        ]);

        try {
            $fileName = "";
            if ($request->file('homework_file') != "") {

                $file = $request->file('homework_file');
                $fileName = $request->teacher_id . time() . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/homework/', $fileName);
                $fileName = 'public/uploads/homework/' . $fileName;
            }
            $homeworks = new AramiscHomework;
            $homeworks->class_id = $request->class;
            $homeworks->section_id = $request->section;
            $homeworks->subject_id = $request->subject;
            $homeworks->marks = $request->marks;
            $homeworks->created_by = $request->teacher_id;
            $homeworks->homework_date = $request->assign_date;
            $homeworks->submission_date = $request->submission_date;
            $homeworks->description = $request->description;
            $homeworks->academic_id = getAcademicId();
            if ($fileName != "") {
                $homeworks->file = $fileName;
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $results = $homeworks->save();

                return ApiBaseMethod::sendResponse($results, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function homeworkList2(Request $request, $id)
    {
        try {
            $teacher = DB::table('aramisc_staffs')
                ->where('user_id', '=', $id)
                ->first();
            $teacher_id = $teacher->id;
            $homeworkLists = AramiscHomework::where('aramisc_homeworks.created_by', '=', $teacher_id)
                ->join('aramisc_classes', 'aramisc_homeworks.class_id', '=', 'aramisc_classes.id')
                ->join('aramisc_sections', 'aramisc_homeworks.section_id', '=', 'aramisc_sections.id')
                ->join('aramisc_subjects', 'aramisc_homeworks.subject_id', '=', 'aramisc_subjects.id')
                ->select('homework_date', 'submission_date', 'evaluation_date', 'file', 'aramisc_homeworks.marks', 'description', 'subject_name', 'class_name', 'section_name')
                ->get();
            $classes = AramiscClass::where('active_status', '=', '1')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                return ApiBaseMethod::sendResponse($homeworkLists, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function homeworkList(Request $request, $id)
    {
        try {
            $teacher = AramiscStaff::where('user_id', '=', $id)->first();
            $teacher_id = $teacher->id;
            $subject_list = AramiscAssignSubject::where('teacher_id', '=', $teacher_id)->get();
            $i = 0;
            foreach ($subject_list as $subject) {
                $homework_subject_list[$subject->subject->subject_name] = $subject->subject->subject_name;
                $allList[$subject->subject->subject_name] = DB::table('aramisc_homeworks')
                    ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                    ->where('aramisc_homeworks.created_by', $teacher_id)
                    ->where('subject_id', $subject->subject_id)->get()->toArray();;
            }

            foreach ($allList as $single) {
                foreach ($single as $singleHw) {
                    $std_homework = DB::table('aramisc_homework_students')
                        ->select('homework_id', 'complete_status')
                        ->where('homework_id', '=', $singleHw->id)
                        ->where('complete_status', 'C')
                        ->first();
                    $d['homework_id'] = $singleHw->id;
                    $d['description'] = $singleHw->description;
                    $d['subject_name'] = $singleHw->subject_name;
                    $d['homework_date'] = $singleHw->homework_date;
                    $d['submission_date'] = $singleHw->submission_date;
                    $d['evaluation_date'] = $singleHw->evaluation_date;
                    $d['file'] = $singleHw->file;
                    $d['marks'] = $singleHw->marks;

                    if (!empty($std_homework)) {
                        $d['status'] = 'C';
                    } else {
                        $d['status'] = 'I';
                    }
                    $kijanidibo[] = $d;
                }
            }
            $data = [];
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $data = $kijanidibo;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function teacherMyAttendanceSearchAPI(Request $request, $id = null)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'month' => "required",
            'year' => "required",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $teacher = AramiscStaff::where('user_id', $id)->first();
            $year = $request->year;
            $month = $request->month;
            if ($month < 10) {
                $month = '0' . $month;
            }
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $request->year);
            $days2 = cal_days_in_month(CAL_GREGORIAN, $month - 1, $request->year);
            $previous_month = $month - 1;
            $previous_date = $year . '-' . $previous_month . '-' . $days2;
            $previousMonthDetails['date'] = $previous_date;
            $previousMonthDetails['day'] = $days2;
            $previousMonthDetails['week_name'] = date('D', strtotime($previous_date));
            $attendances = AramiscStaffAttendence::where('student_id', $teacher->id)
                ->where('attendance_date', 'like', '%' . $request->year . '-' . $month . '%')
                ->select('attendance_type', 'attendance_date')
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['attendances'] = $attendances;
                $data['previousMonthDetails'] = $previousMonthDetails;
                $data['days'] = $days;
                $data['year'] = $year;
                $data['month'] = $month;
                $data['current_day'] = $current_day;
                $data['status'] = 'Present: P, Late: L, Absent: A, Holiday: H, Half Day: F';
                return ApiBaseMethod::sendResponse($data, null);
            }
            //Test
            //return view('backEnd.studentPanel.student_attendance', compact('attendances', 'days', 'year', 'month', 'current_day'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function applyLeave(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'teacher_id' => "required",
                'reason' => "required",
                'attach_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        try {
            $fileName = "";
            if ($request->file('attach_file') != "") {
                $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                $file = $request->file('attach_file');
                $fileSize =  filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if($fileSizeKb >= $maxFileSize){
                    Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }

            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $request->input('teacher_id');
            $apply_leave->role_id = 4;
            $apply_leave->apply_date = date('Y-m-d');
            $apply_leave->leave_define_id = $request->input('leave_type');
            $apply_leave->leave_from = $request->input('leave_from');
            $apply_leave->leave_to = $request->input('leave_to');
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->input('reason');
            $apply_leave->academic_id = getAcademicId();
            if ($fileName != "") {
                $apply_leave->file = $fileName;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $result = $apply_leave->save();

                return ApiBaseMethod::sendResponse($result, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function staffLeaveList(Request $request, $id)
    {
        try {
            $teacher = AramiscStaff::where('user_id', '=', $id)->first();
            $teacher_id = $teacher->id;

            $leave_list = AramiscLeaveRequest::where('staff_id', '=', $teacher_id)
                ->join('aramisc_leave_defines', 'aramisc_leave_defines.id', '=', 'aramisc_leave_requests.leave_define_id')
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
                ->get();
            $status = 'P for Pending, A for Approve, R for reject';
            $data = [];
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['leave_list'] = $leave_list->toArray();
                $data['status'] = $status;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function leaveTypeList(Request $request)
    {

        try {
            $leave_type = DB::table('aramisc_leave_defines')
                ->where('role_id', 4)
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
                ->where('aramisc_leave_defines.active_status', 1)
                ->select('aramisc_leave_types.id', 'type', 'total_days')
                ->distinct('aramisc_leave_defines.type_id')
                ->get();
            //return $leave_type;
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($leave_type, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function uploadContent(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'content_title' => "required",
                'content_type' => "required",
                'upload_date' => "required",
                'description' => "required",
                'attach_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",

            ]);
        }
        //as assignment, st study material, sy sullabus, ot others download

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        if (empty($request->input('available_for'))) {

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', 'Content Receiver not selected');
            }
        }
        try {
            $fileName = "";
            if ($request->file('attach_file') != "") {
                $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                $file = $request->file('attach_file');
                $fileSize =  filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if($fileSizeKb >= $maxFileSize){
                    Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/upload_contents/', $fileName);
                $fileName = 'public/uploads/upload_contents/' . $fileName;
            }

            $uploadContents = new AramiscTeacherUploadContent();
            $uploadContents->content_title = $request->input('content_title');
            $uploadContents->content_type = $request->input('content_type');



            if ($request->input('available_for') == 'admin') {
                $uploadContents->available_for_admin = 1;
            } elseif ($request->input('available_for') == 'student') {
                if (!empty($request->input('all_classes'))) {
                    $uploadContents->available_for_all_classes = 1;
                } else {
                    $uploadContents->class = $request->input('class');
                    $uploadContents->section = $request->input('section');
                }
            }

            $uploadContents->upload_date = date('Y-m-d', strtotime($request->input('upload_date')));
            $uploadContents->description = $request->input('description');
            $uploadContents->upload_file = $fileName;
            $uploadContents->created_by = $request->input('created_by');
            $uploadContents->academic_id = getAcademicId();
            $results = $uploadContents->save();


            if ($request->input('content_type') == 'as') {
                $purpose = 'assignment';
            } elseif ($request->input('content_type') == 'st') {
                $purpose = 'Study Material';
            } elseif ($request->input('content_type') == 'sy') {
                $purpose = 'Syllabus';
            } elseif ($request->input('content_type') == 'ot') {
                $purpose = 'Others Download';
            }


            // foreach ($request->input('available_for') as $value) {
            if ($request->input('available_for') == 'admin') {
                $roles = AramiscRole::where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 9)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

                foreach ($roles as $role) {
                    $staffs = AramiscStaff::where('role_id', $role->id)->get();
                    foreach ($staffs as $staff) {
                        $notification = new AramiscNotification;
                        $notification->user_id = $staff->user_id;
                        $notification->role_id = $role->id;
                        $notification->date = date('Y-m-d');
                        $notification->message = $purpose . ' updated';
                        $notification->school_id = Auth::user()->school_id;
                        $notification->academic_id = getAcademicId();
                        $notification->save();
                    }
                }
            }
            if ($request->input('available_for') == 'student') {
                if (!empty($request->input('all_classes'))) {
                    $students = AramiscStudent::select('id')->get();
                    foreach ($students as $student) {
                        $notification = new AramiscNotification;
                        $notification->user_id = $student->user_id;
                        $notification->role_id = 2;
                        $notification->date = date('Y-m-d');
                        $notification->message = $purpose . ' updated';
                        $notification->school_id = Auth::user()->school_id;
                        $notification->academic_id = getAcademicId();
                        $notification->save();
                    }
                } else {
                    $students = AramiscStudent::select('id')->where('class_id', $request->input('class'))->where('section_id', $request->input('section'))->get();
                    foreach ($students as $student) {
                        $notification = new AramiscNotification;
                        $notification->user_id = $student->user_id;
                        $notification->role_id = 2;
                        $notification->date = date('Y-m-d');
                        $notification->message = $purpose . ' updated';
                        $notification->school_id = Auth::user()->school_id;
                        $notification->academic_id = getAcademicId();
                        $notification->save();
                    }
                }
            }
            // }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $data = '';

                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function contentList(Request $request)
    {

        try {
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
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function deleteContent(Request $request, $id)
    {
        try {
            $content = DB::table('aramisc_teacher_upload_contents')->where('id', $id)->delete();
            //$res=User::where('id',$id)->delete();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
