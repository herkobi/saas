<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlanInterval;
use App\Helpers\CurrencyHelper;
use App\Models\Plan;
use App\Models\PlanPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanPriceFactory extends Factory
{
    protected $model = PlanPrice::class;

    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'price' => fake()->randomElement([29.99, 49.99, 99.99, 199.99, 499.99]),
            'currency' => CurrencyHelper::defaultCode(),
            'interval' => PlanInterval::MONTH,
            'interval_count' => 1,
            'trial_days' => 14,
        ];
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'interval' => PlanInterval::YEAR,
            'interval_count' => 1,
        ]);
    }

    public function noTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_days' => 0,
        ]);
    }
}
