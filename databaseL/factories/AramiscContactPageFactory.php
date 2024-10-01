<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscContactUs;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscContactPageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscContactUs::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'Contact Us',
        ];
    }
}
