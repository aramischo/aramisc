<?php

namespace App;

use App\Scopes\GlobalAcademicScope;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscSection extends Model
{
    //
    use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = "sm_sections";
    protected static function boot()
    {
        parent::boot();
       // static::addGlobalScope(new GlobalAcademicScope);
       static::addGlobalScope(new StatusAcademicSchoolScope);
    }

    public function students()
    {
        return $this->hasMany('App\AramiscStudent', 'section_id', 'id');
    }
    public function unAcademic()
    {
        return $this->belongsTo('Modules\University\Entities\UnAcademicYear', 'un_academic_id', 'id')->withDefault();
    }
}
