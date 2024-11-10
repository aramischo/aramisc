<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscExamScheduleSubject extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_exam_schedule_subjects';
    public function subject(){
    	return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
    }
}
