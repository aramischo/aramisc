<?php

namespace Database\Factories;

use App\AramiscRoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscRoomTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscRoomType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $data = ['Single', 'Double', 'Triple', 'Quad', 'Queen', 'King'];
    public $i=0;
    public function definition()
    {

        return [
            'type' => $this->data[$this->i++] ?? $this->faker->unique()->colorName,
            'description' => $this->faker->colorName,
        ];
    }
}
