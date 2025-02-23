<?php
namespace Modules\Lesson\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscLessonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Lesson\Entities\AramiscLesson::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'lesson_title' => $this->faker->word(20),
        ];
    }
}

