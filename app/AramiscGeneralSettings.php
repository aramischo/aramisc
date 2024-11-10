<?php

namespace App;

use App\AramiscClass;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\YearCheck;
use App\AramiscExamType;
use App\AramiscLanguage;
use App\AramiscDateFormat;
use App\AramiscMarksGrade;
use App\AramiscResultStore;
use App\AramiscAssignSubject;
use App\AramiscTemporaryMeritlist;
use App\AramiscExamAttendanceChild;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscGeneralSettings extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_general_settings';
    public static $users = 'verify-purchase';
    public static $parents = 'item_id';
    public static $students = 'system_purchase_code';
    protected $with = ['currencyDetail'];

    protected $casts = [
        'promotionSetting' => 'integer',
        'active_status' => 'integer',
        'api_url' => 'integer',
        'website_btn' => 'integer',
        'dashboard_btn' => 'integer',
        'report_btn' => 'integer',
        'style_btn' => 'integer',
        'ltl_rtl_btn' => 'integer',
        'lang_btn' => 'integer',
        'ttl_rtl' => 'integer',
        'phone_number_privacy' => 'integer',
        'week_start_id' => 'integer',
        'time_zone_id' => 'integer',
        'attendance_layout' => 'integer',
        'session_id' => 'integer',
        'language_id' => 'integer',
        'date_format_id' => 'integer',
        'ss_page_load' => 'integer',
        'sub_topic_enable' => 'integer',
        'school_id' => 'integer',
        'multiple_roll' => 'integer',
        'Lesson' => 'integer',
        'Chat' => 'integer',
        'FeesCollection' => 'integer',
        'income_head_id' => 'integer',
        'AramiscBiometrics' => 'integer',
        'ResultReports' => 'integer',
        'TemplateSettings' => 'integer',
        'MenuManage' => 'integer',
        'RolePermission' => 'integer',
        'RazorPay' => 'integer',
        'Saas' => 'integer',
        'StudentAbsentNotification' => 'integer',
        'ParentRegistration' => 'integer',
        'Zoom' => 'integer',
        'BBB' => 'integer',
        'VideoWatch' => 'integer',
        'Jitsi' => 'integer',
        'OnlineExam' => 'integer',
        'SaasRolePermission' => 'integer',
        'BulkPrint' => 'integer',
        'HimalayaSms' => 'integer',
        'XenditPayment' => 'integer',
        'Wallet' => 'integer',
        'Lms' => 'integer',
        'University' => 'integer',
        'Gmeet' => 'integer',
        'KhaltiPayment' => 'integer',
        'Raudhahpay' => 'integer',
        'AppSlider' => 'integer',
        'BehaviourRecords' => 'integer',
        'InAppLiveClass' => 'integer',
        'fees_status' => 'integer',
        'lms_checkout' => 'integer',
        'academic_id' => 'integer',
        'un_academic_id' => 'integer',
        'direct_fees_assign' => 'integer',
        'with_guardian' => 'integer',
        'preloader_status' => 'integer',
        'preloader_style' => 'integer',
        'preloader_type' => 'integer',
        'due_fees_login' => 'integer',
        'two_factor' => 'integer',
        'DownloadCenter' => 'integer',
        'ExamPlan' => 'integer',
        'AiContent' => 'integer',
        'created_at' => 'string',
        'software_version' => 'string',
        'email_driver' => 'string',
        'fcm_key' => 'string',
        'WhatsappSupport' => 'integer',
        'auto_approve' => 'integer',
        'blog_search' => 'integer',
        'recent_blog' => 'integer',
        'preloader_image' => 'string',
        'active_theme' => 'string',
        'queue_connection' => 'string',
        'AWSS3' => 'integer',
        'is_comment' => 'integer',
        'ToyyibPay' => 'integer',
        'result_type' => 'string',
    ];

    public function sessions()
    {
        return $this->belongsTo('App\AramiscSession', 'session_id', 'id');
    }
    public function academic_Year()
    {
        return $this->belongsTo('App\AramiscAcademicYear', 'academic_id', 'id');
    }

    public function unacademic_Year()
    {
        return $this->belongsTo('Modules\University\Entities\UnAcademicYear', 'un_academic_id', 'id');
    }

    public function languages()
    {
        return $this->belongsTo('App\AramiscLanguage', 'language_id', 'id');
    }
    public function weekStartDay()
    {
        return $this->belongsTo('App\AramiscWeekend', 'week_start_id', 'id');
    }

    public function dateFormats()
    {
        return $this->belongsTo('App\AramiscDateFormat', 'date_format_id', 'id');
    }



    public function incomeHead()
    {
        return $this->belongsTo('App\AramiscChartOfAccount', 'income_head_id', 'id');
    }

    public static function getLanguageList()
    {
        try {
            $languages = AramiscLanguage::all();
            return $languages;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function value()
    {
        try {
            $value = AramiscGeneralSettings::first();
            return $value->system_purchase_code;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function SUCCESS($redirect_specific_message = null)
    {
        if ($redirect_specific_message) {
            Toastr::success($redirect_specific_message, 'Success');
        } else {
            Toastr::success('Operation successful', 'Success');
        }
        return false;
    }
    public static function ERROR($redirect_specific_message = null)
    {
        if ($redirect_specific_message) {
            Toastr::error($redirect_specific_message, 'Failed');
        } else {
            Toastr::error('Operation Failed', 'Failed');
        }
        return;
    }

    public function timeZone()
    {
        return $this->belongsTo('App\AramiscTimeZone', 'time_zone_id', 'id')->withDefault();
    }
    

    public static function make_merit_list($InputClassId, $InputSectionId, $InputExamId)
    {
        try {
            $iid = time();
            $class          = AramiscClass::find($InputClassId);
            $section        = AramiscSection::find($InputSectionId);
            $exam           = AramiscExamType::find($InputExamId);
            $is_data = DB::table('aramisc_mark_stores')->where([['class_id', $InputClassId], ['section_id', $InputSectionId], ['exam_term_id', $InputExamId]])->first();
            if (empty($is_data)) {
                return $data = 0;
                Toastr::error('Your result is not found!', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('message-danger', 'Your result is not found!');
            }
            $exams = AramiscExamType::where('active_status', 1)->where('academic_id', getAcademicId())->get();
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('academic_id', getAcademicId())->get();
            $assign_subjects = AramiscAssignSubject::where('class_id', $class->id)->where('section_id', $section->id)->where('academic_id', getAcademicId())->get();
            $class_name = $class->class_name;
            $exam_name = $exam->title;

            $eligible_subjects       = AramiscAssignSubject::where('class_id', $InputClassId)->where('section_id', $InputSectionId)->where('academic_id', getAcademicId())->get();

            $examStudents=AramiscExamAttendanceChild::where('academic_id', getAcademicId())->where('school_id',auth()->user()->school_id)->get();
            $examStudentsids=[];

            foreach($examStudents as $e_student){
                $examStudentsids[]=$e_student->student_id;
            }
            // check exam attendance and whereIn

            $eligible_students       = AramiscStudent::whereIn('id',$examStudentsids)->where('class_id', $InputClassId)->where('section_id', $InputSectionId)->get();


            //all subject list in a specific class/section
            $subject_ids        = [];
            $subject_strings    = '';
            $marks_string       = '';
            foreach ($eligible_students as $SingleStudent) {
                foreach ($eligible_subjects as $subject) {
                    $subject_ids[]      = $subject->subject_id;
                    $subject_strings    = (empty($subject_strings)) ? $subject->subject->subject_name : $subject_strings . ',' . $subject->subject->subject_name;

                    $getMark            =  AramiscResultStore::where([
                        ['exam_type_id',   $InputExamId],
                        ['class_id',       $InputClassId],
                        ['section_id',     $InputSectionId],
                        ['student_id',     $SingleStudent->id],
                        ['subject_id',     $subject->subject_id]
                    ])->first();
                    if ($getMark == "") {
                        Toastr::error('Please register marks for all students.!', 'Failed');
                        return redirect()->back();
                        // return redirect()->back()->with('message-danger', 'Please register marks for all students.!');
                    }
                    if ($marks_string == "") {
                        if ($getMark->total_marks == 0) {
                            $marks_string = '0';
                        } else {
                            $marks_string = $getMark->total_marks;
                            /* if ($marks_string < 33) {
                                return $data = 0;
                            } */
                        }
                    } else {
                        $marks_string = $marks_string . ',' . $getMark->total_marks;
                    }
                }
                //end subject list for specific section/class

                $results                =  AramiscResultStore::where([
                    ['exam_type_id',   $InputExamId],
                    ['class_id',       $InputClassId],
                    ['section_id',     $InputSectionId],
                    ['student_id',     $SingleStudent->id]
                ])->where('academic_id', getAcademicId())->get();
                $is_absent                =  AramiscResultStore::where([
                    ['exam_type_id',   $InputExamId],
                    ['class_id',       $InputClassId],
                    ['section_id',     $InputSectionId],
                    ['is_absent',      1],
                    ['student_id',     $SingleStudent->id]
                ])->where('academic_id', getAcademicId())->get();

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
                $insert_results->subjects_string    = $subject_strings;
                $insert_results->marks_string       = $marks_string;
                $insert_results->total_marks        = $sum_of_mark;
                $insert_results->average_mark       = $average_mark;
                $insert_results->gpa_point          = $exart_gp_point;
                $insert_results->iid                = $iid;
                $insert_results->student_id         = $student_id;
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
                $arrCheck = explode(",", $marks_string);

                $checkVal = min($arrCheck);
                $Grade = AramiscMarksGrade::where('gpa', 0)->first();
                if ($checkVal > $Grade->percent_upto) {
                    $insert_results->save();
                }
                $subject_strings = "";
                $marks_string = "";
                $total_marks = 0;
                $average = 0;
                $exart_gp_point = 0;
                $admission_no = 0;
                $full_name = "";
            } //end loop eligible_students

            $first_data = AramiscTemporaryMeritlist::where('iid', $iid)->first();
            if ($first_data == null) {
                return $data = 0;
            } else
                $subjectlist = explode(',', $first_data->subjects_string);
            $allresult_data = AramiscTemporaryMeritlist::where('iid', $iid)->orderBy('gpa_point', 'desc')->where('academic_id', getAcademicId())->get();
            $merit_serial = 1;
            foreach ($allresult_data as $row) {
                $D = AramiscTemporaryMeritlist::where('iid', $iid)->where('id', $row->id)->first();
                $D->merit_order = $merit_serial++;
                $D->save();
            }
            $allresult_data = AramiscTemporaryMeritlist::where('iid', $iid)->orderBy('merit_order', 'asc')->where('academic_id', getAcademicId())->get();
            $data['iid'] = $iid;
            $data['exams'] = $exams;
            $data['classes'] = $classes;
            $data['subjects'] = $subjects;
            $data['class'] = $class;
            $data['section'] = $section;
            $data['exam'] = $exam;
            $data['subjectlist'] = $subjectlist;
            $data['allresult_data'] = $allresult_data;
            $data['eligible_students'] = $eligible_students;
            $data['class_name'] = $class_name;
            $data['assign_subjects'] = $assign_subjects;
            $data['exam_name'] = $exam_name;
            $data['InputClassId'] = $InputClassId;
            $data['InputExamId'] = $InputExamId;
            $data['InputSectionId'] = $InputSectionId;
            return $data;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function isModule($name)
    {
        try {
           
            //check exist modules_statuses.json
            $module = Module::find($name);
           
            if (!empty($module)) {
                // is available Modules / FeesCollection1 / Providers / FeesCollectionServiceProvider . php
                $is_module_available = 'Modules/' . $name . '/Providers/' . $name . 'ServiceProvider.php';
                
                if (file_exists($is_module_available)) {
                    $modulestatus =  Module::find($name)->isDisabled();
                   

                    if ($modulestatus == FALSE) {
                        $is_verify = AramiscModuleManager::where('name', $name)->first();
                       
                        if (!empty($is_verify->purchase_code)) {
                            return TRUE;
                           
                        }
                    }
                }
            }
            return FALSE;
        } catch (\Throwable $th) {
            return FALSE;
        }
    }


    public function unAcademic()
    {
        return $this->belongsTo('Modules\University\Entities\UnAcademicYear', 'un_academic_id', 'id')->withDefault();
    }



    public static function isSE($isConfig)
    {
        return TRUE;
    }

    public function currencyDetail()
    {
        return $this->belongsTo('App\AramiscCurrency', 'currency', 'code');
    }

    public function aboutPage()
    {
        return $this->belongsTo(AramiscAboutPage::class, 'school_id', 'school_id');
    }
}
