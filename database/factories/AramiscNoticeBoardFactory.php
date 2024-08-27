<?php

namespace Database\Factories;

use App\AramiscNoticeBoard;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscNoticeBoardFactory extends Factory
{
    protected $model = AramiscNoticeBoard::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'notice_title' => $this->faker->title,
            'notice_message' => $this->faker->text(200),
            'notice_date' => $this->faker->date,
            'publish_on' => $this->faker->date,
            'inform_to' => '1,2,3,5,6',
            'is_published' => 1
        ];
    }
}
