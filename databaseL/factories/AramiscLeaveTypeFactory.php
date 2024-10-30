<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscLeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscLeaveTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscLeaveType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $leaveType=['Casual Leave','Sick Leave', 'Annual/Vacation Leave','Earned Leave','Public holidays','Maternity/Paternity','Administrative leave'];
    public $i =0;
    public function definition()
    {
        return [
            'type'=>$this->leaveType[$this->i++],
            'total_days'=>rand(5,10),
        ];
    }
}
