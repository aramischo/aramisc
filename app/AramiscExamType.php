<?php

namespace App;

use App\Scopes\GlobalAcademicScope;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscExamType extends Model
{
    use HasFactory;
    protected $fillable = ['percentage'];

    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
    ];

    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
        static::addGlobalScope(new GlobalAcademicScope);
    }
   // Spécifiez le nom de la table explicitement
   protected $table = 'sm_exam_types';
    public static function examType($assinged_exam_type)
    {
        try {
            return AramiscExamType::withOutGlobalScopes()->where('id', $assinged_exam_type)->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getScheduleSubject()
    {
        return $this->belongsTo(AramiscExamSchedule::class, 'exam_period_id');
    }

    public function examSetups()
    {
        return $this->hasMany(AramiscExamSetup::class, 'exam_term_id');
    }
    public function examsSetup()
    {
        return $this->hasMany(AramiscExamSetup::class, 'exam_term_id');
    }

    public function examTerm()
    {
        return $this->belongsTo(CustomResultSetting::class, 'id', 'exam_type_id');
    }

    public function examSettings()
    {
        return $this->belongsTo(AramiscExamSetting::class, 'id','exam_type');
    }
}
