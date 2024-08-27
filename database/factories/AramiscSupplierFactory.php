<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscSupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscSupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscSupplier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_name' => $this->faker->company,
            'company_address' =>  $this->faker->address,
            'contact_person_name' => $this->faker->name,
        ];
    }
}
