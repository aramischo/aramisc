<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class AramiscFeesType extends Model
{
    use HasFactory;
     // Spécifiez le nom de la table explicitement
     protected $table = 'aramisc_fees_types';
    protected $fillable = ['name', 'fees_group_id', 'un_semester_label_id', 'school_id','un_subject_id','un_academic_id'];
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    
    public function fessGroup()
    {
        return $this->belongsTo('App\AramiscFeesGroup', 'fees_group_id');
    }
}
