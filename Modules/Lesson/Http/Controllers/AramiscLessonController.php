<?php

namespace Modules\Lesson\Http\Controllers;
use DataTables;
use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscSection;
use App\AramiscSubject;
use App\AramiscAssignSubject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\Lesson\Entities\AramiscLesson;
use Illuminate\Support\Facades\Config;
use Modules\Lesson\Entities\LessonPlanner;
use Modules\Lesson\Entities\AramiscLessonTopic;
use Modules\University\Entities\UnSubject;
use Modules\Lesson\Entities\AramiscLessonTopicDetail;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class AramiscLessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }
    public function index()
    {
        try {
            $data = $this->loadLesson();
            return view('lesson::lesson.add_new_lesson', $data);
        } catch (\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeLesson(Request $request)
    {
        if (moduleStatusCheck('University')) {
            $request->validate(
                [
                    'un_session_id' => 'required',
                    'un_faculty_id' => 'sometimes|nullable',
                    'un_department_id' => 'required',
                    'un_academic_id' => 'required',
                    'un_semester_id' => 'required',
                    'un_semester_label_id' => 'required',
                    'un_subject_id' => 'required',
                    'un_section_id' => 'sometimes|nullable',
                ],
            );
        } else {
            $request->validate(
                [
                    'class' => 'required',
                    'subject' => 'required',
                ],
            );
        }

        DB::beginTransaction();
        try {
            $sections = AramiscAssignSubject::where('class_id', $request->class)
                ->where('subject_id', $request->subject)
                ->get();
            if (moduleStatusCheck('University')) {
                if ($request->un_section_id) {
                    $sections = UnSubject::where('un_department_id', $request->un_department_id)
                    ->where('school_id', auth()->user()->school_id)
                    ->get();
                } else {
                    $sections = $request->un_section_id;
                }
            }

            foreach ($sections as $section) {
                foreach ($request->lesson as $lesson) {
                    $aramiscLesson = new AramiscLesson;
                    $aramiscLesson->lesson_title = $lesson;
                    $aramiscLesson->class_id = $request->class;
                    $aramiscLesson->subject_id = $request->subject;
                    $aramiscLesson->section_id = $section->section_id;
                    $aramiscLesson->school_id = auth()->user()->school_id;
                    $aramiscLesson->user_id = auth()->user()->id;
                    if (moduleStatusCheck('University')) {
                        $common = App::make(UnCommonRepositoryInterface::class);
                        $common->storeUniversityData($aramiscLesson, $request);
                    }else{
                        $aramiscLesson->academic_id = getAcademicId();
                    }
                    $aramiscLesson->save();
                }
            }
            DB::commit();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editLesson($class_id, $section_id, $subject_id)
    {
        try {
            $data = $this->loadLesson();
            $data['lesson'] = AramiscLesson::where([['class_id', $class_id], ['section_id', $section_id], ['subject_id', $subject_id]])->first();
            $data['lesson_detail'] = AramiscLesson::where([['class_id', $class_id], ['section_id', $section_id], ['subject_id', $subject_id]])->get();
            return view('lesson::lesson.edit_lesson', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editLessonForUniVersity($session_id, $faculty_id = null, $department_id, $academic_id, $semester_id, $semester_label_id, $subject_id)
    {
        try {
            $data = $this->loadLesson();
            $lesson = AramiscLesson::when($session_id, function ($query) use ($session_id) {
                $query->where('un_session_id', $session_id);
            })->when($faculty_id !=0, function ($query) use ($faculty_id) {
                $query->where('un_faculty_id', $faculty_id);
            })->when($department_id, function ($query) use ($department_id) {
                $query->where('un_department_id', $department_id);
            })->when($academic_id, function ($query) use ($academic_id) {
                $query->where('un_academic_id', $academic_id);
            })->when($semester_id, function ($query) use ($semester_id) {
                $query->where('un_semester_id', $semester_id);
            })->when($semester_label_id, function ($query) use ($semester_label_id) {
                $query->where('un_semester_label_id', $semester_label_id);
            })->when($subject_id !=0, function ($query) use ($subject_id) {
                $query->where('un_subject_id', $subject_id);
            });
            $data['lesson_detail'] = $lesson->get();
            $data['lesson'] = $lesson->first();
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data += $interface->getCommonData($data['lesson']);
            return view('lesson::lesson.edit_lesson', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function updateLesson(Request $request)
    {
        try {
            $existingLessons = AramiscLesson::whereIn('id', $request->lesson_detail_id)->get();
            foreach ($existingLessons as $key => $lesson) {
                $lesson->lesson_title = $request->lesson[$key];
                $lesson->school_id    = Auth::user()->school_id;
                $lesson->academic_id  = getAcademicId();
                $lesson->user_id      = Auth::user()->id;
                $lesson->save();
            }
    
            $newLessonCount = count($request->lesson) - count($existingLessons);
    
            if ($newLessonCount > 0) {
                $lastLessonId = AramiscLesson::orderBy('id', 'desc')->first()->id ?? 0;
    
                for ($i = count($existingLessons); $i < count($request->lesson); $i++) {
                    $newLesson = new AramiscLesson;
                    $newLesson->id              = ++$lastLessonId;
                    $newLesson->lesson_title    = $request->lesson[$i];
                    $newLesson->class_id        = $existingLessons->first()->class_id;
                    $newLesson->subject_id      = $existingLessons->first()->subject_id;
                    $newLesson->section_id      = $existingLessons->first()->section_id;
                    $newLesson->school_id       = Auth::user()->school_id;
                    $newLesson->academic_id     = getAcademicId();
                    $newLesson->user_id         = Auth::user()->id;
                    $newLesson->save();
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->route('lesson');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteLesson($id)
    {
        $lesson = AramiscLesson::find($id);
        $lesson_detail = AramiscLesson::where([['class_id', $lesson->class_id], ['section_id', $lesson->section_id], ['subject_id', $lesson->subject_id]])->get();
        foreach ($lesson_detail as $lesson_data) {
            AramiscLesson::destroy($lesson_data->id);
        }
        $AramiscLessonTopic = AramiscLessonTopic::where('lesson_id', $id)->get();
        if ($AramiscLessonTopic) {
            foreach ($AramiscLessonTopic as $t_data) {
                AramiscLessonTopic::destroy($t_data->id);
            }
        }
        $AramiscLessonTopicDetail = AramiscLessonTopicDetail::where('lesson_id', $id)->get();
        if ($AramiscLessonTopicDetail) {
            foreach ($AramiscLessonTopicDetail as $td_data) {
                AramiscLessonTopicDetail::destroy($td_data->id);
            }
        }
        $LessonPlanner = LessonPlanner::where('lesson_id', $id)->get();
        if ($LessonPlanner) {
            foreach ($LessonPlanner as $lp_data) {
                LessonPlanner::destroy($lp_data->id);
            }
        }
        Toastr::success('Operation successful', 'Success');
        return redirect()->route('lesson');
    }

    public function destroyLesson(Request $request)
    {   $id = $request->id;
       return $lesson = AramiscLesson::find($id);
        $lesson_detail = AramiscLesson::where([['class_id', $lesson->class_id], ['section_id', $lesson->section_id], ['subject_id', $lesson->subject_id]])->get();
        foreach ($lesson_detail as $lesson_data) {
            AramiscLesson::destroy($lesson_data->id);
        }
        $AramiscLessonTopic = AramiscLessonTopic::where('lesson_id', $id)->get();
        if ($AramiscLessonTopic) {
            foreach ($AramiscLessonTopic as $t_data) {
                AramiscLessonTopic::destroy($t_data->id);
            }
        }
        $AramiscLessonTopicDetail = AramiscLessonTopicDetail::where('lesson_id', $id)->get();
        if ($AramiscLessonTopicDetail) {
            foreach ($AramiscLessonTopicDetail as $td_data) {
                AramiscLessonTopicDetail::destroy($td_data->id);
            }
        }
        $LessonPlanner = LessonPlanner::where('lesson_id', $id)->get();
        if ($LessonPlanner) {
            foreach ($LessonPlanner as $lp_data) {
                LessonPlanner::destroy($lp_data->id);
            }
        }
        Toastr::success('Operation successful', 'Success');
        return redirect()->route('lesson');
    }

    public function deleteLessonItem($id)
    {
        try {
            $lesson = AramiscLesson::find($id);
            $lesson->delete();
            $AramiscLessonTopic = AramiscLessonTopic::where('lesson_id', $id)->get();
            if ($AramiscLessonTopic) {
                foreach ($AramiscLessonTopic as $t_data) {
                    AramiscLessonTopic::destroy($t_data->id);
                }
            }
            $AramiscLessonTopicDetail = AramiscLessonTopicDetail::where('lesson_id', $id)->get();
            if ($AramiscLessonTopicDetail) {
                foreach ($AramiscLessonTopicDetail as $td_data) {
                    AramiscLessonTopicDetail::destroy($td_data->id);
                }
            }
            $LessonPlanner = LessonPlanner::where('lesson_id', $id)->get();
            if ($LessonPlanner) {
                foreach ($LessonPlanner as $lp_data) {
                    LessonPlanner::destroy($lp_data->id);
                }
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->route('lesson');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function lessonPlanner()
    {
        return view('lesson::lesson.lesson_planner');
    }

    public function loadLesson()
    {
        $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
        $subjects = AramiscAssignSubject::select('subject_id')
        ->where('teacher_id', $teacher_info->id)->get();

        $data['subjects'] = AramiscSubject::where('active_status', 1)
        ->where('academic_id', getAcademicId())
        ->where('school_id', Auth::user()->school_id)->get();
        $data['sections'] = AramiscSection::where('active_status', 1)
        ->where('academic_id', getAcademicId())
        ->where('school_id', Auth::user()->school_id)->get();

        if (Auth::user()->role_id == 4) {
            $data['lessons'] = AramiscLesson::with('lessons', 'class', 'section', 'subject')
            ->whereIn('subject_id', $subjects)->statusCheck()->groupBy(['class_id','section_id','subject_id'])
            ->get();
        } else {
            $data['lessons'] = AramiscLesson::with('lessons', 'class', 'section', 'subject')
                ->statusCheck()
                ->select('class_id','section_id','subject_id','lesson_title','active_status','id')->groupBy(['class_id','section_id','subject_id'])
                ->get();
        }
        if (!teacherAccess()) {
            $data['classes'] = AramiscClass::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)->get();
        } else {
            $data['classes'] = AramiscAssignSubject::where('teacher_id', $teacher_info->id)
                ->join('aramisc_classes', 'aramisc_classes.id', 'aramisc_assign_subjects.class_id')
                ->where('aramisc_assign_subjects.academic_id', getAcademicId())
                ->where('aramisc_assign_subjects.active_status', 1)
                ->where('aramisc_assign_subjects.school_id', Auth::user()->school_id)
                ->select('aramisc_classes.id', 'class_name')
                ->groupBy('aramisc_classes.id')
                ->get();
        }
        return $data;
    }


    public function lessonListAjax(Request $request)
    {
        if(!$request->ajax()){
            if (Auth::user()->role_id == 4) {
                $lessons = AramiscLesson::with('lessons', 'class', 'section', 'subject')
                ->whereIn('subject_id', $subjects)->statusCheck()
                ->get();
            } else {
                $lessons = AramiscLesson::with('lessons', 'class', 'section', 'subject')
                    ->statusCheck()
                    ->get();
            }
            return Datatables::of($lessons)
            ->addIndexColumn()
            ->addColumn('lesson_name', function ($row){
                $lesson_name = "";
                $lesson_title = AramiscLesson::lessonName($row->class_id, $row->section_id, $row->subject_id);
                foreach($lesson_title as $key=> $data){
                        $lesson_name.=  $data->lesson_title;
                        if($lesson_title->last() != $data){
                            $lesson_name.= ',';
                        }
                }
                return $lesson_name;
            })
            ->addColumn('action', function ($row) {
                if(moduleStatusCheck('University')){
                    $btn = '<div class="dropdown CRM_dropdown">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                    <div class="dropdown-menu dropdown-menu-right">' .
                    (userPermission('un-lesson-edit') === true ? '<a class="dropdown-item" href="' . route('lesson-edit', [$row->un_session_id, $row->un_faculty_id ?? 0, $row->un_department_id, $row->un_academic_id, $row->un_semester_id, $row->un_semester_label_id, $row->un_subject_id ?? 0]) . '">' . app('translator')->get('common.edit') . '</a>' : '').
                    (userPermission('lesson-delete') === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                        '<a onclick="deleteLesson(' . $row->id . ');"  class="dropdown-item" href="#" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .
                    '</div>
                </div>';
                }else{
                    $btn = '<div class="dropdown CRM_dropdown">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                    <div class="dropdown-menu dropdown-menu-right">' .
                    (userPermission('lesson-edit') === true ? '<a class="dropdown-item" href="' . route('lesson-edit', [$row->class_id, $row->section_id, $row->subject_id]) . '">' . app('translator')->get('common.edit') . '</a>' : ''). 
                    (userPermission('lesson-delete') === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                    '<a onclick="deleteLesson(' . $row->id . ');"  class="dropdown-item" href="#" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .
                    '</div>
                </div>';
                }
                return $btn;
            })
            ->rawColumns(['action', 'lesson_name'])
            ->make(true);
        }
    }
}
