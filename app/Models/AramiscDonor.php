<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmDonor extends Model
{
    use HasFactory;
    public function gender()
    {
        return $this->belongsTo('App\AramiscBaseSetup', 'gender_id', 'id')->withDefault();
    }

    public function religion()
    {
        return $this->belongsTo('App\AramiscBaseSetup', 'religion_id', 'id')->withDefault();
    }

    public function bloodGroup()
    {
        return $this->belongsTo('App\AramiscBaseSetup', 'bloodgroup_id', 'id')->withDefault();
    }
}
