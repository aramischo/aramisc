<?php

namespace App\Http\Controllers;

use App\User;
use App\AramiscToDo;
use App\AramiscClass;
use App\AramiscEvent;
use App\AramiscStaff;
use App\AramiscSchool;
use App\AramiscHoliday;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscUserLog;
use App\YearCheck;
use Carbon\Carbon;
use App\AramiscAddIncome;
use App\CheckSection;
use App\GlobalVariable;
use App\AramiscAddExpense;
use App\AramiscNoticeBoard;
use App\AramiscAcademicYear;
use App\AramiscClassSection;
use App\AramiscGeneralSettings;
use App\AramiscModuleManager;
use App\Models\DueFeesLoginPrevent;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AramiscCalendarSetting;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Modules\Lead\Entities\LeadReminder;
use Modules\Saas\Entities\SmPackagePlan;
use Modules\RolePermission\Entities\AramiscRole;
use Modules\Wallet\Entities\WalletTransaction;
use Modules\Saas\Entities\SmSubscriptionPayment;

class HomeController extends Controller
{
    private $toastr;

    public function __construct()
    {
        $this->middleware('auth');
        $this->toastr = new Toastr();
    }

    public function dashboard()
    {
        try {
            $user = Auth::user();
            $role_id = $user->role_id;
            $is_due_fees_login_permission   = AramiscGeneralSettings::where('school_id',Auth::user()->school_id)->first('due_fees_login');
            $is_due_fees_login_permission   = $is_due_fees_login_permission->due_fees_login;
            $student_due_fees_login_prevent = DueFeesLoginPrevent::where('user_id',$user->id)->where('school_id',$user->school_id)->where('role_id',2)->first();
            $parent_due_fees_login_prevent  = DueFeesLoginPrevent::where('user_id',$user->id)->where('school_id',$user->school_id)->where('role_id',3)->first();
            
            if( ($user->role_id == 1) && ($user->is_administrator == "yes") && (moduleStatusCheck('Saas') == true) ){   #SuperAdmin
                return redirect('superadmin-dashboard');
            }

            if ($role_id == 2) {    #Student
                if($is_due_fees_login_permission == 1){
                    if($student_due_fees_login_prevent == null){
                        $errorMessage = '';
                        Auth::logout();
                        session(['role_id' => '']);
                        Session::flash('toast_message', [
                            'type' => 'error', // 'success', 'info', 'warning', 'error'
                            'message' => 'Operation Failed, Unable to log in due to unpaid fees.'
                        ]);
                        return redirect('login')->withErrors(['custom_error' => $errorMessage]);
                    } else {
                        return redirect('student-dashboard');
                    }
                } else {
                    return redirect('student-dashboard');
                }
            } elseif ($role_id == 3) {  #Parent
                if($is_due_fees_login_permission == 1){
                    if($parent_due_fees_login_prevent != null){
                        $errorMessage = '';
                        Auth::logout();
                        session(['role_id' => '']);
                        Session::flash('toast_message', [
                            'type' => 'error', // 'success', 'info', 'warning', 'error'
                            'message' => 'Operation Failed, Unable to log in due to unpaid fees.'
                        ]);
                        return redirect('login')->withErrors(['custom_error' => $errorMessage]);
                    }else {
                        return redirect('parent-dashboard');
                    }
                } else {
                    return redirect('parent-dashboard');
                }
            } elseif($role_id == GlobalVariable::isAlumni()) {  #Alumni
                return redirect('alumni-dashboard');
            } elseif ($role_id == "") {
                return redirect('login');
            } elseif (Auth::user()->is_saas == 1) {
                return redirect('saasStaffDashboard');
            }
            else {
                return redirect('admin-dashboard');
            }
        } catch (\Exception $e) {
            $this->toastr->error('Operation Failed,' .$e->getMessage(),'Failed');
            return redirect()->back();
        }
    }
    
