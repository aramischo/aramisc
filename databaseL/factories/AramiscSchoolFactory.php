<?php

namespace Database\Factories;

use App\AramiscSchool;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscSchoolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscSchool::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $i=1;
    public function definition()
    {
        $i=$this->i++;
        return [
            
            'school_name'=>$this->faker->colorName . $i,
            'email'=>'school_'.$i.'@aramiscdu.com',
            'domain'=> 'school'.$i,
            'created_at' => date('Y-m-d h:i:s'),
            'starting_date' => date('Y-m-d'),
            'is_email_verified' => 1,            
        ];
    }
}
