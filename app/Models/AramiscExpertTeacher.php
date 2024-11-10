<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscExpertTeacher extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function staff()
    {
        return $this->belongsTo('App\AramiscStaff', 'staff_id', 'id')->withDefault();
    }
}
