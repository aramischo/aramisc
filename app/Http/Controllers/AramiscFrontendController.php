<?php

namespace App\Http\Controllers;

use App\User;
use App\AramiscExam;
use App\AramiscNews;
use App\AramiscPage;
use App\AramiscClass;
use App\AramiscEvent;
use App\AramiscStaff;
use App\AramiscCourse;
use App\AramiscSchool;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscWeekend;
use App\YearCheck;
use App\AramiscExamType;
use App\AramiscNewsPage;
use App\AramiscAboutPage;
use App\AramiscCoursePage;
use App\AramiscMarksGrade;
use App\ApiBaseMethod;
use App\AramiscContactPage;
use App\AramiscNoticeBoard;
use App\AramiscResultStore;
use App\AramiscTestimonial;
use App\AramiscExamSchedule;
use App\AramiscNewsCategory;
use App\AramiscAssignSubject;
use App\AramiscMarksRegister;
use App\AramiscContactMessage;
use App\AramiscCourseCategory;
use App\AramiscGeneralSettings;
use App\AramiscHomePageSetting;
use App\AramiscSocialMediaIcon;
use App\AramiscBackgroundSetting;
use App\AramiscHeaderMenuManager;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscClassRoutineUpdate;
use App\AramiscFrontendPersmission;
use App\AramiscClassOptionalSubject;
use App\AramiscOptionalSubjectAssign;
use App\Models\FrontendExamResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AramiscClassExamRoutinePage;
use Larabuild\Pagebuilder\Models\Page;
use Illuminate\Support\Facades\Redirect;
use Modules\Saas\Entities\SmPackagePlan;
use Illuminate\Support\Facades\Validator;
use Modules\RolePermission\Entities\AramiscPermissionAssign;
use App\Http\Requests\Admin\FrontSettings\ExamResultSearch;
use Larabuild\Pagebuilder\Http\Controllers\PageBuilderController;

