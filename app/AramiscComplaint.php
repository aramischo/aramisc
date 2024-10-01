<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class AramiscComplaint extends Model
{
        
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_complaints';
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
   
    public function complaintType(){
    	return $this->belongsTo('App\AramiscSetupAdmin', 'complaint_type', 'id');
    }

    public function complaintSource(){
    	return $this->belongsTo('App\AramiscSetupAdmin', 'complaint_source', 'id');
    }
}
