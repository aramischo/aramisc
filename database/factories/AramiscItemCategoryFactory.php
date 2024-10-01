<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscItemCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscItemCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscItemCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $i=0;
    public function definition()
    {
        return [
            'category_name' => $this->faker->colorName . $this->i++ ,
        ];
    }
}
