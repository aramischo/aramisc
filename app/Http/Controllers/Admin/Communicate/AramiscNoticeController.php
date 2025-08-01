<?php

namespace App\Http\Controllers\Admin\Communicate;

use App\GlobalVariable;
use App\User;
use Carbon\Carbon;
use App\AramiscNoticeBoard;
use App\AramiscNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\NoticeRequestForm;
use App\Traits\NotificationSend;
use Modules\RolePermission\Entities\AramiscRole;
use Modules\Saas\Entities\AramiscAdministratorNotice;

class AramiscNoticeController extends Controller
{
    use NotificationSend;
    public function __construct()
	{
        $this->middleware('PM');
	}

    public function sendMessage(Request $request)
    {
        try {
            $roles = AramiscRole::when((generalSetting()->with_guardian !=1), function ($query) {
                $query->where('id', '!=', 3);
            })->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            return view('backEnd.communicate.sendMessage', compact('roles'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function saveNoticeData(NoticeRequestForm $request)
    {
        try {
            $noticeData = new AramiscNoticeBoard();
            if (isset($request->is_published)) {
                $noticeData->is_published = $request->is_published;
            }
            $noticeData->notice_title = $request->notice_title;
            $noticeData->notice_message = $request->notice_message;
            $noticeData->notice_date = date('Y-m-d', strtotime($request->notice_date));
            $noticeData->publish_on = date('Y-m-d', strtotime($request->publish_on));
            $noticeData->inform_to = json_encode($request->role);
            $noticeData->created_by = Auth::user()->id;
            $noticeData->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $noticeData->un_academic_id = getAcademicId();
            }else{
                $noticeData->academic_id = getAcademicId();
            }
            $noticeData->save();

            $data['title'] = $request->notice_title;
            $data['notice'] = $request->notice_title;

            foreach($request->role as $role_id){
                $userIds = User::where('role_id', $role_id)->where('active_status', 1)->pluck('id')->toArray();
                if($role_id == 4){
                    $this->sent_notifications('Notice', $userIds, $data, ['Teacher']);
                }elseif($role_id == 2){
                    $this->sent_notifications('Notice', $userIds, $data, ['Student']);
                }elseif($role_id == 3){
                    $this->sent_notifications('Notice', $userIds, $data, ['Parent']);
                }elseif($role_id == GlobalVariable::isAlumni()){
                    $this->sent_notifications('Notice', $userIds, $data, ['Alumni']);
                }
            }

            if ($request->role != null) {
                foreach ($request->role as $key => $role) {
                    $users = User::where('role_id', $role)->where('active_status', 1)->get();
                    foreach ($users as $key => $user) {
                        $notification = new AramiscNotification();
                        $notification->role_id = $role;
                        $notification->message = "Notice for you";
                        $notification->date = $noticeData->notice_date;
                        $notification->user_id = $user->id;
                        $notification->url = "notice-list";
                        $notification->school_id = Auth::user()->school_id;
                        if(moduleStatusCheck('University')){
                            $notification->un_academic_id = getAcademicId();
                        }else{
                            $notification->academic_id = getAcademicId();
                        }
                        $notification->save();
                    }
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('notice-list');

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function noticeList(Request $request)
    {
        try {
            $allNotices = AramiscNoticeBoard::with('users')
                                        ->orderBy('id', 'DESC')
                                        ->get();
            return view('backEnd.communicate.noticeList', compact('allNotices'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function administratorNotice(Request $request)
    {
        try {
            $allNotices = AramiscAdministratorNotice::where('inform_to', Auth::user()->school_id)
                        ->where('active_status', 1)
                        ->get();
          
            return view('backEnd.communicate.administratorNotice', compact('allNotices'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editNotice(Request $request, $notice_id)
    {

        try {
            $roles = AramiscRole::where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $noticeDataDetails = AramiscNoticeBoard::find($notice_id);
            return view('backEnd.communicate.editSendMessage', compact('noticeDataDetails', 'roles'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function updateNoticeData(NoticeRequestForm $request)
    {
        try {
            
            $noticeData = AramiscNoticeBoard::find($request->notice_id);

            if (isset($request->is_published)) {
                $noticeData->is_published = $request->is_published;
            }
            $noticeData->notice_title = $request->notice_title;
            $noticeData->notice_message = $request->notice_message;

            $noticeData->notice_date = date('Y-m-d', strtotime($request->notice_date));
            $noticeData->publish_on = date('Y-m-d', strtotime($request->publish_on));
            $noticeData->notice_date = Carbon::createFromFormat('Y-m-d', $request->notice_date)->format('Y-m-d');
            $noticeData->publish_on = Carbon::createFromFormat('Y-m-d', $request->publish_on)->format('Y-m-d');
            $noticeData->inform_to = json_encode($request->role);
            $noticeData->updated_by = auth()->user()->id;
            if ($request->is_published) {
               $noticeData->is_published = 1;
            } else {
               $noticeData->is_published = 0;
            }
            $noticeData->update();

            $data['title'] = $request->notice_title;
            $data['notice'] = $request->notice_title;
            foreach($request->role as $role_id){
                $userIds = User::where('role_id', $role_id)->where('active_status', 1)->pluck('id')->toArray();
                if($role_id == 4){
                    $this->sent_notifications('Notice', $userIds, $data, ['Teacher']);
                }elseif($role_id == 2){
                    $this->sent_notifications('Notice', $userIds, $data, ['Student']);
                }elseif($role_id == 3){
                    $this->sent_notifications('Notice', $userIds, $data, ['Parent']);
                }elseif($role_id == GlobalVariable::isAlumni()){
                    $this->sent_notifications('Notice', $userIds, $data, ['Alumni']);
                }
            }

            if ($request->role != null) {

                foreach ($request->role as $key => $role) {
                    $users = User::where('role_id', $role)->get();
                    foreach ($users as $key => $user) {
                        $notification = new AramiscNotification();
                        $notification->role_id = $role;
                        $notification->message = $request->notice_title;
                        $notification->date = $noticeData->notice_date;
                        $notification->user_id = $user->id;
                        $notification->url = "notice-list";
                        $notification->school_id = Auth::user()->school_id;
                        if(moduleStatusCheck('University')){
                            $notification->un_academic_id = getAcademicId();
                        }else{
                            $notification->academic_id = getAcademicId();
                        }
                        $notification->save();
                    }
                }
            }

            Toastr::success('Operation successful', 'Success');
            return redirect('notice-list');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteNoticeView(Request $request, $id)
    {
        try {
            return view('backEnd.communicate.deleteNoticeView', compact('id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteNotice(Request $request, $id)
    {
        try {
            AramiscNoticeBoard::destroy($id);
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
