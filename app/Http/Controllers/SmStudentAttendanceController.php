<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\Imports\StudentAttendanceImport;
use App\SmAssignSubject;
use App\SmClass;
use App\SmClassSection;
use App\SmSection;
use App\SmStaff;
use App\SmStudent;
use App\SmStudentAttendance;
use App\StudentAttendanceBulk;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SmStudentAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function index(Request $request)
    {
        try {
            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.class_id')
                    ->where('sm_assign_subjects.academic_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.school_id', Auth::user()->school_id)
                    ->select('sm_classes.id', 'class_name')
                    ->distinct('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }
            return view('backEnd.studentInformation.student_aramiscAttendance', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'aramiscAttendance_date' => 'required',
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
            $date = $request->aramiscAttendance_date;
            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.class_id')
                    ->where('sm_assign_subjects.academic_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.school_id', Auth::user()->school_id)
                    ->select('sm_classes.id', 'class_name')
                    ->distinct('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }
            $students = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('active_status', 1)->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->get();

            if ($students->isEmpty()) {
                Toastr::error('No Result Found', 'Failed');
                return redirect('student-aramiscAttendance');
            }

            $already_assigned_students = [];
            $new_students = [];
            $aramiscAttendance_type = "";
            foreach ($students as $student) {
                $aramiscAttendance = SmStudentAttendance::where('student_id', $student->id)
                    ->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                if ($aramiscAttendance != "") {
                    $already_assigned_students[] = $aramiscAttendance;
                    $aramiscAttendance_type = $aramiscAttendance->aramiscAttendance_type;
                } else {
                    $new_students[] = $student;
                }
            }
            $class_id = $request->class;
            $section_id = $request->section;
            $class_info = SmClass::find($request->class);
            $section_info = SmSection::find($request->section);

            $search_info['class_name'] = $class_info->class_name;
            $search_info['section_name'] = $section_info->section_name;
            $search_info['date'] = $request->aramiscAttendance_date;

            $sections = SmClassSection::with('sectionName')->where('class_id', $class_id)->where('academic_id', getAcademicId())->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['date'] = $date;
                $data['class_id'] = $class_id;
                $data['already_assigned_students'] = $already_assigned_students;
                $data['new_students'] = $new_students;
                $data['aramiscAttendance_type'] = $aramiscAttendance_type;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.student_aramiscAttendance', compact('classes', 'sections', 'date', 'class_id', 'section_id', 'date', 'already_assigned_students', 'new_students', 'aramiscAttendance_type', 'search_info'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentAttendanceStore(Request $request)
    {
        $aramiscAttendance = SmStudentAttendance::where('student_id', $request->student_id)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))->first();
        try {
            foreach ($request->id as $student) {
                $aramiscAttendance = SmStudentAttendance::where('student_id', $student)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))
                    ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->first();

                if ($aramiscAttendance) {
                    $aramiscAttendance->delete();
                }

                $aramiscAttendance = new SmStudentAttendance();
                $aramiscAttendance->student_id = $student;
                if (isset($request->mark_holiday)) {
                    $aramiscAttendance->aramiscAttendance_type = "H";
                } else {
                    $aramiscAttendance->aramiscAttendance_type = $request->aramiscAttendance[$student];
                    $aramiscAttendance->notes = $request->note[$student];
                }
                $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->date));
                $aramiscAttendance->school_id = Auth::user()->school_id;
                $aramiscAttendance->academic_id = getAcademicId();
                $aramiscAttendance->save();

               

                if ($request->aramiscAttendance[$student] == 'P') {
                    $student_info = SmStudent::find($student);
                    $compact['aramiscAttendance_date'] = $aramiscAttendance->aramiscAttendance_date;
                    $compact['user_email'] = $student_info->email;
                    $compact['student_id'] = $student_info;
                    @send_sms($student_info->mobile, 'student_aramiscAttendance', $compact);

                    $compact['user_email'] = @$student_info->parents->guardians_email;
                    @send_sms(@$student_info->parents->guardians_mobile, 'student_aramiscAttendance_for_parent', $compact);

                } elseif ($request->aramiscAttendance[$student] == 'A') {
                    

                } elseif ($request->aramiscAttendance[$student] == 'L') {
                    
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Student aramiscAttendance been submitted successfully');
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('student-aramiscAttendance');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentAttendanceHoliday(Request $request)
    {
        $students = SmStudent::where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('active_status', 1)->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)->get();
        if ($students->isEmpty()) {
            Toastr::error('No Result Found', 'Failed');
            return redirect('student-aramiscAttendance');
        }

        if ($request->purpose == "mark") {

            foreach ($students as $student) {

                $aramiscAttendance = SmStudentAttendance::where('student_id', $student->id)
                    ->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                if (!empty($aramiscAttendance)) {
                    $aramiscAttendance->delete();

                    $aramiscAttendance = new SmStudentAttendance();
                    $aramiscAttendance->aramiscAttendance_type = "H";
                    $aramiscAttendance->notes = "Holiday";
                    $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->aramiscAttendance_date));
                    $aramiscAttendance->student_id = $student->id;
                    $aramiscAttendance->academic_id = getAcademicId();
                    $aramiscAttendance->school_id = Auth::user()->school_id;
                    $aramiscAttendance->save();
                } else {
                    $aramiscAttendance = new SmStudentAttendance();
                    $aramiscAttendance->aramiscAttendance_type = "H";
                    $aramiscAttendance->notes = "Holiday";
                    $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->aramiscAttendance_date));
                    $aramiscAttendance->student_id = $student->id;
                    $aramiscAttendance->academic_id = getAcademicId();
                    $aramiscAttendance->school_id = Auth::user()->school_id;
                    $aramiscAttendance->save();
                }
            }
        } elseif ($request->purpose == "unmark") {
            foreach ($students as $student) {
                $aramiscAttendance = SmStudentAttendance::where('student_id', $student->id)
                    ->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                if (!empty($aramiscAttendance)) {
                    $aramiscAttendance->delete();
                }
            }
        }

        Toastr::success('Operation successful', 'Success');
        return redirect()->back();
    }

    public function studentAttendanceImport()
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentInformation.student_aramiscAttendance_import', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function downloadStudentAtendanceFile()
    {

        try {
            $studentsArray = ['admission_no', 'class_id', 'section_id', 'aramiscAttendance_date', 'in_time', 'out_time'];

            return Excel::create('student_aramiscAttendance_sheet', function ($excel) use ($studentsArray) {
                $excel->sheet('student_aramiscAttendance_sheet', function ($sheet) use ($studentsArray) {
                    $sheet->fromArray($studentsArray);
                });
            })->download('xlsx');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function studentAttendanceBulkStore(Request $request)
    {
        $request->validate([
            'aramiscAttendance_date' => 'required',
            'file' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);
        $file_type = strtolower($request->file->getClientOriginalExtension());
        if ($file_type != 'csv' && $file_type != 'xlsx' && $file_type != 'xls') {
            Toastr::warning('The file must be a file of type: xlsx, csv or xls', 'Warning');
            return redirect()->back();
        } else {
            try {
                $max_admission_id = SmStudent::where('school_id', Auth::user()->school_id)->max('admission_no');
                $path = $request->file('file')->getRealPath();

                Excel::import(new StudentAttendanceImport($request->class, $request->section), $request->file('file'), 's3', \Maatwebsite\Excel\Excel::XLSX);
                $data = StudentAttendanceBulk::get();

                if (!empty($data)) {
                    $class_sections = [];
                    foreach ($data as $key => $value) {
                        if (date('d/m/Y', strtotime($request->aramiscAttendance_date)) == date('d/m/Y', strtotime($value->aramiscAttendance_date))) {
                            $class_sections[] = $value->class_id . '-' . $value->section_id;
                        }
                    }
                    DB::beginTransaction();

                    $all_student_ids = [];
                    $present_students = [];
                    foreach (array_unique($class_sections) as $value) {

                        $class_section = explode('-', $value);
                        $students = SmStudent::where('class_id', $class_section[0])->where('section_id', $class_section[1])->where('school_id', Auth::user()->school_id)->get();

                        foreach ($students as $student) {
                            StudentAttendanceBulk::where('student_id', $student->id)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))
                                ->delete();
                            $all_student_ids[] = $student->id;
                        }

                    }

                    try {
                        foreach ($data as $key => $value) {
                            if ($value != "") {

                                if (date('d/m/Y', strtotime($request->aramiscAttendance_date)) == date('d/m/Y', strtotime($value->aramiscAttendance_date))) {
                                    $student = SmStudent::select('id')->where('id', $value->student_id)->where('school_id', Auth::user()->school_id)->first();

                                    // return $student;

                                    if ($student != "") {
                                        // SmStudentAttendance
                                        $aramiscAttendance_check = SmStudentAttendance::where('student_id', $student->id)
                                            ->where('aramiscAttendance_date', date('Y-m-d', strtotime($value->aramiscAttendance_date)))->first();
                                        if ($aramiscAttendance_check) {
                                            $aramiscAttendance_check->delete();
                                        }
                                        $present_students[] = $student->id;
                                        $import = new SmStudentAttendance();
                                        $import->student_id = $student->id;
                                        $import->aramiscAttendance_date = date('Y-m-d', strtotime($value->aramiscAttendance_date));
                                        $import->aramiscAttendance_type = $value->aramiscAttendance_type;
                                        $import->notes = $value->note;
                                        $import->school_id = Auth::user()->school_id;
                                        $import->academic_id = getAcademicId();
                                        $import->save();
                                    }
                                } else {
                                    // Toastr::error('Attendance Date not Matched', 'Failed');
                                    $bulk = StudentAttendanceBulk::where('student_id', $value->student_id)->delete();
                                }

                            }

                        }

                        // foreach ($all_student_ids as $all_student_id) {
                        //     if(!in_array($all_student_id, $present_students)){
                        //         $aramiscAttendance_check=SmStudentAttendance::where('student_id',$all_student_id)->where('aramiscAttendance_date',date('Y-m-d', strtotime($value->aramiscAttendance_date)))->first();
                        //         if ($aramiscAttendance_check) {
                        //            $aramiscAttendance_check->delete();
                        //         }
                        //         $import = new SmStudentAttendance();
                        //         $import->student_id = $all_student_id;
                        //         $import->aramiscAttendance_type = 'A';
                        //         $import->in_time = '';
                        //         $import->out_time = '';
                        //         $import->aramiscAttendance_date = date('Y-m-d', strtotime($request->aramiscAttendance_date));
                        //         $import->school_id = Auth::user()->school_id;
                        //         $import->academic_id = getAcademicId();
                        //         $import->save();

                        //         $bulk= StudentAttendanceBulk::where('student_id',$all_student_id)->delete();
                        //     }
                        // }

                    } catch (\Exception $e) {
                        DB::rollback();
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                }
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    }
}
