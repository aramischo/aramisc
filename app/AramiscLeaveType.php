<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscLeaveType extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer'
    ];

    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
     // Spécifiez le nom de la table explicitement
     protected $table = 'aramisc_leave_types';
    public function leaveDefines()
    {
        return $this->hasMany(AramiscLeaveDefine::class, 'type_id');
    }
}
