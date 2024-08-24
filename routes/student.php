<?php

use Illuminate\Support\Facades\Route;
use Modules\RolePermission\Entities\InfixModuleStudentParentInfo;

Route::group(['middleware' => ['XSS', 'subdomain']], function () {


    // Student Panel
    Route::group(['middleware' => ['StudentMiddleware']], function () {
        Route::get('delete-document/{id}', ['as' => 'delete_document', 'uses' => 'AramiscStudentAdmissionController@deleteDocument']);
        Route::post('student_upload_document', ['as' => 'student_upload_document', 'uses' => 'AramiscStudentAdmissionController@studentUploadDocument']);
        Route::get('student-download-document/{file_name}', 'Student\AramiscStudentPanelController@DownlodStudentDocument')->name('student-download-document')->middleware('userRolePermission:student-download-document');
        Route::post('student-logout', 'Auth\LoginController@logout')->name('student-logout');
        Route::get('student-profile', 'Student\AramiscStudentPanelController@studentProfile')->name('student-profile')->middleware('userRolePermission:student-profile');
        Route::get('update-my-profile/{id}', 'Student\AramiscStudentPanelController@studentProfileUpdate')->name('update-my-profile')->middleware('userRolePermission:update-my-profile');
        Route::post('update-my-profile', 'Student\AramiscStudentPanelController@studentUpdate')->name('my-profile-update');
        Route::get('student-dashboard', 'Student\AramiscStudentPanelController@studentDashboard')->name('student-dashboard')->middleware('userRolePermission:student-dashboard');
        // ->middleware('userRolePermission:student-dashboard');
        Route::get('download-timeline-doc/{file_name}', 'Student\AramiscStudentPanelController@DownlodTimeline')->name('download-timeline-doc');

        // Fees
        Route::get('student-fees', ['as' => 'student_fees', 'uses' => 'Student\AramiscFeesController@studentFees'])->middleware('userRolePermission:student_fees');
        
        Route::post('studentPayByPaypal', 'AramiscCollectFeesByPaymentGateway@payByPaypal')->name('studentPayByPaypal');
        // Route::get('fees-payment-stripe/{fees_type}/{student_id}/{amount}', 'Student\AramiscFeesController@feesPaymentStripe');
        // Route::post('fees-payment-stripe-store', 'Student\AramiscFeesController@feesPaymentStripeStore');
        
        // Online Exam
        Route::get('student-online-exam', ['as' => 'student_online_exam', 'uses' => 'Student\SmOnlineExamController@studentOnlineExam'])->middleware('userRolePermission:student_online_exam');
        Route::get('take-online-exam/{id}', ['as' => 'take_online_exam', 'uses' => 'Student\SmOnlineExamController@takeOnlineExam']);
        Route::post('student-online-exam-submit', ['as' => 'student_online_exam_submit', 'uses' => 'Student\SmOnlineExamController@studentOnlineExamSubmit']);
        Route::post('student_done_online_exam', ['as' => 'student_done_online_exam', 'uses' => 'Student\SmOnlineExamController@student_online_exam']);
        Route::GET('ajax-student-online-exam-submit', ['as' => 'ajax_student_online_exam_submit', 'uses' => 'Student\SmOnlineExamController@AjaxStudentOnlineExamSubmit']);

        Route::get('student_view_result', ['as' => 'student_view_result', 'uses' => 'Student\SmOnlineExamController@studentViewResult'])->middleware('userRolePermission:student_view_result');
        Route::get('student-answer-script/{exam_id}/{s_id}', ['as' => 'student_answer_script', 'uses' => 'Student\SmOnlineExamController@studentAnswerScript']);

        Route::get('student-view-online-exam-question/{id}', 'Student\SmOnlineExamController@viewOnlineExam')->name('student-online-exam-question-view');
       
        // Class Timetable
        Route::get('student-class-routine', ['as' => 'student_class_routine', 'uses' => 'Student\AramiscStudentPanelController@classRoutine'])->middleware('userRolePermission:student_class_routine');

        // Student Attendance
        Route::get('student-my-attendance', ['as' => 'student_my_attendance', 'uses' => 'Student\AramiscStudentPanelController@studentMyAttendance'])->middleware('userRolePermission:student_my_attendance');
        Route::post('student-my-attendance', ['as' => 'student_my_attendance_search', 'uses' => 'Student\AramiscStudentPanelController@studentMyAttendanceSearch']);
        Route::get('my-attendance/print/{id}/{month}/{year}/', 'Student\AramiscStudentPanelController@studentMyAttendancePrint')->name('my-attendance/print');

        // Student Result
        Route::get('student-result', ['as' => 'student_result', 'uses' => 'Student\AramiscStudentPanelController@studentResult'])->middleware('userRolePermission:student_result');

        //student Exam Schedule
        Route::get('student-exam-schedule', ['as' => 'student_exam_schedule', 'uses' => 'Student\AramiscStudentPanelController@studentExamSchedule'])->middleware('userRolePermission:student_exam_schedule');
        Route::any('student-exam-schedule-search', ['as' => 'student_exam_schedule_search', 'uses' => 'Student\AramiscStudentPanelController@studentExamScheduleSearch']);
        Route::any('student-exam-schedule/print', ['as' => 'student_exam_schedule_print', 'uses' => 'AramiscExamRoutineController@examSchedulePrint']);
        //abunayem
        Route::get('student-routine-print/{class_id}/{section_id}/{exam_period_id}', 'AramiscExamRoutineController@examRoutinePrint')->name('student-routine-print');

        //student Homework
        Route::get('student-homework', ['as' => 'student_homework', 'uses' => 'Student\AramiscStudentPanelController@studentHomework'])->middleware('userRolePermission:student_homework');
        Route::get('student-homework-view/{class_id}/{section_id}/{homework}', ['as' => 'student_homework_view', 'uses' => 'Student\AramiscStudentPanelController@studentHomeworkView']);

        Route::get('university/student/homework-view/{sem_label_id}/{homework}', ['as' => 'student.un_student_homework_view', 'uses' => 'Student\AramiscStudentPanelController@unStudentHomeworkView']);

        Route::get('add-homework-content/{homework}', 'Student\AramiscStudentPanelController@addHomeworkContent')->name('add-homework-content');
        Route::post('upload-homework-content', 'Student\AramiscStudentPanelController@uploadHomeworkContent')->name('upload-homework-content');
        Route::get('deleteview-homework-content/{homework}', 'Student\AramiscStudentPanelController@deleteViewHomeworkContent')->name('deleteview-homework-content');
        Route::get('delete-homework-content/{homework}', 'Student\AramiscStudentPanelController@deleteHomeworkContent')->name('delete-homework-content');
        Route::get('evaluation-document/{file_name}', 'Student\AramiscStudentPanelController@DownlodDocument')->name('evaluation-document');
        Route::get('student-delete-document/{id}', ['as' => 'student-document-delete', 'uses' => 'AramiscStudentAdmissionController@deleteDocument']);
        // Download Center
        Route::get('student-assignment', ['as' => 'student_assignment', 'uses' => 'Student\AramiscStudentPanelController@studentAssignment'])->middleware('userRolePermission:student_assignment');
        Route::get('student-study-material', ['as' => 'student_study_material', 'uses' => 'Student\AramiscStudentPanelController@studentStudyMaterial'])->middleware('userRolePermission:student_study_material');
        Route::get('student-syllabus', ['as' => 'student_syllabus', 'uses' => 'Student\AramiscStudentPanelController@studentSyllabus'])->middleware('userRolePermission:student_syllabus');
        Route::get('student-others-download', ['as' => 'student_others_download', 'uses' => 'Student\AramiscStudentPanelController@othersDownload'])->middleware('userRolePermission:student_others_download');
        Route::get('upload-content-student-view/{id}', 'Student\AramiscStudentPanelController@uploadContentView')->name('upload-content-student-view');
        Route::get('student-download-content-document/{file_name}', 'Student\AramiscStudentPanelController@DownlodContent')->name('student-download-content-document')->middleware('userRolePermission:student-download-content-document');

        // Student Subject
        Route::get('student-subject', ['as' => 'student_subject', 'uses' => 'Student\AramiscStudentPanelController@studentSubject'])->middleware('userRolePermission:student_subject');

        // Online Exam
        Route::get('student-answer-script/{exam_id}/{s_id}', ['as' => 'student_answer_script', 'uses' => 'Student\SmOnlineExamController@studentAnswerScript']);

        // Transport Route
        Route::get('student-transport', ['as' => 'student_transport', 'uses' => 'Student\AramiscStudentPanelController@studentTransport'])->middleware('userRolePermission:student_transport');
        Route::get('student-transport-view-modal/{r_id}/{v_id}', ['as' => 'student_transport_view_modal', 'uses' => 'Student\AramiscStudentPanelController@studentTransportViewModal']);

        // Dormitory Rooms
        Route::get('student-dormitory', ['as' => 'student_dormitory', 'uses' => 'Student\AramiscStudentPanelController@studentDormitory'])->middleware('userRolePermission:student_dormitory');
        
        // Student Library Book list
        Route::get('student-library', ['as' => 'student_library', 'uses' => 'Student\AramiscStudentPanelController@studentBookList'])->middleware('userRolePermission:student_library');
        
        // Student Library Book Issue
        Route::get('student-book-issue', ['as' => 'student_book_issue', 'uses' => 'Student\AramiscStudentPanelController@studentBookIssue'])->middleware('userRolePermission:student_book_issue');
        
        // Student Noticeboard
        Route::get('student-noticeboard', ['as' => 'student_noticeboard', 'uses' => 'Student\AramiscStudentPanelController@studentNoticeboard'])->middleware('userRolePermission:student_noticeboard');
        
        // Student Teacher
        Route::get('student-teacher', ['as' => 'student_teacher', 'uses' => 'Student\AramiscStudentPanelController@studentTeacher'])->middleware('userRolePermission:student_teacher');
    });


    // Student leave
    Route::group(['middleware' => ['auth'], 'namespace' => 'Student'], function () {
        Route::get('student-apply-leave', 'AramiscStudentPanelController@leaveApply')->name('student-apply-leave')->middleware('userRolePermission:student-apply-leave');
        Route::post('student-leave-store', 'AramiscStudentPanelController@leaveStore')->name('student-leave-store')->middleware('userRolePermission:student-leave-store');
        Route::get('student-leave-edit/{id}', 'AramiscStudentPanelController@studentLeaveEdit')->name('student-leave-edit')->middleware('userRolePermission:student-leave-edit');
        Route::get('student-pending-leave', 'AramiscStudentPanelController@pendingLeave')->name('student-pending-leave')->middleware('userRolePermission:student-pending-leave');
        Route::put('student-leave-update/{id}', 'AramiscStudentPanelController@update')->name('student-leave-update');
        Route::get('download-student-leave-document/{file_name}', function ($file_name = null) {
            $file = public_path() . '/uploads/leave_request/' . $file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
        })->name('download-student-leave-document');
    });
});
Route::get('download-uploaded-content/{id}/{student_id}', 'Student\AramiscStudentPanelController@downloadHomeWorkContent')->name('download-uploaded-content');


