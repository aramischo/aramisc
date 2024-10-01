<?php

namespace Database\Factories;

use App\AramiscFeesDiscount;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscFeesDiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscFeesDiscount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $types=['once','year'];
    public $i=0;
    public function definition()
    {
        $i=$this->i++;
        return [
           'name' => $this->faker->unique()->word ?? $this->faker->colorName.$i,
           'code' => 'SB-0'.$i,
           'type' =>$this->faker->randomElement($this->types),
           'amount' => rand(100,200),
           'description' => $this->faker->word,
        ];
    }
}
