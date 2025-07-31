<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->randomElement(['iPhone 15 Pro', 'Samsung Galaxy S24', 'iPad Pro', 'MacBook Pro', 'Windows PC', 'Android Tablet']),
            'type' => $this->faker->randomElement(['mobile', 'tablet', 'desktop', 'web_browser']),
            'user_id' => null,
            'revoked' => false,
            'last_seen' => now(),
            'session_id' => null,
            'user_agent' => $this->faker->userAgent(),
            'ip_address' => $this->faker->ipv4(),
            'fingerprint' => hash('sha256', $this->faker->text()),
        ];
    }

    /**
     * Indicate that the device is paired with a user.
     */
    public function paired(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => \App\Models\User::factory(),
        ]);
    }

    /**
     * Indicate that the device is a web browser.
     */
    public function webBrowser(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'web_browser',
            'session_id' => Str::random(40),
        ]);
    }

    /**
     * Indicate that the device is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked' => true,
        ]);
    }
}
