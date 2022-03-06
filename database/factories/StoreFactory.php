<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'call_to_action' => $this->faker->randomElement(['Buy', 'Order', 'Pre-order']),
            'registered_with_bank' => $this->faker->randomElement(Store::CLOSED_ANSWERS),
            'banking_with' => $this->faker->randomElement(Store::BANKING_WITH),
            'registered_with_cipa' => $this->faker->randomElement(Store::CLOSED_ANSWERS),
            'registered_with_cipa_as' => $this->faker->randomElement(Store::REGISTERED_WITH_CIPA_AS),
            'company_uin' => $this->faker->unique()->numerify('BW###########'),
            'number_of_employees' => $this->faker->numberBetween(1, 50),
            'accepted_golden_rules' => true,
            'user_id' => 2,
        ];
    }
}
