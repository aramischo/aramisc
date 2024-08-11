<?php

namespace App\Http\Controllers;

use App\SmClass;
use App\SmStaff;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use App\Models\TeacherEvaluation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeacherEvaluationReportController extends Controller
{
    public function getAssignSubjectTeacher(Request $request)
    {
        $staffs = SmAssignSubject::where('class_id', $request->class_id)->where('subject_id', $request->subject_id)->whereIn('section_id', $request->section_ids)->with('teacher')->select('teacher_id')->distinct('teacher_id')->get();
        return response()->json($staffs);
    }
    public function aramiscTeacherApprovedEvaluationReport()
    {
        try {
            $classes = SmClass::get();
            $aramiscTeacherEvaluations = TeacherEvaluation::with('studentRecord.studentDetail.parents', 'staff')->get();
            return view('backEnd.aramiscTeacherEvaluation.report.teacher_approved_evaluation_report', compact('classes', 'aramiscTeacherEvaluations'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function teacherPendingEvaluationReport()
    {
        $classes = SmClass::get();
        $aramiscTeacherEvaluations = TeacherEvaluation::with('studentRecord.studentDetail.parents', 'staff')->get();
        return view('backEnd.aramiscTeacherEvaluation.report.teacher_pending_evaluation_report', compact('classes', 'aramiscTeacherEvaluations'));
    }
    public function teacherWiseEvaluationReport()
    {
        $classes = SmClass::get();
        $teachers = SmStaff::where('role_id', 4)->get();
        $aramiscTeacherEvaluations = TeacherEvaluation::with('studentRecord.studentDetail.parents', 'staff')->get();
        return view('backEnd.aramiscTeacherEvaluation.report.teacher_wise_evaluation_report', compact('classes', 'aramiscTeacherEvaluations', 'teachers'));
    }
    public function aramiscTeacherApprovedEvaluationReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class_id' => "required",
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $classes = SmClass::get();
            $staffs = SmAssignSubject::where('class_id', $request->class_id)
                ->when($request->subject_id, function ($query) use ($request) {
                    $query->where('subject_id', $request->subject_id);
                })
                ->when($request->section_id, function ($query) use ($request) {
                    $query->whereIn('section_id', [$request->section_id]);
                })
                ->when($request->teacher_id, function ($query) use ($request) {
                    $query->where('teacher_id', $request->teacher_id);
                })->get();

            $aramiscTeacherEvaluations = TeacherEvaluation::when($request->class_id, function ($q) use ($request) {
                $q->whereHas('studentRecord', function ($query) use ($request) {
                    $query->where('class_id', $request->class_id);
                });
            })
                ->when($request->subject_id, function ($q) use ($request) {
                    $q->where('subject_id', $request->subject_id);
                })
                ->when($request->section_id, function ($q) use ($request) {
                    $q->whereHas('studentRecord', function ($query) use ($request) {
                        $query->where('section_id', $request->section_id);
                    });
                })
                ->when($request->teacher_id, function ($q) use ($staffs) {
                    foreach ($staffs as $staff) {
                        $q->where('teacher_id', $staff->teacher_id);
                    }
                })
                ->when($request->submitted_by, function ($q) use ($request) {
                    $q->where('role_id', $request->submitted_by);
                })
                ->with('studentRecord.studentDetail.parents', 'staff')->get();

            return view('backEnd.aramiscTeacherEvaluation.report.teacher_approved_evaluation_report', compact('classes', 'aramiscTeacherEvaluations'));
        } catch (\Exception $e) {
           
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function teacherPendingEvaluationReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class_id' => "required",
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $classes = SmClass::get();
            $staffs = SmAssignSubject::where('class_id', $request->class_id)
                ->when($request->subject_id, function ($query) use ($request) {
                    $query->where('subject_id', $request->subject_id);
                })
                ->when($request->section_id, function ($query) use ($request) {
                    $query->whereIn('section_id', [$request->section_id]);
                })
                ->when($request->teacher_id, function ($query) use ($request) {
                    $query->where('teacher_id', $request->teacher_id);
                })->get();

            $aramiscTeacherEvaluations = TeacherEvaluation::when($request->class_id, function ($q) use ($request) {
                $q->whereHas('studentRecord', function ($query) use ($request) {
                    $query->where('class_id', $request->class_id);
                });
            })
                ->when($request->subject_id, function ($q) use ($request) {
                    $q->where('subject_id', $request->subject_id);
                })
                ->when($request->section_id, function ($q) use ($request) {
                    $q->whereHas('studentRecord', function ($query) use ($request) {
                        $query->where('section_id', $request->section_id);
                    });
                })
                ->when($request->teacher_id, function ($q) use ($staffs) {
                    foreach ($staffs as $staff) {
                        $q->where('teacher_id', $staff->teacher_id);
                    }
                })
                ->when($request->submitted_by, function ($q) use ($request) {
                    $q->where('role_id', $request->submitted_by);
                })
                ->with('studentRecord.studentDetail.parents', 'staff')->get();

            return view('backEnd.aramiscTeacherEvaluation.report.teacher_pending_evaluation_report', compact('classes', 'aramiscTeacherEvaluations'));
        } catch (\Exception $e) {
         
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function teacherWiseEvaluationReportSearch(Request $request)
    {
        try {
            $classes = SmClass::get();
            $teachers = SmStaff::where('role_id', 4)->get();
            $aramiscTeacherEvaluations = TeacherEvaluation::query();
            if ($request->teacher_id) {
                $aramiscTeacherEvaluations->where('teacher_id', $request->teacher_id);
            }
            if ($request->submitted_by) {
                $aramiscTeacherEvaluations->where('role_id', $request->submitted_by);
            }
            $aramiscTeacherEvaluations = $aramiscTeacherEvaluations->with('studentRecord.studentDetail.parents', 'staff')->get();
            return view('backEnd.aramiscTeacherEvaluation.report.teacher_wise_evaluation_report', compact('classes', 'aramiscTeacherEvaluations', 'teachers'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function aramiscTeacherEvaluationApproveSubmit($id)
    {
        try {
            $aramiscTeacherEvaluations = TeacherEvaluation::find($id);
            $aramiscTeacherEvaluations->status = 1;
            $aramiscTeacherEvaluations->update();
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function aramiscTeacherEvaluationApproveDelete($id)
    {
        try {
            $aramiscTeacherEvaluations = TeacherEvaluation::find($id);
            $aramiscTeacherEvaluations->delete();
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function teacherPanelEvaluationReport()
    {
        try {
            $staffId = SmStaff::where('user_id', auth()->user()->id)->select('id')->first();
            $aramiscTeacherEvaluations = TeacherEvaluation::where('teacher_id', $staffId->id)->with('studentRecord')->get();
            return view('backEnd.aramiscTeacherEvaluation.report.teacher_panel_evaluation_report', compact('aramiscTeacherEvaluations'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
