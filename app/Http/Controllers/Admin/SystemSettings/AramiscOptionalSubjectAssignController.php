<?php

namespace App\Http\Controllers\Admin\SystemSettings;

use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscClassSection;
use App\AramiscAssignSubject;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscClassOptionalSubject;
use App\AramiscOptionalSubjectAssign;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\Academics\AssignOptionalSubjectSearch;
use App\Http\Requests\Admin\GeneralSettings\SmOptionalSetupStoreRequest;

class AramiscOptionalSubjectAssignController extends Controller
{

    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignOptionalSubject(Request $request)
    {
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }
    public function index(Request $request)
    {

        $classes = AramiscClass::get();
        $sections = AramiscClassSection::get();
        $assign_subjects = AramiscAssignSubject::get();
        $subjects = AramiscSubject::get();
        $teachers = AramiscStaff::where('role_id', 4)->get();
        return view('backEnd.academics.assign_optional_subject', compact('classes', 'sections', 'assign_subjects', 'subjects', 'teachers'));
    }

    public function assignOptionalSubjectSearch(AssignOptionalSubjectSearch $request)
    {
        try {
            $students = StudentRecord::with('studentDetail','studentDetail.subjectAssign', 'studentDetail.subjectAssign.subject')
            ->whereHas('studentDetail', function($q){
                return $q->where('active_status', 1);
            })
                        ->where('class_id', $request->class_id)
                        ->where('section_id', $request->section_id)
                        ->where('academic_id', getAcademicId())
                        ->where('is_promote', 0)
                        ->where('school_id', Auth::user()->school_id)
                        ->get();

            $subject_id = $request->subject_id;
            $subject_info = AramiscSubject::where('id', '=', $request->subject_id)->first();
            $subjects = AramiscSubject::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $teachers = AramiscStaff::where('active_status', 1)->where(function($q)  {
	        $q->where('role_id', 4)->orWhere('previous_role_id', 4);})->where('school_id', Auth::user()->school_id)->get();

            $class_id = $request->class_id;
            $section_id = $request->section_id;
            $classes = AramiscClass::get();
            $class = AramiscClass::with('classSection')->where('id', $class_id)->first();
            $assignSubjects= AramiscAssignSubject::with('subject')->where('class_id', $class_id)->where('section_id', $section_id)->get();
            return view('backEnd.academics.assign_optional_subject', compact('classes', 'teachers', 'subjects', 'class_id', 'section_id', 'students', 'subject_id', 'subject_info', 'class', 'assignSubjects'));
        } catch (\Exception $e) { 
             
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function assignOptionalSubjectStore(Request $request)
    {
        try {
            $old = AramiscOptionalSubjectAssign::where('subject_id', '=', $request->subject_id)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->first();
            if (!is_null($old)) {
                AramiscOptionalSubjectAssign::where('subject_id', '=', $request->subject_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->delete();
            }
            if ($request->student_id != "") {
                foreach ($request->student_id as $student) {
                    $student_info = StudentRecord::where('id', $student)->first();
                    $optional_subject = AramiscOptionalSubjectAssign::where('record_id', '=', $student)
                                        ->where('session_id', '=', $student_info->studentDetail->session_id)
                                        ->first();
 
                    if ($optional_subject != '') {
                        $optional_subject = AramiscOptionalSubjectAssign::find($optional_subject->id);
                        $optional_subject->subject_id = $request->subject_id;
                        $optional_subject->updated_by = Auth::user()->id;
                        $optional_subject->academic_id = getAcademicId();
                        $optional_subject->save();
                    } else {
                        $optional_subject = new AramiscOptionalSubjectAssign();
                        $optional_subject->student_id = $student_info->studentDetail->id;
                        $optional_subject->record_id = $student;
                        $optional_subject->subject_id = $request->subject_id;
                        $optional_subject->session_id = $student_info->session_id;
                        $optional_subject->created_by = Auth::user()->id;
                        $optional_subject->school_id = Auth::user()->school_id;
                        $optional_subject->academic_id = getAcademicId();
                        $optional_subject->save();
                    }
                }

            }else{
                Toastr::warning('No Student Select', 'Warning');
                return redirect('optional-subject');
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('optional-subject');

        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect('optional-subject');
        }
    }

    public function optionalSetup(Request $request)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_optionals = AramiscClassOptionalSubject::join('sm_classes', 'sm_classes.id', '=', 'sm_class_optional_subject.class_id')
                ->select('sm_class_optional_subject.*', 'class_name')
                ->where('sm_class_optional_subject.school_id', Auth::user()->school_id)
                ->where('sm_class_optional_subject.academic_id', getAcademicId())
                ->orderby('sm_class_optional_subject.id', 'DESC')
                ->get();
            return view('backEnd.systemSettings.optional_subject_setup', compact('classes', 'class_optionals'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function optionalSetupStore(SmOptionalSetupStoreRequest $request)
    {

        try {
            foreach ($request->class as $value) {
                $optional_check = AramiscClassOptionalSubject::where('class_id', '=', $value)->first();
                if ($optional_check == '') {
                    $class_optional = new AramiscClassOptionalSubject();
                    $class_optional->class_id = $value;
                } else {
                    $class_optional = AramiscClassOptionalSubject::where('class_id', '=', $value)->first();
                }
                $class_optional->gpa_above = $request->gpa_above;
                $class_optional->school_id = Auth::user()->school_id;
                $class_optional->created_by = Auth::user()->id;
                $class_optional->updated_by = Auth::user()->id;
                $class_optional->academic_id = getAcademicId();
                $class_optional->save();
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('optional-subject-setup');
        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function optionalSetupDelete($id)
    {

        try {
            $class_optional = AramiscClassOptionalSubject::findOrfail($id);
            $class_optional->delete();
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function optionalSetupEdit($id)
    {

        try {
            $editData = AramiscClassOptionalSubject::findOrfail($id);
            $classes = AramiscClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->where('sm_classes.academic_id', getAcademicId())->get();
            //    return $classes;
            $class_optionals = AramiscClassOptionalSubject::join('sm_classes', 'sm_classes.id', '=', 'sm_class_optional_subject.class_id')
                ->select('sm_class_optional_subject.*', 'class_name')
                ->where('sm_class_optional_subject.school_id', Auth::user()->school_id)->get();
            return view('backEnd.systemSettings.optional_subject_setup', compact('classes', 'class_optionals', 'editData'));
        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
