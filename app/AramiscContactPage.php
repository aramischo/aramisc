<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscContactPage extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_contact_pages';
    protected $guarded = ['id'];
}