<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherEvaluation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Models\TeacherEvaluationSetting;
use Illuminate\Support\Facades\Validator;

class TeacherEvaluationController extends Controller
{
    public function aramiscTeacherEvaluationSetting()
    {
        $aramiscTeacherEvaluationSetting = TeacherEvaluationSetting::where('id', 1)->first();
        return view('backEnd.aramiscTeacherEvaluation.setting.aramiscTeacherEvaluationSetting', compact('aramiscTeacherEvaluationSetting'));
    }
    public function aramiscTeacherEvaluationSettingUpdate(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'endDate' => 'after:startDate',
        ]);
        if ($validator->fails()) {
            Toastr::error('End Date cannot be before Start Date', 'Failed');
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $aramiscTeacherEvaluationSetting = TeacherEvaluationSetting::find(1);
            if ($request->type == 'evaluation') {
                $aramiscTeacherEvaluationSetting->is_enable = $request->is_enable;
                $aramiscTeacherEvaluationSetting->auto_approval = $request->auto_approval;
            }
            if ($request->type == 'submission') {
                $aramiscTeacherEvaluationSetting->submitted_by = $request->submitted_by ? $request->submitted_by : $aramiscTeacherEvaluationSetting->submitted_by;
                $aramiscTeacherEvaluationSetting->rating_submission_time = $request->rating_submission_time;
                $aramiscTeacherEvaluationSetting->from_date = date('Y-m-d', strtotime($request->startDate));
                $aramiscTeacherEvaluationSetting->to_date = date('Y-m-d', strtotime($request->endDate));
            }
            $aramiscTeacherEvaluationSetting->update();
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function aramiscTeacherEvaluationSubmit(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'rating' => "required",
            'comment' => "required",
        ]);
        if ($validator->fails()) {
            Toastr::error('Empty Submission', 'Failed');
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $aramiscTeacherEvaluationSetting = TeacherEvaluationSetting::find(1);
            $aramiscTeacherEvaluation = new TeacherEvaluation();
            $aramiscTeacherEvaluation->rating = $request->rating;
            $aramiscTeacherEvaluation->comment = $request->comment;
            $aramiscTeacherEvaluation->record_id = $request->record_id;
            $aramiscTeacherEvaluation->subject_id = $request->subject_id;
            $aramiscTeacherEvaluation->teacher_id = $request->teacher_id;
            $aramiscTeacherEvaluation->student_id = $request->student_id;
            $aramiscTeacherEvaluation->parent_id = $request->parent_id;
            $aramiscTeacherEvaluation->role_id = Auth::user()->role_id;
            $aramiscTeacherEvaluation->academic_id = getAcademicId();
            if ($aramiscTeacherEvaluationSetting->auto_approval == 0) {
                $aramiscTeacherEvaluation->status = 1;
            }
            $aramiscTeacherEvaluation->save();
            Toastr::success('Operation Successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
