<?php


namespace App\Http\Controllers\Admin\Academics;

use App\User;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\ApiBaseMethod;
use App\SmContentType;
use App\SmClassSection;
use App\SmNotification;
use App\SmAssignSubject;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmTeacherUploadContent;
use Illuminate\Support\Facades\DB;
use App\Scopes\GlobalAcademicScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Notification;
use Modules\RolePermission\Entities\AramiscRole;
use Modules\University\Entities\UnSemesterLabel;
use App\Notifications\StudyMeterialCreatedNotification;
use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class GlobalUploadContentController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index(Request $request)
    {
        try {
            $aramiscUploadContents = SmTeacherUploadContent::query()->with('classes', 'sections');
            if (teacherAccess()) {
                    $aramiscUploadContents->where(function ($q) {
                        $q->where('created_by', Auth::user()->id)->orWhere('available_for_admin', 1);
                    });
            }
            $aramiscUploadContents = $aramiscUploadContents->withoutGlobalScope(StatusAcademicSchoolScope::class)
                                            ->withoutGlobalScope(GlobalAcademicScope::class)
                                            ->whereNull('parent_id')
                                            ->where('school_id', Auth::user()->school_id)
                                            ->where('course_id', '=', null)
                                            ->where('chapter_id', '=', null)
                                            ->where('lesson_id', '=', null)
                                            ->orderby('id', 'DESC')
                                            ->get();

            

            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id', Auth::user()->id)->first();
                $classes= $teacher_info->classes;
            } else {
                $classes = SmClass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->with('groupclassSections')->where('school_id', Auth::user()->school_id)->get();
            }

            return view('backEnd.global.global_aramiscUploadContentList', compact( 'classes', 'aramiscUploadContents'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $maxFileSize = generalSetting()->file_size*1024;
        $rules = [];
        if($request->status != 'lmsStudyMaterial'){
            if(!moduleStatusCheck('University')){
                $rules = [
                    'content_title' => "required|max:200",
                    'content_type' => "required",
                    'available_for' => 'required|array',
                    'upload_date' => "required",
                    'content_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mp3,txt|max:".$maxFileSize,
                    'all_classes' =>'sometimes|nullable',
                    'description' =>'sometimes|nullable',
                    'source_url' => 'sometimes|nullable|url',
                    'section'   => 'sometimes|nullable',
                ];
            }
            
            if ($request->available_for and is_array($request->available_for)) {
                if (array_search('admin', $request->available_for) || $request->all_classes=='on') {
                    $rules ['class'] ='sometimes|nullable';
                } elseif(moduleStatusCheck('University') == false && array_search('student', $request->available_for) && $request->all_classes !=='on') {
                    $rules ['class'] ='required';
                }elseif(moduleStatusCheck('University') && $request->un_session_id) {
                    $rules ['un_session_id'] ='required';
                    $rules ['un_department_id'] ='required';
                    $rules ['un_academic_id'] ='required';
                    $rules ['un_semester_id'] ='required';
                    $rules ['un_semester_label_id'] ='required';
                }
            }
        }else{
            $rules = [
                'content_title' => "required|max:200",
                'content_type' => "required",
                'available_for' => 'required|array',
                'upload_date' => "required",
                'content_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mp3,txt|max:".$maxFileSize,
                'description' =>'sometimes|nullable',
                'source_url' => 'sometimes|nullable|url',
                'section'   => 'sometimes|nullable',
            ];
        }
        $request->validate($rules);
        try {
            $student_ids = SmStudentReportController::classSectionStudent($request);
            $destination='public/uploads/upload_contents/';
            if ($request->section == "all") {

            } else {
                if (moduleStatusCheck('University')) {
                    if($request->un_session_id){
                        $labels = UnSemesterLabel::find($request->un_semester_label_id);
                        $sections = $labels->labelSections;
                        if(is_null($request->un_section_id)){
                            foreach($sections as $section){
                                $aramiscUploadContents = new SmTeacherUploadContent();
                                $aramiscUploadContents->content_title = $request->content_title;
                                $aramiscUploadContents->content_type = $request->content_type;
                                $aramiscUploadContents->school_id = Auth::user()->school_id;
                                $aramiscUploadContents->upload_date = date('Y-m-d', strtotime($request->upload_date));
                                $aramiscUploadContents->description = $request->description;
                                $aramiscUploadContents->source_url = $request->source_url;
                                $aramiscUploadContents->upload_file = fileUpload($request->content_file, $destination);
                                $aramiscUploadContents->created_by = auth()->user()->id;
                                $results = $aramiscUploadContents->save();
                                $interface = App::make(UnCommonRepositoryInterface::class);
                                $interface->storeUniversityData($aramiscUploadContents, $request);
                                $aramiscUploadContents->un_section_id = $section->id;
                                $aramiscUploadContents->save();
                            }
                        }else{
                            $aramiscUploadContents = new SmTeacherUploadContent();
                            $aramiscUploadContents->content_title = $request->content_title;
                            $aramiscUploadContents->content_type = $request->content_type;
                            $aramiscUploadContents->school_id = Auth::user()->school_id;
                            $aramiscUploadContents->upload_date = date('Y-m-d', strtotime($request->upload_date));
                            $aramiscUploadContents->description = $request->description;
                            $aramiscUploadContents->source_url = $request->source_url;
                            $aramiscUploadContents->upload_file = fileUpload($request->content_file, $destination);
                            $aramiscUploadContents->created_by = auth()->user()->id;
                            $results = $aramiscUploadContents->save();
                            $interface = App::make(UnCommonRepositoryInterface::class);
                            $interface->storeUniversityData($aramiscUploadContents, $request);
                            $aramiscUploadContents->save();
                        }
                    }else{
                        $aramiscUploadContents = new SmTeacherUploadContent();
                        $aramiscUploadContents->content_title = $request->content_title;
                        $aramiscUploadContents->content_type = $request->content_type;
                        $aramiscUploadContents->school_id = Auth::user()->school_id;
                        $aramiscUploadContents->upload_date = date('Y-m-d', strtotime($request->upload_date));
                        foreach ($request->available_for as $value) {
                            if ($value == 'admin') {
                                $aramiscUploadContents->available_for_admin = 1;
                            }
                        }
                        $aramiscUploadContents->un_academic_id = getAcademicId();
                        $aramiscUploadContents->description = $request->description;
                        $aramiscUploadContents->source_url = $request->source_url;
                        $aramiscUploadContents->upload_file = fileUpload($request->content_file, $destination);
                        $aramiscUploadContents->created_by = auth()->user()->id;
                        $results = $aramiscUploadContents->save();
                    }
                }else{
                    $aramiscUploadContents = new SmTeacherUploadContent();
                    $aramiscUploadContents->content_title = $request->content_title;
                    $aramiscUploadContents->content_type = $request->content_type;
                    $aramiscUploadContents->school_id = Auth::user()->school_id;
                    $aramiscUploadContents->academic_id = getAcademicId();
                    foreach ($request->available_for as $value) {
                        if ($value == 'admin') {
                            $aramiscUploadContents->available_for_admin = 1;
                        }
                        if ($value == 'student') {
                            if (isset($request->all_classes)) {
                                $aramiscUploadContents->available_for_all_classes = 1;
                            } else {
                                $aramiscUploadContents->class = $request->class;
                                $aramiscUploadContents->section = $request->section;
                            }
                        }
                    }
                    $aramiscUploadContents->upload_date = date('Y-m-d', strtotime($request->upload_date));
                    $aramiscUploadContents->description = $request->description;
                    $aramiscUploadContents->source_url = $request->source_url;
                    $aramiscUploadContents->upload_file = fileUpload($request->content_file, $destination);
                    if($request->status == 'lmsStudyMaterial'){
                        if($request->parent_course){
                            $aramiscUploadContents->parent_course_id = $request->course_id;
                        }else{
                            $aramiscUploadContents->course_id = $request->course_id;
                        }
                        $aramiscUploadContents->chapter_id = $request->chapter_id;
                        $aramiscUploadContents->lesson_id = $request->lesson_id;
                    }
                    $aramiscUploadContents->created_by = auth()->user()->id;
                    $results = $aramiscUploadContents->save();
                }
            }

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
                    $roles = AramiscRole::where('id', '=', 1) /* ->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 9) */->where(function ($q) {
                        $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
                    })->get();
                    foreach ($roles as $role) {
                        $staffs = SmStaff::where('role_id', $role->id)->where('school_id', Auth::user()->school_id)->get();
                        foreach ($staffs as $staff) {
                            $notification = new SmNotification;
                            $notification->user_id = $staff->user_id;
                            $notification->role_id = $role->id;
                            $notification->school_id = Auth::user()->school_id;
                            if(moduleStatusCheck('University')){
                                $notification->un_academic_id = getAcademicId();
                            }else{
                                $notification->academic_id = getAcademicId();
                            }
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
                            $notification->message = $purpose . ' '.app('translator')->get('common.uploaded');
                            $notification->save();

                            try {
                                $user=User::find($notification->user_id);
                                Notification::send($user, new StudyMeterialCreatedNotification($notification));
                            } catch (\Exception $e) {
                                Log::info($e->getMessage());
                            }
                        }
                    }
                }
                if (($value == 'student') && ($request->status != 'lmsStudyMaterial') ) {
                    if (isset($request->all_classes)) {
                        $students = SmStudent::select('id', 'user_id')->where('school_id', Auth::user()->school_id)->get();
                        foreach ($students as $student) {
                            $notification = new SmNotification;
                            $notification->user_id = $student->user_id;
                            $notification->role_id = 2;
                            $notification->school_id = Auth::user()->school_id;
                            if(moduleStatusCheck('University')){
                                $notification->un_academic_id = getAcademicId();
                            }else{
                                $notification->academic_id = getAcademicId();
                            }
                            if ($request->content_type == 'as') {
                                $notification->url = 'student-assignment';
                            } elseif ($request->content_type == 'st') {
                                $notification->url = 'student-study-material';
                            } elseif ($request->content_type == 'sy') {
                                $notification->url = 'student-syllabus';
                            } elseif ($request->content_type == 'ot') {
                                $notification->url = 'student-others-download';
                            }
                            $notification->date = date('Y-m-d');
                            $notification->message = $purpose . ' '.app('translator')->get('common.uploaded');
                            $notification->save();

                            try {
                                $user=User::find($notification->user_id);
                                Notification::send($user, new StudyMeterialCreatedNotification($notification));
                            } catch (\Exception $e) {
                                Log::info($e->getMessage());
                            }
                        }
                    } elseif ((!is_null($request->class)) &&   ($request->section == '')) {
                        $students = SmStudent::select('id', 'user_id')->whereIn('id', $student_ids)->where('school_id', Auth::user()->school_id)->get();
                        foreach ($students as $student) {
                            $notification = new SmNotification;
                            $notification->user_id = $student->user_id;
                            $notification->role_id = 2;
                            $notification->school_id = Auth::user()->school_id;
                            if(moduleStatusCheck('University')){
                                $notification->un_academic_id = getAcademicId();
                            }else{
                                $notification->academic_id = getAcademicId();
                            }
                            if ($request->content_type == 'as') {
                                $notification->url = 'student-assignment';
                            } elseif ($request->content_type == 'st') {
                                $notification->url = 'student-study-material';
                            } elseif ($request->content_type == 'sy') {
                                $notification->url = 'student-syllabus';
                            } elseif ($request->content_type == 'ot') {
                                $notification->url = 'student-others-download';
                            }
                            $notification->date = date('Y-m-d');
                            $notification->message = $purpose . ' '.app('translator')->get('common.uploaded');
                            $notification->save();

                            try {
                                $user=User::find($notification->user_id);
                                Notification::send($user, new StudyMeterialCreatedNotification($notification));
                            } catch (\Exception $e) {
                                Log::info($e->getMessage());
                            }
                        }
                    } else {
                        $students = SmStudent::select('id', 'user_id')->whereIn('id', $student_ids)->where('school_id', Auth::user()->school_id)->get();
                        foreach ($students as $student) {
                            $notification = new SmNotification;
                            $notification->user_id = $student->user_id;
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
                            $notification->date = date('Y-m-d');
                            $notification->message = $purpose . ' '.app('translator')->get('common.uploaded');
                            $notification->school_id = Auth::user()->school_id;
                            if(moduleStatusCheck('University')){
                                $notification->un_academic_id = getAcademicId();
                            }else{
                                $notification->academic_id = getAcademicId();
                            }
                            $notification->save();
                            try {
                                $user=User::find($notification->user_id);
                                Notification::send($user, new StudyMeterialCreatedNotification($notification));
                            } catch (\Exception $e) {
                                Log::info($e->getMessage());
                            }

                            try {
                                $user=User::find($notification->user_id);
                                Notification::send($user, new StudyMeterialCreatedNotification($notification));
                            } catch (\Exception $e) {
                               
                                Log::info($e->getMessage());
                            }
                        }
                    }
                }
            }

            if ($results) {
                Toastr::success('Operation successful', 'Success');
                if ($request->status == 'lmsStudyMaterial') {   
                    if($request->parent_course){
                        return redirect()->back();
                    }else{
                        return redirect()->route('lms.courseDetail', [$request->course_id, 'course_curriculum']);
                    }          
                   
                } else {
                    return redirect()->back();
                }
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {  
      
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function aramiscUploadContentEdit($id)
    {
        $editData = SmTeacherUploadContent::withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class)->where('school_id', Auth::user()->school_id)
                                            ->where('id', $id)
                                            ->first();

        if (Auth::user()->role_id != 1 && $editData->created_by != Auth::user()->id) {
            Toastr::error('This Content added by other. you cannot Modify', 'Failed');
            return redirect()->back();
        }
        $sections = SmSection::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', Auth::user()->school_id)->get();
        $contentTypes = SmContentType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

        $aramiscUploadContents = SmTeacherUploadContent::withoutGlobalScope(StatusAcademicSchoolScope::class)
                ->withoutGlobalScope(GlobalAcademicScope::class)
                ->whereNull('parent_id')->with('classes', 'sections')->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

        $classes = SmClass::withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->with('groupclassSections')->where('school_id', Auth::user()->school_id)->whereNull('parent_id')->get();
        $data = [];
        $data['editData'] = $editData;
        $data['contentTypes'] = $contentTypes;
        $data['classes'] = $classes;
        $data['sections'] = $sections;
        $data['aramiscUploadContents'] = $aramiscUploadContents;
        if (moduleStatusCheck('University')) {
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data += $interface->getCommonData($data['editData']);
        }

        return view('backEnd.teacher.aramiscUploadContentList', $data);
    }

    public function aramiscUploadContentView(Request $request, $id)
    {
        try {
            $ContentDetails = SmTeacherUploadContent::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('id', $id)
                                ->where('school_id', Auth::user()->school_id)
                                ->first();
            
            return view('backEnd.global.global_aramiscUploadContentDetails', compact('ContentDetails'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function updateUploadContent(Request $request)
    {
        
       // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $maxFileSize = SmGeneralSettings::first('file_size')->file_size;

        if($request->status != "lmsStudyMaterial"){
            if (isset($request->available_for)) {
                foreach ($request->available_for as $value) {
                    if ($value == 'student') {
                        if (!isset($request->all_classes)) {
                            $request->validate([
                                'content_title' => "required|max:200",
                                'content_type' => "required",
                                'upload_date' => "required",
                                'content_file' => "sometimes|required|mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mp3,txt",

                            ]);
                        } else {
                            $request->validate([
                                'content_title' => "required|max:200",
                                'content_type' => "required",
                                'upload_date' => "required",
                                'content_file' => "sometimes|required|mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mp3,txt",
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
                        'content_file' => "sometimes|required|mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mp3",
                    ],
                    [
                        'available_for.required' => 'At least one checkbox required!',
                    ]
                );
            }
        }
        
        try {
            $fileName = "";
            $imagemimes = ['image/png'];
            $videomimes = ['video/mp4'];
            $audiomimes = ['audio/mp3'];

            $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
            $file = $request->file('content_file');
            $fileSize =  filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size '. $maxFileSize .' Mb is set in system', 'Failed');
                return redirect()->back();
            }

            if (($request->file('content_file') != "")  && (in_array($file->getMimeType() , $videomimes))) {
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/upload_contents/', $fileName);
                $fileName = 'public/uploads/upload_contents/' . $fileName;
            } elseif ($file != "") {
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/upload_contents/', $fileName);
                $fileName = 'public/uploads/upload_contents/' . $fileName;
            }

            $y = '2012';
            $m = '2012';
            $d = '2012';
            $aramiscUploadContents = SmTeacherUploadContent::where('id', $request->id)->first();
            $aramiscUploadContents->content_title = $request->content_title;
            $aramiscUploadContents->content_type = $request->content_type;
            $aramiscUploadContents->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $aramiscUploadContents->un_academic_id = getAcademicId();
            }else{
                $aramiscUploadContents->academic_id = getAcademicId();
            }
            if (in_array('admin', $request->available_for)) {
                $aramiscUploadContents->available_for_admin = 1;
            } else {
                $aramiscUploadContents->available_for_admin = null;
            }

            if (in_array('student', $request->available_for)) {
                if (isset($request->all_classes)) {
                    $aramiscUploadContents->available_for_all_classes = 1;
                    $remove_cls_sec = SmTeacherUploadContent::where('id', $request->id)->first();
                    $remove_cls_sec->class = null;
                    $remove_cls_sec->section = null;
                    $remove_cls_sec->save();

                } else {
                    $remove_all_cls = SmTeacherUploadContent::where('id', $request->id)->first();
                    $remove_all_cls->available_for_all_classes = null;
                    $remove_all_cls->save();

                    $aramiscUploadContents->class = $request->class;
                    $aramiscUploadContents->section = $request->section;
                }
            } else {
                $aramiscUploadContents->class = null;
                $aramiscUploadContents->section = null;
                $aramiscUploadContents->available_for_all_classes = null;
            }

            $aramiscUploadContents->upload_date = date('Y-m-d', strtotime($request->upload_date));
            $aramiscUploadContents->description = $request->description;
            $aramiscUploadContents->source_url = $request->source_url;
            if ($request->file('content_file') != "") {
                $aramiscUploadContents->upload_file = $fileName;
            }
            
            $aramiscUploadContents->created_by = Auth()->user()->id;
            // $aramiscUploadContents->created_at = '2012-11-26 13:04:39';
            $results = $aramiscUploadContents->save();
            // return  $results;

            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $unStore = $interface->storeUniversityData($aramiscUploadContents, $request);
                $aramiscUploadContents->save();
            }

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
                    $roles = AramiscRole::where('id', '=', 1) /* ->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 9) */->where(function ($q) {
                        $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
                    })->get();
                    foreach ($roles as $role) {
                        $staffs = SmStaff::where('role_id', $role->id)->where('school_id', Auth::user()->school_id)->get();
                        foreach ($staffs as $staff) {
                            $notification = new SmNotification;
                            $notification->user_id = $staff->user_id;
                            $notification->role_id = $role->id;
                            $notification->school_id = Auth::user()->school_id;
                            if(moduleStatusCheck('University')){
                                $notification->un_academic_id = getAcademicId();
                            }else{
                                $notification->academic_id = getAcademicId();
                            }
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
                            $notification->message = $purpose . ' '.app('translator')->get('common.updated');
                            $notification->save();
                            try {
                                $user=User::find($notification->user_id);
                                if($user){
                                    Notification::send($user, new StudyMeterialCreatedNotification($notification));
                                }

                            } catch (\Exception $e) {
                                Log::info($e->getMessage());
                            }
                        }
                    }
                }
                if ($value == 'student') {
                    if (isset($request->all_classes)) {
                        $students = SmStudent::select('id', 'user_id')->where('academic_id', getAcademicId())->where('school_id',Auth::user()->school_id)->get();
                        foreach ($students as $student) {
                            $notification = new SmNotification;
                            $notification->user_id = $student->id;
                            $notification->role_id = 2;
                            $notification->school_id = Auth::user()->school_id;
                            if(moduleStatusCheck('University')){
                                $notification->un_academic_id = getAcademicId();
                            }else{
                                $notification->academic_id = getAcademicId();
                            }
                            if ($request->content_type == 'as') {
                                $notification->url = 'student-assignment';
                            } elseif ($request->content_type == 'st') {
                                $notification->url = 'student-study-material';
                            } elseif ($request->content_type == 'sy') {
                                $notification->url = 'student-syllabus';
                            } elseif ($request->content_type == 'ot') {
                                $notification->url = 'student-others-download';
                            }
                            $notification->date = date('Y-m-d');
                            $notification->message = $purpose . ' '.app('translator')->get('common.updated');
                            $notification->save();

                            try{
                                $user=User::find($notification->user_id);
                                if($user){
                                    Notification::send($user, new StudyMeterialCreatedNotification($notification));
                                }

                            }catch (\Exception $e) {
                                Log::info($e->getMessage());
                            }
                        }
                    } else {
                        $student_ids = StudentRecord::where('class_id', $request->class)->when($request->section, function($q) use($request){
                            return $q->where('section_id', $request->section);
                        })->where('academic_id', getAcademicId())->where('school_id',Auth::user()->school_id)->pluck('student_id')->unique()->toArray();

                        
                        $students = SmStudent::select('id')->whereIn('id', $student_ids)->get();
                        foreach ($students as $student) {
                            $notification = new SmNotification;
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
                            $notification->date = date('Y-m-d');
                            $notification->message = $purpose . ' '.app('translator')->get('common.updated');
                            $notification->school_id = Auth::user()->school_id;
                            if(moduleStatusCheck('University')){
                                $notification->un_academic_id = getAcademicId();
                            }else{
                                $notification->academic_id = getAcademicId();
                            }
                            $notification->save();

                            try{
                                $user=User::find($notification->user_id);
                                if($user){
                                    Notification::send($user, new StudyMeterialCreatedNotification($notification));
                                }
                                
                            }catch (\Exception $e) {
                                Log::info($e->getMessage());
                            }


                            try{
                                $user=User::find($notification->user_id);
                                if($user){
                                    Notification::send($user, new StudyMeterialCreatedNotification($notification));
                                }
                            }catch (\Exception $e) {
                                Log::info($e->getMessage());
                            }
                        }
                    }
                }
            }

            if ($results) {
                Toastr::success('Update Operation successful', 'Success');
                if ($request->status == "lmsStudyMaterial") {
                    $type = $request->modal=='is_modal' ? 'study_material' : 'course_curriculum';
                    return redirect()->back();
                } else {
                    if($request->content_type == "as"){
                        return redirect()->route('assignment-list');
                    }
                    elseif($request->content_type =="sy"){
                        return redirect()->route('syllabus-list');
                    }
                    elseif($request->content_type =="st"){
                        return redirect()->route('study-metarial-list');
                    }else{
                        return redirect()->route('other-download-list');
                    }
                }
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function aramiscAssignmentList(Request $request)
    {
        try {
            $user = Auth()->user();

            if (!teacherAccess()) {
                SmNotification::where('user_id', $user->id)->where('role_id', 1)->update(['is_read' => 1]);
            }

            if (!teacherAccess()) {
                    $aramiscUploadContents = SmTeacherUploadContent::where('content_type', 'as')
                    ->where('academic_id', getAcademicId())
                    ->where('course_id', '=', null)
                    ->where('chapter_id', '=', null)
                    ->where('lesson_id', '=', null)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            } else {
                $aramiscUploadContents = SmTeacherUploadContent::where(function ($q) {
                    $q->where('created_by', Auth::user()->id)->orWhere('available_for_admin', 1);
                })->where('content_type', 'as')
                ->where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            }

            return view('backEnd.teacher.aramiscAssignmentList', compact('aramiscUploadContents'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studyMetarialList(Request $request)
    {
        try {
            if (teacherAccess()) {
                $aramiscUploadContents = SmTeacherUploadContent::where(function ($q) {
                    $q->where('created_by', Auth::user()->id)->orWhere('available_for_admin', 1);
                })->where('content_type', 'st')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            } else {
                $aramiscUploadContents = SmTeacherUploadContent::where('content_type', 'st')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($aramiscUploadContents->toArray(), 'null');
            }
            return view('backEnd.teacher.studyMetarialList', compact('aramiscUploadContents'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function aramiscSyllabusList(Request $request)
    {
        try {
            if (teacherAccess()) {
                $aramiscUploadContents = SmTeacherUploadContent::with('classes', 'sections')->where(function ($q) {
                    $q->where('created_by', Auth::user()->id)->orWhere('available_for_admin', 1);
                })->where('content_type', 'sy')
                ->where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            } else {
                $aramiscUploadContents = SmTeacherUploadContent::with('classes', 'sections')
                ->where('content_type', 'sy')
                ->where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            }
            return view('backEnd.teacher.aramiscSyllabusList', compact('aramiscUploadContents'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function aramiscOtherDownloadList(Request $request)
    {

        try {
            if (teacherAccess()) {
                $aramiscUploadContents = SmTeacherUploadContent::with('classes', 'sections')->where(function ($q) {
                    $q->where('created_by', Auth::user()->id)->orWhere('available_for_admin', 1);
                })->where('content_type', 'ot')
                ->where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->Where('created_by', Auth::user()->id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            } else {
                $aramiscUploadContents = SmTeacherUploadContent::with('classes', 'sections')
                ->where('content_type', 'ot')
                ->where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            }

            return view('backEnd.teacher.otherDownloadList', compact('aramiscUploadContents'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteUploadContent(Request $request)
    {
        try {
             $id =  $request->id;
            if (checkAdmin()) {
                $aramiscUploadContent = SmTeacherUploadContent::find($id);
            } else {
                $aramiscUploadContent = SmTeacherUploadContent::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }
            if (checkAdmin() || $aramiscUploadContent->created_by == Auth::user()->id) {
                if (file_exists($aramiscUploadContent->upload_file)) {
                    unlink($aramiscUploadContent->upload_file);
                }
                $aramiscUploadContent->delete();
                if($request->status == 'lmsStudy'){
                    return response()->json(['sucess']);
                }else{
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->route('upload-content');
                }
            } else {
                Toastr::error('This Content is added by other. You Cannot DELETE', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->route('upload-content');
        }
    }

    public function aramiscStudyMaterialModal(Request $request){
        try {
            
              $data = SmTeacherUploadContent::query(); 
              $data->withoutGlobalScope(StatusAcademicSchoolScope::class)
                                            ->withoutGlobalScope(GlobalAcademicScope::class)
                                            ->where('class',$request->global_class_id);
            $data->when($request->global_section_id, function ($query) use ($request){
                $query->where('section', $request->global_section_id);
            });                          
                $aramiscUploadContents = $data->where('school_id', Auth::user()->school_id)
                ->where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->orderby('id', 'DESC')
                ->get();          

            return view('backEnd.global.global_study_material_modal', compact('aramiscUploadContents'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function aramiscStudyMaterialAssigned(Request $request){

        try{
            $selected_ids = $request->study_material;

            if($selected_ids){
                foreach($selected_ids as $aramiscUploadContent){
                     $parentContent = SmTeacherUploadContent::withoutGlobalScope(StatusAcademicSchoolScope::class)
                                                            ->withoutGlobalScope(GlobalAcademicScope::class)
                                                            ->find($aramiscUploadContent);
                    if($parentContent && $parentContent->class){
                        $class = SmClass::where('parent_id',$parentContent->class)->where('academic_id',getAcademicId())->first();
                    } 
                    
                    if($parentContent && $parentContent->section){
                        $section = SmSection::where('parent_id',$parentContent->section)->where('academic_id',getAcademicId())->first();
                    } 
                    
                    $checkExist = SmTeacherUploadContent::where('parent_id', $aramiscUploadContent)->first();
                    if(! $checkExist){
                        $aramiscUploadContents = new SmTeacherUploadContent();
                        $aramiscUploadContents->parent_id = $parentContent->id ;
                        $aramiscUploadContents->content_title = $parentContent->content_title;
                        $aramiscUploadContents->content_type = $parentContent->content_type;
                        $aramiscUploadContents->school_id = Auth::user()->school_id;
                        $aramiscUploadContents->academic_id = getAcademicId();
                        $aramiscUploadContents->available_for_admin = $parentContent->available_for_admin;
                        $aramiscUploadContents->class = @$class->id;
                        $aramiscUploadContents->section = @$section->id;
                        $aramiscUploadContents->upload_date = $parentContent->upload_date ;
                        $aramiscUploadContents->description = $parentContent->description;
                        $aramiscUploadContents->source_url = $parentContent->source_url;
                        $aramiscUploadContents->upload_file = $parentContent->upload_file;
                        $aramiscUploadContents->created_by = auth()->user()->id;
                        $results = $aramiscUploadContents->save();
    
                        if ($results && ($aramiscUploadContents == 'student')) {
                            if (isset($aramiscUploadContents->available_for_all_classes)) {
                                $students = SmStudent::select('id', 'user_id')->where('school_id', Auth::user()->school_id)->get();
                                foreach ($students as $student) {
                                    $notification = new SmNotification;
                                    $notification->user_id = $student->user_id;
                                    $notification->role_id = 2;
                                    $notification->school_id = Auth::user()->school_id;
                                    if(moduleStatusCheck('University')){
                                        $notification->un_academic_id = getAcademicId();
                                    }else{
                                        $notification->academic_id = getAcademicId();
                                    }
                                    if ($request->content_type == 'as') {
                                        $notification->url = 'student-assignment';
                                    } elseif ($request->content_type == 'st') {
                                        $notification->url = 'student-study-material';
                                    } elseif ($request->content_type == 'sy') {
                                        $notification->url = 'student-syllabus';
                                    } elseif ($request->content_type == 'ot') {
                                        $notification->url = 'student-others-download';
                                    }
                                    $notification->date = date('Y-m-d');
                                    $notification->message = $purpose . ' '.app('translator')->get('common.uploaded');
                                    $notification->save();
        
                                    try {
                                        $user=User::find($notification->user_id);
                                        Notification::send($user, new StudyMeterialCreatedNotification($notification));
                                    } catch (\Exception $e) {
                                        Log::info($e->getMessage());
                                    }
                                }
                            } elseif ((!is_null($request->class)) &&   ($request->section == '')) {
                                $students = SmStudent::select('id', 'user_id')->whereIn('id', $student_ids)->where('school_id', Auth::user()->school_id)->get();
                                foreach ($students as $student) {
                                    $notification = new SmNotification;
                                    $notification->user_id = $student->user_id;
                                    $notification->role_id = 2;
                                    $notification->school_id = Auth::user()->school_id;
                                    if(moduleStatusCheck('University')){
                                        $notification->un_academic_id = getAcademicId();
                                    }else{
                                        $notification->academic_id = getAcademicId();
                                    }
                                    if ($request->content_type == 'as') {
                                        $notification->url = 'student-assignment';
                                    } elseif ($request->content_type == 'st') {
                                        $notification->url = 'student-study-material';
                                    } elseif ($request->content_type == 'sy') {
                                        $notification->url = 'student-syllabus';
                                    } elseif ($request->content_type == 'ot') {
                                        $notification->url = 'student-others-download';
                                    }
                                    $notification->date = date('Y-m-d');
                                    $notification->message = $purpose . ' '.app('translator')->get('common.uploaded');
                                    $notification->save();
        
                                    try {
                                        $user=User::find($notification->user_id);
                                        Notification::send($user, new StudyMeterialCreatedNotification($notification));
                                    } catch (\Exception $e) {
                                        Log::info($e->getMessage());
                                    }
                                }
                            } else {
                                $students = SmStudent::select('id', 'user_id')->whereIn('id', $student_ids)->where('school_id', Auth::user()->school_id)->get();
                                foreach ($students as $student) {
                                    $notification = new SmNotification;
                                    $notification->user_id = $student->user_id;
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
                                    $notification->date = date('Y-m-d');
                                    $notification->message = $purpose . ' '.app('translator')->get('common.uploaded');
                                    $notification->school_id = Auth::user()->school_id;
                                    if(moduleStatusCheck('University')){
                                        $notification->un_academic_id = getAcademicId();
                                    }else{
                                        $notification->academic_id = getAcademicId();
                                    }
                                    $notification->save();
                                    try {
                                        $user=User::find($notification->user_id);
                                        Notification::send($user, new StudyMeterialCreatedNotification($notification));
                                    } catch (\Exception $e) {
                                        Log::info($e->getMessage());
                                    }
        
                                    try {
                                        $user=User::find($notification->user_id);
                                        Notification::send($user, new StudyMeterialCreatedNotification($notification));
                                    } catch (\Exception $e) {
                                        Log::info($e->getMessage());
                                    }
                                }
                            }
                        }
                    }
                    
                }
            }  

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        }
        catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }
}
