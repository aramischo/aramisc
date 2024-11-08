<?php

namespace Database\Factories;


use App\AramiscPostalDispatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscPostalDispatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscPostalDispatch::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
           
            'from_title' => $this->faker->name,
            'to_title' => $this->faker->name,
            'reference_no' => $this->faker->ean8,
            'address' => $this->faker->address,
            'date' => $this->faker->dateTime()->format('Y-m-d'),
            'note' => $this->faker->realText($maxNbChars = 100, $indexSize = 1),
        ];
    }
}
