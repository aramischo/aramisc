<?php

namespace App\Http\Controllers\Admin\OnlineExam;

use DataTables;
use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscParent;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use Carbon\Carbon;
use App\AramiscOnlineExam ; // as AramiscOnlineExamModel Use alias for the model
use App\AramiscNotification;
use App\AramiscQuestionBank;
use App\AramiscAssignSubject;
use App\AramiscOnlineExamMark;
use App\AramiscGeneralSettings;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscOnlineExamQuestion;
use App\AramiscStudentTakeOnlineExam;
use App\Traits\NotificationSend;
use Illuminate\Support\Facades\DB;
use App\AramiscOnlineExamQuestionAssign;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\AramiscOnlineExamQuestionMuOption;
use Illuminate\Support\Facades\Schema;
use App\OnlineExamStudentAnswerMarking;
use Illuminate\Support\Facades\Validator;
use Modules\OnlineExam\Entities\OnlineExam ; // as AramiscOnlineExamEntity Use alias for the entity
use App\Http\Requests\Admin\OnlineExam\AramiscOnlineExamRequest;
use App\Http\Controllers\Admin\StudentInfo\AramiscStudentReportController;

class AramiscOnlineExamController extends Controller
{
    use NotificationSend;
    private $timeZone;
    public function __construct()
    {
        $this->middleware('PM');
        $this->timeZone = generalSetting()->timeZone->time_zone ?? 'Asia/Dhaka';
    }

    public function index()
    {
        $time_zone_setup = AramiscGeneralSettings::join('aramisc_time_zones', 'aramisc_time_zones.id', '=', 'aramisc_general_settings.time_zone_id')
            ->where('school_id', Auth::user()->school_id)->first();
        date_default_timezone_set($time_zone_setup->time_zone);
        try {
            if (!Schema::hasColumn('aramisc_online_exams', 'auto_mark')) {
                Schema::table('aramisc_online_exams', function ($table) {
                    $table->integer('auto_mark')->default(0);
                });
            }
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $online_exams = AramiscOnlineExamModel::where('status', '!=', 2)
                    ->join('aramisc_assign_subjects', 'aramisc_assign_subjects.subject_id', '=', 'aramisc_online_exams.subject_id')
                    ->where('aramisc_assign_subjects.teacher_id', $teacher_info->id)
                    ->where('aramisc_online_exams.academic_id', getAcademicId())
                    ->where('aramisc_online_exams.school_id', Auth::user()->school_id)
                    ->select('aramisc_online_exams.*')
                    ->distinct('id')
                    ->get();
                $classes = $teacher_info->classes;
            } else {
                $classes = AramiscClass::get();
                $online_exams = AramiscOnlineExamModel::with('class', 'section', 'subject')->where('status', '!=', 2)->get();
            }
            $sections = AramiscSection::get();
            $subjects = AramiscSubject::get();
            $present_date_time = date("Y-m-d H:i:s");
            $present_time = date("H:i:s");
            return view('backEnd.examination.online_exam', compact('online_exams', 'classes', 'sections', 'subjects', 'present_date_time', 'present_time'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    // Rest of your methods...

    public function store(AramiscOnlineExamRequest $request)
    {
        if (moduleStatusCheck('University')) {
            return $this->universityOnlineExamStore($request);
        } else {

            DB::beginTransaction();
            try {
                foreach ($request->section as $section) {
                    $online_exam = new AramiscOnlineExamModel();
                    $online_exam->title = $request->title;
                    $online_exam->class_id = $request->class;
                    $online_exam->section_id = $section;
                    $online_exam->subject_id = $request->subject;
                    $online_exam->date = date('Y-m-d', strtotime($request->date));
                    $online_exam->start_time = date('H:i:s', strtotime($request->start_time));
                    $online_exam->end_time = date('H:i:s', strtotime($request->end_time));
                    $online_exam->end_date_time = date('Y-m-d H:i:s', strtotime($request->date . ' ' . $request->end_time));
                    $online_exam->percentage = $request->percentage;
                    $online_exam->instruction = $request->instruction;
                    $online_exam->status = 0;
                    if ($request->auto_mark) {
                        $online_exam->auto_mark = $request->auto_mark;
                    }
                    $online_exam->school_id = Auth::user()->school_id;
                    $online_exam->academic_id = getAcademicId();
                    // dd($online_exam, $online_exam->class_id, $online_exam->section_id, $online_exam->subject->subject_name);
                    $online_exam->save();

                    $data['class_id'] = $online_exam->class_id;
                    $data['section_id'] = $online_exam->section_id;
                    $data['subject'] = $online_exam->subject->subject_name;
                    $records = $this->studentRecordInfo($request->class, $request->section)->pluck('studentDetail.user_id');
                    $this->sent_notifications('Online_Exam_Publish', $records, $data, ['Student', 'Parent']);
                }
                DB::commit();

                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    }

    // Rest of your methods...
}
