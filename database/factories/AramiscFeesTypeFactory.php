<?php

namespace Database\Factories;

use App\AramiscFeesType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscFeesTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscFeesType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $fees_types = ['Library','Sports','Environment','E-learning','Fine','Extra-curricular activities','Laptop','Software','Uniforms','Transportation','Lunch','School Products'];
    public $i=0;
    public function definition()
    {
        return [
            'name' => $this->fees_types[$this->i++] ?? $this->faker->unique()->word,
        ];
    }
}
