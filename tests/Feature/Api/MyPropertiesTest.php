<?php

namespace Tests\Feature\Api;

use App\Models\Property;

class MyPropertiesTest extends ApiTestCase
{
    public function test_user_sees_only_their_own_properties(): void
    {
        [$user, $token] = $this->authUser();
        [$other, $otherToken] = $this->authUser();

        $mine = $this->publishedProperty($user, ['title' => 'My Listing']);
        $theirs = $this->publishedProperty($other, ['title' => 'Their Listing']);

        $response = $this->withToken($token)->getJson('/api/v1/my-properties');

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id)
            ->assertJsonPath('data.0.title', 'My Listing');
    }

    public function test_my_properties_include_drafts(): void
    {
        [$user, $token] = $this->authUser();

        $published = $this->publishedProperty($user, ['title' => 'Published One']);
        $draft = $this->draftProperty($user, ['title' => 'Draft One']);

        $this->withToken($token)
            ->getJson('/api/v1/my-properties')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.1.status', 'draft');
    }

    public function test_my_properties_returns_empty_array_for_new_user(): void
    {
        [$user, $token] = $this->authUser();

        $this->withToken($token)
            ->getJson('/api/v1/my-properties')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_owner_can_delete_their_property(): void
    {
        [$user, $token] = $this->authUser();

        $property = $this->publishedProperty($user, ['title' => 'Going Away']);

        $this->withToken($token)
            ->deleteJson("/api/v1/properties/{$property->id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    public function test_delete_returns_404_for_unknown_property(): void
    {
        [$user, $token] = $this->authUser();

        $this->withToken($token)
            ->deleteJson('/api/v1/properties/99999')
            ->assertStatus(404);
    }
}
