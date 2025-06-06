<?php

namespace App\Http\Controllers\Student;

use File;
use App\User;
use App\AramiscBook;
use App\AramiscExam;
use ZipArchive;
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
use App\AramiscMarksGrade;
use App\AramiscOnlineExam;
use App\ApiBaseMethod;
use App\AramiscBankAccount;
use App\AramiscLeaveDefine;
use App\AramiscNoticeBoard;
use App\AramiscAcademicYear;
use App\AramiscExamSchedule;
use App\AramiscLeaveRequest;
use App\AramiscNotification;
use App\AramiscStudentGroup;
use App\AramiscAssignSubject;
use App\AramiscAssignVehicle;
use App\AramiscDormitoryList;
use App\AramiscLibraryMember;
use Barryvdh\DomPDF\PDF;
use App\AramiscPaymentMethhod;
use App\AramiscGeneralSettings;
use App\AramiscStudentCategory;
use App\AramiscStudentDocument;
use App\AramiscStudentTimeline;
use App\AramiscStudentAttendance;
use App\AramiscSubjectAttendance;
use App\Traits\CustomFields;
use Illuminate\Http\Request;
use App\Models\AramiscCustomField;
use App\Models\StudentRecord;
use App\AramiscExamScheduleSubject;
use App\AramiscClassOptionalSubject;
use App\AramiscTeacherUploadContent;
use App\AramiscOptionalSubjectAssign;
use App\AramiscStudentTakeOnlineExam;
use App\AramiscUploadHomeworkContent;
use App\Traits\NotificationSend;
use App\Models\AramiscCalendarSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\TeacherEvaluationSetting;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\AramiscStudentRegistrationField;
use Illuminate\Support\Facades\Notification;
use Modules\RolePermission\Entities\AramiscRole;
use Modules\Wallet\Entities\WalletTransaction;
use App\Notifications\LeaveApprovedNotification;
use Modules\OnlineExam\Entities\OnlineExam;
use Modules\University\Entities\UnAssignSubject;
use Modules\University\Entities\UnSemesterLabel;
use Modules\University\Entities\UniversitySetting;
use Modules\BehaviourRecords\Entities\AssignIncident;
use App\Http\Controllers\AramiscAcademicCalendarController;
use App\Notifications\StudentHomeworkSubmitNotification;
use Modules\BehaviourRecords\Entities\BehaviourRecordSetting;
use App\Http\Requests\Admin\StudentInfo\AramiscStudentAdmissionRequest;

class AramiscStudentPanelController extends Controller
{
    use NotificationSend;
    use CustomFields;
    public function studentMyAttendanceSearchAPI(Request $request, $id = null)
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
            $student_detail = AramiscStudent::where('user_id', $id)->first();

