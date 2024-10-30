<?php

namespace App\Http\Controllers\Admin\Leave;
use App\AramiscLeaveType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Leave\AramiscLeaveTypeRequest;

class AramiscLeaveTypeController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
	}

    public function index(Request $request)
    {
        try{
            $leave_types = AramiscLeaveType::get();
            return view('backEnd.humanResource.leave_type', compact('leave_types'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function store(AramiscLeaveTypeRequest $request)
    {
        try{
            $leave_type = new AramiscLeaveType();
            $leave_type->type = $request->type;
            $leave_type->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $leave_type->un_academic_id = getAcademicId();
            }else{
                $leave_type->academic_id = getAcademicId();
            }
            $leave_type->save();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function show(Request $request, $id)
    {
        try{
            if (checkAdmin()) {
                $leave_type = AramiscLeaveType::find($id);
            }else{
                $leave_type = AramiscLeaveType::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            $leave_types = AramiscLeaveType::get();
            return view('backEnd.humanResource.leave_type', compact('leave_types', 'leave_type'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function update(AramiscLeaveTypeRequest $request, $id)
    {
        try{
            if (checkAdmin()) {
                $leave_type = AramiscLeaveType::find($request->id);
            }else{
                $leave_type = AramiscLeaveType::where('id',$request->id)->where('school_id',Auth::user()->school_id)->first();
            }
            $leave_type->type = $request->type;
            $leave_type->total_days = $request->total_days;
            if(moduleStatusCheck('University')){
                $leave_type->un_academic_id = getAcademicId();
            }
            $leave_type->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('leave-type');
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        try{
            $tables = \App\tableList::getTableList('type_id', $id);
            try {
                if ($tables==null) {
                    if (checkAdmin()) {
                        AramiscLeaveType::destroy($id);
                    }else{
                        AramiscLeaveType::where('id',$id)->where('school_id',Auth::user()->school_id)->delete();
                    }

                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                }else{
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }

            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}