<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\AramiscCoursePage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\FrontSettings\AramiscCourseHeadingRequest;

class AramiscCourseHeadingController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }
    public function index()
    {

        try {
            $AramiscCoursePage = AramiscCoursePage::where('school_id', app('school')->id)->first();
            $update = "";

            return view('backEnd.frontSettings.course.courseHeadingUpdate', compact('AramiscCoursePage', 'update'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function update(AramiscCourseHeadingRequest $request)
    {

        try {     
         


            $destination  = 'public/uploads/about_page/';
            $course_heading = AramiscCoursePage::where('school_id', app('school')->id)->first();
            if($course_heading){
               
                $course_heading->image     = fileUpdate($course_heading->image,$request->image,$destination);
               
            }else{
                $course_heading = new AramiscCoursePage();
                $course_heading->image     = fileUpload($request->image,$destination);               
                $course_heading->school_id = app('school')->id;
            }
            $course_heading->title = $request->title;
            $course_heading->description = $request->description;
            $course_heading->main_title = $request->main_title;
            $course_heading->main_description = $request->main_description;
            $course_heading->button_text = $request->button_text;
            $course_heading->button_url = $request->button_url;
            $course_heading->save();

         
            Toastr::success('Operation successful', 'Success');
            return redirect('course-heading-update');
           
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
