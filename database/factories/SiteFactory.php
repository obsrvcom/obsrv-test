<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' ' . fake()->randomElement(['Office', 'Branch', 'Location', 'Site', 'Facility']),
            'address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->stateAbbr() . ' ' . fake()->postcode(),
            'company_id' => Company::factory(),
        ];
    }
}
