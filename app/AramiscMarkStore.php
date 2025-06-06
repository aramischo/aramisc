<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Modules\University\Entities\UnSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\University\Entities\UnSemesterLabel;

class AramiscMarkStore extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_mark_stores';
    public function class(){
        if(moduleStatusCheck('University')){
            return $this->belongsTo(UnSemesterLabel::class, 'un_semester_label_id', 'id');
        }else{
            return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
        }
        
    }
     public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }

    public function subjectName()
    {
        if(moduleStatusCheck('University')){
            return $this->belongsTo(UnSubject::class, 'un_subject_id', 'id');
        }else{
            return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
        }
    }
 
    public static function get_mark_by_part($student_id, $exam_id, $class_id, $section_id, $subject_id, $exam_setup_id, $record_id){
    	
        try {
            $getMark= AramiscMarkStore::where([
                ['student_id',$student_id], 
                ['exam_term_id',$exam_id], 
                ['class_id',$class_id], 
                ['section_id',$section_id], 
                ['exam_setup_id',$exam_setup_id], 
                ['student_record_id', $record_id], 
                ['subject_id',$subject_id]
            ])->first();
            if(!empty($getMark)){
                $output= $getMark->total_marks;
            }else{
                $output= '0';
            }

            return $output;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }


    public static function un_get_mark_by_part($student_id, $request, $exam_id, $subject_id, $exam_setup_id, $record_id)
    {
        try {
            $AramiscMarkStore = AramiscMarkStore::query();
            $getMark = universityFilter($AramiscMarkStore, $request)
                ->where([
                ['student_id',$student_id], 
                ['exam_term_id',$exam_id], 
                ['exam_setup_id',$exam_setup_id], 
                ['student_record_id', $record_id], 
                ['un_subject_id',$subject_id]
            ])->first();
            
            if(!empty($getMark)){
                $output= $getMark->total_marks;
            }else{
                $output= '0';
            }
            return $output;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function is_absent_check($student_id, $exam_id, $class_id, $section_id, $subject_id, $record_id)
    {
        
        try {
            $getMark= AramiscMarkStore::where([
                ['student_id',$student_id], 
                ['exam_term_id',$exam_id], 
                ['class_id',$class_id], 
                ['student_record_id', $record_id], 
                ['section_id',$section_id], 
                ['subject_id',$subject_id]
            ])->first();
            if (!empty($getMark)) {
                $output= $getMark->is_absent;
            } else {
                $output= '0';
            }
            return $output;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function un_is_absent_check($student_id, $exam_id, $request, $subject_id, $record_id)
    {
        try {
            $AramiscMarkStore = AramiscMarkStore::query();
            $getMark = universityFilter($AramiscMarkStore, $request)
            ->where([
                ['student_id',$student_id], 
                ['exam_term_id',$exam_id],
                ['student_record_id', $record_id], 
                ['subject_id',$subject_id]
            ])->first();
            if (!empty($getMark)) {
                $output= $getMark->is_absent;
            } else {
                $output= '0';
            }
            return $output;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function teacher_remarks($student_id, $exam_id, $class_id, $section_id, $subject_id, $record_id) {
        
        $getMark= AramiscMarkStore::where([
            ['student_id',$student_id], 
            ['exam_term_id',$exam_id], 
            ['class_id',$class_id], 
            ['section_id',$section_id], 
            ['student_record_id', $record_id], 
            ['subject_id',$subject_id]
        ])->first();

        if($getMark != ""){
            $output= $getMark->teacher_remarks;
        }else{
            $output= '';
        }

        return $output;
    }

    public static function un_teacher_remarks($student_id, $exam_id, $request, $subject_id, $record_id) {
        
        $AramiscMarkStore = AramiscMarkStore::query();
            $getMark = universityFilter($AramiscMarkStore, $request)
            ->where([
            ['student_id',$student_id], 
            ['exam_term_id',$exam_id],
            ['student_record_id', $record_id], 
            ['un_subject_id',$subject_id]
        ])->first();

        if($getMark != ""){
            $output= $getMark->teacher_remarks;
        }else{
            $output= '';
        }

        return $output;
    }

    public static function allMarksArray($exam_id, $class_id, $section_id, $subject_id)
    {
        $all_student_marks = [];

        $marks = AramiscResultStore::where('class_id', $class_id)->where('section_id', $section_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam_id)->get();

        foreach($marks as $mark){
            $all_student_marks[] = $mark->total_marks;
        }


        return $all_student_marks;

    }

}
