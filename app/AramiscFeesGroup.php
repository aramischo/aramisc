<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscFeesGroup extends Model
{
    use HasFactory;
     // Spécifiez le nom de la table explicitement
    protected $table = "sm_fees_groups";
    protected $fillable = ['name', 'description', 'created_by', 'active_status', 'school_id', 'un_semester_label_id','un_subject_id','un_academic_id'];
	
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new AcademicSchoolScope);
    }
    public function feesMasters(){
		return $this->hasmany('App\AramiscFeesMaster', 'fees_group_id');
	}


}
