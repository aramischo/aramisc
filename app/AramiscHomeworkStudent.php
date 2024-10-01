<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscHomeworkStudent extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_homework_students';
    protected static function boot(){
        parent::boot();
    }

    public function studentInfo(){
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }
    
    public function users(){
    	return $this->belongsTo('App\User', 'created_by', 'id');

    }
    public function homeworkDetail(){
    	return $this->belongsTo('App\AramiscHomework', 'homework_id', 'id');

    }
}
