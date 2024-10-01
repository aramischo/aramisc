<?php

namespace Database\Factories;

use App\AramiscEmailSmsLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscEmailSmsLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscEmailSmsLog::class;

    public function definition()
    {
        return [
            'title' => $this->faker->title,
            'description' => $this->faker->text(),
            'send_date' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            'send_through' => 'E',
            'send_to' => 'G',
        ];
    }
}