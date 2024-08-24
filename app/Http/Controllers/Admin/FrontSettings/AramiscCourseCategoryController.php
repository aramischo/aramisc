<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\AramiscCourse;
use App\AramiscCourseCategory;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\FrontSettings\AramiscCourseCategoryRequest;

class AramiscCourseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }
    public function index()
    {
        try{
            $course_categories = AramiscCourseCategory::where('school_id', app('school')->id)->get();
            return view('backEnd.course.course_category',compact('course_categories'));
        }catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(AramiscCourseCategoryRequest $request)
    {
     
        try {
          
            $destination = 'public/uploads/course/';
            $image=fileUpload($request->category_image,$destination);

            AramiscCourseCategory::create([
                'category_name' => $request->category_name,
                'category_image' => $image,
                'school_id' => app('school')->id,
            ]);

            Toastr::success('Operation Successfull', 'Success');
            return redirect('course-category');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        try{
            $editData = AramiscCourseCategory::where('id',$id)
                                ->where('school_id', app('school')->id)
                                ->first();

            $course_categories = AramiscCourseCategory::where('school_id', app('school')->id)->get();

            return view('backEnd.course.course_category',compact('editData','course_categories'));
        }catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(AramiscCourseCategoryRequest $request)
    {
        
        try{
            
            $destination = 'public/uploads/course/';

            $data = AramiscCourseCategory::find($request->id);
            $data->category_name = $request->category_name;
            $data->school_id = app('school')->id;
          
            $data->category_image = fileUpdate($data->category_image,$request->category_image,$destination);
          
            $result = $data->save();

            Toastr::success('Operation Successfull', 'Success');
            return redirect('course-category');
           
        }catch(\Exception $e){

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function delete(Request $request, $id)
    {
        try{
            $tables = AramiscCourse::where('category_id', 1)->first();
            if($tables == null){
                $data = AramiscCourseCategory::find($request->id);
                if ($data->category_image != "") {
                    unlink($data->category_image);
                }
                $data->delete();
            } else {
                $msg = 'This category is already assigned with a course.';
                Toastr::warning($msg, 'Warning');
                return redirect()->back();
            }
            Toastr::success('Operation Successfull', 'Success');
            return redirect('course-category');
        }catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function view($id)
    {
        try {
            $category_id = AramiscCourseCategory:: find($id);
            $courseCtaegories = AramiscCourse::where('category_id',$category_id->id)
                        ->where('school_id', app('school')->id)
                        ->get();
            return view('frontEnd.home.course_category', compact('category_id','courseCtaegories'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

}