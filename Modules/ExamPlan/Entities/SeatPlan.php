<?php

namespace Modules\ExamPlan\Entities;

use App\Models\StudentRecord;
use App\AramiscAcademicYear;  
use App\AramiscExamType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeatPlan extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\ExamPlan\Database\factories\SeatPlanFactory::new();
    }

    public function studentRecord(){
        return $this->belongsTo(StudentRecord::class,'student_record_id','id');
    }

    public function examType(){
        return $this->belongsTo(AramiscExamType::class,'exam_type_id','id');
    }
    public function academicYear(){
        return $this->belongsTo(AramiscAcademicYear::class,'academic_id','id');
    }
}
