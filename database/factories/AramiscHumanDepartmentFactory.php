<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscHumanDepartment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscHumanDepartmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscHumanDepartment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $departments = ['Academic', 'Arts', 'Commerce', 'Library', 'Sports', 'Science', 'Exam', 'Finance', 'Health', 'Technology', 'Music and Theater'];
    public $i = 0;
    public function definition()
    {
        return [
            'name' => $this->departments[$this->i++] ?? $this->faker->word,
        ];
    }
}
