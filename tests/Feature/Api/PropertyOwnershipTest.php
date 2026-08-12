<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use Illuminate\Support\Facades\Storage;

class PropertyOwnershipTest extends ApiTestCase
{
    public function test_user_cannot_update_another_users_property(): void
    {
        [$owner, $ownerToken] = $this->authUser();
        [$intruder, $intruderToken] = $this->authUser();

        $property = $this->publishedProperty($owner, ['title' => 'Owner Villa']);

        $this->withToken($intruderToken)
            ->putJson("/api/v1/properties/{$property->id}", $this->propertyPayload(['title' => 'Hacked']))
            ->assertStatus(403)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('properties', ['id' => $property->id, 'title' => 'Owner Villa']);
    }

    public function test_user_cannot_delete_another_users_property(): void
    {
        [$owner, $ownerToken] = $this->authUser();
        [$intruder, $intruderToken] = $this->authUser();

        $property = $this->publishedProperty($owner, ['title' => 'Owner Villa']);

        $this->withToken($intruderToken)
            ->deleteJson("/api/v1/properties/{$property->id}")
            ->assertStatus(403)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_unauthenticated_user_cannot_update_a_property(): void
    {
        [$owner, $ownerToken] = $this->authUser();

        $property = $this->publishedProperty($owner, ['title' => 'Owner Villa']);

        $this->putJson("/api/v1/properties/{$property->id}", $this->propertyPayload())
            ->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_a_property(): void
    {
        [$owner, $ownerToken] = $this->authUser();

        $property = $this->publishedProperty($owner, ['title' => 'Owner Villa']);

        $this->deleteJson("/api/v1/properties/{$property->id}")
            ->assertStatus(401);
    }

    public function test_owner_can_delete_and_cleanup_image(): void
    {
        [$owner, $ownerToken] = $this->authUser();

        Storage::fake('public');
        Storage::disk('public')->put('properties/to-delete.jpg', 'content');

        $property = $this->publishedProperty($owner, ['title' => 'Owner Villa']);
        $property->forceFill(['image' => 'properties/to-delete.jpg'])->save();

        $this->withToken($ownerToken)
            ->deleteJson("/api/v1/properties/{$property->id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
        Storage::disk('public')->assertMissing('properties/to-delete.jpg');
    }

    public function test_policy_rejects_guests_from_my_properties(): void
    {
        $this->getJson('/api/v1/my-properties')
            ->assertStatus(401);
    }
}
