<?php

namespace Database\Factories;

use App\AramiscHourlyRate;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmHourRateFactory extends Factory
{

    protected $model = AramiscHourlyRate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'grade'=>'A+',
            'rate'=>20,
        ];
    }
}
