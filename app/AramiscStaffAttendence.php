<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscStaffAttendence extends Model
{
    use HasFactory;
    protected $table = "aramisc_staff_attendences";

    public function StaffInfo()
    {
        return $this->belongsTo('App\AramiscStaff', 'staff_id', 'id');
    }
}
