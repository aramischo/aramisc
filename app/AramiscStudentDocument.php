<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscStudentDocument extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
   protected $table = 'aramisc_student_documents';
}
