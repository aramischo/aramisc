<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscStudentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscStudentCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscStudentCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $category = [
        'Normal',
        'Obsessive Compulsive Disorder',
        'Attention Deficit Hyperactivity Disorder (ADHD)',
        'Oppositional Defiant Disorder (ODD)',
        'Anxiety Disorder',
        'Conduct Disorders',
    ];

    public $i=0;

    public function definition()
    {
        return [
            'category_name'=>  $this->category[$this->i++] ?? $this->faker->word,
        ];
    }
}
