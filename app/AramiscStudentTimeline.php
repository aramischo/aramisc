<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscStudentTimeline extends Model
{
    use HasFactory;
// Spécifiez le nom de la table explicitement
    protected $table = 'sm_student_timelines';
    protected $casts = [
        'id'            => 'integer',
        'date'          => 'string',
        'title'         => 'string',
        'description'   => 'string',
        'file'          => 'string',
        'created_at'    => 'string',
    ];
}
