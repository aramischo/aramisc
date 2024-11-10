<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\User;
use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscUserLog;
use Carbon\Carbon;
use App\AramiscBaseSetup;
use App\ApiBaseMethod;
use Barryvdh\DomPDF\PDF;
use App\AramiscGeneralSettings;
use App\AramiscStudentCategory;
use App\AramiscModuleManager;
use App\AramiscStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class AramiscStudentReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('PM');

    }

    //studentReport modified by jmrashed
    public function studentReport(Request $request)
    {
        try {
            $classes = AramiscClass::get();
            $types = AramiscStudentCategory::get();
            $genders = AramiscBaseSetup::where('base_group_id', '=', '1')->get();

            return view('backEnd.studentInformation.student_report', compact('classes', 'types', 'genders'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //student report search modified by jmrashed
    public function studentReportSearch(Request $request)
    {
        if (moduleStatusCheck('University')) {
            $request->validate([
                'un_session_id' => 'required'
            ]);
        } else {
            $request->validate([
                'class_id' => 'required'
            ]);
        }

        try {
            $data = [];
            $student_records = StudentRecord::query();
            $student_records->where('school_id', Auth::user()->school_id)->whereHas('studentDetail', function ($q) {
                $q->where('active_status', 1);
            });
            if ($request->class_id) {
                $student_records->where('class_id', $request->class_id);
            }
            if ($request->section_id) {
                $student_records->where('section_id', $request->section_id);
            }
            if (moduleStatusCheck('University')) {
                $student_records = universityFilter($student_records, $request);
            }

            $students = $student_records->with(['student' => function ($q) use ($request) {
                $q->when($request->type, function ($q) use ($request) {
                    $q->where('student_category_id', $request->type);
                })->when($request->gender, function ($q) use ($request) {
                    $q->where('gender_id', $request->gender);
                })->where('active_status', 1);
            }])->whereHas('student', function ($q) use ($request) {
                $q->when($request->type, function ($q) use ($request) {
                    $q->where('student_category_id', $request->type);
                })->when($request->gender, function ($q) use ($request) {
                    $q->where('gender_id', $request->gender);
                })->where('active_status', 1);
            })->get();

            $data['student_records'] = $students;
            $data['classes'] = AramiscClass::get();
            $data['types'] = AramiscStudentCategory::get();
            $data['genders'] = AramiscBaseSetup::where('base_group_id', '=', '1')->get();
            $data['class_id'] = $request->class_id;
            $data['gender_id'] = $request->gender;
            $data['type_id'] = $request->type;
            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($request);
            }
            return view('backEnd.studentInformation.student_report', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentAttendanceReport(Request $request)
    {
        try {
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $classes = $teacher_info->classes;
            } else {
                $classes = AramiscClass::get();
            }
            $types = AramiscStudentCategory::get();
            $genders = AramiscBaseSetup::where('base_group_id', '=', '1')->get();

            return view('backEnd.studentInformation.student_attendance_report', compact('classes', 'types', 'genders'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentAttendanceReportSearch(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'month' => 'required',
            'year' => 'required'
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
            $year = $request->year;
            $month = $request->month;
            $class_id = $request->class;
            $section_id = $request->section;
            $current_day = date('d');
            $clas = AramiscClass::findOrFail($request->class);
            $sec = AramiscSection::findOrFail($request->section);
            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $classes = $teacher_info->classes;
            } else {
                $classes = AramiscClass::get();
            }
            $students = AramiscStudent::where('class_id', $request->class)
                ->where('section_id', $request->section)->get();

            $attendances = [];
            foreach ($students as $student) {
                $attendance = AramiscStudentAttendance::where('student_id', $student->id)->where('attendance_date', 'like', $request->year . '-' . $request->month . '%')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                if (count($attendance) != 0) {
                    $attendances[] = $attendance;
                }
            }


            return view('backEnd.studentInformation.student_attendance_report', compact('classes', 'attendances', 'students', 'days', 'year', 'month', 'current_day',
                'class_id', 'section_id', 'clas', 'sec'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function studentAttendanceReportPrint($class_id, $section_id, $month, $year)
    {
        set_time_limit(2700);
        try {
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $students = DB::table('aramisc_students')
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->get();

            $attendances = [];
            foreach ($students as $student) {
                $attendance = AramiscStudentAttendance::where('student_id', $student->id)
                    ->where('attendance_date', 'like', $year . '-' . $month . '%')
                    ->get();

                if ($attendance) {
                    $attendances[] = $attendance;
                }
            }

            // $pdf = Pdf::loadView(
            //     'backEnd.studentInformation.student_attendance_print',
            //     [
            //         'attendances' => $attendances,
            //         'days' => $days,
            //         'year' => $year,
            //         'month' => $month,
            //         'class_id' => $class_id,
            //         'section_id' => $section_id,
            //         'class' => AramiscClass::find($class_id),
            //         'section' => AramiscSection::find($section_id),
            //     ]
            // )->setPaper('A4', 'landscape');
            // return $pdf->stream('student_attendance.pdf');

            $class = AramiscClass::find($class_id);
            $section = AramiscSection::find($section_id);
            return view('backEnd.studentInformation.student_attendance_print', compact('class', 'section', 'attendances', 'days', 'year', 'month', 'current_day', 'class_id', 'section_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function guardianReport(Request $request)
    {
        try {
            $classes = AramiscClass::get();
            return view('backEnd.studentInformation.guardian_report', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function guardianReportSearch(Request $request)
    {
        $input = $request->all();
        if (moduleStatusCheck('University')) {
            $validator = Validator::make($input, [
                'un_session_id' => 'required'
            ]);
        } else {
            $validator = Validator::make($input, [
                'class_id' => 'required'
            ],[
                'class_id' => 'The Class field is required.'
            ]);
        }


        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $student_records = StudentRecord::query();
            $student_records->where('school_id', Auth::user()->school_id);
            if ($request->class_id) {
                $student_records->where('class_id', $request->class_id);
            }
            if ($request->section_id) {
                $student_records->where('section_id', $request->section_id);
            }
            if (moduleStatusCheck('University')) {
                $student_records = universityFilter($student_records, $request);
            }

            $students = $student_records->with('student.parents', 'class', 'section')->get();
            $data = [];
            $data['student_records'] = $students;
            $data['classes'] = AramiscClass::get();

            $selected['class_id'] = $request->class_id;
            $selected['section_id'] = $request->section_id;

            return view('backEnd.studentInformation.guardian_report', compact('selected'))->with($data);
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentLoginReport(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.studentInformation.login_info', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function studentLoginSearch(Request $request)
    {

        $input = $request->all();
        if (moduleStatusCheck('University')) {
            $request->validate([
                'un_session_id' => 'required'
            ]);
        } else {
            $request->validate([
                'class' => 'required'
            ]);
        }
        try {
            $data = [];
            $student_records = StudentRecord::query();
            $student_records->where('school_id', Auth::user()->school_id);
            if ($request->class) {
                $student_records->where('class_id', $request->class);
            }
            if ($request->section) {
                $student_records->where('section_id', $request->section);
            }
            if (moduleStatusCheck('University')) {
                $student_records = universityFilter($student_records, $request);
            }

            $students = $student_records->with('student.user', 'student.parents')->get();
            $data['student_records'] = $students;
            $data['classes'] = AramiscClass::get();
            $data['class_id'] = $request->class;
            $data['section_id'] = $request->section;
            $data['clas'] = AramiscClass::find($request->class);
            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($request);
            }
            return view('backEnd.studentInformation.login_info', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentHistory(Request $request)
    {
        try {
            $classes = AramiscClass::get();
            $years = AramiscStudent::select('admission_date')->where('active_status', 1)
                ->where('academic_id', getAcademicId())->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->admission_date)->format('Y');
                });
            return view('backEnd.studentInformation.student_history', compact('classes', 'years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentHistorySearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $student_ids = $this->classSectionStudent($request);
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $students = AramiscStudent::query();
            $students->where('academic_id', getAcademicId())->where('active_status', 1);
            if ($request->admission_year != "") {
                $students->where('admission_date', 'like', $request->admission_year . '%');
            }

            $students = $students->whereIn('id', $student_ids)->with('recordClass.class', 'parents', 'promotion', 'session')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $years = AramiscStudent::select('admission_date')->where('active_status', 1)
                ->where('academic_id', getAcademicId())->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->admission_date)->format('Y');
                });
            $class_id = $request->class;
            $year = $request->admission_year;
            $student_id = null;

            $clas = AramiscClass::find($request->class);
            return view('backEnd.studentInformation.student_history', compact('students', 'classes', 'years', 'class_id', 'year', 'clas', 'student_id'));
        } catch (\Exception $e) {
           
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    // this function call others
    public static function classSectionStudent($request)
    {
        $student_ids = StudentRecord::when($request->academic_year, function ($query) use ($request) {
            $query->where('academic_id', $request->academic_year);
        })
            ->when($request->class, function ($query) use ($request) {
                $query->where('class_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request) {
                $query->where('section_id', $request->section);
            })
            ->when(!$request->academic_year, function ($query) use ($request) {
                $query->where('academic_id', getAcademicId());
            })->where('school_id', auth()->user()->school_id)->where('is_promote', 0)->pluck('student_id')->unique();

        return $student_ids;
    }

    public static function classSectionAlumni($request)
    {
        $student_ids = StudentRecord::when($request->academic_year, function ($query) use ($request) {
            $query->where('academic_id', $request->academic_year);
        })
            ->when($request->class, function ($query) use ($request) {
                $query->where('class_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request) {
                $query->where('section_id', $request->section);
            })
            ->when(!$request->academic_year, function ($query) use ($request) {
                $query->where('academic_id', getAcademicId());
            })->where('school_id', auth()->user()->school_id)->where('is_graduate',1)->where('is_promote', 1)->pluck('student_id')->unique();

        return $student_ids;
    }
    public static function SemesterLabelSectionStudent($request)
    {
        $student_ids = StudentRecord::when($request->academic_year, function ($query) use ($request) {
            $query->where('un_academic_id', $request->academic_year);
        })
            ->when($request->un_semester_label_id, function ($query) use ($request) {
                $query->where('un_semester_label_id', $request->un_semester_label_id);
            })
            ->when($request->un_section_id, function ($query) use ($request) {
                $query->where('un_section_id', $request->un_section_id);
            })
            ->when(!$request->academic_year, function ($query) use ($request) {
                $query->where('un_academic_id', getAcademicId());
            })->where('school_id', auth()->user()->school_id)->where('is_promote', 0)->pluck('student_id')->unique();

        return $student_ids;
    }

}