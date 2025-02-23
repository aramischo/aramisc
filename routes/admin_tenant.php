<?php


use App\AramiscStaff;
use App\AramiscSchool;
use App\AramiscBaseSetup;
use App\Models\Theme;
use App\AramiscNotification;
use App\AramiscGeneralSettings;
use App\AramiscModuleManager;
use App\AramiscHeaderMenuManager;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Larabuild\Pagebuilder\Models\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Modules\MenuManage\Entities\Sidebar;
use Modules\RolePermission\Entities\Permission;
use Modules\RolePermission\Entities\AramiscModuleInfo;
use App\Http\Controllers\TeacherEvaluationController;
use Modules\RolePermission\Entities\AssignPermission;
use App\Http\Controllers\AramiscAcademicCalendarController;
use Modules\RolePermission\Entities\AramiscPermissionAssign;
use App\Http\Controllers\TeacherEvaluationReportController;
use App\Http\Controllers\Admin\SystemSettings\PluginController;
use Modules\RolePermission\Entities\AramiscModuleStudentParentInfo;
use App\Http\Controllers\Admin\FrontSettings\ThemeManageController;
use App\Http\Controllers\Admin\FeesCollection\AramiscFeesCarryForwardController;
use App\Http\Controllers\Admin\FeesCollection\DueFeesLoginPermissionController;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
Route::get('checkForeignKey', 'HomeController@checkForeignKey')->name('checkForeignKey');

//ADMIN
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('reg', function () {
    dd('hello');
});

