<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\AramiscAcademicCalendarController;
use App\Http\Controllers\Controller;
use App\Models\AramiscCalendarSetting;
use App\Models\StudentRecord;
use App\Models\User;
use App\AramiscAssignSubject;
use App\AramiscBookIssue;
use App\AramiscComplaint;
use App\AramiscEvent;
use App\AramiscExamSchedule;
use App\AramiscHoliday;
use App\AramiscHomework;
use App\AramiscLeaveDefine;
use App\AramiscMarksGrade;
use App\AramiscNoticeBoard;
use App\AramiscOnlineExam;
use App\AramiscStudent;
use App\AramiscStudentAttendance;
use App\AramiscStudentDocument;
use App\AramiscStudentTimeline;
use App\AramiscSubjectAttendance;
use App\AramiscVehicle;
use App\AramiscWeekend;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\OnlineExam\Entities\AramiscOnlineExam;
use Modules\RolePermission\Entities\AramiscRole;

class AlumniPanelController extends Controller 

{
    public function alumniDashboard () {
        if (moduleStatusCheck('Alumni')) {
            $user = auth()->user();
            $role_id    = AramiscRole::where('name', 'Alumni')->first()->id;
            if ($user) {
                $user_id = $user->id;
            }

            $student_detail = auth()->user()->student->load('studentRecords', 'feesAssign', 'feesAssignDiscount');

            $data['documents']      = AramiscStudentDocument::where('student_staff_id', $student_detail->id)
                                ->where('type', 'stu')
                                ->where('academic_id', getAcademicId())
                                ->where('school_id', $user->school_id)
                                ->get();

            $data['timelines']      = AramiscStudentTimeline::where('staff_student_id', $student_detail->id)
                                ->where('type', 'stu')
                                ->where('visible_to_student', 1)
                                ->where('academic_id', getAcademicId())
                                ->where('school_id', $user->school_id)
                                ->get();

            $data['totalNotices']   = AramiscNoticeBoard::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)
                                ->where(function ($query) {
                                    $query->whereJsonContains('inform_to', '10')
                                        ->orWhere('inform_to', '10');
                                    })
                                    ->get();

            $data['issueBooks'] = AramiscBookIssue::where('member_id', $student_detail->user_id)
                ->where('issue_status', 'I')
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)
                ->get();

            $data['aramiscEvents'] = AramiscEvent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('from_date', '>=', date('Y-m-d'))
                ->where('school_id', $user->school_id)
                ->where(function( $quest) {
                    $quest->whereJsonContains('role_ids','10')
                        ->orWhere('role_ids','10');
                })
                ->get();

            $data['student_detail'] = AramiscStudent::where('user_id', $user->id)->first();

            $data['aramisc_weekends']    = AramiscWeekend::orderBy('order', 'ASC')
                                ->where('active_status', 1)
                                ->where('is_weekend', 1)
                                ->where('school_id', $user->school_id)
                                ->get();

            if (moduleStatusCheck('University')) {
                $records = StudentRecord::where('student_id', $student_detail->id)
                    ->where('un_academic_id', getAcademicId())->get();
            } else {
                $records = StudentRecord::where('student_id', $student_detail->id)
                    ->where('academic_id', getAcademicId())->get();
            }
            
            $data['student_details']  = Auth::user()->student->load('studentRecords', 'attendances');
            $data['student_records']  = $data['student_details']->studentRecords;
            
            $data['settings'] = AramiscCalendarSetting::get();
            $data['roles'] = AramiscRole::where(function ($q) {
                $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
                })
                ->whereNotIn('id', [1, 2])
                ->get();
                
            $academicCalendar = new AramiscAcademicCalendarController();
            $data['events'] = $academicCalendar->calenderData();
        } else {
            abort(404);
        }        
        return view('backEnd.alumniPanel.alumni_dashboard', $data);
    }

    public function viewEvent($id)
    {
        try {
            $event = AramiscEvent::find($id);
            return view('alumni::inc._view_event', compact('event'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function viewDocument($id)
    {
        try {
            $document = AramiscStudentDocument::find($id);
            return view('alumni::inc._view_document', compact('document'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentProfile()
    {
        try {
            $student_id = Auth::user()->student->id;
            $student_detail = AramiscStudent::find($student_id);
            return view('backEnd.alumniPanel.inc._student_profile', compact('student_detail'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}