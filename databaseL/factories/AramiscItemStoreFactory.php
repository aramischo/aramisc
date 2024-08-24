<?php

namespace Database\Factories;

use App\AramiscItemStore;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscItemStoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscItemStore::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
             'store_name' => $this->faker->word(20),
             'store_no' => $this->faker->numberBetween(10,1000000000),            
        ];
    }
}
