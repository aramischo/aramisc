<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscChartOfAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscChartOfAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $items = ['Donation', 'Scholarship', 'Product Sales', 'Utility Bills'];
    public $i = 0;
    public $types = ['I', 'E'];
    public function definition()
    {
        return [
            'head' => $this->items[$this->i++] ?? $this->faker->word(10),
            'type' => $this->faker->randomElement($this->types),
        ];
    }
}
