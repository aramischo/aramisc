<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscNewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscNewsCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscNewsCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $newCategory= ['International',  'Our history','Our mission and vision', 'National','Sports'];
    public $i= 0;
    public function definition()
    {
        return [
            'category_name' =>$this->newCategory[$this->i++] ?? $this->faker->word(10),
        ];
    }
}
