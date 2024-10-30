<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AramiscStaffAttendanceImport extends Model
{
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_staff_attendance_imports';
    protected $fillable  = ['attendence_date','in_time','out_time','attendance_type','notes','staff_id'];
}