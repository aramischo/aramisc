<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $i=0;
    public function definition()
    {
        return [
            'item_name' => $this->faker->colorName.$this->i++,
        ];
    }
}
