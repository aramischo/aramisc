<?php

namespace Database\Factories;

use App\AramiscExamType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscExamTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscExamType::class;

    public $exam_type=['First Term','Second Term','Third Term'];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $i;
        $i = $i ?? 0;
        return [
            'title' => $this->exam_type[$i++] ?? $this->faker->word,
        ];
    }
}
