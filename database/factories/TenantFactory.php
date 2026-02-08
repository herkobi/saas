<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->company();
        $code = Str::upper(Str::random(12));

        return [
            'code' => $code,
            'slug' => Str::slug($name . '-' . $code),
            'status' => SubscriptionStatus::ACTIVE,
            'account' => [
                'title' => $name,
            ],
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::EXPIRED,
        ]);
    }
}
