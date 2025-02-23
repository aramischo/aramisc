<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscClassTime extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
  
       // static::addGlobalScope(new AcademicSchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_class_times';
    public function examSchedules()
    {
        return $this->hasMany(AramiscExamSchedule::class,'exam_period_id');
    }

    public function routineUpdates()
    {
        return $this->hasMany(AramiscClassRoutineUpdate::class,'class_period_id')->where('academic_id',getAcademicId());
    }
    public function studentRoutineUpdates()
    {
        return $this->hasMany(AramiscClassRoutineUpdate::class,'class_period_id')->where('academic_id',getAcademicId())->where('class_id', $this->class_id)
            ->where('section_id', $this->section_id);
    }
}
