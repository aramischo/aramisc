<?php

namespace Modules\Lesson\Http\Controllers\api;

use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscLesson;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscWeekend;
use Carbon\Carbon;
use App\AramiscClassTime;
use App\ApiBaseMethod;
use App\AramiscLessonTopic;
use App\AramiscLessonDetails;
use Carbon\CarbonPeriod;
use App\AramiscGeneralSettings;
use App\Scopes\SchoolScope;
use App\AramiscLessonTopicDetail;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscClassRoutineUpdate;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\Lesson\Entities\LessonPlanner;
use Illuminate\Contracts\Support\Renderable;

class StudentLessonApiController extends Controller
{
    public function index(Request $request, $user_id, $record_id)
    {
        try {
            $student_detail = AramiscStudent::withOutGlobalScope(SchoolScope::class)->where('user_id', $user_id)->first(['id','school_id']);
            $gs = AramiscGeneralSettings::where('school_id',$student_detail->school_id)->first('week_start_id');
             $this_week = $weekNumber = date("W");
            $week_end = AramiscWeekend::withOutGlobalScope(SchoolScope::class)->where('id',$gs->week_start_id)->value('name');
            $start_day = WEEK_DAYS_BY_NAME[$week_end ?? 'Saturday'];
            $end_day = $start_day == 0 ? 6 : $start_day - 1;
            $period = CarbonPeriod::create(Carbon::now()->startOfWeek($start_day)->format('Y-m-d'), Carbon::now()->endOfWeek($end_day)->format('Y-m-d'));
            $dates = [];
            foreach ($period as $date) {
                $dates[] = $date->format('Y-m-d');
            }

            
            $weeks = AramiscWeekend::withOutGlobalScope(SchoolScope::class)->orderBy('order', 'ASC')->where('active_status', 1)->where('school_id',  $student_detail->school_id)->get()
                ->map(function ($value, $index) use ($period) {
                    $dates = [];
                    foreach ($period as $date) {
                        $dates[] = $date->format('Y-m-d');
                    }

                    return [
                        'id' => $value->id,
                        'name' => $value->name,
                        'isWeekend' => $value->is_weekend,
                        'date' => $dates[$index],
                    ];
                });

            return response()->json(compact('this_week', 'weeks'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function getLessonByDate(Request $request, $user_id, $record_id, $date, $day_id)
    {
        try {

            $student_id = AramiscStudent::withOutGlobalScope(SchoolScope::class)->where('user_id', $user_id)->value('id');
            //return $student_detail;

            $aramisc_weekends = AramiscWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $record = StudentRecord::find($record_id);
            $class_id = $record->class_id;
            $section_id = $record->section_id;

            $routine = AramiscClassRoutineUpdate::where('day', $day_id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)->get()
                ->map(function ($value) use ($date) {
                    $lp = LessonPlanner::with('topics.topicName', 'lessonName')
                        ->where('lesson_date', $date)
                        ->where('class_id', $value->class_id)
                        ->where('section_id', $value->section_id)
                        ->where('subject_id', $value->subject_id)
                        ->where('routine_id', $value->id)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)->first();
                    return [
                        'day' => $value->weekend ? $value->weekend->name : '',
                        'room' => $value->classRoom ? $value->classRoom->room_no : '',
                        'subject' => $value->subject ? $value->subject->subject_name : '',
                        'teacher' => $value->teacherDetail ? $value->teacherDetail->full_name : '',
                        'start_time' => date('h:i A', strtotime($value->start_time)),
                        'end_time' => date('h:i A', strtotime($value->end_time)),
                        'break' => $value->is_break ? 'Yes' : 'No',
                        'plan' => $lp,
                        'subTopicEnabled' => generalSetting()->sub_topic_enable ? true : false,
                    ];
                });

            return response()->json($routine);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function previousWeek(Request $request, $user_id, $record_id, $start_day)
    {

        try {
            $end_date = Carbon::parse($start_day)->subDays(1);

            $start_date = Carbon::parse($end_date)->subDays(6);

            $this_week = $week_number = $end_date->weekOfYear;

            $period = CarbonPeriod::create($start_date, $end_date);

            //return $student_detail;
            $weeks = AramiscWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get()

                ->map(function ($value, $index) use ($period) {
                    $dates = [];
                    foreach ($period as $date) {
                        $dates[] = $date->format('Y-m-d');
                    }

                    return [
                        'id' => $value->id,
                        'name' => $value->name,
                        'isWeekend' => $value->is_weekend,
                        'date' => $dates[$index],
                    ];
                });

            return response()->json(compact('this_week', 'weeks'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function nextWeek(Request $request, $user_id, $record_id, $end_day)
    {

        try {
            $start_date = Carbon::parse($end_day)->addDay(1);
            $date = Carbon::parse($end_day)->addDay(1);


            $end_date = Carbon::parse($start_date)->addDay(7);
            $this_week = $week_number = $end_date->weekOfYear;

            $period = CarbonPeriod::create($start_date, $end_date);

            $weeks = AramiscWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get()

                ->map(function ($value, $index) use ($period) {
                    $dates = [];
                    foreach ($period as $date) {
                        $dates[] = $date->format('Y-m-d');
                    }

                    return [
                        'id' => $value->id,
                        'name' => $value->name,
                        'isWeekend' => $value->is_weekend,
                        'date' => $dates[$index],
                    ];
                });

            return response()->json(compact('this_week', 'weeks'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
