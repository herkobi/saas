<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AddonType;
use App\Enums\PlanInterval;
use App\Models\Addon;
use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AddonFactory extends Factory
{
    protected $model = Addon::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'feature_id' => Feature::factory(),
            'addon_type' => AddonType::INCREMENT,
            'value' => fake()->numberBetween(5, 50),
            'price' => fake()->randomFloat(2, 10, 500),
            'currency' => 'TRY',
            'is_recurring' => false,
            'interval' => null,
            'interval_count' => null,
            'is_active' => true,
            'is_public' => true,
        ];
    }

    public function recurring(PlanInterval $interval = PlanInterval::MONTH, int $count = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'interval' => $interval,
            'interval_count' => $count,
        ]);
    }

    public function oneTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => false,
            'interval' => null,
            'interval_count' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
