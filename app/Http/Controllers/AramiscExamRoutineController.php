<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\AramiscAcademicYear;
use App\AramiscAssignSubject;
use App\AramiscClass;
use App\AramiscClassRoom;
use App\AramiscClassTime;
use App\AramiscExam;
use App\AramiscExamSchedule;
use App\AramiscExamSetup;
use App\AramiscExamType;
use App\AramiscHoliday;
use App\AramiscSection;
use App\AramiscStaff;
use App\AramiscSubject;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AramiscExamRoutineController extends Controller
{

    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function examSchedule()
    {
        try {
            $exam_types = AramiscExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
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
            return view('backEnd.examination.exam_schedule', compact('classes', 'exam_types'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleCreate()
    {
        try {
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
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
            $sections = AramiscSection::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exams = AramiscExam::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_types = AramiscExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.exam_schedule_create', compact('classes', 'exams', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function addExamRoutineModal($subject_id, $exam_period_id, $class_id, $section_id, $exam_term_id, $section_id_all)
    {
        try {
            $rooms = AramiscClassRoom::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.examination.add_exam_routine_modal', compact('subject_id', 'exam_period_id', 'class_id', 'section_id', 'exam_term_id', 'rooms', 'section_id_all'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function checkExamRoutinePeriod(Request $request)
    {

        try {
            $exam_period_check = AramiscExamSchedule::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('exam_period_id', $request->exam_period_id)
                ->where('exam_term_id', $request->exam_term_id)
                ->where('date', date('Y-m-d', strtotime($request->date)))
                ->where('school_id', Auth::user()->school_id)
                ->first();

            return response()->json(['exam_period_check' => $exam_period_check]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function updateExamRoutinePeriod(Request $request)
    {

        try {
            $update_exam_period_check = AramiscExamSchedule::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('exam_period_id', $request->exam_period_id)
                ->where('exam_term_id', $request->exam_term_id)
                ->where('date', date('Y-m-d', strtotime($request->date)))
                ->where('school_id', Auth::user()->school_id)
                ->first();

            return response()->json(['update_exam_period_check' => $update_exam_period_check]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function EditExamRoutineModal($subject_id, $exam_period_id, $class_id, $section_id, $exam_term_id, $assigned_id, $section_id_all)
    {

        try {
            $rooms = AramiscClassRoom::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $assigned_exam = AramiscExamSchedule::find($assigned_id);

            return view('backEnd.examination.add_exam_routine_modal', compact('subject_id', 'exam_period_id', 'class_id', 'section_id', 'exam_term_id', 'rooms', 'assigned_exam', 'section_id_all'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteExamRoutineModal($assigned_id, $section_id_all)
    {

        try {
            return view('backEnd.examination.delete_exam_routine', compact('assigned_id', 'section_id_all'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function checkExamRoutineDate(Request $request)
    {

        try {
            if ($request->assigned_id == "") {
                $check_date = AramiscExamSchedule::where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('exam_term_id', $request->exam_term_id)->where('date', date('Y-m-d', strtotime($request->date)))->where('exam_period_id', $request->exam_period_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            } else {
                $check_date = AramiscExamSchedule::where('id', '!=', $request->assigned_id)->where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('exam_term_id', $request->exam_term_id)->where('date', date('Y-m-d', strtotime($request->date)))->where('exam_period_id', $request->exam_period_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            }

            $holiday_check = AramiscHoliday::where('from_date', '<=', date('Y-m-d', strtotime($request->date)))->where('to_date', '>=', date('Y-m-d', strtotime($request->date)))->where('school_id', Auth::user()->school_id)->first();

            if ($holiday_check != "") {
                $from_date = date('jS M, Y', strtotime($holiday_check->from_date));
                $to_date = date('jS M, Y', strtotime($holiday_check->to_date));
            } else {
                $from_date = '';
                $to_date = '';
            }

            return response()->json([$check_date, $holiday_check, $from_date, $to_date]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleReportSearch(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            // 'section' => 'required',
        ]);
        // $InputExamId = $request->exam;
        // $InputClassId = $request->class;
        // $InputSectionId = $request->section;

        try {
            $assign_subjects = AramiscAssignSubject::query();
            if (!empty($request->section)) {
                $assign_subjects->where('section_id', $request->section);
            }
            $assign_subjects = $assign_subjects->where('class_id', $request->class)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->distinct(['section_id', 'subject_id'])
                ->get();

            if ($assign_subjects->count() == 0) {
                Toastr::error('No Subject Assigned. Please assign subjects in this class.', 'Failed');
                return redirect()->back();
                // return redirect('exam-schedule-create')->with('message-danger', 'No Subject Assigned. Please assign subjects in this class.');
            }

            $assign_subjects = AramiscAssignSubject::query();
            if (!empty($request->section)) {
                $assign_subjects->where('section_id', $request->section);
            }
            $assign_subjects = $assign_subjects->where('class_id', $request->class)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->distinct(['section_id', 'subject_id'])
                ->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_id = $request->class;
            if (empty($request->section)) {
                $section_id = 0;
            } else {
                $section_id = $request->section;
            }
            $exam_id = $request->exam;

            $exam_types = AramiscExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_periods = AramiscClassTime::where('type', 'exam')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $exam_schedules = AramiscExamSchedule::query();
            if (!empty($request->section)) {
                $exam_schedules->where('section_id', $request->section);
            }
            $exam_schedules = $exam_schedules->where('exam_term_id', $exam_id)
                ->where('class_id', $request->class)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            // return $exam_schedules;

            $exam_dates = [];
            foreach ($exam_schedules as $exam_schedule) {
                $exam_dates['date'] = $exam_schedule->date;
                $exam_dates['section_id'] = $exam_schedule->section_id;
            }

            $exam_dates = array_unique($exam_dates);

            return view('backEnd.examination.exam_schedule_new', compact('classes', 'exams', 'exam_schedules', 'assign_subjects', 'class_id', 'section_id', 'exam_id', 'exam_types', 'exam_periods', 'exam_dates'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function compareByTimeStamp($time1, $time2)
    {

        try {
            if (strtotime($time1) < strtotime($time2)) {
                return 1;
            } else if (strtotime($time1) > strtotime($time2)) {
                return -1;
            } else {
                return 0;
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleReportSearchOld(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);

        try {
            $assign_subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', Auth::user()->school_id)->get();

            if ($assign_subjects->count() == 0) {
                Toastr::success('No Subject Assigned. Please assign subjects in this class.', 'Success');
                return redirect('exam-schedule-create');
            }

            $assign_subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', Auth::user()->school_id)->get();

            $classes = AramiscClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $exams = AramiscExam::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

            $class_id = $request->class;
            $section_id = $request->section;
            $exam_id = $request->exam;

            $exam_types = AramiscExamType::all();
            $exam_periods = AramiscClassTime::where('type', 'exam')->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.examination.exam_schedule', compact('classes', 'exams', 'assign_subjects', 'class_id', 'section_id', 'exam_id', 'exam_types', 'exam_periods'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function examSchedulePrint(Request $request)
    {

        try {
            $assign_subjects = AramiscAssignSubject::query();

            if ($request->section_id != 0) {
                $assign_subjects->where('section_id', $request->section_id);
            }
            $assign_subjects = $assign_subjects->where('class_id', $request->class_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->distinct(['section_id', 'subject_id'])
                ->get();

            $exam_periods = AramiscClassTime::where('type', 'exam')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $academic_year = AramiscAcademicYear::find(getAcademicId());

            $class_id = $request->class_id;
            $exam_id = $request->exam_id;

            $pdf = Pdf::loadView(
                'backEnd.examination.exam_schedult_print',
                [
                    'assign_subjects' => $assign_subjects,
                    'exam_periods' => $exam_periods,
                    'class_id' => $request->class_id,
                    'academic_year' => $academic_year,
                    'section_id' => $request->section_id,
                    'exam_id' => $request->exam_id,
                ]
            )->setPaper('A4', 'landscape');
            return $pdf->stream('EXAM_SCHEDULE.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutineReport(Request $request)
    {

        try {
            $exam_types = AramiscExamType::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.reports.exam_routine_report', compact('classes', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutineReportSearch(Request $request)
    {

        try {
            $exam_types = AramiscExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_periods = AramiscClassTime::where('type', 'exam')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_routines = AramiscExamSchedule::where('exam_term_id', $request->exam)->orderBy('date', 'ASC')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $exam_term_id = $request->exam;

            return view('backEnd.reports.exam_routine_report', compact('exam_types', 'exam_routines', 'exam_periods', 'exam_term_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutineReportSearchPrint($exam_id)
    {

        try {
            $exam_types = AramiscExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_periods = AramiscClassTime::where('type', 'exam')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_routines = AramiscExamSchedule::where('exam_term_id', $exam_id)->orderBy('date', 'ASC')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_routines = $exam_routines->distinct('date');
            $academic_year = AramiscAcademicYear::find(getAcademicId());

            $exam_term_id = $exam_id;

            $pdf = Pdf::loadView(
                'backEnd.reports.exam_routine_report_print',
                [
                    'exam_types' => $exam_types,
                    'exam_routines' => $exam_routines,
                    'exam_periods' => $exam_periods,
                    'exam_term_id' => $exam_term_id,
                    'academic_year' => $academic_year,
                ]
            )->setPaper('A4', 'landscape');
            return $pdf->stream('exam_routine.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

// change examScheduleSearch for update exam routine =abuNayem
    public function examScheduleSearch(Request $request)
    {
        // return $request->all();
        $request->validate([
            'exam_type' => 'required',
            'class' => 'required',
            // 'section' => 'required',
        ]);

        try {
            $subject_ids = AramiscExamSetup::query();
            $assign_subjects = AramiscAssignSubject::query();

            if ($request->class != null) {
                $assign_subjects->where('class_id', $request->class);
                $subject_ids->where('class_id', $request->class);
            }

            if ($request->section != null) {
                $assign_subjects->where('section_id', $request->section);
                $subject_ids->where('section_id', $request->section);
            }

            $assign_subjects = $assign_subjects->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $subject_ids = $subject_ids->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->pluck('id')->toArray();

            if ($assign_subjects->count() == 0) {
                Toastr::success('No Subject Assigned. Please assign subjects in this class.', 'Success');
                return redirect('exam-schedule-create');
            }

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

            $class_id = $request->class;
            $section_id = $request->section != null ? $request->section : 0;
            $exam_type_id = $request->exam_type;
            $exam_types = AramiscExamType::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exam_schedule = AramiscExamSchedule::query();
            if ($request->class) {
                $exam_schedule->where('class_id', $request->class);
            }
            if ($request->section) {
                $exam_schedule->where('section_id', $request->section);
            }
            $exam_schedule = $exam_schedule->where('exam_term_id', $request->exam_type)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $subjects = AramiscSubject::whereIn('id', $subject_ids)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get(['id', 'subject_name']);

            $teachers = AramiscStaff::where('role_id', 4)->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get(['id', 'user_id', 'full_name']);

            $rooms = AramiscClassRoom::where('active_status', 1)->where('school_id', Auth::user()->school_id)
                ->get(['id', 'room_no']);

            $examName = AramiscExamType::where('id', $request->exam_type)->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->first()->title;

            $search_current_class = AramiscClass::find($request->class);
            $search_current_section = AramiscSection::find($request->section);

            return view('backEnd.examination.exam_schedule_new_update', compact('classes', 'subjects', 'exam_schedule', 'class_id', 'section_id', 'exam_type_id', 'exam_types', 'teachers', 'rooms', 'examName', 'search_current_class', 'search_current_section'));
        } catch (\Exception $e) {          
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

//end

    public function addExamRoutineStore(Request $request)
    {
        //   return   $request->all();
        if(db_engine()=="mysql"){
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            $exam_schedule = AramiscExamSchedule::query();
            if ($request->class_id) {
                $exam_schedule->where('class_id', $request->class_id);
            }
            if ($request->section_id != 0) {
                $exam_schedule->where('section_id', $request->section);
            }
            $exam_schedule = $exam_schedule->where('exam_term_id', $request->exam_type_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->delete();

            foreach ($request->routine as $routine_data) {
                $is_exist = AramiscExamSchedule::where(
                    [
                        'exam_term_id' => $request->exam_type_id,
                        'subject_id' => gv($routine_data, 'subject'),
                        'date' => date('Y-m-d', strtotime(gv($routine_data, 'date'))),
                        'start_time' => date('H:i:s', strtotime(gv($routine_data, 'start_time'))),
                        'end_time' => date('H:i:s', strtotime(gv($routine_data, 'end_time'))),
                        'room_id' => gv($routine_data, 'room'),
                        'class_id' => $request->class_id,
                        'section_id' => gv($routine_data, 'section'),
                    ])->where('school_id', Auth::user()->school_id)->first();

                if ($is_exist) {
                    continue;
                }

                $exam_routine = new AramiscExamSchedule();
                $exam_routine->exam_term_id = $request->exam_type_id;
                $exam_routine->class_id = $request->class_id;
                $exam_routine->section_id = gv($routine_data, 'section');
                $exam_routine->subject_id = gv($routine_data, 'subject');
                $exam_routine->teacher_id = gv($routine_data, 'teacher_id');
                $exam_routine->date = date('Y-m-d', strtotime(gv($routine_data, 'date')));
                $exam_routine->start_time = date('H:i:s', strtotime(gv($routine_data, 'start_time')));
                $exam_routine->end_time = date('H:i:s', strtotime(gv($routine_data, 'end_time')));
                $exam_routine->room_id = gv($routine_data, 'room');
                $exam_routine->school_id = Auth::user()->school_id;
                $exam_routine->academic_id = getAcademicId();
                $exam_routine->save();

            }

            Toastr::success('Exam routine has been assigned successfully', 'Success');

            $class_id = $request->class_id;
            $section_id = $request->section_id == 0 ? 0 : $request->section_id;
            $exam_term_id = $request->exam_type_id;

            return redirect('exam-routine-view/' . $class_id . '/' . $section_id . '/' . $exam_term_id);

        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutineView($class_id, $section_id, $exam_term_id)
    {

        try {

            $subject_ids = AramiscExamSetup::query();

            if ($class_id != null) {
                $subject_ids->where('class_id', $class_id);
            }

            if ($section_id != 0) {

                $subject_ids->where('section_id', $section_id);
            }

            $subject_ids = $subject_ids->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->pluck('id')->toArray();

            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $exams = AramiscExam::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exam_type_id = $exam_term_id;

            $exam_types = AramiscExamType::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $exam_periods = AramiscClassTime::where('type', 'exam')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $rooms = AramiscClassRoom::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $subjects = AramiscSubject::whereIn('id', $subject_ids)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get(['id', 'subject_name']);

            $teachers = AramiscStaff::where('role_id', 4)->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get(['id', 'user_id', 'full_name']);

            $search_current_class = AramiscClass::find($class_id);
            $search_current_section = AramiscSection::find($section_id);

            if ($section_id == 0) {
                $exam_schedule = AramiscExamSchedule::where('class_id', $class_id)->where('exam_term_id', $exam_type_id)->get();
            } else {
                $exam_schedule = AramiscExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)
                    ->where('exam_term_id', $exam_type_id)->get();
            }

            $examName = AramiscExamType::where('id', $exam_type_id)->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->first()->title;

            return view('backEnd.examination.exam_schedule_new_update', compact('classes', 'subjects', 'exam_schedule', 'exams', 'class_id', 'section_id', 'exam_type_id', 'exam_types', 'teachers', 'rooms', 'examName', 'search_current_class', 'search_current_section'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutinePrint($class_id, $section_id, $exam_term_id)
    {

        try {

            $exam_type_id = $exam_term_id;
            $exam_type = AramiscExamType::find($exam_type_id)->title;
            $academic_id = AramiscExamType::find($exam_type_id)->academic_id;
            $academic_year = AramiscAcademicYear::find($academic_id);
            $class_name = AramiscClass::find($class_id)->class_name;
            $section_name = $section_id != 0 ? AramiscSection::find($section_id)->section_name : 'All Sections';

            if ($section_id == 0) {

                $exam_schedules = AramiscExamSchedule::where('class_id', $class_id)->where('exam_term_id', $exam_type_id)->orderBy('date', 'ASC')->get();

            } else {

                $exam_schedules = AramiscExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)
                    ->where('exam_term_id', $exam_type_id)->orderBy('date', 'ASC')->get();
            }

             return view('backEnd.examination.exam_schedule_print', [
                 'exam_schedules' => $exam_schedules,
                 'exam_type' => $exam_type,
                 'class_name' => $class_name,
                 'academic_year' => $academic_year,
                 'section_name' => $section_name,
             ]);

        } catch (\Exception $e) {
          
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteExamRoutine(Request $request)
    {
        try {
            $exam_routine = AramiscExamSchedule::find($request->id);
            $result = $exam_routine->delete();
            return response(["done"]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
