<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscStudentHomework extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
   protected $table = 'sm_student_homeworks';
}