            $year = $request->year;
            $month = $request->month;
            if ($month < 10) {
                $month = '0' . $month;
            }
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $month, $request->year);
            $days2 = '';
            if ($month != 1) {
                $days2 = cal_days_in_month(CAL_GREGORIAN, $month - 1, $request->year);
            } else {
                $days2 = cal_days_in_month(CAL_GREGORIAN, $month, $request->year);
            }
            // return  $days2;
            $previous_month = $month - 1;
            $previous_date = $year . '-' . $previous_month . '-' . $days2;
            $previousMonthDetails['date'] = $previous_date;
            $previousMonthDetails['day'] = $days2;
            $previousMonthDetails['week_name'] = date('D', strtotime($previous_date));
            $attendances = AramiscStudentAttendance::where('student_id', $student_detail->id)
                ->where('attendance_date', 'like', '%' . $request->year . '-' . $month . '%')
                ->select('attendance_type', 'attendance_date')
                ->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.studentPanel.student_attendance', compact('attendances', 'days', 'year', 'month', 'current_day'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentMyAttendanceSearch(Request $request, $id = null)
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
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $login_id = $id;
            } else {
                $login_id = Auth::user()->id;
            }
            $student_detail = AramiscStudent::where('user_id', $login_id)->first();

            $year = $request->year;
            $month = $request->month;
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            $records = studentRecords(null, $student_detail->id)->with('studentAttendance')->get();
            $academic_years = AramiscAcademicYear::where('active_status', '=', 1)->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.studentPanel.student_attendance', compact('days', 'year', 'month', 'current_day', 'student_detail', 'academic_years', 'records'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentMyAttendancePrint($id, $month, $year)
    {
        try {
            $login_id = Auth::user()->id;
            $student_detail = AramiscStudent::where('user_id', $login_id)->first();
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $attendances = AramiscStudentAttendance::where('student_record_id', $id)->where('student_id', $student_detail->id)->where('academic_id', getAcademicId())->where('attendance_date', 'like', $year . '-' . $month . '%')->where('school_id', Auth::user()->school_id)->get();
            $customPaper = array(0, 0, 700.00, 1000.80);
            return view('backEnd.studentPanel.my_attendance_print', compact('attendances', 'days', 'year', 'month', 'current_day', 'student_detail'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentProfile(Request $request, $id = null)
    {
        try {

            $student_detail = auth()->user()->student->load('studentRecords.class', 'studentDocument', 'academicYear', 'defaultClass.class', 'defaultClass.section', 'gender');
            $student = $student_detail;
            $bank_cheque_info = AramiscPaymentMethhod::where('school_id', Auth::user()->school_id)->get();
            $data['bank_info'] = $bank_cheque_info->where('method', 'Bank')->first();
            $data['cheque_info'] =  $bank_cheque_info->where('method', 'Cheque')->first();
            $records = $student_detail->studentRecords->load('directFeesInstallments.installment', 'directFeesInstallments.payments.user');

            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();

            $student_optional_subject = $student_detail->subjectAssign;

            $driver = AramiscVehicle::where('aramisc_vehicles.id', '=', $student_detail->vechile_id)
                ->join('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_vehicles.driver_id')
                ->first();

            $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)
                ->where('school_id', Auth::user()->school_id)->where('id', '!=', $student_detail->id)->whereNotNull('parent_id')
                ->get();

            $fees_assigneds = $student_detail->feesAssign;
            $fees_discounts = $student_detail->feesAssignDiscount;

            $documents = $student_detail->studentDocument;

            $timelines = AramiscStudentTimeline::where('staff_student_id', $student_detail->id)
                ->where('type', 'stu')
                ->where('visible_to_student', 1)
                ->where('academic_id', getAcademicId())
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

            $failgpaname = $grades->where('gpa', $failgpa)->first();

            $exam_terms = AramiscExamType::with('examSettings')->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $leave_details = AramiscLeaveRequest::where('staff_id', Auth::user()->id)
                ->where('role_id', Auth::user()->role_id)
                ->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $result_views = AramiscStudentTakeOnlineExam::where('active_status', 1)
                ->where('status', 2)
                ->where('academic_id', getAcademicId())
                ->where('student_id', @Auth::user()->student->id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $all_paymentMethods = AramiscPaymentMethhod::whereNotIn('method', ["Cash", "Wallet"])
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $paymentMethods = $all_paymentMethods->whereNotIn('method', ["Cash", "Wallet"])->load('gatewayDetail');

            $bankAccounts = AramiscBankAccount::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if (moduleStatusCheck('Wallet')) {
                $walletAmounts = WalletTransaction::where('user_id', Auth::user()->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            } else {
                $walletAmounts = 0;
            }

            $custom_field_data = $student_detail->custom_field;

            if (!is_null($custom_field_data)) {
                $custom_field_values = json_decode($custom_field_data);
            } else {
                $custom_field_values = null;
            }

            $academic_year = $student_detail->academicYear;

            $custom_field_data = $student_detail->custom_field;

            if (!is_null($custom_field_data)) {
                $custom_field_values = json_decode($custom_field_data);
            } else {
                $custom_field_values = null;
            }
            $departmentSubjects = null;
            $next_subjects = null;
            $next_semester_label = null;
            $canChoose = false;
            $unSettings = null;
            if (moduleStatusCheck('University')) {
                $lastRecord = studentRecords(null, $student_detail->id)
                    ->where('is_default', 1)
                    ->orderBy('id', 'DESC')->first();
                $labelIds = StudentRecord::where('student_id', $student_detail->id)
                    ->where('school_id', auth()->user()->school_id)
                    ->pluck('un_semester_label_id')->toArray();
                    $lastRecordCreatedDate= date('Y-m-d');
                if ($lastRecord) {
                    $next_semester_label = UnSemesterLabel::whereNotIn('id', $labelIds)
                        ->where('id', '!=', $lastRecord->un_semester_label_id)
                        ->first();

                    $next_subjects = UnAssignSubject::where('school_id', auth()->user()->school_id)
                        ->where('un_semester_label_id', $lastRecord->un_semester_label_id)
                        ->get();
                    $departmentSubjects = $lastRecord->withOutPreSubject;
                   
                    $lastRecordCreatedDate = $student_detail->lastRecord->value('created_at')->format('Y-m-d');
                }

                $unSettings = UniversitySetting::where('school_id', auth()->user()->school_id)
                    ->first();



                if ($unSettings) {
                    if ($unSettings->choose_subject == 1) {
                        $endDate = Carbon::parse($lastRecordCreatedDate)->addDay($unSettings->end_day)->format('Y-m-d');
                        $now = Carbon::now()->format('Y-m-d');
                        if ($now <= $endDate) {
                            $canChoose = true;
                        }
                    }
                }
            }
            $payment_gateway = $all_paymentMethods->first();

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

            $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', auth()->user()->student->id)->with('incident', 'user', 'academicYear')->get() : null;
            $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();

            if (moduleStatusCheck('University')) {
                $student_id = $student_detail->id;
                $studentDetails = AramiscStudent::find($student_id);
                $studentRecordDetails = StudentRecord::where('student_id', $student_id);
                $studentRecords = $studentRecordDetails->distinct('un_academic_id')->get();
                $print = 1;
                return view(
                    'backEnd.studentPanel.my_profile',
                    compact('next_subjects', 'unSettings', 'departmentSubjects', 'next_semester_label', 'canChoose', 'driver', 'academic_year', 'student_detail', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades', 'exam_terms', 'result_views', 'leave_details', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'failgpaname', 'custom_field_values', 'paymentMethods', 'walletAmounts', 'bankAccounts', 'records', 'studentDetails', 'studentRecordDetails', 'studentRecords', 'print', 'payment_gateway', 'student', 'data', 'studentBehaviourRecords', 'behaviourRecordSetting')
                );
            } else {
                return view(
                    'backEnd.studentPanel.my_profile',
                    compact('next_subjects', 'unSettings', 'departmentSubjects', 'next_semester_label', 'canChoose', 'driver', 'academic_year', 'student_detail', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades', 'exam_terms', 'result_views', 'leave_details', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'failgpaname', 'custom_field_values', 'paymentMethods', 'walletAmounts', 'bankAccounts', 'records', 'payment_gateway', 'student', 'data', 'attendance', 'subjectAttendance', 'days', 'year', 'month', 'studentBehaviourRecords', 'behaviourRecordSetting')
                );
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
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

                    if ($parent) {
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
                }
                // end sibling & parent info update
                // student info update
                $student = AramiscStudent::find($request->id);
                if (($request->sibling_id == 0 || $request->sibling_id == 1) && $request->parent_id == "") {
                    $student->parent_id = @$parent->id;
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
            return redirect('student-profile');
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

    public function studentProfileUpdate(Request $request, $id = null)
    {
        try {
            $student = AramiscStudent::find($id);

            $classes = AramiscClass::where('active_status', '=', '1')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $religions = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '2')->get();
            $blood_groups = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '3')->get();
            $genders = AramiscBaseSetup::where('active_status', '=', '1')->where('base_group_id', '=', '1')->get();
            $route_lists = AramiscRoute::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $vehicles = AramiscVehicle::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $dormitory_lists = AramiscDormitoryList::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $driver_lists = AramiscStaff::where([['active_status', '=', '1'], ['role_id', 9]])->where('school_id', Auth::user()->school_id)->get();
            $categories = AramiscStudentCategory::where('school_id', Auth::user()->school_id)->get();
            $groups = AramiscStudentGroup::where('school_id', Auth::user()->school_id)->get();
            $sessions = AramiscAcademicYear::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $siblings = AramiscStudent::where('parent_id', '!=', 0)->where('parent_id', $student->parent_id)->where('school_id', Auth::user()->school_id)->get();
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
            $custom_fields = AramiscCustomField::where('form_name', 'student_registration')->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentPanel.my_profile_update', compact('student', 'classes', 'religions', 'blood_groups', 'genders', 'route_lists', 'vehicles', 'dormitory_lists', 'categories', 'groups', 'sessions', 'siblings', 'driver_lists', 'lead_city', 'fields', 'sources', 'custom_fields'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function studentDashboard(Request $request, $id = null)
    {
        try {
            $user = auth()->user();
            if ($user) {
                $user_id = $user->id;
            } else {
                $user_id = $request->user_id;
            }
            $student_detail = auth()->user()->student->load('studentRecords', 'feesAssign', 'feesAssignDiscount');

            // record data
            $class_ids = $student_detail->studentRecords->pluck('class_id')->unique()->toArray();
            $section_ids = $student_detail->studentRecords->pluck('section_id')->unique()->toArray();
            // end

            $driver = AramiscVehicle::where('aramisc_vehicles.id', '=', $student_detail->vechile_id)
                ->join('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_vehicles.driver_id')
                ->first();
            $siblings = AramiscStudent::where('parent_id', $student_detail->parent_id)->where('school_id', $user->school_id)->get();
            $fees_assigneds = $student_detail->feesAssign;
            
            $old_fees = 0;
            foreach ($fees_assigneds as $fees_assigned) {
                $fees_assigned->amount = $fees_assigned->fees_amount;
                $old_fees += $fees_assigned->fees_amount;
            }

            $fees_discounts = $student_detail->feesAssignDiscount;
            $documents = AramiscStudentDocument::where('student_staff_id', $student_detail->id)
                ->where('type', 'stu')
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $timelines = AramiscStudentTimeline::where('staff_student_id', $student_detail->id)
                ->where('type', 'stu')
                ->where('visible_to_student', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $exams = AramiscExamSchedule::whereIn('class_id', $class_ids)
                ->whereIn('section_id', $section_ids)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $grades = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $totalSubjects = AramiscAssignSubject::whereIn('class_id', $class_ids)
                ->whereIn('section_id', $section_ids)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $totalNotices = AramiscNoticeBoard::where('active_status', 1)
                ->where('inform_to', 'LIKE', '%2%')
                ->orderBy('id', 'DESC')
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            date_default_timezone_set(@generalSetting()->timeZone->time_zone);
            $now = date('Y-m-d');
            if (moduleStatusCheck('OnlineExam') == true) {
                $online_exams = AramiscOnlineExam::where('active_status', 1)
                    ->where('status', 1)
                    ->whereIn('class_id', $class_ids)
                    ->whereIn('section_id', $section_ids)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $user->school_id)
                    ->get();
            } else {
                $online_exams = AramiscOnlineExam::where('active_status', 1)
                    ->where('status', 1)
                    ->whereIn('class_id', $class_ids)
                    ->whereIn('section_id', $section_ids)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $user->school_id)
                    ->get();
            }

            $teachers = AramiscAssignSubject::select('teacher_id')
                ->whereIn('class_id', $class_ids)
                ->whereIn('section_id', $section_ids)
                ->distinct('teacher_id')
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $issueBooks = AramiscBookIssue::where('member_id', $student_detail->user_id)
                ->where('issue_status', 'I')
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $homeworkLists = AramiscHomework::whereIn('class_id', $class_ids)
                ->whereIn('section_id', $section_ids)
                ->where('evaluation_date', '=', null)
                ->where('submission_date', '>', $now)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $month = date('m');
            $year = date('Y');

            $attendances = AramiscStudentAttendance::where('student_id', $student_detail->id)
                ->where('attendance_date', 'like', $year . '-' . $month . '%')
                ->where('attendance_type', '=', 'P')
                ->where('school_id', $user->school_id)
                ->get();

            $holidays = AramiscHoliday::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $events = AramiscEvent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->where(function ($q) {
                    $q->where('for_whom', 'All')
                        ->orWhere('for_whom', 'Student')
                        ->orWhereNull('for_whom');
                })
                #->whereJsonContains('role_ids', "2")
                ->get();
            
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $aramisc_weekends = AramiscWeekend::orderBy('order', 'ASC')
                ->where('active_status', 1)
                ->where('school_id', $user->school_id)
                ->get();

            if (moduleStatusCheck('University')) {
                $records = StudentRecord::where('student_id', $student_detail->id)
                    ->where('un_academic_id', getAcademicId())->get();
            } else {
                $records = StudentRecord::where('student_id', $student_detail->id)
                    ->where('academic_id', getAcademicId())->get();
            }
            $routineDashboard = true;
            
            $student_details = Auth::user()->student->load('studentRecords', 'attendances');
            $student_records = $student_details->studentRecords;

            $my_leaves = AramiscLeaveDefine::where('role_id', Auth::user()->role_id)->where('user_id', Auth::user()->id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $now = Carbon::now();
            $year = $now->year;
            $month  = $now->month;
            $days = cal_days_in_month(CAL_GREGORIAN, $now->month, $now->year);
            $attendance = $student_details->attendances;

            $subjectAttendance = AramiscSubjectAttendance::with('student')
                ->whereIn('academic_id', $student_records->pluck('academic_id'))
                ->whereIn('student_record_id', $student_records->pluck('id'))
                ->whereIn('school_id', $student_records->pluck('school_id'))
                ->get();
            $complaints = AramiscComplaint::with('complaintType', 'complaintSource')->get();
           
            $data['settings'] = AramiscCalendarSetting::get();
            $data['roles'] = AramiscRole::where(function ($q) {
                $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
            })
                ->whereNotIn('id', [1, 2])
                ->get();
                
            $academicCalendar = new AramiscAcademicCalendarController();
            $events = $academicCalendar->calenderData();

            $due_amount = 0;
            $total_amount = 0;
            $paid_amount = 0;
            if(moduleStatusCheck('University')) {
                if (generalSetting()->fees_status == 0) {
                    $un_fees_assign = UnFeesInstallmentAssign::where('student_id', $student_detail->id)->where('un_academic_id',getAcademicId())->get();
                    foreach ($un_fees_assign as $assign) {
                        $total_amount += $assign->amount;
                        $paid_amount +=  $assign->paid_amount;
                    }
        
                    $due_amount = $total_amount - $paid_amount;
                } else {
                    
                }
            }

            return view('backEnd.studentPanel.studentProfile', compact('due_amount','events','totalSubjects', 'totalNotices', 'online_exams', 'teachers', 'issueBooks', 'homeworkLists', 'attendances', 'driver', 'student_detail', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades', 'events', 'holidays', 'aramisc_weekends', 'records', 'student_records', 'routineDashboard', 'my_leaves', 'attendance', 'year', 'month', 'days', 'subjectAttendance', 'complaints','old_fees'), $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentsDocumentApi(Request $request, $id)
    {
        try {
            $student_detail = AramiscStudent::where('user_id', $id)->first();
            $documents = AramiscStudentDocument::where('student_staff_id', $student_detail->id)->where('type', 'stu')
                ->select('title', 'file')
                ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_detail'] = $student_detail->toArray();
                $data['documents'] = $documents->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function classRoutine(Request $request, $id = null)
    {
        try {
            $user = auth()->user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $aramisc_weekends = AramiscWeekend::orderBy('order', 'ASC')
                ->where('active_status', 1)
                ->where('school_id', $user->school_id)
                ->get();

            if (moduleStatusCheck('University')) {
                $records = StudentRecord::where('student_id', $student_detail->id)
                    ->where('un_academic_id', getAcademicId())->get();
            } else {
                $records = StudentRecord::where('student_id', $student_detail->id)
                    ->where('academic_id', getAcademicId())->get();
            }
            return view('backEnd.studentPanel.class_routine', compact('aramisc_weekends', 'records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentResult()
    {
        try {

            $student_detail = Auth::user()->student;
            $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();
            $records = StudentRecord::where('student_id', $student_detail->id)->where('academic_id', getAcademicId())->get();
            $student_optional_subject = AramiscOptionalSubjectAssign::where('student_id', $student_detail->id)
                ->where('session_id', '=', $student_detail->session_id)
                ->first();

            $exams = AramiscExamSchedule::where('class_id', $student_detail->class_id)
                ->where('section_id', $student_detail->section_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $grades = AramiscMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
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

            if (moduleStatusCheck('University')) {
                $student_id = $student_detail->id;
                $studentDetails = AramiscStudent::find($student_id);
                $studentRecordDetails = StudentRecord::where('student_id', $student_id);
                $studentRecords = StudentRecord::where('student_id', $student_id)->distinct('un_academic_id')->get();
                return view('backEnd.studentPanel.student_result', compact('student_detail', 'exams', 'grades', 'exam_terms', 'failgpaname', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'records', 'studentDetails', 'studentRecordDetails', 'studentRecords'));
            } else {
                return view('backEnd.studentPanel.student_result', compact('student_detail', 'exams', 'grades', 'exam_terms', 'failgpaname', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'records'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentExamSchedule()
    {
        try {
            $student_detail = Auth::user()->student;
            $records = studentRecords(null, $student_detail->id)->get();
            return view('backEnd.studentPanel.exam_schedule', compact('records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentExamScheduleSearch(Request $request)
    {
        $request->validate([
            'exam' => 'required',
        ]);

        try {
            $student_detail = Auth::user()->student;
            $records = studentRecords(null, $student_detail->id)->get();
            $aramiscExam = AramiscExam::findOrFail($request->exam);
            $assign_subjects = AramiscAssignSubject::where('class_id', $aramiscExam->class_id)->where('section_id', $aramiscExam->section_id)
                ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            if ($assign_subjects->count() == 0) {
                Toastr::error('No Subject Assigned.', 'Failed');
                return redirect('student-exam-schedule');
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
                ->where('exam_term_id', $exam_type_id)
                ->orderBy('date', 'ASC')->get();

            return view('backEnd.studentPanel.exam_schedule', compact('exams', 'assign_subjects', 'class_id', 'section_id', 'exam_id', 'exam_schedule_subjects', 'assign_subject_check', 'exam_type_id', 'exam_periods', 'exam_routines', 'records'));
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

    public function studentExamScheduleApi(Request $request, $id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $student_detail = AramiscStudent::where('user_id', $id)->first();
                // $assign_subjects = AramiscAssignSubject::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('academic_id', getAcademicId())->where('school_id',Auth::user()->school_id)->get();
                $exam_schedule = DB::table('aramisc_exam_schedules')
                    ->join('aramisc_students', 'aramisc_students.class_id', '=', 'aramisc_exam_schedules.class_id')
                    ->join('aramisc_exam_types', 'aramisc_exam_types.id', '=', 'aramisc_exam_schedules.exam_term_id')
                    ->join('aramisc_exam_schedule_subjects', 'aramisc_exam_schedule_subjects.exam_schedule_id', '=', 'aramisc_exam_schedules.id')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_exam_schedules.subject_id')
                    ->select('aramisc_subjects.subject_name', 'aramisc_exam_schedule_subjects.start_time', 'aramisc_exam_schedule_subjects.end_time', 'aramisc_exam_schedule_subjects.date', 'aramisc_exam_schedule_subjects.room', 'aramisc_exam_schedules.class_id', 'aramisc_exam_schedules.section_id')
                    //->where('aramisc_students.class_id', '=', 'aramisc_exam_schedules.class_id')

                    ->where('aramisc_exam_schedules.section_id', '=', $student_detail->section_id)
                    ->where('aramisc_exam_schedulesacademic_id', getAcademicId())->where('aramisc_exam_schedules.school_id', Auth::user()->school_id)->get();
                return ApiBaseMethod::sendResponse($exam_schedule, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentViewExamSchedule($id)
    {
        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $class = AramiscClass::find($student_detail->class_id);
            $section = AramiscSection::find($student_detail->section_id);
            $assign_subjects = AramiscExamScheduleSubject::where('exam_schedule_id', $id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.examination.view_exam_schedule_modal', compact('class', 'section', 'assign_subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentMyAttendance()
    {
        try {
            $academic_years = AramiscAcademicYear::where('active_status', '=', 1)->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.studentPanel.student_attendance', compact('academic_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentHomework(Request $request, $id = null)
    {
        try {

            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $records = $student_detail->studentRecords;

            return view('backEnd.studentPanel.student_homework', compact('student_detail', 'records'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentHomeworkView($class_id, $section_id, $homework_id)
    {
        try {
            $homeworkDetails = AramiscHomework::where('class_id', '=', $class_id)->where('section_id', '=', $section_id)->where('id', '=', $homework_id)->first();
            return view('backEnd.studentPanel.studentHomeworkView', compact('homeworkDetails', 'homework_id'));
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

    public function addHomeworkContent($homework_id)
    {
        try {
            return view('backEnd.studentPanel.addHomeworkContent', compact('homework_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteViewHomeworkContent($homework_id)
    {
        try {

            return view('backEnd.studentPanel.deleteHomeworkContent', compact('homework_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteHomeworkContent($homework_id)
    {
        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $contents = AramiscUploadHomeworkContent::where('student_id', $student_detail->id)->where('homework_id', $homework_id)->get();
            foreach ($contents as $key => $content) {
                if ($content->file != "") {
                    if (file_exists($content->file)) {
                        unlink($content->file);
                    }
                }
                $content->delete();
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function uploadHomeworkContent(Request $request)
    {
        // $input = $request->all();
        // $validator = Validator::make($input, [
        //     'files' => "mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mp3,txt",
        // ]);

        // if ($validator->fails()) {
        //     Toastr::warning('Unsupported file upload', 'Failed');
        //     return redirect()->back();
        // }

        if ($request->file('files') == "") {
            Toastr::error('No file uploaded', 'Failed');
            return redirect()->back();
        }
        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $data = [];
            foreach ($request->file('files') as $key => $file) {
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/homeworkcontent/', $fileName);
                $fileName = 'public/uploads/homeworkcontent/' . $fileName;
                $data[$key] = $fileName;
            }
            $all_filename = json_encode($data);
            $content = new AramiscUploadHomeworkContent();
            $content->file = $all_filename;
            $content->student_id = $student_detail->id;
            $content->homework_id = $request->id;
            $content->school_id = Auth::user()->school_id;
            $content->academic_id = getAcademicId();
            $content->save();

            $homework_info = AramiscHomeWork::find($request->id);
            $teacher_info = $teacher_info = User::find($homework_info->created_by);

            $notification = new AramiscNotification;
            $notification->user_id = $teacher_info->id;
            $notification->role_id = $teacher_info->role_id;
            $notification->date = date('Y-m-d');
            $notification->message = Auth::user()->student->full_name . ' ' . app('translator')->get('homework.submitted_homework');
            $notification->school_id = Auth::user()->school_id;
            $notification->academic_id = getAcademicId();
            $notification->save();

            try {
                $user = User::find($teacher_info->id);
                Notification::send($user, new StudentHomeworkSubmitNotification($notification));
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }

            Toastr::success('Operation successful', 'Success');
            if ($request->status == 'lmsHomework') {
                return redirect()->to(url('lms/watchCourse', $request->course_id));
            } else {
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function uploadContentView(Request $request, $id)
    {
        try {
            $ContentDetails = AramiscTeacherUploadContent::where('id', $id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->first();
            return view('backEnd.studentPanel.uploadContentDetails', compact('ContentDetails'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentAssignment()
    {
        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            if (moduleStatusCheck('University')) {
                $records = StudentRecord::where('student_id', $student_detail->id)->where('un_academic_id', getAcademicId())->get();
            } else {
                $records = StudentRecord::where('student_id', $student_detail->id)->where('academic_id', getAcademicId())->get();
            }

            $uploadContents = AramiscTeacherUploadContent::where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->where('content_type', 'as')
                ->where(function ($query) use ($student_detail) {
                    $query->where('available_for_all_classes', 1)
                        ->orWhere([['class', $student_detail->class_id], ['section', $student_detail->section_id]]);
                })
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            if (Auth()->user()->role_id != 1) {
                if ($user->role_id == 2) {
                    AramiscNotification::where('user_id', $user->student->id)->where('role_id', 2)->update(['is_read' => 1]);
                }
            }

            $uploadContents2 = AramiscTeacherUploadContent::where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->where('content_type', 'as')
                ->where('class', $student_detail->class_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentPanel.assignmentList', compact('uploadContents', 'uploadContents2', 'records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentAssignmentApi(Request $request, $id)
    {
        try {
            $student_detail = AramiscStudent::where('user_id', $id)->first();
            $uploadContents = AramiscTeacherUploadContent::where('content_type', 'as')
                ->select('content_title', 'upload_date', 'description', 'upload_file')
                ->where(function ($query) use ($student_detail) {
                    $query->where('available_for_all_classes', 1)
                        ->orWhere([['class', $student_detail->class_id], ['section', $student_detail->section_id]]);
                })->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_detail'] = $student_detail->toArray();
                $data['uploadContents'] = $uploadContents->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentStudyMaterial()
    {

        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();

            $uploadContents = AramiscTeacherUploadContent::where('content_type', 'st')
                ->where(function ($query) use ($student_detail) {
                    $query->where('available_for_all_classes', 1)
                        ->orWhere([['class', $student_detail->class_id], ['section', $student_detail->section_id]]);
                })->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.studentPanel.studyMetarialList', compact('uploadContents'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentSyllabus()
    {
        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            if (moduleStatusCheck('University')) {
                $records = StudentRecord::where('student_id', $student_detail->id)->where('un_academic_id', getAcademicId())->get();
            } else {
                $records = StudentRecord::where('student_id', $student_detail->id)->where('academic_id', getAcademicId())->get();
            }

            $uploadContents = AramiscTeacherUploadContent::where('content_type', 'sy')
                ->where(function ($query) use ($student_detail) {
                    $query->where('available_for_all_classes', 1)
                        ->orWhere([['class', $student_detail->class_id], ['section', $student_detail->section_id]]);
                })->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $uploadContents2 = AramiscTeacherUploadContent::where('content_type', 'ot')
                ->where('class', $student_detail->class_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentPanel.studentSyllabus', compact('uploadContents', 'uploadContents2', 'records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function othersDownload()
    {
        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            $uploadContents = AramiscTeacherUploadContent::where('content_type', 'ot')
                ->where(function ($query) use ($student_detail) {
                    $query->where('available_for_all_classes', 1)
                        ->orWhere([['class', $student_detail->class_id], ['section', $student_detail->section_id]]);
                })->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $uploadContents2 = AramiscTeacherUploadContent::where('content_type', 'ot')
                ->where('class', $student_detail->class_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if (moduleStatusCheck('University')) {
                $records = StudentRecord::where('student_id', $student_detail->id)->where('un_academic_id', getAcademicId())->get();
            } else {
                $records = StudentRecord::where('student_id', $student_detail->id)->where('academic_id', getAcademicId())->get();
            }

            return view('backEnd.studentPanel.othersDownload', compact('uploadContents', 'uploadContents2', 'records'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentSubject()
    {
        try {
            $user = Auth::user();
            if (moduleStatusCheck('University')) {
                $records = StudentRecord::where('student_id', $user->student->id)->where('un_academic_id', getAcademicId())->get();
            } else {
                $records = StudentRecord::where('student_id', $user->student->id)->where('academic_id', getAcademicId())->get();
            }
            return view('backEnd.studentPanel.student_subject', compact('records'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //Student Subject API
    public function studentSubjectApi(Request $request, $id)
    {
        try {
            $student = AramiscStudent::where('user_id', $id)->first();
            $assignSubjects = DB::table('aramisc_assign_subjects')
                ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
                ->leftjoin('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_assign_subjects.teacher_id')
                ->select('aramisc_subjects.subject_name', 'aramisc_subjects.subject_code', 'aramisc_subjects.subject_type', 'aramisc_staffs.full_name as teacher_name')
                ->where('aramisc_assign_subjects.class_id', '=', $student->class_id)
                ->where('aramisc_assign_subjects.section_id', '=', $student->section_id)
                ->where('aramisc_assign_subjects.academic_id', getAcademicId())->where('aramisc_assign_subjects.school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_subjects'] = $assignSubjects->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //student panel Transport
    public function studentTransport()
    {
        try {
            $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', auth()->user()->student->id)->with('incident', 'user', 'academicYear')->get() : null;
            $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();

            // $routes = AramiscAssignVehicle::where('active_status', 1)->where('school_id',Auth::user()->school_id)->get();
            $routes = AramiscAssignVehicle::join('aramisc_vehicles', 'aramisc_assign_vehicles.vehicle_id', 'aramisc_vehicles.id')
                ->join('aramisc_students', 'aramisc_vehicles.id', 'aramisc_students.vechile_id')
                ->where('aramisc_assign_vehicles.active_status', 1)
                ->where('aramisc_students.user_id', Auth::user()->id)
                ->where('aramisc_assign_vehicles.school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentPanel.student_transport', compact('routes', 'student_detail', 'studentBehaviourRecords', 'behaviourRecordSetting'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentTransportViewModal($r_id, $v_id)
    {
        try {
            $vehicle = AramiscVehicle::find($v_id);
            $route = AramiscRoute::find($r_id);
            return view('backEnd.studentPanel.student_transport_view_modal', compact('route', 'vehicle'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentDormitory()
    {
        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            // $room_lists = AramiscRoomList::where('school_id',Auth::user()->school_id)->get();
            // $room_lists = AramiscRoomList::join('aramisc_students','aramisc_students.room_id','aramisc_room_lists.id')
            // ->where('aramisc_room_lists.active_status', 1)->where('aramisc_room_lists.id', $student_detail->room_id)->where('aramisc_room_lists.school_id',Auth::user()->school_id)->get();
            $room_lists = AramiscRoomList::where('active_status', 1)->where('id', $student_detail->room_id)->where('school_id', Auth::user()->school_id)->get();

            $room_lists = $room_lists->groupBy('dormitory_id');
            $room_types = AramiscRoomType::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $dormitory_lists = AramiscDormitoryList::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentPanel.student_dormitory', compact('room_lists', 'room_types', 'dormitory_lists', 'student_detail'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentBookList()
    {
        try {
            $books = AramiscBook::where('active_status', 1)
                ->orderBy('id', 'DESC')
                ->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.studentPanel.studentBookList', compact('books'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentBookIssue()
    {
        try {
            $user = Auth::user();
            $student_detail = AramiscStudent::where('user_id', $user->id)->first();
            // $books = AramiscBook::select('id', 'book_title')->where('active_status', 1)->where('school_id',Auth::user()->school_id)->get();
            // $subjects = AramiscSubject::select('id', 'subject_name')->where('active_status', 1)->where('school_id',Auth::user()->school_id)->get();
            $library_member = AramiscLibraryMember::where('member_type', 2)->where('student_staff_id', $student_detail->user_id)->first();
            if (empty($library_member)) {
                Toastr::error('You are not library member ! Please contact with librarian', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('message-danger', 'You are not library member ! Please contact with librarian');
            }
            $issueBooks = AramiscBookIssue::where('member_id', $library_member->student_staff_id)
                ->leftjoin('aramisc_books', 'aramisc_books.id', 'aramisc_book_issues.book_id')
                ->leftjoin('library_subjects', 'library_subjects.id', 'aramisc_books.book_subject_id')
                // ->where('aramisc_book_issues.issue_status', 'I')
                ->where('aramisc_book_issues.school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentPanel.studentBookIssue', compact('issueBooks'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentNoticeboard(Request $request)
    {
        try {
            $data = [];
            $allNotices = AramiscNoticeBoard::where('active_status', 1)->where('inform_to', 'LIKE', '%2%')
                ->orderBy('id', 'DESC')
                ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['allNotices'] = $allNotices->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.studentPanel.studentNoticeboard', compact('allNotices'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentTeacher()
    {
        try {
            $student_detail = Auth::user()->student->load('studentRecords');
            $records = $student_detail->studentRecords;
            $teacherEvaluationSetting = TeacherEvaluationSetting::find(1);
            return view('backEnd.studentPanel.studentTeacher', compact('records', 'teacherEvaluationSetting'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function studentTeacherApi(Request $request, $id)
    {
        try {
            $student = AramiscStudent::where('user_id', $id)->first();

            $assignTeacher = DB::table('aramisc_assign_subjects')
                ->leftjoin('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_assign_subjects.subject_id')
                ->leftjoin('aramisc_staffs', 'aramisc_staffs.id', '=', 'aramisc_assign_subjects.teacher_id')
                //->select('aramisc_subjects.subject_name', 'aramisc_subjects.subject_code', 'aramisc_subjects.subject_type', 'aramisc_staffs.full_name')
                ->select('aramisc_staffs.full_name', 'aramisc_staffs.email', 'aramisc_staffs.mobile')
                ->where('aramisc_assign_subjects.class_id', '=', $student->class_id)
                ->where('aramisc_assign_subjects.section_id', '=', $student->section_id)
                ->where('aramisc_assign_subjects.school_id', Auth::user()->school_id)->get();

            $class_teacher = DB::table('aramisc_class_teachers')
                ->join('aramisc_assign_class_teachers', 'aramisc_assign_class_teachers.id', '=', 'aramisc_class_teachers.assign_class_teacher_id')
                ->join('aramisc_staffs', 'aramisc_class_teachers.teacher_id', '=', 'aramisc_staffs.id')
                ->where('aramisc_assign_class_teachers.class_id', '=', $student->class_id)
                ->where('aramisc_assign_class_teachers.section_id', '=', $student->section_id)
                ->where('aramisc_assign_class_teachers.active_status', '=', 1)
                ->select('full_name')
                ->first();
            $settings = AramiscGeneralSettings::find(1);
            if (@$settings->phone_number_privacy == 1) {
                $permission = 1;
            } else {
                $permission = 0;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['teacher_list'] = $assignTeacher->toArray();
                $data['class_teacher'] = $class_teacher;
                $data['permission'] = $permission;
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentLibrary(Request $request, $id)
    {
        try {
            $student = AramiscStudent::where('user_id', $id)->first();
            $issueBooks = DB::table('aramisc_book_issues')
                ->leftjoin('aramisc_books', 'aramisc_books.id', '=', 'aramisc_book_issues.book_id')
                ->where('aramisc_book_issues.member_id', '=', $student->user_id)
                ->where('aramisc_book_issues.school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['issueBooks'] = $issueBooks->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function studentDormitoryApi(Request $request)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $studentDormitory = DB::table('aramisc_room_lists')
                    ->join('aramisc_dormitory_lists', 'aramisc_room_lists.dormitory_id', '=', 'aramisc_dormitory_lists.id')
                    ->join('aramisc_room_types', 'aramisc_room_lists.room_type_id', '=', 'aramisc_room_types.id')
                    ->select('aramisc_dormitory_lists.dormitory_name', 'aramisc_room_lists.name as room_number', 'aramisc_room_lists.number_of_bed', 'aramisc_room_lists.cost_per_bed', 'aramisc_room_lists.active_status')->where('aramisc_room_lists.school_id', Auth::user()->school_id)->get();

                return ApiBaseMethod::sendResponse($studentDormitory, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function studentTimelineApi(Request $request, $id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                //$timelines = AramiscStudentTimeline::where('staff_student_id', $id)->first();
                $timelines = DB::table('aramisc_student_timelines')
                    ->leftjoin('aramisc_students', 'aramisc_students.id', '=', 'aramisc_student_timelines.staff_student_id')
                    ->where('aramisc_student_timelines.type', '=', 'stu')
                    ->where('aramisc_student_timelines.active_status', '=', 1)
                    ->where('aramisc_students.user_id', '=', $id)
                    ->select('title', 'date', 'description', 'file', 'aramisc_student_timelines.active_status')
                    ->where('aramisc_student_timelines.academic_id', getAcademicId())->where('aramisc_students.school_id', Auth::user()->school_id)->get();
                return ApiBaseMethod::sendResponse($timelines, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function examListApi(Request $request, $id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $student = AramiscStudent::where('user_id', $id)->first();
                // return  $student;
                $exam_List = DB::table('aramisc_exam_types')
                    ->join('aramisc_exams', 'aramisc_exams.exam_type_id', '=', 'aramisc_exam_types.id')
                    ->where('aramisc_exams.class_id', '=', $student->class_id)
                    ->where('aramisc_exams.section_id', '=', $student->section_id)
                    ->distinct()
                    ->select('aramisc_exam_types.id as exam_id', 'aramisc_exam_types.title as exam_name')
                    ->where('aramisc_exam_types.school_id', Auth::user()->school_id)->get();
                return ApiBaseMethod::sendResponse($exam_List, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function examScheduleApi(Request $request, $id, $exam_id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $student = AramiscStudent::where('user_id', $id)->first();
                $exam_schedule = DB::table('aramisc_exam_schedules')
                    ->join('aramisc_exam_types', 'aramisc_exam_types.id', '=', 'aramisc_exam_schedules.exam_term_id')
                    // ->join('aramisc_exam_types','aramisc_exam_types.id','=','aramisc_exam_schedules.exam_term_id' )
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_exam_schedules.subject_id')
                    ->join('aramisc_class_rooms', 'aramisc_class_rooms.id', '=', 'aramisc_exam_schedules.room_id')
                    ->join('aramisc_class_times', 'aramisc_class_times.id', '=', 'aramisc_exam_schedules.exam_period_id')
                    ->where('aramisc_exam_schedules.exam_term_id', '=', $exam_id)
                    ->where('aramisc_exam_schedules.school_id', '=', $student->school_id)
                    ->where('aramisc_exam_schedules.class_id', '=', $student->class_id)
                    ->where('aramisc_exam_schedules.section_id', '=', $student->section_id)
                    ->where('aramisc_exam_schedules.active_status', '=', 1)
                    ->select('aramisc_exam_types.id', 'aramisc_exam_types.title as exam_name', 'aramisc_subjects.subject_name', 'date', 'aramisc_class_rooms.room_no', 'aramisc_class_times.start_time', 'aramisc_class_times.end_time')
                    ->where('aramisc_exam_schedules.school_id', Auth::user()->school_id)->get();
                return ApiBaseMethod::sendResponse($exam_schedule, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function examResultApi(Request $request, $id, $exam_id)
    {
        try {
            $data = [];

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $student = AramiscStudent::where('user_id', $id)->first();
                $exam_result = DB::table('aramisc_result_stores')
                    ->join('aramisc_exam_types', 'aramisc_exam_types.id', '=', 'aramisc_result_stores.exam_type_id')
                    ->join('aramisc_exams', 'aramisc_exams.id', '=', 'aramisc_exam_types.id')
                    ->join('aramisc_subjects', 'aramisc_subjects.id', '=', 'aramisc_result_stores.subject_id')
                    ->where('aramisc_exams.id', '=', $exam_id)
                    ->where('aramisc_result_stores.school_id', '=', $student->school_id)
                    ->where('aramisc_result_stores.class_id', '=', $student->class_id)
                    ->where('aramisc_result_stores.section_id', '=', $student->section_id)
                    ->where('aramisc_result_stores.student_id', '=', $student->id)
                    ->select('aramisc_exams.id', 'aramisc_exam_types.title as exam_name', 'aramisc_subjects.subject_name', 'aramisc_result_stores.total_marks as obtained_marks', 'aramisc_exams.exam_mark as total_marks', 'aramisc_result_stores.total_gpa_grade as grade')
                    ->where('aramisc_exams.school_id', Auth::user()->school_id)->get();

                $data['exam_result'] = $exam_result->toArray();
                $data['pass_marks'] = 0;

                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function updatePassowrdStoreApi(Request $request)
    {
        try {
            $user = User::find($request->id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                if (Hash::check($request->current_password, $user->password)) {

                    $user->password = Hash::make($request->new_password);
                    $result = $user->save();
                    $msg = "Password Changed Successfully ";
                    return ApiBaseMethod::sendResponse(null, $msg);
                } else {
                    $msg = "You Entered Wrong Current Password";
                    return ApiBaseMethod::sendError(null, $msg);
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function leaveApply(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user) {
                $my_leaves = AramiscLeaveDefine::where('role_id', $user->role_id)->where('user_id', $user->id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('staff_id', $user->id)->where('role_id', $user->role_id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $leave_types = AramiscLeaveDefine::whereHas('leaveType')->where('role_id', $user->role_id)->where('user_id', $user->id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            } else {
                $my_leaves = AramiscLeaveDefine::where('role_id', $request->role_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $request->role_id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $leave_types = AramiscLeaveDefine::whereHas('leaveType')->where('role_id', $request->role_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            }

            return view('backEnd.student_leave.apply_leave', compact('apply_leaves', 'leave_types', 'my_leaves'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function leaveStore(Request $request)
    {
        $request->validate([
            'apply_date' => "required",
            'leave_type' => "required",
            'leave_from' => 'required|before_or_equal:leave_to',
            'leave_to' => "required",
            'attach_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
        ]);
        try {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('attach_file');
            $fileSize = filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $input = $request->all();
            $fileName = "";
            if ($request->file('attach_file') != "") {
                $file = $request->file('attach_file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/' . $fileName;
            }
            $user = auth()->user();
            if ($user) {
                $login_id = $user->id;
                $role_id = $user->role_id;
            } else {
                $login_id = $request->login_id;
                $role_id = $request->role_id;
            }
            $leaveDefine = AramiscLeaveDefine::with('leaveType:id')->find($request->leave_type, ['id', 'type_id']);
            $apply_leave = new AramiscLeaveRequest();
            $apply_leave->staff_id = $login_id;
            $apply_leave->role_id = $role_id;
            $apply_leave->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $apply_leave->leave_define_id = $request->leave_type;
            $apply_leave->type_id = $leaveDefine->leaveType->id;
            $apply_leave->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->reason;
            $apply_leave->file = $fileName;
            $apply_leave->academic_id = getAcademicId();
            $apply_leave->school_id = auth()->user()->school_id;
            $result = $apply_leave->save();

            $studentInfo = AramiscStudent::where('user_id', auth()->user()->id)->first();
            $data['to_date'] = $apply_leave->leave_to;
            $data['name'] = $apply_leave->user->full_name;
            $data['from_date'] = $apply_leave->leave_from;
            $data['class'] = $studentInfo->studentRecord->class->class_name;
            $data['section'] = $studentInfo->studentRecord->section->section_name;
            $this->sent_notifications('Leave_Apply', [$studentInfo->user_id], $data, ['Student']);

            // try {
            //     $data['name'] = $user->full_name;
            //     $data['email'] = $user->email;
            //     $data['role'] = $user->roles->name;
            //     $data['apply_date'] = $request->apply_date;
            //     $data['leave_from'] = $request->leave_from;
            //     $data['leave_to'] = $request->leave_to;
            //     $data['reason'] = $request->reason;
            //     send_mail($user->email, $user->full_name, "leave_applied", $data);

            //     $user = User::where('role_id', 1)->first();
            //     $notification = new AramiscNotification;
            //     $notification->user_id = $user->id;
            //     $notification->role_id = $user->role_id;
            //     $notification->date = date('Y-m-d');
            //     $notification->message = app('translator')->get('leave.leave_request');
            //     $notification->school_id = Auth::user()->school_id;
            //     $notification->academic_id = getAcademicId();
            //     $notification->save();
            //     Notification::send($user, new LeaveApprovedNotification($notification));
            // } catch (\Exception $e) {
            //     Log::info($e->getMessage());
            // }

            if ($result) {
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

    public function pendingLeave(Request $request)
    {
        try {
            $apply_leaves = AramiscLeaveRequest::with('leaveDefine', 'student')->where([['active_status', 1], ['approve_status', 'P']])->where('staff_id', auth()->id())->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();


            return view('backEnd.student_leave.pending_leave', compact('apply_leaves'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentLeaveEdit(request $request, $id)
    {
        try {
            $user = Auth::user();
            if ($user) {
                if ($user->role_id == 2) {
                    $my_leaves = AramiscLeaveDefine::where('user_id', $user->id)->get();
                    $apply_leaves = AramiscLeaveRequest::where('role_id', $user->role_id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                    $leave_types = AramiscLeaveDefine::where('role_id', $user->role_id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                } else {
                    $my_leaves = AramiscLeaveDefine::where('role_id', $user->role_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                    $apply_leaves = AramiscLeaveRequest::where('role_id', $user->role_id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                    $leave_types = AramiscLeaveDefine::where('role_id', $user->role_id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                }
            } else {
                $my_leaves = AramiscLeaveDefine::where('role_id', $request->role_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $apply_leaves = AramiscLeaveRequest::where('role_id', $request->role_id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $leave_types = AramiscLeaveDefine::where('role_id', $request->role_id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            }
            $apply_leave = AramiscLeaveRequest::find($id);
            return view('backEnd.student_leave.apply_leave', compact('apply_leave', 'apply_leaves', 'leave_types', 'my_leaves'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $request->validate([
            'apply_date' => "required",
            'leave_type' => "required",
            'leave_from' => 'required|before_or_equal:leave_to',
            'leave_to' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png",
        ]);
        try {
            $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
            $file = $request->file('attach_file');
            $fileSize = filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                return redirect()->back();
            }
            $fileName = "";
            if ($request->file('attach_file') != "") {
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
            if ($fileName != "") {
                $apply_leave->file = $fileName;
            }
            $result = $apply_leave->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('student-apply-leave');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function DownlodTimeline($file_name)
    {
        try {
            $file = public_path() . '/uploads/student/timeline/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            } else {
                Toastr::error('File not found', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function DownlodDocument($file_name)
    {
        try {
            $file = public_path() . '/uploads/homework/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function DownlodContent($file_name)
    {
        try {
            $file = public_path() . '/uploads/upload_contents/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function DownlodStudentDocument($file_name)
    {
        try {
            $file = public_path() . '/uploads/student/document/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function downloadHomeWorkContent($id, $student_id)
    {
        try {
            $student = AramiscStudent::where('id', $student_id)->first();
            if (Auth::user()->role_id == 2) {
                $student = AramiscStudent::where('user_id', $student_id)->first();
            }
            $hwContent = AramiscUploadHomeworkContent::where('student_id', $student->id)->where('homework_id', $id)->get();
            // $file_array= json_decode($hwContent->file, true);
            // $files = $file_array;
            // $zipname = 'Homework_Content_'.time().'.zip';
            // $zip = new ZipArchive;
            // $zip->open($zipname, ZipArchive::CREATE);
            //     foreach ($files as $file) {
            //         $zip->addFile($file);
            //     }
            // $zip->close();
            // header('Content-Type: application/zip');
            // header('Content-disposition: attachment; filename='.$zipname);
            // header('Content-Length: ' . filesize($zipname));
            // readfile($zipname);
            // File::delete($zipname);

            $file_paths = [];
            foreach ($hwContent as $key => $files_row) {
                $only_files = json_decode($files_row->file);
                foreach ($only_files as $second_key => $upload_file_path) {
                    $file_paths[] = $upload_file_path;
                }
            }
            $zip_file_name = str_replace(' ', '_', time() . '.zip'); // Name of our archive to download

            $new_file_array = [];
            foreach ($file_paths as $key => $file) {

                $file_name_array = explode('/', $file);
                $file_original = $file_name_array[array_key_last($file_name_array)];
                $new_file_array[$key]['path'] = $file;
                $new_file_array[$key]['name'] = $file_original;
            }
            $public_dir = public_path('uploads/homeworkcontent');
            $zip = new ZipArchive;
            if ($zip->open($public_dir . '/' . $zip_file_name, ZipArchive::CREATE) === true) {
                // Add Multiple file
                foreach ($new_file_array as $key => $file) {
                    $zip->addFile($file['path'], @$file['name']);
                }
                $zip->close();
            }

            $zip_file_url = asset('public/uploads/homeworkcontent/' . $zip_file_name);
            session()->put('homework_zip_file', $zip_file_name);

            return Redirect::to($zip_file_url);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
