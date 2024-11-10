<?php

namespace App\Http\Controllers\teacher;

use App\AramiscStaff;
use App\YearCheck;
use App\ApiBaseMethod;
use App\AramiscLeaveRequest;
use App\AramiscGeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

    public function leaveTypeList(Request $request)
    {
        try {
            $leave_type = DB::table('aramisc_leave_defines')
                ->where('role_id', 4)
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
                ->where('aramisc_leave_defines.active_status', 1)
                ->select('aramisc_leave_types.id', 'type', 'total_days')
                ->distinct('aramisc_leave_defines.type_id')
               ->where('aramisc_leave_defines.school_id',Auth::user()->school_id) ->get();

            //return $leave_type;
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($leave_type, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function staffLeaveList(Request $request, $id)
    {
        try {
            $teacher = AramiscStaff::where('user_id', '=', $id)->first();
            $teacher_id = $teacher->id;

            $leave_list = AramiscLeaveRequest::where('staff_id', '=', $teacher_id)
                ->join('aramisc_leave_defines', 'aramisc_leave_defines.id', '=', 'aramisc_leave_requests.leave_define_id')
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
               ->where('aramisc_leave_defines.school_id',Auth::user()->school_id) ->get();
            $status = 'P for Pending, A for Approve, R for reject';
            $data = [];
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['leave_list'] = $leave_list->toArray();
                $data['status'] = $status;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function applyLeave(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'teacher_id' => "required",
                'reason' => "required",
                'attach_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        try {
            $fileName = "";
            if ($request->file('attach_file') != "") {
                $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                $file = $request->file('attach_file');
                $fileSize =  filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if($fileSizeKb >= $maxFileSize){
                    Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }

            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $request->input('teacher_id');
            $apply_leave->role_id = 4;
            $apply_leave->apply_date = date('Y-m-d');
            $apply_leave->leave_define_id = $request->input('leave_type');
            $apply_leave->type_id = $request->input('leave_type');
            $apply_leave->leave_from = $request->input('leave_from');
            $apply_leave->leave_to = $request->input('leave_to');
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->input('reason');
            $apply_leave->school_id = Auth::user()->school_id;
            $apply_leave->academic_id = getAcademicId();
            if ($fileName != "") {
                $apply_leave->file = $fileName;
            }


        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
