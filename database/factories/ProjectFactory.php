<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weightLbs = fake()->randomFloat(2, 1000, 500000);

        return [
            'job_number' => fake()->unique()->regexify('[A-Z]{2}[0-9]{4}'),
            'name' => fake()->words(3, true),
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(['bid', 'active', 'production', 'shipping', 'complete']),
            'job_type' => fake()->randomElement(['new_construction', 'renovation', 'repair', 'miscellaneous']),
            'po_number' => fake()->optional(0.7)->regexify('PO-[0-9]{6}'),
            'contract_weight_lbs' => $weightLbs,
            'contract_weight_kg' => round($weightLbs * 0.453592, 2),
            'ship_date' => fake()->optional(0.8)->dateTimeBetween('now', '+6 months'),
            'notes' => fake()->optional(0.4)->paragraph(),
        ];
    }

    /**
     * Indicate that the project is in bid status.
     */
    public function bid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'bid',
        ]);
    }

    /**
     * Indicate that the project is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the project is complete.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'complete',
        ]);
    }
}