Route::group(['middleware' => ['XSS', 'subscriptionAccessUrl']], function () {

    // User Auth Routes
    Route::group(['middleware' => ['CheckDashboardMiddleware']], function () {

        Route::get('staff-download-timeline-doc/{file_name}', function ($file_name = null) {
            // return "Timeline";                                                                                  
            $file = public_path() . '/uploads/student/timeline/' . $file_name;
            // echo $file;
            // exit();
            if (file_exists($file)) {
                return Response::download($file);
            }
            return redirect()->back();
        })->name('staff-download-timeline-doc');

        Route::get('download-holiday-document/{file_name}', function ($file_name = null) {
            // return "Timeline";
            $file = public_path() . '/uploads/holidays/' . $file_name;

            if (file_exists($file)) {
                return Response::download($file);
            }
            return redirect()->back();
        })->name('download-holiday-document');

        Route::get('get-other-days-ajax', 'Admin\Academics\AramiscClassRoutineNewController@getOtherDaysAjax');


        /* ******************* Dashboard Setting ***************************** */
        Route::get('dashboard/display-setting', 'Admin\SystemSettings\AramiscSystemSettingController@displaySetting');
        Route::post('dashboard/display-setting-update', 'Admin\SystemSettings\AramiscSystemSettingController@displaySettingUpdate');


        /* ******************* Dashboard Setting ***************************** */
        Route::get('api/permission', 'Admin\SystemSettings\AramiscSystemSettingController@apiPermission')->name('api/permission')->middleware('userRolePermission:api/permission');
        Route::get('api-permission-update', 'Admin\SystemSettings\AramiscSystemSettingController@apiPermissionUpdate');
        Route::post('set-fcm_key', 'Admin\SystemSettings\AramiscSystemSettingController@setFCMkey')->name('set_fcm_key');
        /* ******************* Dashboard Setting ***************************** */

        Route::get('delete-student-document/{id}', ['as' => 'delete-student-document', 'uses' => 'AramiscStudentAdmissionController@deleteDocument']);


        Route::view('/admin-setup', 'frontEnd.admin_setup');
        Route::view('/general-setting', 'frontEnd.general_setting');
        Route::view('/student-id', 'frontEnd.student_id');
        Route::view('/add-homework', 'frontEnd.add_homework');
        // Route::view('/fees-collection-invoice', 'frontEnd.fees_collection_invoice');
        Route::view('/exam-promotion-naim', 'frontEnd.exam_promotion');
        Route::view('/front-cms-gallery', 'frontEnd.front_cms_gallery');
        Route::view('/front-cms-media-manager', 'frontEnd.front_cms_media_manager');
        Route::view('/reports-class', 'frontEnd.reports_class');
        Route::view('/human-resource-payroll-generate', 'frontEnd.human_resource_payroll_generate');
        // Route::view('/fees-collection-collect-fees', 'frontEnd.fees_collection_collect_fees');
        Route::view('/calendar', 'frontEnd.calendar');
        Route::view('/design', 'frontEnd.design');
        Route::view('/loginn', 'frontEnd.login');
        Route::view('/dash-board/super-admin', 'frontEnd.dashBoard.super_admin');
        Route::view('/admit-card-report', 'frontEnd.admit_card_report');
        Route::view('/reports-terminal-report2', 'frontEnd.reports_terminal_report');
        // Route::view('/reports-tabulation-sheet', 'frontEnd.reports_tabulation_sheet');
        Route::view('/system-settings-sms', 'frontEnd.system_settings_sms');
        Route::view('/front-cms-setting', 'frontEnd.front_cms_setting');
        Route::view('/base_setup_naim', 'frontEnd.base_setup');
        Route::view('/dark-home', 'frontEnd.home.dark_home');
        Route::view('/dark-about', 'frontEnd.home.dark_about');
        Route::view('/dark-news', 'frontEnd.home.dark_news');
        Route::view('/dark-news-details', 'frontEnd.home.dark_news_details');
        Route::view('/dark-course', 'frontEnd.home.dark_course');
        Route::view('/dark-course-details', 'frontEnd.home.dark_course_details');
        Route::view('/dark-department', 'frontEnd.home.dark_department');
        Route::view('/dark-contact', 'frontEnd.home.dark_contact');
        Route::view('/light-home', 'frontEnd.home.light_home');
        Route::view('/light-about', 'frontEnd.home.light_about');
        Route::view('/light-news', 'frontEnd.home.light_news');
        Route::view('/light-news-details', 'frontEnd.home.light_news_details');
        Route::view('/light-course', 'frontEnd.home.light_course');
        Route::view('/light-course-details', 'frontEnd.home.light_course_details');
        Route::view('/light-department', 'frontEnd.home.light_department');
        Route::view('/light-contact', 'frontEnd.home.light_contact');
        Route::view('/color-home', 'frontEnd.home.color_home');
        Route::view('/id-card', 'frontEnd.home.id_card');

        Route::get('/viewFile/{id}', 'HomeController@viewFile')->name('viewFile');

        Route::get('/dashboard', 'HomeController@index')->name('dashboard');
        Route::get('add-toDo', 'HomeController@addToDo');
        Route::post('saveToDoData', 'HomeController@saveToDoData')->name('saveToDoData');
        Route::get('view-toDo/{id}', 'HomeController@viewToDo')->where('id', '[0-9]+');
        Route::get('view-toDo/{id}', 'HomeController@viewToDo')->where('id', '[0-9]+');
        Route::get('edit-toDo/{id}', 'HomeController@editToDo')->where('id', '[0-9]+');
        Route::post('update-to-do', 'HomeController@updateToDo');
        Route::get('remove-to-do', 'HomeController@removeToDo');
        Route::get('get-to-do-list', 'HomeController@getToDoList');

        Route::get('admin-dashboard', 'HomeController@index')->name('admin-dashboard');


        //Role Setup
        Route::get('role', ['as' => 'role', 'uses' => 'Admin\RolePermission\RoleController@index']);
        Route::post('role-store', ['as' => 'role_store', 'uses' => 'Admin\RolePermission\RoleController@store']);
        Route::get('role-edit/{id}', ['as' => 'role_edit', 'uses' => 'Admin\RolePermission\RoleController@edit'])->where('id', '[0-9]+');
        Route::post('role-update', ['as' => 'role_update', 'uses' => 'Admin\RolePermission\RoleController@update']);
        Route::post('role-delete', ['as' => 'role_delete', 'uses' => 'Admin\RolePermission\RoleController@delete']);


        // Role Permission
        // Route::get('assign-permission/{id}', ['as' => 'assign_permission', 'uses' => 'AramiscRolePermissionController@assignPermission']);
        // Route::post('role-permission-store', ['as' => 'role_permission_store', 'uses' => 'AramiscRolePermissionController@rolePermissionStore']);


        // Module Permission

        Route::get('module-permission', 'Admin\RolePermission\RoleController@modulePermission')->name('module-permission');


        Route::get('assign-module-permission/{id}', 'Admin\RolePermission\RoleController@assignModulePermission')->name('assign-module-permission');
        Route::post('module-permission-store', 'Admin\RolePermission\RoleController@assignModulePermissionStore')->name('module-permission-store');


        //User Route
        Route::get('user', ['as' => 'user', 'uses' => 'UserController@index']);
        Route::get('user-create', ['as' => 'user_create', 'uses' => 'UserController@create']);

        // Base group
        // Route::get('base-group', ['as' => 'base_group', 'uses' => 'AramiscBaseGroupController@index']);
        // Route::post('base-group-store', ['as' => 'base_group_store', 'uses' => 'AramiscBaseGroupController@store']);
        // Route::get('base-group-edit/{id}', ['as' => 'base_group_edit', 'uses' => 'AramiscBaseGroupController@edit']);
        // Route::post('base-group-update', ['as' => 'base_group_update', 'uses' => 'AramiscBaseGroupController@update']);
        // Route::get('base-group-delete/{id}', ['as' => 'base_group_delete', 'uses' => 'AramiscBaseGroupController@delete']);

        // Base setup
        Route::get('base-setup', ['as' => 'base_setup', 'uses' => 'Admin\SystemSettings\AramiscBaseSetupController@index'])->middleware('userRolePermission:base_setup');
        Route::post('base-setup-store', ['as' => 'base_setup_store', 'uses' => 'Admin\SystemSettings\AramiscBaseSetupController@store'])->middleware('userRolePermission:base_setup_store');
        Route::get('base-setup-edit/{id}', ['as' => 'base_setup_edit', 'uses' => 'Admin\SystemSettings\AramiscBaseSetupController@edit'])->middleware('userRolePermission:base_setup_edit');
        Route::post('base-setup-update', ['as' => 'base_setup_update', 'uses' => 'Admin\SystemSettings\AramiscBaseSetupController@update'])->middleware('userRolePermission:base_setup_edit');
        Route::post('base-setup-delete', ['as' => 'base_setup_delete', 'uses' => 'Admin\SystemSettings\AramiscBaseSetupController@delete'])->middleware('userRolePermission:base_setup_delete');

        //// Academics Routing

        // Class route
        Route::get('class', ['as' => 'class', 'uses' => 'Admin\Academics\AramiscClassController@index'])->middleware('userRolePermission:class');
        Route::post('class-store', ['as' => 'class_store', 'uses' => 'Admin\Academics\AramiscClassController@store'])->middleware('userRolePermission:class_store');
        Route::get('class-edit/{id}', ['as' => 'class_edit', 'uses' => 'Admin\Academics\AramiscClassController@edit'])->middleware('userRolePermission:class_edit');
        Route::post('class-update', ['as' => 'class_update', 'uses' => 'Admin\Academics\AramiscClassController@update'])->middleware('userRolePermission:class_edit');
        Route::get('class-delete/{id}', ['as' => 'class_delete', 'uses' => 'Admin\Academics\AramiscClassController@delete'])->middleware('userRolePermission:class_delete');


        //*********************************************** START SUBJECT WISE ATTENDANCE ****************************************************** */
        Route::get('subject-wise-attendance',  'Admin\StudentInfo\AramiscSubjectAttendanceController@index')->name('subject-wise-attendance')->middleware('userRolePermission:subject-wise-attendance');
        Route::get('subject-attendance-search',  'Admin\StudentInfo\AramiscSubjectAttendanceController@search')->name('subject-attendance-search');
        Route::post('subject-attendance-store',  'Admin\StudentInfo\AramiscSubjectAttendanceController@storeAttendance')->name('subject-attendance-store')->middleware('userRolePermission:student-attendance-store');
        Route::post('subject-attendance-store-second',  'Admin\StudentInfo\AramiscSubjectAttendanceController@storeAttendanceSecond')->name('subject-attendance-store-second')->middleware('userRolePermission:student-attendance-store');
        Route::post('student-subject-holiday-store',  'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectHolidayStore')->name('student-subject-holiday-store');


        // Student Attendance Report
        Route::get('subject-attendance-report', 'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectAttendanceReport')->name('subject-attendance-report')->middleware('userRolePermission:subject-attendance-report');
        Route::post('subject-attendance-report-search', 'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectAttendanceReportSearch')->name('subject-attendance-report-search');
        Route::get('subject-attendance-report-search', 'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectAttendanceReport');

        Route::get('subject-attendance-average-report', 'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectAttendanceAverageReport');
        Route::post('subject-attendance-average-report', 'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectAttendanceAverageReportSearch');

        // Route::get('subject-attendance-report/print/{class_id}/{section_id}/{month}/{year}', 'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectAttendanceReportPrint');
        Route::get('subject-attendance-average/print/{class_id}/{section_id}/{month}/{year}', 'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectAttendanceReportAveragePrint')->name('subject-average-attendance/print')->middleware('userRolePermission:subject-attendance/print');

        // for university module

        Route::get('un-subject-attendance-average/print/{semester_label_id}/{month}/{year}', 'Admin\StudentInfo\AramiscSubjectAttendanceController@unSubjectAttendanceReportAveragePrint')->name('un-subject-average-attendance/print')->middleware('userRolePermission:subject-attendance/print');

        Route::get('subject-attendance/print/{class_id}/{section_id}/{month}/{year}', 'Admin\StudentInfo\AramiscSubjectAttendanceController@subjectAttendanceReportPrint')->name('subject-attendance/print')->middleware('userRolePermission:subject-attendance/print');
        //*********************************************** END SUBJECT WISE ATTENDANCE ****************************************************** */



        // Student Attendance Report
        Route::get('student-attendance-report', ['as' => 'student_attendance_report', 'uses' => 'Admin\StudentInfo\AramiscStudentAttendanceReportController@index'])->middleware('userRolePermission:student_attendance_report');
        Route::post('student-attendance-report-search', ['as' => 'student_attendance_report_search', 'uses' => 'Admin\StudentInfo\AramiscStudentAttendanceReportController@search']);
        Route::get('student-attendance-report-search', 'Admin\StudentInfo\AramiscStudentAttendanceReportController@index');
        Route::get('student-attendance/print/{class_id}/{section_id}/{month}/{year}', 'Admin\StudentInfo\AramiscStudentAttendanceReportController@print')->name('student-attendance-print');


        // for university module
        Route::get('un-student-attendance/print/{semester_id}/{month}/{year}', 'Admin\StudentInfo\AramiscStudentAttendanceReportController@unPrint')->name('un-student-attendance-print');
        //Class Section routes
        Route::get('optional-subject',  'Admin\SystemSettings\AramiscOptionalSubjectAssignController@index')->name('optional-subject')->middleware('userRolePermission:optional-subject');

        Route::any('assign-optional-subject',  'Admin\SystemSettings\AramiscOptionalSubjectAssignController@assignOptionalSubjectSearch')->name('assign_optional_subject_search');
        Route::any('assign-optional-subject-search',  'Admin\SystemSettings\AramiscOptionalSubjectAssignController@assignOptionalSubject');
        Route::post('assign-optional-subject-store',  'Admin\SystemSettings\AramiscOptionalSubjectAssignController@assignOptionalSubjectStore')->name('assign-optional-subject-store');


        Route::get('section', ['as' => 'section', 'uses' => 'Admin\Academics\AramiscSectionController@index'])->middleware('userRolePermission:section');

        Route::post('section-store', ['as' => 'section_store', 'uses' => 'Admin\Academics\AramiscSectionController@store'])->middleware('userRolePermission:section_store');
        Route::get('section-edit/{id}', ['as' => 'section_edit', 'uses' => 'Admin\Academics\AramiscSectionController@edit'])->middleware('userRolePermission:section_edit');
        Route::post('section-update', ['as' => 'section_update', 'uses' => 'Admin\Academics\AramiscSectionController@update'])->middleware('userRolePermission:section_edit');
        Route::get('section-delete/{id}', ['as' => 'section_delete', 'uses' => 'Admin\Academics\AramiscSectionController@delete'])->middleware('userRolePermission:section_delete');

        // Subject routes
        Route::get('subject', ['as' => 'subject', 'uses' => 'Admin\Academics\AramiscSubjectController@index'])->middleware('userRolePermission:subject');
        Route::post('subject-store', ['as' => 'subject_store', 'uses' => 'Admin\Academics\AramiscSubjectController@store'])->middleware('userRolePermission:subject_store');
        Route::get('subject-edit/{id}', ['as' => 'subject_edit', 'uses' => 'Admin\Academics\AramiscSubjectController@edit'])->middleware('userRolePermission:subject_edit');
        Route::post('subject-update', ['as' => 'subject_update', 'uses' => 'Admin\Academics\AramiscSubjectController@update'])->middleware('userRolePermission:subject_edit');
        Route::get('subject-delete/{id}', ['as' => 'subject_delete', 'uses' => 'Admin\Academics\AramiscSubjectController@delete'])->middleware('userRolePermission:subject_delete');

        //Class Routine
        // Route::get('class-routine', ['as' => 'class_routine', 'uses' => 'AramiscAcademicsController@classRoutine']);
        // Route::get('class-routine-create', ['as' => 'class_routine_create', 'uses' => 'AramiscAcademicsController@classRoutineCreate']);
        Route::get('ajaxSelectSubject', 'AramiscAcademicsController@ajaxSelectSubject');
        Route::get('ajaxSelectCurrency', 'Admin\SystemSettings\AramiscSystemSettingController@ajaxSelectCurrency');

        // Route::post('assign-routine-search', 'AramiscAcademicsController@assignRoutineSearch');
        // Route::get('assign-routine-search', 'AramiscAcademicsController@classRoutine');
        // Route::post('assign-routine-store', 'AramiscAcademicsController@assignRoutineStore');
        // Route::post('class-routine-report-search', 'AramiscAcademicsController@classRoutineReportSearch');
        // Route::get('class-routine-report-search', 'AramiscAcademicsController@classRoutineReportSearch');


        // class routine new

        Route::get('class-routine-new', ['as' => 'class_routine_new', 'uses' => 'Admin\Academics\AramiscClassRoutineNewController@classRoutine'])->middleware('userRolePermission:class_routine');




        // Route::post('class-routine-new', 'Admin\Academics\AramiscClassRoutineNewController@classRoutineSearch')->name('class_routine_new');
        Route::get('add-new-routine/{class_time_id}/{day}/{class_id}/{section_id}', 'Admin\Academics\AramiscClassRoutineNewController@addNewClassRoutine')->name('add-new-routine')->middleware('userRolePermission:add-new-class-routine-store');

        Route::post('add-new-class-routine-store', 'Admin\Academics\AramiscClassRoutineNewController@addNewClassRoutineStore')->name('add-new-class-routine-store')->middleware('userRolePermission:add-new-class-routine-store');


        Route::get('get-class-teacher-ajax', 'Admin\Academics\AramiscClassRoutineNewController@getClassTeacherAjax');
        Route::get('delete-class-routine/{id}', 'Admin\Academics\AramiscClassRoutineNewController@deleteClassRoutine')->name('delete-class-routine')->middleware('userRolePermission:delete-class-routine');

        Route::get('class-routine-new/{class_id}/{section_id}', 'Admin\Academics\AramiscClassRoutineNewController@classRoutineRedirect');

        Route::post('delete-class-routine', 'Admin\Academics\AramiscClassRoutineNewController@destroyClassRoutine')->name('destroy-class-routine')->middleware('userRolePermission:delete-class-routine');
        //Student Panel

        Route::get('print-teacher-routine/{teacher_id}', 'Admin\Academics\AramiscClassRoutineNewController@printTeacherRoutine')->name('print-teacher-routine');
        Route::get('view-teacher-routine', 'teacher\AramiscAcademicsController@viewTeacherRoutine')->name('view-teacher-routine');

        //assign subject
        Route::get('assign-subject', ['as' => 'assign_subject', 'uses' => 'Admin\Academics\AramiscAssignSubjectController@index'])->middleware('userRolePermission:assign_subject');

        Route::get('assign-subject-create', ['as' => 'assign_subject_create', 'uses' => 'Admin\Academics\AramiscAssignSubjectController@create'])->middleware('userRolePermission:assign-subject-store');

        Route::post('assign-subject-search', ['as' => 'assign_subject_search', 'uses' => 'Admin\Academics\AramiscAssignSubjectController@search']);
        Route::get('assign-subject-search', 'Admin\Academics\AramiscAssignSubjectController@create');
        Route::post('assign-subject-store', 'Admin\Academics\AramiscAssignSubjectController@assignSubjectStore')->name('assign-subject-store')->middleware('userRolePermission:assign-subject-store');
        Route::get('assign-subject-store', 'Admin\Academics\AramiscAssignSubjectController@create');
        Route::post('assign-subject', 'Admin\Academics\AramiscAssignSubjectController@assignSubjectFind')->name('assign-subject');
        Route::get('assign-subject-get-by-ajax', 'Admin\Academics\AramiscAssignSubjectController@assignSubjectAjax');

        //Assign Class Teacher
        // Route::resource('assign-class-teacher', 'AramiscAssignClassTeacherControler')->middleware('userRolePermission:253');
        Route::get('assign-class-teacher', 'Admin\Academics\AramiscAssignClassTeacherController@index')->name('assign-class-teacher')->middleware('userRolePermission:assign-class-teacher');
        Route::post('assign-class-teacher', 'Admin\Academics\AramiscAssignClassTeacherController@store')->name('assign-class-teacher-store')->middleware('userRolePermission:assign-class-teacher-store');
        Route::get('assign-class-teacher/{id}', 'Admin\Academics\AramiscAssignClassTeacherController@edit')->name('assign-class-teacher-edit')->middleware('userRolePermission:assign-class-teacher-edit');
        Route::put('assign-class-teacher/{id}', 'Admin\Academics\AramiscAssignClassTeacherController@update')->name('assign-class-teacher-update')->middleware('userRolePermission:assign-class-teacher-edit');
        Route::delete('assign-class-teacher/{id}', 'Admin\Academics\AramiscAssignClassTeacherController@destroy')->name('assign-class-teacher-delete')->middleware('userRolePermission:assign-class-teacher-delete');
        // Class room
        // Route::resource('class-room', 'AramiscClassRoomController')->middleware('userRolePermission:269');
        Route::get('class-room', 'Admin\Academics\AramiscClassRoomController@index')->name('class-room')->middleware('userRolePermission:class-room');
        Route::post('class-room', 'Admin\Academics\AramiscClassRoomController@store')->name('class-room-store')->middleware('userRolePermission:class-room-store');
        Route::get('class-room/{id}', 'Admin\Academics\AramiscClassRoomController@edit')->name('class-room-edit')->middleware('userRolePermission:class-room-edit');
        Route::put('class-room/{id}', 'Admin\Academics\AramiscClassRoomController@update')->name('class-room-update')->middleware('userRolePermission:class-room-edit');
        Route::delete('class-room/{id}', 'Admin\Academics\AramiscClassRoomController@destroy')->name('class-room-delete')->middleware('userRolePermission:class-room-delete');

        // Route::resource('class-time', 'AramiscClassTimeController')->middleware('userRolePermission:273');
        // Route::get('class-time', 'Admin\Academics\AramiscClassTimeController@index')->name('class-time')->middleware('userRolePermission:273');
        // Route::post('class-time', 'Admin\Academics\AramiscClassTimeController@store')->name('class-time')->middleware('userRolePermission:274');
        // Route::get('class-time/{id}', 'Admin\Academics\AramiscClassTimeController@edit')->name('class-time-edit')->middleware('userRolePermission:275');
        // Route::put('class-time/{id}', 'Admin\Academics\AramiscClassTimeController@update')->name('class-time-update')->middleware('userRolePermission:275');
        // Route::delete('class-time/{id}', 'Admin\Academics\AramiscClassTimeController@destroy')->name('class-time-delete');




        //Admission Query
        Route::get('admission-query', ['as' => 'admission_query', 'uses' => 'Admin\AdminSection\AramiscAdmissionQueryController@index'])->middleware('userRolePermission:admission_query');

        Route::post('admission-query-store-a', ['as' => 'admission_query_store_a', 'uses' => 'Admin\AdminSection\AramiscAdmissionQueryController@store']);

        Route::get('admission-query-edit/{id}', ['as' => 'admission_query_edit', 'uses' => 'Admin\AdminSection\AramiscAdmissionQueryController@edit'])->middleware('userRolePermission:admission_query_edit');
        Route::post('admission-query-update', ['as' => 'admission_query_update', 'uses' => 'Admin\AdminSection\AramiscAdmissionQueryController@update']);
        Route::get('add-query/{id}', ['as' => 'add_query', 'uses' => 'Admin\AdminSection\AramiscAdmissionQueryController@addQuery']);
        Route::post('query-followup-store', ['as' => 'query_followup_store', 'uses' => 'Admin\AdminSection\AramiscAdmissionQueryController@queryFollowupStore']);
        Route::get('delete-follow-up/{id}', ['as' => 'delete_follow_up', 'uses' => 'Admin\AdminSection\AramiscAdmissionQueryController@deleteFollowUp']);
        Route::post('admission-query-delete', ['as' => 'admission_query_delete', 'uses' => 'Admin\AdminSection\AramiscAdmissionQueryController@delete'])->middleware('userRolePermission:admission_query_delete');

        Route::post('admission-query-search', 'Admin\AdminSection\AramiscAdmissionQueryController@admissionQuerySearch')->name('admission-query-search');
        Route::get('admission-query-search', 'Admin\AdminSection\AramiscAdmissionQueryController@index');

        Route::get('admission-query-datatable', 'Admin\AdminSection\AramiscAdmissionQueryController@admissionQueryDatatable')->name('admission-query-datatable');

        // Visitor routes

        Route::get('visitor', ['as' => 'visitor', 'uses' => 'Admin\AdminSection\AramiscVisitorController@index'])->middleware('userRolePermission:visitor');
        Route::post('visitor-store', ['as' => 'visitor_store', 'uses' => 'Admin\AdminSection\AramiscVisitorController@store'])->middleware('userRolePermission:visitor_store');
        Route::get('visitor-edit/{id}', ['as' => 'visitor_edit', 'uses' => 'Admin\AdminSection\AramiscVisitorController@edit'])->middleware('userRolePermission:visitor_edit');
        Route::post('visitor-update', ['as' => 'visitor_update', 'uses' => 'Admin\AdminSection\AramiscVisitorController@update'])->middleware('userRolePermission:visitor_edit');
        Route::post('visitor-delete', ['as' => 'visitor_delete', 'uses' => 'Admin\AdminSection\AramiscVisitorController@delete'])->middleware('userRolePermission:visitor_delete');
        Route::get('download-visitor-document/{file_name}', ['as' => 'visitor_download', 'uses' => 'Admin\AdminSection\AramiscVisitorController@download_files'])->middleware('userRolePermission:visitor_download');

        Route::get('visitor-datatable', ['as' => 'visitor_datatable', 'uses' => 'Admin\AdminSection\AramiscVisitorController@visitorDatatable']);

        // Route::get('download-visitor-document/{file_name}', function ($file_name = null) {

        //     $file = public_path() . '/uploads/visitor/' . $file_name;
        //     if (file_exists($file)) {
        //         return Response::download($file);
        //     }
        // });

        // Fees Group routes
        Route::get('fees-group', ['as' => 'fees_group', 'uses' => 'Admin\FeesCollection\AramiscFeesGroupController@index'])->middleware('userRolePermission:fees_group');
        Route::post('fees-group-store', ['as' => 'fees_group_store', 'uses' => 'Admin\FeesCollection\AramiscFeesGroupController@store'])->middleware('userRolePermission:fees_group_store');
        Route::get('fees-group-edit/{id}', ['as' => 'fees_group_edit', 'uses' => 'Admin\FeesCollection\AramiscFeesGroupController@edit'])->middleware('userRolePermission:fees_group_edit');
        Route::post('fees-group-update', ['as' => 'fees_group_update', 'uses' => 'Admin\FeesCollection\AramiscFeesGroupController@update'])->middleware('userRolePermission:fees_group_edit');
        Route::post('fees-group-delete', ['as' => 'fees_group_delete', 'uses' => 'Admin\FeesCollection\AramiscFeesGroupController@deleteGroup'])->middleware('userRolePermission:fees_group_delete');

        // Fees type routes
        Route::get('fees-type', ['as' => 'fees_type', 'uses' => 'Admin\FeesCollection\AramiscFeesTypeController@index'])->middleware('userRolePermission:fees_type');
        Route::post('fees-type-store', ['as' => 'fees_type_store', 'uses' => 'Admin\FeesCollection\AramiscFeesTypeController@store'])->middleware('userRolePermission:fees_type_store');
        Route::get('fees-type-edit/{id}', ['as' => 'fees_type_edit', 'uses' => 'Admin\FeesCollection\AramiscFeesTypeController@edit'])->middleware('userRolePermission:fees_type_edit');
        Route::post('fees-type-update', ['as' => 'fees_type_update', 'uses' => 'Admin\FeesCollection\AramiscFeesTypeController@update'])->middleware('userRolePermission:fees_type_edit');
        Route::get('fees-type-delete/{id}', ['as' => 'fees_type_delete', 'uses' => 'Admin\FeesCollection\AramiscFeesTypeController@delete'])->middleware('userRolePermission:fees_type_delete');

        // Fees Discount routes
        Route::get('fees-discount', ['as' => 'fees_discount', 'uses' => 'Admin\FeesCollection\AramiscFeesDiscountController@index'])->middleware('userRolePermission:fees_discount');
        Route::post('fees-discount-store', ['as' => 'fees_discount_store', 'uses' => 'Admin\FeesCollection\AramiscFeesDiscountController@store'])->middleware('userRolePermission:fees_discount_store');
        Route::get('fees-discount-edit/{id}', ['as' => 'fees_discount_edit', 'uses' => 'Admin\FeesCollection\AramiscFeesDiscountController@edit'])->middleware('userRolePermission:fees_discount_edit');
        Route::post('fees-discount-update', ['as' => 'fees_discount_update', 'uses' => 'Admin\FeesCollection\AramiscFeesDiscountController@update'])->middleware('userRolePermission:fees_discount_edit');
        Route::get('fees-discount-delete/{id}', ['as' => 'fees_discount_delete', 'uses' => 'Admin\FeesCollection\AramiscFeesDiscountController@delete'])->middleware('userRolePermission:fees_discount_delete');
        Route::get('fees-discount-assign/{id}', ['as' => 'fees_discount_assign', 'uses' => 'Admin\FeesCollection\AramiscFeesDiscountController@feesDiscountAssign'])->middleware('userRolePermission:fees_discount_assign');
        Route::post('fees-discount-assign-search', 'Admin\FeesCollection\AramiscFeesDiscountController@feesDiscountAssignSearch')->name('fees-discount-assign-search');
        Route::post('fees-discount-assign-store', 'Admin\FeesCollection\AramiscFeesDiscountController@feesDiscountAssignStore');
        Route::post('directfees/fees-discount-assign-store', 'Admin\FeesCollection\AramiscFeesDiscountController@directFeesDiscountAssignStore')->name('directFees.fees-discount-assign-store');

        Route::get('fees-generate-modal/{amount}/{student_id}/{type}/{master}/{assign_id}/{record_id}', 'Admin\FeesCollection\AramiscFeesController@feesGenerateModal')->name('fees-generate-modal')->middleware('userRolePermission:fees-generate-modal');
        Route::get('fees-discount-amount-search', 'Admin\FeesCollection\AramiscFeesDiscountController@feesDiscountAmountSearch');
        //delete fees payment
        Route::post('fees-payment-delete', 'Admin\FeesCollection\AramiscFeesController@feesPaymentDelete')->name('fees-payment-delete');

        Route::get('direct-fees-generate-modal/{amount}/{installment_id}/{record_id}', 'Admin\FeesCollection\AramiscFeesController@directFeesGenerateModal')->name('direct-fees-generate-modal')->middleware('userRolePermission:fees-generate-modal');
        Route::post('directFeesInstallmentUpdate', 'Admin\FeesCollection\AramiscFeesController@directFeesInstallmentUpdate')->name('directFeesInstallmentUpdate')->middleware('userRolePermission:fees-generate-modal');

        Route::get('direct-fees-total-payment/{record_id}', 'Admin\FeesCollection\AramiscFeesController@directFeesTotalPayment')->name('direct-fees-total-payment');
        Route::post('direct-fees-total-payment', 'Admin\FeesCollection\AramiscFeesController@directFeesTotalPaymentSubmit')->name('direct-fees-total-payment-submit')->middleware('userRolePermission:fees-generate-modal');


        Route::get('directFees/editSubPaymentModal/{payment_id}/{paid_amount}', 'Admin\FeesCollection\AramiscFeesController@editSubPaymentModal')->name('directFees.editSubPaymentModal')->middleware('userRolePermission:fees-generate-modal');
        Route::post('directFees/deleteSubPayment', 'Admin\FeesCollection\AramiscFeesController@deleteSubPayment')->name('directFees.deleteSubPayment');
        Route::post('directFees/updateSubPaymentModal', 'Admin\FeesCollection\AramiscFeesController@updateSubPaymentModal')->name('directFees.updateSubPaymentModal');
        Route::get('directFees/viewPaymentReceipt/{id}', 'Admin\FeesCollection\AramiscFeesController@viewPaymentReceipt')->name('directFees.viewPaymentReceipt');
        Route::get('directFees/setting', 'Admin\FeesCollection\AramiscFeesController@directFeesSetting')->name('directFees.setting');
        Route::post('directFees/feesInvoiceUpdate', 'Admin\FeesCollection\AramiscFeesController@feesInvoiceUpdate')->name('directFees.feesInvoiceUpdate');
        Route::post('directFees/paymentReminder', 'Admin\FeesCollection\AramiscFeesController@paymentReminder')->name('directFees.paymentReminder');

        // Fees carry forward
        Route::get('fees-forward', ['as' => 'fees_forward', 'uses' => 'Admin\FeesCollection\AramiscFeesCarryForwardController@feesForward'])->middleware('userRolePermission:fees_forward');
        Route::post('fees-forward-search', 'Admin\FeesCollection\AramiscFeesCarryForwardController@feesForwardSearch')->name('fees-forward-search')->middleware('userRolePermission:fees_forward');
        Route::get('fees-forward-search', 'Admin\FeesCollection\AramiscFeesCarryForwardController@feesForward')->middleware('userRolePermission:fees_forward');

        Route::post('fees-forward-store', 'Admin\FeesCollection\AramiscFeesCarryForwardController@feesForwardStore')->name('fees-forward-store')->middleware('userRolePermission:fees_forward');
        Route::get('fees-forward-store', 'Admin\FeesCollection\AramiscFeesCarryForwardController@feesForward')->middleware('userRolePermission:fees_forward');;

        //fees payment store
        Route::post('fees-payment-store', 'Admin\FeesCollection\AramiscFeesController@feesPaymentStore')->name('fees-payment-store');

        Route::get('bank-slip-view/{file_name}', function ($file_name = null) {

            $file = public_path() . '/uploads/bankSlip/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('bank-slip-view');

        // Collect Fees
        Route::get('collect-fees', ['as' => 'collect_fees', 'uses' => 'Admin\FeesCollection\AramiscFeesCollectController@index'])->middleware('userRolePermission:collect_fees');
        Route::get('fees-collect-student-wise/{id}', ['as' => 'fees_collect_student_wise', 'uses' => 'Admin\FeesCollection\AramiscFeesCollectController@collectFeesStudent'])->where('id', '[0-9]+')->middleware('userRolePermission:fees_collect_student_wise');

        Route::post('collect-fees', ['as' => 'collect_fees_search', 'uses' => 'Admin\FeesCollection\AramiscFeesCollectController@search']);


        // fees print
        Route::get('fees-payment-print/{id}/{group}', ['as' => 'fees_payment_print', 'uses' => 'Admin\FeesCollection\AramiscFeesController@feesPaymentPrint']);

        Route::get('fees-payment-invoice-print/{id}/{group}', ['as' => 'fees_payment_invoice_print', 'uses' => 'Admin\FeesCollection\AramiscFeesController@feesPaymentInvoicePrint']);

        Route::get('fees-group-print/{id}', ['as' => 'fees_group_print', 'uses' => 'Admin\FeesCollection\AramiscFeesController@feesGroupPrint'])->where('id', '[0-9]+');

        Route::get('fees-groups-print/{id}/{s_id}', 'Admin\FeesCollection\AramiscFeesController@feesGroupsPrint');

        //Search Fees Payment
        Route::get('search-fees-payment', ['as' => 'search_fees_payment', 'uses' => 'Admin\FeesCollection\AramiscSearchFeesPaymentController@index'])->middleware('userRolePermission:search_fees_payment');
        Route::post('fees-payment-search', ['as' => 'fees_payment_searches', 'uses' => 'Admin\FeesCollection\AramiscSearchFeesPaymentController@search']);
        Route::get('fees-payment-search', ['as' => 'fees_payment_search', 'uses' => 'Admin\FeesCollection\AramiscSearchFeesPaymentController@index']);
        Route::get('edit-fees-payment/{id}', ['as' => 'edit-fees-payment', 'uses' => 'Admin\FeesCollection\AramiscSearchFeesPaymentController@editFeesPayment']);
        Route::post('fees-payment-update', ['as' => 'fees-payment-update', 'uses' => 'Admin\FeesCollection\AramiscSearchFeesPaymentController@updateFeesPayment']);
        //Fees Search due
        Route::get('search-fees-due', ['as' => 'search_fees_due', 'uses' => 'Admin\FeesCollection\AramiscFeesController@searchFeesDue'])->middleware('userRolePermission:search_fees_due');
        Route::post('fees-due-search', ['as' => 'fees_due_searches', 'uses' => 'Admin\FeesCollection\AramiscFeesController@feesDueSearch']);
        Route::get('fees-due-search', ['as' => 'fees_due_search', 'uses' => 'Admin\FeesCollection\AramiscFeesController@searchFeesDue']);


        Route::post('send-dues-fees-email', 'Admin\FeesCollection\AramiscFeesController@sendDuesFeesEmail')->name('send-dues-fees-email');

        // fees bank slip approve
        Route::get('bank-payment-slip', 'Admin\FeesCollection\AramiscFeesBankPaymentController@bankPaymentSlip')->name('bank-payment-slip');
        Route::post('bank-payment-slip', 'Admin\FeesCollection\AramiscFeesBankPaymentController@bankPaymentSlipSearch')->name('bank-payment-slips');
        Route::post('approve-fees-payment', 'Admin\FeesCollection\AramiscFeesBankPaymentController@approveFeesPayment')->name('approve-fees-payment');
        Route::post('reject-fees-payment', 'Admin\FeesCollection\AramiscFeesBankPaymentController@rejectFeesPayment')->name('reject-fees-payment');
        Route::get('bank-payment-slip-ajax', 'DatatableQueryController@bankPaymentSlipAjax')->name('bank-payment-slip-ajax');

        //Fees Statement
        Route::get('fees-statement', ['as' => 'fees_statement', 'uses' => 'Admin\FeesCollection\AramiscFeesController@feesStatemnt'])->middleware('userRolePermission:fees_statement');
        Route::post('fees-statement-search', ['as' => 'fees_statement_search', 'uses' => 'Admin\FeesCollection\AramiscFeesController@feesStatementSearch']);

        // Balance fees report
        Route::get('balance-fees-report', ['as' => 'balance_fees_report', 'uses' => 'Admin\FeesCollection\AramiscFeesReportController@balanceFeesReport'])->middleware('userRolePermission:balance_fees_report');
        Route::post('balance-fees-search', ['as' => 'balance_fees_searches', 'uses' => 'Admin\FeesCollection\AramiscFeesReportController@balanceFeesSearch']);
        Route::get('balance-fees-search', ['as' => 'balance_fees_search', 'uses' => 'Admin\FeesCollection\AramiscFeesReportController@balanceFeesReport']);

        // Transaction Report
        Route::get('transaction-report', ['as' => 'transaction_report', 'uses' => 'Admin\FeesCollection\AramiscCollectionReportController@transactionReport'])->middleware('userRolePermission:transaction_report');
        Route::post('transaction-report-search', ['as' => 'transaction_report_searches', 'uses' => 'Admin\FeesCollection\AramiscCollectionReportController@transactionReportSearch']);
        Route::get('transaction-report-search', ['as' => 'transaction_report_search', 'uses' => 'Admin\FeesCollection\AramiscCollectionReportController@transactionReport']);


        //Fine Report
        Route::get('fine-report', ['as' => 'fine-report', 'uses' => 'Admin\FeesCollection\AramiscFeesController@fineReport'])->middleware('userRolePermission:fine-report');
        Route::post('fine-report-search', ['as' => 'fine-report-search', 'uses' => 'Admin\FeesCollection\AramiscFeesController@fineReportSearch']);


        // Class Report
        Route::get('class-report', ['as' => 'class_report', 'uses' => 'AramiscAcademicsController@classReport'])->middleware('userRolePermission:class_report');
        Route::post('class-report', ['as' => 'class_reports', 'uses' => 'AramiscAcademicsController@classReportSearch']);


        // merit list Report
        Route::get('merit-list-report', ['as' => 'merit_list_report', 'uses' => 'Admin\Examination\AramiscExaminationController@meritListReport'])->middleware('userRolePermission:merit_list_report');
        Route::post('merit-list-report', ['as' => 'merit_list_reports', 'uses' => 'Admin\Examination\AramiscExaminationController@meritListReportSearch']);
        Route::get('merit-list/print/{exam_id}/{class_id}/{section_id}',  'Admin\Examination\AramiscExaminationController@meritListPrint')->name('merit-list/print');


        //tabulation sheet report
        Route::get('reports-tabulation-sheet', ['as' => 'reports_tabulation_sheet', 'uses' => 'Admin\Examination\AramiscExaminationController@reportsTabulationSheet']);
        Route::post('reports-tabulation-sheet', ['as' => 'reports_tabulation_sheets', 'uses' => 'Admin\Examination\AramiscExaminationController@reportsTabulationSheetSearch']);


        //results-archive report resultsArchive
        Route::get('results-archive', 'Admin\Examination\AramiscExaminationController@resultsArchiveView')->name('results-archive');
        Route::get('get-archive-class', 'Admin\Examination\AramiscExaminationController@getArchiveClass');
        Route::post('results-archive',  'Admin\Examination\AramiscExaminationController@resultsArchiveSearch');

        //Previous Record
        Route::get('previous-record', 'AramiscStudentAdmissionController@previousRecord')->name('previous-record')->middleware('userRolePermission:previous-record');
        Route::post('previous-record',  'AramiscStudentAdmissionController@previousRecordSearch')->name('previous-records');

        //previous-class-results
        Route::get('previous-class-results', 'Admin\Examination\AramiscExaminationController@previousClassResults')->name('previous-class-results')->middleware('userRolePermission:previous-class-results');
        Route::post('previous-class-results-view', 'Admin\Examination\AramiscExaminationController@previousClassResultsViewPost')->name('previous-class-results-view');
        Route::post('previous-student-record', 'Admin\Examination\AramiscExaminationController@previousStudentRecord')->name('previous-student-record');

        Route::post('session-student', 'Admin\Examination\AramiscExaminationController@sessionStudentGet')->name('session_student');

        Route::post('previous-class-results', 'Admin\Examination\AramiscExaminationController@previousClassResultsViewPrint')->name('previous-class-result-print');
        // merit list Report
        Route::get('online-exam-report', ['as' => 'online_exam_report', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamReport'])->middleware('userRolePermission:online_exam_report');
        Route::post('online-exam-report', ['as' => 'online_exam_reports', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamReportSearch']);

        // class routine report
        Route::get('class-routine-report', ['as' => 'class_routine_report', 'uses' => 'Admin\Academics\AramiscClassRoutineNewController@classRoutineReport'])->middleware('userRolePermission:class_routine_report');
        Route::post('class-routine-report', 'Admin\Academics\AramiscClassRoutineNewController@classRoutineReportSearch')->name('class_routine_reports');


        // exam routine report
        Route::get('exam-routine-report', ['as' => 'exam_routine_report', 'uses' => 'Admin\Examination\AramiscExamRoutineController@examRoutineReport'])->middleware('userRolePermission:exam_routine_report');
        Route::post('exam-routine-report', ['as' => 'exam_routine_reports', 'uses' => 'Admin\Examination\AramiscExamRoutineController@examRoutineReportSearch']);


        Route::get('exam-routine/print/{exam_id}', 'Admin\Examination\AramiscExamRoutineController@examRoutineReportSearchPrint')->name('exam-routine/print');

        Route::get('teacher-class-routine-report', ['as' => 'teacher_class_routine_report', 'uses' => 'Admin\Academics\AramiscClassRoutineNewController@teacherClassRoutineReport'])->middleware('userRolePermission:teacher_class_routine_report');
        Route::post('teacher-class-routine-report', 'Admin\Academics\AramiscClassRoutineNewController@teacherClassRoutineReportSearch')->name('teacher-class-routine-report');


        // mark sheet Report
        Route::get('mark-sheet-report', ['as' => 'mark_sheet_report', 'uses' => 'Admin\Examination\AramiscExaminationController@markSheetReport']);
        Route::post('mark-sheet-report', ['as' => 'mark_sheet_reports', 'uses' => 'Admin\Examination\AramiscExaminationController@markSheetReportSearch']);
        Route::get('mark-sheet-report/print/{exam_id}/{class_id}/{section_id}/{student_id}', ['as' => 'mark_sheet_report_print', 'uses' => 'Admin\Examination\AramiscExaminationController@markSheetReportStudentPrint']);


        //mark sheet report student
        Route::get('mark-sheet-report-student', ['as' => 'mark_sheet_report_student', 'uses' => 'Admin\Examination\AramiscExaminationController@markSheetReportStudent'])->middleware('userRolePermission:mark_sheet_report_student');
        Route::post('mark-sheet-report-student', ['as' => 'mark_sheet_report_students', 'uses' => 'Admin\Examination\AramiscExaminationController@markSheetReportStudentSearch']);

        //100 Percent mark sheet report student
        Route::get('percent-marksheet-report', ['as' => 'percent-marksheet-report', 'uses' => 'Admin\Examination\AramiscExaminationController@percentMarkSheetReport']);


        //user log
        Route::get('student-fine-report', ['as' => 'student_fine_report', 'uses' => 'Admin\FeesCollection\AramiscFeesController@studentFineReport'])->middleware('userRolePermission:student_fine_report');
        Route::post('student-fine-report', ['as' => 'student_fine_reports', 'uses' => 'Admin\FeesCollection\AramiscFeesController@studentFineReportSearch']);
        Route::get('user-log-ajax', ['as' => 'user_log_ajax', 'uses' => 'DatatableQueryController@userLogAjax'])->middleware('userRolePermission:user_log');

        //user log
        Route::get('user-log', ['as' => 'user_log', 'uses' => 'UserController@userLog'])->middleware('userRolePermission:user_log');

        Route::get('income-list-datatable', ['as' => 'incom_list_datatable', 'uses' => 'DatatableQueryController@incomeList']);

        // income head routes
        // Route::get('income-head', ['as' => 'income_head', 'uses' => 'AramiscIncomeHeadController@index']);
        // Route::post('income-head-store', ['as' => 'income_head_store', 'uses' => 'AramiscIncomeHeadController@store']);
        // Route::get('income-head-edit/{id}', ['as' => 'income_head_edit', 'uses' => 'AramiscIncomeHeadController@edit']);
        // Route::post('income-head-update', ['as' => 'income_head_update', 'uses' => 'AramiscIncomeHeadController@update']);
        // Route::get('income-head-delete/{id}', ['as' => 'income_head_delete', 'uses' => 'AramiscIncomeHeadController@delete']);

        // Search account
        Route::get('search-account', ['as' => 'search_account', 'uses' => 'Admin\Accounts\AramiscAccountsController@searchAccount'])->middleware('userRolePermission:147');
        Route::post('search-account', ['as' => 'search_accounts', 'uses' => 'Admin\Accounts\AramiscAccountsController@searchAccountReportByDate']);
        Route::get('fund-transfer', ['as' => 'fund-transfer', 'uses' => 'Admin\Accounts\AramiscAccountsController@fundTransfer'])->middleware('userRolePermission:fund-transfer');
        Route::post('fund-transfer-store', ['as' => 'fund-transfer-store', 'uses' => 'Admin\Accounts\AramiscAccountsController@fundTransferStore']);
        Route::get('transaction', ['as' => 'transaction', 'uses' => 'Admin\Accounts\AramiscAccountsController@transaction'])->middleware('userRolePermission:transaction');
        Route::post('transaction-search', ['as' => 'transaction-search', 'uses' => 'Admin\Accounts\AramiscAccountsController@transactionSearch']);

        // Accounts Payroll Report
        Route::get('accounts-payroll-report', ['as' => 'accounts-payroll-report', 'uses' => 'Admin\Accounts\AramiscAccountsController@accountsPayrollReport'])->middleware('userRolePermission:accounts-payroll-report');
        Route::post('accounts-payroll-report-search', ['as' => 'accounts-payroll-report-search', 'uses' => 'Admin\Accounts\AramiscAccountsController@accountsPayrollReportSearch']);


        // add income routes
        Route::get('add-income', ['as' => 'add_income', 'uses' => 'Admin\Accounts\AramiscAddIncomeController@index'])->middleware('userRolePermission:add_income');
        Route::post('add-income-store', ['as' => 'add_income_store', 'uses' => 'Admin\Accounts\AramiscAddIncomeController@store'])->middleware('userRolePermission:add_income_store');
        Route::get('add-income-edit/{id}', ['as' => 'add_income_edit', 'uses' => 'Admin\Accounts\AramiscAddIncomeController@edit'])->middleware('userRolePermission:add_income_edit');
        Route::post('add-income-update', ['as' => 'add_income_update', 'uses' => 'Admin\Accounts\AramiscAddIncomeController@update'])->middleware('userRolePermission:add_income_edit');
        Route::post('add-income-delete', ['as' => 'add_income_delete', 'uses' => 'Admin\Accounts\AramiscAddIncomeController@delete'])->middleware('userRolePermission:add_income_delete');
        Route::get('download-income-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/add_income/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-income-document');


        // Profit of account
        Route::get('profit', ['as' => 'profit', 'uses' => 'Admin\Accounts\AramiscAccountsController@profit'])->middleware('userRolePermission:profit');
        Route::post('search-profit-by-date', ['as' => 'search_profit_by_dates', 'uses' => 'Admin\Accounts\AramiscAccountsController@searchProfitByDate']);
        Route::get('search-profit-by-date', ['as' => 'search_profit_by_date', 'uses' => 'Admin\Accounts\AramiscAccountsController@profit']);

        // Student Type Routes
        Route::get('student-category', ['as' => 'student_category', 'uses' => 'Admin\StudentInfo\AramiscStudentCategoryController@index'])->middleware('userRolePermission:student_category');
        Route::post('student-category-store', ['as' => 'student_category_store', 'uses' => 'Admin\StudentInfo\AramiscStudentCategoryController@store'])->middleware('userRolePermission:student_category_store');
        Route::get('student-category-edit/{id}', ['as' => 'student_category_edit', 'uses' => 'Admin\StudentInfo\AramiscStudentCategoryController@edit'])->middleware('userRolePermission:student_category_edit');
        Route::post('student-category-update', ['as' => 'student_category_update', 'uses' => 'Admin\StudentInfo\AramiscStudentCategoryController@update'])->middleware('userRolePermission:student_category_edit');
        Route::get('student-category-delete/{id}', ['as' => 'student_category_delete', 'uses' => 'Admin\StudentInfo\AramiscStudentCategoryController@delete'])->middleware('userRolePermission:student_category_delete');

        // Student Group Routes
        Route::get('student-group', ['as' => 'student_group', 'uses' => 'Admin\StudentInfo\AramiscStudentGroupController@index'])->middleware('userRolePermission:student_group');
        Route::post('student-group-store', ['as' => 'student_group_store', 'uses' => 'Admin\StudentInfo\AramiscStudentGroupController@store'])->middleware('userRolePermission:student_group_store');
        Route::get('student-group-edit/{id}', ['as' => 'student_group_edit', 'uses' => 'Admin\StudentInfo\AramiscStudentGroupController@edit'])->middleware('userRolePermission:student_group_edit');
        Route::post('student-group-update', ['as' => 'student_group_update', 'uses' => 'Admin\StudentInfo\AramiscStudentGroupController@update'])->middleware('userRolePermission:student_group_edit');
        Route::get('student-group-delete/{id}', ['as' => 'student_group_delete', 'uses' => 'Admin\StudentInfo\AramiscStudentGroupController@delete'])->middleware('userRolePermission:student_group_delete');

        // Student Group Routes

        // Route::get('payment-method', ['as' => 'payment_method', 'uses' => 'AramiscPaymentMethodController@index'])->middleware('userRolePermission:payment_method');
        // Route::post('payment-method-store', ['as' => 'payment_method_store', 'uses' => 'AramiscPaymentMethodController@store'])->middleware('userRolePermission:153');
        // Route::get('payment-method-settings-edit/{id}', ['as' => 'payment_method_edit', 'uses' => 'AramiscPaymentMethodController@edit'])->middleware('userRolePermission:154');
        // Route::post('payment-method-update', ['as' => 'payment_method_update', 'uses' => 'AramiscPaymentMethodController@update'])->middleware('userRolePermission:154');
        // Route::get('delete-payment-method/{id}', ['as' => 'payment_method_delete', 'uses' => 'AramiscPaymentMethodController@delete'])->middleware('userRolePermission:155');


        //academic year
        // Route::resource('academic-year', 'Admin\SystemSettings\AramiscAcademicYearController')->middleware('userRolePermission:432');
        Route::get('academic-year', 'Admin\SystemSettings\AramiscAcademicYearController@index')->name('academic-year')->middleware('userRolePermission:academic-year');
        Route::post('academic-year', 'Admin\SystemSettings\AramiscAcademicYearController@store')->name('academic-years')->middleware('userRolePermission:academic-year-store');
        Route::get('academic-year/{id}', 'Admin\SystemSettings\AramiscAcademicYearController@show')->name('academic-year-edit')->middleware('userRolePermission:academic-year-edit');
        Route::put('academic-year/{id}', 'Admin\SystemSettings\AramiscAcademicYearController@update')->name('academic-year-update')->middleware('userRolePermission:academic-year-edit');
        Route::delete('academic-year/{id}', 'Admin\SystemSettings\AramiscAcademicYearController@destroy')->name('academic-year-delete')->middleware('userRolePermission:academic-year-delete');

        //Session
        Route::resource('session', 'AramiscSessionController');


        // exam
        Route::get('exam-reset', 'Admin\Examination\AramiscExamController@exam_reset');

        // Route::resource('exam', 'Admin\Examination\AramiscExamController')->middleware('userRolePermission:214');
        Route::get('exam', 'Admin\Examination\AramiscExamController@index')->name('exam')->middleware('userRolePermission:exam');
        Route::post('exam', 'Admin\Examination\AramiscExamController@store')->name('exam-store')->middleware('userRolePermission:exam-setup-store');
        Route::get('exam/{id}', 'Admin\Examination\AramiscExamController@show')->name('exam-edit')->middleware('userRolePermission:exam-edit');
        Route::put('exam/{id}', 'Admin\Examination\AramiscExamController@update')->name('exam-update')->middleware('userRolePermission:exam-edit');
        Route::delete('exam/{id}', 'Admin\Examination\AramiscExamController@destroy')->name('exam-delete')->middleware('userRolePermission:exam-delete');

        Route::get('exam-marks-setup/{id}', 'Admin\Examination\AramiscExamController@exam_setup')->name('exam-marks-setup')->where('id', '[0-9]+');
        Route::get('get-class-subjects', 'Admin\Examination\AramiscExamController@getClassSubjects');
        Route::get('subject-assign-check', 'Admin\Examination\AramiscExamController@subjectAssignCheck');

        // If 100% Mark Option is Enable
        Route::get('custom-marksheet-report', 'Admin\Examination\AramiscExamController@customMarksheetReport')->name('custom-marksheet-report')->middleware('userRolePermission:custom-marksheet-report');
        Route::post('percent-marksheet-print', 'Admin\Examination\AramiscExaminationController@percentMarksheetPrint')->name('percent-marksheet-print')->middleware('userRolePermission:percent-marksheet-print');

        // Dormitory Module
        //Dormitory List
        // Route::resource('dormitory-list', 'Admin\Dormitory\AramiscDormitoryListController')->middleware('userRolePermission:367');
        Route::get('dormitory-list', 'Admin\Dormitory\AramiscDormitoryListController@index')->name('dormitory-list-index')->middleware('userRolePermission:dormitory-list-index');
        Route::post('dormitory-list', 'Admin\Dormitory\AramiscDormitoryListController@store')->name('dormitory-list-store')->middleware('userRolePermission:dormitory-list-store');
        Route::get('dormitory-list/{id}', 'Admin\Dormitory\AramiscDormitoryListController@show')->name('dormitory-list-edit');
        Route::put('dormitory-list/{id}', 'Admin\Dormitory\AramiscDormitoryListController@update')->name('dormitory-list-update');
        Route::delete('dormitory-list/{id}', 'Admin\Dormitory\AramiscDormitoryListController@destroy')->name('dormitory-list-delete');

        //Room Type
        // Route::resource('room-type', 'Admin\Dormitory\AramiscRoomTypeController@')->middleware('userRolePermission:371');
        Route::get('room-type', 'Admin\Dormitory\AramiscRoomTypeController@index')->name('room-type-index')->middleware('userRolePermission:room-type-index');
        Route::post('room-type', 'Admin\Dormitory\AramiscRoomTypeController@store')->name('room-type-store');
        Route::get('room-type/{id}', 'Admin\Dormitory\AramiscRoomTypeController@show')->name('room-type-edit');
        Route::put('room-type/{id}', 'Admin\Dormitory\AramiscRoomTypeController@update')->name('room-type-update');
        Route::delete('room-type/{id}', 'Admin\Dormitory\AramiscRoomTypeController@destroy')->name('room-type-delete');

        //Room Type
        // Route::resource('room-list', 'Admin\Dormitory\AramiscRoomListController')->middleware('userRolePermission:363');
        Route::get('room-list', 'Admin\Dormitory\AramiscRoomListController@index')->name('room-list-index')->middleware('userRolePermission:room-list-index');
        Route::post('room-list', 'Admin\Dormitory\AramiscRoomListController@store')->name('room-list-store')->middleware('userRolePermission:room-list-index');
        Route::get('room-list/{id}', 'Admin\Dormitory\AramiscRoomListController@show')->name('room-list-edit')->middleware('userRolePermission:room-list-index');
        Route::put('room-list/{id}', 'Admin\Dormitory\AramiscRoomListController@update')->name('room-list-update')->middleware('userRolePermission:room-list-index');
        Route::delete('room-list/{id}', 'Admin\Dormitory\AramiscRoomListController@destroy')->name('room-list-delete')->middleware('userRolePermission:room-list-index');

        // Student Dormitory Report
        Route::get('student-dormitory-report', ['as' => 'student_dormitory_report_index', 'uses' => 'Admin\Dormitory\AramiscDormitoryController@studentDormitoryReport'])->middleware('userRolePermission:student_dormitory_report');

        Route::post('student-dormitory-report', ['as' => 'student_dormitory_report_store', 'uses' => 'Admin\Dormitory\AramiscDormitoryController@studentDormitoryReportSearch']);


        // Transport Module Start
        //Vehicle
        // Route::resource('vehicle', 'Admin\Transport\AramiscVehicleController')->middleware('userRolePermission:353');
        Route::get('vehicle', 'Admin\Transport\AramiscVehicleController@index')->name('vehicle-index')->middleware('userRolePermission:vehicle-index');
        Route::post('vehicle', 'Admin\Transport\AramiscVehicleController@store')->name('vehicle-store');
        Route::get('vehicle/{id}', 'Admin\Transport\AramiscVehicleController@show')->name('vehicle-edit')->middleware('userRolePermission:vehicle-edit');
        Route::put('vehicle/{id}', 'Admin\Transport\AramiscVehicleController@update')->name('vehicle-update')->middleware('userRolePermission:vehicle-edit');
        Route::delete('vehicle/{id}', 'Admin\Transport\AramiscVehicleController@destroy')->name('vehicle-delete')->middleware('userRolePermission:vehicle-delete');

        //Assign Vehicle
        // Route::resource('assign-vehicle', 'Admin\Transport\AramiscAssignVehicleController')->middleware('userRolePermission:357');
        Route::get('assign-vehicle', 'Admin\Transport\AramiscAssignVehicleController@index')->name('assign-vehicle-index')->middleware('userRolePermission:assign-vehicle-index');
        Route::post('assign-vehicle', 'Admin\Transport\AramiscAssignVehicleController@store')->name('assign-vehicle-store');
        Route::get('assign-vehicle/{id}/edit', 'Admin\Transport\AramiscAssignVehicleController@edit')->name('assign-vehicle-edit')->middleware('userRolePermission:assign-vehicle-index');
        Route::put('assign-vehicle/{id}', 'Admin\Transport\AramiscAssignVehicleController@update')->name('assign-vehicle-update')->middleware('userRolePermission:assign-vehicle-index');
        // Route::delete('assign-vehicle/{id}', 'Admin\Transport\AramiscAssignVehicleController@delete')->name('assign-vehicle-delete')->middleware('userRolePermission:360');

        Route::post('assign-vehicle-delete', 'Admin\Transport\AramiscAssignVehicleController@delete')->name('assign-vehicle-delete')->middleware('userRolePermission:assign-vehicle-index');

        // student transport report
        Route::get('student-transport-report', ['as' => 'student_transport_report_index', 'uses' => 'Admin\Transport\AramiscTransportController@studentTransportReport'])->middleware('userRolePermission:student_transport_report');
        Route::post('student-transport-report', ['as' => 'student_transport_report_store', 'uses' => 'Admin\Transport\AramiscTransportController@studentTransportReportSearch']);

        // Route transport
        // Route::resource('transport-route', 'Admin\Transport\AramiscRouteController')->middleware('userRolePermission:349');
        Route::get('transport-route', 'Admin\Transport\AramiscRouteController@index')->name('transport-route-index')->middleware('userRolePermission:transport-route-index');
        Route::post('transport-route', 'Admin\Transport\AramiscRouteController@store')->name('transport-route-store');
        Route::get('transport-route/{id}', 'Admin\Transport\AramiscRouteController@show')->name('transport-route-edit')->middleware('userRolePermission:transport-route-edit');
        Route::put('transport-route/{id}', 'Admin\Transport\AramiscRouteController@update')->name('transport-route-update');
        Route::delete('transport-route/{id}', 'Admin\Transport\AramiscRouteController@destroy')->name('transport-route-delete')->middleware('userRolePermission:transport-route-delete');

        //// Examination
        // instruction Routes
        Route::get('instruction', 'AramiscInstructionController@index')->name('instruction');
        Route::post('instruction', 'AramiscInstructionController@store')->name('instruction-store');
        Route::get('instruction/{id}', 'AramiscInstructionController@show')->name('instruction-edit');
        Route::put('instruction/{id}', 'AramiscInstructionController@update')->name('instruction-update');
        Route::delete('instruction/{id}', 'AramiscInstructionController@destroy')->name('instruction-delete');

        // Question Level
        // Route::get('question-level', 'AramiscQuestionLevelController@index')->name('question-level');
        // Route::post('question-level', 'AramiscQuestionLevelController@store')->name('question-level');
        // Route::get('question-level/{id}', 'AramiscQuestionLevelController@show')->name('question-level-edit');
        // Route::put('question-level/{id}', 'AramiscQuestionLevelController@update')->name('question-level-update');
        // Route::delete('question-level/{id}', 'AramiscQuestionLevelController@destroy')->name('question-level-delete');

        // Question group
        // Route::resource('question-group', 'Admin\OnlineExam\AramiscQuestionGroupController')->middleware('userRolePermission:230');
        Route::get('question-group', 'Admin\OnlineExam\AramiscQuestionGroupController@index')->name('question-group')->middleware('userRolePermission:question-group');
        Route::post('question-group', 'Admin\OnlineExam\AramiscQuestionGroupController@store')->name('question-group-store')->middleware('userRolePermission:question-group-store');
        Route::get('question-group/{id}', 'Admin\OnlineExam\AramiscQuestionGroupController@show')->name('question-group-edit')->middleware('userRolePermission:question-group-edit');
        Route::put('question-group/{id}', 'Admin\OnlineExam\AramiscQuestionGroupController@update')->name('question-group-update')->middleware('userRolePermission:question-group-edit');
        Route::delete('question-group/{id}', 'Admin\OnlineExam\AramiscQuestionGroupController@destroy')->name('question-group-delete')->middleware('userRolePermission:question-group-delete');

        // Question bank
        // Route::resource('question-bank', 'AramiscQuestionBankController')->middleware('userRolePermission:234');
        Route::get('question-bank', 'Admin\OnlineExam\AramiscQuestionBankController@index')->name('question-bank')->middleware('userRolePermission:question-bank');
        Route::post('question-bank', 'Admin\OnlineExam\AramiscQuestionBankController@store')->name('question-bank-store')->middleware('userRolePermission:question-bank-store');
        Route::get('question-bank/{id}', 'Admin\OnlineExam\AramiscQuestionBankController@show')->name('question-bank-edit')->middleware('userRolePermission:question-bank-edit');
        Route::put('question-bank/{id}', 'Admin\OnlineExam\AramiscQuestionBankController@update')->name('question-bank-update')->middleware('userRolePermission:question-bank-edit');
        Route::delete('question-bank/{id}', 'Admin\OnlineExam\AramiscQuestionBankController@destroy')->name('question-bank-delete')->middleware('userRolePermission:question-bank-delete');


        // Marks Grade
        // Route::resource('marks-grade', 'Admin\Examination\AramiscMarksGradeController')->middleware('userRolePermission:225');
        Route::get('marks-grade', 'Admin\Examination\AramiscMarksGradeController@index')->name('marks-grade')->middleware('userRolePermission:marks-grade');
        Route::post('marks-grade', 'Admin\Examination\AramiscMarksGradeController@store')->name('marks-grade-store')->middleware('userRolePermission:marks-grade-store');
        Route::get('marks-grade/{id}', 'Admin\Examination\AramiscMarksGradeController@show')->name('marks-grade-edit')->middleware('userRolePermission:marks-grade-edit');
        Route::put('marks-grade/{id}', 'Admin\Examination\AramiscMarksGradeController@update')->name('marks-grade-update')->middleware('userRolePermission:marks-grade-edit');
        Route::delete('marks-grade/{id}', 'Admin\Examination\AramiscMarksGradeController@destroy')->name('marks-grade-delete')->middleware('userRolePermission:marks-grade-delete');


        // exam
        // Route::resource('exam', 'Admin\Examination\AramiscExamController');

        Route::get('exam-type', 'Admin\Examination\AramiscExaminationController@exam_type')->name('exam-type')->middleware('userRolePermission:exam-type');
        Route::get('exam-type-edit/{id}', ['as' => 'exam_type_edit', 'uses' => 'Admin\Examination\AramiscExaminationController@exam_type_edit'])->middleware('userRolePermission:exam_type_edit');
        Route::post('exam-type-store', ['as' => 'exam_type_store', 'uses' => 'Admin\Examination\AramiscExaminationController@exam_type_store'])->middleware('userRolePermission:exam_type_store');
        Route::post('exam-type-update', ['as' => 'exam_type_update', 'uses' => 'Admin\Examination\AramiscExaminationController@exam_type_update'])->middleware('userRolePermission:exam_type_edit');
        Route::get('exam-type-delete/{id}', ['as' => 'exam_type_delete', 'uses' => 'Admin\Examination\AramiscExaminationController@exam_type_delete'])->middleware('userRolePermission:exam_type_delete');


        Route::get('exam-setup/{id}', 'Admin\Examination\AramiscExamController@examSetup');
        Route::post('exam-setup-store', 'Admin\Examination\AramiscExamController@examSetupStore')->name('exam-setup-store');


        // exam
        // Route::resource('department', 'AramiscHumanDepartmentController')->middleware('userRolePermission:184');
        Route::get('department', 'Admin\Hr\AramiscHumanDepartmentController@index')->name('department')->middleware('userRolePermission:department');
        Route::post('department', 'Admin\Hr\AramiscHumanDepartmentController@store')->name('department-store')->middleware('userRolePermission:department-store');
        Route::get('department/{id}', 'Admin\Hr\AramiscHumanDepartmentController@show')->name('department-edit')->middleware('userRolePermission:department-edit');
        Route::put('department/{id}', 'Admin\Hr\AramiscHumanDepartmentController@update')->name('department-update')->middleware('userRolePermission:department-edit');
        Route::delete('department/{id}', 'Admin\Hr\AramiscHumanDepartmentController@destroy')->name('department-delete')->middleware('userRolePermission:department-delete');

        // Route::post('exam-schedule-store', ['as' => 'exam_schedule_store', 'uses' => 'Admin\Examination\AramiscExaminationController@examScheduleStore']);
        // Route::get('exam-schedule-store', ['as' => 'exam_schedule_store', 'uses' => 'Admin\Examination\AramiscExaminationController@examScheduleCreate']);

        //Exam Schedule
        Route::get('exam-schedule', ['as' => 'exam_schedule', 'uses' => 'Admin\Examination\AramiscExamRoutineController@examSchedule'])->middleware('userRolePermission:exam_schedule');

        Route::post('exam-schedule-report-search', ['as' => 'exam_schedule_report_search_new', 'uses' => 'Admin\Examination\AramiscExamRoutineController@examScheduleReportSearch']);

        Route::get('exam-schedule-report-search', ['as' => 'exam_schedule_report_search', 'uses' => 'Admin\Examination\AramiscExamRoutineController@examSchedule']);
        Route::get('exam-schedule/print/{exam_id}/{class_id}/{section_id}', ['as' => 'exam_schedule_print', 'uses' => 'Admin\Examination\AramiscExamRoutineController@examSchedulePrint']);
        Route::get('view-exam-schedule/{class_id}/{section_id}/{exam_id}', ['as' => 'view_exam_schedule', 'uses' => 'Admin\Examination\AramiscExaminationController@viewExamSchedule']);


        //Exam Schedule create
        Route::get('exam-schedule-create', ['as' => 'exam_schedule_create', 'uses' => 'Admin\Examination\AramiscExamRoutineController@examScheduleCreate'])->middleware('userRolePermission:exam_schedule_create');

        Route::post('exam-schedule-create', ['as' => 'exam_schedule_create_store', 'uses' => 'Admin\Examination\AramiscExamRoutineController@examScheduleSearch'])->middleware('userRolePermission:exam_schedule_store');


        Route::post('delete-exam-routine', 'AramiscExamRoutineController@deleteExamRoutine')->name('delete-exam-routine');/* delete exam rouitne for update =abunayem */





        Route::get('exam-routine-view/{class_id}/{section_id}/{exam_period_id}', 'Admin\Examination\AramiscExamRoutineController@examRoutineView');
        Route::get('exam-routine-print/{class_id}/{section_id}/{exam_period_id}', 'Admin\Examination\AramiscExamRoutineController@examRoutinePrint')->name('exam-routine-print');

        //view exam status
        Route::get('view-exam-status/{exam_id}', ['as' => 'view_exam_status', 'uses' => 'Admin\Examination\AramiscExaminationController@viewExamStatus']);

        // marks register
        Route::get('marks-register', ['as' => 'marks_register', 'uses' => 'Admin\Examination\AramiscExamMarkRegisterController@index']);
        Route::post('marks-register', ['as' => 'marks_register_search', 'uses' => 'Admin\Examination\AramiscExamMarkRegisterController@reportSearch']);

        Route::get('marks-register-create', ['as' => 'marks_register_create', 'uses' => 'Admin\Examination\AramiscExamMarkRegisterController@create']);

        Route::post('add-exam-routine-store', 'Admin\Examination\AramiscExamRoutineController@addExamRoutineStore')->name('add-exam-routine-store');

        Route::post('marks-register-create', ['as' => 'marks_register_create_search', 'uses' => 'Admin\Examination\AramiscExamMarkRegisterController@search']);

        Route::post('marks_register_store', ['as' => 'marks_register_store', 'uses' => 'Admin\Examination\AramiscExamMarkRegisterController@store']); 

        Route::get('exam-settings', ['as' => 'exam-settings', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@index'])->middleware('userRolePermission:exam-settings');
        Route::post('save-exam-content', ['as' => 'save-exam-content', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@store'])->middleware('userRolePermission:save-exam-content');
        Route::get('edit-exam-settings/{id}', ['as' => 'edit-exam-settings', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@edit'])->middleware('userRolePermission:edit-exam-settings');
        Route::post('update-exam-content', ['as' => 'update-exam-content', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@update'])->middleware('userRolePermission:update-exam-content');

        Route::get('delete-content/{id}', ['as' => 'delete-content', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@delete'])->middleware('userRolePermission:delete-content');

        Route::get('exam-report-position', ['as' => 'exam-report-position', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@examReportPosition']);
        Route::post('exam-report-position-store', ['as' => 'exam-report-position-store', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@examReportPositionStore']);

        Route::get('all-exam-report-position', ['as' => 'all-exam-report-position', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@allExamReportPosition']);
        Route::post('all-exam-report-position-store', ['as' => 'all-exam-report-position-store', 'uses' => 'Admin\Examination\AramiscExamFormatSettingsController@allExamReportPositionStore']);


        //Seat Plan
        Route::get('seat-plan', ['as' => 'seat_plan', 'uses' => 'Admin\Examination\AramiscExaminationController@seatPlan']);
        Route::post('seat-plan-report-search', ['as' => 'seat_plan_report_search_new', 'uses' => 'Admin\Examination\AramiscExaminationController@seatPlanReportSearch']);
        Route::get('seat-plan-report-search', ['as' => 'seat_plan_report_search', 'uses' => 'Admin\Examination\AramiscExaminationController@seatPlan']);

        Route::get('seat-plan-create', ['as' => 'seat_plan_create', 'uses' => 'Admin\Examination\AramiscExaminationController@seatPlanCreate']);

        Route::post('seat-plan-store', ['as' => 'seat_plan_store_create', 'uses' => 'Admin\Examination\AramiscExaminationController@seatPlanStore']);
        Route::get('seat-plan-store', ['as' => 'seat_plan_store', 'uses' => 'Admin\Examination\AramiscExaminationController@seatPlanCreate']);

        Route::post('seat-plan-search', ['as' => 'seat_plan_create_search', 'uses' => 'Admin\Examination\AramiscExaminationController@seatPlanSearch']);
        Route::get('seat-plan-search', ['as' => 'seat_plan_search', 'uses' => 'Admin\Examination\AramiscExaminationController@seatPlanCreate']);
        Route::get('assign-exam-room-get-by-ajax', ['as' => 'assign-exam-room-get-by-ajax', 'uses' => 'Admin\Examination\AramiscExaminationController@getExamRoomByAjax']);
        Route::get('get-room-capacity', ['as' => 'get-room-capacity', 'uses' => 'Admin\Examination\AramiscExaminationController@getRoomCapacity']);


        // Exam Attendance
        Route::get('exam-attendance', ['as' => 'exam_attendance', 'uses' => 'Admin\Examination\AramiscExaminationController@examAttendance']);
        Route::post('exam-attendance', ['as' => 'exam_attendance_search', 'uses' => 'Admin\Examination\AramiscExaminationController@examAttendanceAeportSearch']);


        Route::get('exam-attendance-create', ['as' => 'exam_attendance_create', 'uses' => 'Admin\Examination\AramiscExamAttendanceController@examAttendanceCreate']);
        Route::post('exam-attendance-create', ['as' => 'exam_attendance_create_search', 'uses' => 'Admin\Examination\AramiscExamAttendanceController@examAttendanceSearch']);

        Route::post('exam-attendance-store', 'Admin\Examination\AramiscExamAttendanceController@examAttendanceStore')->name('exam-attendance-store');
        // Send Marks By SmS
        Route::get('send-marks-by-sms', ['as' => 'send_marks_by_sms', 'uses' => 'Admin\Examination\AramiscExaminationController@sendMarksBySms'])->middleware('userRolePermission:send_marks_by_sms');
        Route::post('send-marks-by-sms-store', ['as' => 'send_marks_by_sms_store', 'uses' => 'Admin\Examination\AramiscExaminationController@sendMarksBySmsStore'])->middleware('userRolePermission:marks-grade-edit');


        // Online Exam
        // Route::resource('online-exam', 'Admin\OnlineExam\AramiscOnlineExamController')->middleware('userRolePermission:238');
        Route::get('online-exam', 'Admin\OnlineExam\AramiscOnlineExamController@index')->name('online-exam')->middleware('userRolePermission:online-exam');
        Route::post('online-exam', 'Admin\OnlineExam\AramiscOnlineExamController@store')->name('online-exam-store')->middleware('userRolePermission:online-exam-store');
        Route::get('online-exam/{id}', 'Admin\OnlineExam\AramiscOnlineExamController@edit')->name('online-exam-edit')->middleware('userRolePermission:online-exam-edit');
        Route::get('view-online-exam-question/{id}', 'Admin\OnlineExam\AramiscOnlineExamController@viewOnlineExam')->name('online-exam-question-view')->middleware('userRolePermission:online-exam');
        Route::put('online-exam/{id}', 'Admin\OnlineExam\AramiscOnlineExamController@update')->name('online-exam-update')->middleware('userRolePermission:online-exam-edit');
        // Route::delete('online-exam/{id}', 'Admin\OnlineExam\AramiscOnlineExamController@delete')->name('online-exam-delete')->middleware('userRolePermission:241');

        Route::post('online-exam-delete', 'Admin\OnlineExam\AramiscOnlineExamController@delete')->name('online-exam-delete')->middleware('userRolePermission:online-exam-delete');
        Route::get('manage-online-exam-question/{id}', ['as' => 'manage_online_exam_question', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@manageOnlineExamQuestion'])->middleware('userRolePermission:manage_online_exam_question');
        Route::post('online_exam_question_store', ['as' => 'online_exam_question_store', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@manageOnlineExamQuestionStore']);

        Route::get('online-exam-publish/{id}', ['as' => 'online_exam_publish', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamPublish']);
        Route::get('online-exam-publish-cancel/{id}', ['as' => 'online_exam_publish_cancel', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamPublishCancel']);

        Route::get('online-question-edit/{id}/{type}/{examId}', 'Admin\OnlineExam\AramiscOnlineExamController@onlineQuestionEdit');
        Route::post('online-exam-question-edit', ['as' => 'online_exam_question_edit', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamQuestionEdit']);
        Route::post('online-exam-question-delete', 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamQuestionDelete')->name('online-exam-question-delete');

        // store online exam question
        Route::post('online-exam-question-assign', ['as' => 'online_exam_question_assign', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamQuestionAssign']);

        Route::get('view_online_question_modal/{id}', ['as' => 'view_online_question_modal', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@viewOnlineQuestionModal']);


        // Online exam marks
        Route::get('online-exam-marks-register/{id}', ['as' => 'online_exam_marks_register', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamMarksRegister']);

        // Route::post('online-exam-marks-store', ['as' => 'online_exam_marks_store', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamMarksStore']);
        Route::get('online-exam-result/{id}', ['as' => 'online_exam_result', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamResult'])->middleware('userRolePermission:online_exam_result');

        Route::get('online-exam-marking/{exam_id}/{s_id}', ['as' => 'online_exam_marking', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamMarking']);
        Route::post('online-exam-marks-store', ['as' => 'online_exam_marks_store', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamMarkingStore']);

        Route::get('online-exam-datatable', ['as' => 'online_exam_datatable', 'uses' => 'Admin\OnlineExam\AramiscOnlineExamController@onlineExamDatatable']);

        // Staff Hourly rate
        // Route::get('hourly-rate', 'AramiscHourlyRateController@index')->name('hourly-rate');
        // Route::post('hourly-rate', 'AramiscHourlyRateController@store')->name('hourly-rate');
        // Route::get('hourly-rate', 'AramiscHourlyRateController@show')->name('hourly-rate');
        // Route::put('hourly-rate', 'AramiscHourlyRateController@update')->name('hourly-rate');
        // Route::delete('hourly-rate', 'AramiscHourlyRateController@destroy')->name('hourly-rate');

        // Staff leave type
        // Route::resource('leave-type', 'AramiscLeaveTypeController')->middleware('userRolePermission:203');
        Route::get('leave-type', 'Admin\Leave\AramiscLeaveTypeController@index')->name('leave-type')->middleware('userRolePermission:leave-type');
        Route::post('leave-type', 'Admin\Leave\AramiscLeaveTypeController@store')->name('leave-type-store')->middleware('userRolePermission:leave-type-store');
        Route::get('leave-type/{id}', 'Admin\Leave\AramiscLeaveTypeController@show')->name('leave-type-edit')->middleware('userRolePermission:leave-type-edit');
        Route::put('leave-type/{id}', 'Admin\Leave\AramiscLeaveTypeController@update')->name('leave-type-update')->middleware('userRolePermission:leave-type-edit');
        Route::delete('leave-type/{id}', 'Admin\Leave\AramiscLeaveTypeController@destroy')->name('leave-type-delete')->middleware('userRolePermission:leave-type-delete');

        // Staff leave define
        // Route::resource('leave-define', 'Admin\Leave\AramiscLeaveDefineController')->middleware('userRolePermission:199');
        Route::get('leave-define', 'Admin\Leave\AramiscLeaveDefineController@index')->name('leave-define')->middleware('userRolePermission:leave-define');
        Route::post('leave-define', 'Admin\Leave\AramiscLeaveDefineController@store')->name('leave-define-store');
        Route::get('leave-define/{id}', 'Admin\Leave\AramiscLeaveDefineController@show')->name('leave-define-edit')->middleware('userRolePermission:leave-define-edit');
        Route::put('leave-define/{id}', 'Admin\Leave\AramiscLeaveDefineController@update')->name('leave-define-update')->middleware('userRolePermission:leave-define-edit');
        Route::delete('leave-define', 'Admin\Leave\AramiscLeaveDefineController@destroy')->name('leave-define-delete')->middleware('userRolePermission:leave-define-delete');
        Route::post('leave-define-updateLeave', 'Admin\Leave\AramiscLeaveDefineController@updateLeave')->name('leave-define-updateLeave')->middleware('userRolePermission:leave-define-edit');

        Route::get('leave-define-ajax', 'DatatableQueryController@leaveDefineList')->name('leave-define-ajax')->middleware('userRolePermission:leave-define');

        // Staff leave define
        // Route::resource('apply-leave', 'AramiscLeaveRequestController')->middleware('userRolePermission:193');
        Route::get('apply-leave', 'Admin\Leave\AramiscLeaveRequestController@index')->name('apply-leave')->middleware('userRolePermission:apply-leave');
        Route::post('apply-leave', 'Admin\Leave\AramiscLeaveRequestController@store')->name('apply-leave-store')->middleware('userRolePermission:apply-leave-store');
        Route::get('apply-leave/{id}', 'Admin\Leave\AramiscLeaveRequestController@show')->name('apply-leave-edit')->middleware('userRolePermission:apply-leave-edit');
        Route::put('apply-leave/{id}', 'Admin\Leave\AramiscLeaveRequestController@update')->name('apply-leave-update')->middleware('userRolePermission:apply-leave-edit');
        Route::delete('apply-leave/{id}', 'Admin\Leave\AramiscLeaveRequestController@destroy')->name('apply-leave-delete')->middleware('userRolePermission:apply-leave-delete');
        Route::post('apply-leave-delte', 'Admin\Leave\AramiscLeaveRequestController@deleteLeave')->name('delete-apply-leave')->middleware('userRolePermission:apply-leave-delete');


        // Route::resource('approve-leave', 'Admin\Leave\AramiscApproveLeaveController')->middleware('userRolePermission:189');
        Route::get('approve-leave', 'Admin\Leave\AramiscApproveLeaveController@index')->name('approve-leave')->middleware('userRolePermission:approve-leave');
        // Route::post('approve-leave', 'Admin\Leave\AramiscApproveLeaveController@store')->name('approve-leave');
        Route::get('approve-leave/{id}', 'Admin\Leave\AramiscApproveLeaveController@show')->name('approve-leave-edit');
        Route::put('approve-leave/{id}', 'Admin\Leave\AramiscApproveLeaveController@update')->name('approve-leave-update');
        Route::delete('approve-leave/{id}', 'Admin\Leave\AramiscApproveLeaveController@destroy')->name('approve-leave-delete')->middleware('userRolePermission:approve-leave-delete');

        Route::get('pending-leave', 'Admin\Leave\AramiscApproveLeaveController@pendingLeave')->name('pending-leave')->middleware('userRolePermission:pending-leave');

        Route::post('update-approve-leave', 'Admin\Leave\AramiscApproveLeaveController@updateApproveLeave')->name('update-approve-leave');

        Route::get('/staffNameByRole', 'Admin\Leave\AramiscApproveLeaveController@staffNameByRole');

        Route::get('view-leave-details-approve/{id}', 'Admin\Leave\AramiscApproveLeaveController@viewLeaveDetails')->name('view-leave-details-approve')->middleware('userRolePermission:approve-leave-edit');


        // Staff designation
        // Route::resource('designation', 'AramiscDesignationController')->middleware('userRolePermission:180');
        Route::get('designation', 'Admin\Hr\AramiscDesignationController@index')->name('designation')->middleware('userRolePermission:designation');
        Route::post('designation', 'Admin\Hr\AramiscDesignationController@store')->name('designation-store')->middleware('userRolePermission:designation-store');
        Route::get('designation/{id}', 'Admin\Hr\AramiscDesignationController@show')->name('designation-edit')->middleware('userRolePermission:designation-edit');
        Route::put('designation/{id}', 'Admin\Hr\AramiscDesignationController@update')->name('designation-update')->middleware('userRolePermission:designation-edit');
        Route::delete('designation/{id}', 'Admin\Hr\AramiscDesignationController@destroy')->name('designation-delete')->middleware('userRolePermission:designation-delete');


        // Bank Account
        // Route::resource('bank-account', 'Admin\Accounts\AramiscBankAccountController')->middleware('userRolePermission:156');
        Route::get('bank-account', 'Admin\Accounts\AramiscBankAccountController@index')->name('bank-account')->middleware('userRolePermission:bank-account');
        Route::post('bank-account', 'Admin\Accounts\AramiscBankAccountController@store')->name('bank-account-store')->middleware('userRolePermission:bank-account-store');
        Route::get('bank-account/{id}', 'Admin\Accounts\AramiscBankAccountController@show')->name('bank-account-edit');
        Route::put('bank-account/{id}', 'Admin\Accounts\AramiscBankAccountController@update')->name('bank-account-update');
        Route::get('bank-transaction/{id}', 'Admin\Accounts\AramiscBankAccountController@bankTransaction')->name('bank-transaction')->middleware('userRolePermission:bank-transaction');
        Route::delete('bank-account-delete', 'Admin\Accounts\AramiscBankAccountController@destroy')->name('bank-account-delete')->middleware('userRolePermission:bank-account-delete');
        Route::get('bank-account-datatable', 'Admin\Accounts\AramiscBankAccountController@bankAccountDatatable')->name('bank-account-datatable');

        // Expense head
        // Route::resource('expense-head', 'AramiscExpenseHeadController');   //not used 

        // Chart Of Account
        // Route::resource('chart-of-account', 'AramiscChartOfAccountController')->middleware('userRolePermission:148');
        Route::get('chart-of-account', 'Admin\Accounts\AramiscChartOfAccountController@index')->name('chart-of-account')->middleware('userRolePermission:chart-of-account');
        Route::post('chart-of-account', 'Admin\Accounts\AramiscChartOfAccountController@store')->name('chart-of-account-store')->middleware('userRolePermission:chart-of-account-store');
        Route::get('chart-of-account/{id}', 'Admin\Accounts\AramiscChartOfAccountController@show')->name('chart-of-account-edit')->middleware('userRolePermission:chart-of-account-edit');
        Route::put('chart-of-account/{id}', 'Admin\Accounts\AramiscChartOfAccountController@update')->name('chart-of-account-update')->middleware('userRolePermission:chart-of-account-edit');
        Route::delete('chart-of-account/{id}', 'Admin\Accounts\AramiscChartOfAccountController@destroy')->name('chart-of-account-delete')->middleware('userRolePermission:chart-of-account-delete');

        // Add Expense
        // Route::resource('add-expense', 'Admin\Accounts\AramiscAddExpenseController')->middleware('userRolePermission:143');
        Route::get('add-expense', 'Admin\Accounts\AramiscAddExpenseController@index')->name('add-expense')->middleware('userRolePermission:add-expense');
        Route::post('add-expense', 'Admin\Accounts\AramiscAddExpenseController@store')->name('add-expense-store')->middleware('userRolePermission:add-expense-store');
        Route::get('add-expense/{id}', 'Admin\Accounts\AramiscAddExpenseController@show')->name('add-expense-edit')->middleware('userRolePermission:add-expense-edit');
        Route::put('add-expense/{id}', 'Admin\Accounts\AramiscAddExpenseController@update')->name('add-expense-update')->middleware('userRolePermission:add-expense-edit');
        Route::post('add-expense-delete', 'Admin\Accounts\AramiscAddExpenseController@destroy')->name('add-expense-delete')->middleware('userRolePermission:add-expense-delete');
        Route::get('download-expense-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/addExpense/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-expense-document');
        // Fees Master
        // Route::resource('fees-master', 'Admin\FeesCollection\AramiscFeesMasterController')->middleware('userRolePermission:118');
        Route::get('fees-master', 'Admin\FeesCollection\AramiscFeesMasterController@index')->name('fees-master')->middleware('userRolePermission:fees-master');
        Route::post('fees-master', 'Admin\FeesCollection\AramiscFeesMasterController@store')->name('fees-master-store')->middleware('userRolePermission:fees-master-store');
        Route::get('fees-master/{id}', 'Admin\FeesCollection\AramiscFeesMasterController@show')->name('fees-master-edit')->middleware('userRolePermission:fees-master-edit');
        Route::put('fees-master/{id}', 'Admin\FeesCollection\AramiscFeesMasterController@update')->name('fees-master-update')->middleware('userRolePermission:fees-master-edit');
        Route::delete('fees-master/{id}', 'Admin\FeesCollection\AramiscFeesMasterController@destroy')->name('fees-master-delete')->middleware('userRolePermission:fees-master-delete');

        Route::post('fees-master-single-delete', 'Admin\FeesCollection\AramiscFeesMasterController@deleteSingle')->name('fees-master-single-delete')->middleware('userRolePermission:fees-master-delete');
        Route::post('fees-master-group-delete', 'Admin\FeesCollection\AramiscFeesMasterController@deleteGroup')->name('fees-master-group-delete');
        Route::get('fees-assign/{id}', ['as' => 'fees_assign', 'uses' => 'Admin\FeesCollection\AramiscFeesMasterController@feesAssign']);

        Route::post('fees-assign-search', 'Admin\FeesCollection\AramiscFeesMasterController@feesAssignSearch')->name('fees-assign-search');

        Route::post('btn-assign-fees-group', 'Admin\FeesCollection\AramiscFeesMasterController@feesAssignStore');
        Route::post('unssign-all-fees-group', 'Admin\FeesCollection\AramiscFeesMasterController@feesUnassignAll');

        Route::get('fees-assign-datatable', 'Admin\FeesCollection\AramiscFeesMasterController@feesAssignDatatable')->name('fees-assign-datatable');

        //installment
        Route::post('fees-installment-update', 'Admin\FeesCollection\AramiscFeesMasterController@feesInstallmentUpdate')->name('feesInstallmentUpdate');

        // Complaint
        // Route::resource('complaint', 'AramiscComplaintController')->middleware('userRolePermission:21'); 
        Route::get('complaint', 'Admin\AdminSection\AramiscComplaintController@index')->name('complaint')->middleware('userRolePermission:complaint');
        Route::post('complaint', 'Admin\AdminSection\AramiscComplaintController@store')->name('complaint_store')->middleware('userRolePermission:complaint_store');
        Route::get('complaint/{id}', 'Admin\AdminSection\AramiscComplaintController@show')->name('complaint_show')->middleware('userRolePermission:complaint_show');
        Route::get('complaint/{id}/edit', 'Admin\AdminSection\AramiscComplaintController@edit')->name('complaint_edit')->middleware('userRolePermission:complaint_edit');
        Route::put('complaint/{id}', 'Admin\AdminSection\AramiscComplaintController@update')->name('complaint_update')->middleware('userRolePermission:complaint_edit');
        Route::post('delete-complaint', 'Admin\AdminSection\AramiscComplaintController@destroy')->name('complaint_delete')->middleware('userRolePermission:complaint_delete');

        Route::get('download-complaint-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/complaint/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-complaint-document')->middleware('userRolePermission:25');


        // Complaint

        Route::get('setup-admin', 'Admin\AdminSection\AramiscSetupAdminController@index')->name('setup-admin')->middleware('userRolePermission:setup-admin');
        Route::post('setup-admin', 'Admin\AdminSection\AramiscSetupAdminController@store')->name('setup-admin-store')->middleware('userRolePermission:setup-admin-store');
        Route::get('setup-admin/{id}', 'Admin\AdminSection\AramiscSetupAdminController@show')->name('setup-admin-edit')->middleware('userRolePermission:setup-admin-edit');
        Route::put('setup-admin/{id}', 'Admin\AdminSection\AramiscSetupAdminController@update')->name('setup-admin-update')->middleware('userRolePermission:setup-admin-edit');
        Route::get('setup-admin-delete/{id}', 'Admin\AdminSection\AramiscSetupAdminController@destroy')->name('setup-admin-delete')->middleware('userRolePermission:setup-admin-delete');


        // Postal Receive
        // Route::resource('postal-receive', 'AramiscPostalReceiveController');
        Route::get('postal-receive', 'Admin\AdminSection\AramiscPostalReceiveController@index')->name('postal-receive')->middleware('userRolePermission:postal-receive');
        Route::post('postal-receive', 'Admin\AdminSection\AramiscPostalReceiveController@store')->name('postal-receive-store')->middleware('userRolePermission:postal-receive-store');
        Route::get('postal-receive/{id}', 'Admin\AdminSection\AramiscPostalReceiveController@show')->name('postal-receive_edit')->middleware('userRolePermission:postal-receive_edit');
        Route::put('postal-receive/{id}', 'Admin\AdminSection\AramiscPostalReceiveController@update')->name('postal-receive_update')->middleware('userRolePermission:postal-receive_edit');
        Route::post('postal-receive-delete', 'Admin\AdminSection\AramiscPostalReceiveController@destroy')->name('postal-receive_delete')->middleware('userRolePermission:postal-receive_delete');

        Route::get('postal-receive-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/postal/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('postal-receive-document')->middleware('userRolePermission:postal-receive-document');


        Route::get('postal-receive-datatable', 'Admin\AdminSection\AramiscPostalReceiveController@postalReceiveDatatable')->name('postal-receive-datatable');

        // Postal Dispatch
        // Route::resource('postal-dispatch', 'AramiscPostalDispatchController');
        Route::get('postal-dispatch', 'Admin\AdminSection\AramiscPostalDispatchController@index')->name('postal-dispatch')->middleware('userRolePermission:postal-dispatch');
        Route::post('postal-dispatch', 'Admin\AdminSection\AramiscPostalDispatchController@store')->name('postal-dispatch-store')->middleware('userRolePermission:postal-dispatch-store');
        Route::get('postal-dispatch/{id}', 'Admin\AdminSection\AramiscPostalDispatchController@show')->name('postal-dispatch_edit')->middleware('userRolePermission:postal-dispatch_edit');
        Route::put('postal-dispatch/{id}', 'Admin\AdminSection\AramiscPostalDispatchController@update')->name('postal-dispatch_update')->middleware('userRolePermission:postal-dispatch_edit');
        Route::post('postal-dispatch-delete', 'Admin\AdminSection\AramiscPostalDispatchController@destroy')->name('postal-dispatch_delete')->middleware('userRolePermission:postal-dispatch_delete');

        Route::get('postal-dispatch-document/{file_name}', function ($file_name = null) {

            $file = public_path() . '/uploads/postal/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            } else {
                redirect()->back();
            }
        })->name('postal-dispatch-document')->middleware('userRolePermission:postal-dispatch-document');

        Route::get('postal-dispatch-datatable', 'Admin\AdminSection\AramiscPostalDispatchController@postalDispatchDatatable')->name('postal_dispatch_datatable');

        // Phone Call Log
        // Route::resource('phone-call', 'AramiscPhoneCallLogController');
        Route::get('phone-call', 'Admin\AdminSection\AramiscPhoneCallLogController@index')->name('phone-call')->middleware('userRolePermission:phone-call');
        Route::post('phone-call', 'Admin\AdminSection\AramiscPhoneCallLogController@store')->name('phone-call-store')->middleware('userRolePermission:phone-call-store');
        Route::get('phone-call/{id}', 'Admin\AdminSection\AramiscPhoneCallLogController@show')->name('phone-call_edit')->middleware('userRolePermission:phone-call_edit');
        Route::put('phone-call/{id}', 'Admin\AdminSection\AramiscPhoneCallLogController@update')->name('phone-call_update')->middleware('userRolePermission:phone-call_edit');
        Route::delete('phone-call-delete', 'Admin\AdminSection\AramiscPhoneCallLogController@destroy')->name('phone-call_delete')->middleware('userRolePermission:phone-call_delete');
        Route::get('phone-call-datatable', 'Admin\AdminSection\AramiscPhoneCallLogController@phoneCallDatatable')->name('phone-call-datatable');

        // Student Certificate
        // Route::resource('student-certificate', 'AramiscStudentCertificateController');
        Route::get('student-certificate', 'Admin\AdminSection\AramiscStudentCertificateController@index')->name('student-certificate')->middleware('userRolePermission:student-certificate');
        Route::post('student-certificate', 'Admin\AdminSection\AramiscStudentCertificateController@store')->name('student-certificate-store')->middleware('userRolePermission:student-certificate-store');
        Route::get('student-certificate/{id}', 'Admin\AdminSection\AramiscStudentCertificateController@edit')->name('student-certificate-edit')->middleware('userRolePermission:student-certificate-edit');
        Route::put('student-certificate/{id}', 'Admin\AdminSection\AramiscStudentCertificateController@update')->name('student-certificate-update')->middleware('userRolePermission:student-certificate-edit');
        Route::delete('student-certificate/{id}', 'Admin\AdminSection\AramiscStudentCertificateController@destroy')->name('student-certificate-delete')->middleware('userRolePermission:student-certificate-delete');

        // Generate certificate
        Route::get('generate-certificate', ['as' => 'generate_certificate', 'uses' => 'Admin\AdminSection\AramiscStudentCertificateController@generateCertificate'])->middleware('userRolePermission:generate_certificate');
        Route::post('generate-certificate', ['as' => 'generate_certificate_search', 'uses' => 'Admin\AdminSection\AramiscStudentCertificateController@generateCertificateSearch'])->middleware('userRolePermission:generate_certificate');
        // print certificate
        Route::get('generate-certificate-print/{s_id}/{c_id}', ['as' => 'student_certificate_generate', 'uses' => 'Admin\AdminSection\AramiscStudentCertificateController@generateCertificateGenerate']);

        Route::get('class-routine', ['as' => 'class_routine', 'uses' => 'Admin\Academics\AramiscClassRoutineNewController@classRoutine'])->middleware('userRolePermission:class_routine');


        // Student Certificate
        //Route::get('certificate', 'Admin\AdminSection\AramiscStudentCertificateController@index')->name('certificate')->middleware('userRolePermission:49');
        //Route::get('create-certificate', 'Admin\AdminSection\AramiscStudentCertificateController@createCertificate')->name('create-certificate');
        //Route::post('student-certificate-store', 'Admin\AdminSection\AramiscStudentCertificateController@store')->name('student-certificate-store')->middleware('userRolePermission:50');
        //Route::get('student-certificate-edit/{id}', 'Admin\AdminSection\AramiscStudentCertificateController@edit')->name('student-certificate-edit')->middleware('userRolePermission:51');
        //Route::post('student-certificate-update', 'Admin\AdminSection\AramiscStudentCertificateController@update')->name('student-certificate-update')->middleware('userRolePermission:51');
        //Route::post('student-certificate-delete', 'Admin\AdminSection\AramiscStudentCertificateController@destroy')->name('student-certificate-delete')->middleware('userRolePermission:52');
        //Route::get('view-certificate/{id}', 'Admin\AdminSection\AramiscStudentCertificateController@viewCertificate')->name('view-certificate');


        // print certificate
        // Route::get('generate-certificate-print/{s_id}/{c_id}', ['as' => 'student_certificate_generate', 'uses' => 'Admin\AdminSection\AramiscStudentCertificateController@generateCertificateGenerate']);





        Route::get('class-routine-new', 'Admin\Academics\AramiscClassRoutineNewController@classRoutineSearch')->name('class_routine_new')->middleware('userRolePermission:add-new-class-routine-store');/* change method for class routine update ->abunayem */
        Route::post('day-wise-class-routine', 'Admin\Academics\AramiscClassRoutineNewController@dayWiseClassRoutine')->name('dayWise_class_routine');

        Route::get('print-teacher-routine/{teacher_id}', 'Admin\Academics\AramiscClassRoutineNewController@printTeacherRoutine')->name('print-teacher-routine');

        // Student ID Card
        // Route::resource('student-id-card', 'Admin\AdminSection\AramiscStudentIdCardController');

        Route::get('student-id-card', 'Admin\AdminSection\AramiscStudentIdCardController@index')->name('student-id-card')->middleware('userRolePermission:student-id-card');
        Route::get('create-id-card', 'Admin\AdminSection\AramiscStudentIdCardController@create_id_card')->name('create-id-card');
        Route::post('genaret-id-card-bulk', 'Admin\AdminSection\AramiscStudentIdCardController@generateIdCardBulk')->name('genaret-id-card-bulk');
        Route::post('store-id-card', 'Admin\AdminSection\AramiscStudentIdCardController@store')->name('store-id-card')->middleware('userRolePermission:create-id-card');
        Route::get('student-id-card/{id}', 'Admin\AdminSection\AramiscStudentIdCardController@edit')->name('student-id-card-edit')->middleware('userRolePermission:student-id-card-edit');
        Route::put('student-id-card/{id}', 'Admin\AdminSection\AramiscStudentIdCardController@update')->name('student-id-card-update')->middleware('userRolePermission:student-id-card-edit');
        Route::post('student-id-card', 'Admin\AdminSection\AramiscStudentIdCardController@destroy')->name('student-id-card-delete')->middleware('userRolePermission:student-id-card-delete');

        Route::get('generate-id-card', ['as' => 'generate_id_card', 'uses' => 'Admin\AdminSection\AramiscStudentIdCardController@generateIdCard'])->middleware('userRolePermission:generate_id_card');
        Route::post('generate-id-card-search', ['as' => 'generate_id_card_bulk_search', 'uses' => 'Admin\AdminSection\AramiscStudentIdCardController@generateIdCardBulk']);


        // Route::post('generate-id-card-search', ['as' => 'generate_id_card_search', 'uses' => 'Admin\AdminSection\AramiscStudentIdCardController@generateIdCardSearch']);
        Route::get('generate-id-card-search', ['as' => 'generate_id_card_search', 'uses' => 'Admin\AdminSection\AramiscStudentIdCardController@generateIdCard']);
        Route::get('generate-id-card-print/{s_id}/{c_id}', 'Admin\AdminSection\AramiscStudentIdCardController@generateIdCardPrint');



        // Student Module /Student Admission
        Route::get('student-admission', ['as' => 'student_admission', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@index'])->middleware('userRolePermission:student_admission');
        Route::get('student-admission-check/{id}', ['as' => 'student_admission_check', 'uses' => 'AramiscStudentAdmissionController@admissionCheck']);
        Route::get('student-admission-update-check/{val}/{id}', ['as' => 'student_admission_check_update', 'uses' => 'AramiscStudentAdmissionController@admissionCheckUpdate']);
        Route::post('student-admission-pic', ['as' => 'student_admission_pic', 'uses' => 'AramiscStudentAdmissionController@admissionPic']);

        // Ajax get vehicle
        Route::get('/academic-year-get-class', 'AramiscStudentAdmissionController@academicYearGetClass');

        // Ajax get vehicle


        // Ajax Section
        Route::get('/ajaxVehicleInfo', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxVehicleInfo');

        // Ajax Roll No
        Route::get('/ajax-get-roll-id', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxGetRollId');

        // Ajax Roll exist check
        Route::get('/ajax-get-roll-id-check', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxGetRollIdCheck');

        // Ajax Section
        Route::get('/ajaxSectionStudent', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxSectionStudent');

        // Ajax Subject
        Route::get('/ajaxSubjectFromClass', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxSubjectClass');

        Route::get('/ajaxSubjectFromExamType', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxSubjectFromExamType');

        // Ajax room details

        //ajax id card type

        Route::get('/ajaxIdCard', 'Admin\AdminSection\AramiscStudentIdCardController@ajaxIdCard');
        //student store
        Route::post('student-store', ['as' => 'student_store', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@store'])->middleware('userRolePermission:student_store');

        //Student details document

        Route::get('delete-document/{id}', ['as' => 'delete_document', 'uses' => 'AramiscStudentAdmissionController@deleteDocument'])->middleware('userRolePermission:delete_document');
        Route::post('upload-document', ['as' => 'upload_document', 'uses' => 'AramiscStudentAdmissionController@uploadDocument']);



        Route::get('download-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/student/document/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-document');





        // Student timeline upload
        Route::post('student-timeline-store', ['as' => 'student_timeline_store', 'uses' => 'AramiscStudentAdmissionController@studentTimelineStore']);
        //parent
        Route::get('parent-download-timeline-doc/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/student/timeline/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
            return redirect()->back();
        })->name('parent-download-timeline-doc');

        Route::get('delete-timeline/{id}', ['as' => 'delete_timeline', 'uses' => 'AramiscStudentAdmissionController@deleteTimeline']);


        //student import
        Route::get('import-student', ['as' => 'import_student', 'uses' => 'AramiscStudentAdmissionController@importStudent'])->middleware('userRolePermission:import_student');
        Route::get('download_student_file', ['as' => 'download_student_file', 'uses' => 'AramiscStudentAdmissionController@downloadStudentFile']);
        Route::post('student-bulk-store', ['as' => 'student_bulk_store', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@studentBulkStore']);

        //Ajax Sibling section
        Route::get('ajaxSectionSibling', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxSectionSibling');

        //Ajax Sibling info
        Route::get('ajaxSiblingInfo', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxSiblingInfo');

        //Ajax Sibling info detail
        Route::get('ajaxSiblingInfoDetail', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxSiblingInfoDetail');


        //Datatables
        Route::get('student-list-datatable', ['as' => 'student_list_datatable', 'uses' => 'DatatableQueryController@studentDetailsDatatable'])->middleware('userRolePermission:student_list');


        // student list
        Route::get('student-list', ['as' => 'student_list', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@studentDetails'])->middleware('userRolePermission:student_list');
        Route::get('student-settings', ['as' => 'student_settings', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@settings'])->middleware('userRolePermission:student_settings');
        Route::post('student/field/switch', ['as' => 'student_switch', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@statusUpdate']);
        Route::post('student/field/show', ['as' => 'student_show', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@studentFieldShow']);

        // parent list
        Route::get('parent-list', ['as' => 'parent-list', 'uses' => 'Admin\StudentInfo\AramiscStudentParentController@parentList'])->middleware('userRolePermission:parent-list');
        Route::get('parent-list-search', ['as' => 'parent-list-search', 'uses' => 'Admin\StudentInfo\AramiscStudentParentController@parentListSearch'])->middleware('userRolePermission:parent-list-search');

        // student search
        Route::post('student-list-search', 'DatatableQueryController@studentDetailsDatatable')->name('student-list-search');
        Route::post('ajax-student-list-search', 'DatatableQueryController@searchStudentList')->name('ajax-student-list-search');

        Route::get('student-list-search', 'AramiscStudentAdmissionController@studentDetails');

        //student list
        Route::get('student-view/{id}/{type?}', ['as' => 'student_view', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@view']);

        //student delete
        Route::post('student-delete', 'AramiscStudentAdmissionController@studentDelete')->name('student-delete');


        // student edit
        Route::get('student-edit/{id}', ['as' => 'student_edit', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@edit'])->middleware('userRolePermission:student_edit');
        // Student Update
        Route::post('student-update', ['as' => 'student_update', 'uses' => 'Admin\StudentInfo\AramiscStudentAdmissionController@update']);
        // Route::post('student-update-pic/{id}', ['as' => 'student_update_pic', 'uses' => 'AramiscStudentAdmissionController@studentUpdatePic']);

        // Student Promote search
        // Route::get('student-promote', ['as' => 'student_promote', 'uses' => 'AramiscStudentAdmissionController@studentPromote'])->middleware('userRolePermission:81');

        // Route::get('student-current-search', 'AramiscStudentAdmissionController@studentPromote');
        // Route::post('student-current-search', 'AramiscStudentAdmissionController@studentCurrentSearch')->name('student-current-search');

        // Route::get('student-current-search-custom', 'AramiscStudentAdmissionController@studentPromoteCustom');
        // Route::post('student-current-search-custom', 'AramiscStudentAdmissionController@studentCurrentSearchCustom')->name('student-current-search-custom');

        Route::get('view-academic-performance/{id}', 'AramiscStudentAdmissionController@view_academic_performance');


        // // Student Promote Store
        // Route::get('student-promote-store', 'AramiscStudentAdmissionController@studentPromote');
        // Route::post('student-proadminmote-store', 'AramiscStudentAdmissionController@studentPromoteStore')->name('student-promote-store')->middleware('userRolePermission:82');

        Route::get('student-promote', ['as' => 'student_promote', 'uses' => 'AramiscStudentPromoteController@index'])->middleware('userRolePermission:student_promote');
        Route::get('student-current-search', 'AramiscStudentPromoteController@studentCurrentSearch')->name('student-current-search');
        Route::post('student-current-search', 'AramiscStudentPromoteController@studentCurrentSearch');
        Route::get('ajaxStudentRollCheck', 'AramiscStudentPromoteController@rollCheck');
        Route::post('student-promote-store', 'AramiscStudentPromoteController@promote')->name('student-promote-store')->middleware('userRolePermission:student-promote-store');
        Route::get('student-current-search-with-exam', 'AramiscStudentPromoteController@studentSearchWithExam')->name('student-current-search-with-exam');


        // // Student Promote Store Custom
        Route::get('student-promote-store-custom', 'AramiscStudentAdmissionController@studentPromoteCustom');
        Route::post('student-promote-store-custom', 'AramiscStudentAdmissionController@studentPromoteCustomStore')->name('student-promote-store-custom')->middleware('userRolePermission:student-promote-store');

        // Student Export
        Route::get('all-student-export', 'AramiscStudentAdmissionController@allStudentExport')->name('all-student-export')->middleware('userRolePermission:all-student-export');
        Route::get('all-student-export-excel', 'AramiscStudentAdmissionController@allStudentExportExcel')->name('all-student-export-excel')->middleware('userRolePermission:all-student-export-excel');
        Route::get('all-student-export-pdf', 'AramiscStudentAdmissionController@allStudentExportPdf')->name('all-student-export-pdf')->middleware('userRolePermission:all-student-export-pdf');


        //Ajax Student Promote Section
        Route::get('ajaxStudentPromoteSection', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxStudentPromoteSection');
        Route::get('ajaxSubjectSection', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxSubjectSection');
        Route::get('ajax-get-class', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxGetClass');
        Route::get('SearchMultipleSection', 'AramiscStudentAdmissionController@SearchMultipleSection');
        //Ajax Student Select
        Route::get('ajaxSelectStudent', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxSelectStudent');

        Route::get('promote-year/{id?}', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxPromoteYear');

        // Student Attendance
        Route::get('student-attendance', ['as' => 'student_attendance', 'uses' => 'Admin\StudentInfo\AramiscStudentAttendanceController@index'])->middleware('userRolePermission:student_attendance');
        Route::post('student-search', 'Admin\StudentInfo\AramiscStudentAttendanceController@studentSearch')->name('student-search');
        Route::any('ajax-student-attendance-search/{class_id}/{section}/{date}', 'DatatableQueryController@AjaxStudentSearch');
        Route::get('student-search', 'Admin\StudentInfo\AramiscStudentAttendanceController@index');

        Route::post('student-attendance-store', 'Admin\StudentInfo\AramiscStudentAttendanceController@studentAttendanceStore')->name('student-attendance-store')->middleware('userRolePermission:student-attendance-store');
        Route::post('student-attendance-holiday', 'Admin\StudentInfo\AramiscStudentAttendanceController@studentAttendanceHoliday')->name('student-attendance-holiday');


        Route::get('student-attendance-import', 'Admin\StudentInfo\AramiscStudentAttendanceController@studentAttendanceImport')->name('student-attendance-import');
        Route::get('download-student-attendance-file', 'Admin\StudentInfo\AramiscStudentAttendanceController@downloadStudentAtendanceFile');
        Route::post('student-attendance-bulk-store', 'Admin\StudentInfo\AramiscStudentAttendanceController@studentAttendanceBulkStore')->name('student-attendance-bulk-store');

        //Student Report
        Route::get('student-report', ['as' => 'student_report', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@studentReport'])->middleware('userRolePermission:student_report');
        Route::post('student-report', ['as' => 'student_report_search', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@studentReportSearch']);


        //guardian report
        Route::get('guardian-report', ['as' => 'guardian_report', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@guardianReport'])->middleware('userRolePermission:guardian_report');
        Route::post('guardian-report-search', ['as' => 'guardian_report_search_new', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@guardianReportSearch']);
        Route::get('guardian-report-search', ['as' => 'guardian_report_search', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@guardianReport']);

        Route::get('student-history', ['as' => 'student_history', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@studentHistory'])->middleware('userRolePermission:student_history');
        Route::post('student-history-search', ['as' => 'student_history_search_new', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@studentHistorySearch']);
        Route::get('student-history-search', ['as' => 'student_history_search', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@studentHistory']);


        // student login report
        Route::get('student-login-report', ['as' => 'student_login_report', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@studentLoginReport'])->middleware('userRolePermission:student_login_report');
        Route::post('student-login-search', ['as' => 'student_login_report_search', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@studentLoginSearch']);
        Route::get('student-login-search', ['as' => 'student_login_search', 'uses' => 'Admin\StudentInfo\AramiscStudentReportController@studentLoginReport']);

        // student & parent reset password
        Route::post('reset-student-password', 'Admin\RolePermission\AramiscResetPasswordController@resetStudentPassword')->name('reset-student-password');


        // Disabled Student
        Route::get('disabled-student', ['as' => 'disabled_student', 'uses' => 'AramiscStudentAdmissionController@disabledStudent'])->middleware('userRolePermission:disabled_student');

        Route::post('disabled-student', ['as' => 'disabled_student_search', 'uses' => 'AramiscStudentAdmissionController@disabledStudentSearch']);
        Route::post('disabled-student-delete', ['as' => 'disable_student_delete', 'uses' => 'AramiscStudentAdmissionController@disabledStudentDelete'])->middleware('userRolePermission:disable_student_delete');
        Route::post('enable-student', ['as' => 'enable_student', 'uses' => 'AramiscStudentAdmissionController@enableStudent'])->middleware('userRolePermission:enable_student');


        Route::get('student-report-search', 'AramiscStudentAdmissionController@studentReport');

        Route::get('language-list', 'Admin\SystemSettings\LanguageController@index')->name('language-list')->middleware('userRolePermission:language-list');
        Route::get('language-list/{id}', 'Admin\SystemSettings\LanguageController@show')->name('language_edit')->middleware('userRolePermission:language_edit');
        Route::post('language-list/update', 'Admin\SystemSettings\LanguageController@update')->name('language_update')->middleware('userRolePermission:language_edit');
        Route::post('language-list/store', 'Admin\SystemSettings\LanguageController@store')->name('language_store')->middleware('userRolePermission:language_store');
        Route::get('language-delete/{id}', 'Admin\SystemSettings\LanguageController@destroy')->name('language_delete')->middleware('userRolePermission:language_delete');


        // Tabulation Sheet Report
        Route::get('tabulation-sheet-report', ['as' => 'tabulation_sheet_report', 'uses' => 'Admin\Report\AramiscReportController@tabulationSheetReport'])->middleware('userRolePermission:tabulation_sheet_report');
        Route::post('tabulation-sheet-report', ['as' => 'tabulation_sheet_report_search', 'uses' => 'Admin\Report\AramiscReportController@tabulationSheetReportSearch']);
        Route::post('tabulation-sheet/print', 'Admin\Report\AramiscReportController@tabulationSheetReportPrint')->name('tabulation-sheet/print');

        Route::get('optional-subject-setup/delete/{id}', 'Admin\SystemSettings\AramiscOptionalSubjectAssignController@optionalSetupDelete')->name('delete_optional_subject')->middleware('userRolePermission:delete_optional_subject');
        Route::get('optional-subject-setup/edit/{id}', 'Admin\SystemSettings\AramiscOptionalSubjectAssignController@optionalSetupEdit')->name('class_optional_edit')->middleware('userRolePermission:class_optional_edit');
        Route::get('optional-subject-setup', 'Admin\SystemSettings\AramiscOptionalSubjectAssignController@optionalSetup')->name('class_optional')->middleware('userRolePermission:class_optional');
        Route::post('optional-subject-setup', 'Admin\SystemSettings\AramiscOptionalSubjectAssignController@optionalSetupStore')->name('optional_subject_setup_post')->middleware('userRolePermission:optional_subject_setup_post');

        // progress card report
        Route::get('progress-card-report', ['as' => 'progress_card_report', 'uses' => 'Admin\Report\AramiscReportController@progressCardReport'])->middleware('userRolePermission:progress_card_report');
        Route::post('progress-card-report', ['as' => 'progress_card_report_search', 'uses' => 'Admin\Report\AramiscReportController@progressCardReportSearch']);

        Route::get('custom-progress-card-report-percent', ['as' => 'custom_progress_card_report_percent', 'uses' => 'Admin\Report\AramiscReportController@customProgressCardReport']);


        Route::post('progress-card/print', 'Admin\Report\AramiscReportController@progressCardPrint')->name('progress-card/print');


        // staff directory
        Route::get('staff-directory', ['as' => 'staff_directory', 'uses' => 'Admin\Hr\AramiscStaffController@staffList'])->middleware('userRolePermission:staff_directory');
        Route::get('staff-directory-ajax', ['as' => 'staff_directory_ajax', 'uses' => 'DatatableQueryController@getStaffList'])->middleware('userRolePermission:staff_directory');


        Route::post('search-staff', ['as' => 'searchStaff', 'uses' => 'Admin\Hr\AramiscStaffController@searchStaff']);
        Route::post('search-staff-ajax', ['as' => 'AjaxSearchStaff', 'uses' => 'DatatableQueryController@getStaffList']);

        Route::get('add-staff', ['as' => 'addStaff', 'uses' => 'Admin\Hr\AramiscStaffController@addStaff'])->middleware('userRolePermission:addStaff');
        Route::post('staff-store', ['as' => 'staffStore', 'uses' => 'Admin\Hr\AramiscStaffController@staffStore']);
        Route::post('staff-pic-store', ['as' => 'staffPicStore', 'uses' => 'Admin\Hr\AramiscStaffController@staffPicStore']);


        Route::get('edit-staff/{id}', ['as' => 'editStaff', 'uses' => 'Admin\Hr\AramiscStaffController@editStaff']);
        Route::post('update-staff', ['as' => 'staffUpdate', 'uses' => 'Admin\Hr\AramiscStaffController@staffUpdate']);
        Route::post('staff-profile-update/{id}', ['as' => 'staffProfileUpdate', 'uses' => 'Admin\Hr\AramiscStaffController@staffProfileUpdate']);

        // Route::get('staff-roles', ['as' => 'viewStaff', 'uses' => 'Admin\Hr\AramiscStaffController@staffRoles']);
        Route::get('view-staff/{id}', ['as' => 'viewStaff', 'uses' => 'Admin\Hr\AramiscStaffController@viewStaff']);
        Route::get('delete-staff-view/{id}', ['as' => 'deleteStaffView', 'uses' => 'Admin\Hr\AramiscStaffController@deleteStaffView']);

        Route::get('deleteStaff/{id}', 'Admin\Hr\AramiscStaffController@deleteStaff')->name('deleteStaff')->middleware('userRolePermission:deleteStaff');
        Route::post('delete-staff', 'Admin\Hr\AramiscStaffController@delete_staff')->name('delete_staff');
        Route::get('staff-settings', 'Admin\Hr\AramiscStaffController@settings')->name('staff_settings')->middleware('userRolePermission:staff_settings');
        Route::post('staff/field/switch', ['as' => 'staff_switch', 'uses' => 'Admin\Hr\AramiscStaffController@statusUpdate']);
        Route::post('teacher/field_view', ['as' => 'teacher_field_view', 'uses' => 'Admin\Hr\AramiscStaffController@teacherFieldView']);
        Route::get('staff-disable-enable', 'Admin\Hr\AramiscStaffController@staffDisableEnable')->name('staff-disable-enable');

        Route::get('upload-staff-documents/{id}', 'Admin\Hr\AramiscStaffController@uploadStaffDocuments');
        Route::post('save_upload_document', 'Admin\Hr\AramiscStaffController@saveUploadDocument')->name('save_upload_document');
        Route::get('download-staff-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/staff/document/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-staff-document');

        Route::get('download-staff-joining-letter/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/staff_joining_letter/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-staff-joining-letter');

        Route::get('download-resume/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/resume/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-resume');

        Route::get('download-other-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/others_documents/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-other-document');

        Route::get('download-staff-timeline-doc/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/staff/timeline/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-staff-timeline-doc');

        Route::get('delete-staff-document-view/{id}', 'Admin\Hr\AramiscStaffController@deleteStaffDocumentView')->name('delete-staff-document-view');
        Route::get('delete-staff-document/{id}', 'Admin\Hr\AramiscStaffController@deleteStaffDocument')->name('delete-staff-document');

        // staff timeline
        Route::get('add-staff-timeline/{id}', 'Admin\Hr\AramiscStaffController@addStaffTimeline');
        Route::post('staff_timeline_store', 'Admin\Hr\AramiscStaffController@storeStaffTimeline')->name('staff_timeline_store');
        Route::get('delete-staff-timeline-view/{id}', 'Admin\Hr\AramiscStaffController@deleteStaffTimelineView')->name('delete-staff-timeline-view');
        Route::get('delete-staff-timeline/{id}', 'Admin\Hr\AramiscStaffController@deleteStaffTimeline')->name('delete-staff-timeline');


        //Staff Attendance
        Route::get('staff-attendance', ['as' => 'staff_attendance', 'uses' => 'Admin\Hr\AramiscStaffAttendanceController@staffAttendance'])->middleware('userRolePermission:staff_attendance');
        Route::post('staff-attendance', 'Admin\Hr\AramiscStaffAttendanceController@staffAttendanceSearch')->name('staff-attendance-search');
        Route::post('staff-attendance-store', 'Admin\Hr\AramiscStaffAttendanceController@staffAttendanceStore')->name('staff-attendance-store')->middleware('userRolePermission:staff-attendance-store');
        Route::post('staff-holiday-store', 'Admin\Hr\AramiscStaffAttendanceController@staffHolidayStore')->name('staff-holiday-store')->middleware('userRolePermission:staff-holiday-store');

        Route::get('staff-attendance-report', ['as' => 'staff_attendance_report', 'uses' => 'Admin\Hr\AramiscStaffAttendanceController@staffAttendanceReport'])->middleware('userRolePermission:staff_attendance_report');
        Route::post('staff-attendance-report', ['as' => 'staff_attendance_report_search', 'uses' => 'Admin\Hr\AramiscStaffAttendanceController@staffAttendanceReportSearch']);

        Route::get('staff-attendance/print/{role_id}/{month}/{year}/', 'Admin\Hr\AramiscStaffAttendanceController@staffAttendancePrint')->name('staff-attendance/print');


        // Biometric attendance
        Route::post('attendance', 'Admin\Hr\AramiscStaffAttendanceController@attendanceData')->name('attendanceData');



        Route::get('staff-attendance-import', 'Admin\Hr\AramiscStaffAttendanceController@staffAttendanceImport')->name('staff-attendance-import');
        Route::get('download-staff-attendance-file', 'Admin\Hr\AramiscStaffAttendanceController@downloadStaffAttendanceFile');
        Route::post('staff-attendance-bulk-store', 'Admin\Hr\AramiscStaffAttendanceController@staffAttendanceBulkStore')->name('staff-attendance-bulk-store');

        //payroll
        Route::get('payroll', ['as' => 'payroll', 'uses' => 'Admin\Hr\AramiscPayrollController@index'])->middleware('userRolePermission:payroll');

        // Route::post('payroll', ['as' => 'payroll', 'uses' => 'Admin\Hr\AramiscPayrollController@searchStaffPayr'])->middleware('userRolePermission:payroll');

        Route::get('generate-Payroll/{id}/{month}/{year}', 'Admin\Hr\AramiscPayrollController@generatePayroll')->name('generate-Payroll')->middleware('userRolePermission:generate-Payroll');
        Route::post('save-payroll-data', ['as' => 'savePayrollData', 'uses' => 'Admin\Hr\AramiscPayrollController@savePayrollData'])->middleware('userRolePermission:savePayrollData');

        Route::get('pay-payroll/{id}/{role_id}', 'Admin\Hr\AramiscPayrollController@paymentPayroll')->name('pay-payroll')->middleware('userRolePermission:pay-payroll');
        Route::post('savePayrollPaymentData', ['as' => 'savePayrollPaymentData', 'uses' => 'Admin\Hr\AramiscPayrollController@savePayrollPaymentData']);
        Route::get('view-payslip/{id}', 'Admin\Hr\AramiscPayrollController@viewPayslip')->name('view-payslip')->middleware('userRolePermission:view-payslip');
        Route::get('print-payslip/{id}', 'Admin\Hr\AramiscPayrollController@printPayslip')->name('print-payslip');
        Route::get('view-payroll-payment/{id}', 'Admin\Hr\AramiscPayrollController@viewPayrollPayment')->name('view-payroll-payment');
        Route::post('delete-payroll-payment', 'Admin\Hr\AramiscPayrollController@deletePayrollPayment')->name('delete-payroll-payment');
        Route::get('print-payroll-payment/{id}', 'Admin\Hr\AramiscPayrollController@printPayrollPayment')->name('print-payroll-payment');

        //payroll Report
        Route::get('payroll-report', 'Admin\Hr\AramiscPayrollController@payrollReport')->name('payroll-report')->middleware('userRolePermission:payroll-report');
        // Route::post('search-payroll-report', ['as' => 'searchPayrollReport', 'uses' => 'Admin\Hr\AramiscPayrollController@searchPayrollReport']);
        Route::post('payroll-report', 'Admin\Hr\AramiscPayrollController@searchPayrollReport')->name('searchPayrollReport');

        //Homework
        Route::get('homework-list', ['as' => 'homework-list', 'uses' => 'Admin\Homework\AramiscHomeworkController@homeworkList'])->middleware('userRolePermission:homework-list');

        Route::post('homework-list', ['as' => 'homework-list-search', 'uses' => 'Admin\Homework\AramiscHomeworkController@searchHomework'])->middleware('userRolePermission:homework-list');
        Route::get('homework-edit/{id}', ['as' => 'homework_edit', 'uses' => 'Admin\Homework\AramiscHomeworkController@homeworkEdit'])->middleware('userRolePermission:homework_edit');
        Route::post('homework-update', ['as' => 'homework_update', 'uses' => 'Admin\Homework\AramiscHomeworkController@homeworkUpdate'])->middleware('userRolePermission:homework_edit');
        Route::get('homework-delete/{id}', ['as' => 'homework_delete', 'uses' => 'Admin\Homework\AramiscHomeworkController@homeworkDelete'])->middleware('userRolePermission:homework_delete');

        Route::post('homework-delete', ['as' => 'homework-delete', 'uses' => 'Admin\Homework\AramiscHomeworkController@deleteHomework'])->middleware('userRolePermission:homework_delete');
        Route::get('add-homeworks', ['as' => 'add-homeworks', 'uses' => 'Admin\Homework\AramiscHomeworkController@addHomework'])->middleware('userRolePermission:add-homeworks');
        Route::post('save-homework-data', ['as' => 'saveHomeworkData', 'uses' => 'Admin\Homework\AramiscHomeworkController@saveHomeworkData'])->middleware('userRolePermission:saveHomeworkData');
        Route::get('download-uploaded-content-admin/{id}/{student_id}', 'Admin\Homework\AramiscHomeworkController@downloadHomeworkData')->name('download-uploaded-content-admin');
        //Route::get('evaluation-homework/{class_id}/{section_id}', 'Admin\Homework\AramiscHomeworkController@evaluationHomework');
        Route::get('evaluation-homework/{class_id}/{section_id}/{homework_id}', 'Admin\Homework\AramiscHomeworkController@evaluationHomework')->name('evaluation-homework')->middleware('userRolePermission:evaluation-homework');
        Route::get('university/evaluation-homework/{sem_label_id}/{homework_id}', 'Admin\Homework\AramiscHomeworkController@unEvaluationHomework')->name('university.unevaluation-homework')->middleware('userRolePermission:evaluation-homework');
        Route::post('save-homework-evaluation-data', ['as' => 'save-homework-evaluation-data', 'uses' => 'Admin\Homework\AramiscHomeworkController@saveHomeworkEvaluationData']);
        Route::get('evaluation-report', 'Admin\Homework\AramiscHomeworkController@EvaluationReport')->name('evaluation-report')->middleware('userRolePermission:evaluation-report');
        Route::get('evaluation-document-download/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/homework/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('evaluation-document-download');

        Route::post('evaluation-report', ['as' => 'search-evaluation', 'uses' => 'Admin\Homework\AramiscHomeworkController@searchEvaluation']);
        // Route::get('search-evaluation', 'Admin\Homework\AramiscHomeworkController@EvaluationReport');
        Route::get('view-evaluation-report/{homework_id}', 'Admin\Homework\AramiscHomeworkController@viewEvaluationReport')->name('view-evaluation-report')->middleware('userRolePermission:view-evaluation-report');

        Route::get('homework-report', ['as' => 'homework-report', 'uses' => 'Admin\Homework\AramiscHomeworkController@homeworkReport'])->middleware('userRolePermission:homework-report');
        Route::get('homework-report-search', ['as' => 'homework-report-search', 'uses' => 'Admin\Homework\AramiscHomeworkController@homeworkReportSearch'])->middleware('userRolePermission:homework-report-search');
        Route::get('homework-report-view/{student_id}/{class_id}/{section_id}/{homework_id}', ['as' => 'homework-report-view', 'uses' => 'Admin\Homework\AramiscHomeworkController@homeworkReportView']);

        //Study Material
        Route::get('upload-content', 'Admin\StudyMaterial\AramiscUploadContentController@index')->name('upload-content')->middleware('userRolePermission:upload-content');
        Route::post('save-upload-content', 'Admin\StudyMaterial\AramiscUploadContentController@store')->name('save-upload-content')->middleware('userRolePermission:save-upload-content');

        //
        Route::get('upload-content-edit/{id}', 'Admin\StudyMaterial\AramiscUploadContentController@uploadContentEdit')->name('upload-content-edit')->middleware('userRolePermission:upload-content-edit');
        Route::get('upload-content-view/{id}', 'Admin\StudyMaterial\AramiscUploadContentController@uploadContentView')->name('upload-content-view');
        //
        Route::post('update-upload-content', 'Admin\StudyMaterial\AramiscUploadContentController@updateUploadContent')->name('update-upload-content');
        Route::post('delete-upload-content', 'Admin\StudyMaterial\AramiscUploadContentController@deleteUploadContent')->name('delete-upload-content')->middleware('userRolePermission:delete-upload-content');

        Route::get('download-content-document/{file_name}', function ($file_name = null) {

            $file = public_path() . '/uploads/upload_contents/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-content-document');

        Route::get('assignment-list', 'Admin\StudyMaterial\AramiscUploadContentController@assignmentList')->name('assignment-list')->middleware('userRolePermission:assignment-list');
        Route::get('study-metarial-list', 'Admin\StudyMaterial\AramiscUploadContentController@studyMetarialList')->name('study-metarial-list');
        Route::get('syllabus-list', 'Admin\StudyMaterial\AramiscUploadContentController@syllabusList')->name('syllabus-list')->middleware('userRolePermission:syllabus-list');
        Route::get('other-download-list', 'Admin\StudyMaterial\AramiscUploadContentController@otherDownloadList')->name('other-download-list')->middleware('userRolePermission:other-download-list');

        Route::get('assignment-list-ajax', 'DatatableQueryController@assignmentList')->name('assignment-list-ajax')->middleware('userRolePermission:assignment-list');
        Route::get('syllabus-list-ajax', 'DatatableQueryController@syllabusList')->name('syllabus-list-ajax')->middleware('userRolePermission:syllabus-list');
        // Communicate
        Route::get('notice-list', 'Admin\Communicate\AramiscNoticeController@noticeList')->name('notice-list')->middleware('userRolePermission:notice-list');
        Route::get('administrator-notice', 'Admin\Communicate\AramiscNoticeController@administratorNotice')->name('administrator-notice');
        Route::get('add-notice', 'Admin\Communicate\AramiscNoticeController@sendMessage')->name('add-notice');
        Route::post('save-notice-data', 'Admin\Communicate\AramiscNoticeController@saveNoticeData')->name('save-notice-data');
        Route::get('edit-notice/{id}', 'Admin\Communicate\AramiscNoticeController@editNotice')->name('edit-notice');
        Route::post('update-notice-data', 'Admin\Communicate\AramiscNoticeController@updateNoticeData')->name('update-notice-data');
        Route::get('delete-notice-view/{id}', 'Admin\Communicate\AramiscNoticeController@deleteNoticeView')->name('delete-notice-view')->middleware('userRolePermission:delete-notice-view');
        Route::get('send-email-sms-view', 'Admin\Communicate\AramiscCommunicateController@sendEmailSmsView')->name('send-email-sms-view')->middleware('userRolePermission:send-email-sms-view');
        Route::post('send-email-sms', 'Admin\Communicate\AramiscCommunicateController@sendEmailSms')->name('send-email-sms')->middleware('userRolePermission:send-email-sms');
        Route::get('email-sms-log', 'Admin\Communicate\AramiscCommunicateController@emailSmsLog')->name('email-sms-log')->middleware('userRolePermission:email-sms-log');
        Route::get('delete-notice/{id}', 'Admin\Communicate\AramiscNoticeController@deleteNotice')->name('delete-notice');

        Route::get('studStaffByRole', 'Admin\Communicate\AramiscCommunicateController@studStaffByRole');

        Route::get('email-sms-log-ajax', 'DatatableQueryController@emailSmsLogAjax')->name('emailSmsLogAjax')->middleware('userRolePermission:email-sms-log');

        //Holiday
        // Route::resource('holiday', 'Admin\SystemSettings\AramiscHolidayController');
        Route::get('holiday', 'Admin\SystemSettings\AramiscHolidayController@index')->name('holiday')->middleware('userRolePermission:holiday');
        Route::post('holiday', 'Admin\SystemSettings\AramiscHolidayController@store')->name('holiday-store')->middleware('userRolePermission:holiday-store');
        Route::get('holiday/{id}/edit', 'Admin\SystemSettings\AramiscHolidayController@edit')->name('holiday-edit')->middleware('userRolePermission:holiday-edit');
        Route::put('holiday/{id}', 'Admin\SystemSettings\AramiscHolidayController@update')->name('holiday-update')->middleware('userRolePermission:holiday-edit');
        Route::get('delete-holiday-data-view/{id}', 'Admin\SystemSettings\AramiscHolidayController@deleteHolidayView')->name('delete-holiday-data-view')->middleware('userRolePermission:delete-holiday-data-view');
        Route::get('delete-holiday-data/{id}', 'Admin\SystemSettings\AramiscHolidayController@deleteHoliday')->name('delete-holiday-data')->middleware('userRolePermission:delete-holiday-data');

        //Notification Settings
        Route::controller('Admin\SystemSettings\AramiscNotificationController')->group(function () {
            Route::get('notification_settings', 'index')->name('notification_settings')->middleware('userRolePermission:notification_settings');
            Route::get('notification_event_modal/{id}/{key}', 'notificationEventModal')->name('notification_event_modal');
            Route::post('notification-settings-update', 'notificationSettingsUpdate')->name('notification_settings_update');
        });


        // Route::resource('weekend', 'Admin\SystemSettings\AramiscWeekendController');
        Route::get('weekend', 'Admin\SystemSettings\AramiscWeekendController@index')->name('weekend')->middleware('userRolePermission:weekend');
        Route::post('weekend/switch', 'Admin\SystemSettings\AramiscWeekendController@store')
            ->name('weekend.store')->middleware('userRolePermission:weekend.store');
        Route::get('weekend/{id}', 'Admin\SystemSettings\AramiscWeekendController@edit')->name('weekend-edit');
        Route::put('weekend/{id}', 'Admin\SystemSettings\AramiscWeekendController@update')->name('weekend-update');

        //Book Category
        // Route::resource('book-category-list', 'Admin\Library\AramiscBookCategoryController');
        Route::get('book-category-list', 'Admin\Library\AramiscBookCategoryController@index')->name('book-category-list')->middleware('userRolePermission:book-category-list');
        Route::post('book-category-list', 'Admin\Library\AramiscBookCategoryController@store')->name('book-category-list-store')->middleware('userRolePermission:book-category-list-store');
        Route::get('book-category-list/{id}', 'Admin\Library\AramiscBookCategoryController@edit')->name('book-category-list-edit')->middleware('userRolePermission:book-category-list-edit');
        Route::put('book-category-list/{id}', 'Admin\Library\AramiscBookCategoryController@update')->name('book-category-list-update')->middleware('userRolePermission:book-category-list-edit');
        Route::delete('book-category-list/{id}', 'Admin\Library\AramiscBookCategoryController@destroy')->name('book-category-list-delete')->middleware('userRolePermission:book-category-list-delete');

        Route::get('delete-book-category-view/{id}', 'Admin\Library\AramiscBookCategoryController@deleteBookCategoryView');
        Route::get('delete-book-category/{id}', 'Admin\Library\AramiscBookCategoryController@deleteBookCategory')->name('delete-book-category');

        // Book
        Route::get('book-list', 'Admin\Library\AramiscBookController@index')->name('book-list')->middleware('userRolePermission:book-list');
        Route::get('add-book', 'Admin\Library\AramiscBookController@addBook')->name('add-book')->middleware('userRolePermission:add-book');
        Route::post('save-book-data', 'Admin\Library\AramiscBookController@saveBookData')->name('save-book-data')->middleware('userRolePermission:save-book-data');
        Route::get('edit-book/{id}', 'Admin\Library\AramiscBookController@editBook')->name('edit-book');
        Route::post('update-book-data/{id}', 'Admin\Library\AramiscBookController@updateBookData')->name('update-book-data');
        Route::get('delete-book-view/{id}', 'Admin\Library\AramiscBookController@deleteBookView')->name('delete-book-view')->middleware('userRolePermission:delete-book-view');
        Route::get('delete-book/{id}', 'Admin\Library\AramiscBookController@deleteBook');
        Route::get('member-list', 'Admin\Library\AramiscBookController@memberList')->name('member-list')->middleware('userRolePermission:member-list');
        Route::get('issue-books/{member_type}/{id}', 'Admin\Library\AramiscBookController@issueBooks')->name('issue-books');
        Route::post('save-issue-book-data', 'Admin\Library\AramiscBookController@saveIssueBookData')->name('save-issue-book-data');
        Route::get('return-book-view/{id}', 'Admin\Library\AramiscBookController@returnBookView')->name('return-book-view')->middleware('userRolePermission:return-book-view');
        Route::get('return-book/{id}', 'Admin\Library\AramiscBookController@returnBook')->name('return-book');
        Route::get('all-issed-book', 'Admin\Library\AramiscBookController@allIssuedBook')->name('all-issed-book')->middleware('userRolePermission:all-issed-book');
        Route::post('all-issed-book', 'Admin\Library\AramiscBookController@searchIssuedBook')->name('search-issued-book');
        // Route::get('search-issued-book', 'p@allIssuedBook');


        // Library Subject routes
        Route::get('library-subject', ['as' => 'library_subject', 'uses' => 'Admin\Library\AramiscBookController@subjectList'])->middleware('userRolePermission:library_subject');
        Route::post('library-subject-store', ['as' => 'library_subject_store', 'uses' => 'Admin\Library\AramiscBookController@store'])->middleware('userRolePermission:library_subject_store');
        Route::get('library-subject-edit/{id}', ['as' => 'library_subject_edit', 'uses' => 'Admin\Library\AramiscBookController@edit'])->middleware('userRolePermission:library_subject_edit');
        Route::post('library-subject-update', ['as' => 'library_subject_update', 'uses' => 'Admin\Library\AramiscBookController@update'])->middleware('userRolePermission:library_subject_edit');
        Route::get('library-subject-delete/{id}', ['as' => 'library_subject_delete', 'uses' => 'Admin\Library\AramiscBookController@delete'])->middleware('userRolePermission:library_subject_delete');
        //library member
        // Route::resource('library-member', 'Admin\Library\AramiscLibraryMemberController');
        Route::get('library-member', 'Admin\Library\AramiscLibraryMemberController@index')->name('library-member')->middleware('userRolePermission:library-member');
        Route::post('library-member', 'Admin\Library\AramiscLibraryMemberController@store')->name('library-member-store')->middleware('userRolePermission:library-member-store');

        Route::get('cancel-membership/{id}', 'Admin\Library\AramiscLibraryMemberController@cancelMembership')->name('cancel-membership')->middleware('userRolePermission:cancel-membership');


        // Ajax Subject in dropdown by section change
        Route::get('ajaxSubjectDropdown', 'Admin\Academics\AcademicController@ajaxSubjectDropdown');
        Route::post('/language-change', 'Admin\SystemSettings\AramiscSystemSettingController@ajaxLanguageChange');

        // Route::get('localization/{locale}','SmLocalizationController@index');


        //inventory
        // Route::resource('item-category', 'Admin\Inventory\AramiscItemCategoryController');
        Route::get('item-category', 'Admin\Inventory\AramiscItemCategoryController@index')->name('item-category')->middleware('userRolePermission:item-category');
        Route::post('item-category', 'Admin\Inventory\AramiscItemCategoryController@store')->name('item-category-store')->middleware('userRolePermission:item-category-store');
        Route::get('item-category/{id}', 'Admin\Inventory\AramiscItemCategoryController@edit')->name('item-category-edit')->middleware('userRolePermission:item-category-edit');
        Route::put('item-category/{id}', 'Admin\Inventory\AramiscItemCategoryController@update')->name('item-category-update')->middleware('userRolePermission:item-category-edit');

        Route::get('delete-item-category-view/{id}', 'Admin\Inventory\AramiscItemCategoryController@deleteItemCategoryView')->name('delete-item-category-view')->middleware('userRolePermission:delete-item-category-view');
        Route::get('delete-item-category/{id}', 'Admin\Inventory\AramiscItemCategoryController@deleteItemCategory')->name('delete-item-category')->middleware('userRolePermission:delete-item-category-view');

        // Route::resource('item-list', 'Admin\Inventory\AramiscItemController');
        Route::get('item-list', 'Admin\Inventory\AramiscItemController@index')->name('item-list')->middleware('userRolePermission:item-list');
        Route::post('item-list', 'Admin\Inventory\AramiscItemController@store')->name('item-list-store')->middleware('userRolePermission:item-list-store');
        Route::get('item-list/{id}', 'Admin\Inventory\AramiscItemController@edit')->name('item-list-edit')->middleware('userRolePermission:item-list-edit');
        Route::put('item-list/{id}', 'Admin\Inventory\AramiscItemController@update')->name('item-list-update')->middleware('userRolePermission:item-list-edit');

        Route::get('delete-item-view/{id}', 'Admin\Inventory\AramiscItemController@deleteItemView')->name('delete-item-view')->middleware('userRolePermission:delete-item-view');
        Route::get('delete-item/{id}', 'Admin\Inventory\AramiscItemController@deleteItem')->name('delete-item')->middleware('userRolePermission:delete-item-view');

        // Route::resource('item-store', 'Admin\Inventory\AramiscItemStoreController');
        Route::get('item-store', 'Admin\Inventory\AramiscItemStoreController@index')->name('item-store')->middleware('userRolePermission:item-store');
        Route::post('item-store', 'Admin\Inventory\AramiscItemStoreController@store')->name('item-store-store')->middleware('userRolePermission:item-store-store');
        Route::get('item-store/{id}', 'Admin\Inventory\AramiscItemStoreController@edit')->name('item-store-edit')->middleware('userRolePermission:item-store-edit');
        Route::put('item-store/{id}', 'Admin\Inventory\AramiscItemStoreController@update')->name('item-store-update')->middleware('userRolePermission:item-store-edit');

        Route::get('delete-store-view/{id}', 'Admin\Inventory\AramiscItemStoreController@deleteStoreView')->name('delete-store-view')->middleware('userRolePermission:delete-store-view');
        Route::get('delete-store/{id}', 'Admin\Inventory\AramiscItemStoreController@deleteStore')->name('delete-store')->middleware('userRolePermission:delete-store-view');

        Route::get('item-receive', 'Admin\Inventory\AramiscItemReceiveController@itemReceive')->name('item-receive')->middleware('userRolePermission:item-receive');
        Route::post('get-receive-item', 'Admin\Inventory\AramiscItemReceiveController@getReceiveItem');
        Route::post('save-item-receive-data', 'Admin\Inventory\AramiscItemReceiveController@saveItemReceiveData')->name('save-item-receive-data')->middleware('userRolePermission:save-item-receive-data');
        Route::get('item-receive-list', 'Admin\Inventory\AramiscItemReceiveController@itemReceiveList')->name('item-receive-list')->middleware('userRolePermission:item-receive-list');
        Route::get('edit-item-receive/{id}', 'Admin\Inventory\AramiscItemReceiveController@editItemReceive')->name('edit-item-receive')->middleware('userRolePermission:edit-item-receive');
        Route::post('update-edit-item-receive-data/{id}', 'Admin\Inventory\AramiscItemReceiveController@updateItemReceiveData')->name('update-edit-item-receive-data')->middleware('userRolePermission:edit-item-receive');
        Route::post('delete-receive-item', 'Admin\Inventory\AramiscItemReceiveController@deleteReceiveItem');
        Route::get('view-item-receive/{id}', 'Admin\Inventory\AramiscItemReceiveController@viewItemReceive')->name('view-item-receive');
        Route::get('add-payment/{id}', 'Admin\Inventory\AramiscItemReceiveController@itemReceivePayment')->name('add-payment');
        Route::post('save-item-receive-payment', 'Admin\Inventory\AramiscItemReceiveController@saveItemReceivePayment')->name('save-item-receive-payment');
        Route::get('view-receive-payments/{id}', 'Admin\Inventory\AramiscItemReceiveController@viewReceivePayments')->name('view-receive-payments')->middleware('userRolePermission:view-receive-payments');
        Route::post('delete-receive-payment', 'Admin\Inventory\AramiscItemReceiveController@deleteReceivePayment');
        Route::get('delete-item-receive-view/{id}', 'Admin\Inventory\AramiscItemReceiveController@deleteItemReceiveView')->name('delete-item-receive-view')->middleware('userRolePermission:delete-item-receive-view');
        Route::get('delete-item-receive/{id}', 'Admin\Inventory\AramiscItemReceiveController@deleteItemReceive')->name('delete-item-receive');
        Route::get('delete-item-sale-view/{id}', 'Admin\Inventory\AramiscItemReceiveController@deleteItemSaleView')->name('delete-item-sale-view')->middleware('userRolePermission:delete-item-sale-view');
        Route::get('delete-item-sale/{id}', 'Admin\Inventory\AramiscItemReceiveController@deleteItemSale');
        Route::get('cancel-item-receive-view/{id}', 'Admin\Inventory\AramiscItemReceiveController@cancelItemReceiveView')->name('cancel-item-receive-view');
        Route::get('cancel-item-receive/{id}', 'Admin\Inventory\AramiscItemReceiveController@cancelItemReceive')->name('cancel-item-receive');

        // Item Sell in inventory
        Route::get('item-sell-list', 'Admin\Inventory\AramiscItemSellController@itemSellList')->name('item-sell-list')->middleware('userRolePermission:item-sell-list');
        Route::get('item-sell', 'Admin\Inventory\AramiscItemSellController@itemSell')->name('item-sell')->middleware('userRolePermission:save-item-sell-data');
        Route::post('save-item-sell-data', 'Admin\Inventory\AramiscItemSellController@saveItemSellData')->name('save-item-sell-data');

        Route::post('check-product-quantity', 'Admin\Inventory\AramiscItemSellController@checkProductQuantity');
        Route::get('edit-item-sell/{id}', 'Admin\Inventory\AramiscItemSellController@editItemSell')->name('edit-item-sell')->middleware('userRolePermission:edit-item-sell');

        Route::post('update-item-sell-data', 'Admin\Inventory\AramiscItemSellController@UpdateItemSellData')->name('update-item-sell-data');




        Route::get('item-issue', 'Admin\Inventory\AramiscItemSellController@itemIssueList')->name('item-issue')->middleware('userRolePermission:item-issue');
        Route::post('save-item-issue-data', 'Admin\Inventory\AramiscItemSellController@saveItemIssueData')->name('save-item-issue-data')->middleware('userRolePermission:save-item-issue-data');
        Route::get('getItemByCategory', 'Admin\Inventory\AramiscItemSellController@getItemByCategory');
        Route::get('return-item-view/{id}', 'Admin\Inventory\AramiscItemSellController@returnItemView')->name('return-item-view')->middleware('userRolePermission:return-item-view');
        Route::get('return-item/{id}', 'Admin\Inventory\AramiscItemSellController@returnItem')->name('return-item');

        Route::get('view-item-sell/{id}', 'Admin\Inventory\AramiscItemSellController@viewItemSell')->name('view-item-sell');
        Route::get('view-item-sell-print/{id}', 'Admin\Inventory\AramiscItemSellController@viewItemSellPrint')->name('view-item-sell-print');

        Route::get('add-payment-sell/{id}', 'Admin\Inventory\AramiscItemSellController@itemSellPayment')->name('add-payment-sell')->middleware('userRolePermission:add-payment-sell');
        Route::post('save-item-sell-payment', 'Admin\Inventory\AramiscItemSellController@saveItemSellPayment')->name('save-item-sell-payment');


        //Supplier
        // Route::resource('suppliers', 'Admin\Inventory\AramiscSupplierController');
        Route::get('suppliers', 'Admin\Inventory\AramiscSupplierController@index')->name('suppliers')->middleware('userRolePermission:suppliers');
        Route::post('suppliers', 'Admin\Inventory\AramiscSupplierController@store')->name('suppliers-store')->middleware('userRolePermission:suppliers-store');
        Route::get('suppliers/{id}', 'Admin\Inventory\AramiscSupplierController@edit')->name('suppliers-edit')->middleware('userRolePermission:suppliers-edit');
        Route::put('suppliers/{id}', 'Admin\Inventory\AramiscSupplierController@update')->name('suppliers-update')->middleware('userRolePermission:suppliers-edit');
        Route::get('delete-supplier-view/{id}', 'Admin\Inventory\AramiscSupplierController@deleteSupplierView')->name('delete-supplier-view')->middleware('userRolePermission:suppliers-delete');
        Route::get('delete-supplier/{id}', 'Admin\Inventory\AramiscSupplierController@deleteSupplier')->name('delete-supplier')->middleware('userRolePermission:delete-supplier');


        Route::get('view-sell-payments/{id}', 'Admin\Inventory\AramiscItemSellController@viewSellPayments')->name('view-sell-payments')->middleware('userRolePermission:view-sell-payments');


        Route::post('delete-sell-payment', 'Admin\Inventory\AramiscItemSellController@deleteSellPayment');
        Route::get('cancel-item-sell-view/{id}', 'Admin\Inventory\AramiscItemSellController@cancelItemSellView')->name('cancel-item-sell-view');
        Route::get('cancel-item-sell/{id}', 'Admin\Inventory\AramiscItemSellController@cancelItemSell')->name('cancel-item-sell');


        //library member
        // Route::resource('library-member', 'Admin\Library\AramiscLibraryMemberController');
        // Route::get('cancel-membership/{id}', 'Admin\Library\AramiscLibraryMemberController@cancelMembership');


        //ajax theme change
        // Route::get('theme-style-active', 'Admin\SystemSettings\AramiscSystemSettingController@themeStyleActive');
        // Route::get('theme-style-rtl', 'Admin\SystemSettings\AramiscSystemSettingController@themeStyleRTL');
        // Route::get('change-academic-year', 'Admin\SystemSettings\AramiscSystemSettingController@sessionChange');

        // Sms Settings
        Route::get('sms-settings', 'Admin\SystemSettings\AramiscSystemSettingController@smsSettings')->name('sms-settings')->middleware('userRolePermission:sms-settings');
        Route::post('update-clickatell-data', 'Admin\SystemSettings\AramiscSystemSettingController@updateClickatellData')->name('update-clickatell-data');
        Route::post('update-twilio-data', 'Admin\SystemSettings\AramiscSystemSettingController@updateTwilioData')->name('update-twilio-data')->middleware('userRolePermission:update-twilio-data');
        Route::post('update-msg91-data', 'Admin\SystemSettings\AramiscSystemSettingController@updateMsg91Data')->name('update-msg91-data')->middleware('userRolePermission:update-msg91-data');
        Route::any('activeSmsService', 'Admin\SystemSettings\AramiscSystemSettingController@activeSmsService');

        Route::post('update-textlocal-data', 'Admin\SystemSettings\AramiscSystemSettingController@updateTextlocalData')->name('update-textlocal-data')->middleware('userRolePermission:update-textlocal-data');

        Route::post('update-africatalking-data', 'Admin\SystemSettings\AramiscSystemSettingController@updateAfricaTalkingData')->name('update-africatalking-data')->middleware('userRolePermission:update-textlocal-data');


        //Language Setting
        Route::get('language-setup/{id}', 'Admin\SystemSettings\AramiscSystemSettingController@languageSetup')->name('language-setup')->middleware('userRolePermission:language-setup');
        Route::get('language-settings', 'Admin\SystemSettings\AramiscSystemSettingController@languageSettings')->name('language-settings')->middleware('userRolePermission:language-settings');
        Route::post('language-add', 'Admin\SystemSettings\AramiscSystemSettingController@languageAdd')->name('language-add')->middleware('userRolePermission:language-add');

        Route::get('language-edit/{id}', 'Admin\SystemSettings\AramiscSystemSettingController@languageEdit');
        Route::post('language-update', 'Admin\SystemSettings\AramiscSystemSettingController@languageUpdate')->name('language-update');

        Route::post('language-delete', 'Admin\SystemSettings\AramiscSystemSettingController@languageDelete')->name('language-delete')->middleware('userRolePermission:language-delete');

        Route::get('get-translation-terms', 'Admin\SystemSettings\AramiscSystemSettingController@getTranslationTerms');
        Route::post('translation-term-update', 'Admin\SystemSettings\AramiscSystemSettingController@translationTermUpdate')->name('translation-term-update');

        //currency
        Route::get('manage-currency', 'Admin\GeneralSettings\AramiscManageCurrencyController@manageCurrency')->name('manage-currency')->middleware('userRolePermission:manage-currency');

        Route::get('create-currency', 'Admin\GeneralSettings\AramiscManageCurrencyController@create')->name('create-currency')->middleware('userRolePermission:manage-currency');

        Route::post('currency-store', 'Admin\GeneralSettings\AramiscManageCurrencyController@storeCurrency')->name('currency-store')->middleware('userRolePermission:currency-store');

        Route::post('currency-update', 'Admin\GeneralSettings\AramiscManageCurrencyController@storeCurrencyUpdate')->name('currency-update')->middleware('userRolePermission:currency_edit');
        Route::get('manage-currency/edit/{id}', 'Admin\GeneralSettings\AramiscManageCurrencyController@manageCurrencyEdit')->name('currency_edit')->middleware('userRolePermission:currency_edit');

        Route::get('manage-currency/delete/{id}', 'Admin\GeneralSettings\AramiscManageCurrencyController@manageCurrencyDelete')->name('currency_delete')->middleware('userRolePermission:currency_delete');

        Route::get('manage-currency/active/{id}', 'Admin\GeneralSettings\AramiscManageCurrencyController@manageCurrencyActive')->name('currency_active')->middleware('userRolePermission:currency_active');

        Route::get('system-destroyed-by-authorized', 'Admin\GeneralSettings\AramiscManageCurrencyController@systemDestroyedByAuthorized')->name('systemDestroyedByAuthorized');


        //Backup Setting
        Route::post('backup-store', 'Admin\SystemSettings\AramiscSystemSettingController@BackupStore')->name('backup-store')->middleware('userRolePermission:backup-store');
        Route::get('backup-settings', 'Admin\SystemSettings\AramiscSystemSettingController@backupSettings')->name('backup-settings')->middleware('userRolePermission:backup-settings');
        Route::get('get-backup-files/{id}', 'Admin\SystemSettings\AramiscSystemSettingController@getfilesBackup')->name('get-backup-files')->middleware('userRolePermission:get-backup-files');
        Route::get('get-backup-db', 'Admin\SystemSettings\AramiscSystemSettingController@getDatabaseBackup')->name('get-backup-db')->middleware('userRolePermission:get-backup-db');
        Route::get('download-database/{id}', 'Admin\SystemSettings\AramiscSystemSettingController@downloadDatabase');
        Route::get('download-files/{id}', 'Admin\SystemSettings\AramiscSystemSettingController@downloadFiles')->name('download-files')->middleware('userRolePermission:download-files');
        Route::get('restore-database/{id}', 'Admin\SystemSettings\AramiscSystemSettingController@restoreDatabase')->name('restore-database');
        Route::get('delete-database/{id}', 'Admin\SystemSettings\AramiscSystemSettingController@deleteDatabase')->name('delete_database')->middleware('userRolePermission:delete_database');

        //Update System
        Route::get('about-system', 'Admin\SystemSettings\AramiscSystemSettingController@AboutSystem')->name('about-system')->middleware('userRolePermission:about-system');


        Route::get('database-upgrade', 'Admin\SystemSettings\AramiscSystemSettingController@databaseUpgrade')->name('database-upgrade');
        Route::get('update-system', 'Admin\SystemSettings\AramiscSystemSettingController@UpdateSystem')->name('update-system')->middleware('userRolePermission:update-system');
        Route::post('admin/update-system', 'Admin\SystemSettings\AramiscSystemSettingController@admin_UpdateSystem')->name('admin/update-system')->middleware('userRolePermission:admin/update-system');
        Route::any('upgrade-settings', 'Admin\SystemSettings\AramiscSystemSettingController@UpgradeSettings');


        //Route::get('sendSms','SmSmsTestController@sendSms');
        //Route::get('sendSmsMsg91','SmSmsTestController@sendSmsMsg91');
        //Route::get('sendSmsClickatell','SmSmsTestController@sendSmsClickatell');

        //Settings
        Route::get('general-settings', 'Admin\SystemSettings\AramiscSystemSettingController@generalSettingsView')->name('general-settings')->middleware('userRolePermission:general-settings');
        Route::get('update-general-settings', 'Admin\SystemSettings\AramiscSystemSettingController@updateGeneralSettings')->name('update-general-settings')->middleware('userRolePermission:update-general-settings');
        Route::post('update-general-settings-data', 'Admin\SystemSettings\AramiscSystemSettingController@updateGeneralSettingsData')->name('update-general-settings-data')->middleware('userRolePermission:update-general-settings-data');
        Route::post('update-school-logo', 'Admin\SystemSettings\AramiscSystemSettingController@updateSchoolLogo')->name('update-school-logo')->middleware('userRolePermission:update-school-logo');

        //Custom Field Start
        Route::get('student-registration-custom-field', 'AramiscCustomFieldController@index')->name('student-reg-custom-field')->middleware('userRolePermission:student-reg-custom-field');
        Route::post('store-student-registration-custom-field', 'AramiscCustomFieldController@store')->name('store-student-registration-custom-field')->middleware('userRolePermission:store-student-registration-custom-field');
        Route::get('edit-custom-field/{id}', 'AramiscCustomFieldController@edit')->name('edit-custom-field')->middleware('userRolePermission:edit-custom-field');
        Route::post('update-student-registration-custom-field', 'AramiscCustomFieldController@update')->name('update-student-registration-custom-field');
        Route::post('delete-custom-field', 'AramiscCustomFieldController@destroy')->name('delete-custom-field')->middleware('userRolePermission:delete-custom-field');

        Route::get('staff-reg-custom-field', 'AramiscCustomFieldController@staff_reg_custom_field')->name('staff-reg-custom-field')->middleware('userRolePermission:staff-reg-custom-field');
        Route::post('store-staff-registration-custom-field', 'AramiscCustomFieldController@store_staff_registration_custom_field')->name('store-staff-registration-custom-field')->middleware('userRolePermission:store-staff-registration-custom-field');
        Route::get('edit-staff-custom-field/{id}', 'AramiscCustomFieldController@edit_staff_custom_field')->name('edit-staff-custom-field');
        Route::post('update-staff-custom-field', 'AramiscCustomFieldController@update_staff_custom_field')->name('update-staff-custom-field')->middleware('userRolePermission:edit-staff-custom-field');
        Route::post('delete-staff-custom-field', 'AramiscCustomFieldController@delete_staff_custom_field')->name('delete-staff-custom-field')->middleware('userRolePermission:delete-staff-custom-field');

        Route::get('donor-reg-custom-field', 'AramiscCustomFieldController@donor_reg_custom_field')->name('donor-reg-custom-field')->middleware('userRolePermission:donor-reg-custom-field');
        Route::post('store-donor-registration-custom-field', 'AramiscCustomFieldController@store_donor_registration_custom_field')->name('store-donor-registration-custom-field')->middleware('userRolePermission:store-donor-registration-custom-field');
        Route::get('edit-donor-custom-field/{id}', 'AramiscCustomFieldController@edit_donor_custom_field')->name('edit-donor-custom-field');
        Route::post('update-donor-custom-field', 'AramiscCustomFieldController@update_donor_custom_field')->name('update-donor-custom-field')->middleware('userRolePermission:edit-donor-custom-field');
        Route::post('delete-donor-custom-field', 'AramiscCustomFieldController@delete_donor_custom_field')->name('delete-donor-custom-field')->middleware('userRolePermission:delete-donor-custom-field');
        //Custom Field End



        // payment Method Settings
        Route::get('payment-method-settings', 'Admin\SystemSettings\AramiscSystemSettingController@paymentMethodSettings')->name('payment-method-settings')->middleware('userRolePermission:payment-method-settings');
        Route::post('update-paypal-data', 'Admin\SystemSettings\AramiscSystemSettingController@updatePaypalData')->name('updatePaypalData');
        Route::post('update-stripe-data', 'Admin\SystemSettings\AramiscSystemSettingController@updateStripeData');
        Route::post('update-payumoney-data', 'Admin\SystemSettings\AramiscSystemSettingController@updatePayumoneyData');
        Route::post('active-payment-gateway', 'Admin\SystemSettings\AramiscSystemSettingController@activePaymentGateway');
        Route::post('bank-status', 'Admin\SystemSettings\AramiscSystemSettingController@bankStatus')->name('bank-status');

        //Email Settings
        Route::get('email-settings', 'Admin\SystemSettings\AramiscSystemSettingController@emailSettings')->name('email-settings')->middleware('userRolePermission:email-settings');
        Route::post('update-email-settings-data', 'Admin\SystemSettings\AramiscSystemSettingController@updateEmailSettingsData')->name('update-email-settings-data')->middleware('userRolePermission:update-email-settings-data');


        Route::post('send-test-mail', 'Admin\SystemSettings\AramiscSystemSettingController@sendTestMail')->name('send-test-mail');

        // payment Method Settings
        // Route::get('payment-method-settings', 'Admin\SystemSettings\AramiscSystemSettingController@paymentMethodSettings');

        Route::post('is-active-payment', 'Admin\SystemSettings\AramiscSystemSettingController@isActivePayment')->name('is-active-payment')->middleware('userRolePermission:is-active-payment');
        //Route::get('stripeTest', 'SmSmsTestController@stripeTest');
        //Route::post('stripe_post', 'SmSmsTestController@stripePost');

        //Collect fees By Online Payment Gateway(Paypal)
        Route::get('collect-fees-gateway/{amount}/{student_id}/{type}', 'AramiscCollectFeesByPaymentGateway@collectFeesByGateway');
        Route::post('payByPaypal', 'AramiscCollectFeesByPaymentGateway@payByPaypal')->name('payByPaypal');

        //Collect fees By Online Payment Gateway(Stripe)
        Route::get('collect-fees-stripe/{amount}/{student_id}/{type}', 'AramiscCollectFeesByPaymentGateway@collectFeesStripe');
        Route::post('collect-fees-stripe-strore', 'AramiscCollectFeesByPaymentGateway@stripeStore')->name('collect-fees-stripe-strore');

        // To Do list

        //Route::get('stripeTest', 'SmSmsTestController@stripeTest');
        //Route::post('stripe_post', 'SmSmsTestController@stripePost');




        Route::get('custom-result-setting', 'Admin\Examination\CustomResultSettingController@index')->name('custom-result-setting')->middleware('userRolePermission:custom-result-setting');
        Route::get('custom-result-setting/edit/{id}', 'Admin\Examination\CustomResultSettingController@edit')->name('custom-result-setting-edit')->middleware('userRolePermission:custom-result-setting-edit');
        Route::DELETE('custom-result-setting/{id}', 'Admin\Examination\CustomResultSettingController@delete')->name('custom-result-setting-delete')->middleware('userRolePermission:custom-result-setting-delete');
        Route::put('custom-result-setting/update', 'Admin\Examination\CustomResultSettingController@update')->name('custom-result-setting/update')->middleware('userRolePermission:custom-result-setting-edit');
        Route::post('custom-result-setting/store', 'Admin\Examination\CustomResultSettingController@store')->name('custom-result-setting/store')->middleware('userRolePermission:437');
        Route::post('merit-list-settings', 'Admin\Examination\CustomResultSettingController@merit_list_settings')->name('merit-list-settings');

        //Custom Result
        Route::get('custom-merit-list', 'Admin\Examination\CustomResultSettingController@meritListReportIndex')->name('custom-merit-list')->middleware('userRolePermission:custom-merit-list');
        Route::get('custom-merit-list/print/{class}/{section}', 'Admin\Examination\CustomResultSettingController@meritListReportPrint')->name('custom-merit-list-print');
        Route::post('custom-merit-list', 'Admin\Examination\CustomResultSettingController@meritListReport')->name('custom-merit-list-search');

        Route::get('custom-progress-card', 'Admin\Examination\CustomResultSettingController@progressCardReportIndex')->name('custom-progress-card')->middleware('userRolePermission:custom-progress-card');
        Route::post('custom-progress-card', 'Admin\Examination\CustomResultSettingController@progressCardReport')->name('custom-progress-card-search')->middleware('userRolePermission:custom-progress-card');
        Route::post('custom-progress-card/print', 'Admin\Examination\CustomResultSettingController@progressCardReportPrint')->name('custom-progress-card-print');


        Route::post('exam-step-skip', 'Admin\Examination\CustomResultSettingController@stepSkipUpdate')->name('exam.step.skip.update');

        // login access control
        Route::get('login-access-control', 'AramiscLoginAccessControlController@loginAccessControl')->name('login-access-control')->middleware('userRolePermission:login-access-control');
        Route::post('login-access-control', 'AramiscLoginAccessControlController@searchUser')->name('login-access-control-search');
        Route::get('login-access-permission', 'AramiscLoginAccessControlController@loginAccessPermission');
        Route::get('login-password-reset', 'AramiscLoginAccessControlController@loginPasswordDefault');

        Route::get('button-disable-enable', 'Admin\SystemSettings\AramiscSystemSettingController@buttonDisableEnable')->name('button-disable-enable')->middleware('userRolePermission:button-disable-enable');

        Route::get('manage-adons', 'Admin\SystemSettings\AramiscAddOnsController@ManageAddOns')->name('manage-adons')->middleware('userRolePermission:manage-adons');
        Route::get('manage-adons-delete/{name}', 'Admin\SystemSettings\AramiscAddOnsController@ManageAddOns')->name('deleteModule');
        Route::get('manage-adons-enable/{name}', 'Admin\SystemSettings\AramiscAddOnsController@moduleAddOnsEnable')->name('moduleAddOnsEnable');
        Route::get('manage-adons-disable/{name}', 'Admin\SystemSettings\AramiscAddOnsController@moduleAddOnsDisable')->name('moduleAddOnsDisable');

        // Route::post('manage-adons-validation', 'Admin\SystemSettings\AramiscAddOnsController@ManageAddOnsValidation')->name('ManageAddOnsValidation')->middleware('userRolePermission:400');
        Route::get('ModuleRefresh', 'Admin\SystemSettings\AramiscAddOnsController@ModuleRefresh')->name('ModuleRefresh');
        Route::get('view-as-superadmin', 'Admin\SystemSettings\AramiscSystemSettingController@viewAsSuperadmin')->name('viewAsSuperadmin');



        Route::get('/sms-template', 'Admin\Communicate\SmsEmailTemplateController@AramiscTemplate')->name('sms-template');
        Route::post('/sms-template/{id}', 'Admin\Communicate\SmsEmailTemplateController@AramiscTemplateStore')->name('sms-template-store')->middleware('userRolePermission:sms-template-store');

        Route::get('/sms-template-new', 'Admin\Communicate\SmsEmailTemplateController@AramiscTemplateNew')->name('sms-template-new')->middleware('userRolePermission:sms-template-new');
        Route::post('/sms-template-new', 'Admin\Communicate\SmsEmailTemplateController@AramiscTemplateNewStore')->name('sms-template-new-store')->middleware('userRolePermission:sms-template-new-store');
    });


    Route::post('update-payment-gateway', 'Admin\SystemSettings\AramiscSystemSettingController@updatePaymentGateway')->name('update-payment-gateway')->middleware('userRolePermission:update-payment-gateway');
    Route::post('versionUpdateInstall', 'Admin\SystemSettings\AramiscSystemSettingController@versionUpdateInstall')->name('versionUpdateInstall');

    Route::post('moduleFileUpload', 'Admin\SystemSettings\AramiscSystemSettingController@moduleFileUpload')->name('moduleFileUpload');


    //systemsetting utilities 

    Route::get('utility', 'Admin\SystemSettings\UtilityController@index')->name('utility');
    Route::get('utilities/{action}', 'Admin\SystemSettings\UtilityController@action')->name('utilities');
    Route::get('testup', 'Admin\SystemSettings\UtilityController@testup')->name('testup');
    Route::post('maintenance_mode', 'Admin\SystemSettings\UtilityController@updateMaintenance')->name('updateMaintenance');

    // background setting
    Route::get('background-setting', 'Admin\Style\AramiscBackGroundSettingController@index')->name('background-setting')->middleware('userRolePermission:background-setting');
    Route::post('background-settings-update', 'Admin\Style\AramiscBackGroundSettingController@update')->name('background-settings-update');
    Route::post('background-settings-store', 'Admin\Style\AramiscBackGroundSettingController@store')->name('background-settings-store')->middleware('userRolePermission:background-settings-store');
    Route::get('background-setting-delete/{id}', 'Admin\Style\AramiscBackGroundSettingController@delete')->name('background-setting-delete')->middleware('userRolePermission:background-setting-delete');
    Route::get('background_setting-status/{id}', 'Admin\Style\AramiscBackGroundSettingController@status')->name('background_setting-status')->middleware('userRolePermission:background_setting-status');

    //color theme change
    Route::get('color-style', 'Admin\Style\ThemeController@index')->name('color-style')->middleware('userRolePermission:color-style');
    Route::get('make-default-theme/{id}', 'Admin\Style\AramiscBackGroundSettingController@colorThemeSet')->name('make-default-theme')->middleware('userRolePermission:make-default-theme');

    Route::get('theme-create', 'Admin\Style\ThemeController@create')->name('theme-create')->middleware('userRolePermission:theme-create');
    Route::post('theme-create-store', 'Admin\Style\ThemeController@store')->name('theme-store')->middleware('userRolePermission:theme-store');
    Route::get('themes/{theme}/copy', 'Admin\Style\ThemeController@copy')->name('themes.copy')->middleware('userRolePermission:themes.copy');
    Route::get('themes/{theme}/default', 'Admin\Style\ThemeController@default')->name('themes.default')->middleware('userRolePermission:themes.default');
    Route::resource('themes', 'Admin\Style\ThemeController');
    //Front Settings Route

    // Header Menu Manager
    Route::get('header-menu-manager', 'Admin\FrontSettings\AramiscHeaderMenuManagerController@index')->name('header-menu-manager')->middleware('userRolePermission:header-menu-manager');
    Route::post('add-element', 'Admin\FrontSettings\AramiscHeaderMenuManagerController@store')->name('add-element')->middleware('userRolePermission:add-element');
    Route::post('reordering', 'Admin\FrontSettings\AramiscHeaderMenuManagerController@reordering')->name('reordering');
    Route::post('element-update', 'Admin\FrontSettings\AramiscHeaderMenuManagerController@update')->name('element-update')->middleware('userRolePermission:element-update');
    Route::post('delete-element', 'Admin\FrontSettings\AramiscHeaderMenuManagerController@delete')->name('delete-element')->middleware('userRolePermission:delete-element');

    // admin-home-page
    Route::get('admin-home-page', 'Admin\FrontSettings\HomePageController@index')->name('admin-home-page')->middleware('userRolePermission:admin-home-page');
    Route::post('admin-home-page-update', 'Admin\FrontSettings\HomePageController@update')->name('admin-home-page-update')->middleware('userRolePermission:admin-home-page-update');
    // News route start
    Route::get('news-heading-update', 'Admin\FrontSettings\NewsHeadingController@index')->name('news-heading-update')->middleware('userRolePermission:news-heading-update');
    Route::post('news-heading-update', 'Admin\FrontSettings\NewsHeadingController@update')->name('news-heading-update-new')->middleware('userRolePermission:news-heading-update');


    // News route start
    Route::get('exam-result-page', 'Admin\FrontSettings\AramiscPageController@examResultPage')->name('exam-result-page')->middleware('userRolePermission:exam-result-page');
    Route::post('exam-page-result-update', 'Admin\FrontSettings\AramiscPageController@examResultPageUpdate')->name('exam-result-page-update');

    //news categroy
    Route::get('news-category', 'Admin\FrontSettings\AramiscNewsCategoryController@index')->name('news-category')->middleware('userRolePermission:news-category');
    Route::post('/news-category-store', 'Admin\FrontSettings\AramiscNewsCategoryController@store')->name('store_news_category')->middleware('userRolePermission:store_news_category');
    Route::get('edit-news-category/{id}', 'Admin\FrontSettings\AramiscNewsCategoryController@edit')->name('edit-news-category')->middleware('userRolePermission:edit-news-category');
    Route::post('/news-category-update', 'Admin\FrontSettings\AramiscNewsCategoryController@update')->name('update_news_category')->middleware('userRolePermission:edit-news-category');
    Route::get('for-delete-news-category/{id}', 'Admin\FrontSettings\AramiscNewsCategoryController@deleteModalOpen')->name('for-delete-news-category')->middleware('userRolePermission:for-delete-news-category');
    Route::get('delete-news-category/{id}', 'Admin\FrontSettings\AramiscNewsCategoryController@delete')->name('delete-news-category');

    // news 

    Route::get('/news', 'Admin\FrontSettings\AramiscNewsController@index')->name('news_index');
    Route::post('/news-store', 'Admin\FrontSettings\AramiscNewsController@store')->name('store_news')->middleware('userRolePermission:store_news');
    Route::post('/news-update', 'Admin\FrontSettings\AramiscNewsController@update')->name('update_news')->middleware('userRolePermission:edit-news');
    Route::get('newsDetails/{id}', 'Admin\FrontSettings\AramiscNewsController@newsDetails')->name('newsDetails')->middleware('userRolePermission:496');
    Route::get('for-delete-news/{id}', 'Admin\FrontSettings\AramiscNewsController@forDeleteNews')->name('for-delete-news')->middleware('userRolePermission:delete-news');
    Route::get('delete-news/{id}', 'Admin\FrontSettings\AramiscNewsController@delete')->name('delete-news');
    Route::get('edit-news/{id}', 'Admin\FrontSettings\AramiscNewsController@edit')->name('edit-news')->middleware('userRolePermission:edit-news');


    // Course route start
    Route::get('course-heading-update', 'Admin\FrontSettings\AramiscCourseHeadingController@index')->name('course-heading-update')->middleware('userRolePermission:course-heading-update');
    Route::post('course-heading-update', 'Admin\FrontSettings\AramiscCourseHeadingController@update')->name('course-heading-updat-new')->middleware('userRolePermission:course-heading-update');

    // Course Details route start
    Route::get('course-details-heading', 'Admin\FrontSettings\AramiscCourseHeadingDetailsController@index')->name('course-details-heading')->middleware('userRolePermission:course-details-heading');
    Route::post('course-heading-details-update', 'Admin\FrontSettings\AramiscCourseHeadingDetailsController@update')->name('course-details-heading-update')->middleware('userRolePermission:course-details-heading');

    //For course module
    Route::get('course-category', 'Admin\FrontSettings\AramiscCourseCategoryController@index')->name('course-category')->middleware('userRolePermission:course-category');
    Route::post('store-course-category', 'Admin\FrontSettings\AramiscCourseCategoryController@store')->name('store-course-category')->middleware('userRolePermission:store-course-category');
    Route::get('edit-course-category/{id}', 'Admin\FrontSettings\AramiscCourseCategoryController@edit')->name('edit-course-category')->middleware('userRolePermission:edit-course-category');
    Route::post('update-course-category', 'Admin\FrontSettings\AramiscCourseCategoryController@update')->name('update-course-category')->middleware('userRolePermission:edit-course-category');
    Route::post('delete-course-category/{id}', 'Admin\FrontSettings\AramiscCourseCategoryController@delete')->name('delete-course-category')->middleware('userRolePermission:delete-course-category');

    //for frontend
    Route::get('view-course-category/{id}', 'Admin\FrontSettings\AramiscCourseCategoryController@view')->name('view-course-category');
    //course List
    Route::get('course-list', 'Admin\FrontSettings\AramiscCourseListController@index')->name('course-list')->middleware('userRolePermission:course-list');
    Route::post('/course-store', 'Admin\FrontSettings\AramiscCourseListController@store')->name('store_course')->middleware('userRolePermission:store_course');
    Route::post('/course-update', 'Admin\FrontSettings\AramiscCourseListController@update')->name('update_course')->middleware('userRolePermission:edit-course');
    Route::get('for-delete-course/{id}', 'Admin\FrontSettings\AramiscCourseListController@forDeleteCourse')->name('for-delete-course')->middleware('userRolePermission:delete-course');
    Route::get('delete-course/{id}', 'Admin\FrontSettings\AramiscCourseListController@destroy')->name('delete-course')->middleware('userRolePermission:delete-course');

    Route::get('edit-course/{id}', 'Admin\FrontSettings\AramiscCourseListController@edit')->name('edit-course')->middleware('userRolePermission:edit-course');
    Route::get('course-Details-admin/{id}', 'Admin\FrontSettings\AramiscCourseListController@courseDetails')->name('course-Details-admin')->middleware('userRolePermission:course-Details-admin');


    //for testimonial
    Route::get('/testimonial', 'Admin\FrontSettings\AramiscTestimonialController@index')->name('testimonial_index')->middleware('userRolePermission:testimonial_index');

    Route::post('/testimonial-store', 'Admin\FrontSettings\AramiscTestimonialController@store')->name('store_testimonial')->middleware('userRolePermission:store_testimonial');
    Route::post('/testimonial-update', 'Admin\FrontSettings\AramiscTestimonialController@update')->name('update_testimonial')->middleware('userRolePermission:edit-testimonial');
    Route::get('testimonial-details/{id}', 'Admin\FrontSettings\AramiscTestimonialController@testimonialDetails')->name('testimonial-details')->middleware('userRolePermission:testimonial-details');
    Route::get('for-delete-testimonial/{id}', 'Admin\FrontSettings\AramiscTestimonialController@forDeleteTestimonial')->name('for-delete-testimonial')->middleware('userRolePermission:for-delete-testimonial');
    Route::get('delete-testimonial/{id}', 'Admin\FrontSettings\AramiscTestimonialController@delete')->name('delete-testimonial');
    Route::get('edit-testimonial/{id}', 'Admin\FrontSettings\AramiscTestimonialController@edit')->name('edit-testimonial')->middleware('userRolePermission:edit-testimonial');

    //for home-slider
    Route::get('/home-slider', 'Admin\FrontSettings\AramiscHomeSliderController@index')->name('home-slider')->middleware('userRolePermission:home-slider');
    Route::post('/home-slider-store', 'Admin\FrontSettings\AramiscHomeSliderController@store')->name('home-slider-store')->middleware('userRolePermission:home-slider-store');
    Route::get('/home-slider-edit/{id}', 'Admin\FrontSettings\AramiscHomeSliderController@edit')->name('home-slider-edit')->middleware('userRolePermission:home-slider-edit');
    Route::post('/home-slider-update', 'Admin\FrontSettings\AramiscHomeSliderController@update')->name('home-slider-update')->middleware('userRolePermission:home-slider-update');
    Route::get('/home-slider-delete-modal/{id}', 'Admin\FrontSettings\AramiscHomeSliderController@deleteModal')->name('home-slider-delete-modal')->middleware('userRolePermission:home-slider-delete-modal');
    Route::get('/home-slider-delete/{id}', 'Admin\FrontSettings\AramiscHomeSliderController@delete')->name('home-slider-delete')->middleware('userRolePermission:home-slider-delete');

    //for expert-teacher
    Route::get('/expert-teacher', 'Admin\FrontSettings\AramiscExpertTeacherController@index')->name('expert-teacher')->middleware('userRolePermission:expert-teacher');
    Route::post('/expert-teacher-store', 'Admin\FrontSettings\AramiscExpertTeacherController@store')->name('expert-teacher-store')->middleware('userRolePermission:expert-teacher-store');
    Route::get('/expert-teacher-edit/{id}', 'Admin\FrontSettings\AramiscExpertTeacherController@edit')->name('expert-teacher-edit')->middleware('userRolePermission:expert-teacher-edit');
    Route::post('/expert-teacher-update', 'Admin\FrontSettings\AramiscExpertTeacherController@update')->name('expert-teacher-update')->middleware('userRolePermission:expert-teacher-update');
    Route::get('/expert-teacher-delete-modal/{id}', 'Admin\FrontSettings\AramiscExpertTeacherController@deleteModal')->name('expert-teacher-delete-modal')->middleware('userRolePermission:expert-teacher-delete-modal');
    Route::get('/expert-teacher-delete/{id}', 'Admin\FrontSettings\AramiscExpertTeacherController@delete')->name('expert-teacher-delete')->middleware('userRolePermission:expert-teacher-delete');

    //for photo-gallery
    Route::get('/photo-gallery', 'Admin\FrontSettings\AramiscPhotoGalleryController@index')->name('photo-gallery')->middleware('userRolePermission:photo-gallery');
    Route::post('/photo-gallery-store', 'Admin\FrontSettings\AramiscPhotoGalleryController@store')->name('photo-gallery-store')->middleware('userRolePermission:photo-gallery-store');
    Route::get('/photo-gallery-edit/{id}', 'Admin\FrontSettings\AramiscPhotoGalleryController@edit')->name('photo-gallery-edit')->middleware('userRolePermission:photo-gallery-edit');
    Route::post('/photo-gallery-update', 'Admin\FrontSettings\AramiscPhotoGalleryController@update')->name('photo-gallery-update')->middleware('userRolePermission:photo-gallery-update');
    Route::get('/photo-gallery-delete-modal/{id}', 'Admin\FrontSettings\AramiscPhotoGalleryController@deleteModal')->name('photo-gallery-delete-modal')->middleware('userRolePermission:photo-gallery-delete-modal');
    Route::get('/photo-gallery-delete/{id}', 'Admin\FrontSettings\AramiscPhotoGalleryController@delete')->name('photo-gallery-delete')->middleware('userRolePermission:photo-gallery-delete');
    Route::get('/photo-gallery-view-modal/{id}', 'Admin\FrontSettings\AramiscPhotoGalleryController@viewModal')->name('photo-gallery-view-modal')->middleware('userRolePermission:photo-gallery-view-modal');

    //for video-gallery
    Route::get('/video-gallery', 'Admin\FrontSettings\AramiscVideoGalleryController@index')->name('video-gallery')->middleware('userRolePermission:video-gallery');
    Route::post('/video-gallery-store', 'Admin\FrontSettings\AramiscVideoGalleryController@store')->name('video-gallery-store')->middleware('userRolePermission:video-gallery-store');
    Route::get('/video-gallery-edit/{id}', 'Admin\FrontSettings\AramiscVideoGalleryController@edit')->name('video-gallery-edit')->middleware('userRolePermission:video-gallery-edit');
    Route::post('/video-gallery-update', 'Admin\FrontSettings\AramiscVideoGalleryController@update')->name('video-gallery-update')->middleware('userRolePermission:video-gallery-update');
    Route::get('/video-gallery-delete-modal/{id}', 'Admin\FrontSettings\AramiscVideoGalleryController@deleteModal')->name('video-gallery-delete-modal')->middleware('userRolePermission:video-gallery-delete-modal');
    Route::get('/video-gallery-delete/{id}', 'Admin\FrontSettings\AramiscVideoGalleryController@delete')->name('video-gallery-delete')->middleware('userRolePermission:video-gallery-delete');
    Route::get('/video-gallery-view-modal/{id}', 'Admin\FrontSettings\AramiscVideoGalleryController@viewModal')->name('video-gallery-view-modal')->middleware('userRolePermission:video-gallery-view-modal');

    //for front-result
    Route::get('/front-result', 'Admin\FrontSettings\AramiscFrontResultController@index')->name('front-result')->middleware('userRolePermission:front-result');
    Route::post('/front-result-store', 'Admin\FrontSettings\AramiscFrontResultController@store')->name('front-result-store')->middleware('userRolePermission:front-result-store');
    Route::get('/front-result-edit/{id}', 'Admin\FrontSettings\AramiscFrontResultController@edit')->name('front-result-edit')->middleware('userRolePermission:front-result-edit');
    Route::post('/front-result-update', 'Admin\FrontSettings\AramiscFrontResultController@update')->name('front-result-update')->middleware('userRolePermission:front-result-update');
    Route::get('/front-result-delete-modal/{id}', 'Admin\FrontSettings\AramiscFrontResultController@deleteModal')->name('front-result-delete-modal')->middleware('userRolePermission:front-result-delete-modal');
    Route::get('/front-result-delete/{id}', 'Admin\FrontSettings\AramiscFrontResultController@delete')->name('front-result-delete')->middleware('userRolePermission:front-result-delete');

    //for front-class-routine
    Route::get('/front-class-routine', 'Admin\FrontSettings\AramiscFrontClassRoutineController@index')->name('front-class-routine')->middleware('userRolePermission:front-class-routine');
    Route::post('/front-class-routine-store', 'Admin\FrontSettings\AramiscFrontClassRoutineController@store')->name('front-class-routine-store')->middleware('userRolePermission:front-class-routine-store');
    Route::get('/front-class-routine-edit/{id}', 'Admin\FrontSettings\AramiscFrontClassRoutineController@edit')->name('front-class-routine-edit')->middleware('userRolePermission:front-class-routine-edit');
    Route::post('/front-class-routine-update', 'Admin\FrontSettings\AramiscFrontClassRoutineController@update')->name('front-class-routine-update')->middleware('userRolePermission:front-class-routine-update');
    Route::get('/front-class-routine-delete-modal/{id}', 'Admin\FrontSettings\AramiscFrontClassRoutineController@deleteModal')->name('front-class-routine-delete-modal')->middleware('userRolePermission:front-class-routine-delete-modal');
    Route::get('/front-class-routine-delete/{id}', 'Admin\FrontSettings\AramiscFrontClassRoutineController@delete')->name('front-class-routine-delete')->middleware('userRolePermission:front-class-routine-delete');

    //for front-exam-routine
    Route::get('/front-exam-routine', 'Admin\FrontSettings\AramiscFrontExamRoutineController@index')->name('front-exam-routine')->middleware('userRolePermission:front-exam-routine');
    Route::post('/front-exam-routine-store', 'Admin\FrontSettings\AramiscFrontExamRoutineController@store')->name('front-exam-routine-store')->middleware('userRolePermission:front-exam-routine-store');
    Route::get('/front-exam-routine-edit/{id}', 'Admin\FrontSettings\AramiscFrontExamRoutineController@edit')->name('front-exam-routine-edit')->middleware('userRolePermission:front-exam-routine-edit');
    Route::post('/front-exam-routine-update', 'Admin\FrontSettings\AramiscFrontExamRoutineController@update')->name('front-exam-routine-update')->middleware('userRolePermission:front-exam-routine-update');
    Route::get('/front-exam-routine-delete-modal/{id}', 'Admin\FrontSettings\AramiscFrontExamRoutineController@deleteModal')->name('front-exam-routine-delete-modal')->middleware('userRolePermission:front-exam-routine-delete-modal');
    Route::get('/front-exam-routine-delete/{id}', 'Admin\FrontSettings\AramiscFrontExamRoutineController@delete')->name('front-exam-routine-delete')->middleware('userRolePermission:front-exam-routine-delete');

    //for front-academic-calendar
    Route::get('/front-academic-calendar', 'Admin\FrontSettings\AramiscAcademicCalendarController@index')->name('front-academic-calendar')->middleware('userRolePermission:front-academic-calendar');
    Route::post('/front-academic-calendar-store', 'Admin\FrontSettings\AramiscAcademicCalendarController@store')->name('front-academic-calendar-store')->middleware('userRolePermission:front-academic-calendar-store');
    Route::get('/front-academic-calendar-edit/{id}', 'Admin\FrontSettings\AramiscAcademicCalendarController@edit')->name('front-academic-calendar-edit')->middleware('userRolePermission:front-academic-calendar-edit');
    Route::post('/front-academic-calendar-update', 'Admin\FrontSettings\AramiscAcademicCalendarController@update')->name('front-academic-calendar-update')->middleware('userRolePermission:front-academic-calendar-update');
    Route::get('/front-academic-calendar-delete-modal/{id}', 'Admin\FrontSettings\AramiscAcademicCalendarController@deleteModal')->name('front-academic-calendar-delete-modal')->middleware('userRolePermission:front-academic-calendar-delete-modal');
    Route::get('/front-academic-calendar-delete/{id}', 'Admin\FrontSettings\AramiscAcademicCalendarController@delete')->name('front-academic-calendar-delete')->middleware('userRolePermission:front-academic-calendar-delete');

    //for speech-slider
    Route::get('/speech-slider', 'Admin\FrontSettings\SpeechSliderController@index')->name('speech-slider')->middleware('userRolePermission:speech-slider');
    Route::post('/speech-slider-store', 'Admin\FrontSettings\SpeechSliderController@store')->name('speech-slider-store')->middleware('userRolePermission:speech-slider-store');
    Route::get('/speech-slider-edit/{id}', 'Admin\FrontSettings\SpeechSliderController@edit')->name('speech-slider-edit')->middleware('userRolePermission:speech-slider-edit');
    Route::post('/speech-slider-update', 'Admin\FrontSettings\SpeechSliderController@update')->name('speech-slider-update')->middleware('userRolePermission:speech-slider-update');
    Route::get('/speech-slider-delete-modal/{id}', 'Admin\FrontSettings\SpeechSliderController@deleteModal')->name('speech-slider-delete-modal')->middleware('userRolePermission:speech-slider-delete-modal');
    Route::get('/speech-slider-delete/{id}', 'Admin\FrontSettings\SpeechSliderController@delete')->name('speech-slider-delete')->middleware('userRolePermission:speech-slider-delete');

    //for donor
    Route::get('/donor', 'Admin\FrontSettings\AramiscDonorController@index')->name('donor')->middleware('userRolePermission:donor');
    Route::post('/donor-store', 'Admin\FrontSettings\AramiscDonorController@store')->name('donor-store')->middleware('userRolePermission:donor-store');
    Route::get('/donor-edit/{id}', 'Admin\FrontSettings\AramiscDonorController@edit')->name('donor-edit')->middleware('userRolePermission:donor-edit');
    Route::post('/donor-update', 'Admin\FrontSettings\AramiscDonorController@update')->name('donor-update')->middleware('userRolePermission:donor-update');
    Route::get('/donor-delete-modal/{id}', 'Admin\FrontSettings\AramiscDonorController@deleteModal')->name('donor-delete-modal')->middleware('userRolePermission:donor-delete-modal');
    Route::get('/donor-delete/{id}', 'Admin\FrontSettings\AramiscDonorController@delete')->name('donor-delete')->middleware('userRolePermission:donor-delete');

    //for form download
    Route::get('/form-download', 'Admin\FrontSettings\AramiscFormDownloadController@index')->name('form-download')->middleware('userRolePermission:form-download');
    Route::post('/form-download-store', 'Admin\FrontSettings\AramiscFormDownloadController@store')->name('form-download-store')->middleware('userRolePermission:form-download-store');
    Route::get('/form-download-edit/{id}', 'Admin\FrontSettings\AramiscFormDownloadController@edit')->name('form-download-edit')->middleware('userRolePermission:form-download-edit');
    Route::post('/form-download-update', 'Admin\FrontSettings\AramiscFormDownloadController@update')->name('form-download-update')->middleware('userRolePermission:form-download-update');
    Route::get('/form-download-delete-modal/{id}', 'Admin\FrontSettings\AramiscFormDownloadController@deleteModal')->name('form-download-delete-modal')->middleware('userRolePermission:form-download-delete-modal');
    Route::get('/form-download-delete/{id}', 'Admin\FrontSettings\AramiscFormDownloadController@delete')->name('form-download-delete')->middleware('userRolePermission:form-download-delete');

    // Contact us
    Route::get('contact-page', 'Admin\FrontSettings\AramiscContactUsController@index')->name('conpactPage')->middleware('userRolePermission:conpactPage');
    Route::get('contact-page/edit', 'Admin\FrontSettings\AramiscContactUsController@edit')->name('contactPageEdit');
    Route::post('contact-page/update', 'Admin\FrontSettings\AramiscContactUsController@update')->name('contactPageStore');

    // contact message
    Route::get('delete-message/{id}', 'AramiscFrontendController@deleteMessage')->name('delete-message')->middleware('userRolePermission:delete-message');



    //Social Media
    Route::get('social-media', 'Admin\FrontSettings\AramiscSocialMediaController@index')->name('social-media')->middleware('userRolePermission:social-media');
    Route::post('social-media-store', 'Admin\FrontSettings\AramiscSocialMediaController@store')->name('social-media-store');
    Route::get('social-media-edit/{id}', 'Admin\FrontSettings\AramiscSocialMediaController@edit')->name('social-media-edit');
    Route::post('social-media-update', 'Admin\FrontSettings\AramiscSocialMediaController@update')->name('social-media-update');
    Route::get('social-media-delete/{id}', 'Admin\FrontSettings\AramiscSocialMediaController@delete')->name('social-media-delete');

    //page
    Route::get('page-list', 'Admin\FrontSettings\AramiscPageController@index')->name('page-list')->middleware('userRolePermission:page-list');
    Route::get('create-page', 'Admin\FrontSettings\AramiscPageController@create')->name('create-page')->middleware('userRolePermission:save-page-data');
    Route::post('save-page-data', 'Admin\FrontSettings\AramiscPageController@store')->name('save-page-data')->middleware('userRolePermission:save-page-data');
    Route::get('edit-page/{id}', 'Admin\FrontSettings\AramiscPageController@edit')->name('edit-page')->middleware('userRolePermission:edit-page');
    Route::post('update-page-data', 'Admin\FrontSettings\AramiscPageController@update')->name('update-page-data')->middleware('userRolePermission:edit-page');

    // about us
    Route::get('about-page', 'Admin\FrontSettings\AboutPageController@index')->name('about-page')->middleware('userRolePermission:about-page');
    Route::get('about-page/edit', 'Admin\FrontSettings\AboutPageController@edit')->name('about-page/edit');
    Route::post('about-page/update', 'Admin\FrontSettings\AboutPageController@update')->name('about-page/update');

    //footer widget
    Route::get('custom-links', 'Admin\FrontSettings\AramiscFooterWidgetController@index')->name('custom-links')->middleware('userRolePermission:custom-links');
    Route::post('custom-links-update', 'Admin\FrontSettings\AramiscFooterWidgetController@update')->name('custom-links-update')->middleware('userRolePermission:custom-links');
    //student class assign -abunayem
    Route::get('student/{id}/assign-class', 'Admin\StudentInfo\AramiscStudentAdmissionController@assignClass')->name('student.assign-class');







    Route::post('student/record-delete', 'Admin\StudentInfo\AramiscStudentAdmissionController@deleteRecord')->name('student.record.delete');
    Route::get('ajax-get-academic', 'Admin\StudentInfo\AramiscStudentAdmissionController@getSchool')
        ->name('get-school');
    Route::post('student/record-store', 'Admin\StudentInfo\AramiscStudentAdmissionController@recordStore')->name('student.record.store');
    Route::get('student/assign-edit/{student_id}/{record_id}', 'Admin\StudentInfo\AramiscStudentAdmissionController@recordEdit')->name('student_assign_edit');
    Route::post('student/record-update', 'Admin\StudentInfo\AramiscStudentAdmissionController@recordUpdate')->name('student.record.update');
    Route::get('student/check-exit', 'Admin\StudentInfo\AramiscStudentAdmissionController@checkExitStudent');


    Route::get('mm', 'Admin\StudentInfo\AramiscStudentAdmissionController@mm');


    //Smart Web system modification
    Route::get('return_exam_view', 'Admin\Examination\AramiscExamController@examView')->name('examView');
    Route::get('subject_mark_sheet', 'Admin\Report\SubjectMarkSheetReportController@index')->name('subjectMarkSheet')->middleware('userRolePermission:subjectMarkSheet');
    Route::post('subject_mark_sheet-search', 'Admin\Report\SubjectMarkSheetReportController@search')->name('subjectMarkSheetSearch')->middleware('userRolePermission:subjectMarkSheet');
    Route::post('subject_mark_sheet-print', 'Admin\Report\SubjectMarkSheetReportController@print')->name('subjectMarkSheetPrint')->middleware('userRolePermission:subjectMarkSheet');


    Route::get('final_mark_sheet', 'Admin\Report\SubjectMarkSheetReportController@finalMarkSheet')->name('finalMarkSheet')->middleware('userRolePermission:exam_schedule');
    Route::post('final_mark_sheet-search', 'Admin\Report\SubjectMarkSheetReportController@finalMarkSheetSearch')->name('finalMarkSheetSearch')->middleware('userRolePermission:exam_schedule');
    Route::post('final_mark_sheet-print', 'Admin\Report\SubjectMarkSheetReportController@finalMarkSheetPrint')->name('finalMarkSheetPrint')->middleware('userRolePermission:exam_schedule');


    Route::get('student_mark_sheet_final', 'Admin\Report\SubjectMarkSheetReportController@studentFinalMarkSheet')->name('studentFinalMarkSheet')->middleware('userRolePermission:exam_schedule_print');
    Route::post('student_mark_sheet_final_search', 'Admin\Report\SubjectMarkSheetReportController@studentFinalMarkSheetSearch')->name('studentFinalMarkSheetSearch')->middleware('userRolePermission:exam_schedule_print');
    Route::post('student_mark_sheet_final_print', 'Admin\Report\SubjectMarkSheetReportController@studentFinalMarkSheetPrint')->name('studentFinalMarkSheetPrint')->middleware('userRolePermission:exam_schedule_print');

    Route::get('view-as-role', 'Admin\Hr\StaffAsParentController@loginAsRole')->name('viewAsRole');
    Route::get('view-as-parent', 'Admin\Hr\StaffAsParentController@loginAsParent')->name('viewAsParent');



    //custom sms setting 
    Route::post('save-custom-sms-setting', 'Admin\SystemSettings\CustomSmsSettingController@store')->name('save-custom-sms-setting')->middleware('userRolePermission:save-custom-sms-setting');
    Route::get('edit-custom-sms-setting/{id}', 'Admin\SystemSettings\CustomSmsSettingController@edit')->name('edit-custom-sms-setting')->middleware('userRolePermission:edit-custom-sms-setting');
    Route::post('update-custom-sms-setting', 'Admin\SystemSettings\CustomSmsSettingController@update')->name('update-custom-sms-setting')->middleware('userRolePermission:edit-custom-sms-setting');
    Route::post('delete-custom-sms-setting', 'Admin\SystemSettings\CustomSmsSettingController@delete')->name('delete-custom-sms-setting')->middleware('userRolePermission:delete-custom-sms-setting');
    Route::post('send-test-sms', 'Admin\SystemSettings\CustomSmsSettingController@testSms')->name('send-test-sms')->middleware('userRolePermission:send-test-sms');


    // Unassigned Student
    Route::get('unassigned-student', ['as' => 'unassigned_student', 'uses' => 'AramiscStudentAdmissionController@unassignedStudent'])->middleware('userRolePermission:unassigned_student');
    Route::get('sorting-student-list/{class_id}', ['as' => 'sorting_student_list', 'uses' => 'AramiscStudentAdmissionController@sortingStudent'])->middleware('userRolePermission:student_list');
    Route::get('sorting-student-section-list/{class_id}/{section_id}', ['as' => 'sorting_student_list_section', 'uses' => 'AramiscStudentAdmissionController@sortingSectionStudent'])->middleware('userRolePermission:student_list');

    Route::get('multi-class-student', 'Admin\StudentInfo\StudentMultiRecordController@multiRecord')->name('student.multi-class-student')->middleware('userRolePermission:student.multi-class-student');

    Route::get('student-multi-record/{student_id}', 'Admin\StudentInfo\StudentMultiRecordController@studentMultiRecord')->name('student.student-multi-record');

    Route::post('student-record-delete', 'Admin\StudentInfo\StudentMultiRecordController@studentRecordDelete')->name('student.multi-record-delete');

    Route::POST('multi-record-store', 'Admin\StudentInfo\StudentMultiRecordController@multiRecordStore')
        ->name('multi-record-store');

    Route::get('delete-student-record', 'Admin\StudentInfo\StudentMultiRecordController@deleteStudentRecord')
        ->name('student.delete-student-record')->middleware('userRolePermission:student.delete-student-record');

    Route::get('student-record-restore/{record_id}', 'Admin\StudentInfo\StudentMultiRecordController@restoreStudentRecord')
        ->name('student-record-restore');

    Route::post('delete-student-record-permanently', 'Admin\StudentInfo\StudentMultiRecordController@studentRecordDeletePermanently')
        ->name('delete-student-record-permanently');

    Route::get('import-staff', [\App\Http\Controllers\ImportController::class, 'index'])->name('import-staff')
        ->middleware('userRolePermission:import-staff');

    Route::post('staff-bulk-store', [\App\Http\Controllers\ImportController::class, 'staffStore'])->name('staff-bulk-store');

    Route::get('lang-file-export/{lang}', 'LanguageController@index')->name('lang-file-export');
    Route::post('file-export', 'LanguageController@export')->name('file-export');
    Route::get('lang-file-import/{lang}', 'LanguageController@importLang')->name('lang-file-import');
    Route::post('file-import', 'LanguageController@import')->name('file-import');
    Route::get('backup-lang/{lang}', 'LanguageController@backupLanguage')->name('backup-lang');



    Route::get('global-section', ['as' => 'global_section', 'uses' => 'Admin\Academics\GlobalSectionController@index'])->middleware('userRolePermission:265');

    Route::post('global-section-store', ['as' => 'global_section_store', 'uses' => 'Admin\Academics\GlobalSectionController@store'])->middleware('userRolePermission:266');
    Route::get('global-section-edit/{id}', ['as' => 'global_section_edit', 'uses' => 'Admin\Academics\GlobalSectionController@edit'])->middleware('userRolePermission:267');
    Route::post('global-section-update', ['as' => 'global_section_update', 'uses' => 'Admin\Academics\GlobalSectionController@update'])->middleware('userRolePermission:267');
    Route::get('global-section-delete/{id}', ['as' => 'global_section_delete', 'uses' => 'Admin\Academics\GlobalSectionController@delete'])->middleware('userRolePermission:268');

    // Class route
    Route::get('global-class', ['as' => 'global_class', 'uses' => 'Admin\Academics\GlobalClassController@index'])->middleware('userRolePermission:261');
    Route::post('global-class-store', ['as' => 'global_class_store', 'uses' => 'Admin\Academics\GlobalClassController@store'])->middleware('userRolePermission:266');
    Route::get('global-class-edit/{id}', ['as' => 'global_class_edit', 'uses' => 'Admin\Academics\GlobalClassController@edit'])->middleware('userRolePermission:263');
    Route::post('global-class-update', ['as' => 'global_class_update', 'uses' => 'Admin\Academics\GlobalClassController@update'])->middleware('userRolePermission:263');
    Route::get('global-class-delete/{id}', ['as' => 'global_class_delete', 'uses' => 'Admin\Academics\GlobalClassController@delete'])->middleware('userRolePermission:264');

    // Subject routes
    Route::get('global-subject', ['as' => 'global_subject', 'uses' => 'Admin\Academics\GlobalSubjectController@index'])->middleware('userRolePermission:global_subject');
    Route::post('global-subject-store', ['as' => 'global_subject_store', 'uses' => 'Admin\Academics\GlobalSubjectController@store'])->middleware('userRolePermission:global_subject_store');
    Route::get('global-subject-edit/{id}', ['as' => 'global_subject_edit', 'uses' => 'Admin\Academics\GlobalSubjectController@edit'])->middleware('userRolePermission:global_subject_edit');
    Route::post('global-subject-update', ['as' => 'global_subject_update', 'uses' => 'Admin\Academics\GlobalSubjectController@update'])->middleware('userRolePermission:global_subject_update');
    Route::get('global-subject-delete/{id}', ['as' => 'global_subject_delete', 'uses' => 'Admin\Academics\GlobalSubjectController@delete'])->middleware('userRolePermission:global_subject_delete');


    //assign subject
    Route::get('global-assign-subject', ['as' => 'global_assign_subject', 'uses' => 'Admin\Academics\GlobalAssignSubjectController@index'])->middleware('userRolePermission:global_assign_subject');

    Route::get('global-assign-subject-create', ['as' => 'global_assign_subject_create', 'uses' => 'Admin\Academics\GlobalAssignSubjectController@create'])->middleware('userRolePermission:global_assign_subject_create');

    Route::post('global-assign-subject-search', ['as' => 'global_assign_subject_search', 'uses' => 'Admin\Academics\GlobalAssignSubjectController@search'])->middleware('userRolePermission:global_assign_subject_search');
    Route::get('global-assign-subject-search', 'Admin\Academics\GlobalAssignSubjectController@create')->name('global-assign-subject-create')->middleware('userRolePermission:global-assign-subject-create');
    Route::post('global-assign-subject-store', 'Admin\Academics\GlobalAssignSubjectController@assignSubjectStore')->name('global_assign-subject-store')->middleware('userRolePermission:global_assign-subject-store');
    Route::get('global-assign-subject-store', 'Admin\Academics\GlobalAssignSubjectController@create');
    Route::post('global-assign-subject', 'Admin\Academics\GlobalAssignSubjectController@assignSubjectFind')->name('global_assign-subject')->middleware('userRolePermission:global_assign-subject');
    Route::get('global-assign-subject-get-by-ajax', 'Admin\Academics\GlobalAssignSubjectController@assignSubjectAjax');
    Route::get('global-get-assigned-subjects', 'Admin\Academics\GlobalAssignSubjectController@loadAssignedSubject')->name('loadAssignedSubject')->middleware('userRolePermission:loadAssignedSubject');;

    Route::post('global-save-assigned-subjects', 'Admin\Academics\GlobalAssignSubjectController@saveAssignedSubject')->name('saveAssignedSubject');

    //Study Material
    Route::get('global-upload-content', 'Admin\Academics\GlobalUploadContentController@index')->name('global-upload-content')->middleware('userRolePermission:global-upload-content');
    Route::post('global-save-upload-content', 'Admin\Academics\GlobalUploadContentController@store')->name('global-save-upload-content')->middleware('userRolePermission:global-save-upload-content');

    //
    Route::get('global-upload-content-edit/{id}', 'Admin\Academics\GlobalUploadContentController@uploadContentEdit')->name('global-upload-content-edit')->middleware('userRolePermission:global-upload-content-edit');
    Route::get('global-upload-content-view/{id}', 'Admin\Academics\GlobalUploadContentController@uploadContentView')->name('global-upload-content-view')->middleware('userRolePermission:global-upload-content-view');
    //
    Route::post('global-update-upload-content', 'Admin\Academics\GlobalUploadContentController@updateUploadContent')->name('global-update-upload-content');
    Route::post('global-delete-upload-content', 'Admin\Academics\GlobalUploadContentController@deleteUploadContent')->name('global-delete-upload-content')->middleware('userRolePermission:global-delete-upload-content');
    Route::get('global-upload-content-modal', 'Admin\Academics\GlobalUploadContentController@studyMaterialModal')->name('studyMaterialModal')->middleware('userRolePermission:studyMaterialModal');
    Route::post('assigned-global-upload-content', 'Admin\Academics\GlobalUploadContentController@studyMaterialAssigned')->name('studyMaterialAssigned')->middleware('userRolePermission:studyMaterialAssigned');

    Route::get('global-exam-type', 'Admin\Academics\GlobalExaminationController@exam_type')->name('global_exam-type')->middleware('userRolePermission:global_exam-type');
    Route::get('global-exam-type-edit/{id}', ['as' => 'global_exam_type_edit', 'uses' => 'Admin\Academics\GlobalExaminationController@exam_type_edit'])->middleware('userRolePermission:global_exam_type_edit');
    Route::post('global-exam-type-store', ['as' => 'global_exam_type_store', 'uses' => 'Admin\Academics\GlobalExaminationController@exam_type_store'])->middleware('userRolePermission:global_exam_type_store');
    Route::post('global-exam-type-update', ['as' => 'global_exam_type_update', 'uses' => 'Admin\Academics\GlobalExaminationController@exam_type_update'])->middleware('userRolePermission:global_exam_type_update');
    Route::get('global-exam-type-delete/{id}', ['as' => 'global_exam_type_delete', 'uses' => 'Admin\Academics\GlobalExaminationController@exam_type_delete'])->middleware('userRolePermission:global_exam_type_delete');

    Route::get('global-exam', 'Admin\Academics\GlobalExamController@index')->name('global-exam')->middleware('userRolePermission:global-exam');
    Route::post('global-exam', 'Admin\Academics\GlobalExamController@store')->name('global-exam-store');
    Route::get('global-exam/{id}', 'Admin\Academics\GlobalExamController@show')->name('global-exam-edit')->middleware('userRolePermission:global-exam-edit');
    Route::put('global-exam/{id}', 'Admin\Academics\GlobalExamController@update')->name('global-exam-update')->middleware('userRolePermission:global-exam-update');
    Route::delete('global-exam/{id}', 'Admin\Academics\GlobalExamController@destroy')->name('global-exam-delete')->middleware('userRolePermission:global-exam-delete');

    Route::get('return_global_exam_view', 'Admin\Academics\GlobalExamController@examView')->name('global-examView')->middleware('userRolePermission:global-examView');

    Route::get('global-assign', 'Admin\Academics\GlobalAssignSubjectController@globalAssign')->name('global-assign')->middleware('userRolePermission:global-assign');
    Route::post('global-save-assigned', 'Admin\Academics\GlobalAssignSubjectController@saveAssignedSubject')->name('globalSaveAssignedSubject')->middleware('userRolePermission:saveAssignedSubject');


    Route::get('complaint-list-datatable', 'DatatableQueryController@complaintDetailsDatatable')->name('complaint_list_datatable');
    Route::get('unassign-student-list-datatable', 'DatatableQueryController@unAssignStudentList')->name('unassign-student-list-datatable');
    Route::get('disable-student-list-datatable', 'DatatableQueryController@disableStudentList')->name('disable-student-list-datatable');
    Route::get('upload-content-list-datatable', 'DatatableQueryController@uploadContentListDatatable')->name('upload-content-list-datatable');
    Route::get('other-download-list-datatable', 'DatatableQueryController@otherDownloadList')->name('other-download-list-datatable');
    Route::get('get-fees-payment-ajax', 'DatatableQueryController@ajaxFeesPayment')->name('ajaxFeesPayment');
    Route::get('get-bank-slip-ajax', 'DatatableQueryController@ajaxBankSlip')->name('ajaxBankSlip');
    Route::get('get-income-list-ajax', 'DatatableQueryController@ajaxIncomeList')->name('ajaxIncomeList');
    Route::get('get-expense-list-ajax', 'DatatableQueryController@ajaxExpenseList')->name('ajaxExpenseList');
    Route::get('pending-leave-request-ajax', 'DatatableQueryController@ajaxPendingLeave')->name('ajaxPendingLeave');

    Route::get('approve-leave-request-ajax', 'DatatableQueryController@ajaxApproveLeave')->name('ajaxApproveLeave');
    Route::get('homework-list-ajax', 'DatatableQueryController@homeworkListAjax')->name('homework-list-ajax')->middleware('userRolePermission:homework-list');
    Route::get('book-list-ajax', 'DatatableQueryController@bookListAjax')->name('book-list-ajax');
    Route::get('all-issed-book-ajax', 'DatatableQueryController@allIssuedBookAjax')->name('all-issed-book-ajax');
    Route::get('item-list-ajax', 'DatatableQueryController@itemsListAjax')->name('item-list-ajax');
    Route::get('item-receive-list-ajax', 'DatatableQueryController@itemReceiveListAjax')->name('item-receive-list-ajax');

    Route::get('student-transport-report-ajax',  'DatatableQueryController@studentTransportReportAjax')->name('studentTransportReportAjax');
    Route::get('graduate-list-ajax',  'DatatableQueryController@graduateListAjax')->name('graduateListAjax');


    Route::get('due_fees_login_permission',  'Admin\FeesCollection\DueFeesLoginPermissionController@index')->name('due_fees_login_permission')->middleware('userRolePermission:due_fees_login_permission');
    Route::post('due_fees_login_permission',  'Admin\FeesCollection\DueFeesLoginPermissionController@search')->name('due_fees_login_permission_search')->middleware('userRolePermission:due_fees_login_permission');
    Route::get('due_fees_login_permission_store',  'Admin\FeesCollection\DueFeesLoginPermissionController@store')->name('due_fees_login_permission_store')->middleware('userRolePermission:due_fees_login_permission');
    Route::get('file_make', function () {
        return $data = $str = file_get_contents('my.txt');
    });


    Route::get('frontend-page-builder',  'PageBuilderController@index')->name('frontend-page-builder');

    Route::get('exam-signature-settings',  'ExamSignatureSettingsController@index')->name('exam-signature-settings');
    Route::post('exam-signature-settings/store',  'ExamSignatureSettingsController@store')->name('exam-signature-settings-store');
    Route::post('exam-signature-settings/update',  'ExamSignatureSettingsController@update')->name('exam-signature-settings-update');

    // Fees Carry Forward
    Route::get('fees-carry-forward-settings-view', [AramiscFeesCarryForwardController::class, 'feesCarryForwardSettingsView'])->name('fees-carry-forward-settings-view');
    Route::get('fees-carry-forward-settings-view-fees-collection', [AramiscFeesCarryForwardController::class, 'feesCarryForwardSettingsView'])->name('fees-carry-forward-settings-view-fees-collection');
    Route::post('fees-carry-forward-settings-store', [AramiscFeesCarryForwardController::class, 'feesCarryForwardSettings'])->name('fees-carry-forward-settings-store');

    Route::get('fees-carry-forward-view', [AramiscFeesCarryForwardController::class, 'feesCarryForward'])->name('fees-carry-forward-view');
    Route::get('fees-carry-forward-view-fees-collection', [AramiscFeesCarryForwardController::class, 'feesCarryForward'])->name('fees-carry-forward-view-fees-collection');
    Route::post('fees-carry-forward-search', [AramiscFeesCarryForwardController::class, 'feesCarryForwardSearch'])->name('fees-carry-forward-search');
    Route::post('fees-carry-forward-store', [AramiscFeesCarryForwardController::class, 'feesCarryForwardStore'])->name('fees-carry-forward-store');

    Route::get('fees-carry-forward-log-view', [AramiscFeesCarryForwardController::class, 'feesCarryForwardLogView'])->name('fees-carry-forward-log-view');
    Route::get('fees-carry-forward-log-view-fees-collection', [AramiscFeesCarryForwardController::class, 'feesCarryForwardLogView'])->name('fees-carry-forward-log-view-fees-collection');
    Route::get('fees-carry-forward-log-search', [AramiscFeesCarryForwardController::class, 'feesCarryForwardLogSearch'])->name('fees-carry-forward-log-search');


    // Teacher Evaluation Settings
    Route::get('teacher-evaluation-setting', [TeacherEvaluationController::class, 'teacherEvaluationSetting'])->name('teacher-evaluation-setting');
    Route::put('teacher-evaluation-setting-update', [TeacherEvaluationController::class, 'teacherEvaluationSettingUpdate'])->name('teacher-evaluation-setting-update');

    // Teacher Evaluation Submit Parent & Student Panel
    Route::post('teacher-evaluation-submit', [TeacherEvaluationController::class, 'teacherEvaluationSubmit'])->name('teacher-evaluation-submit');

    // Teacher Evaluation Reports
    Route::get('get-assign-subject-teacher', [TeacherEvaluationReportController::class, 'getAssignSubjectTeacher'])->name('get-assign-subject-teacher');
    Route::get('teacher-approved-evaluation-report', [TeacherEvaluationReportController::class, 'teacherApprovedEvaluationReport'])->name('teacher-approved-evaluation-report');
    Route::get('teacher-pending-evaluation-report', [TeacherEvaluationReportController::class, 'teacherPendingEvaluationReport'])->name('teacher-pending-evaluation-report');
    Route::get('teacher-wise-evaluation-report', [TeacherEvaluationReportController::class, 'teacherWiseEvaluationReport'])->name('teacher-wise-evaluation-report');

    // Teacher Evaluation Reports Search
    Route::get('teacher-approved-evaluation-report-search', [TeacherEvaluationReportController::class, 'teacherApprovedEvaluationReportSearch'])->name('teacher-approved-evaluation-report-search');
    Route::get('teacher-pending-evaluation-report-search', [TeacherEvaluationReportController::class, 'teacherPendingEvaluationReportSearch'])->name('teacher-pending-evaluation-report-search');
    Route::get('teacher-wise-evaluation-report-search', [TeacherEvaluationReportController::class, 'teacherWiseEvaluationReportSearch'])->name('teacher-wise-evaluation-report-search');

    // Teacher Evaluation Reports Save/Delete
    Route::get('teacher-evaluation-approve-submit/{id}', [TeacherEvaluationReportController::class, 'teacherEvaluationApproveSubmit'])->name('teacher-evaluation-approve-submit');
    Route::get('teacher-evaluation-approve-delete/{id}', [TeacherEvaluationReportController::class, 'teacherEvaluationApproveDelete'])->name('teacher-evaluation-approve-delete');


    Route::get('teacher-panel-evaluation-report', [TeacherEvaluationReportController::class, 'teacherPanelEvaluationReport'])->name('teacher-panel-evaluation-report');

    Route::controller('Admin\Communicate\AramiscEventController')->group(function () {
        Route::get('event', 'index')->name('event')->middleware('userRolePermission:event');
        Route::get('new-design', 'newDesign');
        Route::post('event', 'store')->name('event')->middleware('userRolePermission:event-store');
        Route::get('event/{id}', 'edit')->name('event-edit')->middleware('userRolePermission:event-edit');
        Route::put('event/{id}', 'update')->name('event-update')->middleware('userRolePermission:event-edit');
        Route::get('delete-event-view/{id}', 'deleteEventView')->name('delete-event-view')->middleware('userRolePermission:delete-event-view');
        Route::get('delete-event/{id}', 'deleteEvent')->name('delete-event')->middleware('userRolePermission:delete-event-view');
        Route::get('get-all-event-list', 'getAllEventList')->name('get-all-event-list');
        Route::get('download-event-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/events/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-event-document');
    });

    Route::controller('AramiscAcademicCalendarController')->group(function () {
        Route::get('academic-calendar', 'academicCalendarView')->name('academic-calendar')->middleware('userRolePermission:academic-calendar');
        Route::post('store-academic-calendar-settings', 'storeAcademicCalendarSettings')->name('store-academic-calendar-settings')->middleware('userRolePermission:store-academic-calendar-settings');
    });

    // class/exam routine routes front site
    Route::get('class-exam-routine-page', 'Admin\FrontSettings\AramiscClassExamRoutinePageController@classExamRoutinePage')->name('class-exam-routine-page')->middleware('userRolePermission:class-exam-routine-page');
    Route::post('class-exam-routine-page-update', 'Admin\FrontSettings\AramiscClassExamRoutinePageController@classExamRoutinePageUpdate')->name('class-exam-routine-page-update')->middleware('userRolePermission:class-exam-routine-page-update');

    Route::post('arrange-table-row-position', 'Admin\SystemSettings\AramiscSystemSettingController@arrangeTablePosition');
    Route::get('store-data-test', 'Admin\SystemSettings\AramiscNotificationController@insertdata')->name('store-data');

   

    Route::controller(ThemeManageController::class)->group(function () {
        Route::get('theme/index', 'index')->name('theme.index')->middleware('userRolePermission:theme.index');
        Route::post('theme/upload', 'upload')->name('theme.upload')->middleware('userRolePermission:theme.upload');
        Route::post('theme/install', 'install')->name('theme.install')->middleware('userRolePermission:theme.install');
        Route::post('theme/remove', 'remove')->name('theme.remove')->middleware('userRolePermission:theme.remove');
    });


    Route::controller(PluginController::class)->group(function () {
        Route::get('plugin/tawk-setting', 'tawkSetting')->name('tawkSetting')->middleware('userRolePermission:tawkSetting');
        Route::post('plugin/tawk-setting', 'tawkSettingUpdate')->name('tawkSettingUpdate');
        Route::get('plugin/facebook-messenger-setting', 'messengerSetting')->name('messengerSetting')->middleware('userRolePermission:messengerSetting');
        Route::post('plugin/facebook-messenger-setting', 'messengerSettingUpdate')->name('messengerSettingUpdate');
    });

});

