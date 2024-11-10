<?php

namespace Modules\ExamPlan\Http\Controllers;

use App\AramiscExam;
use App\AramiscStudent;
use App\AramiscExamSchedule;
use App\AramiscAssignSubject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\ExamPlan\Entities\AdmitCard;
use Illuminate\Contracts\Support\Renderable;
use Modules\ExamPlan\Entities\AdmitCardSetting;

class StudentExamPlanController extends Controller
{
    public function admitCard()
    {
        try{
            $student = Auth::user()->student;
            $records = StudentRecord::where('is_promote',0)
                                    ->where('student_id',$student->id)
                                    ->where('academic_id',getAcademicId())
                                    ->where('school_id',Auth::user()->school_id)
                                    ->get();
            return view('examplan::studentAdmitCard',compact('records'));
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }

    }

    public function admitCardSearch(Request $request)
    {
        try{
            $aramiscExam = AramiscExam::findOrFail($request->exam);
            if(auth()->user()->role_id == 3){
                $student = AramiscStudent::find($request->student_id);
            }else{
                $student = Auth::user()->student;
            }
            $studentRecord =StudentRecord::where('student_id',$student->id)
                                            ->where('class_id',$aramiscExam->class_id)
                                            ->where('section_id',$aramiscExam->section_id)
                                            ->where('school_id',Auth::user()->school_id)
                                            ->where('academic_id',getAcademicId())
                                            ->first();

            $exam_routines = AramiscExamSchedule::where('class_id', $aramiscExam->class_id)
                                            ->where('section_id', $aramiscExam->section_id)
                                            ->where('exam_term_id', $aramiscExam->exam_type_id)
                                            ->orderBy('date', 'ASC')
                                            ->get();
            if($exam_routines){
                
                $admit = AdmitCard::where('academic_id',getAcademicId())
                                    ->where('student_record_id', $studentRecord->id)
                                    ->where('exam_type_id', $aramiscExam->exam_type_id)
                                    ->first();
                if($admit){
                return redirect()->route('examplan.admitCardDownload',$admit->id);
                }else{
                    Toastr::warning('Admit Card Not Pulished Yet','Warning');
                    return redirect()->back();
                }                    
            }else{
                Toastr::warning('Exam Routine Not Pulished Yet','Warning');
                return redirect()->back();
            }

        }
        catch( \Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }

    }

    public function admitCardDownload($id)
    {
        try{

            $admit = AdmitCard::find($id);
            $studentRecord = StudentRecord::find($admit->student_record_id);
            $student = AramiscStudent::find($studentRecord->student_id);
            $setting = AdmitCardSetting::where('school_id',Auth::user()->school_id)
                                         ->where('academic_id',getAcademicId())   
                                        ->first();
            $assign_subjects = AramiscAssignSubject::where('class_id', $studentRecord->class_id)->where('section_id', $studentRecord->section_id)
                                        ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_routines = AramiscExamSchedule::where('class_id', $studentRecord->class_id)
                                        ->where('section_id', $studentRecord->section_id)
                                        ->where('exam_term_id', $admit->exam_type_id)->orderBy('date', 'ASC')->get();
           
            if($setting->admit_layout == 1){
                return view('examplan::studentAdmitCardDownload',compact('setting','assign_subjects','exam_routines','studentRecord','student','admit'));
            }else{
                return view('examplan::studentAdmitCardDownload_two',compact('setting','assign_subjects','exam_routines','studentRecord','student','admit'));
            }
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }
    }

    public function admitCardParent($student_id){
        try{
            $records = StudentRecord::where('is_promote',0)
            ->where('student_id',$student_id)
            ->where('academic_id',getAcademicId())
            ->where('school_id',Auth::user()->school_id)
            ->get();
            return view('examplan::studentAdmitCard',compact('records' ,'student_id'));
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }
    }




}
