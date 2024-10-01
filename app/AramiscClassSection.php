<?php

namespace App;

use App\Scopes\GlobalAcademicScope;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscClassSection extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new GlobalAcademicScope);
       // static::addGlobalScope(new StatusAcademicSchoolScope);
    }
     // Spécifiez le nom de la table explicitement
    protected $table = 'sm_class_sections';
    public function className()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id')->withDefault();
    }
    public function sectionName()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id')->withDefault();
    }

    public function sectionNameSaas()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    

    public function globalSectionName()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    public function globalClassName()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    public function students()
    {
        return $this->hasMany('App\AramiscStudent', 'section_id', 'section_id');
    }
    public function sectionWithoutGlobal()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id')->withoutGlobalScopes()->withDefault();
    }
}
