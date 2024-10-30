<?php

namespace Database\Factories;


use App\AramiscWeekend;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscWeekendFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscWeekend::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $days=['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    public $i=0;
    public function definition()
    {
        return [
            'name' => $this->days[$this->i++],
            'order' => $this->i + 1,
        ];
    }
}
