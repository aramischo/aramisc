<?php

use App\User;
use App\AramiscExam;
use App\AramiscStaff;
use App\AramiscStyle;
use App\AramiscParent;
use App\AramiscSchool;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscExamType;
use App\AramiscLanguage;
use App\AramiscAddIncome;
use App\AramiscExamSetup;
use App\AramiscMarkStore;
use App\AramiscTemplate;
use Clickatell\Rest;
use App\AramiscDateFormat;
use App\AramiscFeesMaster;
use App\AramiscMarksGrade;
use App\AramiscSmsGateway;
use App\Jobs\EmailJob;
use App\AramiscFeesPayment;
use App\AramiscResultStore;
use GuzzleHttp\Client;
use App\AramiscAcademicYear;
use App\AramiscClassTeacher;
use App\AramiscEmailSetting;
use App\AramiscExamSchedule;
use App\AramiscNotification;
use App\AramiscAssignSubject;
use App\AramiscExamAttendance;
use App\AramiscPaymentMethhod;
use App\AramiscGeneralSettings;
use App\AramiscModuleManager;
use App\Models\FeesInvoice;
use App\AramiscFeesCarryForward;
use Illuminate\Support\Str;
use App\CustomResultSetting;
use App\AramiscHeaderMenuManager;
use App\AramiscSubjectAttendance;
use App\Models\StudentRecord;
use App\AramiscExamAttendanceChild;
use Illuminate\Support\Carbon;
use App\Models\AramiscExamSignature;
use App\AramiscClassOptionalSubject;
use App\Models\CustomSmsSetting;
use App\AramiscOptionalSubjectAssign;
use App\Models\ExamMeritPosition;
use App\Models\DirectFeesReminder;
use Illuminate\Support\Facades\DB;
use App\Models\AllExamWisePosition;
use App\Models\FeesCarryForwardLog;
use App\Scopes\AcademicSchoolScope;
use App\Scopes\GlobalAcademicScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\AramiscNotificationSetting;
use Illuminate\Support\Facades\Cache;
use Modules\Fees\Entities\FmFeesType;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Larabuild\Pagebuilder\Models\Page;
use Illuminate\Support\Facades\Storage;
use Modules\Lms\Entities\CourseSetting;
use App\Models\FeesCarryForwardSettings;
use App\Models\AramiscStudentRegistrationField;
use App\Models\DirectFeesInstallmentAssign;
use Modules\MenuManage\Entities\MenuManage;
use Modules\University\Entities\UnAcademicYear;
use Modules\Fees\Entities\FmFeesInvoiceSettings;
use Modules\University\Entities\UnFeesInstallmentAssign;
use Modules\ParentRegistration\Entities\AramiscStudentRegistration;

function sendEmailBio($data, $to_name, $to_email, $email_sms_title)
{
    $systemSetting = DB::table('aramisc_general_settings')->select('school_name', 'email')->find(1);
    $systemEmail = DB::table('aramisc_email_settings')->find(1);
    $system_email = $systemEmail->from_email;
    $school_name = $systemSetting->school_name;
    if (!empty($system_email)) {
        $data['email_sms_title'] = $email_sms_title;
        $data['system_email'] = $system_email;
        $data['school_name'] = $school_name;
        $details = $to_email;
        dispatch(new \App\Jobs\SendEmailJob($data, $details));
        $error_data = [];
        return true;
    } else {
        $error_data[0] = 'success';
        $error_data[1] = 'Operation Failed, Please Updated System Mail';
        return $error_data;
    }
}

if (!function_exists('activeSmsGateway')) {
    function activeSmsGateway()
    {
        $school_id = Auth::check() && saasSettings('sms_settings') ? Auth::user()->school_id : 1;
        $activeSmsGateway = AramiscSmsGateway::where('school_id', $school_id)->where('active_status', '=', 1)->first();

        return $activeSmsGateway;
    }
}
if (!function_exists('youtubeVideo')) {
    function youtubeVideo($video_url)
    {
        if (Str::contains($video_url, 'youtu.be')) {
            $url = explode("/", $video_url);
            return 'https://www.youtube.com/watch?v=' . $url[3];
        }

        if (Str::contains($video_url, '&')) {
            return substr($video_url, 0, strpos($video_url, "&"));
        } else {
            return $video_url;
        }
    }
}
function showFileName($data)
{
    $name = explode('/', $data);
    $number = array_key_last($name);
    return $name[$number];
}

function sendSMSApi($to_mobile, $sms, $id)
{
    $activeSmsGateway = AramiscSmsGateway::find($id);
    if ($activeSmsGateway->gateway_name == 'Twilio') {
        if (!$activeSmsGateway->twilio_account_sid || $activeSmsGateway->twilio_authentication_token) {
            return;
        }
        $client = new Twilio\Rest\Client($activeSmsGateway->twilio_account_sid, $activeSmsGateway->twilio_authentication_token);
        if (!empty($to_mobile)) {
            $result = $message = $client->messages->create($to_mobile, array('from' => $activeSmsGateway->twilio_registered_no, 'body' => $sms));
            return $result;
        }
    } //end Twilio
    else if ($activeSmsGateway->gateway_name == 'Clickatell') {

        // config(['clickatell.api_key' => $activeSmsGateway->clickatell_api_id]); //set a variale in config file(clickatell.php)

        $clickatell = new \Clickatell\Rest();
        $result = $clickatell->sendMessage(['to' => $to_mobile, 'content' => $sms]);
    } //end Clickatell

    //start Himalayasms

    else if ($activeSmsGateway->gateway_name == 'Himalayasms') {
        $client = new Client();
        $request = $client->get("https://sms.techhimalaya.com/base/smsapi/index.php", [
            'query' => [
                'key' => $activeSmsGateway->himalayasms_key,
                'senderid' => $activeSmsGateway->himalayasms_senderId,
                'campaign' => $activeSmsGateway->himalayasms_campaign,
                'routeid' => $activeSmsGateway->himalayasms_routeId,
                'contacts' => $to_mobile,
                'msg' => $sms,
                'type' => "text",
            ],
            'http_errors' => false,
        ]);

        $result = $request->getBody();
    } else if ($activeSmsGateway->gateway_name == 'Msg91') {
        $msg91_authentication_key_sid = $activeSmsGateway->msg91_authentication_key_sid;
        $msg91_sender_id = $activeSmsGateway->msg91_sender_id;
        $msg91_route = $activeSmsGateway->msg91_route;
        $msg91_country_code = $activeSmsGateway->msg91_country_code;

        $curl = curl_init();

        $url = "https://api.msg91.com/api/sendhttp.php?mobiles=" . $to_mobile . "&authkey=" . $msg91_authentication_key_sid . "&route=" . $msg91_route . "&sender=" . $msg91_sender_id . "&message=" . $sms . "&country=91";

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $result = "cURL Error #:" . $err;
        } else {
            $result = $response;
        }
    } //end Msg91
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $result = "cURL Error #:" . $err;
    } else {
        $result = $response;
    }
    return $result;
} //end Msg91

function sendSMSBio($to_mobile, $sms)
{
    $activeSmsGateway = AramiscSmsGateway::where('school_id', Auth::user()->school_id)->where('active_status', '=', 1)->first();
    if ($activeSmsGateway->gateway_name == 'Twilio') {

        config(['TWILIO.SID' => $activeSmsGateway->twilio_account_sid]);
        config(['TWILIO.TOKEN' => $activeSmsGateway->twilio_authentication_token]);
        config(['TWILIO.FROM' => $activeSmsGateway->twilio_registered_no]);
        $account_id = $activeSmsGateway->twilio_account_sid; // Your Account SID from www.twilio.com/console
        $auth_token = $activeSmsGateway->twilio_authentication_token; // Your Auth Token from www.twilio.com/console
        $from_phone_number = $activeSmsGateway->twilio_registered_no;
        $client = new Twilio\Rest\Client($account_id, $auth_token);
        if (!empty($to_mobile)) {
            $result = $message = $client->messages->create($to_mobile, array('from' => $from_phone_number, 'body' => $sms));
            return $result;
        }
    } //end Twilio
    else if ($activeSmsGateway->gateway_name == 'Clickatell') {

        // config(['clickatell.api_key' => $activeSmsGateway->clickatell_api_id]); //set a variale in config file(clickatell.php)

        $clickatell = new \Clickatell\Rest();
        $result = $clickatell->sendMessage(['to' => $to_mobile, 'content' => $sms]);
    } //end Clickatell

    else if ($activeSmsGateway->gateway_name == 'Msg91') {
        $msg91_authentication_key_sid = $activeSmsGateway->msg91_authentication_key_sid;
        $msg91_sender_id = $activeSmsGateway->msg91_sender_id;
        $msg91_route = $activeSmsGateway->msg91_route;
        $msg91_country_code = $activeSmsGateway->msg91_country_code;

        $curl = curl_init();

        $url = "https://api.msg91.com/api/sendhttp.php?mobiles=" . $to_mobile . "&authkey=" . $msg91_authentication_key_sid . "&route=" . $msg91_route . "&sender=" . $msg91_sender_id . "&message=" . $sms . "&country=91";

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $result = "cURL Error #:" . $err;
        } else {
            $result = $response;
        }
    } //end Msg91
    elseif ($activeSmsGateway->gateway_name == 'TextLocal') {

        // Account details
        // $apiKey = urlencode('Your apiKey');
        $apiKey = $activeSmsGateway->textlocal_hash;
        $url = $activeSmsGateway->type == 'in' ? 'http://api.textlocal.in/send/' : 'http://api.txtlocal.com/send/';
        // Message details
        $numbers = $to_mobile;
        $sender = urlencode($activeSmsGateway->textlocal_sender);
        $message = rawurlencode($sms);

        // $numbers = implode(',', $numbers);

        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);

        // Send the POST request with cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Process your response here
        $result = $response;
    }
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION =>
            CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $result = "cURL Error #:" . $err;
    } else {
        $result = $response;
    }
    return $result;
} //end Msg91

function getValueByString($student_id, $string, $extra = null)
{
    $student = AramiscStudent::find($student_id);
    if ($extra != null) {
        return $student->$string->$extra;
    } else {
        return $student->$string;
    }
}

function getParentName($student_id, $string, $extra = null)
{
    $student = AramiscStudent::find($student_id);
    $parent = AramiscParent::where('id', $student->parent_id)->first();
    if ($extra != null) {
        return $student->$parent->$extra;
    } else {
        return $parent->fathers_name;
    }
}

