<?php

namespace App;

use App\AramiscMarkStore;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscResultStore extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_result_stores';
    public function studentInfo(){
    	return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }
    public function exam(){
        return $this->belongsTo(AramiscExamType::class, 'exam_type_id');
    }

    public function subject(){
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
    }
    public function class(){
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }
     public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }

     public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id', 'id');
    }

    public function studentRecords()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id', 'id');
    }

    public static function remarks($gpa){
    try{
        $mark = AramiscMarksGrade::where([
            ['from', '<=', $gpa], 
            ['up', '>=', $gpa]]
            )
            ->where('school_id',Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->first();
            return $mark;
    } catch (\Exception $e) {
        $mark=[];
        return $mark;
    }


    }
    public static function  GetResultBySubjectId($class_id, $section_id, $subject_id,$exam_id,$student_id){
    	
        try {
            $data = AramiscMarkStore::withOutGlobalScopes()->where([
                ['class_id',$class_id],
                ['section_id',$section_id],
                ['exam_term_id',$exam_id],
                ['student_record_id',$student_id],
                ['subject_id',$subject_id]
            ])->get();
            return $data;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function  un_GetResultBySubjectId($subject_id, $exam_id, $student_id, $request){

        try {
            $AramiscMarkStore = AramiscMarkStore::query();
            $data = universityFilter($AramiscMarkStore, $request)
            ->where([
                ['exam_term_id',$exam_id],
                ['student_id',$student_id],
                ['un_subject_id',$subject_id]
            ])->get();
            return $data;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function  GetFinalResultBySubjectId($class_id, $section_id, $subject_id,$exam_id,$student_id){
        
        try {
            $data = AramiscResultStore::where([
                ['class_id',$class_id],
                ['section_id',$section_id],
                ['exam_type_id',$exam_id],
                ['student_record_id',$student_id],
                ['subject_id',$subject_id]
                ])->first();

                return $data;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function  un_GetFinalResultBySubjectId($subject_id, $exam_id, $student_id, $request)
    {
        try {
            $AramiscResultStore = AramiscResultStore::query();
            $data = universityFilter($AramiscResultStore, $request)
            ->where([
                ['exam_type_id',$exam_id],
                ['student_id',$student_id],
                ['un_subject_id',$subject_id]
                ])->first();

                return $data;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function termBaseMark($class_id, $section_id, $subject_id,$exam_id,$student_id){
        $data = AramiscResultStore::where([
            ['class_id',$class_id],
            ['section_id',$section_id],
            ['exam_type_id',$exam_id],
            ['student_record_id',$student_id],
            ['subject_id',$subject_id]
            ])
            ->distinct('exam_type_id')
            ->sum('total_gpa_point');
            return $data;
    }

    public static function un_termBaseMark($subject_id, $exam_id, $student_id, $request){

        $AramiscResultStore = AramiscResultStore::query();
            $data = universityFilter($AramiscResultStore, $request)
            ->where([
                ['exam_type_id',$exam_id],
                ['student_id',$student_id],
                ['un_subject_id',$subject_id]
            ])
            ->distinct('exam_type_id')
            ->sum('total_gpa_point');
            return $data;
    }

    public function unSubjectDetails()
    {
        return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id');
    }

}
