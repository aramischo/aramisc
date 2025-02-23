<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscNews;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscNewsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscNews::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'news_title' => $this->faker->text(40),
            'view_count' => $this->faker->randomDigit,
            'active_status' =>1,
            'news_body' =>$this->faker->text(500),
            'image'=>'public/uploads/news/news.jpg',
            'publish_date' => date('Y-m-d'),        
          
        ];
    }
}
