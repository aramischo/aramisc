<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscBookCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscBookCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscBookCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
   public $book_categories=['Action and adventure','Alternate history','Anthology','Chick lit','Kids','Comic book','Coming-of-age','Crime','Drama',
    'Fairytale','Fantasy','Graphic novel','Historical fiction','Horror', 'Mystery','Paranormal romance'];

    public function definition()
    {
        static $i;
        $i = $i ?? 0;
        return [
           'category_name' => $this->book_categories[$i++] ?? $this->faker->sentences,
        ];
    }
}
