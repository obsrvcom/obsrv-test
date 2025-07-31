<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email_address' => fake()->unique()->safeEmail(),
            'company_name' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'company_id' => Company::factory(),
        ];
    }
}
