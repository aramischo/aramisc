<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscAdmissionQueryFollowup extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new AcademicSchoolScope);
    }
     // Spécifiez le nom de la table explicitement
     protected $table = 'aramisc_admission_query_followups';
    public function user(){
    	return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
