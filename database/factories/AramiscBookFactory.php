<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscBook;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscBookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscBook::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $books = [
        'Algorithms & Data Structures', 'Cellular Automata', 'Cloud Computing', 'Competitive Programming', 'Compiler Design', 'Database', 'Datamining', 'Information Retrieval', 'Licensing', 'Machine Learning', 'Mathematics'
    ];
    public function definition()
    {
        static $i;
        $i = $i ?? 0;
        return [
            
            'book_title' => $this->books[$i++] ?? $this->faker->word(20),

        ];
    }
}
