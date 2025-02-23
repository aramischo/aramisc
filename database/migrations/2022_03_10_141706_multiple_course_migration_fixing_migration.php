<?php

use App\Scopes\AcademicSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\AramiscStudentTakeOnlineExam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MultipleCourseMigrationFixingMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fees = \App\AramiscFeesAssign::withOutGlobalScope(StatusAcademicSchoolScope::class)->get();

        foreach ($fees as $fee) {
            $record = \App\Models\StudentRecord::where(['student_id' => $fee->student_id, 'school_id' => $fee->school_id, 'academic_id' => $fee->academic_id])->first();
            $fee->record_id = optional($record)->id;
            $fee->save();
        }

        $fees = \App\AramiscFeesPayment::all();

        foreach ($fees as $fee) {
            $record = \App\Models\StudentRecord::where(['student_id' => $fee->student_id, 'school_id' => $fee->school_id, 'academic_id' => $fee->academic_id])->first();
            $fee->record_id = optional($record)->id;
            $fee->save();
        }


        $feeDiscounts = \App\AramiscFeesAssignDiscount::withOutGlobalScope(StatusAcademicSchoolScope::class)->get();

        foreach ($feeDiscounts as $feefeeDiscount) {
            $record = \App\Models\StudentRecord::where(['student_id' => $feefeeDiscount->student_id, 'school_id' => $feefeeDiscount->school_id, 'academic_id' => $feefeeDiscount->academic_id])->first();
            $feefeeDiscount->record_id = optional($record)->id;
            $feefeeDiscount->save();
        }


        $onlineExams = AramiscStudentTakeOnlineExam::all();

        foreach ($onlineExams as $onlineExam) {
            $record = \App\Models\StudentRecord::where(['student_id' => $onlineExam->student_id, 'school_id' => $onlineExam->school_id, 'academic_id' => $onlineExam->academic_id])->first();
            $onlineExam->record_id = optional($record)->id;
            $onlineExam->save();
        }

        // Attendance data migration

        $attendances = \App\AramiscStudentAttendance::withOutGlobalScope(AcademicSchoolScope::class)->get();

        foreach ($attendances as $attendance) {
            $record = \App\Models\StudentRecord::where(['student_id' => $attendance->student_id, 'school_id' => $attendance->school_id, 'academic_id' => $attendance->academic_id, 'class_id' => $attendance->class_id, 'section_id' => $attendance->section_id])->first();
            $attendance->student_record_id = optional($record)->id;
            $attendance->save();
        }

        $subjectAttendances = \App\AramiscSubjectAttendance::all();

        foreach ($subjectAttendances as $attendance) {
            $record = \App\Models\StudentRecord::where(['student_id' => $attendance->student_id, 'school_id' => $attendance->school_id, 'academic_id' => $attendance->academic_id, 'class_id' => $attendance->class_id, 'section_id' => $attendance->section_id])->first();
            $attendance->student_record_id = optional($record)->id;
            $attendance->save();
        }


        $examAttendances = \App\AramiscExamAttendanceChild::all();

        foreach ($examAttendances as $examAttendance) {
            $record = \App\Models\StudentRecord::where(['student_id' => $examAttendance->student_id, 'school_id' => $examAttendance->school_id, 'academic_id' => $examAttendance->academic_id, 'class_id' => $examAttendance->class_id, 'section_id' => $examAttendance->section_id])->first();
            $examAttendance->student_record_id = optional($record)->id;
            $examAttendance->save();
        }

        $datas = \App\AramiscResultStore::all();

        foreach ($datas as $data) {
            $record = \App\Models\StudentRecord::where(['student_id' => $data->student_id, 'school_id' => $data->school_id, 'academic_id' => $data->academic_id, 'class_id' => $data->class_id, 'section_id' => $data->section_id])->first();
            $data->student_record_id = optional($record)->id;
            $data->save();
        }


        $datas = \App\AramiscMarkStore::withOutGlobalScope(AcademicSchoolScope::class)->get();

        foreach ($datas as $data) {
            $record = \App\Models\StudentRecord::where(['student_id' => $data->student_id, 'school_id' => $data->school_id, 'academic_id' => $data->academic_id, 'class_id' => $data->class_id, 'section_id' => $data->section_id])->first();
            $data->student_record_id = optional($record)->id;
            $data->save();
        }

        $schools = \App\AramiscSchool::all();
        foreach($schools as $school){
            $setting = \App\AramiscGeneralSettings::where('school_id', $school->id)->first();

            if($setting && !$setting->academic_id){
                $academic_year = \App\AramiscAcademicYear::where('school_id', $school->id)->first();
                $setting->academic_id = $academic_year ? $academic_year->id : null;
                $setting->save();
            }
        }

        \App\Models\AramiscStudentRegistrationField::where('field_name', 'admission_number')->update(['is_required' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
