<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscLibraryMember extends Model
{
    use HasFactory;

    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
     // Spécifiez le nom de la table explicitement
     protected $table = 'sm_library_members';
    public function roles()
    {
        return $this->belongsTo('Modules\RolePermission\Entities\InfixRole', 'member_type', 'id');
    }
    public function studentDetails()
    {
        return $this->belongsTo('App\AramiscStudent', 'student_staff_id', 'user_id');
    }
    public function staffDetails()
    {
        return $this->belongsTo('App\AramiscStaff', 'student_staff_id', 'user_id');
    }
    public function parentsDetails()
    {
        return $this->belongsTo('App\AramiscParent', 'student_staff_id', 'user_id');
    }
    public function memberTypes()
    {
        return $this->belongsTo('Modules\RolePermission\Entities\InfixRole', 'member_type', 'id');
    }
    public function scopeStatus($query)
    {
        $query->where('school_id', auth()->user()->school_id)->where('academic_id', getAcademicId());
    }
}
