<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['XSS', 'subdomain','fees_due_check']], function () {
    // Parent Panel
    Route::group(['middleware' => ['ParentMiddleware']], function () {
        Route::get('parent-dashboard',  'Parent\AramiscParentPanelController@ParentDashboard')->name('parent-dashboard')->middleware('userRolePermission:parent-dashboard');
        Route::get('my-children/{id}', ['as' => 'my_children', 'uses' => 'Parent\AramiscParentPanelController@myChildren'])->middleware('userRolePermission:my_children');
        Route::get('update-my-children/{id}', 'Parent\AramiscParentPanelController@UpdatemyChildren')->name('update-my-children')->middleware('userRolePermission:my-children-update');
        Route::post('my-children-update', 'Parent\AramiscParentPanelController@studentUpdate')->name('my-children-update')->middleware('userRolePermission:my-children-update');
        Route::get('parent-fees/{id}', ['as' => 'parent_fees', 'uses' => 'Parent\AramiscFeesController@childrenFees'])->middleware('userRolePermission:parent_fees');
        Route::get('parent-class-routine/{id}', ['as' => 'parent_class_routine', 'uses' => 'Parent\AramiscParentPanelController@classRoutine'])->middleware('userRolePermission:parent_class_routine');
        Route::get('parent-attendance/{id}', ['as' => 'parent_attendance', 'uses' => 'Parent\AramiscParentPanelController@attendance'])->middleware('userRolePermission:parent_attendance');
        Route::get('my-child-attendance/print/{student_id}/{id}/{month}/{year}/', 'Parent\AramiscParentPanelController@attendancePrint')->name('my_child_attendance_print');
        Route::get('parent-homework/{id}', ['as' => 'parent_homework', 'uses' => 'Parent\AramiscParentPanelController@homework'])->middleware('userRolePermission:parent_homework');
        Route::get('parent-homework-view/{class_id}/{section_id}/{homework}', ['as' => 'parent_homework_view', 'uses' => 'Parent\AramiscParentPanelController@homeworkView'])->middleware('userRolePermission:parent_homework_view');
        

        Route::get('university/parent-homework-view/{sem_label_id}/{homework}', ['as' => 'un_student_homework_view', 'uses' => 'Parent\AramiscParentPanelController@unStudentHomeworkView']);

        Route::get('parent-noticeboard', ['as' => 'parent_noticeboard', 'uses' => 'Parent\AramiscParentPanelController@parentNoticeboard'])->middleware('userRolePermission:parent_noticeboard');
        Route::post('parent-attendance-search', ['as' => 'parent_attendance_search', 'uses' => 'Parent\AramiscParentPanelController@attendanceSearch']);
        Route::post('parent-exam-schedule/print','AramiscExamRoutineController@examSchedulePrint')->name('parent_exam_schedule_print');
        Route::get('parent-online-examination/{id}', ['as' => 'parent_online_examination', 'uses' => 'Parent\AramiscParentPanelController@onlineExamination'])->middleware('userRolePermission:parent_online_examination');
        Route::get('parent-online-examination-result/{id}', ['as' => 'parent_online_examination_result', 'uses' => 'Parent\AramiscParentPanelController@onlineExaminationResult']);
        Route::get('parent-answer-script/{exam_id}/{s_id}', ['as' => 'parent_answer_script', 'uses' => 'Parent\AramiscParentPanelController@parentAnswerScript']);
        Route::get('parent-leave/{id}', ['as' => 'parent_leave', 'uses' => 'Parent\AramiscParentPanelController@parentLeave']);

        // Leave
        Route::get('parent-apply-leave', 'Parent\AramiscParentPanelController@leaveApply')->name('parent-apply-leave')->middleware('userRolePermission:parent-apply-leave');
        Route::post('parent-leave-store', 'Parent\AramiscParentPanelController@leaveStore')->name('parent-leave-store')->middleware('userRolePermission:parent-leave-store');
        Route::get('parent-view-leave-details-apply/{id}', 'Parent\AramiscParentPanelController@viewLeaveDetails')->name('parent-view-leave-details-apply')->middleware('userRolePermission:parent-view-leave-details-apply');
        Route::get('parent-leave-edit/{id}', 'Parent\AramiscParentPanelController@parentLeaveEdit')->name('parent-leave-edit')->middleware('userRolePermission:parent-leave-edit');
        Route::get('parent-pending-leave', 'Parent\AramiscParentPanelController@pendingLeave')->name('parent-pending-leave')->middleware('userRolePermission:parent-pending-leave');
        Route::put('parent-leave-update/{id}', 'Parent\AramiscParentPanelController@update')->name('parent-leave-update')->middleware('userRolePermission:parent-leave-edit');
        Route::delete('parent-leave-delete/{id}', 'Parent\AramiscParentPanelController@DeleteLeave')->name('parent-leave-delete')->middleware('userRolePermission:parent-leave-delete');

        Route::get('parent-examination/{id}', ['as' => 'parent_examination', 'uses' => 'Parent\AramiscParentPanelController@examination'])->middleware('userRolePermission:parent_examination');
        Route::get('parent-examination-schedule/{id}', ['as' => 'parent_exam_schedule', 'uses' => 'Parent\AramiscParentPanelController@examinationSchedule'])->middleware('userRolePermission:parent_exam_schedule');
        Route::post('parent-examination-schedule', ['as' => 'parent_exam_schedule_search', 'uses' => 'Parent\AramiscParentPanelController@examinationScheduleSearch']);

                //abunayem
        Route::get('parent-routine-print/{class_id}/{section_id}/{exam_period_id}', 'Parent\AramiscParentPanelController@examRoutinePrint')->name('parent-routine-print');

        // Student Library Book list
        Route::get('parent-library', ['as' => 'parent_library', 'uses' => 'Parent\AramiscParentPanelController@parentBookList'])->middleware('userRolePermission:parent_library');
        Route::get('parent-book-issue', ['as' => 'parent_book_issue', 'uses' => 'Parent\AramiscParentPanelController@parentBookIssue'])->middleware('userRolePermission:parent_book_issue');
        Route::get('parent-subjects/{id}', ['as' => 'parent_subjects', 'uses' => 'Parent\AramiscParentPanelController@subjects'])->middleware('userRolePermission:parent_subjects');
        Route::get('parent-teacher-list/{id}', ['as' => 'parent_teacher_list', 'uses' => 'Parent\AramiscParentPanelController@teacherList'])->middleware('userRolePermission:parent_teacher_list');
        Route::get('parent-transport/{id}', ['as' => 'parent_transport', 'uses' => 'Parent\AramiscParentPanelController@transport'])->middleware('userRolePermission:parent_transport');
        Route::get('parent-dormitory/{id}', ['as' => 'parent_dormitory_list', 'uses' => 'Parent\AramiscParentPanelController@dormitory'])->middleware('userRolePermission:parent_dormitory_list');

        // Dowmload 
        Route::get('parent/student-download-timeline-doc/{file_name}', ['as' => 'parent_student_download_timeline_doc', 'uses' => 'Parent\AramiscParentPanelController@StudentDownload']);
    });
});