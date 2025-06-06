<?php

namespace Database\Factories;

use App\AramiscAdmissionQueryFollowup;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscAdmissionQueryFollowupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscAdmissionQueryFollowup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'response' => $this->faker->sentence($nbWords = 3, $variableNbWords = true),
            'note' => $this->faker->sentence($nbWords = 4, $variableNbWords = true),
            'date' => $this->faker->dateTime()->format('Y-m-d'),
        ];
    }
}
