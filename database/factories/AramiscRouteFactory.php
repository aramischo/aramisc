<?php

namespace Database\Factories;

use App\AramiscRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscRouteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscRoute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word(20),
            'far' => rand(100, 500),
        ];
    }
}
