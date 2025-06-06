<?php

namespace App\Http\Controllers;

use App\User;
use App\AramiscClass;
use App\AramiscRoute;
use App\AramiscStaff;
use App\AramiscParent;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscVehicle;
use App\AramiscExamType;
use App\AramiscRoomList;
use App\AramiscBaseSetup;
use App\AramiscTemplate;
use App\AramiscFeesAssign;
use App\AramiscMarksGrade;
use App\AramiscAcademicYear;
use App\AramiscClassSection;
use App\AramiscClassTeacher;
use App\AramiscEmailSetting;
use App\AramiscExamSchedule;
use App\AramiscLeaveRequest;
use App\AramiscStudentGroup;
use App\AramiscAssignSubject;
use App\AramiscAssignVehicle;
use App\AramiscDormitoryList;
use App\AramiscLibraryMember;
use App\AramiscGeneralSettings;
use App\AramiscStudentCategory;
use App\AramiscStudentDocument;
use App\AramiscStudentTimeline;
use App\AramiscStudentPromotion;
use App\AramiscStudentAttendance;
use Illuminate\Http\Request;
use App\Models\AramiscCustomField;
use App\Models\StudentRecord;
use App\AramiscFeesAssignDiscount;
use App\StudentBulkTemporary;
use Illuminate\Support\Carbon;
use App\Imports\StudentsImport;
use App\AramiscClassOptionalSubject;
use App\Events\StudentPromotion;
use App\AramiscOptionalSubjectAssign;
use App\Traits\NotificationSend;
use App\Exports\AllStudentExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Events\StudentPromotionGroupDisable;

class AramiscStudentAdmissionController extends Controller
{
    use NotificationSend;
    private $User;
    private $AramiscGeneralSettings;
    private $AramiscUserLog;
    private $AramiscModuleManager;
    private $URL;

    public function __construct()
    {
        $this->middleware('PM');
    }

    function admissionCheck($val)
    {
        $data = DB::table('aramisc_students')->where('admission_no', $val)->where('school_id', Auth::user()->school_id)->first();
        if (!is_null($data)) {
            $msg = 'found';
            $status = 200;
            return response()->json($msg, $status);
        } else {
            $msg = 'not_found';
            $status = 200;
            return response()->json($msg, $status);
        }
    }
    function admissionCheckUpdate($val, $id)
    {
        $data = DB::table('aramisc_students')->where('admission_no', $val)->where('school_id', Auth::user()->school_id)->first();

        $student = AramiscStudent::find($id);

        if (!is_null($data) && $student->admission_no != $data->admission_no) {
            $msg = 'found';
            $status = 200;
            return response()->json($msg, $status);
        } else {
            $msg = 'not_found';
            $status = 200;
            return response()->json($msg, $status);
        }
    }

