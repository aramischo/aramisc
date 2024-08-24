<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscExamAttendance extends Model
{
    use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = "sm_exam_attendances";
    public function examAttendanceChild()
    {
        return $this->hasMany('App\AramiscExamAttendanceChild', 'exam_attendance_id', 'id');
    }
    public function class()
    {
        return $this->belongsTo('App\SmClass', 'class_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }
    public function subject()
    {
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }

    // public function scopesClassSection($query){
    //     return $query->where('class_id',request()->class_id)->where('section_id',request()->section_id)->where('subject_id',request()->subject_id);
    // }
}
