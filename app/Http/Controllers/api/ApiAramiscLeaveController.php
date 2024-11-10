<?php

namespace App\Http\Controllers\api;

use App\User;
use App\AramiscStaff;
use App\AramiscStudent;
use App\AramiscLeaveType;
use App\ApiBaseMethod;
use App\AramiscLeaveDefine;
use App\AramiscAcademicYear;
use App\AramiscClassTeacher;
use App\AramiscLeaveRequest;
use App\AramiscNotification;
use App\AramiscGeneralSettings;
use Illuminate\Http\Request;
use App\AramiscAssignClassTeacher;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Modules\RolePermission\Entities\AramiscRole;

class ApiAramiscLeaveController extends Controller
{
    //
    // {
    //     "success": true,
    //     "data": {
    //         "my_leaves": [
    //         
    //             {
    //                 "id": 10,
    //                 "type": "new",
    //                 "days": 20
    //             }
    //         ]
    //     },
    //     "message": null
    // }
    public function myLeaveType(Request $request,$user_id){
        try{


            $user=User::find($user_id);
            
            if ($user->role_id !=3) {

                $leaves=DB::table('aramisc_leave_defines')->where('role_id', $user->role_id)
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
                ->where('aramisc_leave_defines.user_id',$user_id)
                ->where('aramisc_leave_defines.academic_id',AramiscAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))
                ->where('aramisc_leave_defines.school_id',$request->user()->school_id)  
                ->select('aramisc_leave_types.id','aramisc_leave_types.type','aramisc_leave_defines.days')         
                ->get();
                
             
            }else{
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }

    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                
                return ApiBaseMethod::sendResponse($leaves, null);
            }
         
        }catch (\Exception $e) {
      
          
        }
    }

    public function saas_myLeaveType(Request $request,$school_id,$user_id){
        
        try{


            $user=User::find($user_id);
            
            if ($user->role_id !=3) {

                $leaves=DB::table('aramisc_leave_defines')->where('role_id', $user->role_id)
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
                ->where('aramisc_leave_defines.user_id',$user_id)
                ->where('aramisc_leave_defines.academic_id',AramiscAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))
                ->where('aramisc_leave_defines.school_id',$request->user()->school_id)  
                ->select('aramisc_leave_types.id','aramisc_leave_types.type','aramisc_leave_defines.days')         
                ->get();
                
             
            }else{
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }

    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                
                return ApiBaseMethod::sendResponse($leaves, null);
            }
         
        }catch (\Exception $e) {
      
          
        }
    }
    // {
    //     "success": true,
    //     "data": {
    //         "my_leaves": [
         
    //             {
    //                 "id": 10,
    //                 "type": "new",
    //                 "days": 20
    //             }
    //         ],
    //         "apply_leaves": []
    //     },
    //     "message": null
    // }
   public function studentleaveApply(Request $request,$user_id)
    {
        try {
            $user =User::find($user_id);
              $std_id = AramiscStudent::leftjoin('aramisc_parents','aramisc_parents.id','aramisc_students.parent_id')
                                ->where('aramisc_parents.user_id',$user->id)
                                ->where('aramisc_students.active_status', 1)
                                ->where('aramisc_students.academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))
                                ->where('aramisc_students.school_id',$request->user()->school_id)
                                ->select('aramisc_students.user_id')
                                ->first();
                $my_leaves = AramiscLeaveDefine::join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
            //   ->where('role_id', 2)
               ->where('aramisc_leave_defines.user_id',$user_id)
               ->where('aramisc_leave_defines.school_id',$request->user()->school_id)
               ->select('aramisc_leave_defines.id','aramisc_leave_types.type','aramisc_leave_defines.days') 
               ->get();
                $apply_leaves = AramiscLeaveRequest::where('staff_id', $user_id)
                ->where('role_id', 2)
                ->where('aramisc_leave_requests.approve_status', '=', 'P')
                ->where('aramisc_leave_requests.active_status', 1)
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_requests.type_id')
                ->where('aramisc_leave_requests.academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))
                ->where('aramisc_leave_requests.school_id',$request->user()->school_id)
                ->select('aramisc_leave_requests.id','aramisc_leave_types.type','aramisc_leave_requests.apply_date','aramisc_leave_requests.leave_from','aramisc_leave_requests.leave_to','aramisc_leave_requests.approve_status','aramisc_leave_requests.active_status')
                ->get();
         

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['my_leaves'] = $my_leaves->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();
             
                return ApiBaseMethod::sendResponse($data, null);
            }
           
        } catch (\Exception $e) {
 
           
        }
    }

    public function leaveStoreStudent(Request $request)
    {
        
      
        $user=User::find($request->login_id);
        // if($user->role_id !=2){
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Invalid Student ID, please try again.');

        //     } 
        // }
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "nullable|exists:aramisc_leave_types,id",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'login_id' => "required",               
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
                $file = $request->file('attach_file');             
                $fileName = $request->input('login_id') . time() . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }
          

            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $request->login_id;
            $apply_leave->role_id = $user->role_id;
            $apply_leave->apply_date = date('Y-m-d', strtotime($request->apply_date));
            if($request->leave_type){
                 $apply_leave->leave_define_id = $request->leave_type;
            }
           
            $apply_leave->type_id = $request->leave_type;
            $apply_leave->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->reason;
            $apply_leave->file = $fileName;
            $apply_leave->school_id = $request->user()->school_id;
            $apply_leave->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id);
            $result = $apply_leave->save();

         
            if($user->role_id==2){
                $student=AramiscStudent::where('user_id',$request->login_id)->first();

                $teacher_assign=AramiscAssignClassTeacher::where('class_id',$student->class_id)->where('section_id',$student->section_id)->first();
                if($teacher_assign){
                    $classTeacher=AramiscClassTeacher::select('teacher_id')
                                            ->where('assign_class_teacher_id',$teacher_assign->id)
                                            ->first();  
                                            
                   $notification = new AramiscNotification();
                    $notification->message = $student->full_name .'Apply For Leave';
                    $notification->is_read = 0;
                    $notification->url = "pending-leave";
                    $notification->user_id = $user->id;
                    $notification->role_id = $user->role_id;
                    $notification->school_id = $request->user()->school_id;
                    $notification->academic_id = $student->academic_id;
                    $notification->date = date('Y-m-d');
                    $notification->save(); 
                }
                                       

            }
         

            if($result){
                $users = User::whereIn('role_id',[1,5])->where('school_id', $request->user()->school_id)->get();
                foreach($users as $user){
                    $notification = new AramiscNotification();
                    $notification->message = $user->full_name .'Apply For Leave';
                    $notification->is_read = 0;
                    $notification->url = "pending-leave";
                    $notification->user_id = $user->id;
                    $notification->role_id = $user->role_id;
                    $notification->school_id = 1;
                    $notification->academic_id = $user->academic_id;
                    $notification->date = date('Y-m-d');
                    $notification->save();
                }
            }
            
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } 
            
        } catch (\Exception $e) {
            return $e;
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
    }

    public function paretnLeave(Request $request,$student_id){

    }

    // {
    //     "success": true,
    //     "data": {
    //         "pending_leaves": [
    //             {
    //                 "id": 4,
    //                 "full_name": "Tad Preston",
    //                 "apply_date": "2021-04-12",
    //                 "leave_from": "2021-04-15",
    //                 "leave_to": "2021-04-17",
    //                 "reason": "test",
    //                 "file": "",
    //                 "type": "sick",
    //                 "approve_status": "P"
    //             },
    //             {
    //                 "id": 12,
    //                 "full_name": "Ashely Coleman",
    //                 "apply_date": "2021-04-14",
    //                 "leave_from": "2021-04-17",
    //                 "leave_to": "2021-04-19",
    //                 "reason": null,
    //                 "file": "",
    //                 "type": "sick",
    //                 "approve_status": "P"
    //             }
    //         ]
    //     },
    //     "message": null
    // }

    public function pendingLeave(Request $request,$user_id){
        try {
            $user =User::select('id','role_id')->find($user_id);
            $staff = AramiscStaff::where('user_id', $user->id)->first();

            
            if ($user->role_id==1 || $user->role_id==5) {
                $pending_leaves = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
                ->where('aramisc_leave_requests.approve_status', '=', $request->purpose)
                ->where('aramisc_leave_requests.school_id', '=',$request->user()->school_id)
                ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
                ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
                ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                ->select('aramisc_leave_requests.id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
                ->get();
            }elseif($user->role_id == 4){
                
                
                    $pending_leaves = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
                                ->where('aramisc_leave_requests.approve_status', '=', $request->purpose)
                                ->where('aramisc_leave_requests.staff_id', '=', $user->id)
                                ->where('aramisc_leave_requests.school_id', '=',$request->user()->school_id)
                                ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
                                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
                                ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
                                ->select('aramisc_leave_requests.id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
                                ->get();

                
            }else{
   
                    $pending_leaves = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
                    ->where('aramisc_leave_requests.staff_id', '=', $user->id)
                    ->where('aramisc_leave_requests.approve_status', '=', $request->purpose)
                    ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
                    ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
                    ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')  
                    ->where('aramisc_leave_requests.school_id',$request->user()->school_id)
                    ->where('aramisc_leave_requests.academic_id', getAcademicId())
                    ->select('aramisc_leave_requests.id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
                    ->get();

            }

        
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['pending_leaves'] = $pending_leaves->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
          
        }
    }
    public function leaveApprove(Request $request){
        try {
            $input = $request->all();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $validator = Validator::make($input, [
                  
                    'id' => "required",
                    'user_id' => "required",
                    'approve_status' => 'required',
                  
                ]);
            } 
        
            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
            }
            $user=User::select('id','role_id')->find($request->user_id);
            if ($user->role_id==1 || $user->role_id==5) {
                $leave_request_data = AramiscLeaveRequest::find($request->id);
            }else{
                $leave_request_data = AramiscLeaveRequest::where('id',$request->id)->where('school_id',$request->user()->school_id)->first();
            }
            $staff_id = $leave_request_data->staff_id;
            $role_id = $leave_request_data->role_id;
            $leave_request_data->approve_status = $request->approve_status;
            $leave_request_data->academic_id = getAcademicId();
            $result = $leave_request_data->save();


            $notification = new AramiscNotification;         
            $notification->user_id = $leave_request_data->staff_id;
            $notification->role_id = $role_id;
            $notification->date = date('Y-m-d');
            $notification->message = 'Leave status updated';
            $notification->school_id =$request->user()->school_id;
            $notification->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id);
            $notification->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been updates successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } 
        } catch (\Exception $e) {
        
            return ApiBaseMethod::sendError('Error.',$e->getMessage());
        }
    }
    public function allPendingList(Request $request){

        $pendingRequest = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
        ->select('aramisc_leave_requests.id','aramisc_leave_requests.staff_id','users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
        ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
        ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
        ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
        ->where('aramisc_leave_requests.approve_status', '=', 'P')
        ->where('aramisc_leave_requests.school_id', $request->user()->school_id)
        ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['pending_request'] = $pendingRequest->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function allAprroveList(Request $request){
        $aprroveRequest = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
        ->select('aramisc_leave_requests.id','aramisc_leave_requests.staff_id','users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
        ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
        ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
        ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
        ->where('aramisc_leave_requests.school_id', $request->user()->school_id)
        ->where('aramisc_leave_requests.approve_status', '=', 'A')
        ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['aprrove_request'] = $aprroveRequest->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function allRejectedList(Request $request){
        $rejectedRequest = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
        ->select('aramisc_leave_requests.id','aramisc_leave_requests.staff_id','users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
        ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
        ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
        ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
        ->where('aramisc_leave_requests.school_id', $request->user()->school_id)
        ->where('aramisc_leave_requests.approve_status', '=', 'C')
        ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['rejected_request'] = $rejectedRequest->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function rejectUserLeave(Request $request,$user_id){
        $rejectedRequest = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
        ->select('aramisc_leave_requests.id','aramisc_leave_requests.staff_id','users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
        ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
        ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
        ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
        ->where('aramisc_leave_requests.staff_id', '=', $user_id)
        ->where('aramisc_leave_requests.approve_status', '=', 'C')
        ->where('aramisc_leave_requests.school_id', $request->user()->school_id)
        ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['rejected_request'] = $rejectedRequest->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function userApproveLeave(Request $request,$user_id){
        $aprroveRequest = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
        ->select('aramisc_leave_requests.id','aramisc_leave_requests.staff_id','users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
        ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
        ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
        ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
        ->where('aramisc_leave_requests.staff_id', '=', $user_id)
        ->where('aramisc_leave_requests.approve_status', '=', 'A')
        ->where('aramisc_leave_requests.school_id', $request->user()->school_id)
        ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['aprrove_request'] = $aprroveRequest->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }


    public function rejectLeave(Request $request)
    {
        try {
            $reject_request = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
                ->select('aramisc_leave_requests.id', 'full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
                ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
                ->join('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                ->where('aramisc_leave_requests.approve_status', '=', 'R')
                ->where('aramisc_leave_requests.school_id', $request->user()->school_id)
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['reject_request'] = $reject_request->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
           return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_rejectLeave(Request $request, $school_id)
    {
        try {
            $reject_request = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
                ->select('aramisc_leave_requests.id', 'full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
                ->join('users', 'aramisc_leave_requests.staff_id', '=', 'users.id')
                ->join('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                ->where('aramisc_leave_requests.approve_status', '=', 'C')
                ->where('aramisc_leave_requests.school_id',$request->user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['reject_request'] = $reject_request->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
           return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
}