    public function admission()
    {

        try {
            if (isSubscriptionEnabled()) {

                $active_student = AramiscStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

                if (\Modules\Saas\Entities\SmPackagePlan::student_limit() <= $active_student) {

                    Toastr::error('Your student limit has been crossed.', 'Failed');
                    return redirect()->back();
                }
            }


            $max_admission_id = AramiscStudent::where('school_id', Auth::user()->school_id)->max('admission_no');
            $max_roll_id = AramiscStudent::where('school_id', Auth::user()->school_id)->max('roll_no');

            $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $religions = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '2')->where('school_id', Auth::user()->school_id)->get();
            $blood_groups = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '3')->where('school_id', Auth::user()->school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $route_lists = AramiscRoute::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $vehicles = AramiscVehicle::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $driver_lists = AramiscStaff::where([['active_status', '=', '1'], ['role_id', 9]])->where('school_id', Auth::user()->school_id)->get();
            $dormitory_lists = AramiscDormitoryList::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $categories = AramiscStudentCategory::where('school_id', Auth::user()->school_id)->get();
            $groups = AramiscStudentGroup::where('school_id', Auth::user()->school_id)->get();
            $sessions = AramiscAcademicYear::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();

            $custom_fields = AramiscCustomField::where('form_name', 'student_registration')->get();

            return view('backEnd.studentInformation.student_admission', compact('classes', 'religions', 'blood_groups', 'genders', 'route_lists', 'vehicles', 'dormitory_lists', 'categories', 'groups', 'sessions', 'max_admission_id', 'max_roll_id', 'driver_lists', 'custom_fields'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



    public function ajaxSectionStudent(Request $request)
    {
        try {
            if (teacherAccess()) {
                $sectionIds = AramiscAssignSubject::where('class_id', '=', $request->id)
                    ->where('teacher_id', Auth::user()->staff->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            } else {
                $sectionIds = AramiscClassSection::where('class_id', '=', $request->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }

            $sections = [];
            foreach ($sectionIds as $sectionId) {
                $sections[] = AramiscSection::where('id', $sectionId->section_id)->select('id', 'section_name')->first();
            }
            return response()->json([$sections]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }


    public function ajaxSubjectClass(Request $request)
    {
        try {
            if (teacherAccess()) {
                $subjectIds = AramiscAssignSubject::where('class_id', '=', $request->id)
                    ->where('teacher_id', Auth::user()->staff->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->distinct('subject_id')
                    ->get();
            } else {
                $subjectIds = AramiscAssignSubject::where('class_id', '=', $request->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->distinct('subject_id')
                    ->get();
            }

            $subjects = [];
            foreach ($subjectIds as $subjectId) {
                $subjects[] = AramiscSubject::where('id', $subjectId->subject_id)->select('id', 'subject_name')->first();
            }
            return response()->json([$subjects]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    function studentUpdatePic(Request $r, $id)
    {
        // try {
        $validator = Validator::make($r->all(), [
            'logo_pic' => 'sometimes|required|mimes:jpg,png|max:40000',
            'fathers_photo' => 'sometimes|required|mimes:jpg,png|max:40000',
            'mothers_photo' => 'sometimes|required|mimes:jpg,png|max:40000',
            'guardians_photo' => 'sometimes|required|mimes:jpg,png|max:40000',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'error'], 201);
        }
        try {
            $data = AramiscStudent::find($id);
            $data_parent = $data->parents;
            if ($r->hasFile('logo_pic')) {

                $file = $r->file('logo_pic');
                $images = Image::make($file)->insert($file);
                $pathImage = 'public/uploads/student/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    Session::put('student_photo', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('student_photo')) || file_exists($data->student_photo)) {
                        File::delete($data->student_photo);
                        File::delete(Session::get('student_photo'));
                    }
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    Session::put('student_photo', $imageName);
                }
                $data->student_photo = $imageName;
                $data->save();
            }
            // parent
            if ($r->hasFile('fathers_photo')) {
                $file = $r->file('fathers_photo');
                $images = Image::make($file)->insert($file);
                $pathImage = 'public/uploads/student/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->staff_photo =  $imageName;
                    Session::put('fathers_photo', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('fathers_photo')) || file_exists($data_parent->fathers_photo)) {
                        File::delete(Session::get('fathers_photo'));
                        File::delete($data_parent->fathers_photo);
                    }
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->fathers_photo =  $imageName;
                    Session::put('fathers_photo', $imageName);
                }
                $data_parent->fathers_photo = session()->get('fathers_photo');
                $data_parent->save();
            }
            //mother
            if ($r->hasFile('mothers_photo')) {
                $file = $r->file('mothers_photo');
                $images = Image::make($file)->insert($file);
                $pathImage = 'public/uploads/student/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->staff_photo =  $imageName;
                    Session::put('mothers_photo', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('mothers_photo')) || file_exists($data_parent->mothers_photo)) {
                        File::delete(Session::get('mothers_photo'));
                        File::delete($data_parent->mothers_photo);
                    }
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->mothers_photo =  $imageName;
                    Session::put('mothers_photo', $imageName);
                }
                $data_parent->mothers_photo = session()->get('mothers_photo');
                $data_parent->save();
            }
            // guardians_photo
            if ($r->hasFile('guardians_photo')) {
                $file = $r->file('guardians_photo');
                $images = Image::make($file)->insert($file);
                $pathImage = 'public/uploads/student/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->staff_photo =  $imageName;
                    Session::put('guardians_photo', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('guardians_photo')) || file_exists($data_parent->guardians_photo)) {
                        File::delete(Session::get('guardians_photo'));
                        File::delete($data_parent->guardians_photo);
                    }
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->guardians_photo =  $imageName;
                    Session::put('guardians_photo', $imageName);
                }
                $data_parent->guardians_photo = session()->get('guardians_photo');
                $data_parent->save();
            }

            return response()->json('success', 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'error'], 201);
        }
    }


    public function academicYearGetClass(Request $request)
    {
        try {
            $academic_year = AramiscAcademicYear::select('id')->where('school_id', Auth::user()->school_id)->where('id', $request->id)->first();

            $classes = AramiscClass::where('active_status', '=', '1')
                ->where('academic_id', $academic_year->id)
                ->where('school_id', Auth::user()->school_id)
                ->withoutGlobalScope(StatusAcademicSchoolScope::class)
                ->get(['class_name', 'id']);


            return response()->json([$classes]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxSectionSibling(Request $request)
    {
        try {
            $sectionIds = AramiscClassSection::where('class_id', '=', $request->id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $sibling_sections = [];
            foreach ($sectionIds as $sectionId) {
                $sibling_sections[] = AramiscSection::find($sectionId->section_id);
            }
            return response()->json([$sibling_sections]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }
    public function ajaxSiblingInfo(Request $request)
    {
        try {
            if ($request->id == "") {
                $siblings = AramiscStudent::where('class_id', '=', $request->class_id)->where('section_id', '=', $request->section_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            } else {
                $siblings = AramiscStudent::where('class_id', '=', $request->class_id)->where('section_id', '=', $request->section_id)->where('active_status', 1)->where('id', '!=', $request->id)->where('school_id', Auth::user()->school_id)->get();
            }
            return response()->json($siblings);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxSiblingInfoDetail(Request $request)
    {
        try {
            $sibling_detail = AramiscStudent::find($request->id);
            $parent_detail =  $sibling_detail->parents;
            return response()->json([$sibling_detail, $parent_detail]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxGetVehicle(Request $request)
    {

        try {
            $vehicle_detail = AramiscAssignVehicle::where('route_id', $request->id)->first();
            $vehicles = explode(',', $vehicle_detail->vehicle_id);
            $vehicle_info = [];
            foreach ($vehicles as $vehicle) {
                $vehicle_info[] = AramiscVehicle::find($vehicle[0]);
            }
            return response()->json([$vehicle_info]);
        } catch (\Exception $e) {

            return response()->json("", 404);
        }
    }

    public function ajaxVehicleInfo(Request $request)
    {
        try {
            $vehivle_detail = AramiscVehicle::find($request->id);
            return response()->json([$vehivle_detail]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxRoomDetails(Request $request)
    {
        try {
            $room_details = AramiscRoomList::where('dormitory_id', '=', $request->id)->where('school_id', Auth::user()->school_id)->get();
            $rest_rooms = [];
            foreach ($room_details as $room_detail) {
                $count_room = AramiscStudent::where('room_id', $room_detail->id)->count();
                if ($count_room < $room_detail->number_of_bed) {
                    $rest_rooms[] = $room_detail;
                }
            }
            return response()->json([$rest_rooms]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxGetRollId(Request $request)
    {

        try {
            $max_roll = AramiscStudent::where('class_id', $request->class)
                ->where('section_id', $request->section)
                ->where('school_id', Auth::user()->school_id)
                ->max('roll_no');
            // return $max_roll;
            if ($max_roll == "") {
                $max_roll = 1;
            } else {
                $max_roll = $max_roll + 1;
            }
            return response()->json([$max_roll]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function studentStore(Request $request)
    {
        #dd($request->all());
        // custom field validation start
        $validator = Validator::make($request->all(), $this->generateValidateRules("student_registration"));
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                Toastr::error(str_replace('custom f.', '', $error), 'Failed');
            }
            return redirect()->back()->withInput();
        }
        // custom field validation End


        // if ($request->parent_id == "") {
        //     $request->validate(
        //         [   'email_address' => 'sometimes|email|nullable|unique:users,email',
        //             'admission_number' => 'required',
        //             'roll_number' => 'required',
        //             'class' => 'required',
        //             'section' => 'required',
        //             'session' => 'required',
        //             'gender' => 'required',
        //             'first_name' => 'required|max:100',
        //             'date_of_birth' => 'required',
        //             'guardians_email' => "required",
        //             'document_file_1' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        //             'document_file_2' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        //             'document_file_3' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        //             'document_file_4' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        //         ],
        //         [
        //             'session.required' => 'Academic year field is required.'
        //         ]
        //     );
        // } else {
        //     $request->validate(
        //         [  'email_address' => 'sometimes|email|nullable|unique:users,email',
        //             'admission_number' => 'required',
        //             'roll_number' => 'required',
        //             'class' => 'required',
        //             'section' => 'required',
        //             'gender' => 'required',
        //             'first_name' => 'required|max:100',
        //             'date_of_birth' => 'required',
        //             'session' => 'required',
        //             'document_file_1' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        //             'document_file_2' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        //             'document_file_3' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        //             'document_file_4' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        //         ],
        //         [
        //             'section.required' => 'Academic year field is required.'
        //         ]
        //     );
        // }

        $is_duplicate = AramiscStudent::where('school_id', Auth::user()->school_id)
            ->where('admission_no', $request->admission_number)
            ->first();

        if ($is_duplicate) {
            Toastr::error('Duplicate admission number found!', 'Failed');
            return redirect()->back()->withInput();
        }

        if ($request->guardians_phone != "") {
            $is_duplicate = AramiscParent::where('school_id', Auth::user()->school_id)
                ->where('guardians_mobile', $request->guardians_phone)
                ->first();

            if ($is_duplicate) {

                Toastr::error('Duplicate guardian mobile number found!', 'Failed');
                return redirect()->back()->withInput();
            }
        }

        $document_file_1 = "";
        if ($request->file('document_file_1') != "") {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('document_file_1');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $file = $request->file('document_file_1');
            $document_file_1 = 'doc1-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
            $file->move('public/uploads/student/document/', $document_file_1);
            $document_file_1 =  'public/uploads/student/document/' . $document_file_1;
        }

        $document_file_2 = "";
        if ($request->file('document_file_2') != "") {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('document_file_2');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $file = $request->file('document_file_2');
            $document_file_2 = 'doc2-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
            $file->move('public/uploads/student/document/', $document_file_2);
            $document_file_2 =  'public/uploads/student/document/' . $document_file_2;
        }

        $document_file_3 = "";
        if ($request->file('document_file_3') != "") {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('document_file_3');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $file = $request->file('document_file_3');
            $document_file_3 = 'doc3-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
            $file->move('public/uploads/student/document/', $document_file_3);
            $document_file_3 =  'public/uploads/student/document/' . $document_file_3;
        }

        $document_file_4 = "";
        if ($request->file('document_file_4') != "") {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('document_file_4');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $file = $request->file('document_file_4');
            $document_file_4 = 'doc4-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
            $file->move('public/uploads/student/document/', $document_file_4);
            $document_file_4 =  'public/uploads/student/document/' . $document_file_4;
        }

        if ($request->relation == 'Father') {
            $guardians_photo = "";
            if ($request->file('fathers_photo') != "") {
                $guardians_photo =  Session::get('fathers_photo');
            }
        } elseif ($request->relation == 'Mother') {
            $guardians_photo = "";
            if ($request->file('mothers_photo') != "") {
                $guardians_photo =  Session::get('mothers_photo');
            }
        } elseif ($request->relation == 'Other') {
            $guardians_photo = "";
            if ($request->file('guardians_photo') != "") {
                $guardians_photo =  Session::get('guardians_photo');
            }
        }

        // $get_admission_number = AramiscStudent::where('school_id',Auth::user()->school_id)->max('admission_no') + 1;

        $shcool_details = AramiscGeneralSettings::find(1);
        $school_name = explode(' ', $shcool_details->school_name);
        $short_form = '';
        foreach ($school_name as $value) {
            $ch = str_split($value);
            $short_form = $short_form . '' . $ch[0];
        }
        DB::beginTransaction();

        try {
            $academic_year = AramiscAcademicYear::find($request->session);
            $user_stu = new User();
            $user_stu->role_id = 2;
            $user_stu->full_name = $request->first_name . ' ' . $request->last_name;
            $user_stu->username = $request->admission_number;
            $user_stu->email = $request->email_address;
            $user_stu->password = Hash::make(123456);
            $user_stu->school_id = Auth::user()->school_id;
            $user_stu->created_at = $academic_year->year . '-01-01 12:00:00';
            $user_stu->save();
            $user_stu->toArray();

            try {
                if ($request->parent_id == "") {
                    $user_parent = new User();
                    $user_parent->role_id = 3;
                    $user_parent->full_name = $request->fathers_name;
                    if (!empty($request->guardians_email)) {
                        $data_parent['email'] = $request->guardians_email;
                        $user_parent->username = $request->guardians_email;
                    }
                    $user_parent->email = $request->guardians_email;
                    $user_parent->password = Hash::make(123456);
                    $user_parent->school_id = Auth::user()->school_id;
                    $user_parent->created_at = $academic_year->year . '-01-01 12:00:00';
                    $user_parent->save();
                    $user_parent->toArray();
                }
                try {
                    if ($request->parent_id == "") {
                        $parent = new AramiscParent();
                        $parent->user_id = $user_parent->id;
                        $parent->fathers_name = $request->fathers_name;
                        $parent->fathers_mobile = $request->fathers_phone;
                        $parent->fathers_occupation = $request->fathers_occupation;
                        if (Session::get('fathers_photo') != "") {
                            $parent->fathers_photo = Session::get('fathers_photo');
                        }
                        $parent->mothers_name = $request->mothers_name;
                        $parent->mothers_mobile = $request->mothers_phone;
                        $parent->mothers_occupation = $request->mothers_occupation;
                        if (Session::get('mothers_photo') != "") {
                            $parent->mothers_photo = Session::get('mothers_photo');
                        }
                        $parent->guardians_name = $request->guardians_name;
                        $parent->guardians_mobile = $request->guardians_phone;
                        $parent->guardians_email = $request->guardians_email;
                        $parent->guardians_occupation = $request->guardians_occupation;
                        $parent->guardians_relation = $request->relation;
                        $parent->relation = $request->relationButton;
                        if ($guardians_photo != "") {
                            $parent->guardians_photo = $guardians_photo;
                        }
                        $parent->guardians_address = $request->guardians_address;
                        $parent->is_guardian = $request->is_guardian;
                        $parent->school_id = Auth::user()->school_id;
                        $parent->academic_id = $request->session;
                        $parent->created_at = $academic_year->year . '-01-01 12:00:00';
                        $parent->save();
                        $parent->toArray();
                    }

                    try {
                        $student = new AramiscStudent();
                        //$student->siblings_id = $request->sibling_id;
                        $student->class_id = $request->class;
                        $student->section_id = $request->section;
                        $student->session_id = $request->session;
                        $student->user_id = $user_stu->id;
                        if ($request->parent_id == "") {
                            $student->parent_id = $parent->id;
                        } else {
                            $student->parent_id = $request->parent_id;
                        }
                        $student->role_id = 2;
                        $student->admission_no = $request->admission_number;
                        $student->roll_no = $request->roll_number;
                        $student->first_name = $request->first_name;
                        $student->last_name = $request->last_name;
                        $student->full_name = $request->first_name . ' ' . $request->last_name;
                        $student->gender_id = $request->gender;
                        $student->date_of_birth = date('Y-m-d', strtotime($request->date_of_birth));
                        $student->caste = $request->caste;
                        $student->email = $request->email_address;
                        $student->mobile = $request->phone_number;
                        $student->admission_date = date('Y-m-d', strtotime($request->admission_date));
                        if (Session::get('student_photo') != "") {
                            $student->student_photo = Session::get('student_photo');
                        }
                        if (@$request->blood_group != "") {
                            $student->bloodgroup_id = $request->blood_group;
                        }
                        if (@$request->religion != "") {
                            $student->religion_id = $request->religion;
                        }
                        $student->height = $request->height;
                        $student->weight = $request->weight;
                        $student->current_address = $request->current_address;
                        $student->permanent_address = $request->permanent_address;
                        if (@$request->route != "") {
                            $student->route_list_id = $request->route;
                        }
                        if (@$request->dormitory_name != "") {
                            $student->dormitory_id = $request->dormitory_name;
                        }
                        if (@$request->room_number != "") {
                            $student->room_id = $request->room_number;
                        }
                        //$driver_id=AramiscVehicle::where('id','=',$request->vehicle)->first();
                        if (!empty($request->vehicle)) {
                            $driver = AramiscVehicle::where('id', '=', $request->vehicle)
                                ->select('driver_id')
                                ->first();
                            if (!empty($driver)) {
                                $student->vechile_id = $request->vehicle;
                                $student->driver_id = $driver->driver_id;
                            }
                        }
                        // $student->driver_name = $request->driver_name;
                        // $student->driver_phone_no = $request->driver_phone;
                        $student->national_id_no = $request->national_id_number;
                        $student->local_id_no = $request->local_id_number;
                        $student->bank_account_no = $request->bank_account_number;
                        $student->bank_name = $request->bank_name;
                        $student->previous_school_details = $request->previous_school_details;
                        $student->aditional_notes = $request->additional_notes;
                        $student->ifsc_code = $request->ifsc_code;
                        $student->document_title_1 = $request->document_title_1;
                        $student->document_file_1 =  $document_file_1;
                        $student->document_title_2 = $request->document_title_2;
                        $student->document_file_2 =  $document_file_2;
                        $student->document_title_3 = $request->document_title_3;
                        $student->document_file_3 = $document_file_3;
                        $student->document_title_4 = $request->document_title_4;
                        $student->document_file_4 = $document_file_4;
                        $student->school_id = Auth::user()->school_id;
                        $student->academic_id = $request->session;
                        $student->student_category_id = $request->student_category_id;
                        $student->student_group_id = $request->student_group_id;
                        $student->created_at = $academic_year->year . '-01-01 12:00:00';


                        //Custom Field Start
                        if ($request->customF) {
                            $dataImage = $request->customF;
                            foreach ($dataImage as $label => $field) {
                                if (is_object($field)) {
                                    $key = "";
                                    if ($field != "") {
                                        $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                                        $file = $field;
                                        $fileSize =  filesize($file);
                                        $fileSizeKb = ($fileSize / 1000000);
                                        if ($fileSizeKb >= $maxFileSize) {
                                            Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                                            return redirect()->back();
                                        }
                                        $file = $field;
                                        $key = $file->getClientOriginalName();
                                        $file->move('public/uploads/customFields/', $key);
                                        $dataImage[$label] =  'public/uploads/customFields/' . $key;
                                    }
                                }
                            }
                            $student->custom_field_form_name = "student_registration";
                            $student->custom_field = json_encode($dataImage, true);
                        }



                        $student->save();
                        $student->toArray();



                        if ($student) {
                            $compact['user_email'] = $request->email_address;
                            $compact['slug'] = 'student';
                            $compact['id'] = $student->id;
                            @send_mail($request->email_address, $request->first_name . ' ' . $request->last_name, "student_login_credentials", $compact);
                            @send_sms($request->phone_number, 'student_admission', $compact);
                        }

                        if ($request->parent_id != "") {
                            $parent = AramiscParent::find($request->parent_id);
                        }

                        $user_info = [];
                        $emailTemplete = AramiscTemplate::where('school_id', auth()->user()->school_id)->first();
                        if ($student) {
                            $user_info[] =  array('email' => $request->email_address, 'name' => $student->full_name, 'id' => $student->id, 'slug' => 'student');
                        }

                        if ($parent) {
                            $compact['user_email'] = $parent->guardians_email;
                            $compact['slug'] = 'parent';
                            $compact['id'] = $parent->id;
                            @send_mail($parent->guardians_email, $request->fathers_name, "parent_login_credentials", $compact);
                            @send_sms($request->guardians_phone, 'student_admission_for_parent', $compact);
                        }

                        DB::commit();
                        // session null
                        Session::put('student_photo', '');
                        Session::put('fathers_photo', '');
                        Session::put('mothers_photo', '');
                        Session::put('guardians_photo', '');

                        try {
                            if (count($user_info) != 0) {
                                $systemSetting = AramiscGeneralSettings::select('school_name', 'email')
                                    ->where('school_id', Auth::user()->school_id)->first();

                                $systemEmail = AramiscEmailSetting::where('school_id', Auth::user()->school_id)->first();
                                $system_email = $systemEmail->from_email;
                                $school_name = $systemSetting->school_name;
                                $sender['system_email'] = $system_email;
                                $sender['school_name'] = $school_name;
                                try {
                                    // dispatch(new \App\Jobs\SendUserMailJob($user_info, $sender));
                                    foreach ($user_info as $info) {
                                        $compact['data'] =  $info;

                                        send_mail($info['email'], $info['name'], 'Login Details', 'backEnd.studentInformation.user_credential', $compact);
                                    }
                                } catch (\Exception $e) {

                                    Log::info($e->getMessage());
                                }
                            }
                        } catch (\Exception $e) {

                            Toastr::success('Operation successful', 'Success');
                            return redirect('student-list');
                        }
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } catch (\Exception $e) {

                        DB::rollback();
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                } catch (\Exception $e) {

                    DB::rollback();
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Exception $e) {

                DB::rollback();
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {

            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    function admissionPic(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'logo_pic' => 'sometimes|required|mimes:jpg,png|max:40000',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => 'error'], 201);
            }
            $data = new AramiscStudent();
            $data_parent = new AramiscParent();
            if ($r->hasFile('logo_pic')) {
                $file = $r->file('logo_pic');
                $images = Image::make($file)->insert($file);
                $pathImage = 'public/uploads/student/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->staff_photo =  $imageName;
                    Session::put('student_photo', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('student_photo'))) {
                        File::delete(Session::get('student_photo'));
                    }
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->student_photo =  $imageName;
                    Session::put('student_photo', $imageName);
                }
            }
            // parent
            if ($r->hasFile('fathers_photo')) {
                $file = $r->file('fathers_photo');
                $images = Image::make($file)->insert($file);
                $pathImage = 'public/uploads/student/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->staff_photo =  $imageName;
                    Session::put('fathers_photo', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('fathers_photo'))) {
                        File::delete(Session::get('fathers_photo'));
                    }
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->fathers_photo =  $imageName;
                    Session::put('fathers_photo', $imageName);
                }
            }
            //mother
            if ($r->hasFile('mothers_photo')) {
                $file = $r->file('mothers_photo');
                $images = Image::make($file)->insert($file);
                $pathImage = 'public/uploads/student/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->staff_photo =  $imageName;
                    Session::put('mothers_photo', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('mothers_photo'))) {
                        File::delete(Session::get('mothers_photo'));
                    }
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->mothers_photo =  $imageName;
                    Session::put('mothers_photo', $imageName);
                }
            }

            // guardians_photo
            if ($r->hasFile('guardians_photo')) {
                $file = $r->file('guardians_photo');
                $images = Image::make($file)->insert($file);
                $pathImage = 'public/uploads/student/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->staff_photo =  $imageName;
                    Session::put('guardians_photo', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('guardians_photo'))) {
                        File::delete(Session::get('guardians_photo'));
                    }
                    $images->save('public/uploads/student/' . $name);
                    $imageName = 'public/uploads/student/' . $name;
                    // $data->guardians_photo =  $imageName;
                    Session::put('guardians_photo', $imageName);
                }
            }
            return response()->json('success', 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'error'], 201);
        }
    }


    public function studentDetails(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $student_list = DB::table('aramisc_students')
                ->join('aramisc_classes', 'aramisc_students.class_id', '=', 'aramisc_classes.id')
                ->join('aramisc_sections', 'aramisc_students.section_id', '=', 'aramisc_sections.id')
                ->where('aramisc_students.academic_id', getAcademicId())
                ->where('aramisc_students.school_id', Auth::user()->school_id)
                ->get();

            $students = AramiscStudent::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $sessions = AramiscAcademicYear::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentInformation.student_details', compact('classes', 'sessions', 'students'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function getClassBySchool($schoolId)
    {
        return  $classes = AramiscClass::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', $schoolId)
            ->pluck('class_name', 'id');
    }

    public function studentDetailsSearch(Request $request)
    {
        $request->validate([
            'class' => 'required',
            'academic_year' => 'required',
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
            if ($request->academic_year != "") {
                $students->where('academic_id', $request->academic_year);
            }
            if ($request->name != "") {
                $students->where('full_name', 'like', '%' . $request->name . '%');
            }
            if ($request->roll_no != "") {
                $students->where('roll_no', 'like', '%' . $request->roll_no . '%');
            }

            $students = $students->with('class', 'section', 'parents', 'section', 'gender', 'category')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $sessions = AramiscAcademicYear::where('school_id', Auth::user()->school_id)->get();

            $sections = '';

            if ($request->class)
                $sections = AramiscClassSection::with('sectionName')
                    ->where('class_id', $request->class)
                    ->where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

            $class_id = $request->class;
            $name = $request->name;
            $roll_no = $request->roll_no;
            $academic = $request->academic_year;
            $section_id = $request->section;
            return view('backEnd.studentInformation.student_details', compact('students', 'sections', 'academic', 'section_id', 'classes', 'class_id', 'name', 'roll_no', 'sessions'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed ' . $e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }

    public function studentView(Request $request, $id)
    {


        try {
            if (checkAdmin()) {
                $student_detail = AramiscStudent::find($id);
            } else {
                $student_detail = AramiscStudent::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }
            $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)
                ->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('id', '!=', $student_detail->id)
                ->where('school_id', Auth::user()->school_id)->get();

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();
            $student_optional_subject = AramiscOptionalSubjectAssign::where('student_id', $student_detail->id)->where('session_id', '=', $student_detail->session_id)->first();

            $vehicle = DB::table('aramisc_vehicles')->where('id', $student_detail->vehicle_id)->first();
            // return $vehicle;
            $fees_assigneds = AramiscFeesAssign::where('student_id', $id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            //  return $fees_assigneds;
            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            // $documents = AramiscStudentDocument::where('student_staff_id', $id)->where('type', 'stu')->where('academic_id', getAcademicId())->where('school_id',Auth::user()->school_id)->get();
            $documents = AramiscStudentDocument::where('student_staff_id', $id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $timelines = AramiscStudentTimeline::where('staff_student_id', $id)->where('type', 'stu')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('school_id', Auth::user()->school_id)->get();

            $academic_year = AramiscAcademicYear::where('id', $student_detail->session_id)->first();

            $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)
                ->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('id', '!=', $student_detail->id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();

            $student_optional_subject = AramiscOptionalSubjectAssign::where('student_id', $student_detail->id)
                ->where('session_id', '=', $student_detail->session_id)
                ->first();

            $vehicle = DB::table('aramisc_vehicles')
                ->where('id', $student_detail->vehicle_id)
                ->first();

            $fees_assigneds = AramiscFeesAssign::where('student_id', $id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $documents = AramiscStudentDocument::where('student_staff_id', $id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $timelines = AramiscStudentTimeline::where('staff_student_id', $id)
                ->where('type', 'stu')->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)
                ->where('section_id', $student_detail->section_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $academic_year = AramiscAcademicYear::where('id', $student_detail->session_id)
                ->first();

            $grades = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $maxgpa = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->max('gpa');

            $failgpa = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $failgpaname = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $failgpa)
                ->first();

            if (!empty($student_detail->vechile_id)) {
                $driver_id = AramiscVehicle::where('id', '=', $student_detail->vechile_id)->first();
                $driver_info = AramiscStaff::where('id', '=', $driver_id->driver_id)->first();
            } else {
                $driver_id = '';
                $driver_info = '';
            }

            $exam_terms = AramiscExamType::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $leave_details = AramiscLeaveRequest::where('staff_id', $student_detail->user_id)
                ->where('role_id', 2)
                ->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $custom_field_data = $student_detail->custom_field;

            if (!is_null($custom_field_data)) {
                $custom_field_values = json_decode($custom_field_data);
            } else {
                $custom_field_values = NUll;
            }

            return view('backEnd.studentInformation.student_view', compact('student_detail', 'driver_info', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades', 'academic_year', 'exam_terms', 'leave_details', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'failgpaname', 'custom_field_values', 'academic_history', 'transections'));
        } catch (\Exception $e) {


            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function uploadDocument(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'photo' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
        ]);

        if ($validator->fails()) {
            Toastr::error('Invalid Upload File', 'Failed');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        }

        if (empty($request->title) || empty($request->file('photo'))) {
            Toastr::error('Invalid Data', 'Failed');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        }
        try { 
            if ($request->file('photo') != "" && $request->title != "") {

                $document_photo = "";
                if ($request->file('photo') != "") {
                    $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('photo');
                    $fileSize =  filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                        return redirect()->back();
                    }
                    $file = $request->file('photo');
                    $document_photo = 'stu-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                    $file->move('public/uploads/student/document/', $document_photo);
                    $document_photo =  'public/uploads/student/document/' . $document_photo;
                }

                $document = new AramiscStudentDocument();
                $document->title = $request->title;
                $document->student_staff_id = $request->student_id;
                $document->type = 'stu';
                $document->file = $document_photo;
                $document->school_id = Auth::user()->school_id;
                $document->academic_id = getAcademicId();
                $document->save();
            }
            Toastr::success('Document uploaded successfully', 'Success');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        }
    }

    public function deleteDocument($id)
    {
        try {
            // $document = AramiscStudentDocument::find($id);
            if (checkAdmin()) {
                $document = AramiscStudentDocument::find($id);
            } else {
                $document = AramiscStudentDocument::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }
            if ($document->file != "") {
                unlink($document->file);
            }
            $result = AramiscStudentDocument::destroy($id);
            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        }
    }
    public function deleteStudentDocument(Request $request)
    {

        try {
            $student_detail = AramiscStudent::where('id', $request->student_id)->first();

            if ($request->doc_id == 1) {
                if ($student_detail->document_file_1 != "") {
                    unlink($student_detail->document_file_1);
                }
                $student_detail->document_title_1 = null;
                $student_detail->document_file_1 = null;
            } else if ($request->doc_id == 2) {
                if ($student_detail->document_file_2 != "") {
                    unlink($student_detail->document_file_2);
                }
                $student_detail->document_title_2 = null;
                $student_detail->document_file_2 = null;
            } else if ($request->doc_id == 3) {
                if ($student_detail->document_file_3 != "") {
                    unlink($student_detail->document_file_3);
                }
                $student_detail->document_title_3 = null;
                $student_detail->document_file_3 = null;
            } else if ($request->doc_id == 4) {
                if ($student_detail->document_file_4 != "") {
                    unlink($student_detail->document_file_4);
                }
                $student_detail->document_title_4 = null;
                $student_detail->document_file_4 = null;
            }
            $student_detail->save();
            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        }
    }

    public function studentUploadDocument(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required',
                'photo' => "required|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
            ]);
            if ($request->file('photo') != "" && $request->title != "") {
                $document_photo = "";
                if ($request->file('photo') != "") {
                    $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('photo');
                    $fileSize =  filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                        return redirect()->back();
                    }
                    $file = $request->file('photo');
                    $document_photo = 'stu-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                    $file->move('public/uploads/student/document/', $document_photo);
                    $document_photo =  'public/uploads/student/document/' . $document_photo;
                }

                $document = new AramiscStudentDocument();
                $document->title = $request->title;
                $document->student_staff_id = $request->student_id;
                $document->type = 'stu';
                $document->file = $document_photo;
                $document->school_id = Auth::user()->school_id;
                $document->academic_id = getAcademicId();
                $document->save();
            }
        } catch (\Exception $e) {
            Toastr::error('Input Fields Were Empty', 'Failed');
            return redirect()->back()->with(['studentDocuments' => 'active']);
        }
        Toastr::success('Operation successful', 'Success');
        return redirect()->back()->with(['studentDocuments' => 'active']);
    }

    // timeline
    public function studentTimelineStore(Request $request)
    {
        $request->validate([
            'document_file_4' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        ]);
        if (!$request->title) {
            Toastr::warning('Title Required', 'Warning');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        }
        try {
            if ($request->title != "") {

                $document_photo = "";
                if ($request->file('document_file_4') != "") {
                    $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('document_file_4');
                    $fileSize =  filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                        return redirect()->back();
                    }
                    $file = $request->file('document_file_4');
                    $document_photo = 'stu-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                    $file->move('public/uploads/student/timeline/', $document_photo);
                    $document_photo =  'public/uploads/student/timeline/' . $document_photo;
                }

                $timeline = new AramiscStudentTimeline();
                $timeline->staff_student_id = $request->student_id;
                $timeline->type = 'stu';
                $timeline->title = $request->title;
                $timeline->date = date('Y-m-d', strtotime($request->date));
                $timeline->description = $request->description;
                if (isset($request->visible_to_student)) {
                    $timeline->visible_to_student = $request->visible_to_student;
                }
                $timeline->file = $document_photo;
                $timeline->school_id = Auth::user()->school_id;
                $timeline->academic_id = getAcademicId();
                $timeline->save();
            }
            $data['studentTimeline'] = 'active';
            $data['type'] = 'studentTimeline';
            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with($data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        }
    }

    public function deleteTimeline($id)
    {
        try {
            $document = AramiscStudentTimeline::find($id);
            if ($document->file != "") {
                unlink($document->file);
            }
            $staff_student_id = $document->staff_student_id;
            $result = AramiscStudentTimeline::destroy($id);
            return redirect()->back()->with(['studentTimeline' => 'active']);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentDelete(Request $request)
    {
        try {
            $tables = \App\tableList::getTableList('student_id', $request->id);
            try {
                $student_detail = AramiscStudent::find($request->id);
                $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                DB::beginTransaction();
                $student = AramiscStudent::find($request->id);
                $student->active_status = 0;
                $student->save();

                $student_user = User::find($student_detail->user_id);
                $student_user->active_status = 0;
                $student_user->save();
                $compact['slug'] = 'student';
                $compact['user_email'] = $student_detail->email;

                @send_sms($student_detail->phone_number, 'user_login_permission', $compact);

                $library_member = AramiscLibraryMember::where('student_staff_id', @$student_user->id)->first();

                if ($library_member != "") {
                    $library_member->active_status = 0;
                    $library_member->save();
                }

                if (count($siblings) == 1) {
                    $parent = AramiscParent::find($student_detail->parent_id);
                    if ($parent) {
                        $parent->active_status = 0;
                        $parent->save();
                    }

                    $compact['slug'] = 'parent';
                    $compact['user_email'] = @$parent->guardians_email;
                    @send_sms($parent->guardians_mobile, 'user_login_permission', $compact);
                }

                $student_user = User::find($student_detail->user_id);
                $student_user->active_status = 0;
                $student_user->save();

                if (count($siblings) == 1) {
                    $parent_user = User::find($student_detail->parents->user_id);
                    if ($parent_user) {
                        $parent_user->active_status = 0;
                        $parent_user->save();
                    }
                }

                $class_teacher = AramiscClassTeacher::whereHas('teacherClass', function ($q) use ($student_detail) {
                    $q->where('active_status', 1)
                        ->where('class_id', $student_detail->studentRecord->class_id)
                        ->where('section_id', $student_detail->studentRecord->section_id);
                })
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', auth()->user()->school_id)
                    ->first();
                $data['class_id'] = $request->class;
                $data['section_id'] = $request->section;
                if ($class_teacher != null) {
                    $data['teacher_name'] = $class_teacher->teacher->full_name;
                    $this->sent_notifications('Enable/Disable_Student', [$class_teacher->teacher->user_id], $data, ['Teacher']);
                }
                $this->sent_notifications('Enable/Disable_Student', [$student_detail->user_id], $data, ['Student', 'Parent']);

                DB::commit();
                if ($student_detail) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            } catch (\Exception $e) {
                DB::rollback();
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }






    public function SearchMultipleSection(Request $request)
    {
        $sectionIds = AramiscClassSection::where('class_id', '=', $request->id)->where('school_id', Auth::user()->school_id)->get();
        return response()->json([$sectionIds]);
    }



    public function studentPromoteCustomStore(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'promote_session' => 'required',
            'promote_class' => 'required',
            'promote_section' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $current_session = $request->current_session;
            $current_class = $request->current_class;
            $UpYear = AramiscAcademicYear::find($current_session);

            $exams = AramiscExamType::where('active_status', 1)
                ->where('id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $Upsessions = AramiscAcademicYear::where('active_status', 1)->whereYear('created_at', '>', date('Y', strtotime($UpYear->year)) . ' 00:00:00')
                ->where('school_id', Auth::user()->school_id)->get();
            $sessions = AramiscAcademicYear::where('active_status', 1)->where('id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $promot_year = AramiscAcademicYear::find($request->promote_session);

            if ($request->promote_class == "" || $request->promote_session == "") {
                $students = AramiscStudent::where('class_id', '=', $request->promote_class)->where('session_id', '=', $request->promote_session)
                    ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

                Session::flash('message-danger', 'Something went wrong, please try again');
                return view('backEnd.studentInformation.student_promote_custom', compact('exams', 'Upsessions', 'sessions', 'classes', 'students', 'current_session', 'current_class'));
            } else {

                DB::beginTransaction();
                try {
                    $std_info = [];
                    $student = AramiscStudent::findOrFail($request->id[0]);
                    event(new StudentPromotionGroupDisable($student->section_id, $request->current_class));
                    foreach ($request->id as $student_id) {
                        $student_details = AramiscStudent::findOrFail($student_id);

                        $new_section = $request->promote_section;

                        $roll = null;
                        $merit_list = null;

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
                        $student->academic_id = $request->promote_session;
                        $student->section_id = $new_section;
                        $student->roll_no = $roll;
                        $student->created_at = $promot_year->starting_date . ' 12:00:00';
                        $student->save();
                        event(new StudentPromotion($student_promote, $student));
                    }



                    DB::commit();
                    /*
                                        $students = AramiscStudent::where('class_id', '=', $request->promote_class)->where('session_id', '=', $request->promote_session)
                                            ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();*/
                    Toastr::success('Operation successful', 'Success');
                    return redirect('student-promote');
                } catch (\Exception $e) {
                    DB::rollback();
                    $students = AramiscStudent::where('class_id', '=', $request->current_class)->where('session_id', '=', $request->current_session)
                        ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

                    Session::flash('message-danger-table', 'Something went wrong, please try again');
                    Toastr::error('Operation Failed', 'Failed');
                    return view('backEnd.studentInformation.student_promote_custom', compact('exams', 'Upsessions', 'sessions', 'classes', 'students', 'current_session', 'current_class'));
                }
            }
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    //studentReport modified by jmrashed
    public function studentReport(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $types = AramiscStudentCategory::where('school_id', Auth::user()->school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentInformation.student_report', compact('classes', 'types', 'genders'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //student report search modified by jmrashed
    public function studentReportSearch(Request $request)
    {
        $request->validate([
            'class' => 'required'
        ]);
        try {
            $students = AramiscStudent::query();

            $students->where('academic_id', getAcademicId())->where('active_status', 1);

            //if no class is selected
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            //if no section is selected
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            //if no student is category selected
            if ($request->type != "") {
                $students->where('student_category_id', $request->type);
            }

            //if no gender is selected
            if ($request->gender != "") {
                $students->where('gender_id', $request->gender);
            }
            $students = $students->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $types = AramiscStudentCategory::where('school_id', Auth::user()->school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id', Auth::user()->school_id)->get();

            $class_id = $request->class;
            $type_id = $request->type;
            $gender_id = $request->gender;
            $clas = AramiscClass::find($request->class);
            return view('backEnd.studentInformation.student_report', compact('students', 'classes', 'types', 'genders', 'class_id', 'type_id', 'gender_id', 'clas'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentAttendanceReport(Request $request)
    {
        try {
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
                    ->where('aramisc_assign_subjects.academic_id', getAcademicId())
                    ->where('aramisc_assign_subjects.active_status', 1)
                    ->where('aramisc_assign_subjects.school_id', Auth::user()->school_id)
                    ->select('aramisc_classes.id', 'class_name')
                    ->distinct('aramisc_classes.id')
                    ->get();
            } else {
                $classes = AramiscClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }
            $types = AramiscStudentCategory::where('school_id', Auth::user()->school_id)->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentInformation.student_attendance_report', compact('classes', 'types', 'genders'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentAttendanceReportSearch(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'month' => 'required',
            'year' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $year = $request->year;
            $month = $request->month;
            $class_id = $request->class;
            $section_id = $request->section;
            $current_day = date('d');
            $clas = AramiscClass::findOrFail($request->class);
            $sec = AramiscSection::findOrFail($request->section);
            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $classes = AramiscAssignSubject::where('teacher_id', $teacher_info->id)->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
                    ->where('aramisc_assign_subjects.academic_id', getAcademicId())
                    ->where('aramisc_assign_subjects.active_status', 1)
                    ->where('aramisc_assign_subjects.school_id', Auth::user()->school_id)
                    ->select('aramisc_classes.id', 'class_name')
                    ->distinct('aramisc_classes.id')
                    ->get();
            } else {
                $classes = AramiscClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }
            $students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $attendances = [];
            foreach ($students as $student) {
                $attendance = AramiscStudentAttendance::where('student_id', $student->id)->where('attendance_date', 'like', $request->year . '-' . $request->month . '%')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                if (count($attendance) != 0) {
                    $attendances[] = $attendance;
                }
            }
            return view('backEnd.studentInformation.student_attendance_report', compact(
                'classes',
                'attendances',
                'students',
                'days',
                'year',
                'month',
                'current_day',
                'class_id',
                'section_id',
                'clas',
                'sec'
            ));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function studentAttendanceReportPrint($class_id, $section_id, $month, $year)
    {
        set_time_limit(2700);
        try {
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $students = DB::table('aramisc_students')
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $attendances = [];
            foreach ($students as $student) {
                $attendance = AramiscStudentAttendance::where('student_id', $student->id)
                    ->where('attendance_date', 'like', $year . '-' . $month . '%')
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                if ($attendance) {
                    $attendances[] = $attendance;
                }
            }

            // $pdf = Pdf::loadView(
            //     'backEnd.studentInformation.student_attendance_print',
            //     [
            //         'attendances' => $attendances,
            //         'days' => $days,
            //         'year' => $year,
            //         'month' => $month,
            //         'class_id' => $class_id,
            //         'section_id' => $section_id,
            //         'class' => AramiscClass::find($class_id),
            //         'section' => AramiscSection::find($section_id),
            //     ]
            // )->setPaper('A4', 'landscape');
            // return $pdf->stream('student_attendance.pdf');

            $class = AramiscClass::find($class_id);
            $section = AramiscSection::find($section_id);
            return view('backEnd.studentInformation.student_attendance_print', compact('class', 'section', 'attendances', 'days', 'year', 'month', 'current_day', 'class_id', 'section_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function importStudent()
    {
        try {
            // start check student limitation for subscription
            if (isSubscriptionEnabled()) {

                $active_student = AramiscStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

                if (\Modules\Saas\Entities\SmPackagePlan::student_limit() <= $active_student && saasDomain() != 'school') {

                    Toastr::error('Your student limit has been crossed.', 'Failed');
                    return redirect()->back();
                }
            }
            // End check student limitation for subscription


            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $genders = AramiscBaseSetup::where('base_group_id', 1)->where('school_id', Auth::user()->school_id)->get();
            $blood_groups = AramiscBaseSetup::where('base_group_id', 3)->where('school_id', Auth::user()->school_id)->get();
            $religions = AramiscBaseSetup::where('base_group_id', 2)->where('school_id', Auth::user()->school_id)->get();
            $sessions = AramiscAcademicYear::where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentInformation.import_student', compact('classes', 'genders', 'blood_groups', 'religions', 'sessions'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function downloadStudentFile()
    {
        try {
            $studentsArray = ['admission_number', 'roll_no', 'first_name', 'last_name', 'date_of_birth', 'religion', 'gender', 'caste', 'mobile', 'email', 'admission_date', 'blood_group', 'height', 'weight', 'father_name', 'father_phone', 'father_occupation', 'mother_name', 'mother_phone', 'mother_occupation', 'guardian_name', 'guardian_relation', 'guardian_email', 'guardian_phone', 'guardian_occupation', 'guardian_address', 'current_address', 'permanent_address', 'bank_account_no', 'bank_name', 'national_identification_no', 'local_identification_no', 'previous_school_details', 'note'];

            return Excel::create('students', function ($excel) use ($studentsArray) {
                $excel->sheet('students', function ($sheet) use ($studentsArray) {
                    $sheet->fromArray($studentsArray);
                });
            })->download('xlsx');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function studentBulkStore(Request $request)
    {

        $request->validate(
            [
                'session' => 'required',
                'class' => 'required',
                'section' => 'required',
                'file' => 'required'
            ],
            [
                'session.required' => 'Academic year field is required.'
            ]
        );



        $file_type = strtolower($request->file->getClientOriginalExtension());
        if ($file_type <> 'csv' && $file_type <> 'xlsx' && $file_type <> 'xls') {
            Toastr::warning('The file must be a file of type: xlsx, csv or xls', 'Warning');
            return redirect()->back();
        } else {
            try {
                DB::beginTransaction();
                $path = $request->file('file');
                Excel::import(new StudentsImport, $request->file('file'), 's3', \Maatwebsite\Excel\Excel::XLSX);
                $data = StudentBulkTemporary::where('user_id', Auth::user()->id)->get();

                /*   $usersUnique = $data->unique('admission_number');
                $usersDupes = $data->diff($usersUnique);
                if (sizeof($usersDupes) > sizeof($data)) {
                    return redirect()->back()->with("message-danger","Admission number required");
                 }
                if (sizeof($usersDupes) >= 1) {
                   return redirect()->back()->with("message-danger","Admission number should be unique");
                } */


                $shcool_details = AramiscGeneralSettings::where('school_id', auth()->user()->school_id)->first();
                $school_name = explode(' ', $shcool_details->school_name);
                $short_form = '';
                foreach ($school_name as $value) {
                    $ch = str_split($value);
                    $short_form = $short_form . '' . $ch[0];
                }

                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if (isSubscriptionEnabled()) {

                            $active_student = AramiscStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

                            if (\Modules\Saas\Entities\SmPackagePlan::student_limit() <= $active_student && saasDomain() != 'school') {

                                DB::commit();
                                StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
                                Toastr::error('Your student limit has been crossed.', 'Failed');
                                return redirect('student-list');
                            }
                        }


                        $ad_check = AramiscStudent::where('admission_no', (int) $value->admission_number)->where('school_id', Auth::user()->school_id)->get();
                        //  return $ad_check;

                        if ($ad_check->count() > 0) {
                            DB::rollback();
                            StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
                            Toastr::error('Admission number should be unique.', 'Failed');
                            return redirect()->back();
                        }

                        if ($value->email != "") {
                            $chk =  DB::table('aramisc_students')->where('email', $value->email)->where('school_id', Auth::user()->school_id)->count();
                            if ($chk >= 1) {
                                DB::rollback();
                                StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
                                Toastr::error('Student Email address should be unique.', 'Failed');
                                return redirect()->back();
                            }
                        }

                        if ($value->guardian_email != "") {
                            $chk =  DB::table('aramisc_parents')->where('guardians_email', $value->guardian_email)->where('school_id', Auth::user()->school_id)->count();
                            if ($chk >= 1) {
                                DB::rollback();
                                StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
                                Toastr::error('Guardian Email address should be unique.', 'Failed');
                                return redirect()->back();
                            }
                        }


                        try {

                            if ($value->admission_number == null) {
                                continue;
                            } else {
                            }
                            $academic_year = AramiscAcademicYear::find($request->session);


                            $user_stu = new User();
                            $user_stu->role_id = 2;
                            $user_stu->full_name = $value->first_name . ' ' . $value->last_name;

                            if (empty($value->email)) {
                                $user_stu->username = $value->admission_number;
                            } else {
                                $user_stu->username = $value->email;
                            }

                            $user_stu->email = $value->email;

                            $user_stu->school_id = Auth::user()->school_id;

                            $user_stu->password = Hash::make(123456);

                            $user_stu->created_at = $academic_year->year . '-01-01 12:00:00';

                            $user_stu->save();

                            $user_stu->toArray();

                            try {

                                $user_parent = new User();
                                $user_parent->role_id = 3;
                                $user_parent->full_name = $value->father_name;

                                if (empty($value->guardian_email)) {
                                    $data_parent['email'] = 'par_' . $value->admission_number;

                                    $user_parent->username  = 'par_' . $value->admission_number;
                                } else {

                                    $data_parent['email'] = $value->guardian_email;

                                    $user_parent->username = $value->guardian_email;
                                }

                                $user_parent->email = $value->guardian_email;

                                $user_parent->password = Hash::make(123456);
                                $user_parent->school_id = Auth::user()->school_id;

                                $user_parent->created_at = $academic_year->year . '-01-01 12:00:00';

                                $user_parent->save();
                                $user_parent->toArray();

                                try {

                                    $parent = new AramiscParent();

                                    if (
                                        $value->relation == 'F' ||
                                        $value->guardians_relation == 'F' ||
                                        $value->guardian_relation == 'F' ||
                                        strtolower($value->guardian_relation) == 'father' ||
                                        strtolower($value->guardians_relation) == 'father'
                                    ) {
                                        $relationFull = 'Father';
                                        $relation = 'F';
                                    } elseif (
                                        $value->relation == 'M' ||
                                        $value->guardians_relation == 'M' ||
                                        $value->guardian_relation == 'M' ||
                                        strtolower($value->guardian_relation) == 'mother' ||
                                        strtolower($value->guardians_relation) == 'mother'
                                    ) {
                                        $relationFull = 'Mother';
                                        $relation = 'M';
                                    } else {
                                        $relationFull = 'Other';
                                        $relation = 'O';
                                    }
                                    $parent->guardians_relation = $relationFull;
                                    $parent->relation = $relation;

                                    $parent->user_id = $user_parent->id;
                                    $parent->fathers_name = $value->father_name;
                                    $parent->fathers_mobile = $value->father_phone;
                                    $parent->fathers_occupation = $value->fathe_occupation;
                                    $parent->mothers_name = $value->mother_name;
                                    $parent->mothers_mobile = $value->mother_phone;
                                    $parent->mothers_occupation = $value->mother_occupation;
                                    $parent->guardians_name = $value->guardian_name;
                                    $parent->guardians_mobile = $value->guardian_phone;
                                    $parent->guardians_occupation = $value->guardian_occupation;
                                    $parent->guardians_address = $value->guardian_address;
                                    $parent->guardians_email = $value->guardian_email;
                                    $parent->school_id = Auth::user()->school_id;
                                    $parent->academic_id = $request->session;

                                    $parent->created_at = $academic_year->year . '-01-01 12:00:00';

                                    $parent->save();
                                    $parent->toArray();

                                    try {
                                        $student = new AramiscStudent();
                                        // $student->siblings_id = $value->sibling_id;
                                        $student->class_id = $request->class;
                                        $student->section_id = $request->section;
                                        $student->session_id = $request->session;
                                        $student->user_id = $user_stu->id;

                                        $student->parent_id = $parent->id;
                                        $student->role_id = 2;

                                        $student->admission_no = $value->admission_number;
                                        $student->roll_no = $value->roll_no;
                                        $student->first_name = $value->first_name;
                                        $student->last_name = $value->last_name;
                                        $student->full_name = $value->first_name . ' ' . $value->last_name;
                                        $student->gender_id = $value->gender;
                                        $student->date_of_birth = date('Y-m-d', strtotime($value->date_of_birth));
                                        $student->caste = $value->caste;
                                        $student->email = $value->email;
                                        $student->mobile = $value->mobile;
                                        $student->admission_date = date('Y-m-d', strtotime($value->admission_date));
                                        $student->bloodgroup_id = $value->blood_group;
                                        $student->religion_id = $value->religion;
                                        $student->height = $value->height;
                                        $student->weight = $value->weight;
                                        $student->current_address = $value->current_address;
                                        $student->permanent_address = $value->permanent_address;
                                        $student->national_id_no = $value->national_identification_no;
                                        $student->local_id_no = $value->local_identification_no;
                                        $student->bank_account_no = $value->bank_account_no;
                                        $student->bank_name = $value->bank_name;
                                        $student->previous_school_details = $value->previous_school_details;
                                        $student->aditional_notes = $value->note;
                                        $student->school_id = Auth::user()->school_id;
                                        $student->academic_id = $request->session;

                                        $student->created_at = $academic_year->year . '-01-01 12:00:00';

                                        $student->save();

                                        $user_info = [];

                                        if ($value->email != "") {
                                            $user_info[] =  array('email' => $value->email, 'username' => $value->email);
                                        }


                                        if ($value->guardian_email != "") {
                                            $user_info[] =  array('email' =>  $value->guardian_email, 'username' => $data_parent['email']);
                                        }
                                    } catch (\Illuminate\Database\QueryException $e) {

                                        DB::rollback();
                                        Toastr::error('Operation Failed', 'Failed');
                                        return redirect()->back();
                                    } catch (\Exception $e) {

                                        DB::rollback();
                                        Toastr::error('Operation Failed', 'Failed');
                                        return redirect()->back();
                                    }
                                } catch (\Exception $e) {
                                    DB::rollback();
                                    Toastr::error('Operation Failed', 'Failed');
                                    return redirect()->back();
                                }
                            } catch (\Exception $e) {
                                DB::rollback();
                                Toastr::error('Operation Failed', 'Failed');
                                return redirect()->back();
                            }
                        } catch (\Exception $e) {
                            DB::rollback();
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }

                    StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();

                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                }
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    }


    public function guardianReport(Request $request)
    {
        try {
            $students = AramiscStudent::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentInformation.guardian_report', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function guardianReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $students = AramiscStudent::query();
            $students->where('academic_id', getAcademicId())->where('active_status', 1);
            $students->where('class_id', $request->class);
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            $students = $students->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();


            $class_id = $request->class;
            $clas = AramiscClass::find($request->class);
            return view('backEnd.studentInformation.guardian_report', compact('students', 'classes', 'class_id', 'clas'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentLoginReport(Request $request)
    {
        try {
            $students = AramiscStudent::where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentInformation.login_info', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function studentLoginSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $students = AramiscStudent::query();
            $students->where('academic_id', getAcademicId())->where('active_status', 1);
            $students->where('class_id', $request->class);
            if ($request->section != "") {
                $students->where('section_id', $request->section);
            }
            $students = $students->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_id = $request->class;
            $clas = AramiscClass::find($request->class);
            return view('backEnd.studentInformation.login_info', compact('students', 'classes', 'class_id', 'clas'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function disabledStudent(Request $request)
    {
        try {

            $pt = trans('student.disabled_student');
            $students = AramiscStudent::where('active_status', 0)->where('school_id', Auth::user()->school_id)->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();


            return view('backEnd.studentInformation.disabled_student', compact('students', 'classes', 'pt'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function disabledStudentSearch(Request $request)
    {
        try {
            $pt = trans('student.disabled_student');
            $student_ids = StudentRecord::when($request->class, function ($query) use ($request) {
                $query->where('class_id', $request->class);
            })
                ->when($request->section, function ($query) use ($request) {
                    $query->where('section_id', $request->section);
                })

                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->pluck('student_id')->unique();

            $students = AramiscStudent::query()->withOutGlobalScope(StatusAcademicSchoolScope::class);
            $students->where('academic_id', getAcademicId())->where('active_status', 0);
            if ($request->name != "") {
                $students->where('full_name', 'like', '%' . $request->name . '%');
            }
            if ($request->admission_no != "") {
                $students->where('admission_no', 'like', '%' . $request->admission_no . '%');
            }
            $students = $students->whereIn('id', $student_ids)->where('school_id', Auth::user()->school_id)->get();

            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $class_id = $request->class_id;
            $section_id = $request->section_id;
            $name = $request->name;
            $admission_no = $request->admission_no;


            return view('backEnd.studentInformation.disabled_student', compact('classes', 'class_id', 'section_id', 'name', 'admission_no', 'pt'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



    public function disabledStudentDelete1(Request $request)
    {
        try {

            $student_detail = AramiscStudent::find($request->id);
            $parent_user = @$student_detail->parents->user_id;


            $siblings = AramiscStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('parent_id', $student_detail->parent_id)->where('school_id', Auth::user()->school_id)->get();


            DB::beginTransaction();


            if ($student_detail->student_photo != "") {
                if (file_exists($student_detail->student_photo)) {
                    unlink($student_detail->student_photo);
                }
            }

            AramiscStudent::destroy($request->id);


            if (count($siblings) == 1) {
                $parent = AramiscParent::find($student_detail->parent_id);

                if ($parent->fathers_photo != "") {
                    if (file_exists($parent->fathers_photo)) {
                        unlink($parent->fathers_photo);
                    }
                }
                if ($parent->mothers_photo != "") {
                    if (file_exists($parent->mothers_photo)) {
                        unlink($parent->mothers_photo);
                    }
                }
                if ($parent->guardians_photo != "") {
                    if (file_exists($parent->guardians_photo)) {
                        unlink($parent->guardians_photo);
                    }
                }

                $parent->delete();
            }



            $student_user = User::find($student_detail->user_id);
            $student_user->delete();

            if (count($siblings) == 1) {

                $parent_user = User::find($parent_user);
                $parent_user->delete();
            }
            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function disabledStudentDelete(Request $request)
    {

        try {
            $tables = \App\tableList::getTableList('student_id', $request->id);
            try {
                $single_data = 0;

                if (checkAdmin()) {
                    $student_detail = AramiscStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->find($request->id);
                } else {
                    $student_detail = AramiscStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
                }


                $parent_user = @$student_detail->parents->user_id;
                $siblings = AramiscStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('parent_id', $student_detail->parent_id)->where('school_id', Auth::user()->school_id)->get();
                DB::beginTransaction();
                if ($student_detail->student_photo != "") {
                    if (file_exists($student_detail->student_photo)) {
                        unlink($student_detail->student_photo);
                    }
                }

                if (count($siblings) == 1) {
                    $parent = AramiscParent::find($student_detail->parent_id);

                    if ($parent) {
                        if ($parent->fathers_photo != "") {
                            if (file_exists($parent->fathers_photo)) {
                                unlink($parent->fathers_photo);
                            }
                        }
                        if ($parent->mothers_photo != "") {
                            if (file_exists($parent->mothers_photo)) {
                                unlink($parent->mothers_photo);
                            }
                        }
                        if ($parent->guardians_photo != "") {
                            if (file_exists($parent->guardians_photo)) {
                                unlink($parent->guardians_photo);
                            }
                        }

                        $parent->delete();
                    }
                }
                $student_user = User::find($student_detail->user_id);
                $student_user->delete();

                if (count($siblings) == 1) {

                    $parent_user = User::find($parent_user);
                    if ($parent_user) {
                        $parent_user->delete();
                    }
                }
                $table_list = \App\tableList::ONLY_TABLE_LIST('student_id');
                foreach ($table_list as $key => $table) {
                    $table_data = DB::table($table)->where('student_id', $request->id)->get();
                    foreach ($table_data as $key => $data) {
                        $single_data == DB::table($table)->where('id', $data->id)->delete();
                    }
                }

                foreach ($table_list as $key => $table) {
                    $table_data = DB::table($table)->where('student_id', $request->id)->get();
                    foreach ($table_data as $key => $data) {
                        $single_data == DB::table($table)->where('id', $data->id)->delete();
                    }
                }

                $student_detail->delete();
                DB::commit();
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollback();
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            } catch (\Exception $e) {
                DB::rollback();
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function enableStudent(Request $request)
    {
        try {
            if (isSubscriptionEnabled()) {
                $active_student = AramiscStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();
                if (\Modules\Saas\Entities\SmPackagePlan::student_limit() <= $active_student) {
                    Toastr::error('Your student limit has been crossed.', 'Failed');
                    return redirect()->back();
                }
            }

            DB::beginTransaction();
            // $student_detail = AramiscStudent::find($request->id);
            if (checkAdmin()) {
                $student_detail = AramiscStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->find($request->id);
            } else {
                $student_detail = AramiscStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }
            // dd($student_detail->studentRecord->class_id, $student_detail->studentRecord->section_id);

            $student_detail->active_status = 1;


            $parent = AramiscParent::find($student_detail->parent_id);
            if ($parent) {
                $parent->active_status = 1;
                $parent->save();
            }


            $student_user = User::find($student_detail->user_id);
            $student_user->active_status = 1;
            $student_user->save();

            $parent_user = User::find(@$student_detail->parents->user_id);
            if ($parent_user) {
                $parent_user->active_status = 1;
                $parent_user->save();
            }
            $student_detail->save();

            DB::commit();

            $class_teacher = AramiscClassTeacher::whereHas('teacherClass', function ($q) use ($student_detail) {
                $q->where('active_status', 1)
                    ->where('class_id', $student_detail->studentRecord->class_id)
                    ->where('section_id', $student_detail->studentRecord->section_id);
            })
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)
                ->first();
            $data['class_id'] = $request->class;
            $data['section_id'] = $request->section;
            if ($class_teacher != null) {
                $data['teacher_name'] = $class_teacher->teacher->full_name;
                $this->sent_notifications('Enable/Disable_Student', [$class_teacher->teacher->user_id], $data, ['Teacher']);
            }
            $this->sent_notifications('Enable/Disable_Student', [$student_detail->user_id], $data, ['Student', 'Parent']);

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function studentHistory(Request $request)
    {
        try {
            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $students = AramiscStudent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $years = AramiscStudent::select('admission_date')->where('active_status', 1)
                ->where('academic_id', getAcademicId())->get()
                ->distinct(function ($val) {
                    return Carbon::parse($val->admission_date)->format('Y');
                });
            return view('backEnd.studentInformation.student_history', compact('classes', 'years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentHistorySearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $students = AramiscStudent::query();
            $students->where('academic_id', getAcademicId())->where('active_status', 1);
            $students->where('class_id', $request->class);
            $students->where('active_status', 1);
            if ($request->admission_year != "") {
                $students->where('admission_date', 'like',  $request->admission_year . '%');
            }

            $students = $students->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $years = AramiscStudent::select('admission_date')->where('active_status', 1)
                ->where('academic_id', getAcademicId())->get()
                ->distinct(function ($val) {
                    return Carbon::parse($val->admission_date)->format('Y');
                });

            $class_id = $request->class;
            $year = $request->admission_year;
            $clas = AramiscClass::find($request->class);
            return view('backEnd.studentInformation.student_history', compact('students', 'classes', 'years', 'class_id', 'year', 'clas'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function view_academic_performance(Request $request, $id)
    {
        return $id;
    }

    function previousRecord()
    {
        try {
            $academic_years = AramiscAcademicYear::where('school_id', Auth::user()->school_id)->get();
            $exam_types = AramiscExamType::where('school_id', Auth::user()->school_id)->get();

            $classes = AramiscClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            return view('backEnd.examination.previous_record', compact('classes', 'exam_types', 'academic_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    function previousRecordSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'promote_session' => 'required',
            'promote_class' => 'required',
            'promote_section' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $yearCh = AramiscAcademicYear::find($request->promote_session);
            $students = AramiscStudentPromotion::where('created_at', 'LIKE', '%' . $yearCh->year . '%');

            if ($request->promote_class != "") {
                $students->where('previous_class_id', $request->promote_class);
            }
            if ($request->promote_section != "") {
                $students->where('previous_section_id', $request->promote_section);
            }
            $year = $request->promote_session;
            $students = $students->where('school_id', Auth::user()->school_id)->get();

            $academic_years = AramiscAcademicYear::where('school_id', Auth::user()->school_id)->get();
            $exam_types = AramiscExamType::where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $class = AramiscClass::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($request->promote_class);
            $section = AramiscSection::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($request->promote_section);
            return view('backEnd.examination.previous_record', compact('classes', 'exam_types', 'academic_years', 'students', 'year', 'class', 'section'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function allStudentExport()
    {
        try {
            return view('backEnd.studentInformation.allStudentExport');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function allStudentExportExcel()
    {
        try {
            return Excel::download(new AllStudentExport, 'all_student_export.csv');
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function allStudentExportPdf()
    {
        try {
            $academiYear = AramiscAcademicYear::find(getAcademicId());
            $students = AramiscStudent::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return view('backEnd.studentInformation.allStudentExportPdfPrint', compact('students', 'academiYear'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



    public function unassignedStudent(Request $request)
    {
        try {
            $pt = trans('student.unassigned_student_list');
            $students = AramiscStudent::wheredoesnthave('studentRecords')->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentInformation.unassigned_student', compact('students', 'classes', 'pt'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



    public function sortingStudent($class_id)
    {
        try {
            $class = AramiscClass::find($class_id);
            $pt = trans('student.class') . " " . @$class->class_name . " " . trans('student.student_list');
            return view('backEnd.studentInformation.sortingStudent', compact('class_id', 'pt'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function sortingSectionStudent($class_id, $section_id)
    {
        try {
            $class = AramiscClass::find($class_id);
            $section = AramiscSection::find($section_id);
            $pt = trans('student.class') . "-" . @$class->class_name . '(' . $section->section_name . ') ' . trans('student.student_list');
            return view('backEnd.studentInformation.sortingStudent', compact('section_id', 'class_id', 'pt'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
