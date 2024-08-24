<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use HasFactory;
    // Specify the table name explicitly
    protected $table = 'plugins';
    protected $casts= [ 'applicable_for' => 'array'];
}
