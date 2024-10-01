<?php

namespace App;

use App\Scopes\GlobalAcademicScope;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscSubject extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_subjects';
    protected $casts = [
        'id'            => 'integer',
        'subject_name'  => 'string',
        'subject_code'  => 'string',
        'subject_type'  => 'string',
    ];

    
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new GlobalAcademicScope);
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }

//

}