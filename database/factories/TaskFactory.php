<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company;
use App\Models\User;
use App\Models\Team;
use App\Models\Site;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory();
        $createdByUser = User::factory();

        return [
            'task_number' => fake()->unique()->numberBetween(1, 1000),
            'description' => fake()->sentence(8) . ' ' . fake()->optional(0.7)->paragraph(1),
            'status' => fake()->randomElement(['open', 'on_hold', 'completed', 'cancelled']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'company_id' => $company,
            'assigned_user_id' => fake()->optional(0.4)->randomElement([null, $createdByUser]),
            'assigned_team_id' => fake()->optional(0.3)->randomElement([null, Team::factory(['company_id' => $company])]),
            'site_id' => fake()->optional(0.5)->randomElement([null, Site::factory(['company_id' => $company])]),
            'created_by_user_id' => $createdByUser,
        ];
    }

    /**
     * Indicate that the task is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    /**
     * Indicate that the task is on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
        ]);
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the task is urgent priority.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }


}
