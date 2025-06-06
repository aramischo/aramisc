<?php

namespace Modules\ExamPlan\Http\Controllers;

use App\AramiscExam;
use App\AramiscClass;
use App\AramiscStudent;
use App\AramiscExamType;
use App\AramiscExamSchedule;
use App\AramiscAssignSubject;
use App\Traits\ImageStore;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\Traits\NotificationSend;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Session;
use Modules\ExamPlan\Entities\AdmitCard;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Support\Renderable;
use Modules\ExamPlan\Entities\AdmitCardSetting;
use Modules\University\Entities\UnAssignSubject;

class AdmitCardSettingController extends Controller
{
    use NotificationSend;
    public function setting()
    {
        $setting = AdmitCardSetting::where('school_id', Auth::user()->school_id);
        if (moduleStatusCheck('University')) {
            $setting = $setting->where('un_academic_id', getAcademicId());
        } else {
            $setting = $setting->where('academic_id', getAcademicId());
        }
        $setting =$setting->first();
        if (!$setting) {
            $oldSetting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->latest()->first();
            $setting = $oldSetting->replicate();
            if (moduleStatusCheck('University')) {
                $setting->un_academic_id = getAcademicId();
            } else {
                $setting->academic_id = getAcademicId();
            }
            $setting->save();
        }

        return view('examplan::setting.admitCardSetting', compact('setting'));
    }