class AramiscFrontendController extends Controller
{

    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function index()
    {
        try {
            if (activeTheme() == 'edulia') {
                $home_page = Page::where('school_id', app('school')->id)->where('home_page', 1)->first();
                if ($home_page) {
                    $page = $home_page;
                } else {
                    $page = Page::where('school_id', app('school')->id)->first();
                }
                $controller = new PageBuilderController();
                return $controller->renderPage($page ? $page->slug : '/');
            }
            $setting = AramiscGeneralSettings::where('school_id', app('school')->id)->first();
            $permisions = AramiscFrontendPersmission::where('parent_id', 1)->where('is_published', 1)->get();
            $per = [];
            foreach ($permisions as $permision) {
                $per[$permision->name] = 1;
            }

            $data = [
                'setting' => $setting,
                'per' => $per,
            ];

            $home_data = [
                'exams' => AramiscExam::where('school_id', app('school')->id)->get(),
                'news' => AramiscNews::where('school_id', app('school')->id)->orderBy('order', 'asc')->limit(3)->get(),
                'testimonial' => AramiscTestimonial::where('school_id', app('school')->id)->get(),
                'academics' => AramiscCourse::where('school_id', app('school')->id)->orderBy('id', 'asc')->limit(3)->get(),
                'exam_types' => AramiscExamType::where('school_id', app('school')->id)->get(),
                'events' => AramiscEvent::where('school_id', app('school')->id)->get(),
                'notice_board' => AramiscNoticeBoard::where('school_id', app('school')->id)->where('is_published', 1)->orderBy('created_at', 'DESC')->take(3)->get(),
                'classes' => AramiscClass::where('school_id', app('school')->id)->where('active_status', 1)->get(),
                'subjects' => AramiscSubject::where('school_id', app('school')->id)->where('active_status', 1)->get(),
                'section' => AramiscSection::where('school_id', app('school')->id)->where('active_status', 1)->get(),
                'homePage' => AramiscHomePageSetting::where('school_id', app('school')->id)->first(),
            ];

            $url = explode('/', $setting->website_url);

            if ($setting->website_btn == 0) {
                if (auth()->check()) {
                    return redirect('dashboard');
                }
                return redirect('login');
            } else {

                if ($setting->website_url == '') {
                    return view('frontEnd.home.light_home')->with(array_merge($data, $home_data));
                } elseif ($url[max(array_keys($url))] == 'home') {


                    return view('frontEnd.home.light_home')->with(array_merge($data, $home_data));
                } else if (rtrim($setting->website_url, '/') == url()->current()) {
                    return view('frontEnd.home.light_home')->with(array_merge($data, $home_data));
                } else {
                    $url = $setting->website_url;
                    return Redirect::to($url);
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function about()
    {
        try {
            $exams = AramiscExam::where('school_id', app('school')->id)->get();
            $exams_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $classes = AramiscClass::where('active_status', 1)->where('school_id', app('school')->id)->get();
            $subjects = AramiscSubject::where('active_status', 1)->where('school_id', app('school')->id)->get();
            $sections = AramiscSection::where('active_status', 1)->where('school_id', app('school')->id)->get();
            $about = AramiscAboutPage::where('school_id', app('school')->id)->first();
            $testimonial = AramiscTestimonial::where('school_id', app('school')->id)->get();
            $totalStudents = AramiscStudent::where('active_status', 1)->where('school_id', app('school')->id)->get();
            $totalTeachers = AramiscStaff::where('active_status', 1)
                ->where(function ($q) {
                    $q->where('role_id', 4)->orWhere('previous_role_id', 4);
                })->where('school_id', app('school')->id)->get();
            $history = AramiscNews::with('category')->histories()->limit(3)->where('school_id', app('school')->id)->get();
            $mission = AramiscNews::with('category')->missions()->limit(3)->where('school_id', app('school')->id)->get();

            return view('frontEnd.home.light_about', compact('exams', 'classes', 'subjects', 'exams_types', 'sections', 'about', 'testimonial', 'totalStudents', 'totalTeachers', 'history', 'mission'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function news()
    {
        try {
            $exams = AramiscExam::where('school_id', app('school')->id)->get();
            $exams_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $classes = AramiscClass::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $subjects = AramiscSubject::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $sections = AramiscSection::where('school_id', app('school')->id)->where('active_status', 1)->get();
            return view('frontEnd.home.light_news', compact('exams', 'classes', 'subjects', 'exams_types', 'sections'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function contact()
    {
        try {
            $exams = AramiscExam::where('school_id', app('school')->id)->get();
            $exams_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $classes = AramiscClass::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $subjects = AramiscSubject::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $sections = AramiscSection::where('school_id', app('school')->id)->where('active_status', 1)->get();

            $contact_info = AramiscContactPage::where('school_id', app('school')->id)->first();
            return view('frontEnd.home.light_contact', compact('exams', 'classes', 'subjects', 'exams_types', 'sections', 'contact_info'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function institutionPrivacyPolicy()
    {
        try {
            $exams = AramiscExam::where('school_id', app('school')->id)->get();
            $exams_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $classes = AramiscClass::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $subjects = AramiscSubject::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $sections = AramiscSection::where('school_id', app('school')->id)->where('active_status', 1)->get();

            $contact_info = AramiscContactPage::where('school_id', app('school')->id)->first();
            return view('frontEnd.home.institutionPrivacyPolicy', compact('exams', 'classes', 'subjects', 'exams_types', 'sections', 'contact_info'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function developerTool($purpose)
    {
        if ($purpose == 'debug_true') {
            envu([
                'APP_ENV' => 'local',
                'APP_DEBUG' => 'true',
            ]);
        } elseif ($purpose == 'debug_false') {
            envu([
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
            ]);
        } elseif ($purpose == "sync_true") {
            envu([
                'APP_SYNC' => 'true',
            ]);
        } elseif ($purpose == "sync_false") {
            envu([
                'APP_SYNC' => 'false',
            ]);
        }
    }

    public function institutionTermServices()
    {
        try {
            $exams = AramiscExam::where('school_id', app('school')->id)->get();
            $exams_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $classes = AramiscClass::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $subjects = AramiscSubject::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $sections = AramiscSection::where('school_id', app('school')->id)->where('active_status', 1)->get();

            $contact_info = AramiscContactPage::where('school_id', app('school')->id)->first();
            return view('frontEnd.home.institutionTermServices', compact('exams', 'classes', 'subjects', 'exams_types', 'sections', 'contact_info'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function newsDetails($id)
    {
        $news = AramiscNews::where('school_id', app('school')->id)->findOrFail($id);
        $otherNews = AramiscNews::where('school_id', app('school')->id)->orderBy('id', 'asc')->whereNotIn('id', [$id])->limit(3)->get();
        $notice_board = AramiscNoticeBoard::where('school_id', app('school')->id)->where('is_published', 1)->orderBy('created_at', 'DESC')->take(3)->get();

        return view('frontEnd.home.light_news_details', compact('news', 'notice_board', 'otherNews'));
    }

    public function newsPage()
    {
        try {
            $news = AramiscNews::where('school_id', app('school')->id)->paginate(8);
            $newsPage = AramiscNewsPage::where('school_id', app('school')->id)->first();
            return view('frontEnd.home.light_news', compact('news', 'newsPage'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function loadMorenews(Request $request)
    {
        try {
            $count = AramiscNews::count();
            $skip = $request->skip;
            $limit = $count - $skip;
            $due_news = AramiscNews::skip($skip)->where('school_id', app('school')->id)->take(4)->get();
            return view('frontEnd.home.loadMoreNews', compact('due_news', 'skip', 'count'));
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'sometimes|required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);
        try {
            $contact_message = new AramiscContactMessage();
            $contact_message->name = $request->name;
            if ($request->phone) {
                $contact_message->phone = $request->phone;
            }
            $contact_message->email = $request->email;
            $contact_message->subject = $request->subject;
            $contact_message->message = $request->message;
            $contact_message->school_id = app('school')->id;
            $contact_message->save();

            $receiver_name = "System Admin";
            $compact['contact_name'] = $request->name;
            if ($request->phone) {
                $compact['contact_phone'] = $request->phone;
            }
            $compact['contact_email'] = $request->email;
            $compact['subject'] = $request->subject;
            $compact['contact_message'] = $request->message;
            $contact_page_email = AramiscContactPage::where('school_id', app('school')->id)->first();
            $setting = AramiscGeneralSettings::where('school_id', app('school')->id)->first();
            if ($contact_page_email->email) {
                $email = $contact_page_email->email;
            } else {
                $email = $setting->email;
            }
            @send_mail($email, $receiver_name, "frontend_contact", $compact);
            return response()->json(['success' => 'success']);
        } catch (\Exception $e) {
            return response()->json('error');
        }
    }

    public function contactMessage(Request $request)
    {
        try {
            $contact_messages = AramiscContactMessage::where('school_id', app('school')->id)->orderBy('id', 'desc')->get();
            $module_links = AramiscPermissionAssign::where('role_id', Auth::user()->role_id)->where('school_id', Auth::user()->school_id)->pluck('module_id')->toArray();
            return view('frontEnd.contact_message', compact('contact_messages', 'module_links'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //user register method start
    public function register()
    {
        try {
            $login_background = AramiscBackgroundSetting::where([['is_default', 1], ['title', 'Login Background']])->first();

            if (empty($login_background)) {
                $css = "";
            } else {
                if (!empty($login_background->image)) {
                    $css = "background: url('" . url($login_background->image) . "')  no-repeat center;  background-size: cover;";
                } else {
                    $css = "background:" . $login_background->color;
                }
            }
            $schools = AramiscSchool::where('active_status', 1)->get();
            return view('auth.registerCodeCanyon', compact('schools', 'css'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function customer_register(Request $request)
    {

        $request->validate([
            'fullname' => 'required|min:3|max:100',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required_with:password|same:password|min:6',
        ]);

        try {
            //insert data into user table
            $s = new User();
            $s->role_id = 4;
            $s->full_name = $request->fullname;
            $s->username = $request->email;
            $s->email = $request->email;
            $s->active_status = 0;
            $s->access_status = 0;
            $s->password = Hash::make($request->password);
            $s->save();
            $result = $s->toArray();
            $last_id = $s->id; //last id of user table

            //insert data into staff table
            $st = new AramiscStaff();
            $st->school_id = 1;
            $st->user_id = $last_id;
            $st->role_id = 4;
            $st->first_name = $request->fullname;
            $st->full_name = $request->fullname;
            $st->last_name = '';
            $st->staff_no = 10;
            $st->email = $request->email;
            $st->active_status = 0;
            $st->save();

            $result = $st->toArray();
            if (!empty($result)) {
                Toastr::success('Operation successful', 'Success');
                return redirect('login');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            Toastr::error('Operation Failed,' . $e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }

    public function course()
    {
        try {
            $exams = AramiscExam::where('school_id', app('school')->id)->get();
            $course = AramiscCourse::where('school_id', app('school')->id)->paginate(3);
            $news = AramiscNews::where('school_id', app('school')->id)->orderBy('order', 'asc')->limit(4)->get();
            $exams_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $coursePage = AramiscCoursePage::where('school_id', app('school')->id)->first();
            $classes = AramiscClass::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $subjects = AramiscSubject::where('school_id', app('school')->id)->where('active_status', 1)->get();
            $sections = AramiscSection::where('school_id', app('school')->id)->where('active_status', 1)->get();
            return view('frontEnd.home.light_course', compact('exams', 'classes', 'coursePage', 'subjects', 'exams_types', 'sections', 'course', 'news'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function courseDetails($id)
    {
        try {
            $course = AramiscCourse::where('school_id', app('school')->id)->find($id);
            $course_details = AramiscCoursePage::where('school_id', app('school')->id)->where('is_parent', 0)->first();
            $courses = AramiscCourse::where('school_id', app('school')->id)->orderBy('id', 'asc')->whereNotIn('id', [$id])->limit(3)->get();
            return view('frontEnd.home.light_course_details', compact('course', 'courses', 'course_details'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function loadMoreCourse(Request $request)
    {
        try {
            $count = AramiscCourse::count();
            $skip = $request->skip;
            $limit = $count - $skip;
            $due_courses = AramiscCourse::skip($skip)->where('school_id', app('school')->id)->take(3)->get();
            return view('frontEnd.home.loadMorePage', compact('due_courses', 'skip', 'count'));
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function socialMedia()
    {
        $visitors = AramiscSocialMediaIcon::where('school_id', app('school')->id)->get();
        return view('frontEnd.socialMedia', compact('visitors'));
    }

    public function viewPage($slug)
    {
        try {
            $page = AramiscPage::where('slug', $slug)->where('school_id', app('school')->id)->first();
            return view('frontEnd.pages.pages', compact('page'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deletePage(Request $request)
    {
        try {
            $data = AramiscPage::find($request->id);

            if ($data->header_image != "") {
                unlink($data->header_image);
            }

            $result = AramiscPage::find($request->id)->delete();
            if ($result) {
                Toastr::success('Operation Successfull', 'Success');
            } else {
                Toastr::error('Operation Failed', 'Failed');
            }
            return redirect('page-list');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteMessage($id)
    {
        try {
            AramiscContactMessage::find($id)->delete();
            Toastr::success('Operation successful', 'Success');
            return redirect('contact-message');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examResult()
    {
        try {
            $exam_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $page = FrontendExamResult::where('school_id', app('school')->id)->first();

            return view('frontEnd.home.examResult', compact('exam_types', 'page'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examResultSearch(ExamResultSearch $request)
    {
        try {
            $exam_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $page = FrontendExamResult::where('school_id', app('school')->id)->first();
            $school_id = app('school')->id;
            $student = AramiscStudent::where('admission_no', $request->admission_number)->where('school_id', $school_id)->first();
            if ($student) {

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

                $failgpa = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->min('gpa');

                $failgpaname = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->where('gpa', $failgpa)
                    ->first();

                $exams = AramiscExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $classes = AramiscClass::where('active_status', 1)
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
                $get_optional_subject = AramiscOptionalSubjectAssign::where('record_id', '=', $student_detail->id)
                    ->where('session_id', '=', $student_detail->session_id)
                    ->first();

                if ($get_optional_subject != '') {
                    $optional_subject = $get_optional_subject->subject_id;
                }

                $optional_subject_setup = AramiscClassOptionalSubject::where('class_id', '=', $class_id)
                    ->first();

                $mark_sheet = AramiscResultStore::where([['class_id', $class_id], ['exam_type_id', $request->exam], ['section_id', $section_id], ['student_id', $student_id]])
                    ->whereIn('subject_id', $subjects->pluck('subject_id')->toArray())
                    ->where('school_id', $school_id)
                    ->get();

                $grades = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->orderBy('gpa', 'desc')
                    ->get();

                $maxGrade = AramiscMarksGrade::where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->max('gpa');

                if (count($mark_sheet) == 0) {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                    return redirect()->back();
                }

                $is_result_available = AramiscResultStore::where([['class_id', $class_id], ['exam_type_id', $request->exam], ['section_id', $section_id], ['student_id', $student_id]])
                    ->where('created_at', 'LIKE', '%' . YearCheck::getYear() . '%')
                    ->where('school_id', $school_id)
                    ->get();

                $marks_register = AramiscMarksRegister::where('exam_id', $request->exam)
                    ->where('student_id', $student_id)
                    ->first();

                $subjects = AramiscAssignSubject::where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $exams = AramiscExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $classes = AramiscClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $grades = AramiscMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $class = AramiscClass::find($class_id);
                $section = AramiscSection::find($section_id);
                $exam_detail = AramiscExam::find($request->exam);

                return view('frontEnd.home.examResult', compact(
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

    public function classExamRoutine()
    {
        try {
            $classes = AramiscClass::get();
            $sections = AramiscSection::get();
            $exam_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $routine_page = AramiscClassExamRoutinePage::where('school_id', app('school')->id)->first();
            return view('frontEnd.home.classExamRoutine', compact('routine_page', 'exam_types', 'classes', 'sections'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function classExamRoutineSearch(Request $request)
    {
        $input = $request->all(); {
            $validator = ($request->type == 'class') ? Validator::make($input, [
                'type' => 'required',
                'class' => 'required',
                'section' => 'required',
            ]) : Validator::make($input, [
                'type' => 'required',
                'class' => 'required',
                'section' => 'required',
                'exam' => 'required',
            ]);
        }
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $classes = AramiscClass::get();
            $sections = AramiscSection::get();
            $exam_types = AramiscExamType::where('school_id', app('school')->id)->get();
            $routine_page = AramiscClassExamRoutinePage::where('school_id', app('school')->id)->first();
            $header_class = AramiscClass::where('id', $request->class)->first();
            $header_section = AramiscSection::where('id', $request->section)->first();
            $class_id = $request->class ? $request->class : 0;
            $section_id = $request->section ? $request->section : 0;
            $exam_type_id = $request->exam ? $request->exam : 0;

            $aramisc_weekends = ($request->type == 'class') ? AramiscWeekend::with(['classRoutine' => function ($q) use ($class_id, $section_id) {
                return $q->where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->orderBy('start_time', 'asc');
            }, 'classRoutine.subject'])
                ->where('school_id', app('school')->id)
                ->orderBy('order', 'ASC')
                ->where('active_status', 1)
                ->get() : null;

            $exam_schedules = ($request->type == 'exam') ? AramiscExamSchedule::where('school_id', app('school')->id)
                ->when($request->exam, function ($query) use ($request) {
                    $query->where('exam_term_id', $request->exam);
                })
                ->when($request->class, function ($query) use ($request) {
                    $query->where('class_id', $request->class);
                })
                ->when($request->section, function ($query) use ($request) {
                    $query->where('section_id', $request->section);
                })
                ->get() : null;

            return view('frontEnd.home.classExamRoutine', compact('routine_page', 'exam_types', 'classes', 'sections', 'aramisc_weekends', 'exam_schedules', 'header_class', 'header_section', 'class_id', 'section_id', 'exam_type_id'));
        } catch (\Exception $e) {
            Toastr::error('Routine Not Found', 'Failed');
            return redirect()->back();
        }
    }
}
