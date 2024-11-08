<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscBankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscBankAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscBankAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $i=0;
    public function definition()
    {
        return [
            'bank_name'=> "Bank_ ".$this->i++ ,
            'account_name'=>$this->faker->name,
            'opening_balance'=>2000,
            'note'=>$this->faker->realText($maxNbChars = 100, $indexSize = 1),
            'active_status'=>1,
        ];
    }
}
