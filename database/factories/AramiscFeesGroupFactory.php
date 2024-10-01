<?php

namespace Database\Factories;

use App\AramiscFeesGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscFeesGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscFeesGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $fees_groups=['Library Fee', 'Processing Fee', 'Tuition Fee', 'Development Fee'];
    public $i=0;
    public function definition()
    {
        return [
            'name' => $this->fees_groups[$this->i++] ?? $this->faker->unique()->colorName,
            'type' => 'System',
        ];
    }
}
