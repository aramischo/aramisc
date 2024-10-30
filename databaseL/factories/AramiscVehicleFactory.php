<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscVehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscVehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscVehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'vehicle_no' =>'INFIX-'.rand(100,1000),
            'vehicle_model' =>'INFIX-M'.rand(100,1000),
            'made_year' =>date('Y'),          
            'note' =>$this->faker->sentence($nbWords =6, $variableNbWords = true),
        ];
    }
}
