<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscBankAccount extends Model
{

    protected $casts = [
        'id' => 'integer',
        'bank_name' => 'string',
        'account_name' => 'string',
        'account_number' => 'string',
        'account_type' => 'string',
        'opening_balance' => 'double',
        'current_balance' => 'double',
        'active_status' => 'integer',
        'school_id' => 'integer',
        'academic_id' => 'integer'
    ];

    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_bank_accounts';
}