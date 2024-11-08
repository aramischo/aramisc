<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscDesignation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscDesignationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscDesignation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $designations=[
      'Assistant Head Master','Assistant Teacher','Senior Teacher','Senior Assistant Teacher','Faculty','Accountant','Librarian','Admin','Receptionist','Principal','Director'];
    public $i=0;  
    public function definition()
    {
        return [
            'title' => $this->designations[$this->i++] ?? $this->faker->word,
        ];
    }
}
