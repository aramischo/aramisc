<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmExamAttendance extends Model
{
    use HasFactory;
    public function aramiscExamAttendanceChild()
    {
        return $this->hasMany('App\SmExamAttendanceChild', 'exam_aramiscAttendance_id', 'id');
    }
    public function class()
    {
        return $this->belongsTo('App\SmClass', 'class_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\SmSection', 'section_id', 'id');
    }
    public function subject()
    {
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }

    // public function scopesClassSection($query){
    //     return $query->where('class_id',request()->class_id)->where('section_id',request()->section_id)->where('subject_id',request()->subject_id);
    // }
}