Route::get('fees-payment-stripe/{fees_type}/{student_id}/{amount}/{assign_id}/{record_id}', 'Student\AramiscFeesController@feesPaymentStripe')->name('fees-payment-stripe');

Route::get('stripe-fees-payment-stripe/{installment_id}', 'Student\AramiscFeesController@directFeesPaymentStripe')->name('directFeesPaymentStripe');

Route::post('fees-payment-stripe-store', 'Student\AramiscFeesController@feesPaymentStripeStore')->name('fees-payment-stripe-store');
//student bank cheque payment
Route::get('fees-generate-modal-child/{amount}/{student_id}/{type}/{assign_id}/{record_id}', 'Student\AramiscFeesController@feesGenerateModalChild')->name('fees-generate-modal-child');
Route::post('child-bank-slip-store', 'Student\AramiscFeesController@childBankSlipStore')->name('child-bank-slip-store');
Route::get('fees-generate-modal-child-view/{id}/{type_id}', 'Student\AramiscFeesController@feesGenerateModalBankView')->name('fees-generate-modal-child-view');
Route::post('child-bank-slip-delete', 'Student\AramiscFeesController@childBankSlipDelete');

Route::get('student-direct-fees-total-payment/{record_id}', 'Student\AramiscFeesController@directFeesTotalPayment')->name('student-direct-fees-total-payment');
Route::post('student-direct-fees-total-payment', 'Student\AramiscFeesController@directFeesTotalPaymentSubmit')->name('student-direct-fees-total-payment-submit');



Route::get('direct-fees-generate-modal-child/{amount}/{installment_id}/{record_id}', 'Student\AramiscFeesController@directFeesGenerateModalChild')->name('direct-fees-generate-modal-child');


