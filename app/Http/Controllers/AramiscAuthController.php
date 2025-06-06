<?php

namespace App\Http\Controllers;

use App\User;
use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscParent;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscClassRoom;
use App\AramiscClassTime;
use App\ApiBaseMethod;
use Illuminate\Support\Str;
use App\AramiscBackgroundSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AramiscAuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function get_class_name(Request $request, $id)
    {
        $get_class_name = AramiscClass::select('class_name as name')->where('id', $id)->first();
        return $get_class_name;
    }

    public function get_section_name(Request $request, $id)
    {
        $get_section_name = AramiscSection::select('section_name as name')->where('id', $id)->first();
        return $get_section_name;
    }

    public function get_teacher_name(Request $request, $id)
    {
        $get_teacher_name = AramiscStaff::select('full_name as name')->where('id', $id)->first();
        return $get_teacher_name;
    }

    public function get_subject_name(Request $request, $id)
    {
        $get_subject_name = AramiscSubject::select('subject_name as name')->where('id', $id)->first();
        return $get_subject_name;
    }

    public function get_room_name(Request $request, $id)
    {
        $get_room_name = AramiscClassRoom::select('room_no as name')->where('id', $id)->first();
        return $get_room_name;
    }

    public function get_class_period_name(Request $request, $id)
    {
        $get_class_period_name = AramiscClassTime::select('period as name', 'start_time', 'end_time')->where('id', $id)->first();
        return $get_class_period_name;
    }

    public function getLoginAccess(Request $request)
    {
        if ($request->value == "Student") {
            $user = User::where('role_id', 2)->where('school_id', Auth::user()->school_id)->first();
        } elseif ($request->value == "Parents") {
            $user = User::where('role_id', 3)->where('school_id', Auth::user()->school_id)->first();
        } elseif ($request->value == "Super Admin") {
            $user = User::where('role_id', 1)->where('school_id', Auth::user()->school_id)->first();
        } elseif ($request->value == "Admin") {
            $user = User::where('role_id', 5)->where('school_id', Auth::user()->school_id)->first();
        } elseif ($request->value == "Teacher") {
            $user = User::where('role_id', 4)->where('school_id', Auth::user()->school_id)->first();
        } elseif ($request->value == "Accountant") {
            $user = User::where('role_id', 6)->where('school_id', Auth::user()->school_id)->first();
        } elseif ($request->value == "Receptionist") {
            $user = User::where('role_id', 7)->where('school_id', Auth::user()->school_id)->first();
        } elseif ($request->value == "Librarian") {
            $user = User::where('role_id', 8)->where('school_id', Auth::user()->school_id)->first();
        }
        return response()->json($user);
    }

    public function recoveryPassord()
    {
        try {
            $login_background = AramiscBackgroundSetting::where([['is_default', 1], ['title', 'Login Background']])->first();

            if (empty($login_background)) {
                $css = "background: url(" . url('public/backEnd/img/login-bg.jpg') . ")  no-repeat center; background-size: cover; ";
            } else {
                if (!empty($login_background->image)) {
                    $css = "background: url('" . url($login_background->image) . "')  no-repeat center;  background-size: cover;";
                } else {
                    $css = "background:" . $login_background->color;
                }
            }
            if (generalSetting() &&  generalSetting()->active_theme == 'edulia') {
                return view('frontEnd.theme.' . activeTheme() . '.login.reset_password', compact('css'));
            } else {
                return view('auth.recovery_password', compact('css'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function emailVerify(Request $request)
    {
        $request->validate([
            'email' => 'required'
        ]);
        try {
            $emailCheck = User::select('*')->where('email', $request->email)->first();
            if ($emailCheck == "") {
                return redirect()->back()->with('message-danger', "Invalid Email, Please try again");
            } else {
                $admissionNumber = '';
                $student = AramiscStudent::where('user_id', $emailCheck->id)->first();
                if ($student) {
                    if ($emailCheck->role_id == 2) {
                        $admissionNumber = $student->admission_number;
                    }
                }

                $random = Str::random(32);
                // $data['id'] = Str::random(32);
                $user = User::where('email', $request->email)->first();
                $user->random_code = $random;
                $user->save();
                try {
                    $data['user_email'] = $request->email;
                    $data['id'] = $emailCheck->id;
                    $data['random'] = $user->random_code;
                    $data['role_id'] = $user->role_id;
                    $data['admission_number'] = $admissionNumber;
                    @send_mail($user->email, $user->full_name, "password_reset", $data);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
                Toastr::success('Please check your email', 'Success');
                return redirect()->back()->with('message-success', 'Success ! Please check your email');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function resetEmailConfirtmation($email, $code)
    {
        try {
            $user = User::where('email', $email)->where('random_code', $code)->first();
            if ($user != "") {
                $email = $user->email;

                if (generalSetting() &&  generalSetting()->active_theme == 'edulia') {
                    return view('frontEnd.theme.' . activeTheme() . '.login.new_password', compact('email'));
                } else {
                    return view('auth.new_password', compact('email'));
                }
            } else {
                Toastr::error('You have clicked on a invalid link', 'Failed');
                return redirect('recovery/passord');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeNewPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|same:confirm_password',
            'confirm_password' => 'required'
        ]);
        try {
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->new_password);
            $user->random_code = '';
            $result = $user->save();
            if ($result) {
                Toastr::success('Password has beed reset successfully', 'Success');
                return redirect('login')->with('message-success', 'Password has beed reset successfully');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function mobileLogin(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => "required",
            'password' => "required"
        ]);
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $user = User::where('email', $request->email)->first();
            if ($user != "") {
                if (Hash::check($request->password, $user->password)) {
                    $data = [];
                    $data['user'] = $user->toArray();
                    $role_id = $user->role_id;
                    if ($role_id == 2) {
                        $data['userDetails'] = DB::table('aramisc_students')->select('aramisc_students.*', 'aramisc_parents.*', 'aramisc_classes.*', 'aramisc_sections.*')
                            ->join('aramisc_parents', 'aramisc_parents.id', '=', 'aramisc_students.parent_id')
                            ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')
                            ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')
                            ->where('aramisc_students.user_id', $user->id)
                            ->first();
                        $data['religion'] = DB::table('aramisc_students')->select('aramisc_base_setups.base_setup_name as name')
                            ->join('aramisc_base_setups', 'aramisc_base_setups.id', '=', 'aramisc_students.religion_id')
                            ->where('aramisc_students.user_id', $user->id)
                            ->first();
                        $data['blood_group'] = DB::table('aramisc_students')->select('aramisc_base_setups.base_setup_name as name')
                            ->join('aramisc_base_setups', 'aramisc_base_setups.id', '=', 'aramisc_students.bloodgroup_id')
                            ->where('aramisc_students.user_id', $user->id)
                            ->first();
                        $data['transport'] = DB::table('aramisc_students')
                            ->select('aramisc_vehicles.vehicle_no', 'aramisc_vehicles.vehicle_model', 'aramisc_staffs.full_name as driver_name', 'aramisc_vehicles.note')
                            ->join('aramisc_vehicles', 'aramisc_vehicles.id', '=', 'aramisc_students.vechile_id')
                            ->join('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_vehicles.driver_id')
                            ->where('aramisc_students.user_id', $user->id)
                            ->first();
                        $data['system_settings'] = DB::table('aramisc_general_settings')->where('school_id', Auth::user()->school_id)->get();
                        $data['TTL_RTL_status'] = '1=RTL,2=TTL';
                    } else if ($role_id == 3) {
                        $data['userDetails'] = AramiscParent::where('user_id', $user->id)->first();
                    } else {
                        $data['userDetails'] = AramiscStaff::where('user_id', $user->id)->first();
                    }
                    return ApiBaseMethod::sendResponse($data, 'Login successful.');
                } else {
                    return ApiBaseMethod::sendError('These credentials do not match our records.');
                }
            } else {
                return ApiBaseMethod::sendError('These credentials do not match our records.');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function setToken(Request $request)
    {
        try {
            $user = User::find($request->id);
            $user->notificationToken = $request->token;
            $user->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function childInfo(Request $request, $user_id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $user = AramiscStudent::where('user_id', $user_id)->first();
                $data = [];
                $data['user'] = $user->toArray();
                $data['userDetails'] = DB::table('aramisc_students')->select('aramisc_students.*', 'aramisc_parents.*', 'aramisc_classes.*', 'aramisc_sections.*')
                    ->join('aramisc_parents', 'aramisc_parents.id', '=', 'aramisc_students.parent_id')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')
                    ->where('aramisc_students.id', $user->id)
                    ->first();
                $data['religion'] = DB::table('aramisc_students')->select('aramisc_base_setups.base_setup_name as name')
                    ->join('aramisc_base_setups', 'aramisc_base_setups.id', '=', 'aramisc_students.religion_id')
                    ->where('aramisc_students.id', $user->id)
                    ->first();

                $data['blood_group'] = DB::table('aramisc_students')->select('aramisc_base_setups.base_setup_name as name')
                    ->join('aramisc_base_setups', 'aramisc_base_setups.id', '=', 'aramisc_students.bloodgroup_id')
                    ->where('aramisc_students.id', $user->id)
                    ->first();
                $data['transport'] = DB::table('aramisc_students')
                    ->select('aramisc_vehicles.vehicle_no', 'aramisc_vehicles.vehicle_model', 'aramisc_staffs.full_name as driver_name', 'aramisc_vehicles.note')
                    ->join('aramisc_vehicles', 'aramisc_vehicles.id', '=', 'aramisc_students.vechile_id')
                    ->join('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_students.vechile_id')
                    ->where('aramisc_students.id', $user->id)
                    ->first();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
