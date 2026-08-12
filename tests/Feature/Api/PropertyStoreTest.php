<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PropertyStoreTest extends ApiTestCase
{
    public function test_authenticated_user_can_create_a_published_property(): void
    {
        [$user, $token] = $this->authUser(['name' => 'Yash Dhiman']);

        Storage::fake('public');

        $response = $this->withToken($token)
            ->post('/api/v1/properties', $this->propertyPayload([
                'image' => $this->fakeImage(),
            ]));

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.status', 'published')
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'description', 'property_type', 'price_per_night', 'image_url'],
            ]);

        $this->assertDatabaseHas('properties', [
            'title' => 'Sunny Two-Bedroom Apartment',
            'user_id' => $user->id,
        ]);

        $imagePath = $response->json('data.image');
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_unauthenticated_user_cannot_create_a_property(): void
    {
        $this->postJson('/api/v1/properties', $this->propertyPayload())
            ->assertStatus(401);
    }

    public function test_property_requires_a_valid_image(): void
    {
        [$user, $token] = $this->authUser();

        $this->withToken($token)
            ->postJson('/api/v1/properties', $this->propertyPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    public function test_property_validation_rejects_invalid_values(): void
    {
        [$user, $token] = $this->authUser();

        Storage::fake('public');

        $this->withToken($token)
            ->postJson('/api/v1/properties', $this->propertyPayload([
                'title' => '',
                'property_type' => 'Castle',
                'price_per_night' => 0,
                'guests' => 0,
                'image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'property_type', 'price_per_night', 'guests', 'image']);
    }

    public function test_property_slug_is_generated_from_title(): void
    {
        [$user, $token] = $this->authUser();

        Storage::fake('public');

        $this->withToken($token)
            ->post('/api/v1/properties', $this->propertyPayload([
                'title' => 'Beautiful Apartment in Chandigarh',
                'image' => $this->fakeImage(),
            ]))
            ->assertStatus(201)
            ->assertJsonPath('data.slug', 'beautiful-apartment-in-chandigarh');
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        [$user, $token] = $this->authUser();

        Storage::fake('public');

        $payload = $this->propertyPayload(['title' => 'Same Title', 'image' => $this->fakeImage()]);

        $this->withToken($token)->post('/api/v1/properties', $payload)->assertStatus(201);
        $this->withToken($token)->post('/api/v1/properties', $payload)->assertStatus(201);

        $this->assertDatabaseHas('properties', ['title' => 'Same Title', 'slug' => 'same-title']);
        $this->assertDatabaseHas('properties', ['title' => 'Same Title', 'slug' => 'same-title-2']);
    }

    public function test_user_can_create_a_draft_property(): void
    {
        [$user, $token] = $this->authUser();

        Storage::fake('public');

        $this->withToken($token)
            ->post('/api/v1/properties', $this->propertyPayload([
                'status' => Property::STATUS_DRAFT,
                'image' => $this->fakeImage(),
            ]))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'draft');
    }
}
