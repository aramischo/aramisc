<?php

namespace App\Http\Controllers\api\v2\Dormitory;

use App\AramiscStudent;
use App\AramiscRoomList;
use App\AramiscRoomType;
use App\AramiscDormitoryList;
use App\Scopes\SchoolScope;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Scopes\ActiveStatusSchoolScope;
use App\Http\Resources\v2\StudentDormitoryResource;

class DormitoryController extends Controller
{
    public function studentDormitory(Request $request)
    {
        $student_detail = AramiscStudent::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($request->student_id);

        $room_lists = AramiscRoomList::withoutGlobalScope(ActiveStatusSchoolScope::class)
            ->with('dormitory')
            ->where('active_status', 1)
            ->where('id', $student_detail->room_id)
            ->where('school_id', auth()->user()->school_id)
            ->groupBy('dormitory_id')
            ->get();

        $data = StudentDormitoryResource::collection($room_lists);

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
                'message' => 'Dormitory list'
            ];
        }
        return response()->json($response);
    }
}
