<?php

namespace App\Http\Controllers\Theme\Edulia;

use App\AramiscExam;
use App\AramiscNews;
use App\SmClass;
use App\AramiscEvent;
use App\SmStaff;
use App\AramiscCourse;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscVisitor;
use App\YearCheck;
use App\AramiscExamType;
use App\AramiscNewsPage;
use App\SmMarksGrade;
use App\AramiscExamSetting;
use App\SmNoticeBoard;
use App\SmResultStore;
use App\Models\SmDonor;
use App\AramiscNewsCategory;
use App\SmAssignSubject;
use App\SmMarksRegister;
use App\AramiscCourseCategory;
use App\Models\SpeechSlider;
use Illuminate\Http\Request;
use App\Models\AramiscCustomField;
use App\Models\AramiscNewsComment;
use App\Models\StudentRecord;
use App\Models\SmPhotoGallery;
use App\SmClassOptionalSubject;
use App\SmOptionalSubjectAssign;
use App\Models\FrontendExamResult;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\AdminSection\AramiscVisitorRequest;
use App\Http\Requests\Admin\FrontSettings\ExamResultSearch;
use Modules\RolePermission\Entities\Permission;

class FrontendController extends Controller
{
    public function singleCourseDetails($course_id)
    {
        try {
            $data['course'] = AramiscCourse::where('school_id', app('school')->id)->find($course_id);
            return view('frontEnd.theme.' . activeTheme() . '.course.single_course_details_page', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function singleNewsDetails($news_id)
    {
        try {
            $data['news'] = AramiscNews::with(['newsComments.onlyChildrenFrontend'])->where('school_id', app('school')->id)->findOrFail($news_id);
            return view('frontEnd.theme.' . activeTheme() . '.news.single_news_details_page', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeNewsComment(Request $request)
    {
        try {
            $newsDeyails = AramiscNews::find($request->news_id);
            $store = new AramiscNewsComment();
            $store->message = $request->message;
            $store->news_id = $request->news_id;
            $store->user_id = $request->user_id;
            $store->parent_id = $request->parent_id ?? NULL;
            if ($newsDeyails->is_global == 1 && generalSetting()->auto_approve == 1) {
                $store->status = 1;
            } elseif ($newsDeyails->is_global == 0 && $newsDeyails->auto_approve == 1) {
                $store->status = 1;
            } else {
                $store->status = 0;
            }
            $store->save();
            if (request()->ajax()) {
                return response()->json(['success' => true]);
            } else {
                Toastr::success('Comment Store Successfully', 'Success');
                return redirect()->route('frontend.news-details', $request->news_id);
            }
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => $e]);
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    }

    public function singleGalleryDetails($gallery_id)
    {
        try {
            $data['gallery_feature'] = SmPhotoGallery::where('school_id', app('school')->id)->where('parent_id', '=', null)->findOrFail($gallery_id);
            $data['galleries'] = SmPhotoGallery::where('school_id', app('school')->id)->where('parent_id', '!=', null)->where('parent_id', $gallery_id)->get();
            return view('frontEnd.theme.' . activeTheme() . '.photoGallery.single_photo_gallery', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function singleNoticeDetails($notice_id)
    {
        try {
            $data['notice_detail'] = SmNoticeBoard::where('is_published', 1)->where('school_id', app('school')->id)->findOrFail($notice_id);
            $data['notices'] = SmNoticeBoard::where('is_published', 1)->where('school_id', app('school')->id)->get();
            return view('frontEnd.theme.' . activeTheme() . '.notice.single_notice', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function indiviualResult(ExamResultSearch $request)
    {
        try {
            $exam_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $page = FrontendExamResult::where('school_id', app('school')->id)->first();
            $school_id = app('school')->id;
            $student = AramiscStudent::where('admission_no', $request->admission_number)->where('school_id', $school_id)->with('parents', 'group')->first();
            if ($student) {
                $exam_content = AramiscExamSetting::where('exam_type', $request->exam)
                    ->where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', app('school')->id)
                    ->first();

                $student_detail = $studentDetails = StudentRecord::where('student_id', $student->id)
                    ->where('academic_id', getAcademicId())
                    ->where('is_promote', 0)
                    ->where('school_id', $school_id)
                    ->first();

                $section_id = $student_detail->section_id;
                $class_id = $student_detail->class_id;
                $exam_type_id = $request->exam;
                $student_id = $student->id;
                $exam_id = $request->exam;

                $failgpa = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->min('gpa');

                $failgpaname = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->where('gpa', $failgpa)
                    ->first();

                $exams = AramiscExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $classes = SmClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $examSubjects = AramiscExam::where([['exam_type_id',  $exam_type_id], ['section_id', $section_id], ['class_id', $class_id]])
                    ->where('school_id', $school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();
                $examSubjectIds = [];
                foreach ($examSubjects as $examSubject) {
                    $examSubjectIds[] = $examSubject->subject_id;
                }

                $subjects = $studentDetails->class->subjects->where('section_id', $section_id)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id);
                $subjects = $examSubjects;

                $exam_details = $exams->where('active_status', 1)->find($exam_type_id);

                $optional_subject = '';
                $get_optional_subject = SmOptionalSubjectAssign::where('record_id', '=', $student_detail->id)
                    ->where('session_id', '=', $student_detail->session_id)
                    ->first();

                if ($get_optional_subject != '') {
                    $optional_subject = $get_optional_subject->subject_id;
                }

                $optional_subject_setup = SmClassOptionalSubject::where('class_id', '=', $class_id)
                    ->first();

                $mark_sheet = SmResultStore::where([['class_id', $class_id], ['exam_type_id', $request->exam], ['section_id', $section_id], ['student_id', $student_id]])
                    ->whereIn('subject_id', $subjects->pluck('subject_id')->toArray())
                    ->where('school_id', $school_id)
                    ->get();

                $grades = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->orderBy('gpa', 'desc')
                    ->get();

                $maxGrade = SmMarksGrade::where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->max('gpa');

                if (count($mark_sheet) == 0) {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                    return redirect()->back();
                }

                $is_result_available = SmResultStore::where([['class_id', $class_id], ['exam_type_id', $request->exam], ['section_id', $section_id], ['student_id', $student_id]])
                    ->where('created_at', 'LIKE', '%' . YearCheck::getYear() . '%')
                    ->where('school_id', $school_id)
                    ->get();

                $marks_register = SmMarksRegister::where('exam_id', $request->exam)
                    ->where('student_id', $student_id)
                    ->first();

                $subjects = SmAssignSubject::where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $exams = AramiscExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $classes = SmClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $grades = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $class = SmClass::find($class_id);
                $section = AramiscSection::find($section_id);
                $exam_detail = AramiscExam::find($request->exam);

                return view('frontEnd.theme.' . activeTheme() . '.indivisualResult.indivisual_result', compact(
                    'student',
                    'optional_subject',
                    'classes',
                    'studentDetails',
                    'exams',
                    'classes',
                    'marks_register',
                    'subjects',
                    'class',
                    'section',
                    'exam_detail',
                    'exam_content',
                    'grades',
                    'student_detail',
                    'mark_sheet',
                    'exam_details',
                    'maxGrade',
                    'failgpaname',
                    'exam_id',
                    'exam_type_id',
                    'class_id',
                    'section_id',
                    'student_id',
                    'optional_subject_setup',
                    'exam_types',
                    'page'
                ));
            } else {
                Toastr::error('Student Not Found', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function allBlogList()
    {
        try {
            $data['news'] = AramiscNews::where('school_id', app('school')->id)->paginate(8);
            $data['newsPage'] = AramiscNewsPage::where('school_id', app('school')->id)->first();
            return view('frontEnd.theme.' . activeTheme() . '.news.all_news_list', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function loadMoreBlogs(Request $request)
    {
        try {
            $data['count'] = AramiscNews::count();
            $data['skip'] = $request->skip;
            $data['limit'] = $data['count'] - $data['skip'];
            $data['news'] = AramiscNews::skip($data['skip'])->where('school_id', app('school')->id)->take(4)->get();
            return view('frontEnd.theme.' . activeTheme() . '.news.load_more_news', $data);
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function singleEventDetails($id)
    {
        try {
            $data['event'] = AramiscEvent::with('user')->find($id);
            return view('frontEnd.theme.' . activeTheme() . '.single_event', $data);
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function blogList()
    {
        try {
            $data['blogs'] = AramiscNews::with('category')->where('school_id', app('school')->id);
            return view('frontEnd.theme.' . activeTheme() . '.blog_list', $data);
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function loadMoreBlogList(Request $request)
    {
        try {
            $data['count'] = AramiscNews::count();
            $data['skip'] = $request->skip;
            $data['limit'] = $data['count'] - $data['skip'];
            $data['blogs'] = AramiscNews::skip($data['skip'])->where('school_id', app('school')->id)->take(5)->get();
            $html = view('frontEnd.theme.' . activeTheme() . '.read_more_blog_list', $data)->render();
            return response()->json(['success' => true, 'html' => $html, 'total_data' => $data['count']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    public function singleSpeechSlider($id)
    {
        try {
            $data['singleSpeechSlider'] = SpeechSlider::where('school_id', app('school')->id)->findOrFail($id);
            return view('frontEnd.theme.' . activeTheme() . '.speechSlider.single_speech_slider', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function courseList()
    {
        try {
            $data['courseCategories'] = AramiscCourseCategory::where('school_id', app('school')->id)->with('courses')->get();
            return view('frontEnd.theme.' . activeTheme() . '.courseList.course_list', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function singleCourseDetail($id)
    {
        try {
            $data['singleCourseDetail'] = AramiscCourse::where('id', $id)->where('school_id', app('school')->id)->with('courseCategory')->first();
            return view('frontEnd.theme.' . activeTheme() . '.courseList.single_course_details', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function frontendSingleStudentDetails($id)
    {
        try {
            $data['singleStudent'] = AramiscStudent::where('id', $id)->where('school_id', app('school')->id)->with('parents', 'gender', 'religion', 'bloodGroup', 'studentRecord.class', 'studentRecord.section')->first();
            return view('frontEnd.theme.' . activeTheme() . '.frontend_single_student_details', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function archiveList()
    {
        try {
            $data['archives'] = AramiscNews::with('category')->where('school_id', app('school')->id);
            $data['archiveYears'] = $data['archives']->get()->groupBy(function ($q) {
                return $q->created_at->format('Y');
            });
            $data['archiveCategories'] = AramiscNewsCategory::where('school_id', app('school')->id)->get();
            return view('frontEnd.theme.' . activeTheme() . '.archive.archive_list', $data);
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function loadMoreArchiveList(Request $request)
    {
        try {
            $years = $request->year;
            $data['count'] = AramiscNews::count();
            $data['skip'] = $request->skip;
            $data['limit'] = $data['count'] - $data['skip'];
            $data['archives'] = AramiscNews::when($request->year, function ($q) use ($years) {
                $q->where(function ($query) use ($years) {
                    foreach ($years as $year) {
                        $query->whereYear('created_at', '=', $year, 'or');
                    }
                });
            })->skip($data['skip'])->where('school_id', app('school')->id)->take(5)->get();
            $html = view('frontEnd.theme.' . activeTheme() . '.archive.read_more_archive_list', $data)->render();
            return response()->json(['success' => true, 'html' => $html, 'total_data' => $data['count']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e]);
        }
    }
    public function archiveYearFilter(Request $request)
    {
        try {
            $years = $request->year;
            $data['archives'] = AramiscNews::with('category')
                    ->where('school_id', app('school')->id)
                    ->when($request->data_count > 0 , function($q) use($years){
                        $q->where(function ($q) use ($years) {
                            foreach ($years as $year) {
                                $q->whereYear('created_at', '=', $year, 'or');
                            }
                        });
                    })->paginate(5);
            $html = view('frontEnd.theme.' . activeTheme() . '.archive.archive_year_filter', $data)->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    public function bookAVisit()
    {
        try {
            return view('frontEnd.theme.' . activeTheme() . '.visit_a_book');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function bookAVisitStore(AramiscVisitorRequest $request)
    {
        try {
            $destination = 'public/uploads/visitor/';
            $fileName = fileUpload($request->upload_event_image, $destination);
            $visitor = new AramiscVisitor();
            $visitor->name = $request->name;
            $visitor->phone = $request->phone;
            $visitor->visitor_id = $request->visitor_id;
            $visitor->no_of_person = $request->no_of_person;
            $visitor->purpose = $request->purpose;
            $visitor->date = date('Y-m-d', strtotime($request->date));
            $visitor->in_time = $request->in_time;
            $visitor->out_time = $request->out_time;
            $visitor->file = $fileName;
            $visitor->created_by = null;
            $visitor->school_id = app('school')->id;
            $visitor->academic_id = getAcademicId();
            $visitor->save();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function donorDetails($id)
    {
        try {
            $data['donorDetails'] = SmDonor::where('id', $id)->where('school_id', app('school')->id)->where('show_public', 1)->first();
            $data['custom_filed_values'] = json_decode($data['donorDetails']->custom_field);
            return view('frontEnd.theme.' . activeTheme() . '.donor.donor_details', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function staffDetails($id)
    {
        try {
            $data['staffDetails'] = SmStaff::where('id', $id)->where('school_id', app('school')->id)->first();
            return view('frontEnd.theme.' . activeTheme() . '.staff.staff_details', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
