<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AramiscStudentPromotion extends Model
{
   // Spécifiez le nom de la table explicitement
   protected $table = 'aramisc_student_promotios';
    public function student(){
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }

    public function class(){
		return $this->belongsTo('App\AramiscClass', 'previous_class_id', 'id');
    }
    
}
