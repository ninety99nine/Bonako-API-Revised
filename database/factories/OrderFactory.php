<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'number' => str_pad($this->faker->unique()->numberBetween(1, 1000000), 5, "0", STR_PAD_LEFT),
            'amount' => $this->faker->randomFloat(2, 10, 10000),
        ];
    }
}
