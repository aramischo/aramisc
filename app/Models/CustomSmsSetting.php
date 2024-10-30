<?php

namespace App\Models;

use App\AramiscSmsGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomSmsSetting extends Model
{
    use HasFactory; 
    public function smsGateway()
        {
            return $this->belongsTo('App\AramiscSmsGateway', 'gateway_id', 'id');
        }
   
}