    // for display dashboard
    public function index(Request $request)
    {
       try {
            $chart_data =" ";
            $day_incomes =  AramiscAddIncome::where('academic_id', getAcademicId())
                ->where('name', '!=', 'Fund Transfer')
                ->where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->where('date', '>=', date('Y').'-01-01')
                ->where('date', '<=', date('Y-m-d'))
                ->get(['amount','date']);
              

            $day_expenses =  AramiscAddExpense::where('academic_id', getAcademicId())
                ->where('name', '!=', 'Fund Transfer')
                ->where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->where('date', '>=', date('Y').'-01-01')
                ->where('date', '<=', date('Y-m-d'))
                ->get(['amount','date']);

            $m_total_income = $day_incomes->where('name','!=','Fund Transfer')
                ->where('date', '>=', date('Y-m-01'))
                ->sum('amount');

            $m_total_expense = $day_expenses->where('name','!=','Fund Transfer')
                ->where('date', '>=', date('Y-m-01'))
                ->sum('amount');

            for($i = 1; $i <= date('d'); $i++){
                $i = $i < 10? '0'.$i:$i;
                $income = $day_incomes->filter(function ($value) use ($i) {
                    return $value->date->day == $i  &&  $value->date->month == date('m');
                })->sum('amount');
                
                $expense =  $day_expenses->filter(function ($value) use ($i) {
                    return $value->date->day == $i &&  $value->date->month == date('m');
                })->sum('amount');

                $chart_data .= "{ day: '" . $i . "', income: " . @$income . ", expense:" . @$expense . " },";
            }
            $chart_data_yearly = "";
            for($i = 1; $i <= date('m'); $i++){
                $i = $i < 10? '0'.$i:$i;
                $yearlyIncome = $day_incomes->filter(function ($value) use ($i) {
                    return $value->date->month == $i;
               })->sum('amount');
                $yearlyExpense = $day_expenses->filter(function ($value) use ($i) {
                    return $value->date->month == $i;
               })->sum('amount');
                $chart_data_yearly .= "{ y: '" . $i . "', income: " . @$yearlyIncome . ", expense:" . @$yearlyExpense . " },";
            }
            $count_event =0;
            $SaasSubscription = isSubscriptionEnabled();
            $saas = moduleStatusCheck('Saas');
            if ($SaasSubscription == TRUE) {
                if (!\Modules\Saas\Entities\SmPackagePlan::isSubscriptionAutheticate()) {
                    return redirect('subscription/package-list');
                }
            }
            $user_id = Auth::id();
            $school_id = Auth::user()->school_id;

            if(isSubscriptionEnabled()){
                $last_payment = SmSubscriptionPayment::where('school_id',Auth::user()->school_id)
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now())
                    ->where('approve_status', '=','approved')
                    ->latest()->first();
                $package_info = [];

                if($last_payment){
                    $package = SmPackagePlan::find($last_payment->package_id);


                    if($package->payment_type == 'trial'){
                        $total_days  = $package->trial_days;
                    }else{
                        $total_days  = $package->duration_days;
                    }
                    $now_time = date('Y-m-d');
                    $now_time =  date('Y-m-d', strtotime($now_time. ' + 1 days'));
                    $end_date = date('Y-m-d', strtotime($last_payment->end_date));

                    $formatted_dt1=Carbon::parse($now_time);
                    $formatted_dt2=Carbon::parse($last_payment->end_date);
                    $remain_days =$formatted_dt1->diffInDays($formatted_dt2);

                    $package_info['package_name'] = $package->name;
                    $package_info['student_quantity'] = $package->student_quantity;
                    $package_info['staff_quantity'] = $package->staff_quantity;
                    $package_info['remaining_days'] = $remain_days;
                    $package_info['expire_date'] =  date('Y-m-d', strtotime($last_payment->end_date. ' + 1 days'));
                }


            }

            // for current month start
            if(moduleStatusCheck('Wallet'))
                $monthlyWalletBalance = $this->showWalletBalance('diposit','refund','expense', 'fees_refund','Y-m-',$school_id);
            // for current month end

            // for current year start
            $y_total_income = $day_incomes->where('name','!=','Fund Transfer')
                ->where('date', '>=', date('Y-01-01'))
                ->sum('amount');



            $y_total_expense =  $day_expenses->where('name','!=','Fund Transfer')
                ->where('date', '>=', date('Y-01-01'))
                ->sum('amount');


            if(moduleStatusCheck('Wallet'))
                $yearlyWalletBalance = $this->showWalletBalance('diposit','refund','expense', 'fees_refund','Y-',$school_id);

            // for current year end


            if (Auth::user()->role_id == 4) {
                $events = AramiscEvent::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->where(function ($q) {
                        $q->where('for_whom', 'All')->orWhere('for_whom', 'Teacher');
                    })
                    ->get();
            } else {
                $events = AramiscEvent::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->where('for_whom', 'All')
                    ->get();
            }

            $staffs = AramiscStaff::where('school_id', $school_id)
                ->where('active_status', 1);


            $holidays = AramiscHoliday::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->get();



            $calendar_events = array();
            foreach($holidays as $k => $holiday) {
                $calendar_events[$k]['title'] = $holiday->holiday_title;
                $calendar_events[$k]['start'] = $holiday->from_date;
                $calendar_events[$k]['end'] = Carbon::parse($holiday->to_date)->addDays(1)->format('Y-m-d');
                $calendar_events[$k]['description'] = $holiday->details;
                $calendar_events[$k]['url'] = $holiday->upload_image_file;
                $count_event = $k;
                $count_event++;
            }

            foreach($events as $k => $event) {
                $calendar_events[$count_event]['title'] = $event->event_title;
                $calendar_events[$count_event]['start'] = $event->from_date;
                $calendar_events[$count_event]['end'] = Carbon::parse($event->to_date)->addDays(1)->format('Y-m-d');
                $calendar_events[$count_event]['description'] = $event->event_des;
                $calendar_events[$count_event]['url'] = $event->uplad_image_file;
                $count_event++;
            }
            //added by abu nayem -for lead
            if (moduleStatusCheck('Lead')==true) {
                $reminders = LeadReminder::with('lead:first_name,last_name,id')->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->when(auth()->user()->role_id!=1 && auth()->user()->staff, function ($q) {
                        $q->where('reminder_to', auth()->user()->staff->id);
                    })->get();
                foreach ($reminders as $k => $event) {
                    $calendar_events[$count_event]['title'] = 'Lead Reminder';
                    $calendar_events[$count_event]['start'] = Carbon::parse($event->date_time)->format('Y-m-d').' '.$event->time;
                    $calendar_events[$count_event]['end'] = Carbon::parse($event->date_time)->format('Y-m-d');
                    $calendar_events[$count_event]['description'] = view('lead::lead_calender', compact('event'))->render();
                    $calendar_events[$count_event]['url'] = 'lead/show/'.$event->id;
                    $count_event++;
                }
            }
            //end lead reminder

            $notices = AramiscNoticeBoard::query();
            $notices->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', $school_id) ;
            $notices->when(auth()->user()->role_id != 1, function ($query) {
                $query->where('inform_to', 'LIKE', '%'.auth()->user()->role_id.'%');
            });
            $notices = $notices->get();

            $all_staffs= $staffs->where('role_id', '!=', 1)
                ->where('school_id', $school_id)->get();
            $all_students = AramiscStudent::where('active_status', 1)
                ->where('school_id', $school_id)->get();
            $data =[
                'totalStudents' =>$all_students->count(),
                'totalParents' => $all_students->whereNotNull('parent_id')->unique('parent_id')
                    ->count(),

                'totalTeachers' => $all_staffs->where('role_id', 4)->count(),

                'totalStaffs' =>$all_staffs->count(),

                'toDos' => AramiscToDo::where('created_by', $user_id)
                    ->where('school_id', $school_id)
                    ->get(),

                'notices' => $notices,

                    //where('inform_to', 'LIKE', '%2%')

                'm_total_income' => $m_total_income,
                'y_total_income' => $y_total_income,
                'm_total_expense' => $m_total_expense,
                'y_total_expense' => $y_total_expense,
                'holidays' => $holidays,
                'events' => $events,

                'year' => YearCheck::getYear(),
            ];
            if(moduleStatusCheck('Wallet')){
                $data['monthlyWalletBalance'] = $monthlyWalletBalance;
                $data['yearlyWalletBalance'] = $yearlyWalletBalance;
            }

            if (Session::has('info_check')) {
                session(['info_check' => 'no']);
            } else {
                session(['info_check' => 'yes']);
            }

            $data['settings'] = AramiscCalendarSetting::get();
            $data['roles'] = AramiscRole::where(function ($q) {
                $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
            })
            ->whereNotIn('id', [1, 2])
            ->get();
            $academicCalendar = new AramiscAcademicCalendarController();
            $data['events'] = $academicCalendar->calenderData();
            if(isSubscriptionEnabled()){
                return view('backEnd.dashboard',compact('chart_data','chart_data_yearly','calendar_events','package_info'))->with($data);
            }else{
                return view('backEnd.dashboard',compact('chart_data','chart_data_yearly','calendar_events'))->with($data);
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed, ' . $e, 'Failed');
            Auth::logout();
            session(['role_id' => '']);
            Session::flush();
            return redirect('login');
        }
    }

