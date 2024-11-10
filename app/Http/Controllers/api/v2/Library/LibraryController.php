<?php

namespace App\Http\Controllers\api\v2\Library;

use App\AramiscBook;
use App\AramiscStudent;
use App\AramiscBookIssue;
use App\AramiscLibraryMember;
use App\Scopes\SchoolScope;
use Illuminate\Http\Request;
use App\Scopes\AcademicSchoolScope;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Scopes\ActiveStatusSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\Http\Resources\v2\StudentBookListResource;
use App\Http\Resources\v2\StudentIssuedBookListResource;

class LibraryController extends Controller
{
    public function studentBookList(Request $request)
    {
        $all_book = AramiscBook::withoutGlobalScope(ActiveStatusSchoolScope::class)
            ->with(['bookCategory' => function ($q) {
                $q->withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }, 'bookSubject' => function ($q) {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }])
            ->where('school_id', auth()->user()->school_id)
            ->where('active_status', 1)
            ->when($request->book_title, function ($query) use ($request) {
                $query->where('book_title', 'like', "%$request->book_title%");
            })
            ->latest('id')
            ->get();

        $data = StudentBookListResource::collection($all_book);

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
                'message' => 'Library book list'
            ];
        }
        return response()->json($response);
    }

    public function studentBookIssue(Request $request)
    {
        $student_detail = AramiscStudent::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', auth()->user()->school_id)
            ->where('id', $request->student_id)
            ->firstOrFail();

        $library_member = AramiscLibraryMember::withoutGlobalScopes([StatusAcademicSchoolScope::class])
            ->where('member_type', 2)
            ->where('student_staff_id', $student_detail->user_id)
            ->where('school_id', auth()->user()->school_id)->first();
        if (empty($library_member)) {
            $response = [
                'success' => false,
                'data'    => null,
                'message' => 'You are not library member ! Please contact with librarian',
            ];
            return response()->json($response, 200);
        }

        $issueBooks = AramiscBookIssue::withoutGlobalScopes([StatusAcademicSchoolScope::class])
            ->where('member_id', $library_member->student_staff_id)
            ->leftjoin('aramisc_books', 'aramisc_books.id', 'aramisc_book_issues.book_id')
            ->leftjoin('library_subjects', 'library_subjects.id', 'aramisc_books.book_subject_id')
            ->where('aramisc_book_issues.issue_status', 'I')
            ->where('aramisc_book_issues.school_id', auth()->user()->school_id)
            ->get();

        $data = StudentIssuedBookListResource::collection($issueBooks);

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
                'message' => 'Issued book list'
            ];
        }
        return response()->json($response);
    }
}
