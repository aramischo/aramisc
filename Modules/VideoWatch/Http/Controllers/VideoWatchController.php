<?php

namespace Modules\VideoWatch\Http\Controllers;

use DB;
use App\AramiscClass;
use App\AramiscStudent;
use App\AramiscClassSection;
use Illuminate\Http\Request;
use App\AramiscTeacherUploadContent;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Renderable;
use Modules\VideoWatch\Entities\AramiscVideoWatch;

class VideoWatchController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function view($id)
    {
        $ContentDetails = AramiscTeacherUploadContent::find($id);
        return view('videowatch::index',compact('ContentDetails'));
    }
    public function watchLog($id)
    {
        $content_info=DB::table('aramisc_teacher_upload_contents')->where('id',$id)->first();

        $seen_students=[];
        $unseen_students=[];



        $viewable_student_list=[];

        if ($content_info->available_for_all_classes==1) {
            $students=AramiscStudent::where('school_id',Auth::user()->school_id)->select('id','user_id')
            ->where('academic_id', getAcademicId())->get();
            foreach ($students as $key => $value) {
                $viewable_student_list[]=$value->user_id;
            }
            
        } else{
            $students=AramiscStudent::where('school_id',Auth::user()->school_id)->select('id','user_id')
            ->where('class_id',$content_info->class)
            ->where('section_id',$content_info->section)
            ->where('academic_id', getAcademicId())->get();
            foreach ($students as $key => $value) {
                $viewable_student_list[]=$value->user_id;
            }
        }
        $watchLogs = AramiscVideoWatch::where('study_material_id',$id)->get();
        foreach ($watchLogs as $key => $value) {
            $seen_students[]=$value->student_id;
        }

        $watchLogs = AramiscVideoWatch::where('aramisc_video_watches.study_material_id',$id)
        ->leftjoin('aramisc_students','aramisc_students.user_id','=','aramisc_video_watches.student_id')
        ->leftjoin('aramisc_teacher_upload_contents','aramisc_teacher_upload_contents.id','=','aramisc_video_watches.study_material_id')
        ->select('aramisc_video_watches.*','aramisc_students.id','full_name','admission_no','roll_no','content_title')
        ->get();

        $unseen_lists=[];
        foreach ($viewable_student_list as $key => $value) {
            if (!in_array($value,$seen_students)) {

                $student=AramiscStudent::where('user_id',$value)->first();
                $unseen_lists[$value]['id']=$student->id;
                $unseen_lists[$value]['full_name']=$student->full_name;
                $unseen_lists[$value]['admission_no']=$student->admission_no;
                $unseen_lists[$value]['roll_no']=$student->roll_no;
                $unseen_lists[$value]['class']=$student->class->class_name;
                $unseen_lists[$value]['section']=$student->section->section_name;
            }
        }
    
        return view('videowatch::watch_log',compact('watchLogs','unseen_lists'));
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function traceData(Request $request)
    {
       
        $check_exist=AramiscVideoWatch::where('student_id',$request->user_id)->where('study_material_id',$request->study_id)->first();
        // if ($check_exist==null) {
        if (Auth::user()->role_id==2 && $check_exist==null) {
            date_default_timezone_set(timeZone());

            $watch_trace=new AramiscVideoWatch();
            $watch_trace->student_id=$request->user_id;
            $watch_trace->study_material_id=$request->study_id;
            $watch_trace->time=date("h:i:sa");
            $watch_trace->save();
        }
        return $request;
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('videowatch::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('videowatch::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
