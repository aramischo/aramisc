<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscPage extends Model
{
    use HasFactory;
     // Spécifiez le nom de la table explicitement
     protected $table = 'sm_pages';
     
}
