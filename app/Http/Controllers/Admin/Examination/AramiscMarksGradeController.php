<?php

namespace App\Http\Controllers\Admin\Examination;

use App\tableList;
use App\YearCheck;
use App\AramiscMarksGrade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Examination\AramiscMarkGradeRequest;
use App\AramiscResultStore;

class AramiscMarksGradeController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index(Request $request)
    {
        try {
            $marks_grades = AramiscMarksGrade::orderBy('gpa', 'desc')->where('academic_id', getAcademicId())->get();
            return view('backEnd.examination.marks_grade', compact('marks_grades'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(AramiscMarkGradeRequest $request)
    {
        try {
            if (AramiscResultStore::where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)->count() > 0) {
                Toastr::error('Exam Already Taken', 'Failed');
                return redirect()->back();
            }
            $marks_grade = new AramiscMarksGrade();
            $marks_grade->grade_name = $request->grade_name;
            $marks_grade->gpa = $request->gpa;
            $marks_grade->percent_from = $request->percent_from;
            $marks_grade->percent_upto = $request->percent_upto;
            $marks_grade->from = $request->grade_from;
            $marks_grade->up = $request->grade_upto;
            $marks_grade->description = $request->description;
            $marks_grade->created_by = auth()->user()->id;
            $marks_grade->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $marks_grade->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $marks_grade->un_academic_id = getAcademicId();
            } else {
                $marks_grade->academic_id = getAcademicId();
            }
            $result = $marks_grade->save();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {;
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $marks_grade = AramiscMarksGrade::find($id);
            if (moduleStatusCheck('University')) {
                $marks_grades = AramiscMarksGrade::where('un_academic_id', getAcademicId())->get();
            } else {
                $marks_grades = AramiscMarksGrade::where('academic_id', getAcademicId())->get();
            }
            return view('backEnd.examination.marks_grade', compact('marks_grade', 'marks_grades'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(AramiscMarkGradeRequest $request, $id)
    {
        try {
            $marks_grade = AramiscMarksGrade::find($request->id);
            $marks_grade->grade_name = $request->grade_name;
            $marks_grade->gpa = $request->gpa;
            $marks_grade->percent_from = $request->percent_from;
            $marks_grade->percent_upto = $request->percent_upto;
            $marks_grade->description = $request->description;
            $marks_grade->from = $request->grade_from;
            $marks_grade->updated_by = auth()->user()->id;
            $marks_grade->up = $request->grade_upto;
            $result = $marks_grade->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('marks-grade');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $marks_grade = AramiscMarksGrade::destroy($id);
            Toastr::success('Operation successful', 'Success');
            return redirect('marks-grade');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}