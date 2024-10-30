<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AramiscAssignClassTeacher extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_assign_class_teachers';
    public function class()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }

    public function classTeachers()
    {
        return $this->hasMany('App\AramiscClassTeacher', 'assign_class_teacher_id', 'id');
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('school_id', Auth::user()->school_id);
    }

}
