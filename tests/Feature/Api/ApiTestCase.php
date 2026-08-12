<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with an authenticated Sanctum token.
     */
    protected function authUser(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('test-token')->plainTextToken;

        return [$user, $token];
    }

    /**
     * Build a fully-valid property payload for create/update requests.
     */
    protected function propertyPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Sunny Two-Bedroom Apartment',
            'description' => 'A bright, modern apartment in the heart of the city with a fully equipped kitchen.',
            'property_type' => 'Apartment',
            'location' => 'Sector 17',
            'city' => 'Chandigarh',
            'country' => 'India',
            'price_per_night' => 2500,
            'guests' => 4,
            'bedrooms' => 2,
            'bathrooms' => 2,
        ], $overrides);
    }

    /**
     * Return a fake image file to attach to a request.
     */
    protected function fakeImage(string $name = 'property.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name, 800, 600);
    }

    /**
     * Create a published property owned by the given user.
     */
    protected function publishedProperty(User $user, array $attributes = []): Property
    {
        return Property::factory()->published()->create([
            'user_id' => $user->id,
            ...$attributes,
        ]);
    }

    /**
     * Create a draft property owned by the given user.
     */
    protected function draftProperty(User $user, array $attributes = []): Property
    {
        return Property::factory()->draft()->create([
            'user_id' => $user->id,
            ...$attributes,
        ]);
    }

}
