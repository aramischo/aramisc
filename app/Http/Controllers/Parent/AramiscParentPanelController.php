<?php

namespace App\Http\Controllers\Parent;

use App\User;
use App\AramiscBook;
use App\AramiscExam;
use App\AramiscClass;
use App\AramiscEvent;
use App\AramiscRoute;
use App\AramiscStaff;
use App\AramiscParent;
use App\AramiscHoliday;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscVehicle;
use App\AramiscWeekend;
use Carbon\Carbon;
use App\AramiscExamType;
use App\AramiscHomework;
use App\AramiscRoomList;
use App\AramiscRoomType;
use App\AramiscBaseSetup;
use App\AramiscBookIssue;
use App\AramiscClassTime;
use App\AramiscComplaint;
use App\AramiscLeaveType;
use App\AramiscFeesAssign;
use App\AramiscMarksGrade;
use App\AramiscOnlineExam;
use App\ApiBaseMethod;
use App\AramiscBankAccount;
use App\AramiscFeesPayment;
use App\AramiscLeaveDefine;
use App\AramiscNoticeBoard;
use App\AramiscAcademicYear;
use App\AramiscExamSchedule;
use App\AramiscLeaveRequest;
use App\AramiscStudentGroup;
use App\AramiscAssignSubject;
use App\AramiscAssignVehicle;
use App\AramiscDormitoryList;
use App\AramiscLibraryMember;
use App\AramiscPaymentMethhod;
use App\AramiscGeneralSettings;
use App\AramiscStudentCategory;
use App\AramiscStudentDocument;
use App\AramiscStudentTimeline;
use App\Models\FeesInvoice;
use App\AramiscStudentAttendance;
use App\AramiscSubjectAttendance;
use App\Traits\CustomFields;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscClassRoutineUpdate;
use App\AramiscFeesAssignDiscount;
use App\AramiscClassOptionalSubject;
use Barryvdh\DomPDF\Facade\Pdf;
use App\AramiscOptionalSubjectAssign;
use App\AramiscStudentTakeOnlineExam;
use App\Traits\NotificationSend;
use App\Models\AramiscCalendarSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Models\TeacherEvaluationSetting;
use Illuminate\Support\Facades\Response;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Models\AramiscStudentRegistrationField;
use Modules\RolePermission\Entities\AramiscRole;
use Modules\Wallet\Entities\WalletTransaction;
use Modules\OnlineExam\Entities\OnlineExam;
use Modules\BehaviourRecords\Entities\AssignIncident;
use App\Http\Controllers\AramiscAcademicCalendarController;
use Modules\OnlineExam\Entities\StudentTakeOnlineExam;
use Modules\BehaviourRecords\Entities\BehaviourRecordSetting;
use App\Http\Requests\Admin\StudentInfo\AramiscStudentAdmissionRequest;

class AramiscParentPanelController extends Controller
{
    use NotificationSend;
    use CustomFields;
    public function parentDashboard()
    {
        try {
            $holidays = AramiscHoliday::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)->get();
            $my_childrens = auth()->user()->parent ? auth()->user()->parent->childrens->load('assignSubjects', 'assignSubject', 'studentOnlineExams', 'studentRecords', 'studentRecords.feesInvoice', 'studentRecords.class', 'studentRecords.section', 'studentRecords.incidents', 'examSchedule', 'attendances') : [];

            $aramisc_weekends = AramiscWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', auth()->user()->school_id)->get();
            $aramiscevents = AramiscEvent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)
                ->where(function ($q) {
                    $q->where('for_whom', 'All')->orWhere('for_whom', 'Parents');
                })
                ->get();

            $count_event = 0;
            $calendar_events = array();

            foreach ($holidays as $k => $holiday) {

                $calendar_events[$k]['title'] = $holiday->holiday_title;

                $calendar_events[$k]['start'] = $holiday->from_date;

                $calendar_events[$k]['end'] = Carbon::parse($holiday->to_date)->addDays(1)->format('Y-m-d');

                $calendar_events[$k]['description'] = $holiday->details;

                $calendar_events[$k]['url'] = $holiday->upload_image_file;

                $count_event = $k;
                $count_event++;
            }

