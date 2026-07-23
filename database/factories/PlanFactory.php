<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'monthly_price' => fake()->randomElement([999, 1999, 4999]),
            'yearly_price' => fake()->randomElement([9990, 19990, 49990]),
            'stripe_monthly_price_id' => 'price_monthly_'.Str::lower(Str::random(12)),
            'stripe_yearly_price_id' => 'price_yearly_'.Str::lower(Str::random(12)),
            'features' => [
                'Feature A',
                'Feature B',
                'Feature C',
            ],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
