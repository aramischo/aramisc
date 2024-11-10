<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscClassTeacher extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_class_teachers';
    public function teacher(){
    	return $this->belongsTo('App\AramiscStaff', 'teacher_id', 'id');
    }

    public function teacherClass(){
        return $this->belongsTo(AramiscAssignClassTeacher::class,'assign_class_teacher_id','id');
    }
}
