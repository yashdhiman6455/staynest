<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(Property::PROPERTY_TYPES);

        return [
            'user_id' => User::factory(),
            'title' => fake()->unique()->words(3, true),
            'slug' => fn (array $attributes) => Property::generateSlug($attributes['title']),
            'description' => fake()->paragraphs(3, true),
            'property_type' => $type,
            'location' => fake()->randomElement(['Chandigarh', 'Delhi', 'Jaipur', 'Manali', 'Goa', 'Mumbai', 'Bangalore']),
            'city' => fn (array $attributes) => $attributes['location'],
            'country' => 'India',
            'price_per_night' => fake()->numberBetween(800, 12000),
            'guests' => fake()->numberBetween(1, 10),
            'bedrooms' => fake()->numberBetween(0, 5),
            'bathrooms' => fake()->numberBetween(0, 4),
            'image' => null,
            'status' => Property::STATUS_PUBLISHED,
        ];
    }

    /**
     * Mark the property as published (default behaviour, kept for clarity).
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Property::STATUS_PUBLISHED,
        ]);
    }

    /**
     * Mark the property as a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Property::STATUS_DRAFT,
        ]);
    }
}
