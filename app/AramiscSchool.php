<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\Saas\Entities\SmSubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscSchool extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $guarded = ['id'];
    public function subscription()
    {
        return $this->hasOne(SmSubscriptionPayment::class, 'school_id')->latest();
    }

    public function academicYears()
    {
        return $this->hasMany(AramiscAcademicYear::class, 'school_id', 'id');
    }

    public function sections()
    {
        return $this->hasMany(AramiscSection::class, 'school_id');
    }

    public function classes()
    {
        return $this->hasMany(AramiscClass::class, 'school_id');
    }

    public function classTimes()
    {
        return $this->hasMany(AramiscClassTime::class, 'school_id')->where('type', 'class');
    }
    public function weekends()
    {
        return $this->hasMany(AramiscWeekend::class, 'school_id')->where('active_status', 1);
    }
    public function routineUpdates()
    {
        return $this->hasMany(AramiscClassRoutineUpdate::class, 'school_id','id')->where('active_status', 1);
    }

    public function saasRoutineUpdates()
    {
        return $this->hasMany(AramiscClassRoutineUpdate::class, 'school_id','id')->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('active_status', 1);
    }
}