    private function showWalletBalance($diposit , $refund, $expense, $feesRefund, $date, $school_id){

        $walletTranscations= WalletTransaction::where('status','approve')
            ->where('updated_at', 'like', date($date) . '%')
            ->where('school_id',$school_id)
            ->get();

        $totalWalletBalance = $walletTranscations->where('type',$diposit)->sum('amount');
        $totalWalletRefundBalance = $walletTranscations->where('type',$refund)->sum('amount');
        $totalWalletExpenseBalance = $walletTranscations->where('type',$expense)->sum('amount');
        $totalFeesRefund = $walletTranscations->where('type',$feesRefund)->sum('amount');

        return ($totalWalletBalance - $totalWalletExpenseBalance) - $totalWalletRefundBalance + $totalFeesRefund;
    }

    public function saveToDoData(Request $request)
    {
        try {
            $toDolists = new AramiscToDo();
            $toDolists->todo_title = $request->todo_title;
            $toDolists->date = date('Y-m-d', strtotime($request->date));
            $toDolists->created_by = Auth()->user()->id;
            $toDolists->school_id = Auth()->user()->school_id;
            $toDolists->academic_id = getAcademicId();
            $results = $toDolists->save();

            if ($results) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function viewToDo($id)
    {
        try {
            if (checkAdmin()) {
                $toDolists = AramiscToDo::find($id);
            }else{
                $toDolists = AramiscToDo::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            return view('backEnd.dashboard.viewToDo', compact('toDolists'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editToDo($id)
    {
        try {
            // $editData = AramiscToDo::find($id);
            if (checkAdmin()) {
                $editData = AramiscToDo::find($id);
            }else{
                $editData = AramiscToDo::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            return view('backEnd.dashboard.editToDo', compact('editData', 'id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function updateToDo(Request $request)
    {
        try {
            $to_do_id = $request->to_do_id;
            $toDolists = AramiscToDo::find($to_do_id);
            $toDolists->todo_title = $request->todo_title;
            $toDolists->date = date('Y-m-d', strtotime($request->date));
            $toDolists->complete_status = $request->complete_status;
            $toDolists->updated_by = Auth()->user()->id;
            $results = $toDolists->update();

            if ($results) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function removeToDo(Request $request)
    {
        try {
            $to_do = AramiscToDo::find($request->id);
            $to_do->complete_status = "C";
            $to_do->academic_id = getAcademicId();
            $to_do->save();
            $html = "";
            return response()->json('html');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function getToDoList(Request $request)
    {
        try {

            $to_do_list = AramiscToDo::where('complete_status', 'C')->where('school_id', Auth::user()->school_id)->get();
            $datas = [];
            foreach ($to_do_list as $to_do) {
                $datas[] = array(
                    'title' => $to_do->todo_title,
                    'date' => date('jS M, Y', strtotime($to_do->date))
                );
            }
            return response()->json($datas);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function viewNotice($id)
    {
        try {
            $notice = AramiscNoticeBoard::find($id);
            return view('backEnd.dashboard.view_notice', compact('notice'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function updatePassowrd()
    {
        return view('backEnd.update_password');
    }


    public function updatePassowrdStore(Request $request)
    {
        $request->validate([
            'current_password' => "required",
            'new_password' => "required|same:confirm_password|min:6|different:current_password",
            'confirm_password' => 'required|min:6'
        ]);

        try {
            $user = Auth::user();
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $result = $user->save();
                if ($result){
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                    // return redirect()->back()->with('message-success', 'Password has been changed successfully');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                    // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            } else {
                Toastr::error('Current password not match!', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('password-error', 'You have entered a wrong current password');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}