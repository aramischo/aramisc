<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AramiscStudentExcelFormat extends Model
{
    public $timestamps = false;
    // Spécifiez le nom de la table explicitement
   protected $table = 'aramisc_student_excel_formats';
}
