<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscLeaveDeductionInfo extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();
		static::addGlobalScope(new AcademicSchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_leave_deduction_infos';
}