            foreach ($aramiscevents as $k => $event) {

                $calendar_events[$count_event]['title'] = $event->event_title;

                $calendar_events[$count_event]['start'] = $event->from_date;

                $calendar_events[$count_event]['end'] = Carbon::parse($event->to_date)->addDays(1)->format('Y-m-d');
                $calendar_events[$count_event]['description'] = $event->event_des;
                $calendar_events[$count_event]['url'] = $event->uplad_image_file;
                $count_event++;
            }
            $totalNotices =  AramiscNoticeBoard::where('active_status', 1)->where('inform_to', 'LIKE', '%3%')
                ->orderBy('id', 'DESC')
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)->get();
            $currency = AramiscGeneralSettings::find(1);

            $complaints = AramiscComplaint::with('complaintType', 'complaintSource')->get();

            $data['settings'] = AramiscCalendarSetting::get();
            $data['roles'] = AramiscRole::where(function ($q) {
                $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
            })
                ->whereNotIn('id', [1, 2])
                ->get();
            $academicCalendar = new AramiscAcademicCalendarController();
            $data['events'] = $academicCalendar->calenderData();

            return view('backEnd.parentPanel.parent_dashboard', compact('holidays', 'calendar_events', 'aramiscevents', 'totalNotices', 'my_childrens', 'aramisc_weekends', 'currency', 'complaints'), $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentUpdate(AramiscStudentAdmissionRequest $request)
    {
        try {
            $student_detail = AramiscStudent::find($request->id);
            $validator = Validator::make($request->all(), $this->generateValidateRules("student_registration", $student_detail));
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    Toastr::error(str_replace('custom f.', '', $error), 'Failed');
                }
                return redirect()->back()->withInput();
            }
            // custom field validation End


            $destination = 'public/uploads/student/document/';
            $student_file_destination = 'public/uploads/student/';
            $student = AramiscStudent::find($request->id);

            $academic_year = $request->session ? AramiscAcademicYear::find($request->session) : '';
            DB::beginTransaction();

            if ($student) {
                $username = $request->phone_number ? $request->phone_number : $request->admission_number;
                $phone_number = $request->phone_number ? $request->phone_number : null;
                $user_stu = $this->addUser($student_detail->user_id, 2, $username, $request->email_address, $phone_number);
                //sibling || parent info user update
                if (($request->sibling_id == 0 || $request->sibling_id == 1) && $request->parent_id == "") {
                    $username = $request->guardians_phone ? $request->guardians_phone : $request->guardians_email;
                    $phone_number = $request->guardians_phone;
                    $user_parent =  $this->addUser($student_detail->parents->user_id, 3, $username, $request->guardians_email, $phone_number);

                    $user_parent->toArray();
                } elseif ($request->sibling_id == 0 && $request->parent_id != "") {
                    User::destroy($student_detail->parents->user_id);
                } elseif (($request->sibling_id == 2 || $request->sibling_id == 1) && $request->parent_id != "") {
                } elseif ($request->sibling_id == 2 && $request->parent_id == "") {

                    $username = $request->guardians_phone ? $request->guardians_phone : $request->guardians_email;
                    $phone_number = $request->guardians_phone;
                    $user_parent = $this->addUser(null, 3, $username, $request->guardians_email, $phone_number);
                    $user_parent->toArray();
                }
                // end
                //sibling & parent info update
                if ($request->sibling_id == 0 && $request->parent_id != "") {
                    AramiscParent::destroy($student_detail->parent_id);
                } elseif (($request->sibling_id == 2 || $request->sibling_id == 1) && $request->parent_id != "") {
                } else {

                    if (($request->sibling_id == 0 || $request->sibling_id == 1) && $request->parent_id == "") {
                        $parent = AramiscParent::find($student_detail->parent_id);
                    } elseif ($request->sibling_id == 2 && $request->parent_id == "") {
                        $parent = new AramiscParent();
                    }

                    $parent->user_id = $user_parent->id;
                    if ($request->filled('fathers_name')) {
                        $parent->fathers_name = $request->fathers_name;
                    }
                    if ($request->filled('fathers_phone')) {
                        $parent->fathers_mobile = $request->fathers_phone;
                    }
                    if ($request->filled('fathers_occupation')) {
                        $parent->fathers_occupation = $request->fathers_occupation;
                    }
                    if ($request->filled('fathers_photo')) {
                        $parent->fathers_photo = fileUpdate($parent->fathers_photo, $request->fathers_photo, $student_file_destination);
                    }
                    if ($request->filled('mothers_name')) {
                        $parent->mothers_name = $request->mothers_name;
                    }
                    if ($request->filled('mothers_phone')) {
                        $parent->mothers_mobile = $request->mothers_phone;
                    }
                    if ($request->filled('mothers_occupation')) {
                        $parent->mothers_occupation = $request->mothers_occupation;
                    }
                    if ($request->filled('mothers_photo')) {
                        $parent->mothers_photo = fileUpdate($parent->mothers_photo, $request->mothers_photo, $student_file_destination);
                    }
                    if ($request->filled('guardians_name')) {
                        $parent->guardians_name = $request->guardians_name;
                    }
                    if ($request->filled('guardians_phone')) {
                        $parent->guardians_mobile = $request->guardians_phone;
                    }
                    if ($request->filled('guardians_email')) {
                        $parent->guardians_email = $request->guardians_email;
                    }
                    if ($request->filled('guardians_occupation')) {
                        $parent->guardians_occupation = $request->guardians_occupation;
                    }

                    if ($request->filled('relation')) {
                        $parent->guardians_relation = $request->relation;
                    }
                    if ($request->filled('relationButton')) {
                        $parent->relation = $request->relationButton;
                    }
                    if ($request->filled('guardians_photo')) {
                        $parent->guardians_photo = fileUpdate($student->parents->guardians_photo, $request->guardians_photo, $student_file_destination);
                    }
                    if ($request->filled('guardians_address')) {
                        $parent->guardians_address = $request->guardians_address;
                    }
                    if ($request->filled('is_guardian')) {
                        $parent->is_guardian = $request->is_guardian;
                    }

                    if ($request->filled('session')) {
                        $parent->created_at = $academic_year->year . '-01-01 12:00:00';
                    }
                    $parent->save();
                    $parent->toArray();
                }
                // end sibling & parent info update
                // student info update
                $student = AramiscStudent::find($request->id);
                if (($request->sibling_id == 0 || $request->sibling_id == 1) && $request->parent_id == "") {
                    $student->parent_id = $parent->id;
                } elseif ($request->sibling_id == 0 && $request->parent_id != "") {
                    $student->parent_id = $request->parent_id;
                } elseif (($request->sibling_id == 2 || $request->sibling_id == 1) && $request->parent_id != "") {
                    $student->parent_id = $request->parent_id;
                } elseif ($request->sibling_id == 2 && $request->parent_id == "") {
                    $student->parent_id = $parent->id;
                }
                if ($request->filled('class')) {
                    $student->class_id = $request->class;
                }
                if ($request->filled('section')) {
                    $student->section_id = $request->section;
                }
                if ($request->filled('session')) {
                    $student->session_id = $request->session;
                }
                if ($request->filled('admission_number')) {
                    $student->admission_no = $request->admission_number;
                }
                $student->user_id = $user_stu->id;
                if ($request->filled('roll_number')) {
                    $student->roll_no = $request->roll_number;
                }
                if ($request->filled('first_name')) {
                    $student->first_name = $request->first_name;
                }
                if ($request->filled('last_name')) {
                    $student->last_name = $request->last_name;
                }
                if ($request->filled('first_name') && $request->filled('last_name')) {
                    $student->full_name = $request->first_name . ' ' . $request->last_name;
                }
                if ($request->filled('gender')) {
                    $student->gender_id = $request->gender;
                }
                if ($request->filled('date_of_birth')) {
                    $student->date_of_birth = date('Y-m-d', strtotime($request->date_of_birth));
                }
                if ($request->filled('age')) {
                    $student->age = $request->age;
                }
                if ($request->filled('caste')) {
                    $student->caste = $request->caste;
                }
                if ($request->filled('email_address')) {
                    $student->email = $request->email_address;
                }
                if ($request->filled('phone_number')) {
                    $student->mobile = $request->phone_number;
                }
                if ($request->filled('admission_date')) {
                    $student->admission_date = date('Y-m-d', strtotime($request->admission_date));
                }
                if ($request->filled('photo')) {
                    $student->student_photo = fileUpdate($parent->student_photo, $request->photo, $student_file_destination);
                }
                if ($request->filled('blood_group')) {
                    $student->bloodgroup_id = $request->blood_group;
                }
                if ($request->filled('religion')) {
                    $student->religion_id = $request->religion;
                }
                if ($request->filled('height')) {
                    $student->height = $request->height;
                }
                if ($request->filled('weight')) {
                    $student->weight = $request->weight;
                }
                if ($request->filled('current_address')) {
                    $student->current_address = $request->current_address;
                }
                if ($request->filled('permanent_address')) {
                    $student->permanent_address = $request->permanent_address;
                }
                if ($request->filled('student_category_id')) {
                    $student->student_category_id = $request->student_category_id;
                }
                if ($request->filled('student_group_id')) {
                    $student->student_group_id = $request->student_group_id;
                }
                if ($request->filled('route')) {
                    $student->route_list_id = $request->route;
                }
                if ($request->filled('dormitory_name')) {
                    $student->dormitory_id = $request->dormitory_name;
                }
                if ($request->filled('room_number')) {
                    $student->room_id = $request->room_number;
                }

                if (!empty($request->vehicle)) {
                    $driver = AramiscVehicle::where('id', '=', $request->vehicle)
                        ->select('driver_id')
                        ->first();
                    $student->vechile_id = $request->vehicle;
                    $student->driver_id = $driver->driver_id;
                }
                if ($request->filled('national_id_number')) {
                    $student->national_id_no = $request->national_id_number;
                }
                if ($request->filled('local_id_number')) {
                    $student->local_id_no = $request->local_id_number;
                }
                if ($request->filled('bank_account_number')) {
                    $student->bank_account_no = $request->bank_account_number;
                }
                if ($request->filled('bank_name')) {
                    $student->bank_name = $request->bank_name;
                }
                if ($request->filled('previous_school_details')) {
                    $student->previous_school_details = $request->previous_school_details;
                }
                if ($request->filled('additional_notes')) {
                    $student->aditional_notes = $request->additional_notes;
                }
                if ($request->filled('ifsc_code')) {
                    $student->ifsc_code = $request->ifsc_code;
                }
                if ($request->filled('document_title_1')) {
                    $student->document_title_1 = $request->document_title_1;
                }
                if ($request->filled('document_file_1')) {
                    $student->document_file_1 = fileUpdate($student->document_file_1, $request->file('document_file_1'), $destination);
                }
                if ($request->filled('document_title_2')) {
                    $student->document_title_2 = $request->document_title_2;
                }
                if ($request->filled('document_file_2')) {
                    $student->document_file_2 = fileUpdate($student->document_file_2, $request->file('document_file_2'), $destination);
                }
                if ($request->filled('document_title_3')) {
                    $student->document_title_3 = $request->document_title_3;
                }
                if ($request->filled('document_file_3')) {
                    $student->document_file_3 = fileUpdate($student->document_file_3, $request->file('document_file_3'), $destination);
                }
                if ($request->filled('document_title_4')) {
                    $student->document_title_4 = $request->document_title_4;
                }
                if ($request->filled('document_title_4')) {
                    $student->document_file_4 = fileUpdate($student->document_file_4, $request->file('document_file_3'), $destination);
                }

                if ($request->filled('session')) {
                    $student->created_at = $academic_year->year . '-01-01 12:00:00';
                    $student->academic_id = $academic_year->id;
                }


                if ($request->customF) {
                    $dataImage = $request->customF;
                    foreach ($dataImage as $label => $field) {
                        if (is_object($field) && $field != "") {
                            $key = "";

                            $maxFileSize = generalSetting()->file_size;
                            $file = $field;
                            $fileSize = filesize($file);
                            $fileSizeKb = ($fileSize / 1000000);
                            if ($fileSizeKb >= $maxFileSize) {
                                Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                                return redirect()->back();
                            }
                            $file = $field;
                            $key = $file->getClientOriginalName();
                            $file->move('public/uploads/customFields/', $key);
                            $dataImage[$label] = 'public/uploads/customFields/' . $key;
                        }
                    }

                    //Custom Field Start
                    $student->custom_field_form_name = "student_registration";
                    $student->custom_field = json_encode($dataImage, true);
                    //Custom Field End

                }
                if (moduleStatusCheck('Lead') == true) {
                    if ($request->filled('lead_city')) {
                        $student->lead_city_id = $request->lead_city;
                    }
                    if ($request->filled('source_id')) {
                        $student->source_id = $request->source_id;
                    }
                }
                $student->save();
                DB::commit();
            }

            // session null
            $update_stud = AramiscStudent::where('user_id', $student->user_id)->first('student_photo');
            Session::put('profile', $update_stud->student_photo);
            Toastr::success('Operation successful', 'Success');
            return redirect()->route('my_children', [$student->id]);
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    private function addUser($user_id, $role_id, $username, $email, $phone_number)
    {
        try {

            $user = $user_id == null ? new User() : User::find($user_id);
            $user->role_id = $role_id;
            if ($username != null) {
                $user->username = $username;
            }
            if ($email != null) {
                $user->email = $email;
            }
            if ($phone_number != null) {
                $user->phone_number = $phone_number;
            }
            $user->save();
            return $user;
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }

    public function UpdatemyChildren($id)
    {

        try {
            $student = AramiscStudent::find($id);

            $classes = AramiscClass::where('active_status', '=', '1')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $religions = AramiscBaseSetup::where('active_status', '=', '1')
                ->where('base_group_id', '=', '2')
                ->get();

            $blood_groups = AramiscBaseSetup::where('active_status', '=', '1')
                ->where('base_group_id', '=', '3')
                ->get();

            $genders = AramiscBaseSetup::where('active_status', '=', '1')
                ->where('base_group_id', '=', '1')
                ->get();

            $route_lists = AramiscRoute::where('active_status', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $vehicles = AramiscVehicle::where('active_status', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $dormitory_lists = AramiscDormitoryList::where('active_status', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $driver_lists = AramiscStaff::where([['active_status', '=', '1'], ['role_id', 9]])
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $categories = AramiscStudentCategory::where('school_id', Auth::user()->school_id)
                ->get();

            $groups = AramiscStudentGroup::where('school_id', Auth::user()->school_id)
                ->get();

            $sessions = AramiscAcademicYear::where('active_status', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $siblings = AramiscStudent::where('parent_id', $student->parent_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $lead_city = [];
            $sources = [];

            if (moduleStatusCheck('Lead') == true) {
                $lead_city = \Modules\Lead\Entities\LeadCity::where('school_id', auth()->user()->school_id)->get(['id', 'city_name']);
                $sources = \Modules\Lead\Entities\Source::where('school_id', auth()->user()->school_id)->get(['id', 'source_name']);
            }
            $fields = AramiscStudentRegistrationField::where('school_id', auth()->user()->school_id)
                ->when(auth()->user()->role_id == 2, function ($query) {
                    $query->where('student_edit', 1);
                })
                ->when(auth()->user()->role_id == 3, function ($query) {
                    $query->where('parent_edit', 1);
                })
                ->pluck('field_name')->toArray();
            return view('backEnd.parentPanel.update_my_children', compact('student', 'classes', 'religions', 'blood_groups', 'genders', 'route_lists', 'vehicles', 'dormitory_lists', 'categories', 'groups', 'sessions', 'siblings', 'driver_lists', 'lead_city', 'fields', 'sources'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function myChildren($id)
    {
        try {
            $parent_info = Auth::user()->parent;
            $student_detail = AramiscStudent::where('id', $id)->where('parent_id', $parent_info->id)->with('studentRecords.directFeesInstallments.payments', 'studentAttendances', 'studentRecords.directFeesInstallments.installment', 'feesAssign', 'feesAssignDiscount', 'academicYear', 'defaultClass.class', 'category', 'religion')->first();
            $records = $student_detail->studentRecords;
            if ($student_detail) {
                $driver = AramiscVehicle::where('aramisc_vehicles.id', $student_detail->vechile_id)
                    ->join('aramisc_staffs', 'aramisc_vehicles.driver_id', '=', 'aramisc_staffs.id')
                    ->where('aramisc_staffs.school_id', Auth::user()->school_id)
                    ->first();

                $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();
                $student_optional_subject = AramiscOptionalSubjectAssign::where('student_id', $student_detail->id)
                    ->where('session_id', '=', $student_detail->session_id)
                    ->first();

                $fees_assigneds = $student_detail->feesAssign;
                $invoice_settings = FeesInvoice::where('school_id', Auth::user()->school_id)->first();
                $fees_discounts = $student_detail->feesAssignDiscount;

                $documents = AramiscStudentDocument::where('student_staff_id', $student_detail->id)
                    ->where('type', 'stu')
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $timelines = AramiscStudentTimeline::where('staff_student_id', $student_detail->id)
                    ->where('type', 'stu')
                    ->where('academic_id', getAcademicId())
                    ->where('visible_to_student', 1)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)
                    ->where('section_id', $student_detail->section_id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $grades = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $maxgpa = $grades->max('gpa');

                $failgpa = $grades->min('gpa');

                $failgpaname = $grades->where('gpa', $failgpa)
                    ->first();

                $academic_year = $student_detail->academicYear;

                $exam_terms = AramiscExamType::where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();
                $custom_field_data = $student_detail->custom_field;

                if (!is_null($custom_field_data)) {
                    $custom_field_values = json_decode($custom_field_data);
                } else {
                    $custom_field_values = null;
                }

                $paymentMethods = AramiscPaymentMethhod::whereNotIn('method', ["Cash", "Wallet"])
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $bankAccounts = AramiscBankAccount::where('active_status', 1)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                if (moduleStatusCheck('Wallet')) {
                    $walletAmounts = WalletTransaction::where('user_id', Auth::user()->id)
                        ->where('school_id', Auth::user()->school_id)
                        ->get();
                } else {
                    $walletAmounts = null;
                }

                $custom_field_data = $student_detail->custom_field;

                if (!is_null($custom_field_data)) {
                    $custom_field_values = json_decode($custom_field_data);
                } else {
                    $custom_field_values = null;
                }

                $data['bank_info'] = AramiscPaymentMethhod::where('method', 'Bank')->where('school_id', Auth::user()->school_id)->first();
                $data['cheque_info'] = AramiscPaymentMethhod::where('method', 'Cheque')->where('school_id', Auth::user()->school_id)->first();

                $leave_details = AramiscLeaveRequest::where('staff_id', $student_detail->user_id)->where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $payment_gateway = AramiscPaymentMethhod::first();
                $student = AramiscStudent::where('id', $id)->where('parent_id', $parent_info->id)->first();

                $now = Carbon::now();
                $year = $now->year;
                $month  = $now->month;
                $days = cal_days_in_month(CAL_GREGORIAN, $now->month, $now->year);

                $studentRecord = StudentRecord::where('student_id', $student_detail->id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $student_detail->school_id)
                    ->get();


                $attendance = AramiscStudentAttendance::where('student_id', $student_detail->id)
                    ->whereIn('academic_id', $studentRecord->pluck('academic_id'))
                    ->whereIn('student_record_id', $studentRecord->pluck('id'))
                    ->get();

                $subjectAttendance = AramiscSubjectAttendance::with('student')
                    ->whereIn('academic_id', $studentRecord->pluck('academic_id'))
                    ->whereIn('student_record_id', $studentRecord->pluck('id'))
                    ->where('school_id', $student_detail->school_id)
                    ->get();

                $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', $id)->with('incident', 'user', 'academicYear')->get() : null;
                $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();

                if (moduleStatusCheck('University')) {
                    $student_id = $student_detail->id;
                    $studentDetails = AramiscStudent::find($student_id);
                    $studentRecordDetails = StudentRecord::where('student_id', $student_id);
                    $studentRecords = $studentRecordDetails->distinct('un_academic_id')->get();
                    $print = 1;

                    return view('backEnd.parentPanel.my_children', compact('student_detail', 'fees_assigneds', 'driver', 'fees_discounts', 'exams', 'documents', 'timelines', 'grades', 'exam_terms', 'academic_year', 'leave_details', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'failgpaname', 'custom_field_values', 'walletAmounts', 'bankAccounts', 'paymentMethods', 'records', 'studentDetails', 'studentRecordDetails', 'studentRecords', 'print', 'payment_gateway', 'student', 'data', 'invoice_settings', 'studentBehaviourRecords', 'behaviourRecordSetting'));
                } else {
                    return view('backEnd.parentPanel.my_children', compact('student_detail', 'fees_assigneds', 'driver', 'fees_discounts', 'exams', 'documents', 'timelines', 'grades', 'exam_terms', 'academic_year', 'leave_details', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'failgpaname', 'custom_field_values', 'walletAmounts', 'bankAccounts', 'paymentMethods', 'records', 'payment_gateway', 'student', 'data', 'invoice_settings', 'attendance', 'subjectAttendance', 'days', 'year', 'month', 'studentBehaviourRecords', 'behaviourRecordSetting'));
                }
            } else {
                Toastr::warning('Invalid Student ID', 'Invalid');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function onlineExamination($id)
    {

        try {
            // $student = Auth::user()->student;
            $student = AramiscStudent::findOrfail($id);
            $records = studentRecords(null, $student->id)->get();

            $time_zone_setup = AramiscGeneralSettings::join('aramisc_time_zones', 'aramisc_time_zones.id', '=', 'aramisc_general_settings.time_zone_id')
                ->where('school_id', Auth::user()->school_id)->first();
            date_default_timezone_set($time_zone_setup->time_zone);
            // $now = date('H:i:s');

            // ->where('start_time', '<', $now)
            if (moduleStatusCheck('OnlineExam') == true) {
                $online_exams = AramiscOnlineExam::where('active_status', 1)->where('status', 1)->where('class_id', $student->class_id)->where('section_id', $student->section_id)
                    ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

                $marks_assigned = AramiscStudentTakeOnlineExam::whereIn('online_exam_id', $online_exams->pluck('id')->toArray())->where('student_id', $student->id)->where('status', 2)
                    ->where('school_id', Auth::user()->school_id)->pluck('online_exam_id')->toArray();
            } else {
                $online_exams = AramiscOnlineExam::where('active_status', 1)->where('status', 1)->where('class_id', $student->class_id)->where('section_id', $student->section_id)
                    ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

                $marks_assigned = AramiscStudentTakeOnlineExam::whereIn('online_exam_id', $online_exams->pluck('id')->toArray())->where('student_id', $student->id)->where('status', 2)
                    ->where('school_id', Auth::user()->school_id)->pluck('online_exam_id')->toArray();
            }

            return view('backEnd.parentPanel.parent_online_exam', compact('online_exams', 'marks_assigned', 'student', 'records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function onlineExaminationResult($id)
    {

        try {
            if (moduleStatusCheck('OnlineExam') == true) {
                $result_views = AramiscStudentTakeOnlineExam::where('active_status', 1)->where('status', 2)
                    ->where('academic_id', getAcademicId())
                    ->where('student_id', $id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            } else {
                $result_views = AramiscStudentTakeOnlineExam::where('active_status', 1)->where('status', 2)
                    ->where('academic_id', getAcademicId())
                    ->where('student_id', $id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }
            $records = studentRecords(null, $id)->get();

            return view('backEnd.parentPanel.parent_online_exam_result', compact('result_views', 'records', 'id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function parentAnswerScript($exam_id, $s_id)
    {
        try {
            if (moduleStatusCheck('OnlineExam') == true) {
                $take_online_exam = AramiscStudentTakeOnlineExam::where('online_exam_id', $exam_id)->where('student_id', $s_id)->where('school_id', Auth::user()->school_id)->first();
            } else {
                $take_online_exam = AramiscStudentTakeOnlineExam::where('online_exam_id', $exam_id)->where('student_id', $s_id)->where('school_id', Auth::user()->school_id)->first();
            }

            return view('backEnd.examination.online_answer_view_script_modal', compact('take_online_exam', 's_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function parentLeave($id)
    {

        try {
            $student = AramiscStudent::findOrfail($id);
            $apply_leaves = AramiscLeaveRequest::where('staff_id', '=', $student->user_id)
                ->join('aramisc_leave_defines', 'aramisc_leave_defines.id', '=', 'aramisc_leave_requests.leave_define_id')
                ->join('aramisc_leave_types', 'aramisc_leave_types.id', '=', 'aramisc_leave_defines.type_id')
                ->where('aramisc_leave_requests.academic_id', getAcademicId())
                ->where('aramisc_leave_requests.school_id', Auth::user()->school_id)->get();

            return view('backEnd.parentPanel.parent_leave', compact('apply_leaves', 'student'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function leaveApply(Request $request)
    {
        try {
            $user = Auth::user();
            $std_id = AramiscStudent::leftjoin('aramisc_parents', 'aramisc_parents.id', 'aramisc_students.parent_id')
                ->where('aramisc_parents.user_id', $user->id)
                ->where('aramisc_students.active_status', 1)
                ->where('aramisc_students.school_id', Auth::user()->school_id)
                ->select('aramisc_students.user_id')
                ->get();
            $my_leaves = AramiscLeaveDefine::where('role_id', 2)->whereIn('user_id', $std_id->pluck('user_id'))->where('school_id', Auth::user()->school_id)->get();
            $apply_leaves = AramiscLeaveRequest::whereIn('staff_id', $std_id->pluck('user_id'))->where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $leave_types = AramiscLeaveDefine::where('role_id', 2)->where('active_status', 1)->whereIn('user_id', $std_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.parentPanel.apply_leave', compact('apply_leaves', 'leave_types', 'my_leaves', 'user'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function leaveStore(Request $request)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $input = $request->all();
        $validator = Validator::make($input, [
            'student_id' => "required",
            'apply_date' => "required",
            'leave_type' => "required",
            'leave_from' => 'required|before_or_equal:leave_to',
            'leave_to' => "required",
            'attach_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
            'reason' => "required",
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
            $input = $request->all();
            $fileName = "";
            if ($request->file('attach_file') != "") {
                //                'attach_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
                $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                $file = $request->file('attach_file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }
            $leaveDefine = AramiscLeaveDefine::where('user_id', $request->student_id)->where('type_id', $request->leave_type)->first();
            if (!$leaveDefine) {
                Toastr::warning('Please Add Leave Define First', 'Warning');
                return redirect()->back();
            }

            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $request->student_id;
            $apply_leave->role_id = 2;
            $apply_leave->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $apply_leave->leave_define_id = $request->leave_type;
            $apply_leave->type_id = $request->leave_type;
            $apply_leave->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->reason;
            $apply_leave->file = $fileName;
            $apply_leave->school_id = Auth::user()->school_id;
            $apply_leave->academic_id = getAcademicId();
            $result = $apply_leave->save();

            $studentInfo = AramiscStudent::where('user_id', $request->student_id)->first();
            $data['to_date'] = $apply_leave->leave_to;
            $data['name'] = $apply_leave->user->full_name;
            $data['from_date'] = $apply_leave->leave_from;
            $data['class'] = $studentInfo->studentRecord->class->class_name;
            $data['section'] = $studentInfo->studentRecord->section->section_name;
            $this->sent_notifications('Leave_Apply', [$studentInfo->user_id], $data, ['Parent']);

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
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function viewLeaveDetails(Request $request, $id)
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
            return view('backEnd.parentPanel.viewLeaveDetails', compact('leaveDetails', 'apply'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function leaveEdit($id)
    {
    }

    public function pendingLeave(Request $request)
    {
        try {
            $user = Auth::user();
            $std_id = AramiscStudent::leftjoin('aramisc_parents', 'aramisc_parents.id', 'aramisc_students.parent_id')
                ->where('aramisc_parents.user_id', $user->id)
                ->where('aramisc_students.active_status', 1)
                ->where('aramisc_students.academic_id', getAcademicId())
                ->where('aramisc_students.school_id', Auth::user()->school_id)
                ->select('aramisc_students.user_id')
                ->get();

            $apply_leaves = AramiscLeaveRequest::whereIn('staff_id', $std_id->pluck('user_id'))->where('role_id', 2)->where([['active_status', 1], ['approve_status', 'P']])->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();


            return view('backEnd.parentPanel.pending_leave', compact('apply_leaves'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function parentLeaveEdit(request $request, $id)
    {
        try {
            $user = Auth::user();
            if ($user) {
                $my_leaves = AramiscLeaveDefine::where('role_id', 2)->where('school_id', Auth::user()->school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            } else {
                $my_leaves = AramiscLeaveDefine::where('role_id', $request->role_id)->where('school_id', Auth::user()->school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $request->role_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $request->role_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
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
            return view('backEnd.parentPanel.apply_leave', compact('apply_leave', 'apply_leaves', 'leave_types', 'my_leaves', 'user'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
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
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
            ]);
        } else {
            $validator = Validator::make($input, [
                'apply_date' => "required",
                'leave_type' => "required",
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => "required",
                'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
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
                $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                $file = $request->file('attach_file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $apply_leave = AramiscLeaveRequest::find($request->id);
                if (file_exists($apply_leave->file)) {
                    unlink($apply_leave->file);
                }

                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }

            $user = Auth()->user();
            $apply_leave = AramiscLeaveRequest::find($request->id);
            $apply_leave->staff_id = $request->student_id;
            $apply_leave->role_id = 2;
            $apply_leave->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $apply_leave->leave_define_id = $request->leave_type;
            $apply_leave->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->reason;
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
                    return redirect('parent-apply-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function DeleteLeave(Request $request, $id)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            $apply_leave = AramiscLeaveRequest::find($id);
            if ($apply_leave->file != "") {
                if (file_exists($apply_leave->file)) {
                    unlink($apply_leave->file);
                }
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
                    return redirect('parent-apply-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function classRoutine($id)
    {
        try {
            $student_detail = AramiscStudent::where('id', $id)->first();

            $class_id = $student_detail->class_id;
            $section_id = $student_detail->section_id;
            $aramisc_weekends = AramiscWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $class_times = AramiscClassTime::where('type', 'class')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $records = $student_detail->studentRecords;
            return view('backEnd.parentPanel.class_routine', compact('class_times', 'class_id', 'section_id', 'aramisc_weekends', 'student_detail', 'records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function attendance($id)
    {
        try {
            $student_detail = AramiscStudent::where('id', $id)->first();
            $academic_years = AramiscAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.parentPanel.attendance', compact('student_detail', 'academic_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function attendanceSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'month' => 'required',
            'year' => 'required',
        ]);
        if ($validator->fails()) {
            Toastr::error('Please fill the required fields', 'Failed');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $student_detail = AramiscStudent::where('id', $request->student_id)->first();
            $year = $request->year;
            $month = $request->month;
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            $records = studentRecords(null, $student_detail->id)->with('studentAttendance')->get();
            $attendances = AramiscStudentAttendance::where('student_id', $student_detail->id)->where('academic_id', getAcademicId())->where('attendance_date', 'like', $request->year . '-' . $request->month . '%')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $academic_years = AramiscAcademicYear::where('active_status', '=', 1)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.parentPanel.attendance', compact('records', 'days', 'year', 'month', 'current_day', 'student_detail', 'academic_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function attendancePrint($student_id, $id, $month, $year)
    {
        try {
            $student_detail = AramiscStudent::where('id', $student_id)->first();
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            //$students = AramiscStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id',Auth::user()->school_id)->get();
            $attendances = AramiscStudentAttendance::where('student_record_id', $id)->where('student_id', $student_detail->id)->where('attendance_date', 'like', $year . '-' . $month . '%')->where('school_id', Auth::user()->school_id)->get();
            $customPaper = array(0, 0, 700.00, 1000.80);
            $pdf = Pdf::loadView(
                'backEnd.parentPanel.attendance_print',
                [
                    'attendances' => $attendances,
                    'days' => $days,
                    'year' => $year,
                    'month' => $month,
                    'current_day' => $current_day,
                    'student_detail' => $student_detail,
                ]
            )->setPaper('A4', 'landscape');
            return $pdf->stream('my_child_attendance.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examinationSchedule($id)
    {
        try {
            $user = Auth::user();
            $parent = AramiscParent::where('user_id', $user->id)->first();
            $student_detail = AramiscStudent::where('id', $id)->first();
            $student_id = $student_detail->id;
            $records = studentRecords(null, $student_detail->id)->get();
            return view('backEnd.parentPanel.parent_exam_schedule', compact('student_id', 'records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutinePrint($class_id, $section_id, $exam_term_id)
    {

        try {

            $exam_type_id = $exam_term_id;
            $exam_type = AramiscExamType::find($exam_type_id)->title;
            $academic_id = AramiscExamType::find($exam_type_id)->academic_id;
            $academic_year = AramiscAcademicYear::find($academic_id);
            $class_name = AramiscClass::find($class_id)->class_name;
            $section_name = AramiscSection::find($section_id)->section_name;
            $exam_schedules = AramiscExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)
                ->where('exam_term_id', $exam_type_id)->orderBy('date', 'ASC')->get();
            $print = request()->print;
            return view(
                'backEnd.examination.exam_schedule_print',
                [
                    'exam_schedules' => $exam_schedules,
                    'exam_type' => $exam_type,
                    'class_name' => $class_name,
                    'academic_year' => $academic_year,
                    'section_name' => $section_name,
                    'print' => $print,
                ]
            );
            $pdf = Pdf::loadView(
                'backEnd.examination.exam_schedule_print',
                [
                    'exam_schedules' => $exam_schedules,
                    'exam_type' => $exam_type,
                    'class_name' => $class_name,
                    'academic_year' => $academic_year,
                    'section_name' => $section_name,

                ]
            )->setPaper('A4', 'landscape');
            return $pdf->stream('EXAM_SCHEDULE.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function parentBookList()
    {

        try {
            $books = AramiscBook::where('active_status', 1)
                ->orderBy('id', 'DESC')
                ->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.parentPanel.parentBookList', compact('books'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function parentBookIssue()
    {
        try {
            $user = Auth::user();
            $parent_detail = AramiscParent::where('user_id', $user->id)->first();

            $library_member = AramiscLibraryMember::where('member_type', 3)->where('student_staff_id', $parent_detail->user_id)->first();
            if (empty($library_member)) {
                Toastr::error('You are not library member ! Please contact with librarian', 'Failed');
                return redirect()->back();
            }
            $issueBooks = AramiscBookIssue::where('member_id', $library_member->student_staff_id)
                ->leftjoin('aramisc_books', 'aramisc_books.id', 'aramisc_book_issues.book_id')
                ->leftjoin('library_subjects', 'library_subjects.id', 'aramisc_books.book_subject_id')
                /* ->where('aramisc_book_issues.issue_status', 'I') */->where('aramisc_book_issues.school_id', Auth::user()->school_id)->get();

            return view('backEnd.parentPanel.parentBookIssue', compact('issueBooks'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function examinationScheduleSearch(Request $request)
    {

        try {
            $request->validate([
                'exam' => 'required',
            ]);
            $user = Auth::user();
            $parent = AramiscParent::where('user_id', $user->id)->first();
            $student_detail = AramiscStudent::find($request->student_id);
            $records = studentRecords(null, $student_detail->id)->get();
            $aramiscExam = AramiscExam::findOrFail($request->exam);
            $student_id = $student_detail->id;
            $assign_subjects = AramiscAssignSubject::where('class_id', $aramiscExam->class_id)->where('section_id', $aramiscExam->section_id)
                ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            if ($assign_subjects->count() == 0) {
                Toastr::error('No Subject Assigned. Please assign subjects in this class.', 'Failed');
                return redirect()->back();
            }

            $exams = AramiscExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_id = $aramiscExam->class_id;
            $section_id = $aramiscExam->section_id;
            $exam_id = $aramiscExam->id;
            $exam_type_id = $aramiscExam->exam_type_id;
            $exam_periods = AramiscClassTime::where('type', 'exam')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_schedule_subjects = "";
            $assign_subject_check = "";

            $exam_routines = AramiscExamSchedule::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('exam_term_id', $exam_type_id)->orderBy('date', 'ASC')->get();

            return view('backEnd.parentPanel.parent_exam_schedule', compact('exams', 'assign_subjects', 'class_id', 'section_id', 'exam_id', 'exam_schedule_subjects', 'assign_subject_check', 'records', 'exam_type_id', 'exam_routines', 'exam_periods', 'student_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Empty Search');
            return redirect()->route('parent_exam_schedule', $request->student_id);
        }
    }
    public function examination($id)
    {
        try {
            $student_detail = AramiscStudent::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($id);
            $records = studentRecords(null, $student_detail->id)->get();
            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();

            $student_optional_subject = AramiscOptionalSubjectAssign::where('student_id', $student_detail->id)
                ->where('session_id', '=', $student_detail->session_id)
                ->first();

            $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)
                ->where('section_id', $student_detail->section_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $grades = AramiscMarksGrade::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $failgpa = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $failgpaname = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $failgpa)
                ->first();
            $maxgpa = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->max('gpa');

            $exam_terms = AramiscExamType::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return view('backEnd.parentPanel.student_result', compact('student_detail', 'exams', 'grades', 'exam_terms', 'failgpaname', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function subjects($id)
    {
        try {
            $student_detail = AramiscStudent::where('id', $id)->first();
            $records = studentRecords(null, $student_detail->id)->get();
            return view('backEnd.parentPanel.subject', compact('records', 'student_detail'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function teacherList($id)
    {
        try {
            $student_detail = AramiscStudent::where('id', $id)->first();
            $records = studentRecords(null, $student_detail->id)->get();
            $teacherEvaluationSetting = TeacherEvaluationSetting::find(1);
            return view('backEnd.parentPanel.teacher_list', compact('records', 'student_detail', 'teacherEvaluationSetting'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function transport($id)
    {
        try {
            $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();
            $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', $id)->with('incident', 'user', 'academicYear')->get() : null;
            $student_detail = AramiscStudent::where('id', $id)->first();
            $routes = AramiscAssignVehicle::join('aramisc_vehicles', 'aramisc_assign_vehicles.vehicle_id', 'aramisc_vehicles.id')
                ->join('aramisc_students', 'aramisc_vehicles.id', 'aramisc_students.vechile_id')
                ->join('aramisc_parents', 'aramisc_parents.id', 'aramisc_students.parent_id')
                ->where('aramisc_assign_vehicles.active_status', 1)
                ->where('aramisc_parents.user_id', Auth::user()->id)
                ->where('aramisc_assign_vehicles.school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.parentPanel.transport', compact('routes', 'student_detail', 'behaviourRecordSetting', 'studentBehaviourRecords'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function dormitory($id)
    {
        try {
            $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();
            $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', $id)->with('incident', 'user', 'academicYear')->get() : null;
            $student_detail = AramiscStudent::where('id', $id)->first();
            $room_lists = AramiscRoomList::where('active_status', 1)->where('id', $student_detail->room_id)->where('school_id', Auth::user()->school_id)->get();
            $room_lists = $room_lists->groupBy('dormitory_id');
            $room_types = AramiscRoomType::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $dormitory_lists = AramiscDormitoryList::where('active_status', 1)->where('id', $student_detail->dormitory_id)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.parentPanel.dormitory', compact('room_lists', 'room_types', 'dormitory_lists', 'student_detail', 'behaviourRecordSetting', 'studentBehaviourRecords'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function homework($id)
    {
        try {
            $student_detail = AramiscStudent::where('id', $id)->first();

            if (moduleStatusCheck('University')) {
                $records = $student_detail->studentRecords;
            } else {
                $records = studentRecords(null, $student_detail->id)->with('homework')->get();
            }
            return view('backEnd.parentPanel.homework', compact('records', 'student_detail'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function homeworkView($class_id, $section_id, $homework_id)
    {
        try {
            $homeworkDetails = AramiscHomework::where('class_id', '=', $class_id)->where('section_id', '=', $section_id)->where('id', '=', $homework_id)->first();
            return view('backEnd.parentPanel.homeworkView', compact('homeworkDetails', 'homework_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function unStudentHomeworkView($sem_label_id, $homework)
    {
        try {
            $homeworkDetails = AramiscHomework::find($homework);
            $homework_id = $homework;
            return view('backEnd.studentPanel.studentHomeworkView', compact('homeworkDetails', 'homework_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function parentNoticeboard()
    {
        try {
            $allNotices = AramiscNoticeBoard::where('active_status', 1)->where('inform_to', 'LIKE', '%3%')
                ->orderBy('id', 'DESC')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.parentPanel.parentNoticeboard', compact('allNotices'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function childListApi(Request $request, $id)
    {
        try {
            $parent = AramiscParent::where('user_id', $id)->first();
            $student_info = DB::table('aramisc_students')
                ->join('aramisc_classes', 'aramisc_classes.id', '=', 'aramisc_students.class_id')
                ->join('aramisc_sections', 'aramisc_sections.id', '=', 'aramisc_students.section_id')
                // ->join('aramisc_exams','aramisc_exams.id','=','aramisc_exam_types.id' )
                // ->join('aramisc_subjects','aramisc_subjects.id','=','aramisc_result_stores.subject_id' )

                ->where('aramisc_students.parent_id', '=', $parent->id)

                ->select('aramisc_students.user_id', 'student_photo', 'aramisc_students.full_name as student_name', 'class_name', 'section_name', 'roll_no')

                ->where('aramisc_students.school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($student_info, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function childProfileApi(Request $request, $id)
    {
        try {
            $student_detail = AramiscStudent::where('id', $id)->first();
            $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $fees_assigneds = AramiscFeesAssign::where('student_id', $student_detail->id)->where('school_id', Auth::user()->school_id)->get();
            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $student_detail->id)->where('school_id', Auth::user()->school_id)->get();
            $documents = AramiscStudentDocument::where('student_staff_id', $student_detail->id)->where('type', 'stu')->where('school_id', Auth::user()->school_id)->get();
            $timelines = AramiscStudentTimeline::where('staff_student_id', $student_detail->id)->where('type', 'stu')->where('visible_to_student', 1)->where('school_id', Auth::user()->school_id)->get();
            $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('school_id', Auth::user()->school_id)->get();
            $grades = AramiscMarksGrade::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

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

            //return view('backEnd.studentPanel.my_profile', compact('student_detail', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function collectFeesChildApi(Request $request, $id)
    {
        try {
            $student = AramiscStudent::where('id', $id)->first();
            $fees_assigneds = AramiscFeesAssign::where('student_id', $id)->orderBy('id', 'desc')->where('school_id', Auth::user()->school_id)->get();

            $fees_assigneds2 = DB::table('aramisc_fees_assigns')
                ->select('aramisc_fees_types.id as fees_type_id', 'aramisc_fees_types.name', 'aramisc_fees_masters.date as due_date', 'aramisc_fees_masters.amount as amount')
                ->join('aramisc_fees_masters', 'aramisc_fees_masters.id', '=', 'aramisc_fees_assigns.fees_master_id')
                ->join('aramisc_fees_types', 'aramisc_fees_types.id', '=', 'aramisc_fees_masters.fees_type_id')
                ->join('aramisc_fees_payments', 'aramisc_fees_payments.fees_type_id', '=', 'aramisc_fees_masters.fees_type_id')
                ->where('aramisc_fees_assigns.student_id', $student->id)
                //->where('aramisc_fees_payments.student_id', $student->id)
                ->where('aramisc_fees_assigns.school_id', Auth::user()->school_id)->get();
            $i = 0;
            return $fees_assigneds2;
            foreach ($fees_assigneds2 as $row) {
                $d[$i]['fees_name'] = $row->name;
                $d[$i]['due_date'] = $row->due_date;
                $d[$i]['amount'] = $row->amount;
                $d[$i]['paid'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('amount');
                $d[$i]['fine'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('fine');
                $d[$i]['discount_amount'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('discount_amount');
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine']) - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }
            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->where('school_id', Auth::user()->school_id)->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = AramiscFeesPayment::where('active_status', 1)->select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->first();
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
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function classRoutineApi(Request $request, $id)
    {
        try {
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
            $class_times = AramiscClassTime::where('type', 'class')->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_detail'] = $student_detail->toArray();
                // $data['class_id'] = $class_id;
                // $data['section_id'] = $section_id;
                // $data['aramisc_weekends'] = $aramisc_weekends->toArray();
                // $data['class_times'] = $class_times->toArray();

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
                        ])->where('aramisc_classes.school_id', Auth::user()->school_id)->get();
                }

                return ApiBaseMethod::sendResponse($data, null);
            }

            //return view('backEnd.studentPanel.class_routine', compact('class_times', 'class_id', 'section_id', 'aramisc_weekends'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function childHomework(Request $request, $id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $student_detail = AramiscStudent::where('id', $id)->first();

                $class_id = $student_detail->class->id;
                $subject_list = AramiscAssignSubject::where([['class_id', $class_id], ['section_id', $student_detail->section_id]])->where('school_id', Auth::user()->school_id)->get();

                $i = 0;
                foreach ($subject_list as $subject) {
                    $homework_subject_list[$subject->subject->subject_name] = $subject->subject->subject_name;
                    $allList[$subject->subject->subject_name] =
                        DB::table('aramisc_homeworks')
                        ->select('aramisc_homeworks.description', 'aramisc_subjects.subject_name', 'aramisc_homeworks.homework_date', 'aramisc_homeworks.submission_date', 'aramisc_homeworks.evaluation_date', 'aramisc_homeworks.file', 'aramisc_homeworks.marks', 'aramisc_homework_students.complete_status as status')
                        ->leftjoin('aramisc_homework_students', 'aramisc_homework_students.homework_id', '=', 'aramisc_homeworks.id')
                        ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_homeworks.subject_id')
                        ->where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('subject_id', $subject->subject_id)->where('aramisc_homeworks.school_id', Auth::user()->school_id)->get();
                }

                $homeworkLists = AramiscHomework::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('school_id', Auth::user()->school_id)->get();
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
            // return view('backEnd.studentPanel.student_homework', compact('homeworkLists', 'student_detail'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
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
        try {
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
                ->where('school_id', Auth::user()->school_id)->get();

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
            //Test
            //return view('backEnd.studentPanel.student_attendance', compact('attendances', 'days', 'year', 'month', 'current_day'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function aboutApi(request $request)
    {
        try {
            $about = DB::table('aramisc_general_settings')
                ->join('aramisc_languages', 'aramisc_general_settings.language_id', '=', 'aramisc_languages.id')
                ->join('aramisc_academic_years', 'aramisc_general_settings.session_id', '=', 'aramisc_academic_years.id')
                ->join('aramisc_about_pages', 'aramisc_general_settings.school_id', '=', 'aramisc_about_pages.school_id')
                ->select('main_description', 'school_name', 'site_title', 'school_code', 'address', 'phone', 'email', 'logo', 'aramisc_languages.language_name', 'year as session', 'copyright_text')
                ->where('aramisc_general_settings.school_id', Auth::user()->school_id)->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($about, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function StudentDownload($file_name)
    {
        try {
            $file = public_path() . '/uploads/student/timeline/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
