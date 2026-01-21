<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\WorkArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkArea>
 */
class WorkAreaFactory extends Factory
{
    protected $model = WorkArea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{2}'),
            'name' => fake()->randomElement(['Cutting', 'Drilling', 'Punching', 'Bending', 'Welding', 'Blasting', 'Painting', 'Assembly']),
            'description' => fake()->optional(0.5)->sentence(),
            'sort_order' => fake()->numberBetween(1, 50),
            'is_active' => true,
            'badge_barcode' => fake()->optional(0.3)->regexify('[0-9]{12}'),
        ];
    }

    /**
     * Indicate that the work area is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
