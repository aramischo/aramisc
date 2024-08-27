<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscMarksRegisterChild extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_marks_register_children';
    public function subject(){
    	return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
    }
}
