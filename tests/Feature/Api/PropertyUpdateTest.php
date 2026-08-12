<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Storage;

class PropertyUpdateTest extends ApiTestCase
{
    public function test_owner_can_update_their_property(): void
    {
        [$user, $token] = $this->authUser();

        $property = $this->publishedProperty($user, ['title' => 'Old Title']);

        $response = $this->withToken($token)
            ->putJson("/api/v1/properties/{$property->id}", $this->propertyPayload([
                'title' => 'New Title',
                'price_per_night' => 5000,
            ]));

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'New Title')
            ->assertJsonPath('data.price_per_night', '5000.00')
            ->assertJsonPath('data.slug', 'new-title');

        $this->assertDatabaseHas('properties', ['id' => $property->id, 'title' => 'New Title']);
    }

    public function test_owner_can_replace_the_image(): void
    {
        [$user, $token] = $this->authUser();

        Storage::fake('public');

        $property = $this->publishedProperty($user, ['title' => 'With Image']);
        Storage::disk('public')->put('properties/old-image.jpg', 'old');
        $property->forceFill(['image' => 'properties/old-image.jpg'])->save();

        $response = $this->withToken($token)
            ->put('/api/v1/properties/'.$property->id, $this->propertyPayload([
                'title' => 'With New Image',
                'image' => $this->fakeImage(),
            ]));

        $response->assertStatus(200);

        $newImage = $response->json('data.image');
        $this->assertNotSame('properties/old-image.jpg', $newImage);
        Storage::disk('public')->assertMissing('properties/old-image.jpg');
        Storage::disk('public')->assertExists($newImage);
    }

    public function test_owner_can_keep_the_existing_image_when_not_uploading(): void
    {
        [$user, $token] = $this->authUser();

        Storage::fake('public');
        Storage::disk('public')->put('properties/existing.jpg', 'existing');

        $property = $this->publishedProperty($user, ['title' => 'Keep Image']);
        $property->forceFill(['image' => 'properties/existing.jpg'])->save();

        $this->withToken($token)
            ->putJson("/api/v1/properties/{$property->id}", $this->propertyPayload(['title' => 'Keep Image v2']))
            ->assertStatus(200)
            ->assertJsonPath('data.image', 'properties/existing.jpg');
    }

    public function test_update_validation_rejects_invalid_data(): void
    {
        [$user, $token] = $this->authUser();

        $property = $this->publishedProperty($user, ['title' => 'Valid Title']);

        $this->withToken($token)
            ->putJson("/api/v1/properties/{$property->id}", [
                'title' => '',
                'description' => '',
                'property_type' => 'Invalid',
                'location' => '',
                'price_per_night' => 0,
                'guests' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'property_type', 'location', 'price_per_night', 'guests']);
    }

    public function test_owner_can_change_status_to_draft(): void
    {
        [$user, $token] = $this->authUser();

        $property = $this->publishedProperty($user, ['title' => 'Go Draft']);

        $this->withToken($token)
            ->putJson("/api/v1/properties/{$property->id}", $this->propertyPayload([
                'title' => 'Go Draft',
                'status' => 'draft',
            ]))
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_updating_title_regenerates_slug_uniquely(): void
    {
        [$user, $token] = $this->authUser();

        $this->publishedProperty($user, ['title' => 'Shared Name']);
        $property = $this->publishedProperty($user, ['title' => 'Something Else']);

        $this->withToken($token)
            ->putJson("/api/v1/properties/{$property->id}", $this->propertyPayload([
                'title' => 'Shared Name',
            ]))
            ->assertStatus(200)
            ->assertJsonPath('data.slug', 'shared-name-2');
    }
}
