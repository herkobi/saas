<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeatureType;
use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'code' => Str::slug($name, '_'),
            'slug' => Str::slug($name),
            'name' => ucfirst($name),
            'description' => fake()->sentence(),
            'type' => FeatureType::LIMIT,
            'unit' => 'adet',
            'is_active' => true,
        ];
    }
}
