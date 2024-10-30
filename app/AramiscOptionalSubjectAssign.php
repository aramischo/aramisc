<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscOptionalSubjectAssign extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_optional_subject_assigns';
    public static function is_optional_subject($student_id, $subject_id)
    {
        try {
            $result = AramiscOptionalSubjectAssign::where('student_id', $student_id)->where('subject_id', $subject_id)->first();
            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    public function subject()
    {
        return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
    }

}