function SMSBody($body, $s_id, $time)
{
    try {
        $original_message = $body;
        // $original_message= "Dear Parent [fathers_name], your child [class] came to the school at [section]";
        $chars = preg_split('/[\s,]+/', $original_message, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach ($chars as $item) {
            if (strstr($item[0], "[")) {
                $str = str_replace('[', '', $item);
                $str = str_replace(']', '', $str);
                $str = str_replace('.', '', $str);
                if ($str == "class") {
                    $str = "class";
                    $extra = "class_name";
                    $custom_array[$item] = getValueByString($s_id, $str, $extra);
                } elseif ($str == "section") {
                    $str = "section";
                    $extra = "section_name";
                    $custom_array[$item] = getValueByString($s_id, $str, $extra);
                } elseif ($str == 'check_in_time') {
                    $custom_array[$item] = $time;
                } elseif ($str == 'fathers_name') {
                    $str = "parents";
                    $extra = "fathers_name";
                    $custom_array[$item] = getValueByString($s_id, $str, $extra);
                    // $custom_array[$item]= 'father';
                } else {
                    $custom_array[$item] = getValueByString($s_id, $str);
                }
            }
        }

        foreach ($custom_array as $key => $value) {
            $original_message = str_replace($key, $value, $original_message);
        }

        return $original_message;
    } catch (\Exception $e) {
        $data = [];
        return $data;
    }
}

function FeesDueSMSBody($body, $s_id, $time)
{
    try {
        $original_message = $body;
        $chars = preg_split('/[\s,]+/', $original_message, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach ($chars as $item) {
            if (strstr($item[0], "|")) {
                $str = str_replace('|', '', $item);
                // return $str;
                $str = str_replace('|', '', $str);
                $str = str_replace('.', '', $str);
                if ($str == "StudentName") {
                    $str = "StudentName";
                    $extra = "full_name";
                    $custom_array[$item] = getValueByString($s_id, $str, $extra);
                } elseif ($str == 'fathers_name') {
                    $str = "parents";
                    $extra = "fathers_name";
                    $custom_array[$item] = getValueByString($s_id, $str, $extra);
                    // $custom_array[$item]= 'father';
                } else {
                    $custom_array[$item] = getValueByString($s_id, $str);
                }
            }
        }

        foreach ($custom_array as $key => $value) {
            $original_message = str_replace($key, $value, $original_message);
        }

        return $original_message;
    } catch (\Exception $e) {
        $data = [];
        return $data;
    }
}

if (!function_exists('userPermission')) {
    function userPermission($route, $role_id = null, $purpose = null)
    {
        $role_id = Auth::user()->role_id;
        $permissions = app('permission');
        if ($role_id == 1 && Auth::user()->is_administrator == "yes") {
            return true;
        }

        if ((!empty($permissions)) && ($role_id != 1)) {
            if (@in_array($route, $permissions)) {
                return true;
            } else {
                return false;
            }
        } elseif (moduleStatusCheck('Saas') == true) {
            $saas_status_ids = app('saasSettings');
            if (@in_array($route, $saas_status_ids)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}

if (!function_exists('moduleStatusCheck')) {
    function moduleStatusCheck($module)
    {
        try {
            $all_module = session()->get('all_module');
            $moduleStatus = true;

            if (empty($all_module)) {
                $all_module = [];
                $modules = AramiscModuleManager::select('name')->get();
                foreach ($modules as $m) {
                    $all_module[] = $m->name;
                }

                session()->put('all_module', $all_module);
            }

            $is_module_available = 'Modules/' . $module . '/Providers/' . $module . 'ServiceProvider.php';
            if (file_exists($is_module_available)) {
                $moduleStatus = Module::find($module)->isDisabled();
            }

            if (!in_array($module, $all_module) || $moduleStatus) {
                return false;
            }

            $is_verify = Cache::rememberForever('module_' . $module, function () use ($module) {
                return AramiscModuleManager::where('name', $module)->first();
            });

            if (!$is_verify || !$is_verify->purchase_code) {
                return false;
            }

            if ($module != 'Saas' && isSubscriptionEnabled() && in_array($module, planPermissions('modules') ?? [])) {
                return isModuleForSchool($module);
            }

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists('dateConvert')) {

    function dateConvert($input_date)
    {
        try {
            $system_date_format = session()->get('system_date_format');
            if (empty($system_date_format)) {
                $date_format_id = AramiscGeneralSettings::where('id', 1)->first(['date_format_id'])->date_format_id;
                $system_date_format = AramiscDateFormat::where('id', $date_format_id)->first(['format'])->format;
                session()->put('system_date_format', $system_date_format);
            }

//            $slang = session()->get('selected_language');
//            $userlang = session()->get('user_language');
//            $language = ($slang) ? $slang : (($userlang) ? $userlang : 'en');
//            $system_date_format = ($language =='fr') ? 'd/m/Y' : 'Y-m-d';

            return \Carbon\Carbon::createFromFormat('Y-m-d', $input_date)->format($system_date_format);
        } catch (\Throwable $th) {

            return $input_date;
        }
    }
}

if (!function_exists('dateTimeConvert')) {

    function dateTimeConvert($input_date_time)
    {
        try {
            $system_date_format = session()->get('system_date_format') . ' g:i A';
            if (empty($system_date_format)) {
                $date_format_id = AramiscGeneralSettings::where('id', 1)->first(['date_format_id'])->date_format_id;
                $system_date_format = AramiscDateFormat::where('id', $date_format_id)->first(['format'])->format . ' g:i A';
                session()->put('system_date_format', $system_date_format);
            }
            return \Carbon\Carbon::parse($input_date_time)->format($system_date_format);
        } catch (\Throwable $th) {
            return $input_date_time;
        }
    }
}

if (!function_exists('getAcademicId')) {
    function getAcademicId()
    {

        if (session()->has('sessionId')) {
            return session()->get('sessionId');
        } else {
            if (moduleStatusCheck('University')) {
                $session_id = generalSetting()->un_academic_id;
                if (!$session_id) {
                    $session_id = UnAcademicYear::where('school_id', Auth::user()->school_id)->where('active_status', 1)->first()->id;
                }
            } else {
                $session_id = generalSetting()->session_id;
                if (!$session_id) {
                    $session_id = AramiscAcademicYear::where('school_id', Auth::user()->school_id)->where('active_status', 1)->first()->id;
                }
            }

            session()->put('sessionId', $session_id);
            return session()->get('sessionId');
        }
    }
}

if (!function_exists('timeZone')) {
    function timeZone()
    {
        $time_zone_setup = session()->get('time_zone_setup');
        if (is_null($time_zone_setup)) {
            $time_zone = AramiscGeneralSettings::join('aramisc_time_zones', 'aramisc_time_zones.id', '=', 'aramisc_general_settings.time_zone_id')
                ->where('school_id', 1)->first('time_zone');
            session()->put('time_zone_setup', $time_zone);
            $time_zone_setup = session()->get('time_zone_setup');
        }
        return $time_zone_setup->time_zone;
    }
}
if (!function_exists('schoolTimeZone')) {
    function schoolTimeZone()
    {
        $time_zone_setup = session()->get('time_zone_setup');
        if (is_null($time_zone_setup)) {
            $time_zone = AramiscGeneralSettings::join('aramisc_time_zones', 'aramisc_time_zones.id', '=', 'aramisc_general_settings.time_zone_id')
                ->where('school_id', Auth::user()->school_id)->first('time_zone');
            session()->put('time_zone_setup', $time_zone);
            $time_zone_setup = session()->get('time_zone_setup');
        }
        return $time_zone_setup->time_zone;
    }
}

if (!function_exists('getUserLanguage')) {
    function getUserLanguage()
    {

        if (Auth::check()) {
            return userLanguage();
        } else {
            $school_id = app()->bound('school') ? app('school')->id : 1;
            $user = User::where('role_id', 1)->where('school_id', $school_id)->first();

            return $user ? $user->language : 'en';
        }
    }
}

if (!function_exists('checkAdmin')) {
    function checkAdmin()
    {
        if (Auth::check()) {
            if (Auth::user()->is_administrator == "yes") {
                return true;
            } elseif (Auth::user()->is_saas == 1) {
                return true;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('getTempleteDetails')) {
    function getTempleteDetails($purpose, $type = null)
    {
        $data = AramiscTemplate::query();
        $data = $data->where('purpose', $purpose)->where('status', 1);
        if ($type) {
            $data->where('type', $type);
        }

        if (Auth::check()) {
            $details = $data->where('school_id', Auth::user()->school_id)->first();
        } else {
            $details = $data->first();
        }
        return $details;
    }
}

if (!function_exists('send_mail')) {
    function send_mail($reciver_email, $receiver_name, $purpose, $data = [])
    {
        if (!$reciver_email) {
            return;
        }
        $templete = getTempleteDetails($purpose, 'email');
        if (!$templete) {
            return;
        }

        $school_id = Auth::check() && saasSettings('email_settings') ? Auth::user()->school_id : 1;

        $setting = AramiscEmailSetting::where('school_id', $school_id)->where('active_status', 1)->first();

        if (!$setting) {
            return;
        }


        $sender_email = $setting->from_email;
        $sender_name = $setting->from_name;
        $email_driver = $setting->mail_driver;

        $subject = getTempleteDetails($purpose, 'email')->subject;

        $body = AramiscTemplate::emailTempleteToBody(getTempleteDetails($purpose, 'email')->body, $data);
        $view = view('backEnd.email.emailBody', compact('body'));

        try {
            if (Schema::hasTable('aramisc_email_settings')) {
                if ($email_driver == "smtp") {
                    $config = Auth::check() ? DB::table('aramisc_email_settings')
                        ->where('school_id', Auth::user()->school_id)
                        ->where('mail_driver', 'smtp')
                        ->first() :
                        DB::table('aramisc_email_settings')
                            ->where('mail_driver', 'smtp')
                            ->first();

                    if ($config) {
                        Config::set('mail.default', 'smtp');
                        Config::set('mail.from.from', $config->mail_username);
                        Config::set('mail.from.name', $config->from_name);
                        Config::set('mail.mailers.smtp.host', $config->mail_host);
                        Config::set('mail.mailers.smtp.port', $config->mail_port);
                        Config::set('mail.mailers.smtp.username', $config->mail_username);
                        Config::set('mail.mailers.smtp.password', $config->mail_password);
                        Config::set('mail.mailers.smtp.encryption', $config->mail_encryption);
                    }
                } else {
                    Config::set('mail.default', 'sendmail');
                }
            }

            $emailData['driver'] = $email_driver;
            $emailData['reciver_email'] = $reciver_email;
            $emailData['receiver_name'] = $receiver_name;
            $emailData['sender_name'] = $sender_name;
            $emailData['sender_email'] = $sender_email;
            $emailData['subject'] = $subject;


            dispatch(new EmailJob($body, $emailData));
        } catch (\Exception $e) {
            Log::info($e);
        }
    }
}

if (!function_exists('send_mail_without_template')) {
    function send_mail_without_template($reciver_email, $receiver_name, $subject, $view, $compact = [])
    {


        $school_id = Auth::check() && saasSettings('email_settings') ? Auth::user()->school_id : 1;

        $setting = AramiscEmailSetting::where('school_id', $school_id)->where('active_status', 1)->first();

        if (!$setting) {
            return;
        }

        $sender_email = $setting->from_email;
        $sender_name = $setting->from_name;
        $email_driver = $setting->mail_driver;
        $view = view($view, $compact);
        try {
            if ($email_driver == "smtp") {
                if (Schema::hasTable('aramisc_email_settings')) {
                    $config = Auth::check() ? DB::table('aramisc_email_settings')
                        ->where('school_id', Auth::user()->school_id)
                        ->where('mail_driver', 'smtp')
                        ->first() :
                        DB::table('aramisc_email_settings')
                            ->where('mail_driver', 'smtp')
                            ->first();

                    if ($config) {
                        Config::set('mail.driver', $config->mail_driver);
                        Config::set('mail.from', $config->mail_username);
                        Config::set('mail.name', $config->from_name);
                        Config::set('mail.host', $config->mail_host);
                        Config::set('mail.port', $config->mail_port);
                        Config::set('mail.username', $config->mail_username);
                        Config::set('mail.password', $config->mail_password);
                        Config::set('mail.encryption', $config->mail_encryption);
                    }
                }
                Mail::send('backEnd.email.emailBody', compact('body'), function ($message) use ($reciver_email, $receiver_name, $sender_name, $sender_email, $subject) {
                    $message->to($reciver_email, $receiver_name)->subject($subject);
                    $message->from($sender_email, $sender_name);
                });
            }
            if ($email_driver == "php") {
                $message = (string)$view;
                $headers = "From: <$sender_email> \r\n";
                $headers .= "Reply-To: $receiver_name <$reciver_email> \r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                @mail($reciver_email, $subject, $message, $headers);
            }
        } catch (\Exception $e) {
            Log::info($e);
        }
    }
}


if (!function_exists('getFileName')) {
    function getFileName($data)
    {
        if ($data) {
            $name = explode('/', $data);
            return $name[count($name) - 1] ?? $name[0];
        } else {
            return '';
        }
    }
}


// Get File Path From HELPER

if (!function_exists('getFilePath3')) {
    function getFilePath3($data)
    {

        if ($data) {
            $name = explode('/', $data);
            return $name[3] ?? $name[0];
        } else {
            return '';
        }
    }
}

if (!function_exists('getFilePath4')) {
    function getFilePath4($data)
    {
        if ($data) {
            $name = explode('/', $data);
            if ($name[4]) {
                return $name[3];
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
}

if (!function_exists('showPicName')) {
    function showPicName($data)
    {
        try {
            if ($data) {
                $name = explode('/', $data);
                if ($name[4]) {
                    return $name[4];
                } else {
                    return '';
                }
            } else {
                return '';
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('showJoiningLetter')) {
    function showJoiningLetter($data)
    {
        $name = explode('/', $data);
        return $name[3];
    }
}

if (!function_exists('showResume')) {
    function showResume($data)
    {
        $name = explode('/', $data);
        return $name[3];
    }
}

if (!function_exists('showDocument')) {
    function showDocument($data)
    {
        @$name = explode('/', @$data);
        if (!empty(@$name[4])) {

            return $name[4];
        } else {
            return '';
        }
    }
}
// end get file path from helpers

if (!function_exists('termResult')) {
    function termResult($exam_id, $class_id, $section_id, $student_id, $subject_count)
    {
        try {
            $assigned_subject = AramiscAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->get();
            $mark_store = DB::table('aramisc_mark_stores')->where([['class_id', $class_id], ['section_id', $section_id], ['exam_term_id', $exam_id], ['student_id', $student_id]])->first();
            $subject_marks = [];
            $subject_gpas = [];
            foreach ($assigned_subject as $subject) {
                $subject_mark = DB::table('aramisc_mark_stores')->where([['class_id', $class_id], ['section_id', $section_id], ['exam_term_id', $exam_id], ['student_id', $student_id], ['subject_id', $subject->subject_id]])->first();
                $custom_result = new CustomResultSetting; // correct

                $subject_gpa = $custom_result->getGpa($subject_mark->total_marks);
                // return $subject_mark;
                $subject_marks[$subject->subject_id][0] = $subject_mark->total_marks;
                $subject_marks[$subject->subject_id][1] = $subject_gpa;
                $subject_gpas[$subject->subject_id] = $subject_gpa;
            }
            $total_gpa = array_sum($subject_gpas);
            $term_result = $total_gpa / $subject_count;
            return $term_result;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('getFinalResult')) {
    function getFinalResult($exam_id, $class_id, $section_id, $student_id, $percentage)
    {
        try {
            $system_setting = AramiscGeneralSettings::where('school_id', auth()->user()->school_id)->first();
            $system_setting = $system_setting->session_id;
            $custom_result_setup = CustomResultSetting::where('academic_year', $system_setting)->first();

            $assigned_subject = AramiscAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->get();

            $all_subjects_gpa = [];
            foreach ($assigned_subject as $subject) {
                $custom_result = new CustomResultSetting;
                $subject_gpa = $custom_result->getSubjectGpa($exam_id, $class_id, $section_id, $student_id, $subject->subject_id);
                $all_subjects_gpa[] = $subject_gpa[$subject->subject_id][1];
            }
            $percentage = $custom_result_setup->$percentage;
            $term_gpa = array_sum($all_subjects_gpa) / $assigned_subject->count();
            $percentage = number_format((float)$percentage, 2, '.', '');
            $new_width = ($percentage / 100) * $term_gpa;
            return $new_width;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('getSubjectGpa')) {
    function getSubjectGpa($class_id, $section_id, $exam_id, $student_id, $subject)
    {
        try {
            $subject_marks = [];
            $subject_mark = DB::table('aramisc_mark_stores')->where('student_id', $student_id)->where('exam_term_id', '=', $exam_id)->first();

            $custom_result = new CustomResultSetting;
            $subject_gpa = $custom_result->getGpa($subject_mark->total_marks);

            $subject_marks[$subject][0] = $subject_mark->total_marks;
            $subject_marks[$subject][1] = $subject_gpa;

            // return $subject_mark->total_marks;
            return $subject_marks;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('getGrade')) {
    function getGrade($marks, $description = null)
    {
        try {
            if ($description) {
                $marks_gpa = DB::table('aramisc_marks_grades')->where('percent_from', '<=', $marks)->where('percent_upto', '>=', $marks)
                    ->where('academic_id', getAcademicId())->first();
                return $marks_gpa->description;
            } else {
                $marks_gpa = DB::table('aramisc_marks_grades')->where('percent_from', '<=', $marks)->where('percent_upto', '>=', $marks)
                    ->where('academic_id', getAcademicId())->first();
                return $marks_gpa->grade_name;
            }
        } catch (\Exception $e) {
            return 'Undefined';
        }
    }
}

if (!function_exists('getNumberOfPart')) {
    function getNumberOfPart($subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            $results = AramiscExamSetup::where([
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('GetResultBySubjectId')) {
    function GetResultBySubjectId($class_id, $section_id, $subject_id, $exam_id, $student_id)
    {

        try {
            $data = AramiscMarkStore::where([
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_id],
                ['student_id', $student_id],
                ['subject_id', $subject_id],
            ])->get();
            return $data;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('GetFinalResultBySubjectId')) {
    function GetFinalResultBySubjectId($class_id, $section_id, $subject_id, $exam_id, $student_id)
    {

        try {
            $data = AramiscResultStore::where([
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['exam_type_id', $exam_id],
                ['student_id', $student_id],
                ['subject_id', $subject_id],
            ])->first();

            return $data;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('markGpa')) {
    function markGpa($marks)
    {
        $mark = AramiscMarksGrade::where([
            ['percent_from', '<=', floor($marks)],
            ['percent_upto', '>=', floor($marks)]
        ])
            ->first();
        if ($mark) {
            return $mark;
        } else {
            $fail_grade = AramiscMarksGrade::min('gpa');
            $mark = AramiscMarksGrade::where('gpa', $fail_grade)->first();
            return $mark;
        }
    }
}
if (!function_exists('getGrade')) {
    function getGrade($grade)
    {
        $mark = AramiscMarksGrade::where('from', '<=', $grade)->where('up', '>=', $grade)->where('academic_id', getAcademicId())->first();
        if ($mark) {
            return $mark;
        } else {
            $fail_grade = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $mark = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $fail_grade)
                ->first();

            return $mark;
        }
    }
}

if (!function_exists('getGradeUpdate')) {
    function getGradeUpdate($grade)
    {
        $mark = AramiscMarksGrade::where('from', '<=', $grade)->where('up', '>=', $grade)->where('academic_id', getAcademicId())->first();
        if ($mark) {
            return $mark;
        } else {
            $fail_grade = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $mark = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $fail_grade)
                ->first();

            return $mark;
        }
    }
}

if (!function_exists('is_optional_subject')) {
    function is_optional_subject($student_id, $subject_id)
    {
        try {
            $result = AramiscOptionalSubjectAssign::where('student_id', $student_id)->where('subject_id', $subject_id)->first();
            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('getMarksOfPart')) {
    function getMarksOfPart($student_id, $subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            $results = AramiscMarkStore::where([
                ['student_id', $student_id],
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('getExamResult')) {
    function getExamResult($exam_id, $student)
    {
        $eligible_subjects = AramiscAssignSubject::where('class_id', $student->class_id)->where('section_id', $student->section_id)->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)->get();

        foreach ($eligible_subjects as $subject) {

            $getMark = AramiscResultStore::where([
                ['exam_type_id', $exam_id],
                ['class_id', $student->class_id],
                ['section_id', $student->section_id],
                ['student_id', $student->id],
                ['subject_id', $subject->subject_id],
            ])->first();

            if ($getMark == "") {
                return false;
            }

            $result = AramiscResultStore::where([
                ['exam_type_id', $exam_id],
                ['class_id', $student->class_id],
                ['section_id', $student->section_id],
                ['student_id', $student->id],
            ])->get();

            return $result;
        }
    }
}

if (!function_exists('teacherAssignedClass')) {
    function teacherAssignedClass()
    {
        try {
            $class_id = [];
            $role_id = Auth::user()->role_id;
            if ($role_id == 4) {
                $classes = AramiscClassTeacher::where('teacher_id', Auth::user()->id)->get(['id']);
                foreach ($classes as $class) {
                    $class_id[] = $class->module_id;
                }
            } else {

                $general_setting = AramiscGeneralSettings::where('school_id', auth()->user()->school_id)->first();
                return @$general_setting->school_name;
            }
        } catch (\Exception $e) {
            return $class_id = [];
        }
    }
}

if (!function_exists('getValueByStringTestRegistration')) {
    function getValueByStringTestRegistration($data, $str)
    {
        if ($str == 'password') {
            return '123456';
        } elseif ($str == 'school_name') {
            if (moduleStatusCheck('Saas') == true) {
                $student_info = AramiscStudentRegistration::find(@$data['id']);
                return @$student_info->school->school_name;
            } else {
                $general_setting = AramiscGeneralSettings::find(1);
                return @$general_setting->school_name;
            }
        }

        if ($data['slug'] == 'student') {
            $student_info = AramiscStudentRegistration::find(@$data['id']);
            if ($str == 'name') {
                return @$student_info->first_name . ' ' . @$student_info->last_name;
            } elseif ($str == 'guardian_name') {
                return @$student_info->guardian_name;
            } elseif ($str == 'class') {
                return @$student_info->class->class_name;
            } elseif ($str == 'section') {
                return @$student_info->section->section_name;
            }
        } elseif ($data['slug'] == 'parent') {
            $parent_info = AramiscStudentRegistration::find(@$data['id']);
            if ($str == 'name') {
                return @$parent_info->guardian_name;
            } elseif ($str == 'student_name') {
                return @$parent_info->first_name . ' ' . @$parent_info->last_name;
            }
        }
    }
}
if (!function_exists('getValueByStringTestReset')) {
    function getValueByStringTestReset($data, $str)
    {
        if ($str == 'school_name') {

            $general_setting = AramiscGeneralSettings::where('school_id', auth()->user()->school_id)->first();
            return @$general_setting->school_name;
        } elseif ($str == 'name') {
            $user = User::where('email', $data['email'])->first();
            return @$user->full_name;
        }
    }
}

if (!function_exists('subjectPosition')) {
    function subjectPosition($subject_id, $class_id, $custom_result)
    {

        $students = AramiscStudent::where('class_id', $class_id)->get();

        $subject_mark_array = [];
        foreach ($students as $student) {
            $subject_marks = 0;

            $first_exam_mark = AramiscMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id1)->sum('total_marks');

            $subject_marks = $subject_marks + $first_exam_mark / 100 * $custom_result->percentage1;

            $second_exam_mark = AramiscMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id2)->sum('total_marks');

            $subject_marks = $subject_marks + $second_exam_mark / 100 * $custom_result->percentage2;

            $third_exam_mark = AramiscMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id3)->sum('total_marks');

            $subject_marks = $subject_marks + $third_exam_mark / 100 * $custom_result->percentage3;

            $subject_mark_array[] = round($subject_marks);
        }

        arsort($subject_mark_array);

        $position_array = [];
        foreach ($subject_mark_array as $position_mark) {
            $position_array[] = $position_mark;
        }

        return $position_array;
    }
}

if (!function_exists('getValueByStringDuesFees')) {
    function getValueByStringDuesFees($student_detail, $str, $fees_info)
    {

        if ($str == 'student_name') {

            return @$student_detail->full_name;
        } elseif ($str == 'parent_name') {

            $parent_info = AramiscParent::find($student_detail->parent_id);
            return @$parent_info->fathers_name;
        } elseif ($str == 'due_amount') {

            return @$fees_info['dues_fees'];
        } elseif ($str == 'due_date') {

            $fees_master = AramiscFeesMaster::find($fees_info['fees_master']);
            return @$fees_master->date;
        } elseif ($str == 'school_name') {

            return @Auth::user()->school->school_name;
        } elseif ($str == 'fees_name') {

            $fees_master = AramiscFeesMaster::find($fees_info['fees_master']);
            return $fees_master->feesTypes->name;
        }
    }
}
if (!function_exists('assignedRoutineSubject')) {

    function assignedRoutineSubject($class_id, $section_id, $exam_id, $subject_id)
    {

        try {
            return AramiscExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)->where('exam_term_id', $exam_id)->where('subject_id', $subject_id)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('assignedRoutine')) {

    function assignedRoutine($class_id, $section_id, $exam_id, $subject_id, $exam_period_id)
    {
        try {
            return AramiscExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)->where('exam_term_id', $exam_id)->where('subject_id', $subject_id)
                ->where('exam_period_id', $exam_period_id)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('is_absent_check')) {

    function is_absent_check($exam_id, $class_id, $section_id, $subject_id, $student_id)
    {
        try {
            $exam_attendance = AramiscExamAttendance::where('exam_id', $exam_id)->where('class_id', $class_id)->where('section_id', $section_id)->where('subject_id', $subject_id)->first();
            $exam_attendance_child = AramiscExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->where('student_id', $student_id)->first();
            return $exam_attendance_child;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('feesPayment')) {
    function feesPayment($type_id, $student_id)
    {
        try {
            return AramiscFeesPayment::where('active_status', 1)->where('fees_type_id', $type_id)->where('student_id', $student_id)->get();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}

if (!function_exists('generalSetting')) {
    function generalSetting()
    {
        if (session()->has('generalSetting')) {
            return session()->get('generalSetting');
        } else {
            if (app()->bound('school')) {
                $generalSetting = AramiscGeneralSettings::where('school_id', app('school')->id)->first();
            } elseif (request('school_id')) {
                $generalSetting = AramiscGeneralSettings::where('school_id', request('school_id'))->first();
            } else {
                // $generalSetting = Auth::check() ? AramiscGeneralSettings::where('school_id', Auth::user()->school_id)->first() : AramiscGeneralSettings::where('school_id',app('school')->id)->first();
                $generalSetting = Auth::check() ? AramiscGeneralSettings::where('school_id', Auth::user()->school_id)->first() : AramiscGeneralSettings::where('school_id', 1)->first();
            }
        }

        session()->put('generalSetting', $generalSetting);

        return session()->get('generalSetting');
    }
}


if (!function_exists('systemDateFormat')) {
    function systemDateFormat()
    {
        if (session()->has('system_date_format')) {
            return session()->get('system_date_format');
        } else {
            $system_date_format = AramiscDateFormat::find(DB::table('aramisc_general_settings')->first()->date_format_id);
            session()->put('system_date_foramt', $system_date_format);

            return session()->get('system_date_foramt');
        }
    }
}
if (!function_exists('emailTemplate')) {
    function emailTemplate()
    {
        if (session()->has('email_template')) {
            return session()->get('email_template');
        } else {
            $email_template = AramiscTemplate::where('school_id', Auth::user()->school_id)->first();
            session()->put('email_template', $email_template);

            return session()->get('email_template');
        }
    }
}

if (!function_exists('dashboardBackground')) {
    function dashboardBackground()
    {
        return app('dashboard_bg');
    }
}

if (!function_exists('allStyles')) {
    function allStyles()
    {

        if (session()->has('all_styles')) {
            return session()->get('all_styles');
        } else {
            $all_styles = AramiscStyle::where('school_id', 1)->where('active_status', 1)->get();
            session()->put('all_styles', $all_styles);

            return session()->get('all_styles');
        }
    }
}

if (!function_exists('textDirection')) {
    function textDirection()
    {


        if (session()->has('text_direction')) {
            return session()->get('text_direction');
        } else {
            $ttl_rtl = Auth::user()->rtl_ltl;
            session()->put('text_direction', $ttl_rtl);
            // return $ttl_rtl;
            return session()->get('text_direction');
        }
    }
}
if (!function_exists('userRtlLtl')) {
    function userRtlLtl()
    {
        // return 1;

        if (session()->has('user_text_direction')) {
            return session()->get('user_text_direction');
        } else {
            $school_id = app()->bound('school') ? app('school')->id : 1;
            $user = User::where('role_id', 1)->where('school_id', $school_id)->first();

            $ttl_rtl = $user ? $user->rtl_ltl : 2;
            session()->put('user_text_direction', $ttl_rtl);

            return session()->get('user_text_direction');
        }
    }
}
if (!function_exists('userLanguage')) {
    function userLanguage()
    {

        if (session()->has('user_language')) {
            return session()->get('user_language');
        } else {
            $language = Auth::user()->language;
            session()->put('user_language', $language);

            return session()->get('user_language');
        }
    }
}

if (!function_exists('schoolConfig')) {
    function schoolConfig()
    {
        return app('school_info');
    }
}
if (!function_exists('selectedLanguage')) {
    function selectedLanguage()
    {
        if (session()->has('selected_language')) {
            return session()->get('selected_language');
        } else {
            $selected_language = Auth::check() ? AramiscGeneralSettings::where('school_id', Auth::user()->school_id)->first() :
                DB::table('aramisc_general_settings')->where('school_id', 1)->first();
            session()->put('selected_language', $selected_language);

            return session()->get('selected_language');
        }
    }
}

if (!function_exists('profile')) {
    function profile()
    {
        return auth()->user()->profile;
    }
}

if (!function_exists('getSession')) {
    function getSession()
    {
        if (session()->has('session')) {
            return session()->get('session');
        } else {
            $selected_language = Auth::check() ? AramiscGeneralSettings::where('school_id', Auth::user()->school_id)->first() :
                DB::table('aramisc_general_settings')->where('school_id', 1)->first();
            $session = DB::table('aramisc_academic_years')->where('id', $selected_language->session_id)->first();
            session()->put('session', $session);

            return session()->get('session');
        }
    }
}

if (!function_exists('systemLanguage')) {
    function systemLanguage()
    {
        session()->forget('systemLanguage');
        if (session()->has('systemLanguage')) {
            return session()->get('systemLanguage');
        } else {
            $systemLanguage = AramiscLanguage::where('school_id', auth()->user()->school_id)->get();
            session()->put('systemLanguage', $systemLanguage);
            return session()->get('systemLanguage');
        }
    }
}

if (!function_exists('academicYears')) {
    function academicYears()
    {
        // session()->forget('academic_years');
        if (moduleStatusCheck('University')) {
            if (!session()->has('academic_years')) {
                $academic_years = Auth::check() ? UnAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get() : '';
                session()->put('academic_years', $academic_years);
                return session()->get('academic_years');
            } else {
                return session()->get('academic_years');
            }
        } else {
            if (session()->has('academic_years')) {
                return session()->get('academic_years');
            } else {
                $academic_years = Auth::check() ? AramiscAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get() : '';
                session()->put('academic_years', $academic_years);
                return session()->get('academic_years');
            }
        }
    }
}

if (!function_exists('subjectFullMark')) {
    function subjectFullMark($examtype, $subject, $class_id = null, $section_id = null)
    {
        try {
            $full_mark = AramiscExam::withOutGlobalScopes();
            $full_mark->where('school_id', Auth::user()->school_id)
                ->where('exam_type_id', $examtype);
            if (moduleStatusCheck('University')) {

                $full_mark = $full_mark->where('un_subject_id', $subject);
            } else {
                $full_mark = $full_mark->where('subject_id', $subject);
            }
            if (moduleStatusCheck('University')) {
                $full_mark = $full_mark->first('exam_mark')->exam_mark;
            } else {
                $full_mark = $full_mark->where('class_id', $class_id)->where('section_id', $section_id)->first('exam_mark')->exam_mark;
            }


            return $full_mark;
        } catch (\Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('subject100PercentMark')) {
    function subject100PercentMark()
    {
        try {
            return 100;
        } catch (\Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('teacherAccess')) {
    function teacherAccess()
    {
        try {
            $user = Auth::user();
            if ($user->role_id == 4) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('subjectPercentageMark')) {
    function subjectPercentageMark($obtained_mark, $full_nark)
    {
        if (!$full_nark) {
            return 0;
        }
        try {
            return ($obtained_mark / $full_nark) * 100;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('termWiseFullMark')) {
    function termWiseFullMark($type_ids, $student_id, $academic_id = null)
    {
        if (!$academic_id) {
            $academic_id = getAcademicId();
        }
        try {
            $average_gpa = 0;
            foreach ($type_ids as $type_id) {
                $total_gpa = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $total_subject = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->count('subject_id');

                $percentage = CustomResultSetting::where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first('exam_percentage')->exam_percentage;

                if ($total_subject) {
                    $average_gpa += ($total_gpa / $total_subject) * ($percentage / 100);
                }
            }
            return $average_gpa;
        } catch (\Exception $e) {
            return false;
        }
    }
}


if (!function_exists('termWiseGpa')) {
    function termWiseGpa($type_id, $student_id, $with_optional_subject_mark = null, $academic_id = null)
    {
        if (!$academic_id) {
            $academic_id = getAcademicId();
        }
        try {
            $average_gpa = 0;
            if ($with_optional_subject_mark == null) {
                $total_gpa = AramiscResultStore::select('total_gpa_point')->where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $total_subject = AramiscResultStore::select('subject_id')->where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->count('subject_id');

                $percentage = CustomResultSetting::where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first('exam_percentage')->exam_percentage;

                if ($total_subject) {
                    $average_gpa += ($total_gpa / $total_subject) * ($percentage / 100);
                }
                return $average_gpa;
            } elseif ($with_optional_subject_mark != null) {

                $percentage = CustomResultSetting::where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first('exam_percentage')->exam_percentage;

                $average_gpa += $with_optional_subject_mark * ($percentage / 100);
                return $average_gpa;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}


if (!function_exists('termWiseTotalMark')) {
    function termWiseTotalMark($type_id, $student_id, $optional_subject = null, $academic_id = null)
    {
        if (!$academic_id) {
            $academic_id = getAcademicId();
        }
        try {
            if ($optional_subject == null) {
                $average_gpa = 0;
                $total_gpa = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $total_subject = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->count('subject_id');

                if ($total_subject) {
                    $average_gpa += $total_gpa / $total_subject;
                }


                return $average_gpa;
            } elseif ($optional_subject != null) {
                $average_gpa = 0;
                $optional_subject_extra_gpa = 0;

                $class_id = StudentRecord::find($student_id)->class_id;
                $optional_subject_above = AramiscClassOptionalSubject::where('class_id', $class_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', $academic_id)
                    ->first('gpa_above')->gpa_above;

                $subject_ids = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get('subject_id');

                $optional_subject_id = AramiscOptionalSubjectAssign::whereIn('subject_id', $subject_ids)
                    ->where('student_id', $student_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first('subject_id')->subject_id;

                $without_optional_subject_gpa = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', '!=', $optional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $optional_subject_gpa = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', $optional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $maxgpa = AramiscMarksGrade::withOutGlobalScopes()->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->max('gpa');

                if ($optional_subject_gpa > $optional_subject_above) {
                    $optional_subject_extra_gpa = $optional_subject_gpa - $optional_subject_above;
                }

                $with_optional_subject_extra_gpa = $without_optional_subject_gpa + $optional_subject_extra_gpa;

                $final_gpa_with_optional_subject = $with_optional_subject_extra_gpa / (count($subject_ids) - 1);

                if ($maxgpa < $final_gpa_with_optional_subject) {
                    return $maxgpa;
                } else {
                    return $final_gpa_with_optional_subject;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}


if (!function_exists('optionalSubjectFullMark')) {
    function optionalSubjectFullMark($type_id, $student_id, $above_gpa, $purpose = null, $academic_id = null)
    {
        if (!$academic_id) {
            $academic_id = getAcademicId();
        }
        try {
            $subject_ids = AramiscResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get('subject_id');

            $additional_subject_id = AramiscOptionalSubjectAssign::whereIn('subject_id', $subject_ids)
                ->where('record_id', $student_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first('subject_id')->subject_id;

            if ($purpose == "optional_sub_gpa") {
                $total_mark = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', $additional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                return $total_mark;
            } elseif ($purpose == "with_optional_sub_gpa") {
                $total_mark = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', $additional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $exam_type_id = AramiscResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', $additional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->count('exam_type_id');

                $total = ($total_mark - $above_gpa) * $exam_type_id;
                return $total;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('termWiseAddOptionalMark')) {
    function termWiseAddOptionalMark($type_id, $student_id, $above_gpa, $academic_id = null)
    {
        if (!$academic_id) {
            $academic_id = getAcademicId();
        }
        try {
            $subject_ids = AramiscResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get('subject_id');

            $additional_subject_id = AramiscOptionalSubjectAssign::whereIn('subject_id', $subject_ids)
                ->where('record_id', $student_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first('subject_id')->subject_id;

            $additional_subject_mark = AramiscResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('subject_id', $additional_subject_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->sum('total_gpa_point');

            $additional_single_subject_mark = AramiscResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('subject_id', $additional_subject_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first('total_gpa_point')->total_gpa_point;

            $additional_mark_reduction = $additional_single_subject_mark - $above_gpa;
            if ($additional_mark_reduction > 0) {
            }
            $all_subject_mark = AramiscResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('subject_id', '!=', $additional_subject_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->sum('total_gpa_point');

            $without_additional_total_subject = AramiscResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('subject_id', '!=', $additional_subject_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->count('subject_id');

            $with_additional_full_gpa = $all_subject_mark + ($additional_subject_mark - $above_gpa);

            $percentage = CustomResultSetting::where('exam_type_id', $type_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first('exam_percentage')->exam_percentage;

            $with_additional_average_gpa = ($with_additional_full_gpa / $without_additional_total_subject) * ($percentage / 100);

            return $with_additional_average_gpa;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('gradeName')) {
    function gradeName($total_gpa, $academic_id = null)
    {
        if (!$academic_id) {
            $academic_id = getAcademicId();
        }
        try {
            $grade_name = AramiscMarksGrade::where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->where('from', '<=', $total_gpa)
                ->where('up', '>=', $total_gpa)
                ->first('grade_name')->grade_name;
            return $grade_name;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('remarks')) {
    function remarks($total_gpa, $academic_id = null)
    {
        if (!$academic_id) {
            $academic_id = getAcademicId();
        }
        try {
            $description = AramiscMarksGrade::where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->where('from', '<=', $total_gpa)
                ->where('up', '>=', $total_gpa)
                ->first('description')->description;
            return $description;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('subjectHighestMark')) {
    function subjectHighestMark($exam_id, $subject_id, $class_id, $section_id)
    {
        try {
            $highest_mark = AramiscResultStore::where([['class_id', $class_id], ['exam_type_id', $exam_id], ['section_id', $section_id]])
                ->where('subject_id', $subject_id)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->max('total_marks');
            return $highest_mark;
        } catch (\Throwable $e) {
            return false;
        }
    }
}


if (!function_exists('getAllUserForChatBasedOnCondition')) {
    function getAllUserForChatBasedOnCondition()
    {
        try {
            $users = User::with('roles')->where('id', '!=', auth()->id())->get();
            if (app('general_settings')->get('chat_can_teacher_chat_with_parents') == 'no') {
                if (auth()->user()->roles->id == 4) {
                    foreach ($users as $index => $user) {
                        $user->roles->id === 3 ? $users->forget($index) : '';
                    }
                }
            }
            return $users;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('chatOpen')) {
    function chatOpen()
    {
        return app('general_settings')->get('chat_open') == 'yes';
    }
}

// Jitsi Module Start
if (!function_exists('getDomainName')) {
    function getDomainName($url)
    {
        $url_domain = preg_replace("(^https?://)", "", $url);
        $url_domain = preg_replace("(^http?://)", "", $url_domain);
        $url_domain = str_replace("/", "", $url_domain);
        return $url_domain;
    }
}
// Jitsi Module End

if (!function_exists('invitationRequired')) {
    function invitationRequired()
    {
        return app('general_settings')->get('chat_invitation_requirement') == 'required';
    }
}

if (!function_exists('intallMdouleMenu')) {
    function intallMdouleMenu($module_id, $module_name)
    {
        if (Auth::user()->role_id == 2 || Auth::user()->role_id == 3) {
            $menu_manage_module_id = MenuManage::where('active_status', 1)
                ->where('user_id', Auth::user()->id)
                ->where('role_id', Auth::user()->role_id)
                ->where('module_id', $module_id)
                ->first();
        } else {
            $menu_manage_module_id = MenuManage::where('active_status', 1)
                ->where('user_id', Auth::user()->id)
                ->where('role_id', Auth::user()->role_id)
                ->where('module_addons', $module_id)
                ->first();
        }
        if (moduleStatusCheck($module_name) == true && is_null($menu_manage_module_id)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('customFieldValue')) {
    function customFieldValue($student_id, $labelName, $formName)
    {
        $custom_field_values = [];
        if ($formName == "student_registration") {
            $custom_field_data = AramiscStudent::withOutGlobalScopes()->where('id', $student_id)->first();
            if (is_null($custom_field_data) && moduleStatusCheck('ParentRegistration')) {
                $custom_field_data = Modules\ParentRegistration\Entities\AramiscStudentRegistration::find($student_id);
            }
            @$value = $custom_field_data->custom_field;
        } elseif ($formName == "staff_registration") {
            $custom_field_data = AramiscStaff::withOutGlobalScopes()->where('id', $student_id)->first();
            $value = $custom_field_data->custom_field;
        } elseif ($formName == "school_registration") {
            $custom_field_data = AramiscSchool::withOutGlobalScopes()->where('id', $student_id)->first();
            $value = $custom_field_data->custom_field;
        } else {
            $value = null;
        }

        if ($value != null) {
            $custom_field_values = json_decode($custom_field_data->custom_field, true);
            if (array_key_exists($labelName, $custom_field_values)) {
                return $custom_field_values[$labelName];
            } else {
                return null;
            }
        }
    }
}
if (!function_exists('paymentMethodName')) {
    function paymentMethodName($payment_method_id)
    {
        $paymentMethodName = AramiscPaymentMethhod::where('id', $payment_method_id)
            ->where('school_id', Auth::user()->school_id)
            ->first('method')->method;
        if ($paymentMethodName == "Bank") {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('moduleVersion')) {
    function moduleVersion($module_name)
    {
        $dataPath = 'Modules/' . $module_name . '/' . $module_name . '.json';
        $strJsonFileContents = file_get_contents($dataPath);
        $array = json_decode($strJsonFileContents, true);
        $version = $array[$module_name]['versions'][0];
        return $version;
    }
}

if (!function_exists('menuPosition')) {
    function menuPosition($id)
    {

        $is_have = count(app('sidebar_news')) > 0;
        if ($id == 'is_submit') {
            return $is_have ? 1 : 0;
        }

        if ($is_have) {
            $sidebar = app('sidebar_news')->where('active_status', 1)->where('aramisc_module_id', $id)->first();

            return $sidebar ? $sidebar->parent_position_no : $id;
        } else {
            return false;
        }
    }
}

if (!function_exists('menuStatus')) {
    function menuStatus($id)
    {
        $is_have = count(app('sidebar_news')) > 0;
        if (($is_have)) {
            $is_have_id = app('sidebar_news')->where('aramisc_module_id', $id)->first();
            if ($is_have_id) {
                return $is_have_id->active_status == 1 ? true : false;
            } else {
                return auth()->user()->role_id == 1 ? true : userPermission($id);
            }
        }
        return true;
    }
}


if (!function_exists('courseSetting')) {
    function courseSetting()
    {
        return CourseSetting::where('school_id', Auth::user()->school_id)->first();
    }
}

if (!function_exists('fileUpload')) {
    function fileUpload($file, $destination)
    {

        $fileName = "";

        if (!$file) {
            return $fileName;
        }

        $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();

        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }
        $file->move($destination, $fileName);
        $fileName = $destination . $fileName;
        return $fileName;
    }
}

if (!function_exists('fileUpdate')) {
    function fileUpdate($databaseFile, $file, $destination)
    {

        $fileName = "";

        if ($file) {
            $fileName = fileUpload($file, $destination);

            if ($databaseFile && file_exists($databaseFile)) {

                unlink($databaseFile);
            }
        } elseif (!$file and $databaseFile) {
            $fileName = $databaseFile;
        }

        return $fileName;
    }
}

if (!function_exists('putEnvConfigration')) {
    function putEnvConfigration($envKey, $envValue)
    {

        $value = '"' . $envValue . '"';
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $str .= "\n";
        $keyPosition = strpos($str, "{$envKey}=");

        if (is_bool($keyPosition)) {

            $str .= $envKey . '="' . $envValue . '"';
        } else {
            $endOfLinePosition = strpos($str, "\n", $keyPosition);
            $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
            $str = str_replace($oldLine, "{$envKey}={$value}", $str);

            $str = substr($str, 0, -1);
        }

        if (!file_put_contents($envFile, $str)) {
            return false;
        } else {
            return true;
        }
    }
}


if (!function_exists('feesInvoiceSettings')) {
    function feesInvoiceSettings()
    {
        $invoiceSettings = FmFeesInvoiceSettings::where('school_id', Auth::user()->school_id)->first();
        return $invoiceSettings;
    }
}

if (!function_exists('feesInvoiceNumber')) {
    function feesInvoiceNumber($invoice)
    {
        $settings = feesInvoiceSettings();
        $positions = json_decode($settings->invoice_positions);
        $format = '';
        foreach ($positions as $position) {
            if ($format) {
                $format .= '-';
            }
            $format .= $position->id;
        }

        $format = $format . '-inv_id';

        $key = [
            'prefix',
            'admission_no',
            'class',
            'section',
            'inv_id',
        ];

        $value = [
            $settings->prefix,
            Str::limit(@$invoice->studentInfo->admission_no, $settings->admission_limit),
            Str::limit(@$invoice->recordDetail->class->class_name, $settings->class_limit),
            Str::limit(@$invoice->recordDetail->section->section_name, $settings->section_limit),
            $settings->uniq_id_start + $invoice->id
        ];
        return str_replace($key, $value, $format);
    }
}

if (!function_exists('send_sms')) {
    function send_sms($reciver_number, $purpose, $data)
    {
        if ($purpose != "test_sms") {
            $templete = getTempleteDetails($purpose, 'sms');
            if (!$templete) {
                return;
            }
        }

        $school_id = Auth::check() && saasSettings('sms_settings') ? Auth::user()->school_id : 1;

        $activeSmsGateway = AramiscSmsGateway::where('school_id', $school_id)->where('active_status', 1)->first();
        if (!$activeSmsGateway) {
            return;
        }

        if ($purpose != "test_sms") {
            $body = AramiscTemplate::smsTempleteToBody($templete->body, $data);
        } else {
            $body = "It is a Test Sms From " . $activeSmsGateway->gateway_name . " -" . generalSetting()->school_name;
        }

        try {
            if ($activeSmsGateway->gateway_name == 'Twilio') {
                $account_id = $activeSmsGateway->twilio_account_sid;
                $auth_token = $activeSmsGateway->twilio_authentication_token;
                $from_phone_number = $activeSmsGateway->twilio_registered_no;
                if (!$account_id || $auth_token) {
                    return;
                }

                $client = new Client($account_id, $auth_token);
                $result = $message = $client->messages->create($reciver_number, array('from' => $from_phone_number, 'body' => $body));
            } else if ($activeSmsGateway->gateway_name == 'Msg91') {
                $msg91_authentication_key_sid = $activeSmsGateway->msg91_authentication_key_sid;
                $msg91_sender_id = $activeSmsGateway->msg91_sender_id;
                $msg91_route = $activeSmsGateway->msg91_route;
                $msg91_country_code = $activeSmsGateway->msg91_country_code;

                if ($reciver_number != "") {
                    $curl = curl_init();
                    $url = "https://api.msg91.com/api/sendhttp.php?mobiles=" .
                        $reciver_number . "&authkey=" .
                        $msg91_authentication_key_sid . "&route=" .
                        $msg91_route . "&sender=" .
                        $msg91_sender_id . "&message=" .
                        urlencode($body) . "&country=" + $msg91_country_code;

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                }
            } elseif ($activeSmsGateway->gateway_name == 'TextLocal') {
                // Config variables. Consult http://api.txtlocal.com/docs for more info.
                $url = $activeSmsGateway->type == 'in' ? 'https://api.textlocal.in/send/?' : 'https://api.txtlocal.com/send/?';
                $test = "0";
                $sender = $activeSmsGateway->textlocal_sender; // This is who the message appears to be from.
                $message = urlencode($body);
                $data = "username=" . $activeSmsGateway->textlocal_username .
                    "&hash=" . $activeSmsGateway->textlocal_hash .
                    "&message=" . $message .
                    "&sender=" . $sender .
                    "&numbers=" . $reciver_number .
                    "&test=" . $test;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch); // This is the result from the API
                curl_close($ch);
            } else if ($activeSmsGateway->gateway_name == 'AfricaTalking') {
                $username = $activeSmsGateway->africatalking_username;
                $apiKey = $activeSmsGateway->africatalking_api_key;
                $AT = new AfricasTalking($username, $apiKey);

                $sms_Send = $AT->sms();
                $sms_Send->send(['to' => $reciver_number, 'message' => $body]);
            } else if ($activeSmsGateway->gateway_name == 'Himalayasms') {
                if ($reciver_number != "") {
                    $client = new Http();
                    $request = $client->get("https://sms.techhimalaya.com/base/smsapi/index.php", [
                        'query' => [
                            'key' => $activeSmsGateway->himalayasms_key,
                            'senderid' => $activeSmsGateway->himalayasms_senderId,
                            'campaign' => $activeSmsGateway->himalayasms_campaign,
                            'routeid' => $activeSmsGateway->himalayasms_routeId,
                            'contacts' => $reciver_number,
                            'msg' => $body,
                            'type' => "text"
                        ],
                        'http_errors' => false
                    ]);
                    $request->getBody();
                }
            } elseif ($activeSmsGateway->gateway_type == "custom") {
                @send_custom_sms($reciver_number, $body, $activeSmsGateway);
            }
        } catch (\Exception $e) {
            Log::info($e);
        }
    }
}

// time format 2 hours 30 min
if (!function_exists('timeCalculation')) {
    function timeCalculation($time): string
    {
        $minutes = floor(($time / (60)) % 60);
        $hours = floor(($time / (60 * 60)));

        $hours = ($hours < 10) ? "0" . $hours : $hours;
        $minutes = ($minutes < 10) ? "0" . $minutes : $minutes;
        if ($hours == 0) {
            return $minutes . " minutes ";
        }
        return $hours . " hours " . $minutes . " minutes ";
    }
}

function spn_active_link($route_or_path, $class = 'active')
{

    if (is_array($route_or_path)) {
        foreach ($route_or_path as $route) {
            if (request()->is($route)) {
                return $class . ' a';
            }
        }
        return in_array(request()->route()->getName(), $route_or_path) ? $class : false;
    } else {
        if (request()->route()->getName() == $route_or_path) {
            return $class . ' b';
        }

        if (request()->is($route_or_path)) {
            return $class . ' c';
        }
    }

    return false;
}


function spn_nav_item_open($data, $default_class = 'active')
{
    foreach ($data as $d) {
        if (spn_active_link($d, true)) {
            return $default_class;
        }
    }

    return false;
}


if (!function_exists('addIncome')) {
    function addIncome($payment_method, $name, $amount, $fees_colection_id, $user_id, $request = null)
    {
        $payment_method = AramiscPaymentMethhod::where('method', $payment_method)->first();
        $income_head = generalSetting();

        $add_income = new AramiscAddIncome();
        $add_income->name = $name;
        $add_income->date = date('Y-m-d');
        $add_income->amount = $amount;
        $add_income->fees_collection_id = $fees_colection_id;
        $add_income->active_status = 1;
        $add_income->income_head_id = $income_head->income_head_id;
        $add_income->payment_method_id = $payment_method->id;
        $add_income->created_by = $user_id;
        $add_income->school_id = auth()->user()->school_id;
        if (moduleStatusCheck('University')) {
            $common = App::make(UnCommonRepositoryInterface::class);
            $common->storeUniversityData($add_income, $request);
            $add_income->un_academic_id = getAcademicId();
        } else {
            $add_income->academic_id = getAcademicId();
        }
        $add_income->academic_id = getAcademicId();
        $result = $add_income->save();

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('sendNotification')) {
    function sendNotification($message, $url = null, $user_id = null, $role_id = null)
    {
        $notification = new AramiscNotification;
        $notification->date = date('Y-m-d');
        $notification->message = $message;
        $notification->url = $url;
        $notification->user_id = $user_id;
        $notification->role_id = $role_id;
        $notification->school_id = Auth::user()->school_id;
        if (moduleStatusCheck('University')) {
            $notification->un_academic_id = getAcademicId();
        } else {
            $notification->academic_id = getAcademicId();
        }
        $result = $notification->save();

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}


if (!function_exists('apk_secret')) {
    function apk_secret()
    {
        return \Illuminate\Support\Facades\Storage::exists('.apk_secret') ? Storage::get('.apk_secret') : false;
    }
}

function studentFieldLabel($fields, $name)
{
    $field = $fields->where('field_name', $name)->first();
    if ($field && $field->label_name) {
        return $field->label_name;
    }

    return __('student.' . $name);
}

if (!function_exists('is_required')) {
    function is_required($field_name)
    {
        $school_id = auth()->user()->school_id;
        $fields = getStudentRegistrationFields();
        $field = $fields->where('field_name', $field_name)
            ->first();
        return $field && $field->is_required == 1;
    }
}

if (!function_exists('is_show')) {
    function is_show($field_name)
    {
        $fields = getStudentRegistrationFields();
        $field = $fields->where('field_name', $field_name)->first();
        if (moduleStatusCheck('ParentRegistration')) {
            return $field && $field->is_show == 1;
        } else {
            return true;
        }
    }
}

if (!function_exists('getStudentRegistrationFields')) {
    function getStudentRegistrationFields($school_id = null)
    {

        if (!$school_id) {
            $school_id = auth()->user()->school_id;
        }
        return Cache::rememberForever('student_field_' . $school_id, function () use ($school_id) {
            return AramiscStudentRegistrationField::where('school_id', $school_id)->get()->filter(function ($field) {
                return !$field->admin_section || isMenuAllowToShow($field->admin_section);
            });
        });
    }
}

if (!function_exists('has_permission')) {
    function has_permission($field_name)
    {

        $fields = getStudentRegistrationFields()
            ->when(auth()->user()->role_id == 2, function ($query) {
                $query->where('student_edit', 1);
            })
            ->when(auth()->user()->role_id == 3, function ($query) {
                $query->where('parent_edit', 1);
            })
            ->pluck('field_name')->toArray();

        return in_array($field_name, $fields);
    }
}

if (!function_exists('studentRecords')) {
    function studentRecords($request = null, $student_id = null, $school_id = null)
    {
        $studentRecord = StudentRecord::query()->with('classes', 'studentDetail')->where('active_status', 1);
        if ($student_id != null) {
            $studentRecord->where('student_id', $student_id);
        }
        if ($school_id != null) {
            $studentRecord->where('school_id', $school_id);
        } else {
            $studentRecord->where('school_id', auth()->user()->school_id);
        }
        if ($request != null && !moduleStatusCheck('University')) {
            $studentRecord->when($request->class, function ($query) use ($request) {
                $query->where('class_id', $request->class);
            })
                ->when($request->section, function ($query) use ($request) {
                    $query->where('section_id', $request->section);
                });
        }
        if ($request != null && moduleStatusCheck('University')) {
            $studentRecord->when($request->un_session_id, function ($q) use ($request) {
                $q->where('un_session_id', $request->un_session_id);
            })
                ->when($request->un_faculty_id, function ($q) use ($request) {
                    $q->where('un_faculty_id', $request->un_faculty_id);
                })
                ->when($request->un_department_id, function ($q) use ($request) {
                    $q->where('un_department_id', $request->un_department_id);
                })
                ->when($request->un_academic_id, function ($q) use ($request) {
                    $q->where('un_academic_id', $request->un_academic_id);
                })
                ->when($request->un_semester_id, function ($q) use ($request) {
                    $q->where('un_semester_id', $request->un_semester_id);
                })
                ->when($request->un_semester_label_id, function ($q) use ($request) {
                    $q->where('un_semester_label_id', $request->un_semester_label_id);
                });
        }
        $studentRecord = $studentRecord->when(moduleStatusCheck('University'), function ($q) {
            $q->where('un_academic_id', getAcademicId());
        }, function ($query) {
            $query->where('academic_id', getAcademicId());
        })->where('is_promote', 0);

        return $studentRecord;
    }
}

if (!function_exists('SchoolModuleStatus')) {
    function SchoolModuleStatus($schoolmodule)
    {
        return isModuleForSchool($schoolmodule);
    }
}

if (!function_exists('universityColumns')) {
    function universityColumns($table)
    {
        $columns = [
            'un_sessions' => 'un_session_id',
            'un_faculties' => 'un_faculty_id',
            'un_departments' => 'un_department_id',
            'un_academic_years' => 'un_academic_id',
            'un_semesters' => 'un_semester_id',
            'un_semester_labels' => 'un_semester_label_id',
        ];
        foreach ($columns as $key => $column) {
            if (!Schema::hasColumn($table, $column)) {
                $table->unsignedBigInteger($column)->nullable();
            }
            if (Schema::hasTable($key)) {
                $table->foreign($column)->on($key)->references('id')->cascadeOnDelete();
            }
        }
    }
}

if (!function_exists('labelWiseStudentResult')) {
    function labelWiseStudentResult($studentRecord, $subject_id, $examTerm = null)
    {
        // $subejcts = [1,2,3,4,5];
        // $data = [];
        // foreach($subejcts as $subject) {
        //     $data[$subject]= [
        //         'result' => '',
        //         'total_mark' =>''
        //     ];
        // }
        // $assingSubjects = $studentRecord->unStudentSubjects->pluck('un_subject_id')->toArray();
        $marks = AramiscMarkStore::withOutGlobalScope(AcademicSchoolScope::class)->where('student_record_id', $studentRecord->id)
            ->where('un_semester_label_id', $studentRecord->un_semester_label_id)
            ->where('un_academic_id', $studentRecord->un_academic_id)
            // ->where('un_subject_id', $subject_id)
            ->where('school_id', auth()->user()->school_id);

        $exit = $marks->get();

        $data = [];
        $data['exit'] = $exit;
        $data['passSubject'] = [];
        $data['total_mark'] = null;
        $data['result'] = 'not taken';
        if (count($exit) > 0) {
            $data['result'] = 'fail';
            $settings = CustomResultSetting::where('school_id', $studentRecord->school_id)
                ->where('un_academic_id', $studentRecord->un_academic_id)
                ->whereNotIn('exam_type_id', [0])
                ->get();
            $subjectPassMark = Modules\University\Entities\UnSubject::where('id', $subject_id)
                ->where('school_id', $studentRecord->school_id)
                ->value('pass_mark');

            if (!$subjectPassMark) {
                $data['result'] = 'pass';
                $data['passSubject'] = [$subject_id];
                return $data;
            }

            if ($settings) {
                $total_mark = 0;
                foreach ($settings as $setting) {
                    $mark = $marks->where('exam_term_id', $setting->exam_type_id)->value('total_marks');
                    $total_mark += ($mark * $setting->exam_percentage) / 100;
                }

                if ($total_mark >= $subjectPassMark) {
                    $data['result'] = 'pass';
                    $data['passSubject'] = [$subject_id];
                }
                $data['total_mark'] = $total_mark;
            } else {
                $totalSubjectMark = $marks->count('total_marks');
                if ($totalSubjectMark >= $subjectPassMark) {
                    $data['result'] = 'pass';
                    $data['passSubject'] = [$subject_id];
                }
                $data['total_mark'] = $totalSubjectMark;
            }
        }
        return $data;
    }
}


const WEEK_DAYS = [
    3 => 1,
    4 => 2,
    5 => 3,
    6 => 4,
    7 => 5,
    1 => 6,
    2 => 0,
];

const WEEK_DAYS_BY_NAME = [
    'Saturday' => 6,
    'Sunday' => 0,
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
];


const PERMITTED_MODULE = [
    //keep it all lower case.
    'lead', 'lms', 'university', 'alumni'
];


if (!function_exists('directFees')) {
    function directFees()
    {
        if (generalSetting()->direct_fees_assign) {
            return true;
        } else {
            return false;
        }
    }
}


if (!function_exists('discountFees')) {
    function discountFees($installment_id)
    {
        $amount = 0;
        $installment = DirectFeesInstallmentAssign::find($installment_id);
        if ($installment) {
            $amount = $installment->amount - $installment->discount_amount;
        }

        return $amount;
    }
}

if (!function_exists('discount_fees')) {
    function discount_fees($amount, $discount = 0)
    {
        if ($discount) {
            return ($amount - $discount);
        } else {
            return $amount;
        }
    }
}


if (!function_exists('aramisc_fees_invoice')) {
    function aramisc_fees_invoice($invoice, $setting)
    {
        $number = (($setting->start_form + $invoice) - 1);
        $format = $setting->prefix . "-" . $number;

        $key = [
            'prefix',
            'start_form',
        ];

        $value = [
            $setting->prefix,
            $setting->start_form
        ];
        return str_replace($key, $value, $format);
    }
}


if (!function_exists('smFeesInvoice')) {
    function smFeesInvoice($invoice)
    {
        $settings = FeesInvoice::where('school_id', auth()->user()->school_id)->first();

        $number = (($settings->start_form + $invoice) - 1);
        $format = $settings->prefix . "-" . $number;

        $key = [
            'prefix',
            'start_form',
        ];

        $value = [
            $settings->prefix,
            $settings->start_form
        ];
        return str_replace($key, $value, $format);
    }
}

if (!function_exists('fees_payment_status')) {
    function fees_payment_status($amount, $discount = 0, $paid_amo = 0, $status = null)
    {
        $balance_fees = ($amount - $discount) - ($paid_amo);
        if (moduleStatusCheck('University')) {
            if ($status == 1 && $balance_fees == 0) {
                $paid = __('fees.paid');
                return [$paid, 'bg-success'];
            } elseif ($status == 2 || ($paid_amo > 0)) {
                $partial = __('fees.partial');
                return [$partial, 'bg-warning'];
            } else {
                $unpaid = __('fees.unpaid');
                return [$unpaid, 'bg-danger'];
            }
        } else {
            if ($status == 1 && $balance_fees == 0) {
                $paid = __('fees.paid');
                return [$paid, 'bg-success'];
            } elseif ($status == 2 || ($paid_amo > 0)) {
                $partial = __('fees.partial');
                return [$partial, 'bg-warning'];
            } else {
                $unpaid = __('fees.unpaid');
                return [$unpaid, 'bg-danger'];
            }
        }
    }
}

if (!function_exists('feesPaymentStatus')) {
    function feesPaymentStatus($installment_id)
    {
        if (moduleStatusCheck('University')) {
            $feesInstallment = UnFeesInstallmentAssign::find($installment_id);
            $balance_fees = $balance_fees = discountFeesAmount($feesInstallment->id) - ($feesInstallment->paid_amount);
            if ($feesInstallment->active_status == 1 && $balance_fees == 0) {
                $paid = __('fees.paid');
                return [$paid, 'bg-success'];
            } elseif ($feesInstallment->active_status == 2 || ($feesInstallment->paid_amount > 0)) {
                $partial = __('fees.partial');
                return [$partial, 'bg-warning'];
            } else {
                $unpaid = __('fees.unpaid');
                return [$unpaid, 'bg-danger'];
            }
        } else {
            $feesInstallment = DirectFeesInstallmentAssign::find($installment_id);
            $balance_fees = $balance_fees = discount_fees($feesInstallment->amount, $feesInstallment->discount_amount) - ($feesInstallment->paid_amount);
            if ($feesInstallment->active_status == 1 && $balance_fees == 0) {
                $paid = __('fees.paid');
                return [$paid, 'bg-success'];
            } elseif ($feesInstallment->active_status == 2 || ($feesInstallment->paid_amount > 0)) {
                $partial = __('fees.partial');
                return [$partial, 'bg-warning'];
            } else {
                $unpaid = __('fees.unpaid');
                return [$unpaid, 'bg-danger'];
            }
        }
    }
}


if (!function_exists('universityFeesInvoice')) {
    function universityFeesInvoice($invoice)
    {
        $settings = FeesInvoice::where('school_id', auth()->user()->school_id)
            ->first();

        $number = $settings->start_form + $invoice;
        $format = $settings->prefix . "-" . $number;

        $key = [
            'prefix',
            'start_form',
        ];

        $value = [
            $settings->prefix,
            $settings->start_form
        ];
        return str_replace($key, $value, $format);
    }
}


if (!function_exists('aramiscPaymentRemainder')) {
    function aramiscPaymentRemainder($school_id = null)
    {
        $today = date('Y-m-d');

        if (!$school_id) {
            $school_id = auth()->user()->school_id;
        }
        $notificationData = DirectFeesReminder::where('school_id', $school_id)
            ->first();
        $notificationType = json_decode($notificationData->notification_types);

        $dueDate = Carbon::parse($today)->addDays($notificationData->due_date_before)->format('Y-m-d');


        $feesDues = DirectFeesInstallmentAssign::where('school_id', $school_id)
            ->where('active_status', '!=', 1)
            ->where('due_date', $dueDate)
            ->get();

        foreach ($feesDues as $feesDue) {
            if (in_array('system', $notificationType)) {
                $message = 'Fees Remainder';
                $user_id = @$feesDue->recordDetail->student->user_id;
                $role_id = @$feesDue->recordDetail->student->role_id;
                sendNotification($message, '', $user_id, $role_id);
            }

            if (in_array('email', $notificationType)) {
                $reciver_email = @$feesDue->recordDetail->student->email;
                $receiver_name = @$feesDue->recordDetail->student->full_name;
                $purpose = 'university_fees_remainder';

                $data['student_name'] = @$feesDue->recordDetail->student->full_name;
                $data['class'] = @$feesDue->recordDetail->class->class_name;
                $data['section'] = @$feesDue->recordDetail->section->section_name;
                $data['semester_label'] = @$feesDue->recordDetail->unSemesterLabel->name;
                $data['academic'] = @$feesDue->recordDetail->academic->name;
                $data['fees_type'] = @$feesDue->feesType->name;
                $data['amount'] = $feesDue->amount;
                $data['due_date'] = dateConvert($feesDue->due_date);
                send_mail($reciver_email, $receiver_name, $purpose, $data);
            }

            if (in_array('sms', $notificationType)) {
                $reciver_number = @$feesDue->recordDetail->student->mobile;
                $purpose = 'university_fees_remainder';
                $data['student_name'] = @$feesDue->recordDetail->student->full_name;
                $data['class'] = @$feesDue->recordDetail->class->class_name;
                $data['section'] = @$feesDue->recordDetail->section->section_name;
                $data['semester_label'] = @$feesDue->recordDetail->unSemesterLabel->name;
                $data['academic'] = @$feesDue->recordDetail->academic->name;
                $data['fees_type'] = @$feesDue->feesType->name;
                $data['amount'] = $feesDue->amount;
                $data['due_date'] = dateConvert($feesDue->due_date);
                send_sms($reciver_number, $purpose, $data);
            }
            return true;
        }
    }
}

if (!function_exists('singleSubjectMark')) {
    function singleSubjectMark($record_id, $subject_id, $exam_id, $exam_rule = null)
    {
        try {
            $mark = 0;
            $full_mark = 100;

            if (moduleStatusCheck('University')) {
                $aramisc_mark = AramiscResultStore::where('student_record_id', $record_id)->where('un_subject_id', $subject_id)->where('exam_type_id', $exam_id)->first();
                if ($aramisc_mark) {
                    $full_mark = AramiscExam::where('exam_type_id', $exam_id)->where('un_subject_id', $subject_id)->where('un_semester_label_id', $aramisc_mark->un_semester_label_id)->where('un_section_id', $aramisc_mark->un_section_id)->first('exam_mark');
                }
            } else {
                $aramisc_mark = AramiscResultStore::where('student_record_id', $record_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam_id)->first();
                if ($aramisc_mark) {
                    $full_mark = AramiscExam::where('exam_type_id', $exam_id)->where('subject_id', $subject_id)->where('class_id', $aramisc_mark->class_id)->where('section_id', $aramisc_mark->section_id)->first('exam_mark');
                }
            }

            if (is_null($exam_rule)) {
                $mark = ($aramisc_mark->total_marks * 100) / $full_mark->exam_mark;
            } else {
                $mark = $aramisc_mark->total_marks;
            }
            return [$mark];
        } catch (\Exception $e) {
            return [0];
        }
    }
}

if (!function_exists('subjectAverageMark')) {
    function subjectAverageMark($record_id, $subject_id)
    {
        try {
            $total_mark = 0;
            $grade = "";
            if (moduleStatusCheck('University')) {
                $result_setting = CustomResultSetting::where('un_academic_id', getAcademicId())->where('school_id', Auth()->user()->school_id)->get();
            } else {
                $result_setting = CustomResultSetting::where('academic_id', getAcademicId())->where('school_id', Auth()->user()->school_id)->get();
            }

            if ($result_setting) {
                foreach ($result_setting as $exam) {
                    $mark = AramiscResultStore::query();
                    $mark->where('student_record_id', $record_id)->where('exam_type_id', $exam->exam_type_id);
                    if (moduleStatusCheck('University')) {
                        $mark = $mark->where('un_subject_id', $subject_id);
                    } else {
                        $mark = $mark->where('subject_id', $subject_id);
                    }
                    $mark = $mark->first();

                    if ($mark) {
                        $full_mark = AramiscExam::query();
                        $full_mark->where('exam_type_id', $mark->exam_type_id);
                        if (moduleStatusCheck('University')) {
                            $full_mark = $full_mark->where('un_subject_id', $subject_id)
                                ->where('un_semester_label_id', $mark->un_semester_label_id)
                                ->where('un_section_id', $mark->un_section_id);
                        } else {
                            $full_mark->where('subject_id', $subject_id)
                                ->where('class_id', $mark->class_id)
                                ->where('section_id', $mark->section_id);
                        }
                        $full_mark = $full_mark->first('exam_mark');
                        $total_mark += ((($mark->total_marks * 100) / $full_mark->exam_mark) * ($exam->exam_percentage / 100));
                    }
                }
            } else {
                foreach (examTypes() as $exam) {
                    $mark = AramiscResultStore::query();
                    $mark->where('student_record_id', $record_id)->where('exam_type_id', $exam->id);
                    if (moduleStatusCheck('University')) {
                        $mark = $mark->where('un_subject_id', $subject_id);
                    } else {
                        $mark = $mark->where('subject_id', $subject_id);
                    }
                    $mark = $mark->first();

                    if ($mark) {
                        $full_mark = AramiscExam::query();
                        $full_mark->where('exam_type_id', $mark->exam_type_id);
                        if (moduleStatusCheck('University')) {
                            $full_mark = $full_mark->where('un_subject_id', $subject_id)
                                ->where('un_semester_label_id', $mark->un_semester_label_id)
                                ->where('un_section_id', $mark->un_section_id);
                        } else {
                            $full_mark->where('subject_id', $subject_id)
                                ->where('class_id', $mark->class_id)
                                ->where('section_id', $mark->section_id);
                        }
                        $full_mark = $full_mark->first('exam_mark');
                        $total_mark += $mark->total_marks;
                    }
                }
            }
            $total_mark = number_format($total_mark, 2);
            return [$total_mark];
        } catch (\Exception $e) {
            return [0];
        }
    }
}

if (!function_exists('allSubjectAverageMark')) {
    function allSubjectAverageMark($record_id, $subject_id)
    {
        try {
            $exam_rules = CustomResultSetting::where('school_id', Auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();
            $total_mark = 0;
            $grade = "";
            if (!is_null($exam_rules))
                foreach ($exam_rules as $exam) {
                    $mark = AramiscResultStore::where('student_record_id', $record_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam->exam_type_id)->first();
                    if ($mark) {
                        $full_mark = AramiscExam::where('exam_type_id', $mark->exam_type_id)->where('subject_id', $subject_id)->where('class_id', $mark->class_id)->where('section_id', $mark->section_id)->first('exam_mark');
                        $total_mark += ((($mark->total_marks * 100) / $full_mark->exam_mark) * ($exam->exam_percentage / 100));
                    }
                }
            $total_mark = number_format($total_mark, 2);
            return [$total_mark];
        } catch (\Exception $e) {
            return [0];
        }
    }


    if (!function_exists('allExamSubjectMark')) {
        function allExamSubjectMark($record_id, $exam_rule_id, $exam_rule = true)
        {
            try {
                $avg_marks = 0;
                $studentRecord = StudentRecord::find($record_id);

                if ($exam_rule) {
                    $exam_rule = CustomResultSetting::find($exam_rule_id);
                    if ($exam_rule) {
                        $result = AramiscResultStore::where('student_record_id', $record_id)
                            ->where('exam_type_id', $exam_rule->exam_type_id)
                            ->where('academic_id', getAcademicId())->get();
                        if ($result->count()) {
                            $total_marks = $result->sum('total_marks');
                            $avg_marks = ($total_marks / count($result)) * ($exam_rule->exam_percentage / 100);
                        }
                    }
                } else {
                    $result = AramiscResultStore::where('student_record_id', $record_id)
                        ->where('exam_type_id', $exam_rule_id)
                        ->where('academic_id', getAcademicId())->get();
                    if ($result->count()) {
                        $total_marks = $result->sum('total_marks');
                        $avg_marks = $total_marks / count($result);
                    }
                }
                $avg_marks = number_format($avg_marks, 2);
                return [$avg_marks];
            } catch (\Exception $e) {
                return [0];
            }
        }
    }

    if (!function_exists('allExamSubjectMarkAverage')) {
        function allExamSubjectMarkAverage($record_id, $all_subject_ids)
        {
            try {
                $total_avg = 0;
                if (moduleStatusCheck('University')) {
                    $exam_rules = CustomResultSetting::where('un_academic_id', getAcademicId())->where('school_id', Auth()->user()->school_id)
                        ->where('un_academic_id', getAcademicId())
                        ->get();
                } else {
                    $exam_rules = CustomResultSetting::where('academic_id', getAcademicId())->where('school_id', Auth()->user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->get();
                }

                if (count($exam_rules)) {
                    foreach ($all_subject_ids as $subject_id) {
                        foreach ($exam_rules as $exam) {
                            if (moduleStatusCheck('University')) {
                                $mark = AramiscResultStore::where('student_record_id', $record_id)->where('un_subject_id', $subject_id)->where('exam_type_id', $exam->exam_type_id)->first();
                                $full_mark = AramiscExam::where('exam_type_id', $mark->exam_type_id)->where('un_subject_id', $subject_id)->where('class_id', $mark->class_id)->where('un_section_id', $mark->un_section_id)->first('exam_mark');
                            } else {
                                $mark = AramiscResultStore::where('student_record_id', $record_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam->exam_type_id)->first();
                                $full_mark = AramiscExam::where('exam_type_id', $mark->exam_type_id)->where('subject_id', $subject_id)->where('class_id', $mark->class_id)->where('section_id', $mark->section_id)->first('exam_mark');
                            }

                            if ($mark) {
                                $total_avg += ((($mark->total_marks * 100) / $full_mark->exam_mark) * ($exam->exam_percentage / 100));
                            }
                        }
                    }
                } else {
                    foreach ($all_subject_ids as $subject_id) {
                        foreach (examTypes() as $exam) {
                            if (moduleStatusCheck('University')) {
                                $mark = AramiscResultStore::where('student_record_id', $record_id)->where('un_subject_id', $subject_id)->where('exam_type_id', $exam->id)->first();
                            } else {
                                $mark = AramiscResultStore::where('student_record_id', $record_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam->id)->first();
                            }

                            if ($mark) {
                                if (moduleStatusCheck('University')) {
                                    $full_mark = AramiscExam::where('exam_type_id', $mark->id)->where('un_subject_id', $subject_id)->where('un_semester_label_id', $mark->un_semester_label_id)->where('un_section_id', $mark->un_section_id)->first('exam_mark');
                                } else {
                                    $full_mark = AramiscExam::where('exam_type_id', $mark->id)->where('subject_id', $subject_id)->where('class_id', $mark->class_id)->where('section_id', $mark->section_id)->first('exam_mark');
                                }

                                $total_avg += $mark->total_marks;
                            }
                        }
                    }
                }

                $average = $total_avg / count($all_subject_ids);
                return number_format($average, 2);
            } catch (\Exception $e) {
                return 0;
            }
        }
    }


    if (!function_exists('avgSubjectPassMark')) {
        function avgSubjectPassMark($all_subject_ids)
        {
            try {
                $pass_mark = 0;
                $subjects = AramiscSubject::whereIn('id', $all_subject_ids)->get();
                if (count($subjects)) {

                    $pass_mark = $subjects->sum('pass_mark') / count($subjects);
                }
                return number_format($pass_mark, 2);
            } catch (\Exception $e) {
                return 0;
            }
        }
    }
}


if (!function_exists('SaasSchool')) {
    function SaasSchool()
    {
        $request = request();
        $domain = $request->subdomain;
        $host = $request->getHttpHost();
        $school = null;
        $short_url = preg_replace('#^https?://#', '', rtrim(env('APP_URL', 'http://localhost'), '/'));
        if (!$domain) {
            $domain = str_replace('.' . $short_url, '', $host);
        }

        if ($domain == $host) {
            $domain = null;
        }

        $saas_module = 'Modules/Saas/Providers/SaasServiceProvider.php';
        if (file_exists($saas_module)) {
            $module_status = json_decode(file_get_contents('modules_statuses.json'), true);
            if (isset($module_status['Saas']) && $module_status['Saas']) {

                if ($domain) {
                    $school = \App\AramiscSchool::where(['domain' => $domain, 'active_status' => 1])->firstOrFail();
                    $request->route()->forgetParameter('subdomain');
                } else if ($host == $short_url) {
                    $school = \App\AramiscSchool::findOrFail(1);
                } else if ($host != $short_url and config('app.allow_custom_domain')) {
                    $school = \App\AramiscSchool::where(['custom_domain' => $host, 'active_status' => 1])->firstOrFail();
                } elseif (Auth::check()) {
                    $school = \App\AramiscSchool::findOrFail(Auth::user()->school_id);
                } else {
                    $school = \App\AramiscSchool::where(['domain' => $domain, 'active_status' => 1])->firstOrFail();
                    $request->route()->forgetParameter('subdomain');
                }
            }
        }

        if (!$school) {
            $school = Auth::check() ? auth()->user()->school : \App\AramiscSchool::findOrFail(1);
        }
        if (app()->bound('school')) {
            return app('school');
        } else {
            app()->forgetInstance('school');
            app()->instance('school', $school);
        }
        return $school;
    }
}

if (!function_exists('SaasDomain')) {
    function SaasDomain()
    {
        return SaasSchool()->domain;
    }
}

if (!function_exists('saasEnv')) {
    function saasEnv($value, $default = null)
    {

        try {

            $domain = SaasDomain();
            $settings_prefix = Str::lower(str_replace(' ', '_', $domain));
            $path = storage_path('app/chat/' . $settings_prefix . '_settings.json');
            if (!file_exists($path)) {
                copy(storage_path('app/chat/default_settings.json'), $path);
            }


            $data = json_decode(file_get_contents($path), true);

            $settings = [];
            if (!empty($data)) {
                foreach ($data as $key => $property) {
                    $settings[$key] = $property;
                }
            }

            $env = $settings[$value] ?? '';
        } catch (\Throwable $th) {
            $env = null;
        }

        if (empty($env)) {
            $env = $default;
        }
        return $env;
    }
}

if (!function_exists('examTypes')) {
    function examTypes()
    {
        try {
            return AramiscExamType::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->where('active_status', 1)
                ->get();
        } catch (\Throwable $th) {
            return [];
        }
    }
}

if (!function_exists('allExamsSubjectTotalMark')) {
    function allExamsSubjectTotalMark($subject_id)
    {
        try {
            $toal_mark = 0;
            foreach (examTypes() as $exam) {
                $toal_mark += subjectFullMark($exam->id, $subject_id);
            }
            return $toal_mark;
        } catch (\Throwable $th) {
            return 100;
        }
    }
}

if (!function_exists('send_custom_sms')) {
    function send_custom_sms($reciver_number, $message, $active_gateway = null)
    {
        if (!$active_gateway) {
            $school_id = Auth::check() && saasSettings('sms_settings') ? Auth::user()->school_id : 1;
            $active_gateway = AramiscSmsGateway::where('active_status', 1)->where('school_id', $school_id)->first();
        }
        if (!$active_gateway) {
            return;
        }

        $sms_settings = CustomSmsSetting::where('gateway_id', $active_gateway->id)->first();

        $response = false;
        if (empty($sms_settings->gateway_url)) {
            Toastr::info(__('common.set_sms_credentials'), __('common.info'));
            return $response;
        }
        $request_data = [
            $sms_settings->send_to_parameter_name => $reciver_number,
            $sms_settings->messege_to_parameter_name => $message,
        ];


        if (!empty($sms_settings->param_key_1)) {
            $request_data[$sms_settings->param_key_1] = $sms_settings->param_value_1;
        }
        if (!empty($sms_settings->param_key_2)) {
            $request_data[$sms_settings->param_key_2] = $sms_settings->param_value_2;
        }
        if (!empty($sms_settings->param_key_3)) {
            $request_data[$sms_settings->param_key_3] = $sms_settings->param_value_3;
        }
        if (!empty($sms_settings->param_key_4)) {
            $request_data[$sms_settings->param_key_4] = $sms_settings->param_value_4;
        }
        if (!empty($sms_settings->param_key_5)) {
            $request_data[$sms_settings->param_key_5] = $sms_settings->param_value_5;
        }
        if (!empty($sms_settings->param_key_6)) {
            $request_data[$sms_settings->param_key_6] = $sms_settings->param_value_6;
        }
        if (!empty($sms_settings->param_key_7)) {
            $request_data[$sms_settings->param_key_7] = $sms_settings->param_value_7;
        }
        if (!empty($sms_settings->param_key_8)) {
            $request_data[$sms_settings->param_key_8] = $sms_settings->param_value_8;
        }

        $params = [];

        $user_name = array_search('username', $sms_settings->toArray());
        $password = array_search('password', $sms_settings->toArray());

        if ($user_name && $password && $sms_settings->set_auth == "header") {
            $params['auth'] = [
                $request_data[$sms_settings->$user_name],
                $request_data[$sms_settings->$password],
            ];
            unset($request_data['username']);
            unset($request_data['password']);
        }


        if (array_key_exists("csms_id", $request_data)) {
            $request_data->csms_id = date('dmY');
        }

        $params['form_params'] = $request_data;

        $client = new \GuzzleHttp\Client();
        $method = strtolower($sms_settings->request_method);

        if ($method == 'get') {
            $response = $client->$method($sms_settings->gateway_url . '?' . http_build_query($request_data));
        } else {
            $response = $client->$method($sms_settings->gateway_url, $params);
        }

        return $response;
    }
}

if (!function_exists('total_no_records')) {
    function total_no_records($class_id, $section_id = null)
    {
        try {
            $records = StudentRecord::query();
            $records->where('class_id', $class_id)->where('is_promote', 0);
            if ($section_id) {
                $records->where('section_id', $section_id);
            }
            return $records->whereHas('student')->count();
        } catch (\Throwable $th) {
            return 0;
        }
    }
}


if (!function_exists('isSkip')) {
    function isSkip($name)
    {
        $data = \App\Models\ExamStepSkip::where('name', $name)->where('school_id', auth()->user()->school_id)->first();
        if ($data) {
            return true;
        }
        return false;
    }
}

if (!function_exists('resultPrintStatus')) {
    function resultPrintStatus($data)
    {
        try {
            $printSettings = CustomResultSetting::first();
            if ($data == 'image') {
                if ($printSettings->profile_image == $data) {
                    return true;
                } else {
                    return false;
                }
            } elseif ($data == 'header') {
                if ($printSettings->header_background == $data) {
                    return true;
                } else {
                    return false;
                }
            } elseif ($data == 'body') {
                if ($printSettings->body_background == $data) {
                    return true;
                } else {
                    return false;
                }
            } elseif ($data == 'vertical_boarder') {
                if ($printSettings->vertical_boarder == $data) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists('getStudentMeritPosition')) {
    function getStudentMeritPosition($class_id, $section_id, $exam_term_id, $record_id)
    {
        try {
            $position = ExamMeritPosition::withOutGlobalScopes()->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('exam_term_id', $exam_term_id)
                ->where('record_id', $record_id)
                ->first();
            if ($position) {
                return $position->position;
            } else {
                return '';
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists('getStudentAllExamMeritPosition')) {
    function getStudentAllExamMeritPosition($class_id, $section_id, $record_id)
    {
        try {
            $position = AllExamWisePosition::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('record_id', $record_id)
                ->first();
            if ($position) {
                return $position->position;
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists('gpaResult')) {
    function gpaResult($gpa)
    {
        $mark = AramiscMarksGrade::where('gpa', floor($gpa))->first();
        if ($mark) {
            return $mark;
        } else {
            return null;
        }
    }
}


if (!function_exists('getGlobalExamBySecClsSub')) {
    function getGlobalExamBySecClsSub($section_id, $class_id, $subject_id)
    {
        $globalExams = AramiscExam::withoutGlobalScope(AcademicSchoolScope::class)
            ->withoutGlobalScope(GlobalAcademicScope::class)
            ->where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->where('section_id', $section_id)
            ->with('GetGlobalExamTitle')
            ->get();
        if ($globalExams) {
            return $globalExams;
        } else {
            return [];
        }
    }
}
if (!function_exists('db_engine')) {
    function db_engine()
    {
        try {
            return \DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (\Exception $e) {
            return 'mysql';
        }
    }
}

if (!function_exists('examReportSignatures')) {
    function examReportSignatures()
    {
        return AramiscExamSignature::where('active_status', 1)->get(['title', 'signature']);
    }
}

if (!function_exists('send_notification')) {
    function send_notification($event, $user_id, $data)
    {
        $notification = AramiscNotificationSetting::where('event', $event)->where('school_id', auth()->user()->school_id)->first();
        $user = User::find($user_id);
        $all_recivers = $notification->recipient;
        $reciver = "";
        $active_recivers = [];
        $active_dest = [];
        $body = "";
        if ($user->role_id == 1) {
            $reciver = 'Admin';
        } elseif ($user->role_id == 2) {
            $reciver = 'Student';
        } elseif ($user->role_id == 3) {
            $reciver = 'Parent';
        } elseif ($user->role_id == 4) {
            $reciver = 'Teacher';
        }

        foreach ($all_recivers as $key => $value) {
            if ($value == 1) {
                $active_recivers[] = $key;
            }
        }

        if (in_array($reciver, $active_recivers)) {
            $destinations = $notification->destination;

            foreach ($destinations as $via => $value) {
                if ($value == 1) {
                    $active_dest[] = $via;
                }
            }

            if (in_array('Email', $active_dest)) {
                $body = short_code_messege($notification->template[$reciver]['Email'], $data);
                $view = view('backEnd.email.emailBody', compact('body'));
                $message = (string)$view;
                $headers = "From: <$user->email> \r\n";
                $headers .= "Reply-To: $user->full_name <$user->email> \r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                @mail($user->email, $event, $message, $headers);
            }

            if (in_array('SMS', $active_dest)) {
                $sms_body = short_code_messege($notification->template[$reciver]['SMS'], $data);
            }

            if (in_array('Web', $active_dest)) {
                $web_body = short_code_messege($notification->template[$reciver]['Web'], $data);

                $notification = new AramiscNotification;
                $notification->user_id = $user->id;
                $notification->role_id = $user->role_id;
                $notification->school_id = $user->school_id;
                $notification->academic_id = getAcademicId();
                $notification->date = date('Y-m-d');
                $notification->message = $web_body;
                $notification->save();
            }
            if (in_array('App', $active_dest)) {
            }
        }


        return AramiscExamSignature::where('active_status', 1)->get(['title', 'signature']);
    }
}

if (!function_exists('short_code_messege')) {
    function short_code_messege($templete, $data)
    {
        $templete = str_replace('[class]', @$data['class'], $templete);
        $templete = str_replace('[section]', @$data['section'], $templete);
        $templete = str_replace('[teacher_name]', @$data['teacher_name'], $templete);
        $templete = str_replace('[admin_name]', @$user->full_name, $templete);
        $templete = str_replace('[student_name]', gv($data, 'student_name', @$user->full_name), $templete);
        return $templete;
    }
}

if (!function_exists('feesCarryForward')) {
    function feesCarryForward($studentRecordId, $feesType, $payableAmount, $sub_total)
    {
        $carryForward = AramiscFeesCarryForward::where('student_id', $studentRecordId)->first();
        if (!$carryForward) {
            return;
        }
        $settings = FeesCarryForwardSettings::first();

        if (Carbon::now()->format('Y-m-d') <= $carryForward->due_date) {
            $totalPayableAmount = 0;
            foreach ($payableAmount as $amount) {
                $totalPayableAmount += $amount;
            }
            if ($carryForward->balance_type == 'due' && $carryForward->balance > 0) {
                $dueBalance = $carryForward->balance;
                if ($carryForward) {
                    $fees_type = new FmFeesType();
                    $fees_type->type = "fees_carry";
                    $fees_type->name = $carryForward->notes ? $carryForward->notes : $settings->title;
                    $fees_type->school_id = auth()->user()->school_id;
                    $fees_type->academic_id = getAcademicId();
                    $fees_type->save();
                }
                $data['feesTypes'] = array_merge($feesType, [(int)$fees_type->id]);
                $data['amount'] = array_merge($payableAmount, [(int)$dueBalance]);
                $data['sub_total'] = array_merge($sub_total, [(int)$dueBalance]);
                $data['type'] = 'due';

                $updateCarry = AramiscFeesCarryForward::where('student_id', $studentRecordId)->first();
                $updateCarry->balance = NULL;
                $updateCarry->balance_type = 'add';
                $updateCarry->update();

                carryForwardLog($studentRecordId, $dueBalance, 'due', 'Fees Payment', 'fees');
            } else {
                if ($totalPayableAmount <= $carryForward->balance) {
                    $addBalance = $carryForward->balance - $totalPayableAmount;

                    $updateCarry = AramiscFeesCarryForward::where('student_id', $studentRecordId)->first();
                    $updateCarry->balance = $addBalance;
                    $updateCarry->balance_type = 'add';
                    $updateCarry->update();

                    carryForwardLog($studentRecordId, $totalPayableAmount, 'due', 'Fees Payment Added', 'fees');
                    carryForwardLog($studentRecordId, $addBalance, 'add', 'Fees Payment and Carry Ballance Added', 'fees');

                    $data['paymentAmount'] = $payableAmount;
                    $data['type'] = 'full_paid_add_xtra_amount';
                } else {
                    $cAmount = $carryForward->balance;
                    $paidFeesType = [];
                    $paidFeesAmount = [];
                    foreach ($feesType as $key => $type) {
                        $paidFeesType[$key] = $type;
                        if ($cAmount) {
                            $pAmount = $payableAmount[$key] * 1;
                            if ($cAmount >= $pAmount) {
                                $paidFeesAmount[$key] = $pAmount;
                                $cAmount -= $pAmount;
                            } elseif ($cAmount < $pAmount) {
                                $paidFeesAmount[$key] = $cAmount;
                                $cAmount = 0;
                            } else {
                                $paidFeesAmount[$key] = 0;
                            }
                        }
                    }
                    $updateCarry = AramiscFeesCarryForward::where('student_id', $studentRecordId)->first();
                    $updateCarry->balance = NULL;
                    $updateCarry->balance_type = 'add';
                    $updateCarry->update();

                    carryForwardLog($studentRecordId, $cAmount, 'due', 'Fees Payment', 'fees');

                    $data['paidFeesType'] = $paidFeesType;
                    $data['paidFeesAmount'] = $paidFeesAmount;
                    $data['type'] = 'multi_payment';
                }
            }
            $data['paymentMethod'] = $settings->payment_gateway;
            return $data;
        } else {
            return;
        }
    }
}

if (!function_exists('carryForwardLog')) {
    function carryForwardLog($record_id, $amount, $amount_type, $note, $type)
    {
        $storeLog = new FeesCarryForwardLog();
        $storeLog->student_record_id = $record_id;
        $storeLog->amount = $amount;
        $storeLog->amount_type = $amount_type;
        $storeLog->date = date("Y-m-d H:i:s");
        $storeLog->note = $note;
        $storeLog->type = $type;
        $storeLog->created_by = auth()->user()->id;
        $storeLog->school_id = auth()->user()->school_id;
        $storeLog->save();
        return true;
    }
}

if (!function_exists('averagePassingMark')) {
    function averagePassingMark($exam_type_id)
    {
        $examType = AramiscExamType::find($exam_type_id);
        if ($examType && $examType->is_average == 1) {
            return $examType->average_mark;
        }
        return null;
    }
}

if (!function_exists('inAppLiveClassJoinAndClose')) {
    function inAppLiveClassJoinAndClose($classMeeting)
    {
        $currentDayStatus = Carbon::now();
        $currentTime = $currentDayStatus->format('g:i A');
        $givenTime = Carbon::parse($classMeeting->time)->addMinutes((int)$classMeeting->duration)->format('g:i A');
        if ($currentDayStatus->format('Y-m-d') <= Carbon::parse($classMeeting->date)->format('Y-m-d') && is_null($classMeeting->end_at)) {
            if ($currentTime >= Carbon::parse($classMeeting->time)->format('g:i A')) {
                if ($currentDayStatus->isBetween($currentTime, $givenTime)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('storeCalendarInfo')) {
    function storeCalendarInfo(
        $title,
        $description,
        $date,
        $created_by,
        $record_id = null,
        $role_id = null,
        $staff_id = null,
        $parent_id = null,
        $class_id = null,
        $section_id = null,
        $url = null

    )
    {
        $storeData = new SmCalendar();
        $storeData->record_id = $record_id;
        $storeData->role_id = $role_id;
        $storeData->staff_id = $staff_id;
        $storeData->parent_id = $parent_id;
        $storeData->class_id = $class_id;
        $storeData->section_id = $section_id;
        $storeData->title = $title;
        $storeData->description = $description;
        $storeData->url = $url;
        $storeData->date = $date;
        $storeData->created_by = $created_by;
        $storeData->school_id = auth()->user()->school_id;
        $storeData->academic_id = getAcademicId();
        $storeData->save();
    }
}
if (!function_exists('getYoutubeName')) {
    function getYoutubeName($link)
    {
        $name = explode('</title>', explode('<title>', file_get_contents($link))[1])[0];
        return $name;
    }
}
if (!function_exists('youtubeVideoLinkValidation')) {
    function youtubeVideoLinkValidation($link)
    {
        $youtubeVideoLinkValidation = preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]{11})/", $link);
        return $youtubeVideoLinkValidation;
    }
}
if (!function_exists('generateRandomString')) {
    function generateRandomString($length)
    {
        $validChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';
        $validCharsLen = strlen($validChars);
        $str = '';
        $i = 0;
        while ($i++ < $length) {
            $str .= $validChars[random_int(0, $validCharsLen - 1)];
        }
        return $str;
    }
}
if (!function_exists('getSubjectAttendance')) {
    function getSubjectAttendance($record)
    {
        $subjectAttendance = AramiscSubjectAttendance::with('student')
            ->whereIn('academic_id', $record->pluck('academic_id'))
            ->whereIn('student_record_id', $record->pluck('id'))
            ->whereIn('school_id', $record->pluck('school_id'))
            ->get();
        return $subjectAttendance;
    }
}

if (!function_exists('activeTheme')) {
    function activeTheme()
    {
        try {
            return generalSetting()->active_theme;
        } catch (\Exception $e) {
            return null;
        }
    }
}
if (!function_exists('userLocal')) {
    function userLocal()
    {
        try {
            $user = auth()->user();
            if (isset($user->language)) {
                $user_lang = $user->language;
            } else {
                $user_lang = App::getLocale();
            }
            return $user_lang;
        } catch (\Throwable $th) {
            return 'en';
        }
    }
}
if (!function_exists('_translation')) {
    function _translation($key)
    {
        $trans = trans($key);
        try {
            $exp = explode('.', $trans);
            if (count($exp) == 2) {
                $txt = Str::replace('_', ' ', ucfirst($exp[1]));
                $txt = ucfirst($txt);
            } else {
                $txt = $trans;
                $txt = Str::replace('_', ' ', ucfirst($txt));
                $txt = ucfirst($txt);
            }
            return $txt;
        } catch (\Throwable $th) {
            return $key;
        }
    }
}


if (!function_exists('_trans')) {
    function _trans($value)
    {

        try {
            if (env('APP_ENV') == 'production') {
                return trans($value);
            } else {

                $local = userLocal() ? userLocal() : app()->getLocale();

                $langPath = resource_path('lang/' . $local . '/');

                if (!file_exists($langPath)) {
                    mkdir($langPath, 0777, true);
                }

                if (str_contains($value, '.')) {
                    $new_trns = explode('.', $value);
                    $file_name = $new_trns[0];
                    // $trans_key = $new_trns[1];
                    $trans_key = str_replace($file_name . '.', '', $value);


                    $file_path = $langPath . '' . $file_name . '.php';
                    if (file_exists($file_path)) {
                        $file_content = include($file_path);

                        if (array_key_exists($trans_key, $file_content)) {
                            return _translation($value);
                        } else {
                            $file_content[$trans_key] = $trans_key;
                            $str = <<<EOT
                                            <?php
                                                return [
                                            EOT;
                            foreach ($file_content as $key => $val) {
                                if (gettype($val) == 'string') {

                                    $line = <<<EOT
                                                                    "{$key}" => "{$val}",\n
                                                                EOT;
                                }
                                if (gettype($val) == 'array') {
                                    $line = <<<EOT
                                                                            "{$key}" => [\n
                                                                        EOT;
                                    $str .= $line;
                                    foreach ($val as $lang_key => $lang_val) {

                                        $line = <<<EOT
                                                                            "{$lang_key}" => "{$lang_val}",\n
                                                                        EOT;

                                        $str .= $line;
                                    }

                                    $line = <<<EOT
                                                                        ],\n
                                                                    EOT;
                                }

                                $str .= $line;
                            }
                            $end = <<<EOT
                                                    ]
                                            ?>
                                            EOT;
                            $str .= $end;

                            file_put_contents($file_path, $str, $flags = 0, $context = null);
                        }
                    } else {

                        fopen($file_path, 'w');
                        $file_content = [];
                        $file_content[$trans_key] = $trans_key;
                        $str = <<<EOT
                                            <?php
                                                return [
                                            EOT;
                        foreach ($file_content as $key => $val) {
                            if (gettype($val) == 'string') {

                                $line = <<<EOT
                                                                    "{$key}" => "{$val}",\n
                                                                EOT;
                            }
                            if (gettype($val) == 'array') {
                                $line = <<<EOT
                                                                            "{$key}" => [\n
                                                                        EOT;
                                $str .= $line;
                                foreach ($val as $lang_key => $lang_val) {

                                    $line = <<<EOT
                                                                            "{$lang_key}" => "{$lang_val}",\n
                                                                        EOT;

                                    $str .= $line;
                                }

                                $line = <<<EOT
                                                                        ],\n
                                                                    EOT;
                            }

                            $str .= $line;
                        }
                        $end = <<<EOT
                                                    ]
                                            ?>
                                            EOT;
                        $str .= $end;

                        file_put_contents($file_path, $str, $flags = 0, $context = null);
                    }

                    return _translation($value);
                } else {

                    $trans_key = $value;
                    $file_path = resource_path('lang/' . $local . '/' . $local . '.php');

                    fopen($file_path, 'w');
                    $file_content = [];
                    $file_content[$trans_key] = $trans_key;
                    $str = <<<EOT
                                            <?php
                                                return [
                                            EOT;
                    foreach ($file_content as $key => $val) {
                        if (gettype($val) == 'string') {

                            $line = <<<EOT
                                                                    "{$key}" => "{$val}",\n
                                                                EOT;
                        }
                        if (gettype($val) == 'array') {
                            $line = <<<EOT
                                                                            "{$key}" => [\n
                                                                        EOT;
                            $str .= $line;
                            foreach ($val as $lang_key => $lang_val) {

                                $line = <<<EOT
                                                                            "{$lang_key}" => "{$lang_val}",\n
                                                                        EOT;

                                $str .= $line;
                            }

                            $line = <<<EOT
                                                                        ],\n
                                                                    EOT;
                        }

                        $str .= $line;
                    }
                    $end = <<<EOT
                                                    ]
                                            ?>
                                            EOT;
                    $str .= $end;

                    file_put_contents($file_path, $str, $flags = 0, $context = null);
                    return _translation($value);
                }
                return _translation($value);
            }
        } catch (Exception $exception) {
            dd($exception);
            return $value;
        }
    }
}

if (!function_exists('defaultLogo')) {
    function defaultLogo($path)
    {
        if ($path && file_exists($path)) {
            return asset($path);
        } else {
            return asset('public/uploads/settings/logo.png');
        }
    }
}

if (!function_exists('defaultUserLogo')) {
    function defaultUserLogo($path)
    {
        if ($path && file_exists($path)) {
            return asset($path);
        } else {
            return asset('public/uploads/staff/demo/staff.jpg');
        }
    }
}

if (!function_exists('latterAvater')) {
    function latterAvater($string)
    {
        $words = explode(" ", $string);
        if (count($words) > 1) {
            $output = substr($words[0], 0, 1) . substr($words[1], 0, 1);
        } else {
            $first = substr($string, 0, 1);
            $last = substr($string, -1);
            $output = $first . $last;
        }
        return $output;
    }
}

if (!function_exists('getProfileImage')) {
    function getProfileImage($user_id)
    {
        $user = User::find($user_id);
        $role_id = $user->role_id;
        $student = AramiscStudent::where('user_id', $user_id)->first();
        $parent = AramiscParent::where('user_id', $user_id)->first();
        $staff = AramiscStaff::where('user_id', $user_id)->first();
        if ($role_id == 2) {
            $profile = $student->student_photo ? $student->student_photo : 'public/backEnd/assets/img/avatar.png';
        } elseif ($role_id == 3) {
            $profile = $parent->fathers_photo ? $parent->fathers_photo : 'c';
        } else {
            $profile = $staff->staff_photo ? $staff->staff_photo : 'public/backEnd/assets/img/avatar.png';
        }
        return $profile;
    }
}

function headerContent()
{
    $headerPageData = Page::where('school_id', app('school')->id)->where('name', 'header_menu')
        ->select('id', 'name', 'title', 'description', 'slug', 'settings', 'status')
        ->first();
    if ($headerPageData) {
        return view('pagebuilder::components.header-footer-page-components', ['page' => $headerPageData]);
    }
}

function footerContent()
{
    $footerPage = Page::where('school_id', app('school')->id)->where('name', 'footer_menu')
        ->select('id', 'name', 'title', 'description', 'slug', 'settings', 'status')
        ->first();
    if ($footerPage) {
        return view('pagebuilder::components.header-footer-page-components', ['page' => $footerPage]);
    }
}

function formatedDate($date)
{
    return date('Y-m-d', strtotime($date));
}

function formatedDateformat($date,$format='Y-m-d')
{
    return date($format, strtotime($date));
}


if (!function_exists('envu')) {
    function envu($data = array())
    {
        foreach ($data as $key => $value) {
            if (env($key) === $value) {
                unset($data[$key]);
            }
        }

        if (!count($data)) {
            return false;
        }

        // write only if there is change in content

        $env = file_get_contents(base_path() . '/.env');
        $env = explode("\n", $env);
        foreach ((array)$data as $key => $value) {
            foreach ($env as $env_key => $env_value) {
                $entry = explode("=", $env_value, 2);
                if ($entry[0] === $key) {
                    $env[$env_key] = $key . "=" . (is_string($value) ? '"' . $value . '"' : $value);
                } else {
                    $env[$env_key] = $env_value;
                }
            }
        }
        $env = implode("\n", $env);
        file_put_contents(base_path() . '/.env', $env);
        return true;
    }
}
if (!function_exists('lastOneMonthDates')) {
    function lastOneMonthDates()
    {
        $days_ago = [];
        for ($i = 30; $i >= 1; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days', strtotime(date('Y-m-d'))));
            array_push($days_ago, $day);
        }
        return $days_ago;
    }
}
if (!function_exists('insertMenuManage')) {
    function insertMenuManage($menu)
    {
        $menuData = AramiscHeaderMenuManager::create($menu);
        if (gv($menu, 'childs')) {
            foreach (gv($menu, 'childs') as $child) {
                $child['parent_id'] = $menuData->id;
                insertMenuManage($child);
            }
        }
    }
}
if (!function_exists('asset_path')) {
    function asset_path($path = null)
    {
        return 'public/' . $path;
    }
}
if (!function_exists('isTestMode')) {
    function isTestMode()
    {
        if (env('APP_MODE') == 'test') {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('isConnected')) {
    function isConnected()
    {
        $connected = @fsockopen("www.google.com", 80);
        if ($connected) {
            fclose($connected);
            return true;
        }

        return false;
    }
}

if (!function_exists('curlIt')) {

    function curlIt($url, $postData = array())
    {
        $url = preg_replace("/\r|\n/", "", $url);
        try {
            $response = Http::timeout(3)->acceptJson()->get($url);
            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            \Log::error($e);
        }
        return [
            'goto' => $url . '&from=browser'
        ];
    }
}

if (!function_exists('gv')) {

    function gv($params, $key, $default = null)
    {
        return (isset($params[$key]) && $params[$key]) ? $params[$key] : $default;
    }
}

if (!function_exists('gbv')) {
    function gbv($params, $key)
    {
        return (isset($params[$key]) && $params[$key]) ? 1 : 0;
    }
}

if (!function_exists('active_link')) {
    function active_link($route_or_path, $class = 'active')
    {
        if (request()->route()->getName() == $route_or_path) {
            return $class;
        }

        if (request()->is($route_or_path)) {
            return $class;
        }
        return false;
    }
}

if (!function_exists('active_progress_bar')) {
    function active_progress_bar($route_or_path_arr, $class = 'active')
    {
        return in_array(request()->route()->getName(), $route_or_path_arr) ? $class : false;
    }
}

if (!function_exists('nav_item_open')) {
    function nav_item_open($data, $index, $default_class = 'nav-item-open')
    {
        return in_array($index, $data) ? $default_class : false;
    }
}


if (!function_exists('app_url')) {
    function app_url()
    {
        $saas = config('spondonit.saas_module_name', 'Saas');
        $module_check_function = config('spondonit.module_status_check_function', 'moduleStatusCheck');
        if (function_exists($module_check_function) && $module_check_function($saas)) {
            return config('app.url');
        }
        return url('/');
    }
}

if (!function_exists('bytesToSize')) {
    function bytesToSize($size, $precision = 2)
    {
        $size = is_numeric($size) ? $size : 0;

        $base = log($size, 1024);
        $suffixes = array('Bytes', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}


if (!function_exists('verifyUrl')) {
    function verifyUrl($verifier = 'auth')
    {
        if ($verifier == 'auth') {
            $url = config('app.verifier');
        } else {
            $url = config('app.ux_verifier');
        }

        return $url;
    }
}
/*
function moduleVerify($file, $type = null)
{

    $filename = pathinfo($file, PATHINFO_FILENAME);

    $repo = App::make(InstallRepository::class);

    $params = [
        'name' => $filename,
        'item_id' => config('app.item'),
        'envatouser' => Storage::exists('.account_email') ? Storage::get('.account_email') : null,
        'tariq' => true,
    ];

    if ($type) {
        $params += [
            $type => true,
        ];
    }

    return $repo->installModule($params);
}*/