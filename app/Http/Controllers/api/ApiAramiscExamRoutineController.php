<?php

namespace App\Http\Controllers\Api;

use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscExamType;
use App\AramiscClassRoom;
use App\AramiscExamSetup;
use App\ApiBaseMethod;
use App\AramiscAcademicYear;
use App\AramiscExamSchedule;
use App\AramiscAssignSubject;
use App\Scopes\SchoolScope;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use App\Scopes\GlobalAcademicScope;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\AramiscExam;

class ApiAramiscExamRoutineController extends Controller
{
    public function examRoutine()
    {
        try {
            $school_id = auth()->user()->school_id;
            $exam_types = AramiscExamType::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->get();
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', $school_id)->first();
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
            return response()->json(compact('classes', 'exam_types'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function examScheduleSearch(Request $request)
    {
        // return $request->all();
        $request->validate([
            'exam_type' => 'required',
            'class' => 'required',
            'section' => 'sometimes|nullable',
        ]);

        try {
            $school_id = auth()->user()->school_id;
            $subject_ids = AramiscExamSetup::query();
            $assign_subjects = AramiscAssignSubject::query();

            if ($request->class != null) {
                $assign_subjects->where('class_id', $request->class);
                $subject_ids->where('class_id', $request->class);
            }

            if ($request->section != null) {
                $assign_subjects->where('section_id', $request->section);
                $subject_ids->where('section_id', $request->section);
            }

            $assign_subjects = $assign_subjects->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->get();
            $subject_ids = $subject_ids->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->pluck('subject_id')->toArray();

            if ($assign_subjects->count() == 0) {
                return response()->json(['message' => 'No Subject Assigned. Please assign subjects in this class']);
            }

            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', $school_id)->first();
                $classes = $teacher_info->classes;
            } else {
                $classes = AramiscClass::get();
            }

            $class_id = $request->class;
            $section_id = $request->section != null ? $request->section : 0;
            $exam_type_id = $request->exam_type;
            $exam_types = AramiscExamType::where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->get();

            $exam_schedule = AramiscExamSchedule::query();
            if ($request->class) {
                $exam_schedule->where('class_id', $request->class);
            }
            if ($request->section) {
                $exam_schedule->where('section_id', $request->section);
            }
            $exam_schedule = $exam_schedule->where('exam_term_id', $request->exam_type)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->get();

            $subjects = AramiscSubject::whereIn('id', $subject_ids)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->get(['id', 'subject_name']);

            $teachers = AramiscStaff::where('role_id', 4)
                ->where('active_status', 1)
                ->where('school_id', $school_id)
                ->get(['id', 'user_id', 'full_name']);

            $rooms = AramiscClassRoom::where('active_status', 1)
                ->where('school_id', $school_id)
                ->get(['id', 'room_no']);

            $examName = AramiscExamType::where('id', $request->exam_type)->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)->first()->title;

            $search_current_class = AramiscClass::find($request->class);
            $search_current_section = AramiscSection::find($request->section);

            return response()->json(compact('classes', 'subjects', 'exam_schedule', 'class_id', 'section_id', 'exam_type_id', 'exam_types', 'teachers', 'rooms', 'examName', 'search_current_class', 'search_current_section'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    // add exam routine
    // {

    //     "class_id": "1",
    //     "section_id": "0",
    //     "exam_type_id": "1",
    //     "routine": {
    //                 "1": {
    //                 "subject": "1",
    //                 "section": "1",
    //                 "teacher_id": "4",
    //                 "date": "11/18/2021",
    //                 "start_time": "5:08 PM",
    //                 "end_time": "6:08 PM",
    //                 "room": "1"
    //                 }
    //             }
    // }
    public function addExamRoutineStore(Request $request)
    {
        // return   $request->all();
        $input = $request->all();
        $validator = Validator::make($input, [
            // 'subject' => 'required',
            'class_id' => 'required',
            'section_id' => 'required',
            // 'room' => 'required',
            // 'date' => 'required',
            // 'start_time' => 'required',
            // 'end_time' => 'required',
            'exam_type_id' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $class_id = $request->class_id;
            $section_id = $request->section_id == 0 ? 0 : $request->section_id;
            $exam_term_id = $request->exam_type_id;
            $school_id = auth()->user()->school_id;
            $exam_schedule = AramiscExamSchedule::query();
            if ($request->class_id) {
                $exam_schedule->where('class_id', $request->class_id);
            }
            if ($request->section_id != 0) {
                $exam_schedule->where('section_id', $request->section);
            }
            $exam_schedule = $exam_schedule->where('exam_term_id', $request->exam_type_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->delete();

            foreach ($request->routine as $routine_data) {
                if (gv($routine_data, 'subject') == "Select Subject *") {
                    Toastr::error('Subject Can not Be Empty', 'Failed');
                    return redirect('exam-routine-view/' . $class_id . '/' . $section_id . '/' . $exam_term_id);
                }
                if (!gv($routine_data, 'subject') || gv($routine_data, 'subject') == "Select Subject *" || !gv($routine_data, 'start_time') || !gv($routine_data, 'end_time')) {
                    continue;
                }
                $is_exist = AramiscExamSchedule::where(
                    [
                        'exam_term_id' => $request->exam_type_id,
                        'subject_id' => gv($routine_data, 'subject'),
                        'date' => date('Y-m-d', strtotime(gv($routine_data, 'date'))),
                        'start_time' => date('H:i:s', strtotime(gv($routine_data, 'start_time'))),
                        'end_time' => date('H:i:s', strtotime(gv($routine_data, 'end_time'))),
                        'room_id' => gv($routine_data, 'room'),
                        'class_id' => $request->class_id,
                        'section_id' => gv($routine_data, 'section'),
                    ]
                )->where('school_id', $school_id)->first();

                if ($is_exist) {
                    continue;
                }

                $exam_routine = new AramiscExamSchedule();
                $exam_routine->exam_term_id = $request->exam_type_id;
                $exam_routine->class_id = $request->class_id;
                $exam_routine->section_id = gv($routine_data, 'section');
                $exam_routine->subject_id = gv($routine_data, 'subject');
                $exam_routine->teacher_id = gv($routine_data, 'teacher_id');
                $exam_routine->date = date('Y-m-d', strtotime(gv($routine_data, 'date')));
                $exam_routine->start_time = date('H:i:s', strtotime(gv($routine_data, 'start_time')));
                $exam_routine->end_time = date('H:i:s', strtotime(gv($routine_data, 'end_time')));
                $exam_routine->room_id = gv($routine_data, 'room');
                $exam_routine->school_id = $school_id;
                $exam_routine->academic_id = getAcademicId();
                $exam_routine->save();
            }

            return response()->json(['success' => 'Exam routine has been Created successfully']);

            // return redirect('exam-routine-view/' . $class_id . '/' . $section_id . '/' . $exam_term_id);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentRoutine($user_id)
    {
        try {
            $student_detail = AramiscStudent::withoutGlobalScope(SchoolScope::class)->with('studentRecords')->select('id', 'full_name', 'user_id','school_id')
                ->where('user_id', $user_id)
                ->first();

            $records = $student_detail->studentRecords;
            
            $class_ids = $records->pluck('class_id') ? $records->pluck('class_id') : [] ;
            $section_ids = $records->pluck('section_id') ? $records->pluck('section_id') : [];
            
             $exam_type_ids = AramiscExam::withoutGlobalScope(StatusAcademicSchoolScope::class, GlobalAcademicScope::class)->where('school_id', $student_detail->school_id)
                ->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($student_detail->school_id))
                ->whereIn('class_id', $class_ids)->whereIn('section_id',$section_ids)
                ->where('active_status', 1)->pluck('exam_type_id');

            $exam_types = AramiscExamType::whereIn('id', $exam_type_ids)->get(['id', 'title']);
            return response()->json(compact('exam_types', 'student_detail'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function studentExamRoutineSearch(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'exam' => 'required',
                'student_id'=>'required',
            ]);

            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
            }

            $student_detail = AramiscStudent::select('id', 'full_name')
                ->where('user_id', $request->student_id)
                ->first();
            $record = StudentRecord::where('student_id', $request->student_id)->first();
            $class_id = $record->class_id;
            $section_id = $record->section_id;
            $school_id = $record->school_id;
            $academic_id = $record->academic_id;
            $routines = AramiscExamSchedule::where('exam_term_id', $request->exam)
                ->where('class_id', $class_id)->where('section_id', $section_id)
                ->where('school_id', $school_id)->where('academic_id', $academic_id)
                ->get();

            $exam_routines =[];
            foreach ($routines as $routine) {
                $exam_routines[] = [
                    'id' => $routine->id,
                    'class' => $routine->class ? $routine->class->class_name :'',
                    'section' => $routine->section ? $routine->section->section_name :'',
                    'room' => $routine->classRoom ? $routine->classRoom->room_no :'',
                    'subject' => $routine->subject ? $routine->subject->subject_name :'',
                    'teacher' => $routine->teacher ? $routine->teacher->full_name :'',
                    'start_time'=> date('h:i A', strtotime($routine->start_time)),
                    'end_time'=> date('h:i A', strtotime($routine->end_time)),
                ];
            }

            return response()->json(compact('exam_routines'));
        } catch (\Throwable $th) {
            return ApiBaseMethod::sendError('Error.', $th->getMessage());

        }
    }
    
    public function examRoutineReportSearch(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'exam' => 'required',
                'student_id' => 'required'
            ]);

            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
            }

            $student_detail = AramiscStudent::with('studentRecords')->select('id', 'full_name', 'user_id')
                ->where('id', $request->student_id)
                ->first();


            $examType = AramiscExamType::select('id', 'title')->find($request->exam);
            // $routines = AramiscExamSchedule::where('exam_term_id', $request->exam)->get();
            $exa_routines = AramiscExamSchedule::when($student_detail, function ($q) use($student_detail){
                $records = $student_detail->studentRecords;
                $q->whereIn('class_id', $records->pluck('class_id'))
                    ->whereIn('section_id', $records->pluck('section_id'));
            })
                ->where('exam_term_id', $request->exam)
                ->orderBy('date', 'ASC')->get();
            $exa_routines = $exa_routines->groupBy('date');
            $exam_term_id  = $request->exam;
            $exam_routines =[];

            foreach ($exa_routines as $date => $routines) {
                foreach($routines as $routine){
                    $exam_routines[$date][] = [
                        'id' => $routine->id,
                        'date' => $date,
                        'class' => $routine->class ? $routine->class->class_name :'',
                        'section' => $routine->section ? $routine->section->section_name :'',
                        'room' => $routine->classRoom ? $routine->classRoom->room_no :'',
                        'subject' => $routine->subject ? $routine->subject->subject_name :'',
                        'teacher' => $routine->teacher ? $routine->teacher->full_name :'',
                        'exam_type'=> $examType->title,
                        'start_time'=> date('h:i A', strtotime($routine->start_time)),
                        'end_time'=> date('h:i A', strtotime($routine->end_time)),
                    ];
                }
            }

            return response()->json(compact('examType', 'exam_routines'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
}
