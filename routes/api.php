<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('db-correction', 'AramiscApiController@dbCorrections');
Route::post('deviceInfo', 'api\ApiAramiscStudentAttendanceController@deviceInfo');
// Route::post('system-disable', 'AramiscApiController@systemDisbale');

// admin section visitor
Route::any('login', 'AramiscApiController@mobileLogin');
Route::get('user-demo', 'AramiscApiController@DemoUser');
Route::any('saas-login', 'AramiscApiController@saasLogin');


Route::any('login', 'AramiscApiController@mobileLogin');

Route::get('user-permission/{role_id}/{school_id}/{is_saas}', 'AramiscApiController@userPermission');

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('auth/logout', 'api\SmAdminController@logout');
});

Route::group(['middleware' => ['XSS', 'auth:api', 'json.response'], 'as' => 'api.'], function () {
    Route::get('send-sms', 'AramiscApiController@SendSMS');
    Route::post('user_delete', 'AramiscApiController@deleteUser');
    Route::get('sync', 'AramiscApiController@sync');
    Route::get('set-fcm-token', 'AramiscApiController@setFcmToken');

    Route::get('privacy-permission/{id}', 'AramiscApiController@privacyPermission');
    Route::get('privacy-permission-status', 'AramiscApiController@privacyPermissionStatus');

    Route::get('force-sample-data/{email}', 'AramiscApiController@sample_data');
    Route::get('migrate/{email}', 'AramiscApiController@sample_migrate');
    Route::get('seed/{email}', 'AramiscApiController@sample_seed');



    // payment process and call back 

    Route::post('payment-data-save', 'api\AramiscPaymentGatewayController@dataSave');
    Route::post('payment-success-callback', 'api\AramiscPaymentGatewayController@successCallback');

    // -------------------Start admin Module------------------
    Route::any('is-enabled', 'AramiscApiController@checkColumnAvailable');


    Route::any('schools', 'AramiscApiController@allSchools');


    Route::get('class-id/{id}', 'AramiscApiController@get_class_name');
    Route::get('school/{school_id}/class-id/{id}', 'AramiscApiController@saas_get_class_name');
    Route::get('section-id/{id}', 'AramiscApiController@get_section_name');
    Route::get('school/{school_id}/section-id/{id}', 'AramiscApiController@saas_get_section_name');
    Route::get('teacher-id/{id}', 'AramiscApiController@get_teacher_name');
    Route::get('school/{school_id}/teacher-id/{id}', 'AramiscApiController@saas_get_teacher_name');
    Route::get('subject-id/{id}', 'AramiscApiController@get_subject_name');
    Route::get('school/{school_id}/subject-id/{id}', 'AramiscApiController@saas_get_subject_name');
    Route::get('room-id/{id}', 'AramiscApiController@get_room_name');
    Route::get('school/{school_id}/room-id/{id}', 'AramiscApiController@saas_get_room_name');
    Route::get('class-period-id/{id}', 'AramiscApiController@get_class_period_name');
    Route::get('school/{school_id}/class-period-id/{id}', 'AramiscApiController@saas_get_class_period_name');


    Route::get('visitor', ['as' => 'visitor', 'uses' => 'AramiscApiController@visitor_index']);
    Route::get('school/{school_id}/visitor', ['as' => 'saas_visitor', 'uses' => 'AramiscApiController@saas_visitor_index']);
    Route::post('visitor-store', ['as' => 'visitor_store', 'uses' => 'AramiscApiController@visitor_store']);
    Route::post('saas-visitor-store', ['as' => 'saas_visitor_store', 'uses' => 'AramiscApiController@saas_visitor_store']);
    Route::get('visitor-edit/{id}', ['as' => 'visitor_edit', 'uses' => 'AramiscApiController@visitor_edit']);
    Route::get('school/{school_id}/visitor-edit/{id}', ['as' => 'saas_visitor_edit', 'uses' => 'AramiscApiController@saas_visitor_edit']);

    Route::post('visitor-update', ['as' => 'visitor_update', 'uses' => 'AramiscApiController@visitor_update']);
    Route::post('saas-visitor-update', ['as' => 'saas_visitor_update', 'uses' => 'AramiscApiController@saas_visitor_update']);
    Route::get('visitor-delete/{id}', ['as' => 'visitor_delete', 'uses' => 'AramiscApiController@visitor_delete']);
    Route::get('school/{school_id}/visitor-delete/{id}', ['as' => 'saas_visitor_delete', 'uses' => 'AramiscApiController@saas_visitor_delete']);




    // admin section complaint
    Route::get('complaint', 'AramiscApiController@complaint');
    Route::post('complaint-store', 'AramiscApiController@complaintStore');


    Route::get('complaint', 'AramiscApiController@complaint_index');
    Route::get('school/{school_id}/complaint', 'AramiscApiController@saas_complaint_index');
    Route::post('complaint-store', 'AramiscApiController@complaint_store');
    Route::post('saas-complaint-store', 'AramiscApiController@saas_complaint_store');
    Route::get('complaint-edit/{id}', 'AramiscApiController@complaint_edit');
    Route::get('school/{school_id}/complaint-edit/{id}', 'AramiscApiController@saas_complaint_edit');
    Route::post('complaint-update', 'AramiscApiController@complaint_update');
    Route::post('saas-complaint-update', 'AramiscApiController@saas_complaint_update');
    Route::get('complaint-delete/{id}', 'AramiscApiController@complaint_update');


    // Admin section postal-receive

    Route::get('postal-receive', 'AramiscApiController@postal_receive_index');
    Route::get('school/{school_id}/postal-receive', 'AramiscApiController@saas_postal_receive_index');
    Route::post('postal-receive-store', 'AramiscApiController@postal_receive_store');
    Route::post('saas-postal-receive-store', 'AramiscApiController@saas_postal_receive_store');
    Route::post('postal-receive-edit/{id}', 'AramiscApiController@postal_receive_show');
    Route::post('school/{school_id}/postal-receive-edit/{id}', 'AramiscApiController@saas_postal_receive_show');
    Route::post('postal-receive-update', 'AramiscApiController@postal_receive_update');
    Route::post('saas-postal-receive-update', 'AramiscApiController@saas_postal_receive_update');
    Route::get('postal-receive-delete/{id}', 'AramiscApiController@postal_receive_destroy');
    Route::get('school/{school_id}/postal-receive-delete/{id}', 'AramiscApiController@saas_postal_receive_destroy');


    // Admin section postal-dispatch
    Route::get('postal-dispatch', 'AramiscApiController@postal_dispatch_index');
    Route::get('school/{school_id}/postal-dispatch', 'AramiscApiController@saas_postal_dispatch_index');
    Route::post('postal-dispatch-store', 'AramiscApiController@postal_dispatch_store');
    Route::post('saas-postal-dispatch-store', 'AramiscApiController@saas_postal_dispatch_store');
    Route::get('postal-dispatch-edit/{id}', 'AramiscApiController@postal_dispatch_show');
    Route::get('school/{school_id}/postal-dispatch-edit/{id}', 'AramiscApiController@saas_postal_dispatch_show');
    Route::post('postal-dispatch-update', 'AramiscApiController@postal_dispatch_update');
    Route::post('saas-postal-dispatch-update', 'AramiscApiController@saas_postal_dispatch_update');
    Route::get('postal-dispatch-delete/{id}', 'AramiscApiController@postal_dispatch_destroy');
    Route::get('school/{school_id}/postal-dispatch-delete/{id}', 'AramiscApiController@saas_postal_dispatch_destroy');
    // Phone Call Log
    Route::resource('phone-call', 'api\ApiSmPhoneCallLogController');

    // Admin Setup
    Route::resource('setup-admin', 'api\ApiSmSetupAdminController');
    Route::get('setup-admin-delete/{id}', 'AramiscApiController@setup_admin_destroy');

    // -------------------End admin Module------------------


    // -----------Start Student Information---------------
    // student list
    Route::get('student-list', ['as' => 'student_list', 'uses' => 'AramiscApiController@studentDetails']);
    Route::get('school/{school_id}/student-list', ['as' => 'saas_student_list', 'uses' => 'AramiscApiController@saas_studentDetails']);

    // student search

    Route::any('student-list-search', 'AramiscApiController@studentDetailsSearch');
    // Route::get('student-list-search', 'AramiscApiController@student_search_Details');
    Route::get('school/{school_id}/student-list-search', 'AramiscApiController@saas_student_search_Details');

    // student list
    Route::get('student-view/{id}', ['as' => 'student_view', 'uses' => 'AramiscApiController@studentView']);
    Route::get('school/{school_id}/student-view/{id}', ['as' => 'saas_student_view', 'uses' => 'AramiscApiController@saas_studentView']);
    // student delete
    Route::any('student-delete', ['as' => 'student_delete', 'uses' => 'AramiscApiController@studentDelete']);
    Route::any('school/{school_id}/student-delete', ['as' => 'saas_student_delete', 'uses' => 'AramiscApiController@saas_studentDelete']);
    // student edit
    Route::get('student-edit/{id}', ['as' => 'student_edit', 'uses' => 'AramiscApiController@studentEdit']);
    Route::get('school/{school_id}/student-edit/{id}', ['as' => 'saas_student_edit', 'uses' => 'AramiscApiController@saas_studentEdit']);


    // Student Attendance
    Route::get('student-attendance', ['as' => 'student_attendance', 'uses' => 'api\ApiAramiscStudentAttendanceController@student_attendance_index']);
    Route::get('school/{school_id}/student-attendance', ['as' => 'saas_student_attendance', 'uses' => 'api\ApiAramiscStudentAttendanceController@saas_student_attendance_index']);
    Route::post('student-search', 'api\ApiAramiscStudentAttendanceController@studentSearch');
    Route::post('school/{school_id}/student-search', 'api\ApiAramiscStudentAttendanceController@saaas_studentSearch');
    Route::get('student-search', 'api\ApiAramiscStudentAttendanceController@student_search_index');
    Route::get('school/{school_id}/student-search', 'api\ApiAramiscStudentAttendanceController@saas_student_search_index');

    Route::post('student-attendance-store', 'api\ApiAramiscStudentAttendanceController@studentAttendanceStore');
    Route::post('saas-student-attendance-store', 'api\ApiAramiscStudentAttendanceController@saas_studentAttendanceStore');

    Route::get('student-attendance-check', 'api\ApiAramiscStudentAttendanceController@studentAttendanceCheck');
    Route::get('school/{school_id}/student-attendance-check', 'api\ApiAramiscStudentAttendanceController@saas_studentAttendanceCheck');
    Route::get('student-attendance-store-first', 'api\ApiAramiscStudentAttendanceController@studentAttendanceStoreFirst');
    Route::get('school/{school_id}/student-attendance-store-first', 'api\ApiAramiscStudentAttendanceController@saas_studentAttendanceStoreFirst');
    Route::get('student-attendance-store-second', 'api\ApiAramiscStudentAttendanceController@studentAttendanceStoreSecond');
    Route::get('school/{school_id}/student-attendance-store-second', 'api\ApiAramiscStudentAttendanceController@saas_studentAttendanceStoreSecond');


    //Subject Wise Attendance

    Route::get('section-subject', 'api\SubjectWiseAttendanceController@SelectSubject');
    Route::get('attendance/search-student', 'api\SubjectWiseAttendanceController@studentSearch');
    Route::get('attendance/store', 'api\SubjectWiseAttendanceController@studentAttendanceStore');

    Route::get('student-subject-attendance-check', 'api\SubjectWiseAttendanceController@studentAttendanceCheck');
    Route::get('student-subject-attendance-store-first', 'api\SubjectWiseAttendanceController@studentAttendanceStoreFirst');
    Route::get('student-subject-attendance-store-second', 'api\SubjectWiseAttendanceController@studentAttendanceStoreSecond');


    // Student Attendance Report
    Route::get('student-attendance-report', ['as' => 'student_attendance_report_api', 'uses' => 'api\ApiAramiscStudentAttendanceController@studentAttendanceReport']);
    Route::get('school/{school_id}/student-attendance-report', ['as' => 'saas_student_attendance_report', 'uses' => 'api\ApiAramiscStudentAttendanceController@saas_studentAttendanceReport']);

    Route::post('student-attendance-report-search', ['as' => 'student_attendance_report_search_api', 'uses' => 'api\ApiAramiscStudentAttendanceController@studentAttendanceReportSearch']);
    Route::post('school/{school_id}/student-attendance-report-search', ['as' => 'saas_student_attendance_report_search', 'uses' => 'api\ApiAramiscStudentAttendanceController@saas_studentAttendanceReportSearch']);
    Route::get('student-attendance-report-search', 'api\ApiAramiscStudentAttendanceController@studentAttendanceReport_search');
    Route::get('school/{school_id}/student-attendance-report-search', 'api\ApiAramiscStudentAttendanceController@saas_studentAttendanceReport_search');

    // Student Category
    Route::get('student-category', ['as' => 'student_category', 'uses' => 'AramiscApiController@student_type_index']);
    Route::get('school/{school_id}/student-category', ['as' => 'saas_student_category', 'uses' => 'AramiscApiController@saas_student_type_index']);
    Route::post('student-category-store', ['as' => 'student_category_store', 'uses' => 'AramiscApiController@student_type_store']);
    Route::post('saas-student-category-store', ['as' => 'saas_student_category_store', 'uses' => 'AramiscApiController@saas_student_type_store']);
    Route::get('student-category-edit/{id}', ['as' => 'student_category_edit', 'uses' => 'AramiscApiController@student_type_edit']);
    Route::get('school/{school_id}/student-category-edit/{id}', ['as' => 'saas_student_category_edit', 'uses' => 'AramiscApiController@saas_student_type_edit']);
    Route::post('student-category-update', ['as' => 'student_category_update', 'uses' => 'AramiscApiController@student_type_update']);
    Route::post('saas-student-category-update', ['as' => 'saas_student_category_update', 'uses' => 'AramiscApiController@saas_student_type_update']);
    Route::get('student-category-delete/{id}', ['as' => 'student_category_delete', 'uses' => 'AramiscApiController@student_type_delete']);
    Route::get('school/{school_id}/student-category-delete/{id}', ['as' => 'saas_student_category_delete', 'uses' => 'AramiscApiController@saas_student_type_delete']);


    // Student Group Routes
    Route::get('student-group', ['as' => 'student_group', 'uses' => 'AramiscApiController@student_group_index']);
    Route::get('school/{school_id}/student-group', ['as' => 'saas_student_group', 'uses' => 'AramiscApiController@saas_student_group_index']);
    Route::post('student-group-store', ['as' => 'student_group_store', 'uses' => 'AramiscApiController@student_group_store']);
    Route::post('saas-student-group-store', ['as' => 'saas_student_group_store', 'uses' => 'AramiscApiController@saas_student_group_store']);
    Route::get('student-group-edit/{id}', ['as' => 'student_group_edit', 'uses' => 'AramiscApiController@student_group_edit']);
    Route::get('school/{school_id}/student-group-edit/{id}', ['as' => 'saas_student_group_edit', 'uses' => 'AramiscApiController@saas_student_group_edit']);
    Route::post('student-group-update', ['as' => 'student_group_update', 'uses' => 'AramiscApiController@student_group_update']);
    Route::post('saas-student-group-update', ['as' => 'saas_student_group_update', 'uses' => 'AramiscApiController@saas_student_group_update']);
    Route::get('student-group-delete/{id}', ['as' => 'student_group_delete', 'uses' => 'AramiscApiController@student_group_delete']);
    Route::get('school/{school_id}/student-group-delete/{id}', ['as' => 'saas_student_group_delete', 'uses' => 'AramiscApiController@saas_student_group_delete']);


    // Student Promote search
    Route::get('student-promote', ['as' => 'student_promote', 'uses' => 'AramiscApiController@studentPromote_index']);
    Route::get('school/{school_id}/student-promote', ['as' => 'saas_student_promote', 'uses' => 'AramiscApiController@saas_studentPromote_index']);

    Route::get('student-current-search', 'AramiscApiController@studentPromote');
    Route::get('school/{school_id}/student-current-search', 'AramiscApiController@saas_studentPromote');
    Route::post('student-current-search', 'AramiscApiController@studentCurrentSearch');
    Route::post('school/{school_id}/student-current-search', 'AramiscApiController@saas_studentCurrentSearch');
    Route::get('view-academic-performance/{id}', 'AramiscApiController@view_academic_performance');


    // // Student Promote Store
    Route::get('student-promote-store', 'AramiscApiController@studentPromote_store');
    Route::get('school/{school_id}/student-promote-store', 'AramiscApiController@saas_studentPromote_store');
    Route::post('student-promote-store', 'AramiscApiController@studentPromoteStore');

    // Disabled Student
    Route::get('disabled-student', ['as' => 'disabled_student', 'uses' => 'AramiscApiController@disabledStudent']);
    Route::get('school/{school_id}/disabled-student', ['as' => 'saas_disabled_student', 'uses' => 'AramiscApiController@saas_disabledStudent']);
    Route::post('disabled-student', ['as' => 'post_disabled_student', 'uses' => 'AramiscApiController@disabledStudentSearch']);
    Route::post('school/{school_id}/disabled-student', ['as' => 'saas_disabled_student_post', 'uses' => 'AramiscApiController@saas_disabledStudentSearch']);
    // -----------End Student Information---------------

    // -------------------Teacher Module------------------
    // Start Upload Content
    Route::get('upload-content', 'AramiscApiController@uploadContentList');
    Route::get('school/{school_id}/upload-content', 'AramiscApiController@saas_uploadContentList');
    Route::post('save-upload-content', 'AramiscApiController@saveUploadContent'); // incomplete for API
    Route::get('delete-upload-content/{id}', 'AramiscApiController@deleteUploadContent');
    Route::get('school/{school_id}/delete-upload-content/{id}', 'AramiscApiController@saas_deleteUploadContent');
    Route::get('upload-content-view/{id}', 'api\ApiAramiscTeacherController@viewContent');

    // Start rest of the routes
    Route::get('assignment-list', 'AramiscApiController@assignmentList');
    Route::get('school/{school_id}/assignment-list', 'AramiscApiController@saas_assignmentList');
    Route::get('study-metarial-list', 'AramiscApiController@studyMetarialList');
    Route::get('school/{school_id}/study-metarial-list', 'AramiscApiController@saas_studyMetarialList');
    Route::get('syllabus-list', 'AramiscApiController@syllabusList');
    Route::get('school/{school_id}/syllabus-list', 'AramiscApiController@saas_syllabusList');
    Route::get('other-download-list', 'AramiscApiController@otherDownloadList');
    Route::get('school/{school_id}/other-download-list', 'AramiscApiController@saas_otherDownloadList');
    // End rest of the routes

    // ------------------- End Teacher Module------------------
    //--------------------HomwWork ----------------------
    Route::get('homework-list', ['as' => 'homework-list', 'uses' => 'api\ApiSmHomeWorkController@homeworkList']);
    Route::get('add-homeworks', 'api\ApiSmHomeWorkController@addHomework');
    Route::post('save-homework-data', ['as' => 'saveHomeworkData', 'uses' => 'api\ApiSmHomeWorkController@saveHomeworkData']);


    // ------------------End HomeWork -----------------


    //--------------- Start Fees Collection --------------

    // Collect Fees
    Route::get('collect-fees', ['as' => 'collect_fees', 'uses' => 'AramiscApiController@collectFees']);
    Route::get('school/{school_id}/collect-fees', ['as' => 'saas_collect_fees', 'uses' => 'AramiscApiController@saas_collectFees']);
    Route::get('fees-collect-student-wise/{id}', ['as' => 'fees_collect_student_wise', 'uses' => 'AramiscApiController@collectFeesStudentApi']);
    Route::get('school/{school_id}/fees-collect-student-wise/{id}', ['as' => 'saas_fees_collect_student_wise', 'uses' => 'AramiscApiController@saas_collectFeesStudentApi']);
    Route::post('collect-fees', ['as' => 'collect_fees_post', 'uses' => 'AramiscApiController@collectFeesSearch']);

    //Search Fees Payment
    Route::get('search-fees-payment', ['as' => 'search_fees_payment', 'uses' => 'AramiscApiController@searchFeesPayment']);
    Route::get('school/{school_id}/search-fees-payment', ['as' => 'saas_search_fees_payment', 'uses' => 'AramiscApiController@saas_searchFeesPayment']);
    Route::post('fees-payment-search', ['as' => 'fees_payment_search_post', 'uses' => 'AramiscApiController@feesPaymentSearch']);
    Route::post('school/{school_id}/fees-payment-search', ['as' => 'saas_fees_payment_search_post', 'uses' => 'AramiscApiController@saas_feesPaymentSearch']);
    Route::get('fees-payment-search', ['as' => 'fees_payment_search', 'uses' => 'AramiscApiController@search_Fees_Payment']);
    Route::get('school/{school_id}/fees-payment-search', ['as' => 'saas_fees_payment_search', 'uses' => 'AramiscApiController@saas_search_Fees_Payment']);

    //Fees Search due
    Route::get('search-fees-due', ['as' => 'search_fees_due', 'uses' => 'AramiscApiController@searchFeesDue']);
    Route::get('school/{school_id}/search-fees-due', ['as' => 'saas_search_fees_due', 'uses' => 'AramiscApiController@saas_searchFeesDue']);
    Route::post('fees-due-search', ['as' => 'fees_due_search', 'uses' => 'AramiscApiController@feesDueSearch']);
    Route::post('school/{school_id}/fees-due-search', ['as' => 'saas_fees_due_search', 'uses' => 'AramiscApiController@saas_feesDueSearch']);
    Route::get('fees-due-search', ['as' => 'fees_due_search_get', 'uses' => 'AramiscApiController@search_FeesDue']);
    Route::get('school/{school_id}/fees-due-search', ['as' => 'saas_fees_due_search_get', 'uses' => 'AramiscApiController@saas_search_FeesDue']);


    // Route::resource('fees-master', 'AramiscFeesMasterController');
    Route::post('fees-master-single-delete', 'AramiscApiController@deleteSingle');
    Route::post('school/{school_id}/fees-master-single-delete', 'AramiscApiController@saas_deleteSingle');
    Route::post('fees-master-group-delete', 'AramiscApiController@deleteGroup');
    Route::post('school/{school_id}/fees-master-group-delete', 'AramiscApiController@saas_deleteGroup');
    Route::get('fees-assign/{id}', ['as' => 'fees_assign', 'uses' => 'AramiscApiController@feesAssign']);
    Route::get('school/{school_id}/fees-assign/{id}', ['as' => 'saas_fees_assign', 'uses' => 'AramiscApiController@saas_feesAssign']);
    Route::get('fees-assign/{id}', ['as' => 'fees_assign_get', 'uses' => 'AramiscApiController@fees_Assign']);
    Route::get('school/{school_id}/fees-assign/{id}', ['as' => 'saas_fees_assign_get', 'uses' => 'AramiscApiController@saas_fees_Assign']);
    Route::post('fees-assign-search', 'AramiscApiController@feesAssignSearch');
    Route::post('school/{school_id}/fees-assign-search', 'AramiscApiController@saas_feesAssignSearch');

    // Fees Master
    Route::get('fees-master-store', ['as' => 'fees_master_add', 'uses' => 'AramiscApiController@feesMasterStore']);
    Route::get('school/{school_id}/fees-master-store', ['as' => 'saas_fees_master_add', 'uses' => 'AramiscApiController@saas_feesMasterStore']);
    Route::get('fees-master-update', ['as' => 'fees_master_update', 'uses' => 'AramiscApiController@feesMasterUpdate']);
    Route::get('school/{school_id}/fees-master-update', ['as' => 'saas_fees_master_update', 'uses' => 'AramiscApiController@saas_feesMasterUpdate']);

    // Fees Group routes
    Route::get('fees-group', ['as' => 'fees_group', 'uses' => 'api\ApiAramiscFeesGroupController@fees_group_index']);
    Route::get('school/{school_id}/fees-group', ['as' => 'saas_fees_group', 'uses' => 'api\ApiAramiscFeesGroupController@saas_fees_group_index']);
    Route::get('fees-group-store', ['as' => 'fees_group_store', 'uses' => 'api\ApiAramiscFeesGroupController@fees_group_store']);
    Route::get('school/{school_id}/fees-group-store', ['as' => 'saas_fees_group_store', 'uses' => 'api\ApiAramiscFeesGroupController@saas_fees_group_store']);
    Route::get('fees-group-edit/{id}', ['as' => 'fees_group_edit', 'uses' => 'api\ApiAramiscFeesGroupController@fees_group_edit']);
    Route::get('school/{school_id}/fees-group-edit/{id}', ['as' => 'saas_fees_group_edit', 'uses' => 'api\ApiAramiscFeesGroupController@saas_fees_group_edit']);
    Route::get('sm-fees-group-update', ['as' => 'fees_group_update', 'uses' => 'api\ApiAramiscFeesGroupController@fees_group_update']);
    Route::get('school/{school_id}/fees-group-update', ['as' => 'saas_fees_group_update', 'uses' => 'api\ApiAramiscFeesGroupController@saas_fees_group_update']);
    Route::post('sm-fees-group-delete', ['as' => 'fees_group_delete', 'uses' => 'api\ApiAramiscFeesGroupController@fees_group_delete']);
    Route::post('school/{school_id}/fees-group-delete', ['as' => 'saas_fees_group_delete', 'uses' => 'api\ApiAramiscFeesGroupController@saas_fees_group_delete']);

    // Fees type routes
    Route::get('fees-type', ['as' => 'fees_type', 'uses' => 'AramiscApiController@fees_type_index']);
    Route::get('school/{school_id}/fees-type', ['as' => 'saas_fees_type', 'uses' => 'AramiscApiController@saas_fees_type_index']);
    Route::post('fees-type-store', ['as' => 'fees_type_store', 'uses' => 'AramiscApiController@fees_type_store']);
    Route::post('saas-fees-type-store', ['as' => 'saas_fees_type_store', 'uses' => 'AramiscApiController@saas_fees_type_store']);
    Route::get('fees-type-edit/{id}', ['as' => 'fees_type_edit', 'uses' => 'AramiscApiController@fees_type_edit']);
    Route::get('school/{school_id}/fees-type-edit/{id}', ['as' => 'saas_fees_type_edit', 'uses' => 'AramiscApiController@saas_fees_type_edit']);
    Route::post('fees-type-update', ['as' => 'fees_type_update', 'uses' => 'AramiscApiController@fees_type_update']);
    Route::post('saas-fees-type-update', ['as' => 'saas_fees_type_update', 'uses' => 'AramiscApiController@saas_fees_type_update']);
    Route::get('fees-type-delete/{id}', ['as' => 'fees_type_delete', 'uses' => 'AramiscApiController@fees_type_delete']);
    Route::get('school/{school_id}/fees-type-delete/{id}', ['as' => 'saas_fees_type_delete', 'uses' => 'AramiscApiController@saas_fees_type_delete']);

    // Fees Discount routes
    Route::get('fees-discount', ['as' => 'fees_discount', 'uses' => 'AramiscApiController@fees_discount_index']);
    Route::get('school/{school_id}/fees-discount', ['as' => 'saas_fees_discount', 'uses' => 'AramiscApiController@saas_fees_discount_index']);
    Route::post('fees-discount-store', ['as' => 'fees_discount_store', 'uses' => 'AramiscApiController@fees_discount_store']);
    Route::post('saas-fees-discount-store', ['as' => 'saas_fees_discount_store', 'uses' => 'AramiscApiController@saas_fees_discount_store']);
    Route::get('fees-discount-edit/{id}', ['as' => 'fees_discount_edit', 'uses' => 'AramiscApiController@fees_discount_edit']);
    Route::get('school/{school_id}/fees-discount-edit/{id}', ['as' => 'saas_fees_discount_edit', 'uses' => 'AramiscApiController@saas_fees_discount_edit']);
    Route::post('fees-discount-update', ['as' => 'fees_discount_update', 'uses' => 'AramiscApiController@fees_discount_update']);
    Route::post('saas-fees-discount-update', ['as' => 'saas_fees_discount_update', 'uses' => 'AramiscApiController@saas_fees_discount_update']);
    Route::get('fees-discount-delete/{id}', ['as' => 'fees_discount_delete', 'uses' => 'AramiscApiController@fees_discount_delete']);
    Route::get('school/{school_id}/fees-discount-delete/{id}', ['as' => 'saas_fees_discount_delete', 'uses' => 'AramiscApiController@saas_fees_discount_delete']);
    Route::get('fees-discount-assign/{id}', ['as' => 'fees_discount_assign', 'uses' => 'AramiscApiController@feesDiscountAssign']);
    Route::get('school/{school_id}/fees-discount-assign/{id}', ['as' => 'saas_fees_discount_assign', 'uses' => 'AramiscApiController@saas_feesDiscountAssign']);
    Route::post('fees-discount-assign-search', 'AramiscApiController@feesDiscountAssignSearch');
    Route::post('school/{school_id}/fees-discount-assign-search', 'AramiscApiController@saas_feesDiscountAssignSearch');
    Route::get('fees-discount-assign-store', 'AramiscApiController@feesDiscountAssignStore');
    Route::get('school/{school_id}/fees-discount-assign-store', 'AramiscApiController@saas_feesDiscountAssignStore');

    Route::get('fees-generate-modal/{amount}/{student_id}/{type}', 'AramiscApiController@feesGenerateModal');
    Route::get('school/{school_id}/fees-generate-modal/{amount}/{student_id}/{type}', 'AramiscApiController@saas_feesGenerateModal');
    Route::get('fees-discount-amount-search', 'AramiscApiController@feesDiscountAmountSearch');
    Route::get('school/{school_id}/fees-discount-amount-search', 'AramiscApiController@saas_feesDiscountAmountSearch');
    // delete fees payment
    Route::post('fees-payment-delete', 'AramiscApiController@feesPaymentDelete');
    Route::post('school/{school_id}/fees-payment-delete', 'AramiscApiController@saas_feesPaymentDelete');

    // Fees carry forward
    Route::get('fees-forward', ['as' => 'fees_forward', 'uses' => 'AramiscApiController@feesForward']);
    Route::get('school/{school_id}/fees-forward', ['as' => 'saas_fees_forward', 'uses' => 'AramiscApiController@saas_feesForward']);
    Route::post('fees-forward-search', 'AramiscApiController@feesForwardSearch');
    Route::post('school/{school_id}/fees-forward-search', 'AramiscApiController@saas_feesForwardSearch');
    Route::get('fees-forward-search', 'AramiscApiController@fees_Forward');
    Route::get('school/{school_id}/fees-forward-search', 'AramiscApiController@saas_fees_Forward');

    Route::post('fees-forward-store', 'AramiscApiController@feesForwardStore');
    Route::post('school/{school_id}/fees-forward-store', 'AramiscApiController@saas_feesForwardStore');
    Route::get('fees-forward-store', 'AramiscApiController@Fees_fward');
    Route::get('school/{school_id}/fees-forward-store', 'AramiscApiController@saas_Fees_fward');

    //--------------- End Fees Collection --------------


    //--------------- Start Accounts Modules --------------

    // Profit of account
    Route::get('profit', ['as' => 'profit', 'uses' => 'AramiscApiController@profit']);
    Route::get('school/{school_id}/profit', ['as' => 'saas_profit', 'uses' => 'AramiscApiController@saas_profit']);
    Route::post('search-profit-by-date', ['as' => 'search_profit_by_date', 'uses' => 'AramiscApiController@searchProfitByDate']);
    Route::post('school/{school_id}/search-profit-by-date', ['as' => 'saas_search_profit_by_date', 'uses' => 'AramiscApiController@saas_searchProfitByDate']);
    Route::get('search-profit-by-date', ['as' => 'search_profit_by_date_get', 'uses' => 'AramiscApiController@Accounts_Profit']);
    Route::get('school/{school_id}/search-profit-by-date', ['as' => 'saas_search_profit_by_date_get', 'uses' => 'AramiscApiController@saas_Accounts_Profit']);

    // add income routes
    Route::get('add-income', ['as' => 'add_income', 'uses' => 'AramiscApiController@income_index']);
    Route::get('school/{school_id}/add-income', ['as' => 'saas_add_income', 'uses' => 'AramiscApiController@saas_income_index']);
    Route::post('add-income-store', ['as' => 'add_income_store', 'uses' => 'AramiscApiController@income_store']);
    Route::post('saas-add-income-store', ['as' => 'saas_add_income_store', 'uses' => 'AramiscApiController@saas_income_store']);
    Route::get('add-income-edit/{id}', ['as' => 'add_income_edit', 'uses' => 'AramiscApiController@income_edit']);
    Route::get('school/{school_id}/add-income-edit/{id}', ['as' => 'saas_add_income_edit', 'uses' => 'AramiscApiController@saas_income_edit']);
    Route::post('add-income-update', ['as' => 'add_income_update', 'uses' => 'AramiscApiController@income_update']);
    Route::post('saas-add-income-update', ['as' => 'saas_add_income_update', 'uses' => 'AramiscApiController@saas_income_update']);
    Route::post('add-income-delete', ['as' => 'add_income_delete', 'uses' => 'AramiscApiController@income_delete']);
    Route::post('school/{school_id}/add-income-delete', ['as' => 'saas_add_income_delete', 'uses' => 'AramiscApiController@saas_income_delete']);

    // Add Expense
    Route::resource('add-expense', 'api\ApiSmAddExpenseController');

    //payment method
    Route::get('payment-method', ['as' => 'payment_method', 'uses' => 'AramiscApiController@payment_index']);
    Route::get('school/{school_id}/payment-method', ['as' => 'saas_payment_method', 'uses' => 'AramiscApiController@saas_payment_index']);
    Route::post('payment-method-store', ['as' => 'payment_method_store', 'uses' => 'AramiscApiController@payment_store']);
    Route::post('saas-payment-method-store', ['as' => 'saas_payment_method_store', 'uses' => 'AramiscApiController@saas_payment_store']);
    Route::get('payment-method-edit/{id}', ['as' => 'payment_method_edit', 'uses' => 'AramiscApiController@payment_edit']);
    Route::get('school/{school_id}/payment-method-edit/{id}', ['as' => 'saas_payment_method_edit', 'uses' => 'AramiscApiController@saas_payment_edit']);
    Route::post('payment-method-update', ['as' => 'payment_method_update', 'uses' => 'AramiscApiController@payment_update']);
    Route::post('saas-payment-method-update', ['as' => 'saas_payment_method_update', 'uses' => 'AramiscApiController@saas_payment_update']);
    Route::get('payment-method-delete/{id}', ['as' => 'payment_method_delete', 'uses' => 'AramiscApiController@payment_delete']);
    Route::get('school/{school_id}/payment-method-delete/{id}', ['as' => 'saas_payment_method_delete', 'uses' => 'AramiscApiController@saas_payment_delete']);

    //--------------- End Accounts Modules --------------


    //--------------- Start Human Resource  --------------

    // staff directory
    Route::get('staff-directory', ['as' => 'staff_directory', 'uses' => 'AramiscApiController@staffList']);
    Route::get('school/{school_id}/staff-directory', ['as' => 'saas_staff_directory', 'uses' => 'AramiscApiController@saas_staffList']);
    Route::get('staff-roles', ['as' => 'staff_roles', 'uses' => 'AramiscApiController@staffRoles']);
    Route::get('school/{school_id}/staff-roles', ['as' => 'saas_staff_roles', 'uses' => 'AramiscApiController@saas_staffRoles']);
    Route::get('staff-list/{role_id}', ['as' => 'staff_dlist', 'uses' => 'AramiscApiController@roleStaffList']);
    Route::get('school/{school_id}/staff-list/{role_id}', ['as' => 'saas_staff_dlist', 'uses' => 'AramiscApiController@saas_roleStaffList']);
    Route::get('staff-view/{id}', ['as' => 'staff_view', 'uses' => 'AramiscApiController@staffView']);
    Route::get('school/{school_id}/staff-view/{id}', ['as' => 'saas_staff_view', 'uses' => 'AramiscApiController@saas_staffView']);
    Route::get('search-staff', 'AramiscApiController@staff_List');
    Route::get('school/{school_id}/search-staff', 'AramiscApiController@saas_staff_List');
    Route::post('search-staff', ['as' => 'searchStaff', 'uses' => 'AramiscApiController@searchStaff']);
    Route::post('school/{school_id}/search-staff', ['as' => 'saas_searchStaff', 'uses' => 'AramiscApiController@saas_searchStaff']);
    Route::get('deleteStaff/{id}', 'AramiscApiController@deleteStaff');
    Route::get('school/{school_id}/deleteStaff/{id}', 'AramiscApiController@saas_deleteStaff');

    //Staff Attendance
    Route::get('staff-attendance', ['as' => 'staff_attendance', 'uses' => 'AramiscApiController@staffAttendance']);
    Route::get('school/{school_id}/staff-attendance', ['as' => 'saas_staff_attendance', 'uses' => 'AramiscApiController@saas_staffAttendance']);
    Route::post('staff-attendance-search', 'AramiscApiController@staffAttendanceSearch');
    Route::post('saas-staff-attendance-search', 'AramiscApiController@saas_staffAttendanceSearch');
    Route::post('staff-attendance-store', 'AramiscApiController@staffAttendanceStore');
    Route::post('saas-staff-attendance-store', 'AramiscApiController@saas_staffAttendanceStore');

    Route::get('staff-attendance-report', ['as' => 'staff_attendance_report', 'uses' => 'AramiscApiController@staffAttendanceReport']);
    Route::get('school/{school_id}/staff-attendance-report', ['as' => 'saas_staff_attendance_report', 'uses' => 'AramiscApiController@saas_staffAttendanceReport']);
    Route::post('staff-attendance-report-search', ['as' => 'staff_attendance_report_search', 'uses' => 'AramiscApiController@staffAttendanceReportSearch']);
    Route::post('school/{school_id}/staff-attendance-report-search', ['as' => 'saas_staff_attendance_report_search', 'uses' => 'AramiscApiController@saas_staffAttendanceReportSearch']);

    // Staff designation
    Route::resource('designation', 'api\ApiSmDesignationController');

    //Department
    Route::resource('department', 'api\ApiSmHumanDepartmentController');
    //--------------- End Human Resource  --------------


    //--------------- Start Leave module --------------

    //Start Approve Leave Request
    Route::get('approve-leave', 'api\ApiSmLeaveController@allAprroveList');
    Route::get('approve-leave/{user_id}', 'api\ApiSmLeaveController@userApproveLeave');
    Route::get('school/{school_id}/approve-leave', 'AramiscApiController@saas_Approve_Leave_index');
    Route::post('approve-leave-store', 'api\ApiSmLeaveController@leaveApprove');
    // Route::post('approve-leave-store', 'AramiscApiController@Approve_Leave_store');
    Route::post('saas-approve-leave-store', 'AramiscApiController@saas_Approve_Leave_store');
    Route::get('approve-leave-edit/{id}', 'AramiscApiController@Approve_Leave_edit');
    Route::get('school/{school_id}/approve-leave-edit/{id}', 'AramiscApiController@saas_Approve_Leave_edit');
    Route::get('staffNameByRole', 'AramiscApiController@staffNameByRole');
    Route::get('school/{school_id}/staffNameByRole', 'AramiscApiController@saas_staffNameByRole');
    Route::post('update-approve-leave', 'AramiscApiController@updateApproveLeave');
    Route::post('school/{school_id}/update-approve-leave', 'AramiscApiController@saas_updateApproveLeave');
    Route::get('view-leave-details-approve/{id}', 'AramiscApiController@viewLeaveDetails');
    Route::get('school/{school_id}/view-leave-details-approve/{id}', 'AramiscApiController@saas_viewLeaveDetails');
    //End Approve Leave Request

    //Start Apply Leave
    Route::get('apply-leave', 'AramiscApiController@apply_leave_index');
    Route::get('school/{school_id}/apply-leave', 'AramiscApiController@saas_apply_leave_index');
    Route::post('apply-leave-store', 'AramiscApiController@apply_leave_store');
    Route::post('saas-apply-leave-store', 'AramiscApiController@saas_apply_leave_store');
    Route::get('apply-leave-edit/{id}', 'AramiscApiController@apply_leave_show');
    Route::get('school/{school_id}/apply-leave-edit/{id}', 'AramiscApiController@saas_apply_leave_show');
    Route::post('apply-leave-update', 'AramiscApiController@apply_leave_update');
    Route::post('saas-apply-leave-update', 'AramiscApiController@saas_apply_leave_update');
    Route::get('view-leave-details-apply/{id}', 'AramiscApiController@view_Leave_Details');
    Route::get('school/{school_id}/view-leave-details-apply/{id}', 'AramiscApiController@saas_view_Leave_Details');
    Route::get('delete-apply-leave/{id}', 'AramiscApiController@apply_leave_destroy');
    Route::get('school/{school_id}/delete-apply-leave/{id}', 'AramiscApiController@saas_apply_leave_destroy');

    //End Apply Leave

    //Student leave
    Route::get('student-apply-leave/{user_id}', 'api\ApiSmLeaveController@studentleaveApply');
    Route::post('student-apply-leave-store', 'api\ApiSmLeaveController@leaveStoreStudent');
    Route::get('school/{school_id}/student-apply-leave', 'Parent\SmParentPanelController@saas_leaveApply');
    Route::get('student-view-leave-details-apply/{id}', 'Parent\SmParentPanelController@viewLeaveDetails');
    Route::get('student-apply-leave-edit/{id}', 'Parent\SmParentPanelController@parentLeaveEdit');
    Route::post('student-apply-leave-update', 'Parent\SmParentPanelController@update');
    // Route::post('student-apply-leave-store', 'Parent\SmParentPanelController@leaveStore');
    Route::get('student-delete-apply-leave/{id}', 'Parent\SmParentPanelController@DeleteLeave');
    Route::get('my-leave-type/{user_id}', 'api\ApiSmLeaveController@myLeaveType');

    //End student leave

    // Staff leave define
    Route::resource('leave-define', 'api\ApiSmLeaveDefineController');

    // Staff leave type
    Route::resource('leave-type', 'api\ApiSmLeaveTypeController');

    //--------------- End Leave module --------------


    //--------------- Start Examination Module--------------

    // Marks Grade
    Route::resource('marks-grade', 'api\ApiSmMarksGradeController');

    //--------------- End Examination Module--------------


    //--------------- Start Academic Module--------------

    // class routine new
    Route::get('class-routine-new', ['as' => 'class_routine_new_api', 'uses' => 'AramiscApiController@classRoutine']);
    Route::get('school/{school_id}/class-routine-new', ['as' => 'saas_class_routine_new', 'uses' => 'AramiscApiController@saas_classRoutine']);

    Route::post('class-routine-new', 'api\ApiSmClassRoutineController@classRoutineSearch');
    Route::post('add-new-class-routine-store', 'api\ApiSmClassRoutineController@addNewClassRoutineStore');
    Route::get('student-routine-view/{student_id}/{record_id}', 'api\ApiSmClassRoutineController@studentClassRoutine');
    Route::get('teacher-routine-view/{techer_id}', 'api\ApiSmClassRoutineController@teacherClassRoutine');
    Route::get('class-routine-view/{user_id}/{record_id}', 'api\ApiSmClassRoutineController@studentClassRoutine')->middleware('subdomain');
    Route::post('day-wise-class-routine', 'api\ApiSmClassRoutineController@dayWiseClassRoutine')->name('dayWise_class_routine');

    Route::post('school/{school_id}/class-routine-new', 'AramiscApiController@saas_classRoutineSearch');

    //assign subject
    Route::get('assign-subject', ['as' => 'assign_subject', 'uses' => 'AramiscApiController@assignSubject']);
    Route::get('school/{school_id}/assign-subject', ['as' => 'saas_assign_subject', 'uses' => 'AramiscApiController@saas_assignSubject']);
    Route::get('assign-subject-create', ['as' => 'assign_subject_create', 'uses' => 'AramiscApiController@assigSubjectCreate']);
    Route::get('school/{school_id}/assign-subject-create', ['as' => 'saas_assign_subject_create', 'uses' => 'AramiscApiController@saas_assigSubjectCreate']);
    Route::post('assign-subject-search', ['as' => 'assign_subject_search', 'uses' => 'AramiscApiController@assignSubjectSearch']);
    Route::post('school/{school_id}/assign-subject-search', ['as' => 'saas_assign_subject_search', 'uses' => 'AramiscApiController@saas_assignSubjectSearch']);
    Route::get('assign-subject-search', 'AramiscApiController@assign_Subject_Create');
    Route::get('school/{school_id}/assign-subject-search', 'AramiscApiController@saas_assign_Subject_Create');
    Route::post('assign-subject-store', 'AramiscApiController@assignSubjectStore');
    Route::post('school/{school_id}/assign-subject-store', 'AramiscApiController@saas_assignSubjectStore');
    Route::get('assign-subject-store', 'AramiscApiController@assignSubject_Create');
    Route::get('school/{school_id}/assign-subject-store', 'AramiscApiController@saas_assignSubject_Create');
    Route::post('assign-subject', 'AramiscApiController@assignSubjectFind');
    Route::post('school/{school_id}/assign-subject', 'AramiscApiController@saas_assignSubjectFind');
    Route::get('assign-subject-get-by-ajax', 'AramiscApiController@assignSubjectAjax');
    Route::get('school/{school_id}/assign-subject-get-by-ajax', 'AramiscApiController@saas_assignSubjectAjax');

    //Assign Class Teacher
    Route::resource('assign-class-teacher', 'api\ApiSmAssignClassTeacherControler');

    // Subject routes
    Route::get('subject', ['as' => 'subject_api', 'uses' => 'AramiscApiController@subject_index']);
    Route::get('library-subject', ['as' => 'library_subject', 'uses' => 'api\AramiscApiBookController@library_subject_index']);
    Route::get('school/{school_id}/subject', ['as' => 'saas_subject', 'uses' => 'AramiscApiController@saas_subject_index']);
    Route::post('subject-store', ['as' => 'subject_store_api', 'uses' => 'AramiscApiController@subject_store']);
    Route::post('saas-subject-store', ['as' => 'saas_subject_store', 'uses' => 'AramiscApiController@saas_subject_store']);
    Route::get('subject-edit/{id}', ['as' => 'subject_edit_api', 'uses' => 'AramiscApiController@subject_edit']);
    Route::get('school/{school_id}/subject-edit/{id}', ['as' => 'saas_subject_edit', 'uses' => 'AramiscApiController@saas_subject_edit']);
    Route::post('subject-update', ['as' => 'subject_update_api', 'uses' => 'AramiscApiController@subject_update']);
    Route::post('saas-subject-update', ['as' => 'saas_subject_update', 'uses' => 'AramiscApiController@saas_subject_update']);
    Route::get('subject-delete/{id}', ['as' => 'subject_delete_api', 'uses' => 'AramiscApiController@subject_delete']);
    Route::get('school/{school_id}/subject-delete/{id}', ['as' => 'saas_subject_delete', 'uses' => 'AramiscApiController@saas_subject_delete']);

    // Class route
    Route::get('class', ['as' => 'class_api', 'uses' => 'AramiscApiController@class_index']);
    Route::get('school/{school_id}/class', ['as' => 'saas_class', 'uses' => 'AramiscApiController@saas_class_index']);
    Route::post('class-store', ['as' => 'class_store_api', 'uses' => 'AramiscApiController@class_store']);
    Route::post('saas-class-store', ['as' => 'saas_class_store', 'uses' => 'AramiscApiController@saas_class_store']);
    Route::get('class-edit/{id}', ['as' => 'class_edit_api', 'uses' => 'AramiscApiController@class_edit']);
    Route::get('school/{school_id}/class-edit/{id}', ['as' => 'saas_class_edit', 'uses' => 'AramiscApiController@saas_class_edit']);
    Route::post('class-update', ['as' => 'class_update_api', 'uses' => 'AramiscApiController@class_update']);
    Route::post('saas-class-update', ['as' => 'saas_class_update', 'uses' => 'AramiscApiController@saas_class_update']);
    Route::get('class-delete/{id}', ['as' => 'class_delete_api', 'uses' => 'AramiscApiController@class_delete']);
    Route::get('school/{school_id}/class-delete/{id}', ['as' => 'saas_class_delete', 'uses' => 'AramiscApiController@saas_class_delete']);

    //Class Section routes
    Route::get('section', ['as' => 'section_api', 'uses' => 'AramiscApiController@Section_index']);
    Route::get('school/{school_id}/section', ['as' => 'saas_section', 'uses' => 'AramiscApiController@saas_Section_index']);
    Route::post('saas-section-store', ['as' => 'saas_section_store', 'uses' => 'AramiscApiController@Section_store']);
    Route::post('section-store', ['as' => 'section_store_api', 'uses' => 'AramiscApiController@saas_Section_store']);
    Route::get('section-edit/{id}', ['as' => 'section_edit_api', 'uses' => 'AramiscApiController@Section_edit']);
    Route::get('school/{school_id}/section-edit/{id}', ['as' => 'saas_section_edit', 'uses' => 'AramiscApiController@saas_Section_edit']);
    Route::post('section-update', ['as' => 'section_update_api', 'uses' => 'AramiscApiController@Section_update']);
    Route::post('saas-section-update', ['as' => 'saas_section_update', 'uses' => 'AramiscApiController@saas_Section_update']);
    Route::get('section-delete/{id}', ['as' => 'section_delete_api', 'uses' => 'AramiscApiController@Section_delete']);
    Route::get('school/{school_id}/section-delete/{id}', ['as' => 'saas_section_delete', 'uses' => 'AramiscApiController@saas_Section_delete']);


    // Class room
    Route::resource('class-room', 'api\ApiSmClassRoomController');

    //class time
    Route::resource('class-time', 'api\ApiSmClassTimeController');


    //class routine
    Route::get('student-class-routine/{id}', 'AramiscApiController@class_Routine');
    Route::get('school/{school_id}/student-class-routine/{id}', 'AramiscApiController@saas_class_Routine');
    //--------------- End Academic Module--------------


    //--------------- Start Homework Module--------------
    //homework list
    Route::get('homework-list/{user_id}', ['uses' => 'api\ApiSmHomeWorkController@homeworkList']);
    Route::get('school/{school_id}/homework-list', ['as' => 'saas_homework-list', 'uses' => 'api\ApiSmHomeWorkController@saas_homeworkList']);
    Route::post('homework-list', ['as' => 'homework-list_post', 'uses' => 'api\ApiSmHomeWorkController@searchHomework']);
    Route::post('school/{school_id}/homework-list', ['as' => 'saas_homework-list_post', 'uses' => 'AramiscApiController@saas_searchHomework']);
    Route::get('evaluation-homework/{class_id}/{section_id}/{homework_id}', ['as' => 'evaluation-homework', 'uses' => 'api\ApiSmHomeWorkController@evaluationHomework']);

    Route::get('school/{school_id}/evaluation-homework/{class_id}/{section_id}/{homework_id}', ['as' => 'saas-evaluation-homework', 'uses' => 'api\ApiSmHomeWorkController@saas_evaluationHomework']);

    Route::post('evaluate-homework', ['as' => 'evaluate-homework', 'uses' => 'api\ApiSmHomeWorkController@saveHomeworkEvaluationData']);
    Route::post('school/{school_id}/evaluate-homework', ['as' => 'saas-evaluate-homework', 'uses' => 'api\ApiSmHomeWorkController@saasSaveHomeworkEvaluationData']);


    Route::any('add-homework', 'api\ApiSmHomeWorkController@addHomework');
    Route::post('update-homework', 'api\ApiSmHomeWorkController@homeworkUpdate');
    Route::any('saas-add-homework', 'api\ApiSmHomeWorkController@saas_addHomework');
    Route::get('school/{school_id}/homework-list/{id}', 'api\ApiSmHomeWorkController@saas_homework_List_Teacher');
    //--------------- End Homework Module--------------


    //--------------- Start Communicate Module --------------
    // Communicate
    Route::get('notice-list', 'AramiscApiController@noticeList');
    Route::get('school/{school_id}/notice-list', 'AramiscApiController@saas_noticeList');
    Route::get('send-message', 'AramiscApiController@sendMessage');
    Route::get('school/{school_id}/send-message', 'AramiscApiController@saas_sendMessage');
    Route::post('save-notice-data', 'AramiscApiController@saveNoticeData');
    Route::post('saas-save-notice-data', 'AramiscApiController@saas_saveNoticeData');
    Route::get('edit-notice/{id}', 'AramiscApiController@editNotice');
    Route::get('school/{school_id}/edit-notice/{id}', 'AramiscApiController@saas_editNotice');
    Route::post('update-notice-data', 'AramiscApiController@updateNoticeData');
    Route::post('saas-update-notice-data', 'AramiscApiController@saas_updateNoticeData');
    Route::get('delete-notice-view/{id}', 'AramiscApiController@deleteNoticeView');
    Route::get('school/{school_id}/delete-notice-view/{id}', 'AramiscApiController@saas_deleteNoticeView');
    Route::get('send-email-sms-view', 'AramiscApiController@sendEmailSmsView');
    Route::get('school/{school_id}/send-email-sms-view', 'AramiscApiController@saas_sendEmailSmsView');
    Route::get('delete-notice/{id}', 'AramiscApiController@deleteNotice');
    Route::get('school/{school_id}/delete-notice/{id}', 'AramiscApiController@saas_deleteNotice');

    //Event
    Route::resource('event', 'api\ApiAramiscEventController');
    Route::get('delete-event-view/{id}', 'AramiscApiController@deleteEventView');
    Route::get('school/{school_id}/delete-event-view/{id}', 'AramiscApiController@saas_deleteEventView');
    Route::get('delete-event/{id}', 'AramiscApiController@deleteEvent');
    Route::get('school/{school_id}/delete-event/{id}', 'AramiscApiController@saas_deleteEvent');

    //--------------- Start Communicate Module --------------


    //--------------- Start Library Module --------------

    // Book
    Route::get('book-list', 'api\ApiAramiscBookController@Library_index');
    Route::get('library-subject', ['as' => 'library_subject', 'uses' => 'api\ApiAramiscBookController@library_subject_index']);
    Route::get('school/{school_id}/book-list', 'api\ApiAramiscBookController@saas_Library_index');
    // Route::get('add-book', 'AramiscBookController@addBook');
    Route::post('save-book-data', 'api\ApiAramiscBookController@saveBookData');
    Route::post('saas-save-book-data', 'api\ApiAramiscBookController@saas_saveBookData');
    Route::get('edit-book/{id}', 'api\ApiAramiscBookController@editBook');
    Route::get('school/{school_id}/edit-book/{id}', 'api\ApiAramiscBookController@saas_editBook');
    Route::post('update-book-data/{id}', 'api\ApiAramiscBookController@updateBookData');
    Route::post('saas-update-book-data/{id}', 'api\ApiAramiscBookController@saas_updateBookData');
    Route::get('delete-book-view/{id}', 'api\ApiAramiscBookController@deleteBookView');
    Route::get('school/{school_id}/delete-book-view/{id}', 'api\ApiAramiscBookController@saas_deleteBookView');
    Route::get('delete-book/{id}', 'api\ApiAramiscBookController@deleteBook');
    Route::get('school/{school_id}/delete-book/{id}', 'api\ApiAramiscBookController@saas_deleteBook');
    Route::get('member-list', 'AramiscApiController@memberList');
    Route::get('school/{school_id}/member-list', 'AramiscApiController@saas_memberList');
    Route::get('issue-books/{member_type}/{id}', 'AramiscApiController@issueBooks');
    Route::get('school/{school_id}/issue-books/{member_type}/{id}', 'AramiscApiController@saas_issueBooks');
    Route::post('save-issue-book-data', 'AramiscApiController@saveIssueBookData');
    Route::post('saas-save-issue-book-data', 'AramiscApiController@saas_saveIssueBookData');
    Route::get('return-book-view/{id}', 'AramiscApiController@returnBookView');
    Route::get('school/{school_id}/return-book-view/{id}', 'AramiscApiController@saas_returnBookView');
    Route::get('return-book/{id}', 'AramiscApiController@returnBook');
    Route::get('school/{school_id}/return-book/{id}', 'AramiscApiController@saas_returnBook');
    Route::get('all-issed-book', 'AramiscApiController@allIssuedBook');
    Route::get('school/{school_id}/all-issed-book', 'AramiscApiController@saas_allIssuedBook');
    Route::get('search-issued-book', 'AramiscApiController@searchIssuedBook');
    Route::get('school/{school_id}/search-issued-book', 'AramiscApiController@saas_searchIssuedBook');
    Route::get('search-issued-book', 'AramiscApiController@all_IssuedBook');
    Route::get('school/{school_id}/search-issued-book', 'AramiscApiController@saas_all_IssuedBook');

    //library member
    Route::resource('library-member', 'api\ApiSmLibraryMemberController');
    Route::post('add-library-member', 'api\ApiSmLibraryMemberController@library_member_store');
    Route::post('saas-add-library-member', 'api\ApiSmLibraryMemberController@saas_library_member_store');
    Route::get('library-member-role', 'AramiscApiController@member_role');
    Route::get('school/{school_id}/library-member-role', 'AramiscApiController@saas_member_role');
    Route::get('cancel-membership/{id}', 'AramiscApiController@cancelMembership');
    Route::get('school/{school_id}/cancel-membership/{id}', 'AramiscApiController@saas_cancelMembership');

    //--------------- End Library Module --------------


    //-----------------Start Inventory Module------------------------

    //Item Category
    Route::resource('item-category', 'api\ApiAramiscItemCategoryController');
    Route::get('delete-item-category-view/{id}', 'AramiscApiController@deleteItemCategoryView');
    Route::get('school/{school_id}/delete-item-category-view/{id}', 'AramiscApiController@saas_deleteItemCategoryView');
    Route::get('delete-item-category/{id}', 'AramiscApiController@deleteItemCategory');
    Route::get('school/{school_id}/delete-item-category/{id}', 'AramiscApiController@saas_deleteItemCategory');

    //Item List
    Route::resource('item-list', 'api\ApiAramiscItemController');
    Route::get('delete-item-view/{id}', 'AramiscApiController@deleteItemView');
    Route::get('delete-item/{id}', 'AramiscApiController@deleteItem');

    //Item Store
    Route::resource('item-store', 'api\ApiAramiscItemStoreController');
    Route::get('delete-store-view/{id}', 'AramiscApiController@deleteStoreView');
    Route::get('delete-store/{id}', 'AramiscApiController@deleteStore');

    //Supplier
    Route::resource('suppliers', 'api\ApiSmSupplierController');
    Route::get('delete-supplier-view/{id}', 'AramiscApiController@deleteSupplierView');
    Route::get('delete-supplier/{id}', 'AramiscApiController@deleteSupplier');

    //Issue Item
    Route::get('item-issue', 'AramiscApiController@itemIssueList');
    Route::post('save-item-issue-data', 'AramiscApiController@saveItemIssueData');
    Route::get('getItemByCategory', 'AramiscApiController@getItemByCategory');
    Route::get('return-item-view/{id}', 'AramiscApiController@returnItemView');
    Route::get('return-item/{id}', 'AramiscApiController@returnItem');
    //-----------------End Inventory Module------------------------


    //------------------Start Transport Module--------------

    //routes
    Route::resource('transport-route', 'api\ApiAramiscRouteController');
    Route::resource('saas-transport-route', 'api\SaasRouteController');

    //Vehicle
    Route::resource('vehicle', 'api\ApiSmSmVehicleController');
    Route::resource('saas-vehicle', 'api\SaasVehicleController');

    //Assign Vehicle
    Route::resource('assign-vehicle', 'api\ApiSmAssignVehicleController');
    Route::post('assign-vehicle-delete', 'AramiscApiController@Assign_Vehicle_delete');
    Route::post('school/{school_id}/assign-vehicle-delete', 'AramiscApiController@saas_Assign_Vehicle_delete');

    // student transport report
    Route::get('student-transport-report', ['as' => 'student_transport_report', 'uses' => 'AramiscApiController@studentTransportReportApi']);
    Route::get('school/{school_id}/student-transport-report', ['as' => 'saas_student_transport_report', 'uses' => 'AramiscApiController@saas_studentTransportReportApi']);

    //Route::get('student-transport-reportApi', ['as' => 'student_transport_report', 'uses' => 'SmTransportController@studentTransportReportApi']);


    Route::post('student-transport-report', ['as' => 'student_transport_report_post', 'uses' => 'AramiscApiController@studentTransportReportSearch']);
    Route::post('school/{school_id}/student-transport-report', ['as' => 'saas_student_transport_report_post', 'uses' => 'AramiscApiController@saas_studentTransportReportSearch']);
    //------------------End Transport Module--------------


    // ---------------Start Dormitory Module-----------------

    //Room list
    Route::resource('room-list', 'api\ApiAramiscRoomListController');

    //Room Type
    Route::resource('room-type', 'api\ApiAramiscRoomTypeController');

    //Dormitory List
    Route::resource('dormitory-list', 'api\ApiAramiscDormitoryListController');

    // Student Dormitory Report
    Route::get('student-dormitory-report', ['as' => 'student_dormitory_report', 'uses' => 'AramiscApiController@studentDormitoryReport']);
    Route::get('school/{school_id}/student-dormitory-report', ['as' => 'saas_student_dormitory_report', 'uses' => 'AramiscApiController@saas_studentDormitoryReport']);
    Route::post('student-dormitory-report', ['as' => 'student_dormitory_report_post', 'uses' => 'AramiscApiController@studentDormitoryReportSearch']);
    Route::post('school/{school_id}/student-dormitory-report', ['as' => 'saas_student_dormitory_report_post', 'uses' => 'api\ApiAramiscDormitoryListController@saas_studentDormitoryReportSearch']);

    // ---------------End Dormitory Module-----------------


    //------------- Start Report Module---------------------

    //Student Report
    Route::get('student-report', ['as' => 'student_report', 'uses' => 'AramiscApiController@studentReport']);
    Route::post('student-report', ['as' => 'saas_student_report', 'uses' => 'AramiscApiController@studentReportSearch']);

    //guardian report
    Route::get('guardian-report', ['as' => 'guardian_report', 'uses' => 'AramiscApiController@guardianReport']);
    Route::post('guardian-report-search', ['as' => 'guardian_report_search_post', 'uses' => 'AramiscApiController@guardianReportSearch']);
    Route::get('guardian-report-search', ['as' => 'guardian_report_search', 'uses' => 'AramiscApiController@guardian_Report']);

    //Student history
    Route::get('student-history', ['as' => 'student_history', 'uses' => 'AramiscApiController@studentHistory']);
    Route::post('student-history-search', ['as' => 'student_history_search_post', 'uses' => 'AramiscApiController@studentHistorySearch']);
    Route::get('student-history-search', ['as' => '_post', 'uses' => 'AramiscApiController@student_History']);

    // student login report
    Route::get('student-login-report', ['as' => 'student_login_report', 'uses' => 'AramiscApiController@studentLoginReport']);
    Route::post('student-login-search', ['as' => 'student_login_search_post', 'uses' => 'AramiscApiController@studentLoginSearch']);
    Route::get('student-login-search', ['as' => 'student_login_search_repost', 'uses' => 'AramiscApiController@student_Login_Report']);

    // student & parent reset password
    Route::post('reset-student-password', 'AramiscApiController@resetStudentPassword');

    //Fees Statement
    Route::get('fees-statement', ['as' => 'fees_statement', 'uses' => 'AramiscApiController@feesStatemnt']);
    Route::post('fees-statement-search', ['as' => 'fees_statement_search', 'uses' => 'AramiscApiController@feesStatementSearch']);

    // Balance fees report
    Route::get('balance-fees-report', ['as' => 'balance_fees_report', 'uses' => 'AramiscApiController@balanceFeesReport']);
    Route::post('balance-fees-search', ['as' => 'balance_fees_search_post', 'uses' => 'AramiscApiController@balanceFeesSearch']);
    Route::get('balance-fees-search', ['as' => 'balance_fees_search', 'uses' => 'AramiscApiController@balance_Fees_Report']);

    // Transaction Report
    Route::get('transaction-report', ['as' => 'transaction_report', 'uses' => 'AramiscApiController@transactionReport']);
    Route::post('transaction-report-search', ['as' => 'transaction_report_search_post', 'uses' => 'AramiscApiController@transactionReportSearch']);
    Route::get('transaction-report-search', ['as' => 'transaction_report_search', 'uses' => 'AramiscApiController@transaction_Report']);

    // Class Report
    Route::get('class-report', ['as' => 'class_report', 'uses' => 'AramiscApiController@classReport']);
    Route::post('class-report', ['as' => 'class_report_post', 'uses' => 'AramiscApiController@classReportSearch']);

    // class routine report
    Route::get('class-routine-report', ['as' => 'class_routine_report', 'uses' => 'AramiscApiController@classRoutineReport']);
    Route::post('class-routine-report', 'api\ApiSmClassRoutineController@classRoutineReportSearch');

    // exam routine student
    Route::get('student-exam-schedule/{student_id}', ['as' => 'student-exam-schedule', 'uses' => 'api\ApiAramiscExamRoutineController@studentRoutine']);
    Route::post('student-exam-schedule', ['as' => 'student-exam-schedule-search', 'uses' => 'api\ApiAramiscExamRoutineController@studentExamRoutineSearch']);
    // exam routine report
    Route::get('exam-routine-report', ['as' => 'exam_routine_report', 'uses' => 'AramiscApiController@examRoutineReport']);
    Route::post('exam-routine-report', ['as' => 'exam_routine_report_post', 'uses' => 'api\ApiAramiscExamRoutineController@examRoutineReportSearch']);

    //teacher class routine report
    Route::get('teacher-class-routine-report', ['as' => 'teacher_class_routine_report', 'uses' => 'AramiscApiController@teacherClassRoutineReport']);
    Route::post('teacher-class-routine-report', 'api\ApiSmClassRoutineController@teacherClassRoutineReportSearch');

    // merit list Report
    Route::get('merit-list-report', ['as' => 'merit_list_report', 'uses' => 'AramiscApiController@meritListReport']);
    Route::post('merit-list-report', ['as' => 'merit_list_report_post', 'uses' => 'AramiscApiController@meritListReportSearch']);

    // online exam report
    Route::get('online-exam-report', ['as' => 'online_exam_report', 'uses' => 'AramiscApiController@onlineExamReport']);
    Route::post('online-exam-report', ['as' => 'online_exam_report_post', 'uses' => 'AramiscApiController@onlineExamReportSearch']);

    //mark sheet report student
    Route::get('mark-sheet-report-student', ['as' => 'mark_sheet_report_student', 'uses' => 'AramiscApiController@markSheetReportStudent']);
    Route::post('mark-sheet-report-student', ['as' => 'mark_sheet_report_student_post', 'uses' => 'AramiscApiController@markSheetReportStudentSearch']);

    //mark sheet report student
    Route::get('mark-sheet-report-student', ['as' => 'mark_sheet_report_student', 'uses' => 'AramiscApiController@markSheetReport_Student']);
    Route::post('mark-sheet-report-student', ['as' => 'mark_sheet_report_student_post', 'uses' => 'AramiscApiController@markSheetReportStudent_Search']);

    // Tabulation Sheet Report
    Route::get('tabulation-sheet-report', ['as' => 'tabulation_sheet_report', 'uses' => 'AramiscApiController@tabulationSheetReport']);
    Route::post('tabulation-sheet-report', ['as' => 'tabulation_sheet_report_post', 'uses' => 'AramiscApiController@tabulationSheetReportSearch']);

    // progress card report
    Route::get('progress-card-report', ['as' => 'progress_card_report', 'uses' => 'AramiscApiController@progressCardReport']);
    Route::post('progress-card-report', ['as' => 'progress_card_report_post', 'uses' => 'AramiscApiController@progressCardReportSearch']);

    //student fine report
    Route::get('student-fine-report', ['as' => 'student_fine_report', 'uses' => 'AramiscApiController@studentFineReport']);
    Route::post('student-fine-report', ['as' => 'student_fine_report_post', 'uses' => 'AramiscApiController@studentFineReportSearch']);

    //user log
    Route::get('user-log', ['as' => 'user_log', 'uses' => 'AramiscApiController@userLog']);
    //------------- End Report Module---------------------


    //------------Start System Settings Module--------------

    //General Settings
    Route::get('general-settings/{school_id}', 'AramiscApiController@generalSettingsView');
    Route::get('update-general-settings', 'AramiscApiController@updateGeneralSettings');
    Route::post('update-general-settings-data', 'AramiscApiController@updateGeneralSettingsData');
    Route::post('update-school-logo', 'AramiscApiController@updateSchoolLogo');

    //Role Setup
    Route::get('system-role', ['as' => 'system-role', 'uses' => 'AramiscApiController@systemRole']);

    Route::get('role', ['as' => 'role_api', 'uses' => 'AramiscApiController@role_index']);
    Route::post('role-store', ['as' => 'role_store_api', 'uses' => 'AramiscApiController@role_store']);
    Route::get('role-edit/{id}', ['as' => 'role_edit_api', 'uses' => 'AramiscApiController@role_edit']);
    Route::post('role-update', ['as' => 'role_update_api', 'uses' => 'AramiscApiController@role_update']);
    Route::post('role-delete', ['as' => 'role_delete_api', 'uses' => 'AramiscApiController@role_delete']);

    // Role Permission
    Route::get('assign-permission/{id}', ['as' => 'assign_permission', 'uses' => 'AramiscApiController@assignPermission']);
    Route::post('role-permission-store', ['as' => 'role_permission_store', 'uses' => 'AramiscApiController@rolePermissionStore']);

    // Base group
    Route::get('base-group', ['as' => 'base_group', 'uses' => 'AramiscApiController@base_group_index']);
    Route::post('base-group-store', ['as' => 'base_group_store', 'uses' => 'AramiscApiController@base_group_store']);
    Route::get('base-group-edit/{id}', ['as' => 'base_group_edit', 'uses' => 'AramiscApiController@base_group_edit']);
    Route::post('base-group-update', ['as' => 'base_group_update', 'uses' => 'AramiscApiController@base_group_update']);
    Route::get('base-group-delete/{id}', ['as' => 'base_group_delete', 'uses' => 'AramiscApiController@base_group_delete']);

    //academic year
    Route::resource('academic-year', 'api\ApiAramiscAcademicYearController');

    //Session
    Route::resource('session', 'api\ApiAramiscSessionController');

    //Holiday
    Route::resource('holiday', 'api\ApiAramiscHolidayController');
    Route::get('delete-holiday-view/{id}', 'AramiscApiController@deleteHolidayView');
    Route::get('delete-holiday/{id}', 'AramiscApiController@deleteHoliday');

    //weekend
    Route::resource('weekend', 'api\ApiSmWeekendController');

    //------------End System Settings Module--------------


    //******************Start Student Panel ********************


    //------------Start Student Dashboard --------------
    Route::get('student-homework/{user_id}/{record_id}', 'api\ApiSmHomeWorkController@studentHomework');
    Route::post('student-upload-homework', 'api\ApiSmHomeWorkController@studentUploadHomework');
    Route::get('school/{school_id}/student-homework/{user_id}/{record_id}', 'api\ApiSmHomeWorkController@saas_studentHomework');
    Route::post('school/{school_id}/student-upload-homework', 'api\ApiSmHomeWorkController@saas_studentUploadHomework');
    Route::get('student-dashboard/{id}', 'AramiscApiController@studentDashboard');
    Route::get('school/{school_id}/student-dashboard/{id}', 'AramiscApiController@saas_studentDashboard');
    Route::get('student-my-attendance/{id}/{record_id}', 'api\ApiAramiscStudentAttendanceController@studentMyAttendanceSearchAPI');
    Route::get('school/{school_id}/student-my-attendance/{id}/{record_id}', 'api\ApiAramiscStudentAttendanceController@saas_studentMyAttendanceSearchAPI');
    Route::get('student-noticeboard/{id}', 'AramiscApiController@studentNoticeboard');
    Route::get('school/{school_id}/student-noticeboard/{id}', 'AramiscApiController@saas_studentNoticeboard');
    //------------End Student Dashboard --------------


    //******************Start Student Panel ********************


    Route::get('studentSubject/{id}/{record_id}', 'AramiscApiController@studentSubjectApi');
    Route::get('school/{school_id}/studentSubject/{id}/{record_id}', 'AramiscApiController@saas_studentSubjectApi');
    Route::get('student-library/{id}', 'AramiscApiController@studentLibrary');
    Route::get('school/{school_id}/student-library/{id}', 'AramiscApiController@saas_studentLibrary');
    Route::get('studentTeacher/{id}', 'api\ApiAramiscStudentPanelController@studentTeacherApi');
    Route::get('school/{school_id}/studentTeacher/{user_id}/{record_id}', 'api\ApiAramiscStudentPanelController@saas_studentTeacherApi');

    Route::get('studentAssignment/{user_id}/{record_id}', 'api\ApiSmStudyMaterialController@studentAssignmentApi');
    Route::get('studentSyllabus/{user_id}/{record_id}', 'api\ApiSmStudyMaterialController@studentSyllabusApi');
    Route::get('studentOtherDownloads/{user_id}/{record_id}', 'api\ApiSmStudyMaterialController@studentOtherDownloadsApi');
    Route::get('school/{school_id}/studentAssignment/{user_id}/{record_id}', 'api\ApiSmStudyMaterialController@saas_studentAssignmentApi');
    Route::get('studentDocuments/{user_id}/{record_id}', 'api\ApiSmStudyMaterialController@studentsDocumentApi');
    Route::get('school/{school_id}/studentDocuments/{user_id}/{record_id}', 'api\ApiSmStudyMaterialController@saas_studentsDocumentApi');

    Route::get('student-dormitory', 'AramiscApiController@studentDormitoryApi');
    Route::get('school/{school_id}/student-dormitory', 'AramiscApiController@saas_studentDormitoryApi');

    Route::get('student-exam_schedule/{id}', 'AramiscApiController@studentExamScheduleApi');
    Route::get('school/{school_id}/student-exam_schedule/{id}', 'AramiscApiController@saas_studentExamScheduleApi');

    Route::get('student-timeline/{id}', 'AramiscApiController@studentTimelineApi');
    Route::get('school/{school_id}/student-timeline/{id}', 'AramiscApiController@saas_studentTimelineApi');

    Route::get('student-online-exam/{user_id}/{record_id}', 'api\ApiAramiscExamController@studentOnlineExamApi');
    Route::get('school/{school_id}/student-online-exam/{user_id}/{record_id}', 'api\ApiAramiscExamController@saas_studentOnlineExamApi');
    Route::get('choose-exam/{user_id}/{record_id}', 'api\ApiAramiscExamController@chooseExamApi');
    Route::get('school/{school_id}/choose-exam/{user_id}/{record_id}', 'api\ApiAramiscExamController@saas_chooseExamApi');
    Route::get('online-exam-result/{user_id}/{exam_id}/{record_id}', 'api\ApiAramiscExamController@examResultApi');
    Route::get('school/{school_id}/online-exam-result/{user_id}/{exam_id}/{record_id}', 'api\ApiAramiscExamController@saas_examResultApi');
    Route::get('getGrades/{marks}', 'AramiscApiController@getGrades');
    Route::get('school/{school_id}/getGrades/{marks}', 'AramiscApiController@saas_getGrades');


    //******************SYSTEM********************
    Route::get('getSystemVersion', 'AramiscApiController@getSystemVersion');
    Route::get('getSystemUpdate/{id}', 'AramiscApiController@getSystemUpdate');


    Route::get('exam-list/{user_id}/{record_id}', 'api\ApiAramiscExamController@examListApi');
    Route::get('school/{school_id}/exam-list/{user_id}/{record_id}', 'api\ApiAramiscExamController@saas_examListApi');
    Route::get('exam-schedule/{user_id}/{exam_id}/{record_id}', 'api\ApiAramiscExamController@examScheduleApi');
    Route::get('school/{school_id}/exam-schedule/{user_id}/{exam_id}/{record_id}', 'api\ApiAramiscExamController@saas_examScheduleApi');
    Route::get('exam-result/{user_id}/{exam_id}/{record_id}', 'api\ApiAramiscExamController@examResult_Api');
    Route::get('school/{school_id}/exam-result/{user_id}/{exam_id}/{record_id}', 'api\ApiAramiscExamController@saas_examResult_Api');

    //Add new exam setup
    Route::get('new-exam-setup', 'api\ApiAramiscExamController@NewExamSetup');
    Route::get('school/{school_id}/new-exam-setup', 'api\ApiAramiscExamController@saas_NewExamSetup');
    Route::get('new-exam-schedule', 'api\ApiAramiscExamController@NewExamSchedule');
    Route::get('school/{school_id}/new-exam-schedule', 'api\ApiAramiscExamController@saas_NewExamSchedule');

    Route::any('change-password', 'AramiscApiController@updatePassowrdStoreApi');
    Route::any('school/{school_id}/change-password', 'AramiscApiController@saas_updatePassowrdStoreApi');
    // exam routine 
    Route::get('exam-schedule-create', 'api\ApiAramiscExamRoutineController@examRoutine');
    Route::post('exam-schedule-create', 'api\ApiAramiscExamRoutineController@examScheduleSearch');
    Route::post('add-exam-routine-store', 'api\ApiAramiscExamRoutineController@addExamRoutineStore');

    // Parents

    Route::get('child-list/{id}', 'api\ApiSmParentPanelController@childListApi');
    Route::get('school/{school_id}/child-list/{id}', 'api\ApiSmParentPanelController@saas_childListApi');
    Route::get('child-info/{id}', 'AramiscApiController@childProfileApi');
    Route::get('school/{school_id}/child-info/{id}', 'AramiscApiController@saas_childProfileApi');
    Route::get('child-fees/{id}', 'AramiscApiController@collectFeesChildApi');
    Route::get('school/{school_id}/child-fees/{id}', 'AramiscApiController@saas_collectFeesChildApi');
    Route::get('child-class-routine/{id}', 'AramiscApiController@classRoutineApi');
    Route::get('school/{school_id}/child-class-routine/{id}', 'AramiscApiController@saas_classRoutineApi');
    Route::get('child-homework/{id}', 'AramiscApiController@childHomework');
    Route::get('school/{school_id}/child-homework/{id}', 'AramiscApiController@saas_childHomework');

    Route::get('child-attendance/{id}/{record_id}', 'AramiscApiController@childAttendanceAPI');
    Route::get('school/{school_id}/child-attendance/{id}/{record_id}', 'AramiscApiController@saas_childAttendanceAPI');

    Route::get('childInfo/{id}', 'api\ApiSmParentPanelController@childInfo');
    Route::get('school/{school_id}/childInfo/{id}', 'api\ApiSmParentPanelController@saas_childInfo');

    Route::get('parent-about', 'AramiscApiController@aboutApi');
    Route::get('school/{school_id}/parent-about', 'AramiscApiController@saas_aboutApi');


    //Route::get('parent-about', 'Parent\SmParentPanelController@aboutApi');


    //Teacher Api

    Route::any('search-student', 'api\ApiAramiscStudentController@searchStudent');
    Route::any('school/{school_id}/search-student', 'api\ApiAramiscStudentController@saas_searchStudent');
    // https://infixedu.com/api/search-student?class=2
    // https://infixedu.com/api/search-student?section=1&class=2
    // https://infixedu.com/api/search-student?name=Conner Stamm
    // https://infixedu.com/api/search-student?roll_no=28229
    Route::get('my-routine/{user_id}', 'api\ApiSmClassRoutineController@teacherClassRoutine');
    Route::get('school/{school_id}/my-routine/{id}', 'api\ApiSmClassRoutineController@sassTeacherClassRoutine');
    Route::get('section-routine/{user_id}/{class}/{section}', 'api\ApiSmClassRoutineController@sectionRoutine');
    Route::get('school/{school_id}/section-routine/{user_id}/{class}/{section}', 'AramiscApiController@saas_sectionRoutine');
    Route::get('class-section/{id}', 'AramiscApiController@classSection');
    Route::get('school/{school_id}/class-section/{id}', 'AramiscApiController@saas_classSection');
    Route::get('subject/{id}', 'AramiscApiController@subjectsName');
    Route::get('school/{school_id}/subject/{id}', 'AramiscApiController@saas_subjectsName');


    Route::get('teacher-class-list', 'AramiscApiController@teacherClassList');
    Route::get('school/{school_id}/teacher-class-list', 'AramiscApiController@saas_teacherClassList');
    Route::get('teacher-section-list', 'AramiscApiController@teacherSectionList');
    Route::get('school/{school_id}/teacher-section-list', 'AramiscApiController@saas_teacherSectionList');


    Route::get('my-attendance/{id}', 'api\ApiSmStaffAttendanceController@teacherMyAttendanceSearchAPI');
    Route::get('school/{school_id}/my-attendance/{id}', 'api\ApiSmStaffAttendanceController@saas_teacherMyAttendanceSearchAPI');
    Route::get('staff-leave-type', 'AramiscApiController@leaveTypeList');
    Route::get('school/{school_id}/staff-leave-type', 'AramiscApiController@saas_leaveTypeList');
    Route::any('staff-apply-leave', 'AramiscApiController@applyLeave');
    Route::any('saas-staff-apply-leave', 'AramiscApiController@saas_applyLeave');
    Route::get('staff-apply-list/{id}', 'AramiscApiController@staffLeaveList');
    Route::get('school/{school_id}/staff-apply-list/{id}', 'AramiscApiController@saas_staffLeaveList');

    // Route::get('upload-content-type', 'teacher\AramiscAcademicsController@contentType');
    Route::any('teacher-upload-content', 'AramiscApiController@uploadContent');
    Route::any('saas-teacher-upload-content', 'AramiscApiController@saas_uploadContent');
    Route::get('content-list', 'api\ApiAramiscTeacherController@uploadContentList');
    Route::get('content-list/{user_id}', 'api\ApiAramiscTeacherController@uploadContentListByUser');
    Route::get('school/{school_id}/content-list', 'api\ApiAramiscTeacherController@saasUploadContentList');
    Route::get('school/{school_id}/admin-content-list', 'api\ApiAramiscTeacherController@saas_contentList');
    Route::get('delete-content/{id}', 'api\ApiAramiscTeacherController@deleteContent');
    Route::get('school/{school_id}/delete-content/{id}', 'api\ApiAramiscTeacherController@saas_deleteContent');


    //for all staff/student
    Route::get('pending-leave/{user_id}', 'api\ApiSmLeaveController@pendingLeave');

    //Super Admin Api
    Route::get('pending-leave', 'api\ApiSmLeaveController@allPendingList');
    Route::get('school/{school_id}/pending-leave', 'AramiscApiController@saas_pendingLeave');
    Route::get('approved-leave', 'AramiscApiController@approvedLeave');
    Route::get('school/{school_id}/approved-leave', 'AramiscApiController@saas_approvedLeave');
    Route::get('reject-leave', 'api\ApiSmLeaveController@allRejectedList');
    Route::get('reject-leave/{user_id}', 'api\ApiSmLeaveController@rejectUserLeave');
    Route::get('school/{school_id}/reject-leave', 'api\ApiSmLeaveController@saas_rejectLeave');
    Route::any('staff-leave-apply', 'AramiscApiController@apply_Leave');
    Route::any('saas-staff-leave-apply', 'AramiscApiController@saas_apply_Leave');
    Route::get('update-leave', 'AramiscApiController@updateLeave');
    Route::get('school/{school_id}/update-leave', 'AramiscApiController@saas_updateLeave');

    Route::post('update-staff',  'AramiscApiController@UpdateStaffApi');
    Route::post('update-student',  'AramiscApiController@UpdateStudentApi');
    //Super Admin Student
    Route::any('set-token', 'AramiscApiController@setToken');
    Route::get('set-fcm-token', 'AramiscApiController@setFcmToken');
    Route::any('school/{school_id}/set-token', 'AramiscApiController@saas_setToken');

    Route::get('group-token', 'AramiscApiController@groupToken');
    Route::get('school/{school_id}/group-token', 'AramiscApiController@saas_groupToken');
    //infixedu.com/android/api/group-token?id=2&body=Notification body&title=Notification title
    // Route::get('notification-api', 'SmSystemSettingController@notificationApi');

    Route::get('flutter-group-token', 'AramiscApiController@flutterGroupToken');
    //  Route::get('flutter-notification-api', 'SmSystemSettingController@flutterNotificationApi');
    Route::get('homework-notification-api', 'api\ApiSmHomeWorkController@HomeWorkNotification');

    Route::get('room-list', 'AramiscApiController@roomList');


    Route::get('myNotification/{user_id}', 'AramiscApiController@myNotification');
    Route::get('viewNotification/{user_id}/{notification_id}', 'AramiscApiController@viewNotification');
    Route::get('viewAllNotification/{user_id}', 'AramiscApiController@viewAllNotification');
    Route::post('child-bank-slip-store', 'AramiscApiController@childBankSlipStore');
    Route::get('banks', 'AramiscApiController@bankList');

    Route::get('room-type-list', 'AramiscApiController@roomTypeList');
    Route::get('school/{school_id}/room-type-list', 'AramiscApiController@saas_roomTypeList');
    Route::post('room-store', 'AramiscApiController@storeRoom');
    Route::post('saas-room-store', 'AramiscApiController@saas_storeRoom');
    Route::post('room-update', 'AramiscApiController@updateRoom');
    Route::post('saas-room-update', 'AramiscApiController@saas_updateRoom');
    Route::get('room-delete/{id}', 'AramiscApiController@deleteRoom');
    Route::get('school/{school_id}/room-delete/{id}', 'AramiscApiController@saas_deleteRoom');

    Route::get('dormitory-list', 'AramiscApiController@dormitoryList');
    Route::get('school/{school_id}/dormitory-list', 'AramiscApiController@saas_dormitoryList');
    Route::post('add-dormitory', 'AramiscApiController@addDormitory');
    Route::post('saas-add-dormitory', 'AramiscApiController@saas_addDormitory');
    Route::get('edit-dormitory', 'AramiscApiController@editDormitory');
    Route::get('edit-dormitory', 'AramiscApiController@saas_editDormitory');
    Route::get('delete-dormitory/{id}', 'AramiscApiController@deleteDormitory');
    Route::get('school/{school_id}/delete-dormitory/{id}', 'AramiscApiController@saas_deleteDormitory');

    Route::get('driver-list', 'AramiscApiController@getDriverList');
    Route::get('school/{school_id}/driver-list', 'AramiscApiController@saas_getDriverList');


    Route::get('book-category', 'AramiscApiController@bookCategory');
    //download file
    Route::get('download-content-document/{file_name}', 'AramiscApiController@DownloadContent');
    Route::get('download-complaint-document/{file_name}', 'AramiscApiController@DownloadComplaint');
    Route::get('download-visitor-document/{file_name}', 'AramiscApiController@DownloadVisitor');
    Route::get('postal-receive-document/{file_name}', 'AramiscApiController@DownloadPostal');
    Route::get('postal-dispatch-document/{file_name}', 'AramiscApiController@DownloadDispatch');


    // End Upload Content
    // Route::post('custom-merit-list', 'CustomResultSettingController@meritListReport');

    // Route::post('custom-progress-card', 'CustomResultSettingController@progressCardReport');
    // Route::post('student-final-result', 'CustomResultSettingController@studentFinalResult');
    //User Info for demo


    Route::get('school/{school_id}/user-demo', 'AramiscApiController@SaasDemoUser');
    Route::get('currency-converter', 'AramiscApiController@convertCurrency'); //api/currency-converter?amount=2&from_currency=USD&to_currency=BDT
    Route::any('student-fees-payment', 'AramiscApiController@studentFeesPayment');
    Route::any('school/{school_id}/student-fees-payment', 'AramiscApiController@saas_studentFeesPayment');

    Route::get('banks/{school_id}', 'api\ApiSmSaasBankController@saas_bankList');
    Route::post('saas-child-bank-slip-store', 'api\ApiSmSaasBankController@saas_childBankSlipStore');
    Route::get('school/{school_id}/studentSyllabus/{user_id}/{record_id}', 'api\ApiSmStudyMaterialController@saas_studentSyllabusApi');
    Route::get('school/{school_id}/studentOtherDownloads/{user_id}/{record_id}', 'api\ApiSmStudyMaterialController@saas_studentOtherDownloadsApi');
    Route::get('school/{school_id}/room-list', 'api\ApiSmSaasBankController@saas_roomList');
    Route::any('saas-book-category', 'api\ApiSmSaasBankController@saas_bookCategory');
    Route::get('my-leave-type/{school_id}/{user_id}', 'api\ApiSmLeaveController@saas_myLeaveType');
    // update 1-14-2-2022
    Route::get('student-record/{student_id}', 'api\ApiStudentRecordController@getRecord');
    Route::get('student-record/{school_id}/{student_id}', 'api\ApiStudentRecordController@getRecordSaas');

    //class routine
    Route::get('student-class-routine/{user_id}/{record_id}', 'api\ApiSmClassRoutineController@studentClassRoutine');
    Route::get('school/{school_id}/student-class-routine/{user_id}/{record_id}', 'api\ApiSmClassRoutineController@sassclassRoutine');
    Route::post('student-attendance-store-all', 'api\ApiAramiscStudentAttendanceController@studentStoreAttendanceAllApi');

    //22/04/22
    //Attendance store all
    Route::post('student-attendance-store-all', 'api\ApiAramiscStudentAttendanceController@studentStoreAttendanceAllApi');


    Route::get('student-fees-installments/{record_id}', 'api\DirectFeesApiController@getInstallments');
    Route::get('student-fees-installment-make-payment/{record_id}', 'api\DirectFeesApiController@makePayment');
    Route::post('student-fees-installment-submit-payment/{record_id}', 'api\DirectFeesApiController@submitPayment');




    Route::post('class-section-subjectList', 'api\ApiAramiscStudentAttendanceController@subjectList');
    Route::post('student-subject-attendance-store', 'api\ApiAramiscStudentAttendanceController@studentSubjectAttendanceStore');
    Route::get('student-subject-attendance/{record_id}', 'api\ApiAramiscStudentAttendanceController@studentSubjectAttendanceSearch');
    Route::get('student-subject-attendance-check', 'api\ApiAramiscStudentAttendanceController@studentSubjectAttendanceCheck');

    Route::get('student-subject-attendance/{user_id}/{record_id}', 'api\ApiAramiscStudentAttendanceController@studentSubjectAttendanceSearch');

    Route::get('student-my-subject-attendance/{id}/{record_id}', 'api\ApiAramiscStudentAttendanceController@studentMySubjectAttendanceSearchAPI');
});

Route::get('apk-secret', function () {
    return response()->json(apk_secret());
});
