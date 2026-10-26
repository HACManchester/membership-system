<?php

namespace Database\Factories;

use BB\Entities\Address;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * A member's address awaiting an admin's approval, as members' own changes
 * are saved.
 *
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'line_1' => $this->faker->streetAddress(),
            'line_2' => $this->faker->city(),
            'line_3' => $this->faker->county(),
            'line_4' => null,
            'postcode' => $this->faker->postcode(),
            // AddressRepository spots an unchanged address by this hash of its lines
            'hash' => fn (array $address) => md5(json_encode(
                Arr::only($address, ['line_1', 'line_2', 'line_3', 'line_4', 'postcode'])
            )),
        ];
    }

    public function approved(): static
    {
        return $this->state(['approved' => true]);
    }
}
