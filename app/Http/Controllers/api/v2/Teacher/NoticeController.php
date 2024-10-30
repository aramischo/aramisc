<?php

namespace App\Http\Controllers\api\v2\Teacher;

use App\AramiscNoticeBoard;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Scopes\StatusAcademicSchoolScope;


class NoticeController extends Controller
{
    public function noticeList()
    {
        $data = AramiscNoticeBoard::withoutGlobalScope(StatusAcademicSchoolScope::class)
            ->where('school_id', auth()->user()->school_id)
            ->select('id', 'notice_title', 'notice_message', 'notice_date')->get();
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
                'message' => 'Notice list'
            ];
        }
        return response()->json($response);
    }
}
