<?php

namespace App\Http\Controllers\api\v2\Timeline;

use App\Http\Controllers\Controller;
use App\AramiscStudentTimeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\AramiscAcademicYear;

class TimelineController extends Controller
{
    public function stdTimeline(Request $request)
    {
        $data['timelines'] = AramiscStudentTimeline::select('id', 'date', 'title', 'description', 'file', 'created_at')
            ->where('staff_student_id', $request->student_timeline_id)
            ->where('type', 'stu')
            ->where('academic_id', AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
            ->where('school_id', auth()->user()->school_id)
            ->get();
        if (!$data) {
            $response = [
                'success' => false,
                'data'    => null,
                'message' => 'Operation failed'
            ];
        } else {
            $response = [
                'success' => true,
                'data'    => $data,
                'message' => 'Timeline list'
            ];
        }

        return response()->json($response);
    }
}
