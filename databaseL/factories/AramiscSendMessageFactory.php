<?php

namespace Database\Factories;

use App\AramiscSendMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscSendMessageFactory extends Factory
{
    protected $model = AramiscSendMessage::class; 
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'message_title'=> $this->faker->realText($maxNbChars= 30, $indexSize= 2),
            'message_des'=> $this->faker->realText($maxNbChars= 100, $indexSize= 2),
            'notice_date'=> $this->faker->dateTime()->format('Y-m-d'),
            'publish_on'=> $this->faker->dateTime()->format('Y-m-d'),
            'message_to'=> "2,3,9",
            'created_at'=> date('Y-m-d h:i:s'),
        ];
    }
}
