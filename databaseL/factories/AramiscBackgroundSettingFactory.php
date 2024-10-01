<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscBackgroundSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscBackgroundSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscBackgroundSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'Dashboard Background',
            'type' => 'image',
            'image' => 'public/backEnd/img/body-bg.jpg',
            'color' => '',          
            'is_default' => 1,
        ];
    }
}
