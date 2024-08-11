<?php

namespace App\Http\Controllers\api;

use App\Role;

use App\User;

use App\SmClass;

use App\SmSection;

use App\SmStudent;

use App\SmSubject;

use App\YearCheck;



use App\ApiBaseMethod;

use App\SmAcademicYear;


use App\SmAssignSubject;

use App\SmSubjectAttendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SubjectWiseAttendanceController extends Controller
{
    public function SelectSubject(Request $request){
       
        $input = $request->all();

        $validator = Validator::make($input, [

            'class' => 'required',

            'section' => 'required'

        ]);
        if ($validator->fails()) {

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()

                ->withErrors($validator)

                ->withInput();

        }
            $subject_all = SmAssignSubject::where('class_id', '=', $request->class)
            ->where('section_id', $request->section)
            ->distinct('subject_id')
            ->get();

        $students = [];
        foreach ($subject_all as $allSubject) {
            $students[] = SmSubject::where('id',$allSubject->subject_id)->first(['subject_name','id','subject_type']);
        }
        return ApiBaseMethod::sendResponse($students, null);
    }
   

    public function studentSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'subject' => 'required',
            'aramiscAttendance_date' => 'required'
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
            $classes = SmClass::where('active_status', 1)->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $students = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('active_status', 1)->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if ($students->isEmpty()) {
                return ApiBaseMethod::sendError('No Result Found',null);
            }

            $already_assigned_students = [];
            $new_students = [];
            $aramiscAttendance_type = "";
            foreach ($students as $student) {
                $aramiscAttendance = SmSubjectAttendance::where('student_id', $student->id)->where('subject_id', $request->subject)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->aramiscAttendance_date)))->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->first();

                if ($aramiscAttendance != "") {
                    $already_assigned_students[] = $aramiscAttendance;
                    $aramiscAttendance_type =  $aramiscAttendance->aramiscAttendance_type;
                } else {
                    $new_students[] =  $student;
                }
            }

            $class_id = $request->class;
            $class_info = SmClass::find($request->class);
            $section_info = SmSection::find($request->section);

            $search_info['class_name'] = $class_info->class_name;
            $search_info['section_name'] = $section_info->section_name;
            $search_info['date'] = $request->aramiscAttendance_date;


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
        } catch (\Exception $e) {
           return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentAttendanceStore(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'subject' => 'required',
            'date' => 'required'
        ]);



        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        // return $request;
        try {
            foreach ($request->id as $student) {
                $aramiscAttendance = SmSubjectAttendance::where('student_id', $student)
                ->where('subject_id', $request->subject)
                ->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))
                ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->first();

                if ($aramiscAttendance != "") {
                    $aramiscAttendance->delete();
                }


                $aramiscAttendance = new SmSubjectAttendance();
                $aramiscAttendance->student_id = $student;
                $aramiscAttendance->subject_id = $request->subject;
                if (isset($request->mark_holiday)) {
                    $aramiscAttendance->aramiscAttendance_type = "H";
                } else {
                    $aramiscAttendance->aramiscAttendance_type = $request->aramiscAttendance[$student];
                    $aramiscAttendance->notes = $request->note[$student];
                }
                $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->date));
                $aramiscAttendance->save();

            }

                return ApiBaseMethod::sendResponse(null, 'Student aramiscAttendance been submitted successfully');
        } catch (\Exception $e) {
           return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function studentAttendanceCheck(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'date' => "required",
            'class' => "required",
            'subject' => 'required',
            'section' => "required"
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
 
        }
        $student_ids = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->select('id')->get();
        $students = SmStudent::with('class','section')->where('class_id', $request->class)->where('section_id', $request->section)->get();
        $studentAttendance=SmSubjectAttendance::whereIn('student_id', $student_ids)->where('subject_id', $request->subject)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))->orderby('student_id','ASC')->get();

                $student_aramiscAttendance=[];
                $no_aramiscAttendance=[];
                 if(count($studentAttendance)==0){
         
			            foreach($students as $student){

			                $d['id']=$student->id;
			                $d['student_id']=$student->id;
			                $d['student_photo']=@$student->student_photo;
			                $d['full_name']=$student->full_name;
			                $d['roll_no']=  $student->roll_no;
			                $d['class_name']=$student->class->class_name;
			                $d['section_name']=  $student->section->section_name;    
			                $d['aramiscAttendance_type']=null;
			                $d['user_id']=$student->user_id;
			    
			                $no_aramiscAttendance[]=$d;
			            }
       				 }else{
			        foreach ($studentAttendance as $aramiscAttendance){

			            $d['id']=$aramiscAttendance->id;
			            $d['student_id']=$aramiscAttendance->student_id;
			            $d['student_photo']=$aramiscAttendance->student->student_photo;
			            $d['full_name']=$aramiscAttendance->student->full_name;
			            $d['roll_no']=  $aramiscAttendance->student->roll_no;
			            $d['class_name']=$aramiscAttendance->student->class->class_name;
			            $d['section_name']=  $aramiscAttendance->student->section->section_name;    
			            $d['aramiscAttendance_type']=$aramiscAttendance->aramiscAttendance_type;
			            $d['user_id']=$aramiscAttendance->student->user_id;
			            
			            $student_aramiscAttendance[]=$d;
			        }
                }
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
               if (count($studentAttendance)>0) {
                    return ApiBaseMethod::sendResponse($student_aramiscAttendance,null);
                } else {
                    return ApiBaseMethod::sendResponse($no_aramiscAttendance,'Student aramiscAttendance not done yet');
                }
         }       


        // if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //     return ApiBaseMethod::sendResponse(null, 'Student aramiscAttendance been submitted successfully');
        // }
    }
    public function studentAttendanceStoreFirst(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'date' => "required",
            'class' => "required",
            'subject' => 'required',
            'section' => "required"
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $students = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->select('id')->get();
        $aramiscAttendance = SmSubjectAttendance::where('student_id', $request->id)->where('subject_id', $request->subject)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))->first();
        if (empty($aramiscAttendance)) {
            foreach ($students as $student) {
                $aramiscAttendance = SmSubjectAttendance::where('student_id', $student->id)->where('subject_id', $request->subject)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))->first();
                if ($aramiscAttendance != "") {
                    $aramiscAttendance->delete();
                } else {
                    $aramiscAttendance = new SmSubjectAttendance();
                    $aramiscAttendance->student_id = $student->id;
                    $aramiscAttendance->subject_id = $request->subject;
                    $aramiscAttendance->aramiscAttendance_type = "P";
                    $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->date));
                    $aramiscAttendance->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                    $aramiscAttendance->save();
                }
            }
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse(null, 'Student aramiscAttendance been submitted successfully');
        }
    }
    public function studentAttendanceStoreSecond(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            // 'id' => "required",
            'date' => "required",
            'aramiscAttendance' => "required",
            'class' => "required",
            'subject' => 'required',
            'section' => "required"
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
            
            $students = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->select('id')->get();
        $aramiscAttendance = SmSubjectAttendance::where('student_id', $request->id)->where('subject_id', $request->subject)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))->first();
       
        if (empty($aramiscAttendance)) {
            foreach ($students as $student) {
                $aramiscAttendance = SmSubjectAttendance::where('student_id', $student->id)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))->first();
                if ($aramiscAttendance != "") {
                    $aramiscAttendance->delete();
                }
                
                $aramiscAttendance = new SmSubjectAttendance();
                $aramiscAttendance->student_id = $student->id;
                $aramiscAttendance->subject_id = $request->subject;
                $aramiscAttendance->aramiscAttendance_type =$request->aramiscAttendance;
                $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->date));
                $aramiscAttendance->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                $aramiscAttendance->save();
                
            }
        }
        $aramiscAttendance = SmSubjectAttendance::where('student_id', $request->id)->where('subject_id', $request->subject)->where('aramiscAttendance_date', date('Y-m-d', strtotime($request->date)))->first();
        if ($aramiscAttendance != "") {
            $aramiscAttendance->delete();
        }
        $aramiscAttendance = new SmSubjectAttendance();
        $aramiscAttendance->student_id = $request->id;
        $aramiscAttendance->subject_id = $request->subject;
        $aramiscAttendance->aramiscAttendance_type = $request->aramiscAttendance;
        $aramiscAttendance->aramiscAttendance_date = date('Y-m-d', strtotime($request->date));
        $aramiscAttendance->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $aramiscAttendance->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Student aramiscAttendance been submitted successfully');
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
       
    }
}
