<?php

namespace App\Models;

use App\AramiscStaff;
use App\AramiscAssignSubject;
use App\Models\StudentRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherEvaluation extends Model
{
    use HasFactory;
    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class, 'record_id', 'id')->withDefault();
    }
    public function staff()
    {
        return $this->belongsTo(AramiscStaff::class, 'teacher_id', 'id')->withDefault();
    }
    public function assignSubject()
    {
        return $this->belongsTo(AramiscAssignSubject::class, 'subject_id', 'id')->withDefault();
    }
}
