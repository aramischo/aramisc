<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscStudentAttendance extends Model
{
    use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = "sm_student_attendances";
    
    protected $casts = [
        'attendance_type' => 'string',
        'attendance_date' => 'string',
    ];
    
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new AcademicSchoolScope);
    }
    public function studentInfo()
    {
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }
    public function scopemonthAttendances($query, $month)
    {
        return $query->whereMonth('attendance_date', $month);
    }
}
