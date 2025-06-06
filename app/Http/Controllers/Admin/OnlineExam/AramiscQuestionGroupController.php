<?php

namespace App\Http\Controllers\Admin\OnlineExam;

use App\AramiscQuestionGroup;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\OnlineExam\AramiscQuestionGroupRequest;

class AramiscQuestionGroupController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

  
    public function index()
    {
        try{
            $groups = AramiscQuestionGroup::get();
            return view('backEnd.examination.question_group', compact('groups'));
        }catch (\Exception $e) {
            
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function store(AramiscQuestionGroupRequest $request)
    {
  
        try{
            $group = new AramiscQuestionGroup();
            $group->title = $request->title;
            $group->school_id = Auth::user()->school_id;
            $group->academic_id = getAcademicId();
            $group->save();
         
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
           
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }


    public function show($id)
    {
        try{
            $group = AramiscQuestionGroup::find($id);            
            $groups = AramiscQuestionGroup::get();
            return view('backEnd.examination.question_group', compact('groups', 'group'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }
    public function update(AramiscQuestionGroupRequest $request, $id)
    {
   
        try{
            $group = AramiscQuestionGroup::find($request->id);             
            $group->title = $request->title;
            $group->save();
            Toastr::success('Operation successful', 'Success');
            return redirect('question-group');
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function destroy($id)
    {
        $tables = \App\tableList::getTableList('question_group_id', $id);

        try{
            if ($tables==null) {
                 $group = AramiscQuestionGroup::destroy($id);                 
                 Toastr::success('Operation successful', 'Success');
                 return redirect('question-group');

            } else {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }


        }catch (\Exception $e) {
           $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
            Toastr::error($msg, 'Failed');
           return redirect()->back();
        }
    }
}