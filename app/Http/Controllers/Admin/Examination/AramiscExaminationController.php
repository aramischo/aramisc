<?php

namespace App\Http\Controllers\Admin\Examination;

use App\AramiscExam;
use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\YearCheck;
use App\AramiscExamType;
use App\AramiscSeatPlan;
use App\AramiscClassRoom;
use App\AramiscClassTime;
use App\AramiscExamSetup;
use App\AramiscMarkStore;
use App\AramiscMarksGrade;
use App\AramiscExamSetting;
use App\AramiscResultStore;
use App\AramiscAcademicYear;
use App\AramiscExamSchedule;
use App\AramiscAssignSubject;
use App\AramiscMarksRegister;
use App\AramiscSeatPlanChild;
use App\AramiscExamAttendance;
use App\AramiscGeneralSettings;
use App\AramiscStudentPromotion;
use App\CustomResultSetting;
use App\AramiscStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscTemporaryMeritlist;
use App\AramiscExamAttendanceChild;
use App\AramiscExamScheduleSubject;
use App\AramiscClassOptionalSubject;
use App\AramiscOptionalSubjectAssign;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\University\Entities\UnFaculty;
use Modules\University\Entities\UnSession;
use Modules\University\Entities\UnSubject;
use App\Http\Requests\ExamTypeStoreRequest;
use Modules\University\Entities\UnSemester;
use Modules\University\Entities\UnDepartment;
use Modules\University\Entities\UnAcademicYear;
use Modules\University\Entities\UnSemesterLabel;
use App\Http\Requests\AdmissionNumberGetStudentRequest;
use Modules\University\Http\Controllers\ExamCommonController;
use App\Http\Requests\Admin\Examination\MarkSheetReportRequest;
use App\Http\Requests\Admin\Examination\MeritListReportRequest;
use App\Http\Requests\Examination\PercentMarkSheetReportRequest;
use App\Http\Controllers\Admin\StudentInfo\AramiscStudentReportController;
use App\Http\Requests\Admin\Examination\AramiscExamAttendanceSearchRequest;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class AramiscExaminationController extends Controller
{


    public function examSchedule()
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.exam_schedule', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function resultsArchiveView()
    {
        try {
            $academic_years = AramiscAcademicYear::where('school_id', Auth::user()->school_id)->get();
            $exam_types = AramiscExamType::where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.resultsArchiveView', compact('classes', 'exam_types', 'academic_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function previousClassResults()
    {
        try {
            return view('backEnd.reports.previousClassResults');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function previousClassResultsView($admission_no, Request $request)
    {
        $request->validate([
            'admission_number' => 'required',
        ]);
        try {
            $admission_number = $admission_no;
            $promotes = AramiscStudentPromotion::where('admission_number', '=', $admission_no)
                ->join('aramisc_academic_years', 'aramisc_academic_years.id', '=', 'aramisc_student_promotions.previous_session_id')
                ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_student_promotions.previous_class_id')
                ->join('aramisc_students', 'aramisc_students.id', '=', 'aramisc_student_promotions.student_id')
                ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_student_promotions.previous_section_id')
                // ->select('admission_number', 'student_id', 'previous_class_id', 'class_name', 'previous_section_id', 'section_name', 'year', 'previous_session_id')
                ->get();
            if ($promotes->count() < 1) {
                Toastr::error('Ops! Admission number is not found in previous academic year', 'Failed');
                return redirect()->back()->withInput();
                // return redirect()->back()->withInput()->with('message-danger', 'Ops! Admission number is not found in previous academic year. Please try again');
            }
            $studentDetails = AramiscStudentPromotion::where('admission_number', '=', $admission_no)
                ->join('aramisc_academic_years', 'aramisc_academic_years.id', '=', 'aramisc_student_promotions.previous_session_id')
                ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_student_promotions.previous_class_id')
                ->join('aramisc_students', 'aramisc_students.id', '=', 'aramisc_student_promotions.student_id')
                ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_student_promotions.previous_section_id')
                // ->select('admission_number', 'student_id', 'previous_class_id', 'class_name', 'previous_section_id', 'section_name', 'year', 'previous_session_id')
                ->first();
            //  return $promotes;

            $generalSetting = AramiscGeneralSettings::where('school_id', auth()->user()->school_id)->first();

            if ($promotes->count() > 0) {
                $student_id = $studentDetails->student_id;

                $current_class = AramiscStudent::where('aramisc_students.id', $student_id)->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')->first();
                $current_section = AramiscStudent::where('aramisc_students.id', $student_id)->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')->first();
                $current_session = AramiscStudent::where('aramisc_students.id', $student_id)->join('aramisc_academic_years', 'aramisc_academic_years.id', '=', 'aramisc_students.session_id')->first();

                return view('backEnd.reports.previousClassResults', compact('promotes', 'studentDetails', 'generalSetting', 'current_class', 'current_section', 'current_session', 'admission_number'));
            } else {
                Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                return redirect('previous-class-results');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function previousClassResultsViewPost(Request $request)
    {
        try {
            $studentRecord = StudentRecord::find($request->recordId);
            $class_id = $studentRecord->class_id;
            $section_id = $studentRecord->section_id;
            $academic_id = $studentRecord->academic_id;
            $student_id = $studentRecord->student_id;
            $record_id = $studentRecord->id;
            $school_id = $studentRecord->school_id;

            $exam_content = AramiscExamSetting::withOutGlobalScopes()->whereNull('exam_type')
                ->where('active_status', 1)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $exams = AramiscExam::withOutGlobalScopes()->where('active_status', 1)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', $school_id)
                ->get();

            $exam_types = AramiscExamType::withOutGlobalScopes()->where('active_status', 1)
                ->where('academic_id', $academic_id)
                ->where('school_id', $school_id)
                ->pluck('id');


            $classes = AramiscClass::withOutGlobalScopes()->where('active_status', 1)
                ->where('academic_id', $academic_id)
                ->where('school_id', $school_id)
                ->get();

            $fail_grade = AramiscMarksGrade::withOutGlobalScopes()->where('active_status', 1)
                ->where('academic_id', $academic_id)
                ->where('school_id', $school_id)
                ->min('gpa');

            $fail_grade_name = AramiscMarksGrade::withOutGlobalScopes()->where('active_status', 1)
                ->where('academic_id', $academic_id)
                ->where('school_id', $school_id)
                ->where('gpa', $fail_grade)
                ->first();

            $studentDetails = $studentRecord;

            $marks_grade = AramiscMarksGrade::withOutGlobalScopes()->where('school_id', $school_id)
                ->where('academic_id', $academic_id)
                ->orderBy('gpa', 'desc')
                ->get();

            $maxGrade = AramiscMarksGrade::withOutGlobalScopes()->where('academic_id', $academic_id)
                ->where('school_id', $school_id)
                ->max('gpa');

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $class_id)
                ->first();

            $student_optional_subject = AramiscOptionalSubjectAssign::where('student_id', $studentDetails->student_id)
                ->where('session_id', '=', $studentDetails->session_id)
                ->first();

            $exam_setup = AramiscExamSetup::where([
                ['class_id', $class_id],
                ['section_id', $section_id]])
                ->where('school_id', $school_id)
                ->get();

            $examSubjects = AramiscExam::withOutGlobalScopes()->where([['section_id', $section_id], ['class_id', $class_id]])
                ->where('school_id', $school_id)
                ->where('academic_id', $academic_id)
                ->get();

            $examSubjectIds = [];
            foreach ($examSubjects as $examSubject) {
                $examSubjectIds[] = $examSubject->subject_id;
            }

            $subjects = AramiscAssignSubject::withOutGlobalScopes()->where([
                ['class_id', $class_id],
                ['section_id', $section_id]])
                ->where('school_id', $school_id)
                ->whereIn('subject_id', $examSubjectIds)
                ->get();

            $assinged_exam_types = [];
            foreach ($exams as $exam) {
                $assinged_exam_types[] = $exam->exam_type_id;
            }
            $assinged_exam_types = array_unique($assinged_exam_types);


            foreach ($assinged_exam_types as $assinged_exam_type) {
                foreach ($subjects as $subject) {
                    $is_mark_available = AramiscResultStore::where([
                            ['class_id', $class_id],
                            ['section_id', $section_id],
                            ['student_id', $studentDetails->student_id]
                        ])
                        ->first();
                    if ($is_mark_available == "") {
                        Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                        return redirect('progress-card-report');
                    }
                }
            }

            $is_result_available = AramiscResultStore::where([
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['student_id', $studentDetails->student_id]])
                ->where('school_id', $school_id)
                ->get();

            $data ['exams'] = $exams;
            $data ['optional_subject_setup'] = $optional_subject_setup;
            $data ['student_optional_subject'] = $student_optional_subject;
            $data ['classes'] = $classes;
            $data ['studentDetails'] = $studentDetails;
            $data ['is_result_available'] = $is_result_available;
            $data ['subjects'] = $subjects;
            $data ['class_id'] = $class_id;
            $data ['section_id'] = $section_id;
            $data ['student_id'] = $student_id;
            $data ['exam_types'] = $exam_types;
            $data ['assinged_exam_types'] = $assinged_exam_types;
            $data ['marks_grade'] = $marks_grade;
            $data ['fail_grade_name'] = $fail_grade_name;
            $data ['fail_grade'] = $fail_grade;
            $data ['maxGrade'] = $maxGrade;
            $data ['custom_mark_report'] = null;
            $data ['exam_content'] = $exam_content;


            $html = view('backEnd.reports._progress_card_report_content', $data)->render();

            if ($is_result_available->count() > 0) {
                return response()->json(['success' => true, 'html' => $html]);
            }else{
                return response()->json(['error' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => true]);
        }
    }

    public function previousStudentRecord(AdmissionNumberGetStudentRequest $request)
    {
        $admission_number = $request->admission_number;
        $studentInfo = AramiscStudent::where('admission_no', $admission_number)->first();

        if(!$studentInfo){
            return response()->json(['not_valid']);
        }
        $allPromotedYearData = StudentRecord::with('class', 'section', 'academic')->where('is_promote', 1)->where('student_id', $studentInfo->id)->get();
        return response()->json([$allPromotedYearData]);
    }

    public function previousClassResultsViewPrint(Request $request)
    {
        try {
            $promotes = AramiscStudentPromotion::where('admission_number', '=', $request->admission_number)
                ->join('aramisc_academic_years', 'aramisc_academic_years.id', '=', 'aramisc_student_promotions.previous_session_id')
                ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_student_promotions.previous_class_id')
                ->join('aramisc_students', 'aramisc_students.id', '=', 'aramisc_student_promotions.student_id')
                ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_student_promotions.previous_section_id')
                // ->select('admission_number', 'student_id', 'previous_class_id', 'class_name', 'previous_section_id', 'section_name', 'year', 'previous_session_id')
                ->get();
            $studentDetails = AramiscStudentPromotion::where('admission_number', '=', $request->admission_number)
                ->join('aramisc_academic_years', 'aramisc_academic_years.id', '=', 'aramisc_student_promotions.previous_session_id')
                ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_student_promotions.previous_class_id')
                ->join('aramisc_students', 'aramisc_students.id', '=', 'aramisc_student_promotions.student_id')
                ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_student_promotions.previous_section_id')
                // ->select('admission_number', 'student_id', 'previous_class_id', 'class_name', 'previous_section_id', 'section_name', 'year', 'previous_session_id')
                ->first();
            $student_id = $studentDetails->student_id;


            $exams = AramiscExam::where('active_status', 1)
                ->where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('academic_id', $studentDetails->academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exam_types = AramiscExamType::where('active_status', 1)
                ->where('academic_id', $studentDetails->academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', $studentDetails->academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $marks_grade = AramiscMarksGrade::where('school_id', Auth::user()->school_id)
                ->where('academic_id', $studentDetails->academic_id)
                ->orderBy('gpa', 'desc')
                ->get();

            $fail_grade = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', $studentDetails->academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $fail_grade_name = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', $studentDetails->academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $fail_grade)
                ->first();

            $student_detail = AramiscStudent::find($request->student_id);

            $exam_setup = AramiscExamSetup::where([
                ['class_id', $request->class_id],
                ['section_id', $request->section_id]])
                ->where('academic_id', $studentDetails->academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $class_id = $request->class_id;
            $section_id = $request->section_id;
            $student_id = $request->student_id;

            $examSubjects = AramiscExam::where([['section_id', $request->section_id], ['class_id', $request->class_id]])
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', $studentDetails->academic_id)
                ->get();

            $examSubjectIds = [];
            foreach ($examSubjects as $examSubject) {
                $examSubjectIds[] = $examSubject->subject_id;
            }

            $subjects = AramiscAssignSubject::where([
                ['class_id', $request->class_id],
                ['section_id', $request->section_id]])
                ->where('academic_id', $studentDetails->academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->whereIn('subject_id', $examSubjectIds)
                ->get();

            $assinged_exam_types = [];
            foreach ($exams as $exam) {
                $assinged_exam_types[] = $exam->exam_type_id;
            }
            $assinged_exam_types = array_unique($assinged_exam_types);
            foreach ($assinged_exam_types as $assinged_exam_type) {
                foreach ($subjects as $subject) {
                    $is_mark_available = AramiscResultStore::where([
                        ['class_id', $request->class_id],
                        ['section_id', $request->section_id],
                        ['student_id', $studentDetails->id],
                        ['subject_id', $subject->subject_id],
                    ])
                        ->where('academic_id', getAcademicId())
                        ->first();
                    if ($is_mark_available == "") {
                        Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                        return redirect()->redirect('previous-class-results');
                        // return redirect('progress-card-report')->with('message-danger', 'Ops! Your result is not found! Please check mark register.');
                    }
                }
            }

            $is_result_available = AramiscResultStore::where([
                ['class_id', $request->class_id], ['section_id', $request->section_id], ['student_id', $studentDetails->id]])
                ->where('academic_id', $studentDetails->academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $request->class_id)->first();

            $student_optional_subject = AramiscOptionalSubjectAssign::where('student_id', $request->student_id)
                ->where('academic_id', '=', $studentDetails->academic_id)
                ->first();

            if ($promotes->count() > 0) {
                return view('backEnd.reports.student_archive_print',
                    compact(
                        'optional_subject_setup',
                        'student_optional_subject',
                        'exams',
                        'classes',
                        'is_result_available',
                        'subjects',
                        'class_id',
                        'section_id',
                        'student_id',
                        'exam_types',
                        'assinged_exam_types',
                        'marks_grade',
                        'studentDetails',
                        'fail_grade_name'
                    ));
            } else {
                Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                return redirect()->route('previous-class-results');
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function resultsArchiveSearch(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);
    }

    public function examScheduleCreate()
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $sections = AramiscSection::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exams = AramiscExam::where('school_id', Auth::user()->school_id)->get();
            $exam_types = AramiscExamType::where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.exam_schedule_create', compact('classes', 'exams', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleSearch(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);

        try {
            $assign_subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', getAcademicId())->get();

            if ($assign_subjects->count() == 0) {
                Toastr::error('No Subject Assigned. Please assign subjects in this class', 'Failed');
                return redirect('exam-schedule-create');
            }

            $assign_subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', getAcademicId())->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_id = $request->class;
            $section_id = $request->section;
            $exam_id = $request->exam;

            $exam_types = AramiscExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_periods = AramiscClassTime::where('type', 'exam')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.examination.exam_schedule_create', compact('classes', 'exams', 'assign_subjects', 'class_id', 'section_id', 'exam_id', 'exam_types', 'exam_periods'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleStore(Request $request)
    {
        $update_check = AramiscExamSchedule::where('exam_id', $request->exam_id)->where('class_id', $request->class_id)->where('section_id', $request->section_id)->first();

        DB::beginTransaction();

        try {
            if ($update_check == "") {
                $exam_schedule = new AramiscExamSchedule();
            } else {
                $exam_schedule = $update_check = AramiscExamSchedule::where('exam_id', $request->exam_id)->where('class_id', $request->class_id)->where('section_id', $request->section_id)->first();
            }

            $exam_schedule->class_id = $request->class_id;
            $exam_schedule->section_id = $request->section_id;
            $exam_schedule->exam_id = $request->exam_id;
            $exam_schedule->school_id = Auth::user()->school_id;
            $exam_schedule->academic_id = getAcademicId();
            $exam_schedule->save();
            $exam_schedule->toArray();

            $counter = 0;

            if ($update_check != "") {
                AramiscExamScheduleSubject::where('exam_schedule_id', $exam_schedule->id)->delete();
            }

            foreach ($request->subjects as $subject) {
                $counter++;
                $date = 'date_' . $counter;
                $start_time = 'start_time_' . $counter;
                $end_time = 'end_time_' . $counter;
                $room = 'room_' . $counter;
                $full_mark = 'full_mark_' . $counter;
                $pass_mark = 'pass_mark_' . $counter;

                $exam_schedule_subject = new AramiscExamScheduleSubject();
                $exam_schedule_subject->exam_schedule_id = $exam_schedule->id;
                $exam_schedule_subject->subject_id = $subject;
                $exam_schedule_subject->date = date('Y-m-d', strtotime($request->$date));
                $exam_schedule_subject->start_time = $request->$start_time;
                $exam_schedule_subject->end_time = $request->$end_time;
                $exam_schedule_subject->room = $request->$room;
                $exam_schedule_subject->full_mark = $request->$full_mark;
                $exam_schedule_subject->pass_mark = $request->$pass_mark;
                $exam_schedule_subject->school_id = Auth::user()->school_id;
                $exam_schedule_subject->academic_id = getAcademicId();
                $exam_schedule_subject->save();
            }

            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('exam-schedule');
        } catch (\Exception $e) {
            DB::rollBack();
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }

    public function viewExamSchedule($class_id, $section_id, $exam_id)
    {
        try {
            $class = AramiscClass::find($class_id);
            $section = AramiscSection::find($section_id);
            $assign_subjects = AramiscExamScheduleSubject::where('exam_schedule_id', $exam_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.view_exam_schedule_modal', compact('class', 'section', 'assign_subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function viewExamStatus($exam_id)
    {
        try {
            $exam = AramiscExam::find($exam_id);
            $view_exams = AramiscExamSchedule::where('exam_id', $exam_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.view_exam_status', compact('exam', 'view_exams'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    // Mark Register View Page
    public function marksRegister()
    {
        try {
            $exams = AramiscExam::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
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

            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.masks_register', compact('exams', 'classes', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function marksRegisterCreate()
    {
        try {
            $exams = AramiscExam::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exam_types = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

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
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.masks_register_create', compact('exams', 'classes', 'subjects', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //show exam type method from aramisc_exams_types table
    public function exam_type()
    {
        try {
            $exams = AramiscExam::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
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
            $exams_types = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            return view('backEnd.examination.exam_type', compact('exams', 'classes', 'exams_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //edit exam type method from aramisc_exams_types table
    public function exam_type_edit($id)
    {
        try {
            if (checkAdmin()) {
                $exam_type_edit = AramiscExamType::find($id);
            } else {
                $exam_type_edit = AramiscExamType::where('id', $id)->first();
            }
            $exams_types = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            return view('backEnd.examination.exam_type', compact('exam_type_edit', 'exams_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //update exam type method from aramisc_exams_types table
    public function exam_type_update(Request $request)
    {
        $request->validate([
            'exam_type_title' => 'required|max:50',
            // 'active_status' => 'required'
        ]);
        // school wise uquine validation
        $is_duplicate = AramiscExamType::where('title', $request->exam_type_title)
            ->where('id', '!=', $request->id)
            ->where('academic_id', '!=', getAcademicId())
            ->where('school_id', '!=', Auth::user()->school_id)
            ->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');
            return redirect()->back()->withInput();
        }
        DB::beginTransaction();
        try {
            if (checkAdmin()) {
                $update_exame_type = AramiscExamType::find($request->id);
            } else {
                $update_exame_type = AramiscExamType::where('id', $request->id)->first();
            }
            $update_exame_type->title = $request->exam_type_title;
            $update_exame_type->is_average = ($request->is_average == 'yes' || $request->is_average == 'on') ? 1 : 0;
            $update_exame_type->average_mark = $request->average_mark ?? 0;
            $update_exame_type->save();
            $update_exame_type->toArray();

            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('exam-type');
        } catch (\Exception $e) {
            DB::rollback();
           
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //store exam type method from aramisc_exams_types table
    public function exam_type_store(ExamTypeStoreRequest $request)
    {
        try {
            $update_exame_type = new AramiscExamType();
            $update_exame_type->title = $request->exam_type_title;
            $update_exame_type->active_status = 1; //1 for status active & 0 for inactive
            $update_exame_type->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $update_exame_type->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $update_exame_type->un_academic_id = getAcademicId();
            } else {
                $update_exame_type->academic_id = getAcademicId();
            }
            $update_exame_type->is_average = ($request->is_average == 'yes' || $request->is_average == 'on') ? 1 : 0;
            $update_exame_type->average_mark = $request->average_mark ?? 0;
            
            $result = $update_exame_type->save();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-type');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //delete exam type method from aramisc_exams_types table
    public function exam_type_delete(Request $request, $id)
    {
        try {
            $id_key = 'exam_type_id';
            $term_key = 'exam_term_id';
            $type = \App\tableList::getTableList($id_key, $id);
            $term = \App\tableList::getTableList($term_key, $id);
            $tables = $type . '' . $term;
            try {
                if ($tables == null || $tables == '') {
                    if (checkAdmin()) {
                        $delete_query = AramiscExamType::destroy($id);
                    } else {
                        $data = AramiscExamType::where('id', $id)->first();
                        $delete_query = $data->delete();
                    }
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                } else {
                    $msg = 'This data already used in   : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function marksRegisterSearch(Request $request)
    {

        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            // 'section' => 'required',
            'subject' => 'required',
        ]);
        try {
            if ($request->section == '') {
                $classSections = AramiscAssignSubject::where('class_id', $request->class)
                    ->where('subject_id', $request->subject)
                    ->where('school_id', auth()->user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->distinct(['section_id', 'subject_id'])
                    ->get(['section_id']);

                $exam_attendance = AramiscExamAttendance::where('class_id', $request->class)->where('exam_id', $request->exam)->where('subject_id', $request->subject)->first();

            } else {
                $exam_attendance = AramiscExamAttendance::where('class_id', $request->class)->where('section_id', $request->section)->where('exam_id', $request->exam)->where('subject_id', $request->subject)->first();

            }

            if ($exam_attendance == "") {

                Toastr::error('Exam Attendance not taken yet, please check exam attendance', 'Failed');
                return redirect()->back();

            }
            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_id = $request->exam;
            $class_id = $request->class;
            $section_id = $request->section;
            $subject_id = $request->subject;
            $subjectNames = AramiscSubject::where('id', $subject_id)->first();

            $exam_type = AramiscExamType::find($request->exam);
            $class = AramiscClass::find($request->class);
            $section = AramiscSection::find($request->section);

            $search_info['exam_name'] = $exam_type->title;
            $search_info['class_name'] = $class->class_name;
            if ($request->section != '') {
                $search_info['section_name'] = $section->section_name;

            } else {
                $search_info['section_name'] = 'All Sections';
            }

            if ($request->section != '') {
                $students = AramiscStudent::where('active_status', 1)->where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', getAcademicId())->get();
            } else {
                $students = AramiscStudent::where('active_status', 1)->where('class_id', $request->class)->where('academic_id', getAcademicId())->get();
            }

            $exam_schedule = AramiscExamSchedule::where('exam_id', $request->exam)->where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', getAcademicId())->first();

            if ($students->count() < 1) {
                Toastr::error('Student is not found in according this class and section!', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('message-danger', 'Student is not found in according this class and section! Please add student in this section of that class.');
            } else {
                if ($request->section != '') {
                    $marks_entry_form = AramiscExamSetup::where(
                        [
                            ['exam_term_id', $exam_id],
                            ['class_id', $class_id],
                            ['section_id', $section_id],
                            ['subject_id', $subject_id],
                        ]
                    )->where('academic_id', getAcademicId())->get();
                } else {
                    $marks_entry_form = AramiscExamSetup::where(
                        [
                            ['exam_term_id', $exam_id],
                            ['class_id', $class_id],
                            ['subject_id', $subject_id],
                        ]
                    )->whereIn('section_id', $classSections)->distinct(['subject_id', 'exam_title'])->where('academic_id', getAcademicId())->orderby('id', 'ASC')->get();
                }

                if ($marks_entry_form->count() > 0) {
                    $number_of_exam_parts = count($marks_entry_form);

                    return view('backEnd.examination.masks_register_create', compact('exams', 'classes', 'students', 'exam_id', 'class_id', 'section_id', 'subject_id', 'subjectNames', 'number_of_exam_parts', 'marks_entry_form', 'exam_types', 'search_info'));
                } else {
                    Toastr::error('No result found or exam setup is not done!', 'Failed');
                    return redirect()->back();
                    // return redirect()->back()->with('message-danger', 'No result found or exam setup is not done!');
                }
                return view('backEnd.examination.masks_register_create', compact('exams', 'classes', 'students', 'exam_id', 'class_id', 'section_id', 'marks_register_subjects', 'assign_subject_ids', 'search_info'));
            }
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function marksRegisterStore(Request $request)
    {
        // return $request->all();
        DB::beginTransaction();
        try {
            $abc = [];
            $class_id = $request->class_id;
            if ($request->section_id != '') {
                $section_id = $request->section_id;
            }
            $subject_id = $request->subject_id;
            $exam_id = $request->exam_id;
            $counter = 0; // Initilize by 0

            foreach ($request->student_ids as $student_id) {
                $sid = $student_id;
                if ($request->section_id == '') {
                    $section_id = AramiscStudent::where('school_id', auth()->user()->school_id)->where('id', $sid)->where('active_status', 1)->first()->section_id;
                }
                $admission_no = ($request->student_admissions[$sid] == null) ? '' : $request->student_admissions[$sid];
                $roll_no = ($request->student_rolls[$sid] == null) ? '' : $request->student_rolls[$sid];

                if (!empty($request->marks[$sid])) {
                    $exam_setup_count = 0;
                    $total_marks_persubject = 0;
                    foreach ($request->marks[$sid] as $part_mark) {
                        $mark_by_exam_part = ($part_mark == null) ? 0 : $part_mark;
                        // 0=If exam part is empty
                        $total_marks_persubject = $total_marks_persubject + $mark_by_exam_part;
                        // $is_absent = ($request->abs[$sid]==null) ? 0 : 1;
                        $exam_setup_id = $request->exam_Sids[$sid][$exam_setup_count];

                        $previous_record = AramiscMarkStore::where([
                            ['class_id', $class_id],
                            ['section_id', $section_id],
                            ['subject_id', $subject_id],
                            ['exam_term_id', $exam_id],
                            ['exam_setup_id', $exam_setup_id],
                            ['student_id', $sid],
                        ])->where('academic_id', getAcademicId())->first();
                        // Is previous record exist ?

                        if ($previous_record == "" || $previous_record == null) {

                            $marks_register = new AramiscMarkStore();
                            $marks_register->exam_term_id = $exam_id;
                            $marks_register->class_id = $class_id;
                            $marks_register->section_id = $section_id;
                            $marks_register->subject_id = $subject_id;
                            $marks_register->student_id = $sid;
                            $marks_register->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $marks_register->total_marks = $mark_by_exam_part;
                            $marks_register->exam_setup_id = $exam_setup_id;
                            if (isset($request->absent_students)) {
                                if (in_array($sid, $request->absent_students)) {
                                    $marks_register->is_absent = 1;
                                } else {
                                    $marks_register->is_absent = 0;
                                }
                            }

                            $marks_register->teacher_remarks = $request->teacher_remarks[$sid][$subject_id];

                            $marks_register->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $marks_register->school_id = Auth::user()->school_id;
                            $marks_register->academic_id = getAcademicId();

                            $marks_register->save();
                            $marks_register->toArray();
                        } else { //If already exists, it will updated
                            $pid = $previous_record->id;
                            $marks_register = AramiscMarkStore::find($pid);
                            $marks_register->total_marks = $mark_by_exam_part;

                            if (isset($request->absent_students)) {
                                if (in_array($sid, $request->absent_students)) {
                                    $marks_register->is_absent = 1;
                                } else {
                                    $marks_register->is_absent = 0;
                                }
                            }

                            $marks_register->teacher_remarks = $request->teacher_remarks[$sid][$subject_id];

                            $marks_register->save();
                        }

                        $exam_setup_count++;
                    } // end part insertion

                    $subject_full_mark = subjectFullMark($request->exam_id, $request->subject_id, $class_id, $section_id);
                    $student_obtained_mark = $total_marks_persubject;
                    $mark_by_persentage = subjectPercentageMark($student_obtained_mark, $subject_full_mark);

                    $mark_grade = AramiscMarksGrade::where([
                        ['percent_from', '<=', $mark_by_persentage],
                        ['percent_upto', '>=', $mark_by_persentage]])
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->first();

                    $abc[] = $total_marks_persubject;

                    $previous_result_record = AramiscResultStore::where([
                        ['class_id', $class_id],
                        ['section_id', $section_id],
                        ['subject_id', $subject_id],
                        ['exam_type_id', $exam_id],
                        ['student_id', $sid],
                    ])->first();

                    if ($previous_result_record == "" || $previous_result_record == null) { //If not result exists, it will create
                        $result_record = new AramiscResultStore();
                        $result_record->class_id = $class_id;
                        $result_record->section_id = $section_id;
                        $result_record->subject_id = $subject_id;
                        $result_record->exam_type_id = $exam_id;
                        $result_record->student_id = $sid;

                        if (isset($request->absent_students)) {
                            if (in_array($sid, $request->absent_students)) {
                                $result_record->is_absent = 1;
                            } else {
                                $result_record->is_absent = 0;
                            }
                        }

                        $result_record->total_marks = $total_marks_persubject;
                        $result_record->total_gpa_point = @$mark_grade->gpa;
                        $result_record->total_gpa_grade = @$mark_grade->grade_name;

                        $result_record->teacher_remarks = $request->teacher_remarks[$sid][$subject_id];

                        $result_record->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $result_record->school_id = Auth::user()->school_id;
                        $result_record->academic_id = getAcademicId();
                        $result_record->save();
                        $result_record->toArray();
                    } else { //If already result exists, it will updated
                        $id = $previous_result_record->id;
                        $result_record = AramiscResultStore::find($id);
                        $result_record->total_marks = $total_marks_persubject;
                        $result_record->total_gpa_point = @$mark_grade->gpa;
                        $result_record->total_gpa_grade = @$mark_grade->grade_name;
                        $result_record->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        if (isset($request->absent_students)) {
                            if (in_array($sid, $request->absent_students)) {
                                $result_record->is_absent = 1;
                            } else {
                                $result_record->is_absent = 0;
                            }
                        }

                        $result_record->teacher_remarks = $request->teacher_remarks[$sid][$subject_id];

                        $result_record->save();
                        $result_record->toArray();
                    }
                } // If student id is valid

            } //end student loop
            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('marks-register-create');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function marksRegisterReportSearch(Request $request)
    {

        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            // 'section' => 'required',
            'subject' => 'required',
        ]);
        try {
            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $exam_id = $request->exam;
            $class_id = $request->class;
            $section_id = $request->section != null ? $request->section : null;
            $subject_id = $request->subject;
            $subjectNames = AramiscSubject::where('id', $subject_id)->first();

            $exam_attendance = AramiscExamAttendance::query();
            if ($request->class != null) {
                $exam_attendance->where('exam_id', $exam_id)->where('class_id', $class_id);
            }
            if ($request->section != null) {
                $exam_attendance->where('section_id', $request->section);
            }

            $exam_attendance = $exam_attendance->where('subject_id', $subject_id)->first();

            if ($exam_attendance) {
                $exam_attendance_child = AramiscExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->first();
            } else {
                Toastr::error('Exam attendance not done yet', 'Failed');
                return redirect()->back();
            }

            $students = AramiscStudent::query();
            if ($request->class != null) {
                $students->where('class_id', $request->class);
            }
            if ($request->section != null) {
                $students->where('section_id', $request->section);
            }
            $students = $students->where('active_status', 1)->where('academic_id', getAcademicId())->get();

            $exam_schedule = AramiscExamSchedule::where('exam_id', $request->exam)->where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', getAcademicId())->first();
            if ($students->count() == 0) {
                Toastr::error('Sorry ! Student is not available Or exam schedule is not set yet.', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('message-danger', 'Sorry ! Student is not available Or exam schedule is not set yet.');
            } else {

                $marks_entry_form = AramiscExamSetup::query();
                if ($request->class != null) {
                    $marks_entry_form->where('exam_term_id', $exam_id)->where('class_id', $class_id);
                }
                if ($request->section != null) {
                    $marks_entry_form->where('section_id', $request->section);
                }
                $marks_entry_form = $marks_entry_form->where('subject_id', $subject_id)->where('academic_id', getAcademicId())->get();

                if ($marks_entry_form->count() > 0) {
                    $number_of_exam_parts = count($marks_entry_form);
                    return view('backEnd.examination.masks_register_search', compact('exams', 'classes', 'students', 'exam_id', 'class_id', 'section_id', 'subject_id', 'subjectNames', 'number_of_exam_parts', 'marks_entry_form', 'exam_types'));
                } else {
                    Toastr::error('Sorry ! Exam setup is not set yet.', 'Failed');
                    return redirect()->back();
                    // return redirect()->back()->with('message-danger', 'Sorry ! Exam schedule is not set yet.');
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function seatPlan()
    {
        try {
            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.seat_plan', compact('exam_types', 'classes', 'subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function seatPlanCreate()
    {
        try {
            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_rooms = AramiscClassRoom::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.seat_plan_create', compact('exam_types', 'classes', 'subjects', 'class_rooms'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function seatPlanSearch(Request $request)
    {

        $request->validate([
            'exam' => 'required',
            'subject' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);
        try {
            $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('active_status', 1)->where('academic_id', getAcademicId())->get();

            if ($students->count() == 0) {
                Toastr::error('No result found', 'Failed');
                return redirect('seat-plan-create');
                // return redirect('seat-plan-create')->with('message-danger', 'No result found');
            }

            $seat_plan_assign = AramiscSeatPlan::where('exam_id', $request->exam)->where('subject_id', $request->subject)->where('class_id', $request->class)->where('section_id', $request->section)->where('date', date('Y-m-d', strtotime($request->date)))->first();

            $seat_plan_assign_childs = [];
            if ($seat_plan_assign != "") {
                $seat_plan_assign_childs = $seat_plan_assign->seatPlanChild;
            }

            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $class_rooms = AramiscClassRoom::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $fill_uped = [];
            foreach ($class_rooms as $class_room) {
                $assigned_student = AramiscSeatPlanChild::where('room_id', $class_room->id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                if ($assigned_student->count() > 0) {
                    $assigned_student = $assigned_student->sum('assign_students');
                    if ($assigned_student >= $class_room->capacity) {
                        $fill_uped[] = $class_room->id;
                    }
                }
            }
            $class_id = $request->class;
            $section_id = $request->section;
            $exam_id = $request->exam;
            $subject_id = $request->subject;
            $date = $request->date;

            return view('backEnd.examination.seat_plan_create', compact('exam_types', 'classes', 'class_rooms', 'students', 'class_id', 'section_id', 'exam_id', 'subject_id', 'seat_plan_assign_childs', 'fill_uped', 'date'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function getExamRoomByAjax(Request $request)
    {
        try {
            $class_rooms = AramiscClassRoom::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $rest_class_rooms = [];
            foreach ($class_rooms as $class_room) {
                $assigned_student = AramiscSeatPlanChild::where('room_id', $class_room->id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                if ($assigned_student->count() > 0) {
                    $assigned_student = $assigned_student->sum('assign_students');
                    if ($assigned_student < $class_room->capacity) {
                        $rest_class_rooms[] = $class_room;
                    }
                } else {
                    $rest_class_rooms[] = $class_room;
                }
            }
            return response()->json([$rest_class_rooms]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function getRoomCapacity(Request $request)
    {
        try {
            // $class_room = AramiscClassRoom::find($request->id);
            if (checkAdmin()) {
                $class_room = AramiscClassRoom::find($request->id);
            } else {
                $class_room = AramiscClassRoom::where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }
            $assigned = AramiscSeatPlanChild::where('room_id', $request->id)->where('date', date('Y-m-d', strtotime($request->date)))->first();
            $assigned_student = 0;
            if ($assigned != '') {
                $assigned_student = AramiscSeatPlanChild::where('room_id', $request->id)->where('date', date('Y-m-d', strtotime($request->date)))->where('start_time', '<=', date('H:i:s', strtotime($request->start_time)))->where('end_time', '>=', date('H:i:s', strtotime($request->end_time)))->sum('assign_students');
            }
            return response()->json([$class_room, $assigned_student]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function seatPlanStore(Request $request)
    {

        $seat_plan_assign = AramiscSeatPlan::where('exam_id', $request->exam_id)->where('subject_id', $request->subject_id)->where('class_id', $request->class_id)->where('section_id', $request->section_id)->first();

        DB::beginTransaction();
        try {
            if ($seat_plan_assign == "") {
                $seat_plan = new AramiscSeatPlan();
            } else {
                $seat_plan = AramiscSeatPlan::where('exam_id', $request->exam_id)->where('subject_id', $request->subject_id)->where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('date', date('Y-m-d', strtotime($request->exam_date)))->first();
            }
            $seat_plan->exam_id = $request->exam_id;
            $seat_plan->subject_id = $request->subject_id;
            $seat_plan->class_id = $request->class_id;
            $seat_plan->section_id = $request->section_id;
            $seat_plan->date = date('Y-m-d', strtotime($request->exam_date));
            $seat_plan->school_id = Auth::user()->school_id;
            $seat_plan->academic_id = getAcademicId();
            $seat_plan->save();
            $seat_plan->toArray();

            if ($seat_plan_assign != "") {
                AramiscSeatPlanChild::where('seat_plan_id', $seat_plan->id)->delete();
            }

            $i = 0;
            foreach ($request->room as $room) {
                $seat_plan_child = new AramiscSeatPlanChild();
                $seat_plan_child->seat_plan_id = $seat_plan->id;
                $seat_plan_child->room_id = $room;
                $seat_plan_child->assign_students = $request->assign_student[$i];
                $seat_plan_child->start_time = date('H:i:s', strtotime($request->start_time));
                $seat_plan_child->end_time = date('H:i:s', strtotime($request->end_time));
                $seat_plan_child->date = date('Y-m-d', strtotime($request->exam_date));
                $seat_plan_child->school_id = Auth::user()->school_id;
                $seat_plan_child->academic_id = getAcademicId();
                $seat_plan_child->save();
                $i++;
            }
            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('seat-plan');
            // return redirect('seat-plan')->with('message-success', 'Seat Plan has been assigned successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
        }
    }

    public function seatPlanReportSearch(Request $request)
    {
        try {
            $seat_plans = AramiscSeatPlan::query();
            $seat_plans->where('active_status', 1);
            if ($request->exam != "") {
                $seat_plans->where('exam_id', $request->exam);
            }
            if ($request->subject != "") {
                $seat_plans->where('subject_id', $request->subject);
            }

            if ($request->class != "") {
                $seat_plans->where('class_id', $request->class);
            }

            if ($request->section != "") {
                $seat_plans->where('section_id', $request->section);
            }
            if ($request->date != "") {
                $seat_plans->where('date', date('Y-m-d', strtotime($request->date)));
            }
            $seat_plans = $seat_plans->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if ($seat_plans->count() == 0) {
                Toastr::success('No Record Found', 'Success');
                return redirect('seat-plan');
            }

            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.examination.seat_plan', compact('exams', 'classes', 'subjects', 'seat_plans'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendance()
    {

        try {
            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

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
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.exam_attendance', compact('exams', 'classes', 'subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendanceAeportSearch(AramiscExamAttendanceSearchRequest $request)
    {
        try {
            if (moduleStatusCheck('University')) {
                $data = [];

                $un_session = UnSession::find($request->un_session_id);
                $un_faculty = UnFaculty::find($request->un_faculty_id);
                $un_department = UnDepartment::find($request->un_department_id);
                $un_academic = UnAcademicYear::find($request->un_academic_id);
                $un_semester = UnSemester::find($request->un_semester_id);
                $un_semester_label = UnSemesterLabel::find($request->un_semester_label_id);
                $un_section = AramiscSection::find($request->un_section_id);

                $AramiscExam = AramiscExam::query();
                $aramisc_exam = universityFilter($AramiscExam, $request)
                    ->where('exam_type_id', $request->exam_type)
                    ->where('un_subject_id', $request->subject_id)
                    ->first();

                $AramiscExamAttendance = AramiscExamAttendance::query();
                $exam_attendance = universityFilter($AramiscExamAttendance, $request)
                    ->where('un_subject_id', $request->subject_id)
                    ->where('exam_id', $aramisc_exam->id)
                    ->first();

                if ($exam_attendance == "") {
                    Toastr::success('No Record Found', 'Success');
                    return redirect('exam-attendance');
                }

                $exam_attendance_childs = [];
                if ($exam_attendance != "") {
                    $exam_attendance_childs = $exam_attendance->examAttendanceChild;
                }

                $exams = AramiscExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $subjectName = UnSubject::find($request->subject_id);

                $data['un_semester_label_id'] = $request->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data = $interface->oldValueSelected($request);

                return view('backEnd.examination.exam_attendance', compact(
                    'exams',
                    'exam_attendance_childs',
                    'un_session',
                    'un_faculty',
                    'un_department',
                    'un_academic',
                    'un_semester',
                    'un_semester_label',
                    'un_section',
                    'subjectName',
                ))->with($data);

            } else {

                $exam = AramiscExam::where('exam_type_id',$request->exam)
                        ->where('class_id',$request->class)
                        ->when($request->section, function($q)use($request){
                            $q->where('section_id',$request->section);
                        })
                        ->where('subject_id',$request->subject)
                        ->first();

                $exam_attendance = AramiscExamAttendance::query();
                if (!empty($request->section)) {
                    $exam_attendance->where('section_id', $request->section);
                }
                $exam_attendance = $exam_attendance->where('class_id', $request->class)
                    ->where('subject_id', $request->subject)
                    ->where('exam_id', $exam->id)
                    ->first();

                if ($exam_attendance == "") {
                    Toastr::error('No Record Found', 'Success');
                    return redirect('exam-attendance');
                }

                $exam_attendance_childs = [];
                if ($exam_attendance != "") {
                    $exam_attendance_childs = $exam_attendance->examAttendanceChild;
                }

                $exams = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
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
                $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                return view('backEnd.examination.exam_attendance', compact('exams', 'classes', 'subjects', 'exam_attendance_childs'));
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendanceCreate()
    {
        try {
            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

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
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.examination.exam_attendance_create', compact('exams', 'classes', 'subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendanceSearch(Request $request)
    {
        //  return $request->all();
        $request->validate([
            'exam' => 'required',
            'subject' => 'required',
            'class' => 'required',
            // 'section' => 'required'
        ]);
        try {

            $exam_schedules = AramiscExamSchedule::query();
            if ($request->class != null) {
                $exam_schedules->where('class_id', $request->class);
            }
            if ($request->section != null) {
                $exam_schedules->where('section_id', $request->section);
            }

            $exam_schedules = $exam_schedules->where('exam_term_id', $request->exam)
                ->where('subject_id', $request->subject)
                ->count();

            if ($exam_schedules == 0) {
                Toastr::error('You have to create exam schedule first', 'Failed');
                return redirect('exam-attendance-create');
            }

            $students = AramiscStudent::query();
            if ($request->class != null) {
                $students->where('class_id', $request->class);
            }
            if ($request->section != null) {
                $students->where('section_id', $request->section);
            }

            $students = $students->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->get();

            if ($students->count() == 0) {
                Toastr::error('No Record Found', 'Failed');
                return redirect('exam-attendance-create');
            }
            if ($request->section == null) {
                $exam_attendance = AramiscExamAttendance::where('class_id', $request->class)
                    ->where('subject_id', $request->subject)
                    ->where('exam_id', $request->exam)
                    ->first();
            } else {
                $exam_attendance = AramiscExamAttendance::where('class_id', $request->class)
                    ->where('section_id', $request->section)
                    ->where('subject_id', $request->subject)
                    ->where('exam_id', $request->exam)
                    ->first();
            }

            $exam_attendance_childs = [];
            if ($exam_attendance != "") {
                $exam_attendance_childs = $exam_attendance->examAttendanceChild;
            }
            $exam_attendance_childs;

            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

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

            $subjects = AramiscSubject::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exam_id = $request->exam;
            $subject_id = $request->subject;
            $class_id = $request->class;

            if (($request->section != null)) {
                $section_id = $request->section;
            } else {
                $section_id = null;
            }

            // return search result
            $class_info = AramiscClass::find($request->class);
            if (($request->section != null)) {
                $section_info = AramiscSection::find($request->section);
                $section_info = $section_info->section_name;
            } else {
                $section_info = 'All Sections';

            }
            $subject_info = AramiscSubject::find($request->subject);

            $search_info['class_name'] = $class_info->class_name;
            $search_info['section_name'] = $section_info;
            $search_info['subject_name'] = $subject_info->subject_name;

            return view('backEnd.examination.exam_attendance_create', compact('exams', 'classes', 'subjects', 'students', 'exam_id', 'subject_id', 'class_id', 'section_id', 'exam_attendance_childs', 'search_info'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendanceStore(Request $request)
    {
        try {

            if ($request->section_id == '') {
                $alreday_assigned = AramiscExamAttendance::where('class_id', $request->class_id)->where('subject_id', $request->subject_id)->where('exam_id', $request->exam_id)->first();

            } else {
                $alreday_assigned = AramiscExamAttendance::where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('subject_id', $request->subject_id)->where('exam_id', $request->exam_id)->first();

            }
            DB::beginTransaction();
           // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            try {
                if ($request->section_id != '') {
                    if ($alreday_assigned == "") {
                        $exam_attendance = new AramiscExamAttendance();
                    } else {
                        $exam_attendance = AramiscExamAttendance::where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('subject_id', $request->subject_id)->where('exam_id', $request->exam_id)->first();
                    }

                    $exam_attendance->exam_id = $request->exam_id;
                    $exam_attendance->subject_id = $request->subject_id;
                    $exam_attendance->class_id = $request->class_id;
                    $exam_attendance->section_id = $request->section_id;
                    $exam_attendance->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $exam_attendance->school_id = Auth::user()->school_id;
                    $exam_attendance->academic_id = getAcademicId();
                    $exam_attendance->save();
                    $exam_attendance->toArray();

                    if ($alreday_assigned != "") {
                        AramiscExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->delete();
                    }

                    foreach ($request->id as $student) {
                        $exam_attendance_child = new AramiscExamAttendanceChild();
                        $exam_attendance_child->exam_attendance_id = $exam_attendance->id;
                        $exam_attendance_child->student_id = $student;
                        $exam_attendance_child->attendance_type = $request->attendance[$student];
                        $exam_attendance_child->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $exam_attendance_child->school_id = Auth::user()->school_id;
                        $exam_attendance_child->academic_id = getAcademicId();
                        $exam_attendance_child->save();
                    }
                } else {
                    $classSections = AramiscAssignSubject::where('class_id', $request->class_id)
                        ->where('subject_id', $request->subject_id)
                        ->where('school_id', auth()->user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->distinct(['section_id', 'subject_id'])
                        ->get();
                    foreach ($classSections as $section) {
                        if ($alreday_assigned == "") {

                            $exam_attendance = new AramiscExamAttendance();
                        } else {
                            $exam_attendance = AramiscExamAttendance::where('class_id', $request->class_id)->where('section_id', $section->section_id)
                                ->where('subject_id', $request->subject_id)->where('exam_id', $request->exam_id)->first();
                        }

                        $exam_attendance->exam_id = $request->exam_id;
                        $exam_attendance->subject_id = $request->subject_id;
                        $exam_attendance->class_id = $request->class_id;
                        $exam_attendance->section_id = $section->section_id;
                        $exam_attendance->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $exam_attendance->school_id = Auth::user()->school_id;
                        $exam_attendance->academic_id = getAcademicId();
                        $exam_attendance->save();
                        $exam_attendance->toArray();

                        if ($alreday_assigned != "") {
                            AramiscExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->delete();
                        }

                        foreach ($request->id as $student) {
                            $exam_attendance_child = new AramiscExamAttendanceChild();
                            $exam_attendance_child->exam_attendance_id = $exam_attendance->id;
                            $exam_attendance_child->student_id = $student;
                            $exam_attendance_child->attendance_type = $request->attendance[$student];
                            $exam_attendance_child->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $exam_attendance_child->school_id = Auth::user()->school_id;
                            $exam_attendance_child->academic_id = getAcademicId();
                            $exam_attendance_child->save();
                        }
                    }
                }

                DB::commit();
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-attendance-create');
            } catch (\Exception $e) {
                DB::rollback();
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function sendMarksBySms()
    {
        $exams = AramiscExamType::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

        if (teacherAccess()) {
            $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
            $classes = $teacher_info->classes;
        } else {
            $classes = AramiscClass::get();
        }
        return view('backEnd.examination.send_marks_by_sms', compact('exams', 'classes'));
    }
    function universitySendSms($request){
        $request->validate([
            'un_session_id' => 'required',
            'un_semester_label_id' => 'required',
            'exam_type' => 'required',
            'receiver'=>'required',

        ]);
        try {
            $examType = AramiscExamType::find($request->exam_type);
            $students = StudentRecord::where('un_semester_label_id',$request->un_semester_label_id)
                ->where('un_academic_id', getAcademicId())
                ->with('student')
                ->get();
            $marks = AramiscMarkStore::where('exam_term_id', $request->exam_type)
                ->whereIn('student_record_id', $students->pluck('id')->toArray())
                ->get();

            $subjectMarkinfos = [];
            foreach($marks as $mark){
                $subjectMarkinfos [$mark->student_record_id][]= ['subject'=>$mark->subjectName->subject_name, 'mark'=>$mark->total_marks];
            }
            foreach($subjectMarkinfos as $key => $subjectMarkinfo){
                $students = StudentRecord::with('student')->find($key);
                $subjects = '';
                $marks = '';
                $subjectMarks = '';
                $total_marks = 0;
                foreach($subjectMarkinfo as $subjectMarkinf){
                    $subjects .= $subjectMarkinf['subject']. ',';
                    $marks .= $subjectMarkinf['mark']. ',';
                    $total_marks += $subjectMarkinf['mark'];
                    $subjectMarks .= $subjectMarkinf['subject'] . "-" . $subjectMarkinf['mark']. ',';
                }
                $compact['class_name'] = $students->unSemesterLabel->name;
                $compact['section_name'] = $students->section->section_name;
                $compact['user_email'] = $students->student->email;
                $compact['marks'] = $marks;
                $compact['total_mark'] = $total_marks;
                $compact['subject_marks'] = $subjectMarks;
                $compact['exam_type'] = $examType->title;
                $compact['school_name'] = generalSetting()->school_name;
                if($request->receiver == 'students'){
                    @send_sms($students->student->mobile, 'exam_mark_student', $compact);
                }else{
                    $compact['parent_name'] = $students->student->parents->guardians_name;
                    @send_sms($students->student->parents->guardians_mobile, 'exam_mark_parent', $compact);
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->route('send_marks_by_sms');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function sendMarksBySmsStore(Request $request)
    {
      if(moduleStatusCheck('University')){
        return $this->universitySendSms($request);
      }else{
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'receiver' => 'required',
        ]);
        try {
            $examType = AramiscExamType::find($request->exam);
            $students = StudentRecord::where('class_id',$request->class)
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)
                ->with('student')
                ->get();

            $marks = AramiscMarkStore::where('exam_term_id', $request->exam)
                ->whereIn('student_record_id', $students->pluck('id')->toArray())
                ->get();

            $subjectMarkinfos = [];
            foreach($marks as $mark){
                $subjectMarkinfos [$mark->student_record_id][]= ['subject'=>$mark->subjectName->subject_name, 'mark'=>$mark->total_marks];
            }

            foreach($subjectMarkinfos as $key => $subjectMarkinfo){
                $students = StudentRecord::with('student')->find($key);
                $subjects = '';
                $marks = '';
                $subjectMarks = '';
                $total_marks = 0;
                foreach($subjectMarkinfo as $subjectMarkinf){
                    $subjects .= $subjectMarkinf['subject']. ',';
                    $marks .= $subjectMarkinf['mark']. ',';
                    $total_marks += $subjectMarkinf['mark'];
                    $subjectMarks .= $subjectMarkinf['subject'] . "-" . $subjectMarkinf['mark']. ',';
                }
                $compact['class_name'] = $students->class->class_name;
                $compact['section_name'] = $students->section->section_name;
                $compact['user_email'] = $students->student->email;
                $compact['marks'] = $marks;
                $compact['total_mark'] = $total_marks;
                $compact['subject_marks'] = $subjectMarks;
                $compact['exam_type'] = $examType->title;
                $compact['school_name'] = generalSetting()->school_name;
                if($request->receiver == 'students'){
                    @send_sms($students->student->mobile, 'exam_mark_student', $compact);
                }else{
                    $compact['parent_name'] = $students->student->parents->guardians_name;
                    @send_sms($students->student->parents->guardians_mobile, 'exam_mark_parent', $compact);
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->route('send_marks_by_sms');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
      }
    }

    public function meritListReport(Request $request)
    {
        try {
            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

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
            return view('backEnd.reports.merit_list_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function reportsTabulationSheet()
    {
        try {
            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.reports.report_tabulation_sheet', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function reportsTabulationSheetSearch(Request $request)
    {
        try {
            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.reports.report_tabulation_sheet', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //end tabulation sheet report

    public function make_merit_list($InputClassId, $InputSectionId, $InputExamId, Request $request)
    {
        $iid = time();
        $class = AramiscClass::find($InputClassId);
        $section = AramiscSection::find($InputSectionId);
        $exam = AramiscExamType::find($InputExamId);
        $is_data = DB::table('aramisc_mark_stores')->where([['class_id', $InputClassId], ['section_id', $InputSectionId], ['exam_term_id', $InputExamId]])->first();
        if (empty($is_data)) {
            Toastr::error('Your result is not found!', 'Failed');
            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Your result is not found!');
        }
        $exams = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
        $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
        $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
        $assign_subjects = AramiscAssignSubject::where('class_id', $class->id)->where('section_id', $section->id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
        $class_name = $class->class_name;
        $exam_name = $exam->title;
        $eligible_subjects = AramiscAssignSubject::where('class_id', $InputClassId)->where('section_id', $InputSectionId)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
        $eligible_students = AramiscStudent::where('class_id', $InputClassId)->where('section_id', $InputSectionId)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

        //all subject list in a specific class/section
        $subject_ids = [];
        $subject_strings = '';
        $marks_string = '';
        foreach ($eligible_students as $SingleStudent) {
            foreach ($eligible_subjects as $subject) {
                $subject_ids[] = $subject->subject_id;
                $subject_strings = (empty($subject_strings)) ? $subject->subject->subject_name : $subject_strings . ',' . $subject->subject->subject_name;

                $getMark = AramiscResultStore::where([
                    ['exam_type_id', $InputExamId],
                    ['class_id', $InputClassId],
                    ['section_id', $InputSectionId],
                    ['student_id', $SingleStudent->id],
                    ['subject_id', $subject->subject_id],
                ])->first();
                if ($getMark == "") {
                    Toastr::error('Please register marks for all students.!', 'Failed');
                    return redirect()->back();
                    // return redirect()->back()->with('message-danger', 'Please register marks for all students.!');
                }
                if ($marks_string == "") {
                    if ($getMark->total_marks == 0) {
                        $marks_string = '0';
                    } else {
                        $marks_string = $getMark->total_marks;
                    }
                } else {
                    $marks_string = $marks_string . ',' . $getMark->total_marks;
                }
            }

            //end subject list for specific section/class

            $results = AramiscResultStore::where([
                ['exam_type_id', $InputExamId],
                ['class_id', $InputClassId],
                ['section_id', $InputSectionId],
                ['student_id', $SingleStudent->id],
            ])->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $is_absent = AramiscResultStore::where([
                ['exam_type_id', $InputExamId],
                ['class_id', $InputClassId],
                ['section_id', $InputSectionId],
                ['is_absent', 1],
                ['student_id', $SingleStudent->id],
            ])->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $total_gpa_point = AramiscResultStore::where([
                ['exam_type_id', $InputExamId],
                ['class_id', $InputClassId],
                ['section_id', $InputSectionId],
                ['student_id', $SingleStudent->id],
            ])->sum('total_gpa_point');

            $total_marks = AramiscResultStore::where([
                ['exam_type_id', $InputExamId],
                ['class_id', $InputClassId],
                ['section_id', $InputSectionId],
                ['student_id', $SingleStudent->id],
            ])->sum('total_marks');

            $sum_of_mark = ($total_marks == 0) ? 0 : $total_marks;
            $average_mark = ($total_marks == 0) ? 0 : floor($total_marks / $results->count()); //get average number
            $is_absent = (count($is_absent) > 0) ? 1 : 0; //get is absent ? 1=Absent, 0=Present
            $total_GPA = ($total_gpa_point == 0) ? 0 : $total_gpa_point / $results->count();
            $exart_gp_point = number_format($total_GPA, 2, '.', ''); //get gpa results
            $full_name = $SingleStudent->full_name; //get name
            $admission_no = $SingleStudent->admission_no; //get admission no
            $student_id = $SingleStudent->id; //get admission no
            $is_existing_data = AramiscTemporaryMeritlist::where([['admission_no', $admission_no], ['class_id', $InputClassId], ['section_id', $InputSectionId], ['exam_id', $InputExamId]])->first();
            if (empty($is_existing_data)) {
                $insert_results = new AramiscTemporaryMeritlist();
            } else {
                $insert_results = AramiscTemporaryMeritlist::find($is_existing_data->id);
            }
            $insert_results->student_name = $full_name;
            $insert_results->admission_no = $admission_no;
            $insert_results->subjects_string = $subject_strings;
            $insert_results->marks_string = $marks_string;
            $insert_results->total_marks = $sum_of_mark;
            $insert_results->average_mark = $average_mark;
            $insert_results->gpa_point = $exart_gp_point;
            $insert_results->iid = $iid;
            $insert_results->student_id = $student_id;
            $markGrades = AramiscMarksGrade::where([['from', '<=', $exart_gp_point], ['up', '>=', $exart_gp_point]])->where('school_id', Auth::user()->school_id)->first();

            if ($is_absent == "") {
                $insert_results->result = $markGrades->grade_name;
            } else {
                $insert_results->result = 'F';
            }
            $insert_results->section_id = $InputSectionId;
            $insert_results->class_id = $InputClassId;
            $insert_results->exam_id = $InputExamId;
            $insert_results->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $insert_results->school_id = Auth::user()->school_id;
            $insert_results->academic_id = getAcademicId();
            $insert_results->save();

            $subject_strings = "";
            $marks_string = "";
            $total_marks = 0;
            $average = 0;
            $exart_gp_point = 0;
            $admission_no = 0;
            $full_name = "";
        } //end loop eligible_students

        $first_data = AramiscTemporaryMeritlist::where('iid', $iid)->first();
        $subjectlist = explode(',', $first_data->subjects_string);
        $allresult_data = AramiscTemporaryMeritlist::where('iid', $iid)->orderBy('gpa_point', 'desc')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
        $merit_serial = 1;
        foreach ($allresult_data as $row) {
            $D = AramiscTemporaryMeritlist::where('iid', $iid)->where('id', $row->id)->first();
            $D->merit_order = $merit_serial++;
            $D->save();
        }

        $allresult_data = AramiscTemporaryMeritlist::orderBy('merit_order', 'asc')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();


        $data['iid'] = $iid;
        $data['exams'] = $exams;
        $data['classes'] = $classes;
        $data['subjects'] = $subjects;
        $data['class'] = $class;
        $data['section'] = $section;
        $data['exam'] = $exam;
        $data['subjectlist'] = $subjectlist;
        $data['allresult_data'] = $allresult_data;
        $data['class_name'] = $class_name;
        $data['assign_subjects'] = $assign_subjects;
        $data['exam_name'] = $exam_name;
        $data['InputClassId'] = $InputClassId;
        $data['InputExamId'] = $InputExamId;
        $data['InputSectionId'] = $InputSectionId;
        return $data;
    }

    public function meritListReportSearch(MeritListReportRequest $request)
    {
        try {
            if (moduleStatusCheck('University')) {
                $common = new ExamCommonController();
                return $common->meritListReport((object)$request->all());
            } else {
                $iid = time();
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                if ($request->method() == 'POST') {
                    $InputClassId = $request->class;
                    $InputExamId = $request->exam;
                    $InputSectionId = $request->section;

                    $class = AramiscClass::with('academic')->find($InputClassId);
                    $section = AramiscSection::find($InputSectionId);
                    $exam = AramiscExamType::find($InputExamId);

                    $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $request->class)->first();

                    $is_data = DB::table('aramisc_mark_stores')
                        ->where([
                            ['class_id', $InputClassId],
                            ['section_id', $InputSectionId],
                            ['exam_term_id', $InputExamId]])
                        ->first();
                    if (empty($is_data)) {
                        Toastr::error('Your result is not found!', 'Failed');
                        return redirect()->back();
                    }

                    $examSubjects = AramiscExam::where([['exam_type_id', $InputExamId], ['section_id', $InputSectionId], ['class_id', $InputClassId]])
                        ->where('school_id', Auth::user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->get();

                    $examSubjectIds = [];
                    foreach ($examSubjects as $examSubject) {
                        $examSubjectIds[] = $examSubject->subject_id;
                    }

                    $exams = AramiscExamType::where('active_status', 1)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->get();

                    $classes = AramiscClass::where('active_status', 1)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->get();

                    $subjects = AramiscSubject::where('active_status', 1)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->get();

                    $assign_subjects = AramiscAssignSubject::where('class_id', $class->id)
                        ->where('section_id', $section->id)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->whereIn('subject_id', $examSubjectIds)
                        ->get();

                    $class_name = $class->class_name;

                    $exam_name = $exam->title;

                    $eligible_subjects = AramiscAssignSubject::where('class_id', $InputClassId)
                        ->where('section_id', $InputSectionId)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->whereIn('subject_id', $examSubjectIds)
                        ->get();
                    $student_ids = AramiscStudentReportController::classSectionStudent($request);
                    $eligible_students = AramiscStudent::whereIn('id', $student_ids)->where('school_id', Auth::user()->school_id)
                        ->where('active_status', 1)->get();
                    $subject_total_mark = 0;
                    $failgpa = AramiscMarksGrade::where('active_status', 1)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->min('gpa');

                    $failgpaname = AramiscMarksGrade::where('active_status', 1)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->where('gpa', $failgpa)
                        ->first();

                    $maxGpa = AramiscMarksGrade::where('active_status', 1)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->max('gpa');
                    //all subject list in a specific class/section
                    $subject_ids = [];
                    $subject_strings = '';
                    $subject_id_strings = '';
                    $marks_string = '';
                    foreach ($eligible_students as $SingleStudent) {
                        foreach ($eligible_subjects as $subject) {
                            $subject_ids[] = $subject->subject_id;
                            $subject_strings = (empty($subject_strings)) ? $subject->subject->subject_name : $subject_strings . ',' . $subject->subject->subject_name;
                            $subject_id_strings = (empty($subject_id_strings)) ? $subject->subject_id : $subject_id_strings . ',' . $subject->subject_id;
                            $getMark = AramiscResultStore::where([
                                ['exam_type_id', $InputExamId],
                                ['class_id', $InputClassId],
                                ['section_id', $InputSectionId],
                                ['student_id', $SingleStudent->id],
                                ['subject_id', $subject->subject_id],
                            ])->first();
                            if ($getMark == "") {
                                Toastr::error('Please register marks for all students & all subjects.!', 'Failed');
                                return redirect()->back();
                            }

                            $subject_total_mark += subjectFullMark($InputExamId, $subject->subject_id, $InputClassId, $InputSectionId);
                            $subject_total_mark = AramiscExam::where('class_id', $InputClassId)->where('section_id', $InputSectionId)->where('exam_type_id', $InputExamId)->where('subject_id', $subject->subject_id)->first('exam_mark')->exam_mark;
                            $obtain_mark = $getMark->total_marks;

                            if (generalSetting()->result_type == 'mark') {
                                if ($obtain_mark != 0) {
                                    $obtain_mark = (($obtain_mark * 100) / $subject_total_mark);
                                }
                            }

                            if ($marks_string == "") {
                                if ($getMark->total_marks == 0) {
                                    $marks_string = '0';
                                } else {
                                    $marks_string = $obtain_mark;
                                }
                            } else {
                                $marks_string = $marks_string . ',' . $obtain_mark;
                            }
                        }


                        //end subject list for specific section/class

                        $results = AramiscResultStore::where([
                            ['exam_type_id', $InputExamId],
                            ['class_id', $InputClassId],
                            ['section_id', $InputSectionId],
                            ['student_id', $SingleStudent->id],
                        ])->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

                        $is_absent = AramiscResultStore::where([
                            ['exam_type_id', $InputExamId],
                            ['class_id', $InputClassId],
                            ['section_id', $InputSectionId],
                            ['is_absent', 1],
                            ['student_id', $SingleStudent->id],
                        ])->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

                        $total_gpa_point = AramiscResultStore::where([
                            ['exam_type_id', $InputExamId],
                            ['class_id', $InputClassId],
                            ['section_id', $InputSectionId],
                            ['student_id', $SingleStudent->id],
                        ])->sum('total_gpa_point');

                        $total_marks = AramiscResultStore::where([
                            ['exam_type_id', $InputExamId],
                            ['class_id', $InputClassId],
                            ['section_id', $InputSectionId],
                            ['student_id', $SingleStudent->id],
                        ])->sum('total_marks');

                        $dat = array();
                        $sum_of_mark = ($total_marks == 0) ? 0 : $total_marks;

                        $average_mark = ($total_marks == 0) ? 0 : floor($total_marks / $results->count()); //get average number
                        $is_absent = (count($is_absent) > 0) ? 1 : 0; //get is absent ? 1=Absent, 0=Present

                        foreach ($results as $key => $gpa_result) {
                            $da = DB::table('aramisc_optional_subject_assigns')->where(['student_id' => $gpa_result->student_id, 'subject_id' => $gpa_result->subject_id])->count();
                            if ($da < 1) {
                                $grade_gpa = markGpa($subject_total_mark);

                                if ($grade_gpa->grade_name == $failgpaname) {
                                    array_push($dat, $grade_gpa->gpa);
                                }
                            }
                        }
                        $total_GPA = ($total_gpa_point == 0) ? 0 : $total_gpa_point / $results->count();
                        if (!empty($dat)) {
                            $exart_gp_point = $dat['0'];
                        } else {

                            $exart_gp_point = number_format($total_GPA, 2, '.', ''); //get gpa results
                        }
                        $student_gpa_point = number_format($total_GPA, 2, '.', '');

                        $full_name = $SingleStudent->full_name; //get name
                        $admission_no = $SingleStudent->admission_no; //get admission no
                        $roll_no = $SingleStudent->roll_no; //get admission no
                        $student_id = $SingleStudent->id; //get admission no

                        $is_existing_data = AramiscTemporaryMeritlist::where([
                            ['admission_no', $admission_no],
                            ['class_id', $InputClassId],
                            ['section_id', $InputSectionId],
                            ['exam_id', $InputExamId]])
                            ->first();

                        // return $is_existing_data;
                        if (empty($is_existing_data)) {
                            $insert_results = new AramiscTemporaryMeritlist();
                        } else {
                            $insert_results = AramiscTemporaryMeritlist::find($is_existing_data->id);
                        }
                        // $insert_results                     = new AramiscTemporaryMeritlist();
                        $insert_results->merit_order = $student_gpa_point;
                        $insert_results->student_name = $full_name;
                        $insert_results->admission_no = $admission_no;
                        $insert_results->roll_no = $roll_no;
                        $insert_results->subjects_id_string = implode(',', array_unique($subject_ids));
                        $insert_results->subjects_string = $subject_strings;
                        $insert_results->marks_string = $marks_string;
                        $insert_results->total_marks = $sum_of_mark;
                        $insert_results->average_mark = $average_mark;
                        $insert_results->gpa_point = $exart_gp_point;
                        $insert_results->iid = $iid;
                        $insert_results->student_id = $SingleStudent->id;
                        $markGrades = getGrade($exart_gp_point);

                        $insert_results->result = (is_null($is_absent)) ? "F" : @$markGrades->grade_name;

                        $insert_results->section_id = $InputSectionId;
                        $insert_results->class_id = $InputClassId;
                        $insert_results->exam_id = $InputExamId;
                        $insert_results->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $insert_results->school_id = Auth::user()->school_id;
                        $insert_results->academic_id = getAcademicId();
                        $insert_results->save();

                        $subject_strings = "";
                        $marks_string = "";
                        $total_marks = 0;
                        $average = 0;
                        $exart_gp_point = 0;
                        $admission_no = 0;
                        $full_name = "";
                    } //end loop eligible_students

                    $first_data = AramiscTemporaryMeritlist::where('iid', $iid)->first();

                    $subjectlist = explode(',', @$first_data->subjects_string);

                    $meritListSettings = CustomResultSetting::first('merit_list_setting')->merit_list_setting;

                    if ($meritListSettings == "total_grade") {
                        $allresult_data = AramiscTemporaryMeritlist::where('exam_id', '=', $InputExamId)
                            ->where('academic_id', getAcademicId())
                            ->where('school_id', Auth::user()->school_id)
                            ->where('class_id', $class->id)
                            ->where('section_id', $section->id)
                            ->orderBy('merit_order', 'desc')
                            ->get();

                    } elseif($meritListSettings == "total_mark") {
                        $allresult_data = AramiscTemporaryMeritlist::where('exam_id', '=', $InputExamId)
                            ->where('academic_id', getAcademicId())
                            ->where('school_id', Auth::user()->school_id)
                            ->where('class_id', $class->id)
                            ->where('section_id', $section->id)
                            ->orderBy('total_marks', 'desc')
                            ->get();

                    }else{
                        $allresult_data = AramiscTemporaryMeritlist::where('exam_id', '=', $InputExamId)
                            ->where('academic_id', getAcademicId())
                            ->where('school_id', Auth::user()->school_id)
                            ->where('class_id', $class->id)
                            ->where('section_id', $section->id)
                            ->orderBy('roll_no', 'asc')
                            ->get();
                    }

                    $exam_content = AramiscExamSetting::where('exam_type', $InputExamId)
                        ->where('active_status', 1)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->first();
                    if ($optional_subject_setup == '') {
                        return view('backEnd.reports.merit_list_report_normal', compact('iid', 'exams', 'classes', 'subjects', 'class', 'section', 'exam', 'subjectlist', 'allresult_data', 'class_name', 'assign_subjects', 'exam_name', 'InputClassId', 'InputExamId', 'InputSectionId', 'optional_subject_setup', 'failgpaname', 'subject_total_mark', 'exam_content'));
                    } else {
                        return view('backEnd.reports.merit_list_report', compact('iid', 'exams', 'classes', 'subjects', 'class', 'section', 'exam', 'subjectlist', 'allresult_data', 'class_name', 'assign_subjects', 'exam_name', 'InputClassId', 'InputExamId', 'InputSectionId', 'optional_subject_setup', 'failgpaname', 'failgpa', 'maxGpa', 'exam_content'));
                    }
                }
            }
        } catch (\Exception $e) {
            $msg = str_replace("'", " ", $e->getMessage());
            Toastr::error($msg, 'Failed');
            return redirect()->back();
        }
    }

    public function meritListPrint($exam_id, $class_id, $section_id)
    {
        set_time_limit(2700);
        try {
            // $iid = time();
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // $emptyResult = AramiscTemporaryMeritlist::truncate();

            $InputClassId = $class_id;
            $InputExamId = $exam_id;
            $InputSectionId = $section_id;
            $exam_content = AramiscExamSetting::where('exam_type', $InputExamId)
                ->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $class = AramiscClass::with('academic')->find($InputClassId);
            $section = AramiscSection::find($InputSectionId);
            $exam = AramiscExamType::find($InputExamId);

            // $is_data = DB::table('aramisc_mark_stores')->where([['class_id', $InputClassId], ['section_id', $InputSectionId], ['exam_term_id', $InputExamId]])->first();

            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $subjects = AramiscSubject::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $examSubjects = AramiscExam::where([['section_id', $InputSectionId], ['class_id', $InputClassId]])
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $examSubjectIds = [];
            foreach ($examSubjects as $examSubject) {
                $examSubjectIds[] = $examSubject->subject_id;
            }

            $assign_subjects = AramiscAssignSubject::where('class_id', $class->id)
                ->where('section_id', $section->id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->whereIn('subject_id', $examSubjectIds)
                ->get();
            foreach ($assign_subjects as $subjects) {
                $subject = $subjects->subject_id;

            }

            $subject_total_mark = subjectFullMark($InputExamId, $subject, $class->id, $section->id);

            $class_name = $class->class_name;
            $exam_name = $exam->title;

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $class_id)->first();

            $allresult_dat = AramiscTemporaryMeritlist::orderBy('merit_order', 'asc')
                ->where(['exam_id' => $exam_id,
                    'class_id' => $class_id,
                    'section_id' => $section_id])
                ->where('academic_id', getAcademicId())
                ->first();

            $meritListSettings = CustomResultSetting::first('merit_list_setting')->merit_list_setting;

            if ($meritListSettings == "total_grade") {
                $allresult_data = AramiscTemporaryMeritlist::where(
                    ['exam_id' => $exam_id,
                        'class_id' => $class_id,
                        'section_id' => $section_id])
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->orderBy('merit_order', 'desc')
                    ->get();
            } elseif($meritListSettings == "total_mark") {
                $allresult_data = AramiscTemporaryMeritlist::where(
                    ['exam_id' => $exam_id,
                        'class_id' => $class_id,
                        'section_id' => $section_id])
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->orderBy('total_marks', 'desc')
                    ->get();
            }else{
                $allresult_data = AramiscTemporaryMeritlist::where(
                    ['exam_id' => $exam_id,
                        'class_id' => $class_id,
                        'section_id' => $section_id])
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->orderBy('roll_no', 'asc')
                    ->get();
            }

            $grades = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $failgpa = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $failgpaname = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $failgpa)
                ->first();

            $maxGpa = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->max('gpa');

            // $allresult_data = AramiscTemporaryMeritlist::orderBy('merit_order', 'asc')->where('academic_id', getAcademicId())->where('school_id',Auth::user()->school_id)->get();
            $subjectlist = explode(',', $allresult_dat->subjects_string);

            return view('backEnd.reports.merit_list_report_print', compact('exams', 'classes', 'subjects', 'class', 'section', 'exam', 'subjectlist', 'allresult_data', 'class_name', 'assign_subjects', 'exam_name', 'optional_subject_setup', 'subject_total_mark', 'failgpa', 'maxGpa', 'failgpaname', 'InputClassId', 'InputExamId', 'InputSectionId', 'exam_content'));

            $pdf = Pdf::loadView(
                'backEnd.reports.merit_list_report_print',
                [
                    'exams' => $exams,
                    'classes' => $classes,
                    'subjects' => $subjects,
                    'class' => $class,
                    'section' => $section,
                    'exam' => $exam,
                    'subjectlist' => $subjectlist,
                    'allresult_data' => $allresult_data,
                    'class_name' => $class_name,
                    'assign_subjects' => $assign_subjects,
                    'exam_name' => $exam_name,
                    'grades' => $grades,
                    'optional_subject_setup' => $optional_subject_setup,
                    'exam_content' => $exam_content
                ]
            )->setPaper('A4', 'landscape');

            return $pdf->stream('student_merit_list.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function markSheetReport()
    {
        try {
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.reports.mark_sheet_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function markSheetReportSearch(Request $request)
    {
        if(db_engine() !="pgsql"){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
      
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);
        try {
            $class = AramiscClass::find($request->class);
            $section = AramiscSection::find($request->section);
            $exam = AramiscExam::find($request->exam);

            $subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $all_students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $marks_registers = AramiscMarksRegister::where('exam_id', $request->exam)->where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $marks_register = AramiscMarksRegister::where('exam_id', $request->exam)->where('class_id', $request->class)->where('section_id', $request->section)->first();
            if ($marks_registers->count() == 0) {
                Toastr::error('Result not found', 'Failed');
                return redirect()->back();
                // return redirect('mark-sheet-report')->with('message-danger', 'Result not found');
            }
            // $marks_register_childs = $marks_register->marksRegisterChilds;
            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $grades = AramiscMarksGrade::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $exam_id = $request->exam;
            $class_id = $request->class;

            return view('backEnd.reports.mark_sheet_report', compact('exams', 'classes', 'marks_registers', 'marks_register', 'all_students', 'subjects', 'class', 'section', 'exam', 'grades', 'exam_id', 'class_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function markSheetReportStudent(Request $request)
    {
        try {
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.reports.mark_sheet_report_student', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //marks     SheetReport     Student     Search

    public function markSheetReportStudentSearch(MarkSheetReportRequest $request)
    {
        try {
            $total_class_days = 0;
            $student_attendance = 0;
            $input['exam_id'] = $request->exam;
            $input['class_id'] = $request->class;
            $input['section_id'] = $request->section;
            $input['student_id'] = $request->student;

            // dd($request->all());
            if (moduleStatusCheck('University')) {
                $exam_type = $request->exam_type;
                $student_id = $request->student_id;

                // Attendance Part Start
                $exam_content = AramiscExamSetting::where('exam_type', $exam_type)
                    ->where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();


                if (isset($exam_content)) {
                    $total_class_day = AramiscStudentAttendance::whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                        ->where('un_semester_label_id', $request->un_semester_label_id)
                        ->where('un_section_id', $request->un_section_id)
                        ->where('un_academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->where('attendance_type', '!=', 'H')
                        ->get()
                        ->groupBy('attendance_date');

                    $total_class_days = count($total_class_day);

                    $student_attendance = AramiscStudentAttendance::where('student_id', $student_id)
                        ->whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                        ->whereIn('attendance_type', ["P", "L"])
                        ->where('un_academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->count();
                }
                // Attendance Part Start
                //Falil Grade Dynamic Start
                $failgpa = AramiscMarksGrade::min('gpa');

                $failgpaname = AramiscMarksGrade::where('gpa', $failgpa)->first();
                //Falil Grade Dynamic End

                $exams = AramiscExamType::get();

                $exam_details = $exams->where('active_status', 1)->find($exam_type);
                $examInfo=$exam_details; // For university module
                $StudentRecord = StudentRecord::query();
                $student_detail = universityFilter($StudentRecord, $request)
                    ->where('student_id', $student_id)
                    ->with('student')
                    ->first();

                $AramiscExam = AramiscExam::query();
                $examSubjects = universityFilter($AramiscExam, $request)
                    ->where('exam_type_id', $exam_type)
                    ->get();

                $examSubjectIds = [];
                foreach ($examSubjects as $examSubject) {
                    $examSubjectIds[] = $examSubject->un_subject_id;
                }

                $UnSubject = UnSubject::query();
                $subjects = $UnSubject->when($request->un_faculty_id, function ($q) use ($request) {
                    $q->where('un_faculty_id', $request->un_faculty_id);
                })
                    ->when($request->un_department_id, function ($q) use ($request) {
                        $q->where('un_department_id', $request->un_department_id);
                    })
                    ->whereIn('id', $examSubjectIds);

                $optional_subject = '';

                $get_optional_subject = AramiscOptionalSubjectAssign::where('student_id', '=', $student_id)
                    ->first();

                if ($get_optional_subject != '') {
                    $optional_subject = $get_optional_subject->subject_id;
                }

                $optional_subject_setup = AramiscClassOptionalSubject::first();

                $AramiscResultStore = AramiscResultStore::query();
                $mark_sheet = universityFilter($AramiscResultStore, $request)
                    ->where([
                        ['exam_type_id', $exam_type],
                        ['student_id', $student_id]
                    ])
                    ->whereIn('un_subject_id', $subjects->pluck('id')->toArray())
                    ->with('unSubjectDetails')
                    ->get();

                $grades = AramiscMarksGrade::orderBy('gpa', 'desc')->get();

                $maxGrade = AramiscMarksGrade::max('gpa');

                if (count($mark_sheet) == 0) {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                    return redirect('mark-sheet-report-student');
                }

                $SmResultStor = AramiscResultStore::query();
                $is_result_available = universityFilter($SmResultStor, $request)
                    ->where([
                        ['exam_type_id', $exam_type],
                        ['student_id', $student_id]
                    ])
                    ->where('created_at', 'LIKE', '%' . YearCheck::getYear() . '%')
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
                $un_session = UnSession::find($request->un_session_id);
                $un_faculty = UnFaculty::find($request->un_faculty_id);
                $un_department = UnDepartment::find($request->un_department_id);
                $un_academic = UnAcademicYear::find($request->un_academic_id);
                $un_semester = UnSemester::find($request->un_semester_id);
                $un_semester_label = UnSemesterLabel::find($request->un_semester_label_id);
                $un_section = AramiscSection::find($request->un_section_id);
                $pass_mark=0;
                if ($exam_details) {
                    $pass_mark = $examInfo->average_mark;
                } else {
                    Toastr::warning('Exam Setup Not Complete', 'Warning');
                    return redirect()->back();
                }
                // dd($un_session,$un_semester,$un_semester_label);
                $data=[];
                $data['semester']=$un_semester->name;
                $data['semester_label']=$un_semester_label->name;
                $data['session']=$un_session->name;
                $data['requestData']['un_session_id']=$request->un_session_id;
                $data['requestData']['un_academic_id']=$request->un_academic_id;
                $data['requestData']['un_semester_id']=$request->un_semester_id;
                $data['requestData']['un_semester_label_id']=$request->un_semester_label_id;
                $data['requestData']['un_faculty_id']=$request->un_faculty_id;
                $data['requestData']['un_department_id']=$request->un_department_id;
                // $data['requestData']['un_subject_id']=$request->un_subject_id;

                $subjectInfo='';
                $exam_rule = CustomResultSetting::where('school_id', auth()->user()->school_id)->first();
                //$exam_detail = AramiscExam::find($request->exam);

                return view('backEnd.reports.mark_sheet_report_student', compact(
                    'mark_sheet',
                    'subjects',
                    'grades',
                    'student_detail',
                    'exam_details',
                    'optional_subject',
                    'un_session',
                    'un_faculty',
                    'un_department',
                    'un_academic',
                    'un_semester',
                    'un_semester_label',
                    'maxGrade',
                    'examInfo',
                    'exam_rule',
                    'data',
                    'pass_mark',
                    'failgpaname',
                    'exam_type',
                    'student_id',
                    'un_section'
                ));
            } else {
                // Attendance Part Start
                $exam_content = AramiscExamSetting::where('exam_type', $request->exam)
                    ->where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();

                if ($exam_content) {
                    $total_class_day = AramiscStudentAttendance::whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                                ->where('class_id', $input['class_id'])
                                ->where('section_id', $input['section_id'])
                                ->where('academic_id', getAcademicId())
                                ->where('school_id', Auth::user()->school_id)
                                ->whereNotIn('attendance_type', ["H"])
                                ->get()
                                ->groupBy('attendance_date');

                    $total_class_days = count($total_class_day);

                    $student_attendance = AramiscStudentAttendance::where('student_id', $request->student)
                        ->whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                        ->where('class_id', $input['class_id'])
                        ->where('section_id', $input['section_id'])
                        ->whereIn('attendance_type', ["P", "L"])
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->count();
                }
                // Attendance Part End

                $failgpa = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->min('gpa');

                $failgpaname = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->where('gpa', $failgpa)
                    ->first();

                $exams = AramiscExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $classes = AramiscClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $student_detail = $studentDetails = StudentRecord::where('student_id', $request->student)
                    ->where('academic_id', getAcademicId())
                    ->where('is_promote', 0)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();


                $examSubjects = AramiscExam::where([['exam_type_id', $request->exam], ['section_id', $request->section], ['class_id', $request->class]])
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();
                $examSubjectIds = [];
                foreach ($examSubjects as $examSubject) {
                    $examSubjectIds[] = $examSubject->subject_id;
                }

                $subjects = $studentDetails->class->subjects->where('section_id', $request->section)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id);
                $subjects = $examSubjects;

                $section_id = $request->section;
                $class_id = $request->class;
                $exam_type_id = $request->exam;
                $student_id = $request->student;
                $exam_details = $exams->where('active_status', 1)->find($exam_type_id);


                $optional_subject = '';

                $get_optional_subject = AramiscOptionalSubjectAssign::where('record_id', '=', $student_detail->id)
                    ->where('session_id', '=', $student_detail->session_id)
                    ->first();

                if ($get_optional_subject != '') {
                    $optional_subject = $get_optional_subject->subject_id;
                }

                $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $request->class)
                    ->first();

                $mark_sheet = AramiscResultStore::where([['class_id', $request->class], ['exam_type_id', $request->exam], ['section_id', $request->section], ['student_id', $request->student]])
                    ->whereIn('subject_id', $subjects->pluck('subject_id')->toArray())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();


                $grades = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->orderBy('gpa', 'desc')
                    ->get();

                $maxGrade = AramiscMarksGrade::where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->max('gpa');

                if (count($mark_sheet) == 0) {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                    return redirect('mark-sheet-report-student');
                }

                $is_result_available = AramiscResultStore::where([['class_id', $request->class], ['exam_type_id', $request->exam], ['section_id', $request->section], ['student_id', $request->student]])
                    ->where('created_at', 'LIKE', '%' . YearCheck::getYear() . '%')
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $marks_register = AramiscMarksRegister::where('exam_id', $request->exam)
                    ->where('student_id', $request->student)
                    ->first();

                $subjects = AramiscAssignSubject::where('class_id', $request->class)
                    ->where('section_id', $request->section)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $exams = AramiscExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $classes = AramiscClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $grades = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $class = AramiscClass::find($request->class);
                $section = AramiscSection::find($request->section);
                $exam_detail = AramiscExam::find($request->exam);
                $exam_id = $request->exam;
                $class_id = $request->class;

                return view('backEnd.reports.mark_sheet_report_student', compact(
                    'optional_subject',
                    'classes',
                    'studentDetails',
                    'exams',
                    'classes',
                    'marks_register',
                    'subjects',
                    'class',
                    'section',
                    'exam_detail',
                    'grades',
                    'exam_id',
                    'class_id',
                    'student_detail',
                    'input',
                    'mark_sheet',
                    'exam_details',
                    'maxGrade',
                    'failgpaname',
                    'exam_type_id',
                    'section_id', 'exam_content', 'total_class_days', 'student_attendance', 'optional_subject_setup'));
            }
        } catch (\Exception $e) {
            dd($e);
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function markSheetReportStudentPrint($exam_id, $class_id, $section_id, $student_id)
    {
        try {
            $total_class_days = 0;
            $student_attendance = 0;

            $student_detail = $studentDetails = StudentRecord::where('student_id', $student_id)->where('academic_id', getAcademicId())->where('is_promote', 0)->where('school_id', Auth::user()->school_id)->first();

            $examSubjects = AramiscExam::where([['exam_type_id', $exam_id], ['section_id', $section_id], ['class_id', $class_id]])
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $examSubjectIds = [];
            foreach ($examSubjects as $examSubject) {
                $examSubjectIds[] = $examSubject->subject_id;
            }

            $subjects = $examSubjects;


            $mark_sheet = AramiscResultStore::where([['class_id', $class_id], ['exam_type_id', $exam_id], ['section_id', $section_id], ['student_id', $student_id]])
                ->whereIn('subject_id', $subjects->pluck('subject_id')->toArray())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $grades = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->orderBy('gpa', 'desc')
                ->get();

            $maxGrade = AramiscMarksGrade::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->max('gpa');

            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exam_types = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $subjects = AramiscAssignSubject::where([['class_id', $class_id], ['section_id', $section_id]])
                ->whereIn('subject_id', $examSubjectIds)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $section = AramiscSection::where('active_status', 1)
                ->where('id', $section_id)
                ->first();

            $failgpa = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $failgpaname = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $failgpa)
                ->first();


            $exam_content = AramiscExamSetting::where('exam_type', $exam_id)
                ->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->first();

            if (isset($exam_content)) {
                $total_class_day = AramiscStudentAttendance::whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                            ->where('class_id', $class_id)
                            ->where('section_id', $section_id)
                            ->where('academic_id', getAcademicId())
                            ->where('school_id', Auth::user()->school_id)
                            ->where('attendance_type', '!=', 'H')
                            ->get()
                            ->groupBy('attendance_date');

                $total_class_days = count($total_class_day);

                $student_attendance = AramiscStudentAttendance::where('student_id', $student_id)
                    ->whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                    ->whereIn('attendance_type', ['P',"L"])
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->count();
            }

            $section_id = $section_id;
            $class_id = $class_id;
            $class_name = AramiscClass::find($class_id);
            $exam_type_id = $exam_id;
            $student_id = $student_id;
            $exam_details = AramiscExamType::where('active_status', 1)->find($exam_type_id);
            $optional_subject = '';

            $get_optional_subject = AramiscOptionalSubjectAssign::where('record_id', '=', $student_detail->id)
                ->where('session_id', '=', $student_detail->session_id)
                ->first();

            if ($get_optional_subject != '') {
                $optional_subject = $get_optional_subject->subject_id;
            }

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $class_id)->first();
            $is_result_available = AramiscResultStore::where([['class_id', $class_id], ['exam_type_id', $exam_id], ['section_id', $section_id], ['student_id', $student_id]])
                ->where('academic_id', getAcademicId())
                ->get();

            if ($is_result_available->count() > 0) {
                if ($optional_subject == '') {
                    return view('backEnd.reports.mark_sheet_report_normal_print', [
                            'exam_types' => $exam_types,
                            'grades' => $grades,
                            'classes' => $classes,
                            'subjects' => $subjects,
                            'class' => $class_id,
                            'class_name' => $class_name,
                            'section' => $section,
                            'exams' => $exams,
                            'section_id' => $section_id,
                            'exam_type_id' => $exam_type_id,
                            'is_result_available' => $is_result_available,
                            'student_detail' => $student_detail,
                            'class_id' => $class_id,
                            'studentDetails' => $studentDetails,
                            'student_id' => $student_id,
                            'exam_details' => $exam_details,
                            'optional_subject' => $optional_subject,
                            'optional_subject_setup' => $optional_subject_setup,
                            'exam_content' => $exam_content,
                            'failgpaname' => $failgpaname,
                            'total_class_days' => $total_class_days,
                            'student_attendance' => $student_attendance,
                            'mark_sheet' => $mark_sheet,
                        ]
                    );
                } else {
                    return view('backEnd.reports.mark_sheet_report_student_print', [
                            'exam_types' => $exam_types,
                            'classes' => $classes,
                            'subjects' => $subjects,
                            'class' => $class_id,
                            'class_name' => $class_name,
                            'section' => $section,
                            'grades' => $grades,
                            'exams' => $exams,
                            'maxGrade' => $maxGrade,
                            'section_id' => $section_id,
                            'exam_type_id' => $exam_type_id,
                            'is_result_available' => $is_result_available,
                            'student_detail' => $student_detail,
                            'class_id' => $class_id,
                            'studentDetails' => $studentDetails,
                            'student_id' => $student_id,
                            'exam_details' => $exam_details,
                            'optional_subject' => $optional_subject,
                            'optional_subject_setup' => $optional_subject_setup,
                            'mark_sheet' => $mark_sheet,
                            'failgpaname' => $failgpaname,
                            'total_class_days' => $total_class_days,
                            'student_attendance' => $student_attendance,
                            'exam_content' => $exam_content,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examSettings()
    {
        try {
            $content_infos = AramiscExamSetting::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $already_assigned = [];
            foreach ($content_infos as $content_info) {
                $already_assigned[] = $content_info->exam_type;
            }

            return view('backEnd.examination.exam_settings', compact('content_infos', 'exams', 'already_assigned'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function saveExamContent(Request $request)
    {
        $request->validate([
            'exam_type' => "required",
            'title' => "required",
            'publish_date' => "required",
            'start_date' => "required|before:end_date",
            'end_date' => "required|before:publish_date",
            'file' => "sometimes|nullable|mimes:jpg,jpeg,png,svg",
        ]);

        try {
            $fileName = "";
            if ($request->file('file') != "") {
                $maxFileSize = AramiscGeneralSettings::where('school_id', auth()->user()->school_id)->first()->file_size;
                $file = $request->file('file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/exam/', $fileName);
                $fileName = 'public/uploads/exam/' . $fileName;
            }

            $add_content = new AramiscExamSetting();
            $add_content->exam_type = $request->exam_type;
            $add_content->title = $request->title;
            $add_content->publish_date = date('Y-m-d', strtotime($request->publish_date));
            $add_content->file = $fileName;
            $add_content->start_date = date('Y-m-d', strtotime($request->start_date));
            $add_content->end_date = date('Y-m-d', strtotime($request->end_date));
            $add_content->school_id = Auth::user()->school_id;
            $add_content->academic_id = getAcademicId();
            $result = $add_content->save();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-settings');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editExamSettings($id)
    {
        try {
            $content_infos = AramiscExamSetting::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $editData = AramiscExamSetting::where('id', $id)
                ->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $already_assigned = [];
            foreach ($content_infos as $content_info) {
                if ($editData->exam_type != $content_info->exam_type) {
                    $already_assigned[] = $content_info->exam_type;
                }
            }

            return view('backEnd.examination.exam_settings', compact('editData', 'content_infos', 'exams', 'already_assigned'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function updateExamContent(Request $request)
    {

        $request->validate([
            'exam_type' => "required",
            'title' => "required",
            'publish_date' => "required",
            'start_date' => "required|before:end_date",
            'end_date' => "required|before:publish_date",
            'file' => "sometimes|nullable|mimes:jpg,jpeg,png,svg",
        ]);

        try {
            if ($request->file('file') != "") {
                $maxFileSize = AramiscGeneralSettings::where('school_id', auth()->user()->school_id)->first()->file_size;
                $file = $request->file('file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                    return redirect()->back();
                }

                $signature = AramiscExamSetting::find($request->id);
                if ($signature->file != "") {
                    @unlink($signature->file);
                }

                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/exam/', $fileName);
                $fileName = 'public/uploads/exam/' . $fileName;
            } else {
                $signature = AramiscExamSetting::find($request->id);
                $fileName = $signature->file;
            }

            if (checkAdmin()) {
                $update_add_content = AramiscExamSetting::find($request->id);
            } else {
                $update_add_content = AramiscExamSetting::where('id', $request->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->first();
            }
            $update_add_content->exam_type = $request->exam_type;
            $update_add_content->title = $request->title;
            $update_add_content->publish_date = date('Y-m-d', strtotime($request->publish_date));
            $update_add_content->file = $fileName;
            $update_add_content->start_date = date('Y-m-d', strtotime($request->start_date));
            $update_add_content->end_date = date('Y-m-d', strtotime($request->end_date));
            $update_add_content->school_id = Auth::user()->school_id;
            $update_add_content->academic_id = getAcademicId();
            $update_add_content->file = $fileName;
            $result = $update_add_content->save();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-settings');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteContent($id)
    {
        try {
            if (checkAdmin()) {
                $content = AramiscExamSetting::find($id);
            } else {
                $content = AramiscExamSetting::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }
            unlink($content->file);
            $result = $content->delete();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-settings');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function percentMarkSheetReport(PercentMarkSheetReportRequest $request)
    {
        try {
            $exams = AramiscExamType::get();
            $classes = AramiscClass::get();
            $pass_mark = 0;
            $examInfo = AramiscExamType::find($request->exam_type);

            $classInfo = AramiscClass::find($request->class);
            $sectionInfo = AramiscSection::find($request->section);
            $data = [];
            if (moduleStatusCheck('University')) {
                $subjectInfo = UnSubject::find($request->un_subject_id);
                $data['.'] = UnSemester::find($request->un_semester_id)->name;
                $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                $data['session'] = UnSession::find($request->un_session_id)->name;
                $data['requestData'] = $request->all();
                $exam = AramiscExam::query();
                $exam = universityFilter($exam, $request);
                $exam = $exam->first();
            } else {
                $subjectInfo = AramiscSubject::find($request->subject);
                $exam = AramiscExam::where('class_id', $request->class)
                    ->where('section_id', $request->section)
                    ->where('exam_type_id', $request->exam_type)
                    ->where('subject_id', $request->subject)
                    ->first();
            }
            
            if ($exam) {
                $pass_mark = $examInfo->average_mark;
            } else {
                Toastr::warning('Exam Setup Not Complete', 'Warning');
                return redirect()->back();
            }
            $exam_rule = CustomResultSetting::where('school_id', auth()->user()->school_id)->first();

            if ($exam_rule) {
                $mark_sheet = AramiscResultStore::query();
                $mark_sheet->where('exam_type_id', $request->exam_type);
                if (moduleStatusCheck('University')) {
                    $mark_sheet = universityFilter($mark_sheet, $request)->where('un_subject_id', $request->un_subject_id);
                } else {
                    $mark_sheet = $mark_sheet->where('class_id', $request->class)
                        ->where('section_id', $request->section)
                        ->where('subject_id', $request->subject);
                }
                $mark_sheet = $mark_sheet->orderBy('total_marks', 'DESC')->with('studentRecords')->get();
                return view('backEnd.examination.report.marksheetReport', compact('exams', 'classes', 'mark_sheet', 'examInfo', 'subjectInfo', 'classInfo', 'sectionInfo', 'pass_mark', 'exam_rule', 'data'));
            } else {
                $mark_sheet = AramiscResultStore::query();
                $mark_sheet->where('exam_type_id', $request->exam_type);
                
                if(moduleStatusCheck('University')){
                    $mark_sheet = universityFilter($mark_sheet, $request)->where('un_subject_id', $request->un_subject_id); 
                }else{
                    $mark_sheet = $mark_sheet->where('class_id', $request->class)
                        ->where('section_id', $request->section)
                        ->where('subject_id', $request->subject);
                }
                $mark_sheet =$mark_sheet->orderBy('total_marks', 'DESC')->with('studentRecords')->get();
               
                
                return view('backEnd.examination.report.marksheetReport', compact('exams', 'classes', 'mark_sheet','examInfo', 'subjectInfo', 'classInfo', 'sectionInfo','pass_mark','exam_rule','data'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function percentMarksheetPrint(Request $request)
    {
        try {
            $examInfo = AramiscExamType::find($request->exam);
            $subjectInfo = AramiscSubject::find($request->subject);
            $classInfo = AramiscClass::find($request->class);
            $sectionInfo = AramiscSection::find($request->section);

            $data = [];
            if (moduleStatusCheck('University')) {
                $subjectInfo = UnSubject::find($request->un_subject_id);
                $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                $data['session'] = UnSession::find($request->un_session_id)->name;
                $data['requestData'] = $request->all();
                $exam = AramiscExam::query();
                $exam = universityFilter($exam, $request);
                $exam = $exam->first();
            } else {
                $subjectInfo = AramiscSubject::find($request->subject);
                $exam = AramiscExam::where('class_id',$request->class)
                ->where('section_id',$request->section)
                ->where('exam_type_id',$request->exam)
                ->where('subject_id',$request->subject)
                ->first();
            }
            $pass_mark = $examInfo->average_mark;
            $mark_sheet = AramiscResultStore::query();
            $mark_sheet->where('exam_type_id', $request->exam);
            if (moduleStatusCheck('University')) {
                $mark_sheet = universityFilter($mark_sheet, $request)
                ->when($request->un_subject_id, function ($query, $request) {
                    return $query->where('un_subject_id', $request->un_subject_id);
                });
            } else {
                $mark_sheet = $mark_sheet->where('class_id', $request->class)
                    ->where('section_id', $request->section)
                    ->where('subject_id', $request->subject);
            }
            $mark_sheet = $mark_sheet->orderBy('total_marks', 'DESC')->with('studentRecords')->get();
            $exam_rule = CustomResultSetting::where('school_id', auth()->user()->school_id)
                ->first();

            return view('backEnd.examination.report.marksheetReportPrint', compact('mark_sheet', 'examInfo', 'subjectInfo', 'classInfo', 'sectionInfo', 'pass_mark', 'exam_rule', 'data'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
