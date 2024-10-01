<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscSetupAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscSetupAdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscSetupAdmin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(10),
            'type' =>rand(1,4),
        ];
    }
}