    public function settingUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'principal_signature_photo_2' => 'sometimes|nullable|mimes:jpg,png,jpeg|max:40000',
            'principal_signature_photo' => 'sometimes|nullable|mimes:jpg,png,jpeg|max:40000',
            'teacher_signature_photo' => 'sometimes|nullable|mimes:jpg,png,jpeg|max:40000',
        ]);


        if ($validator->fails()) {
            return redirect()->route('examplan.admitcard.setting')
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $setting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->first();
            if (!$setting) {
                $oldSetting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->latest()->first();
                $setting = $oldSetting->replicate();
            }
            if ($request->tab_layout == 2) {
                $principle_signature = fileUpdate($setting->principal_signature_photo, $request->principal_signature_photo_2, 'public/uploads/examplan/');
            } else {
                $principle_signature = fileUpdate($setting->principal_signature_photo, $request->principal_signature_photo, 'public/uploads/examplan/');
            }
            $teacher_signature = fileUpdate($setting->teacher_signature_photo, $request->teacher_signature_photo, 'public/uploads/examplan/');

            $setting->student_photo = $request->student_photo;
            $setting->student_name = $request->student_name;
            $setting->admission_no = $request->admission_no;
            $setting->class_section = $request->class_section;
            $setting->exam_name = $request->exam_name;
            if ($request->tab_layout == 2) {
                $setting->admit_sub_title = $request->admit_sub_title;
                $setting->description = $request->description;
            }
            $setting->academic_year = $request->academic_year;
            $setting->principal_signature = $request->principal_signature;
            $setting->gaurdian_name = $request->gaurdian_name;
            $setting->school_address = $request->school_address;
            $setting->student_download = $request->student_download;
            $setting->parent_download = $request->parent_download;
            $setting->student_notification = $request->student_notification;
            $setting->parent_notification = $request->parent_notification;
            $setting->class_teacher_signature = $request->class_teacher_signature;
            $setting->principal_signature_photo = $principle_signature;
            $setting->teacher_signature_photo = $teacher_signature;
            $setting->save();
            Toastr::success('Update Successfully', 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Error');
            return redirect()->back();
        }
    }

    public function imageUpload(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'logo_pic' => 'sometimes|required|mimes:jpg,png,jpeg|max:40000',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => 'error'], 201);
            }
            if ($r->hasFile('logo_pic')) {
                $file = $r->file('logo_pic');
                $images = Image::make($file)->insert($file);
                $pathImage = 'Modules/ExamPlan/Public/images';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('Modules/ExamPlan/Public/images/' . $name);
                    $imageName = 'Modules/ExamPlan/Public/images/' . $name;
                    Session::put('class_teacher_sign', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('class_teacher_sign'))) {
                        File::delete(Session::get('class_teacher_sign'));
                    }
                    $images->save('Modules/ExamPlan/Public/images/' . $name);
                    $imageName = 'Modules/ExamPlan/Public/images/' . $name;
                    Session::put('class_teacher_sign', $imageName);
                }
            }
            // parent
            if ($r->hasFile('fathers_photo')) {
                $file = $r->file('fathers_photo');
                $images = Image::make($file)->insert($file);
                $pathImage = 'Modules/ExamPlan/Public/images/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('Modules/ExamPlan/Public/images/' . $name);
                    $imageName = 'Modules/ExamPlan/Public/images/' . $name;
                    Session::put('principal_sign', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('fathers_photo'))) {
                        File::delete(Session::get('fathers_photo'));
                    }
                    $images->save('Modules/ExamPlan/Public/images/' . $name);
                    $imageName = 'Modules/ExamPlan/Public/images/' . $name;
                    Session::put('principal_sign', $imageName);
                }
            }

            return response()->json('success', 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'error'], 201);
        }
    }


    public function admitcard()
    {
        try {
            $exams = AramiscExamType::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $classes = AramiscClass::where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)->get();
            return view('examplan::admitCard', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Error');
            return redirect()->back();
        }
    }

    public function index()
    {
        return view('examplan::create');
    }
    function universityAdmitCardSearch($request)
    {
        
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'un_session_id' => 'required',
                'un_faculty_id'=>'required',
                'un_department_id'=>'required',
                'un_academic_id'=>'required',
                'un_semester_id'=>'required',
                'un_semester_label_id'=>'required',
                'un_section_id'=>'required',
                'exam_type'=>'required',
            ],[
                'un_session_id.required'=>'The session field is required',
                'un_faculty_id.required'=>'The faculty field is required',
                'un_department_id.required'=>'The department field is required',
                'un_academic_id.required'=>'The academic field is required',
                'un_semester_id.required'=>'The semester field is required',
                'un_semester_label_id.required'=>'The semester label field is required',
                'un_section_id.required'=>'The section field is required',
                'exam_type.required'=>'The exam type field is required',
            ]);
            if ($validator->fails()) {
                return redirect()->route('examplan.admitcard.index')
                    ->withErrors($validator)
                    ->withInput();
            }
            // dd($request->all());
            $exam = AramiscExamSchedule::query();
            $exam_id = $request->exam_type;
            $class_id = $request->class;
            $exam->where('school_id', auth()->user()->school_id)->where('academic_id', getAcademicId());
            if ($request->exam_type != "") {
                $exam->where('exam_term_id', $request->exam_type);
            }
            if ($request->un_semester_label_id != "") {
                $exam->where('un_semester_label_id', $request->un_semester_label_id);
            }
            if ($request->un_section_id != "") {
                $exam->where('un_section_id', $request->un_section_id);
            }
            $exam_routine = $exam->get();
           
            $old_admits = AdmitCard::where('exam_type_id', $request->exam_type)
                ->where('school_id', Auth::user()->school_id)
                ->where('un_academic_id', getAcademicId())
                ->get(['student_record_id']);

            $old_admit_ids = [];
            foreach ($old_admits as $admit) {
                $old_admit_ids[] = $admit->student_record_id;
            }
            $active_status = 1;
            if ($exam_routine) {
                $student_records = StudentRecord::query();
                $student_records->where('school_id', auth()->user()->school_id)
                    ->where('un_academic_id', getAcademicId())
                    ->whereHas('student', function ($q) {
                        $q->where('active_status', 1);
                    });
                    // ->where('is_promote', 0);
                if ($request->un_semester_label_id != "") {
                    $student_records->where('un_semester_label_id', $request->un_semester_label_id);
                }
                if ($request->un_section_id != "") {
                    $student_records->where('un_section_id', $request->un_section_id);
                }

                $records = $student_records->get();

                // dd($records);
                
                return view('examplan::admitCard', compact('records', 'exam_id', 'class_id', 'old_admit_ids'));
            } else {
                Toastr::warning('Exam shedule is not ready', 'warning');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Error');
            return redirect()->back();
        }
    }

    public function admitcardSearch(Request $request)
    {
        if (moduleStatusCheck('University')) {
            return  $this->universityAdmitCardSearch($request);
        } else {
            try {
                $input = $request->all();
                $validator = Validator::make($input, [
                    'exam' => 'required',
                    'class' => 'required',
                    'section' => 'required',
                ]);
                if ($validator->fails()) {
                    return redirect()->route('examplan.admitcard.index')
                        ->withErrors($validator)
                        ->withInput();
                }

                $exam = AramiscExamSchedule::query();
                $exam_id = $request->exam;
                $class_id = $request->class;
                $exam->where('school_id', auth()->user()->school_id)->where('academic_id', getAcademicId());
                if ($request->exam != "") {
                    $exam->where('exam_term_id', $request->exam);
                }
                if ($request->class != "") {
                    $exam->where('class_id', $request->class);
                }
                if ($request->section != "") {
                    $exam->where('section_id', $request->section);
                }
                $exam_routine = $exam->get();

                $old_admits = AdmitCard::where('exam_type_id', $request->exam)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get(['student_record_id']);

                $old_admit_ids = [];
                foreach ($old_admits as $admit) {
                    $old_admit_ids[] = $admit->student_record_id;
                }
                $active_status = 1;
                if ($exam_routine) {
                    $student_records = StudentRecord::query();
                    $student_records->where('school_id', auth()->user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->whereHas('student', function ($q) {
                            $q->where('active_status', 1);
                        })
                        ->where('is_promote', 0);
                    if ($request->class != "") {
                        $student_records->where('class_id', $request->class);
                    }
                    if ($request->section != "") {
                        $student_records->where('section_id', $request->section);
                    }

                    $records = $student_records->get();


                    $exams = AramiscExamType::where('active_status', 1)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->get();
                    $classes = AramiscClass::where('academic_id', getAcademicId())
                        ->where('school_id', auth()->user()->school_id)
                        ->get();
                    return view('examplan::admitCard', compact('exams', 'classes', 'records', 'exam_id', 'class_id', 'old_admit_ids'));
                } else {
                    Toastr::warning('Exam shedule is not ready', 'warning');
                    return redirect()->back();
                }
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Error');
                return redirect()->back();
            }
        }
    }
    function UniversityAdmitCardGenerate($request){
        try {
            $student_records = [];
            $studentRecord = null;
            $setting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->where('un_academic_id', getAcademicId())->first();
            if (!$setting) {
                $oldSetting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->latest()->first();
                $setting = $oldSetting->replicate();
                $setting->un_academic_id = getAcademicId();
                $setting->save();
            }
           
            if ($request->data) {
                foreach ($request->data as $key => $data) {
                    if (count($data) == 2) {
                        $student_records[] = $data['student_record_id'];
                    }
                }

                foreach ($student_records as $record) {
                    $admit_card = AdmitCard::where('exam_type_id', $request->exam_type_id)->where('student_record_id', $record)->first();
                   
                    $studentRecord = StudentRecord::find($record);
                    if (!$admit_card) {
                        $new_admit = new AdmitCard();
                        $new_admit->student_record_id = $record;
                        $new_admit->exam_type_id = $request->exam_type_id;
                        $new_admit->created_by = Auth::id();
                        $new_admit->school_id = Auth::user()->school_id;
                        $new_admit->un_academic_id = getAcademicId();
                        $new_admit->save();

                        $un_semester_label_id = $new_admit->studentRecord->un_semester_label_id;
                        $un_section_id = $new_admit->studentRecord->un_section_id;
                        $records=StudentRecord::with('studentDetail')->where('un_semester_label_id',$un_semester_label_id)->where('un_section_id',$un_section_id)->distinct('student_id')->get();
                        $records=$records->pluck('studentDetail.user_id');
                        $this->sent_notifications('Exam_Admit_Card', $records, $data, ['Student', 'Parent']);
                    }
                }
                $admitcards = AdmitCard::whereIn('student_record_id', $student_records)->where('exam_type_id', $request->exam_type_id)->with('studentRecord')->get();
                $assign_subjects = UnAssignSubject::where('un_semester_label_id', $studentRecord->un_semester_label_id)->where('school_id', Auth::user()->school_id)->get();
                $exam_routines = AramiscExamSchedule::where('un_semester_label_id', $studentRecord->un_semester_label_id)
                    ->where('un_section_id', $studentRecord->un_section_id)
                    ->where('exam_term_id', $request->exam_type_id)->orderBy('date', 'ASC')->get();

                if ($setting->admit_layout == 2) {
                    return view('university::exam_plan.admitcardPrint_2', compact('setting', 'assign_subjects', 'exam_routines', 'admitcards'));
                } elseif ($setting->admit_layout == 1) {
                    return view('university::exam_plan.admitcardPrint', compact('setting', 'assign_subjects', 'exam_routines', 'admitcards'));
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Error');
            return redirect()->route('examplan.admitcard.index');
        }
    }

    public function admitcardGenerate(Request $request)
    {
        if(moduleStatusCheck('University')){
            return $this->UniversityAdmitCardGenerate($request);
        }else{
            try {
                $student_records = [];
                $studentRecord = null;
                $setting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->first();
                if (!$setting) {
                    $oldSetting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->latest()->first();
                    $setting = $oldSetting->replicate();
                    $setting->academic_id = getAcademicId();
                    $setting->save();
                }
                if ($request->data) {
                    foreach ($request->data as $key => $data) {
                        if (count($data) == 2) {
                            $student_records[] = $data['student_record_id'];
                        }
                    }
    
                    foreach ($student_records as $record) {
                        $admit_card = AdmitCard::where('exam_type_id', $request->exam_type_id)->where('student_record_id', $record)->first();
                        $studentRecord = StudentRecord::find($record);
                        if (!$admit_card) {
                            $new_admit = new AdmitCard();
                            $new_admit->student_record_id = $record;
                            $new_admit->exam_type_id = $request->exam_type_id;
                            $new_admit->created_by = Auth::id();
                            $new_admit->school_id = Auth::user()->school_id;
                            $new_admit->academic_id = getAcademicId();
                            $new_admit->save();
    
                            $data['class_id'] = $new_admit->studentRecord->class_id;
                            $data['section_id'] = $new_admit->studentRecord->section_id;
                            $records = $this->studentRecordInfo($data['class_id'], $data['section_id'])->pluck('studentDetail.user_id');
                            $this->sent_notifications('Exam_Admit_Card', $records, $data, ['Student', 'Parent']);
    
                        }
                    }
                    $admitcards = AdmitCard::whereIn('student_record_id', $student_records)->where('exam_type_id', $request->exam_type_id)->with('studentRecord')->get();
                    $assign_subjects = AramiscAssignSubject::where('class_id', $studentRecord->class_id)->where('section_id', $studentRecord->section_id)
                        ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                    $exam_routines = AramiscExamSchedule::where('class_id', $studentRecord->class_id)
                        ->where('section_id', $studentRecord->section_id)
                        ->where('exam_term_id', $request->exam_type_id)->orderBy('date', 'ASC')->get();
    
                    if ($setting->admit_layout == 2) {
    
                        return view('examplan::admitcardPrint_2', compact('setting', 'assign_subjects', 'exam_routines', 'admitcards'));
                    } elseif ($setting->admit_layout == 1) {
                        return view('examplan::admitcardPrint', compact('setting', 'assign_subjects', 'exam_routines', 'admitcards'));
                    }
                }
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Error');
                return redirect()->route('examplan.admitcard.index');
            }
        }
       
    }

    public function changeAdmitCardLayout(Request $request)
    {
        $setting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->first();
        if (!$setting) {
            $oldSetting = AdmitCardSetting::where('school_id', Auth::user()->school_id)->latest()->first();
            $setting = $oldSetting->replicate();
            $setting->academic_id = getAcademicId();
        }

        $setting->admit_layout = $request->layout;
        $setting->save();
        return response()->json('success');
    }
}
