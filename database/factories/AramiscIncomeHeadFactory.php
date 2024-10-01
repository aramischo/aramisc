<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscIncomeHead;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscIncomeHeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscIncomeHead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'=>$this->faker->word,
            'description'=>$this->faker->realText($maxNbChars = 200, $indexSize = 1),
        ];
    }
}
