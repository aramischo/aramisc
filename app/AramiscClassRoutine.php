<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscClassRoutine extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_class_routines';
    public function subject(){
    	return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
    }

    public function class(){
    	return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }

    public function section(){
    	return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }
    
    public static function teacherId($class_id, $section_id, $subject_id){
    	
        try {
            return AramiscAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->where('subject_id', $subject_id)->first();
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
}
