<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\User;
use App\SmClass;
use App\SmParent;
use App\SmSection;
use App\SmStudent;
use App\SmSubject;
use App\SmBaseSetup;
use App\SmClassSection;
use App\SmNotification;
use App\SmAssignSubject;
use App\SmStudentCategory;
use App\SmStudentAttendance;
use App\SmSubjectAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\Traits\NotificationSend;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\FlutterAppNotification;
use Modules\University\Entities\UnSubjectAssignStudent;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;
use App\Http\Requests\Admin\StudentInfo\StudentSubjectWiseAttendanceStoreRequest;
use App\Http\Requests\Admin\StudentInfo\StudentSubjectWiseAttendancSearchRequest;
use App\Http\Requests\Admin\StudentInfo\StudentSubjectWiseAttendanceSearchRequest;
use App\Http\Requests\Admin\StudentInfo\subjectAttendanceAverageReportSearchRequest;

class SmSubjectAttendanceController extends Controller
{
    use NotificationSend;
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index(Request $request)
    {
        try {
            $classes = SmClass::get();
            return view('backEnd.studentInformation.subject_aramiscAttendance', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function search(StudentSubjectWiseAttendancSearchRequest $request)
    {
        try {
            $data = [];
            $input['aramiscAttendance_date'] = $request->aramiscAttendance_date;
            $input['class'] = $request->class_id;
            $input['subject'] = $request->subject_id;
            $input['section'] = $request->section_id;

            $classes = SmClass::get();
            $sections = SmClassSection::with('sectionName')->where('class_id', $input['class'])->get();
            $subjects = SmAssignSubject::with('subject')->where('class_id', $input['class'])->where('section_id', $input['section'])
                ->get();

            if(moduleStatusCheck('University') == false){
                request()->merge([
                    'class' => $request->class_id,
                    'section' => $request->section_id,
                    'subject' =>$request->subject_id
                ]);
            }

            $students = StudentRecord::with('studentDetail', 'studentDetail.DateSubjectWiseAttendances')
                ->whereHas('studentDetail', function ($q) {
                    $q->where('active_status', 1);
                })
                ->where('class_id', $input['class'])
                ->where('section_id', $input['section'])
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if (moduleStatusCheck('University')) {
                $data['un_semester_label_id'] = $request->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->searchInfo($request);
                $data += $interface->oldValueSelected($request);
                $assigned_students =  UnSubjectAssignStudent::where('un_subject_id', $request->un_subject_id)
                    ->where('un_semester_label_id', $request->un_semester_label_id)
                    ->get('student_record_id')->toArray();
                $students =  StudentRecord::whereIn('id', $assigned_students)->get();
            }

            if ($students->isEmpty()) {
                Toastr::error('No Result Found', 'Failed');
                return redirect('subject-wise-aramiscAttendance');
            }

            $aramiscAttendance_type = $students[0]['studentDetail']['DateSubjectWiseAttendances'] != null  ? $students[0]['studentDetail']['DateSubjectWiseAttendances']['aramiscAttendance_type'] : '';

            if (!moduleStatusCheck('University')) {
                $search_info['class_name'] = SmClass::find($request->class_id)->class_name;
                $search_info['section_name'] = SmSection::find($request->section_id)->section_name;
                $search_info['subject_name'] = SmSubject::find($request->subject_id)->subject_name;
            }

            $search_info['date'] = $input['aramiscAttendance_date'];

            if (generalSetting()->aramiscAttendance_layout == 1) {
                return view('backEnd.studentInformation.subject_aramiscAttendance_list', compact('classes', 'subjects', 'sections', 'students', 'aramiscAttendance_type', 'search_info', 'input'))->with($data);
            } else {
                return view('backEnd.studentInformation.subject_aramiscAttendance_list2', compact('classes', 'subjects', 'sections', 'students', 'aramiscAttendance_type', 'search_info', 'input'))->with($data);
            }
        } catch (\Exception $e) {;
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeAttendance(StudentSubjectWiseAttendanceStoreRequest $request)
    {
        try {
            foreach ($request->aramiscAttendance as $record_id => $student) {
                $aramiscAttendance = SmSubjectAttendance::where('student_id', gv($student, 'student'))
                    ->where('subject_id', $request->subject)
                    ->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))
                    ->where('class_id', gv($student, 'class'))
                    ->where('section_id', gv($student, 'section'))
                    ->where('student_record_id', $record_id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();

                if ($aramiscAttendance != "") {
                    $aramiscAttendance->delete();
                }

                $aramiscAttendance = new SmSubjectAttendance();
                $aramiscAttendance->student_record_id = $record_id;
                $aramiscAttendance->subject_id = $request->subject;
                $aramiscAttendance->student_id = gv($student, 'student');
                $aramiscAttendance->class_id = gv($student, 'class');
                $aramiscAttendance->section_id = gv($student, 'section');
                $aramiscAttendance->aramiscAttendance_type = gv($student, 'aramiscAttendance_type');
                $aramiscAttendance->notes = gv($student, 'note');
                $aramiscAttendance->school_id = Auth::user()->school_id;
                $aramiscAttendance->academic_id = getAcademicId();
                $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->date));
                $r = $aramiscAttendance->save();

                $student_user_id = SmStudent::find($aramiscAttendance->student_id)->user_id;
                $data['class_id'] = $aramiscAttendance->class_id;
                $data['section_id'] = $aramiscAttendance->section_id;
                $data['subject'] = $aramiscAttendance->subject->subject_name;
                $data['aramiscAttendance_type'] = $aramiscAttendance->aramiscAttendance_type;
                $this->sent_notifications('Subject_Wise_Attendance', [$student_user_id], $data, ['Student', 'Parent']);

                // $messege = "";
                // $date = dateConvert($aramiscAttendance->aramiscAttendance_date);

                // if (gv($student, 'student')) {

                //     $student = SmStudent::find(gv($student, 'student'));
                //     $subject = SmSubject::find($request->subject);
                //     $subject_name = $subject->subject_name;
                //     if ($student) {
                //         if ($aramiscAttendance->aramiscAttendance_type == "P") {
                //             $messege = app('translator')->get('student.Your_teacher_has_marked_you_present_in_the_aramiscAttendance_on_subject', ['date' => $date, 'subject_name' => $subject_name]);
                //         } elseif ($aramiscAttendance->aramiscAttendance_type == "L") {
                //             $messege = app('translator')->get('student.Your_teacher_has_marked_you_late_in_the_aramiscAttendance_on_subject', ['date' => $date, 'subject_name' => $subject_name]);
                //         } elseif ($aramiscAttendance->aramiscAttendance_type == "A") {
                //             $messege = app('translator')->get('student.Your_teacher_has_marked_you_absent_in_the_aramiscAttendance_on_subject', ['date' => $date, 'subject_name' => $subject_name]);
                //         } elseif ($aramiscAttendance->aramiscAttendance_type == "F") {
                //             $messege = app('translator')->get('student.Your_teacher_has_marked_you_halfday_in_the_aramiscAttendance_on_subject', ['date' => $date, 'subject_name' => $subject_name]);
                //         }

                //         $notification = new SmNotification();
                //         $notification->user_id = $student->user_id;
                //         $notification->role_id = 2;
                //         $notification->date = date('Y-m-d');
                //         $notification->message = $messege;
                //         $notification->school_id = Auth::user()->school_id;
                //         $notification->academic_id = getAcademicId();
                //         $notification->save();
                //         try {
                //             if ($student->user) {
                //                 $title = app('translator')->get('student.aramiscAttendance_notication');
                //                 Notification::send($student->user, new FlutterAppNotification($notification, $title));
                //             }
                //         } catch (\Exception $e) {

                //             Log::info($e->getMessage());
                //         }

                //         // for parent user
                //         $parent = SmParent::find($student->parent_id);
                //         if ($parent) {
                //             if ($aramiscAttendance->aramiscAttendance_type == "P") {
                //                 $messege = app('translator')->get('student.Your_child_is_marked_present_in_the_aramiscAttendance_on_subject', ['date' => $date, 'student_name' => $student->full_name . "'s", 'subject_name' => $subject_name]);
                //             } elseif ($aramiscAttendance->aramiscAttendance_type == "L") {
                //                 $messege = app('translator')->get('student.Your_child_is_marked_late_in_the_aramiscAttendance_on_subject', ['date' => $date, 'student_name' => $student->full_name . "'s", 'subject_name' => $subject_name]);
                //             } elseif ($aramiscAttendance->aramiscAttendance_type == "A") {
                //                 $messege = app('translator')->get('student.Your_child_is_marked_absent_in_the_aramiscAttendance_on_subject', ['date' => $date, 'student_name' => $student->full_name . "'s", 'subject_name' => $subject_name]);
                //             } elseif ($aramiscAttendance->aramiscAttendance_type == "F") {
                //                 $messege = app('translator')->get('student.Your_child_is_marked_halfday_in_the_aramiscAttendance_on_subject', ['date' => $date, 'student_name' => $student->full_name . "'s", 'subject_name' => $subject_name]);
                //             }

                //             $notification = new SmNotification();
                //             $notification->user_id = $parent->user_id;
                //             $notification->role_id = 3;
                //             $notification->date = date('Y-m-d');
                //             $notification->message = $messege;
                //             $notification->school_id = Auth::user()->school_id;
                //             $notification->academic_id = getAcademicId();
                //             $notification->save();

                //             try {
                //                 $user = User::find($notification->user_id);
                //                 if ($user) {
                //                     $title = app('translator')->get('student.aramiscAttendance_notication');
                //                     Notification::send($user, new FlutterAppNotification($notification, $title));
                //                 }
                //             } catch (\Exception $e) {

                //                 Log::info($e->getMessage());
                //             }
                //         }
                //     }
                // }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('subject-wise-aramiscAttendance');
        } catch (\Exception $e) {
         
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeAttendanceSecond(Request $request)
    {

        try {
            foreach ($request->aramiscAttendance as $record_id => $student) {

                $aramiscAttendance_type = gv($student, 'aramiscAttendance_type') ? gv($student, 'aramiscAttendance_type') : 'A';
                $aramiscAttendance = SmSubjectAttendance::where('student_id', gv($student, 'student'))
                    ->where('subject_id', $request->subject)
                    ->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))
                    ->where('class_id', gv($student, 'class'))
                    ->where('section_id', gv($student, 'section'))
                    ->where('student_record_id', $record_id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                if ($aramiscAttendance != "") {
                    $aramiscAttendance->delete();
                }

                $aramiscAttendance = new SmSubjectAttendance();
                $aramiscAttendance->student_record_id = $record_id;
                $aramiscAttendance->subject_id = $request->subject;
                $aramiscAttendance->student_id = gv($student, 'student');
                $aramiscAttendance->class_id = gv($student, 'class');
                $aramiscAttendance->section_id = gv($student, 'section');
                $aramiscAttendance->aramiscAttendance_type = $aramiscAttendance_type;
                $aramiscAttendance->notes = gv($student, 'note');
                $aramiscAttendance->school_id = Auth::user()->school_id;
                $aramiscAttendance->academic_id = getAcademicId();
                $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->aramiscAttendance_date));
                $r = $aramiscAttendance->save();
            }
            return response()->json('success');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function subjectHolidayStore(Request $request)
    {
        $active_students = SmStudent::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get()->pluck('id')->toArray();
        $students = StudentRecord::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->whereIn('student_id', $active_students)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

        if ($students->isEmpty()) {
            Toastr::error('No Result Found', 'Failed');
            return redirect('subject-wise-aramiscAttendance');
        }
        if ($request->purpose == "mark") {
            foreach ($students as $record) {
                $aramiscAttendance = SmSubjectAttendance::where('student_id', $record->student_id)
                    ->where('subject_id', $request->subject_id)
                    ->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))
                    ->where('class_id', $request->class_id)->where('section_id', $request->section_id)
                    ->where('student_record_id', $record->id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                if (!empty($aramiscAttendance)) {
                    $aramiscAttendance->delete();
                    $aramiscAttendance = new SmSubjectAttendance();
                    $aramiscAttendance->aramiscAttendance_type = "H";
                    $aramiscAttendance->notes = "Holiday";
                    $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->aramiscAttendance_date));
                    $aramiscAttendance->student_id = $record->student_id;
                    $aramiscAttendance->subject_id = $request->subject_id;
                    $aramiscAttendance->student_record_id = $record->id;
                    $aramiscAttendance->class_id = $record->class_id;
                    $aramiscAttendance->section_id = $record->section_id;
                    $aramiscAttendance->academic_id = getAcademicId();
                    $aramiscAttendance->school_id = Auth::user()->school_id;
                    $aramiscAttendance->save();
                } else {
                    $aramiscAttendance = new SmSubjectAttendance();
                    $aramiscAttendance->aramiscAttendance_type = "H";
                    $aramiscAttendance->notes = "Holiday";
                    $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->aramiscAttendance_date));
                    $aramiscAttendance->student_id = $record->student_id;
                    $aramiscAttendance->subject_id = $request->subject_id;

                    $aramiscAttendance->student_record_id = $record->id;
                    $aramiscAttendance->class_id = $record->class_id;
                    $aramiscAttendance->section_id = $record->section_id;

                    $aramiscAttendance->academic_id = getAcademicId();
                    $aramiscAttendance->school_id = Auth::user()->school_id;
                    $aramiscAttendance->save();
                }


                //notification

                $messege = "";
                $date = dateConvert($aramiscAttendance->aramiscAttendance_date);

                $student = SmStudent::find($record->student_id);
                $subject = SmSubject::find($request->subject_id);
                $subject_name = $subject->subject_name;

                if ($student) {
                    $messege = app('translator')->get('student.Your_teacher_has_marked_holiday_in_the_aramiscAttendance_on_subject', ['date' => $date, 'subject_name' => $subject_name]);

                    $notification = new SmNotification();
                    $notification->user_id = $student->user_id;
                    $notification->role_id = 2;
                    $notification->date = date('Y-m-d');
                    $notification->message = $messege;
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = getAcademicId();
                    $notification->save();
                    try {
                        if ($student->user) {
                            $title = app('translator')->get('student.aramiscAttendance_notication');
                            Notification::send($student->user, new FlutterAppNotification($notification, $title));
                        }
                    } catch (\Exception $e) {
                        Log::info($e->getMessage());
                    }



                    // for parent user
                    $parent = SmParent::find($student->parent_id);
                    if ($parent) {
                        $messege = app('translator')->get('student.Your_child_is_marked_holiday_in_the_aramiscAttendance_on_subject', ['date' => $date, 'student_name' => $student->full_name . "'s", 'subject_name' => $subject_name]);

                        $notification = new SmNotification();
                        $notification->user_id = $parent->user_id;
                        $notification->role_id = 3;
                        $notification->date = date('Y-m-d');
                        $notification->message = $messege;
                        $notification->school_id = Auth::user()->school_id;
                        $notification->academic_id = getAcademicId();
                        $notification->save();

                        try {
                            $user = User::find($notification->user_id);
                            if ($user) {
                                $title = app('translator')->get('student.aramiscAttendance_notication');
                                Notification::send($user, new FlutterAppNotification($notification, $title));
                            }
                        } catch (\Exception $e) {
                            Log::info($e->getMessage());
                        }
                    }
                }
            }
        } elseif ($request->purpose == "unmark") {
            foreach ($students as $record) {
                $aramiscAttendance = SmSubjectAttendance::where('student_id', $record->student_id)
                    ->where('subject_id', $request->subject_id)
                    ->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))
                    ->where('class_id', $request->class_id)->where('section_id', $request->section_id)
                    ->where('student_record_id', $record->id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                if (!empty($aramiscAttendance)) {
                    $aramiscAttendance->delete();
                }
            }
        }
        Toastr::success('Operation successful', 'Success');
        return redirect('subject-wise-aramiscAttendance');
    }

    public function subjectAttendanceReport(Request $request)
    {
        try {

            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $types = SmStudentCategory::where('school_id', Auth::user()->school_id)->get();

            $genders = SmBaseSetup::where('active_status', '=', '1')
                ->where('base_group_id', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentInformation.subject_aramiscAttendance_report_view', compact('classes', 'types', 'genders'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function subjectAttendanceReportSearch(StudentSubjectWiseAttendanceSearchRequest $request)
    {

        try {
            $year = $request->year;
            $month = $request->month;
            $class_id = $request->class;
            $section_id = $request->section;
            $assign_subjects = SmAssignSubject::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->first();

            if (!$assign_subjects) {
                Toastr::warning('Subject Not Assign', 'Failed');
                return redirect()->back();
            }
            $subject_id = $assign_subjects->subject_id;
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $student_records = StudentRecord::query();
            $student_records->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->where('is_promote', 0)
                ->whereHas('student', function ($q) {
                    $q->where('active_status', 1);
                });
            if ($class_id != "") {
                $student_records->where('class_id', $class_id);
            }
            if ($section_id != "") {
                $student_records->where('section_id', $section_id);
            }
            $student_records = $student_records->get();
            $aramiscAttendances = [];
            foreach ($student_records as $record) {
                $aramiscAttendance = SmSubjectAttendance::with('student')->where('student_record_id', $record->id)
                    ->where('aramiscAttendance_date', 'like', $year . '-' . $month . '%')
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                if ($aramiscAttendance) {
                    $aramiscAttendances[] = $aramiscAttendance;
                }
            }

            return view('backEnd.studentInformation.subject_aramiscAttendance_report_view', compact('classes', 'aramiscAttendances', 'days', 'year', 'month', 'current_day', 'class_id', 'section_id', 'subject_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function subjectAttendanceAverageReport(Request $request)

    {

        try {

            $classes = SmClass::get();

            $types = SmStudentCategory::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', Auth::user()->school_id)->get();

            $genders = SmBaseSetup::where('base_group_id', '=', '1')->get();

            return view('backEnd.studentInformation.subject_aramiscAttendance_report_average_view', compact('classes', 'types', 'genders'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
    public function subjectAttendanceAverageReportSearch(subjectAttendanceAverageReportSearchRequest $request)

    {
        try {

            $year = $request->year;

            $month = $request->month;

            $class_id = $request->class_id;

            $section_id = $request->section_id;

            $assign_subjects = SmAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->first();

            if (!$assign_subjects) {

                Toastr::error('No Subject Assign ', 'Failed');

                return redirect()->back();
            }
            $subject_id = $assign_subjects->subject_id;

            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);

            $classes = SmClass::get();
            $activeStudentIds = SmStudentAttendanceController::activeStudent()->pluck('id')->toArray();
            $students = StudentRecord::where('class_id', $request->class)->where('section_id', $request->section)->whereIn('student_id', $activeStudentIds)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get()->sortBy('roll_no');

            $aramiscAttendances = [];

            foreach ($students as $record) {

                $aramiscAttendance = SmSubjectAttendance::where('sm_subject_aramiscAttendances.student_id', $record->student_id)

                    //  ->join('student_records','student_records.student_id','=','sm_subject_aramiscAttendances.student_id')

                    // // ->where('subject_id', $subject_id)

                    ->where('aramiscAttendance_date', 'like', $year . '-' . $month . '%')
                    ->where('sm_subject_aramiscAttendances.student_record_id', $record->id)
                    ->where('sm_subject_aramiscAttendances.academic_id', getAcademicId())
                    ->where('sm_subject_aramiscAttendances.school_id', Auth::user()->school_id)

                    ->get();

                if ($aramiscAttendance) {

                    $aramiscAttendances[] = $aramiscAttendance;
                }
            }
            $selected['class_id'] = $class_id;
            $selected['section_id'] = $section_id;
            //   return $aramiscAttendances;
            return view('backEnd.studentInformation.subject_aramiscAttendance_report_average_view', compact('classes', 'aramiscAttendances', 'days', 'year', 'month', 'current_day', 'class_id', 'section_id', 'subject_id', 'selected'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }


    public function studentAttendanceReportPrint($class_id, $section_id, $month, $year)
    {
        try {
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $classes = SmClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $activeStudentIds = SmStudentAttendanceController::activeStudent()->pluck('id')->toArray();
            $students = StudentRecord::where('class_id', $class_id)->where('section_id', $section_id)->whereIn('student_id', $activeStudentIds)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $aramiscAttendances = [];
            foreach ($students as $record) {
                $aramiscAttendance = SmStudentAttendance::where('student_id', $record->student_id)->where('aramiscAttendance_date', 'like', $year . '-' . $month . '%')->where('school_id', Auth::user()->school_id)
                    ->where('student_record_id', $record->id)
                    ->get();
                if (count($aramiscAttendance) != 0) {
                    $aramiscAttendances[] = $aramiscAttendance;
                }
            }

            return view('backEnd.studentInformation.student_aramiscAttendance_report', compact('classes', 'aramiscAttendances', 'days', 'year', 'month', 'current_day', 'class_id', 'section_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function subjectAttendanceReportAveragePrint($class_id, $section_id, $month, $year)
    {
        set_time_limit(2700);
        try {
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $activeStudentIds = SmStudentAttendanceController::activeStudent()->pluck('id')->toArray();
            $students = StudentRecord::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->whereIn('student_id', $activeStudentIds)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $aramiscAttendances = [];

            foreach ($students as $record) {
                $aramiscAttendance = SmSubjectAttendance::where('sm_subject_aramiscAttendances.student_id', $record->student_id)
                    // ->join('student_records','student_records.student_id','=','sm_subject_aramiscAttendances.student_id')
                    ->where('sm_subject_aramiscAttendances.student_record_id', $record->id)
                    ->where('aramiscAttendance_date', 'like', $year . '-' . $month . '%')
                    ->where('sm_subject_aramiscAttendances.academic_id', getAcademicId())
                    ->where('sm_subject_aramiscAttendances.school_id', Auth::user()->school_id)
                    ->get();

                if ($aramiscAttendance) {
                    $aramiscAttendances[] = $aramiscAttendance;
                }
            }

            return view('backEnd.studentInformation.student_subject_aramiscAttendance', compact('aramiscAttendances', 'days', 'year', 'month', 'class_id', 'section_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function subjectAttendanceReportPrint($class_id, $section_id, $month, $year)
    {
        set_time_limit(2700);
        try {
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $student_records = StudentRecord::query();
            $student_records->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->where('is_promote', 0)
                ->whereHas('student', function ($q) {
                    $q->where('active_status', 1);
                });
            if ($class_id != "") {
                $student_records->where('class_id', $class_id);
            }
            if ($section_id != "") {
                $student_records->where('section_id', $section_id);
            }
            $student_records = $student_records->get();
            $aramiscAttendances = [];
            foreach ($student_records as $record) {
                $aramiscAttendance = SmSubjectAttendance::with('student')->where('student_record_id', $record->id)
                    ->where('aramiscAttendance_date', 'like', $year . '-' . $month . '%')
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                if ($aramiscAttendance) {
                    $aramiscAttendances[] = $aramiscAttendance;
                }
            }

            return view('backEnd.studentInformation.student_subject_aramiscAttendance', compact('aramiscAttendances', 'days', 'year', 'month', 'class_id', 'section_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
