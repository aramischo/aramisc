<?php

namespace Database\Seeders\Exam;

use App\AramiscExam;
use App\YearCheck;
use App\AramiscExamType;
use App\AramiscAssignSubject;
use App\AramiscExamAttendance;
use App\Models\StudentRecord;
use App\AramiscExamAttendanceChild;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class AramiscExamAttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id)
    {     
        $aramiscExamTypes = AramiscExam::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        $assignSubjects = AramiscAssignSubject::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        foreach ($aramiscExamTypes as $exam) {
            foreach ($assignSubjects as $subject) {
                $studentRecord = StudentRecord::where('school_id', $school_id)->where('academic_id', $academic_id)->where('class_id', $subject->class_id)->where('section_id', $subject->section_id)->get();
                $store = new AramiscExamAttendance();
                $store->exam_id = $exam->id;
                $store->subject_id = $subject->subject_id;
                $store->class_id = $subject->class_id;
                $store->section_id = $subject->section_id;
                $store->created_by = 1;
                $store->created_at = date('Y-m-d h:i:s');
                $store->save();
                foreach ($studentRecord as $record) {
                    $exam_attendance_child = new AramiscExamAttendanceChild();
                    $exam_attendance_child->exam_attendance_id = $store->id;
                    $exam_attendance_child->student_id = $record->student_id;
                    $exam_attendance_child->student_record_id = $record->id;
                    $exam_attendance_child->class_id = $record->class_id;
                    $exam_attendance_child->section_id = $record->section_id;
                    $exam_attendance_child->attendance_type = 'P';
                    $exam_attendance_child->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $exam_attendance_child->school_id = $school_id;
                    $exam_attendance_child->academic_id = $academic_id;
                    $exam_attendance_child->save();
                }
            }

        }
    }
}
