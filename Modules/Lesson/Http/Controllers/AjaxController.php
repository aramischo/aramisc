<?php

namespace Modules\Lesson\Http\Controllers;

use App\AramiscStaff;
use App\AramiscSubject;
use App\AramiscAssignSubject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\Lesson\Entities\AramiscLesson;;
use Illuminate\Contracts\Support\Renderable;
use Modules\Lesson\Entities\AramiscLessonTopicDetail;

class AjaxController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
        public function ajaxSelectLesson(Request $request){
        try {
           
            $lesson_all=AramiscLesson::where('class_id',$request->class)
            ->where('section_id','=',$request->section)
            ->where('subject_id','=',$request->subject)
            ->get(['id', 'lesson_title']);

            $lessons=[];
            foreach ($lesson_all as $lesson) {
                $lessons[]=$lesson;
            }
            return response()->json([$lessons]);
        } catch (\Exception $e) {
           Toastr::error('Operation Failed','Failed');
           return redirect()->back(); 
        }
    }
    //get topic from lesson 
    public function ajaxSelectTopic(Request $request)
    {
        try {
           
            $topic_all = AramiscLessonTopicDetail::where('lesson_id', $request->lesson_id)
                                        ->distinct('topic_id')
                                        ->get();
            $topics=[];
            foreach ($topic_all as $topic) {
                $topics[]=$topic;
            }
            return response()->json([$topics]);
        } catch (\Exception $e) {

           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }

    public function ajaxGetTopicRow(Request $request)
    {
        $topics = AramiscLessonTopicDetail::where('lesson_id', $request->lesson_id)
                                        ->distinct('topic_id')
                                        ->get();
        return view('lesson::topic_row', compact('topics'));
    }
    //edit lesson plan
    public function getTopicRow($lessonPlanDetail)
    {
       return $topics = AramiscLessonTopicDetail::where('lesson_id', $lessonPlanDetail->lesson_detail_id)
                                        ->distinct('topic_id')
                                        ->get();
                                     
        return view('lesson::topic_row', compact('topics', 'lessonPlanDetail'));
    }
    public function getSubject(Request $request){


        $class_id = $request->class;
        $selectedSections = $request->message_to_section;

        $subjectId=AramiscSubject::query();
        $subjectId=$subjectId->where('class_id', $class_id);
         foreach ($selectedSections as $key => $value) {            
            $subjectId=$subjectId->where('section_id', $value);  
           
         }
         return $subjectId->get();
    
        

    }
    public function getSubjectLesson(Request $request){
        try {
           
            $staff_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
            // return $staff_info;
            if (teacherAccess()) {
                $subject_all = AramiscAssignSubject::where('class_id', '=', $request->class_id)->where('teacher_id', $staff_info->id)->distinct('subject_id')->get();

            } else {
                $subject_all = AramiscAssignSubject::where('class_id', '=', $request->class_id)->distinct('subject_id')->get();

            }
    
            $students = [];
            foreach ($subject_all as $allSubject) {
                $students[] = AramiscSubject::find($allSubject->subject_id);
            }
            return response()->json([$students]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        
    }
  
}
