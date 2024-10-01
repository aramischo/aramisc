<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscExpenseHead;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscExpenseHeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscExpenseHead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->realText($maxNbChars = 100, $indexSize = 1),
        ];
    }
}
