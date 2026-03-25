<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id'      => Project::factory(),
            'title'           => fake()->sentence(4),
            'description'     => fake()->optional()->paragraph(),
            'priority'        => fake()->randomElement(['high', 'medium', 'low']),
            'status'          => fake()->randomElement(['backlog', 'in_progress', 'testing', 'done']),
            'estimated_hours' => fake()->optional()->randomFloat(2, 0.5, 40),
            'position'        => fake()->numberBetween(0, 100),
        ];
    }
}
