<?php

namespace App;

use App\Models\StudentRecord;
use App\Scopes\GlobalAcademicScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BehaviourRecords\Entities\AssignIncident;

class AramiscClass extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
        static::addGlobalScope(new GlobalAcademicScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_classes';
    protected $casts = [
        'id'            => 'integer',
        'class_name'    => 'string',
    ];


    public function classSection()
    {
      return $this->hasMany('App\AramiscClassSection', 'class_id')->with('sectionName');

      
    }
    public function classSectionAll(){
        return $this->belongsToMany('App\AramiscSection','aramisc_class_sections','class_id','section_id');
    }

    public function sectionName()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id');
    }

    public function sections()
    {
        return $this->hasMany('App\AramiscSection', 'id', 'section_id');
    }

    public function records()
    {
        return $this->hasMany(StudentRecord::class, 'class_id', 'id')->where('is_promote', 0)->whereHas('student');
    }
    public function allIncident()
    {
        return $this->hasManyThrough(AssignIncident::class, StudentRecord::class, 'class_id', 'record_id', 'id', 'id');
    }

    public function classSections()
    {
        return $this->hasMany('App\AramiscClassSection', 'class_id', 'id');
    }
    public function groupclassSections()
    {
        return $this->hasMany('App\AramiscClassSection', 'class_id', 'id')->with('sectionName');
    }


    public function globalGroupclassSections()
    {
        return $this->hasMany('App\AramiscClassSection', 'class_id', 'id')->distinct(['class_id','section_id'])->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->with('sectionName');
    }

    public function students()
    {
        return $this->hasMany('App\AramiscStudent', 'user_id', 'id');
    }

    public function subjects()
    {
        return $this->hasMany(AramiscAssignSubject::class, 'class_id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function routineUpdates()
    {
        return $this->hasMany(AramiscClassRoutineUpdate::class, 'class_id')->where('active_status', 1);
    }

    public function academic()
    {
        return $this->belongsTo('App\AramiscAcademicYear', 'academic_id', 'id')->withDefault();
    }
}