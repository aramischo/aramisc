<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmOnlineExamMark extends Model
{
    use HasFactory;
    public function studentInfo()
    {
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }
}
