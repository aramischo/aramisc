<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmStudentAttendance extends Model
{
    use HasFactory;
    protected $table = "sm_student_aramiscAttendances";
    
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new AcademicSchoolScope);
    }
    public function studentInfo()
    {
        return $this->belongsTo('App\SmStudent', 'student_id', 'id');
    }
    public function scopemonthAttendances($query, $month)
    {
        return $query->whereMonth('aramiscAttendance_date', $month);
    }
}
