<?php

namespace Database\Factories;

use App\AramiscFeesMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscFeesMasterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscFeesMaster::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount'=>500+rand()%500,
        ];
    }
}
