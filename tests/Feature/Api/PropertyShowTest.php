<?php

namespace Tests\Feature\Api;

class PropertyShowTest extends ApiTestCase
{
    public function test_public_can_view_a_published_property(): void
    {
        [$owner, $token] = $this->authUser(['name' => 'Yash Dhiman']);

        $property = $this->publishedProperty($owner, [
            'title' => 'Sunset Skyline Apartment',
            'property_type' => 'Apartment',
            'price_per_night' => 8500,
        ]);

        $this->getJson("/api/v1/properties/{$property->slug}")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', $property->slug)
            ->assertJsonPath('data.title', 'Sunset Skyline Apartment')
            ->assertJsonPath('data.property_type', 'Apartment')
            ->assertJsonPath('data.user.name', 'Yash Dhiman');
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->getJson('/api/v1/properties/this-does-not-exist')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_draft_is_hidden_from_public_visitors(): void
    {
        [$owner, $token] = $this->authUser();

        $property = $this->draftProperty($owner, ['title' => 'Private Lakeview Suite']);

        $this->getJson("/api/v1/properties/{$property->slug}")
            ->assertStatus(404);
    }

    public function test_owner_can_view_their_own_draft(): void
    {
        [$owner, $token] = $this->authUser();

        $property = $this->draftProperty($owner, ['title' => 'Private Lakeview Suite']);

        $this->withToken($token)
            ->getJson("/api/v1/properties/{$property->slug}")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_other_user_cannot_view_someone_elses_draft(): void
    {
        [$owner, $ownerToken] = $this->authUser();
        [$visitor, $visitorToken] = $this->authUser();

        $property = $this->draftProperty($owner, ['title' => 'Secret Draft']);

        $this->withToken($visitorToken)
            ->getJson("/api/v1/properties/{$property->slug}")
            ->assertStatus(404);
    }
}
