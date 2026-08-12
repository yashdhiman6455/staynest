<?php

namespace Tests\Feature\Api;

use App\Models\Property;

class PropertyListingTest extends ApiTestCase
{
    public function test_public_listing_returns_only_published_properties(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, ['title' => 'Published Villa']);
        $this->draftProperty($owner, ['title' => 'Hidden Draft']);

        $response = $this->getJson('/api/v1/properties');

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Published Villa');
    }

    public function test_listing_is_paginated(): void
    {
        [$owner, $token] = $this->authUser();

        Property::factory()->published()->count(5)->create(['user_id' => $owner->id]);

        $this->getJson('/api/v1/properties?per_page=2')
            ->assertStatus(200)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonPath('meta.total', 5);
    }

    public function test_listing_supports_pagination_page_param(): void
    {
        [$owner, $token] = $this->authUser();

        Property::factory()->published()->count(5)->create(['user_id' => $owner->id]);

        $this->getJson('/api/v1/properties?per_page=2&page=3')
            ->assertStatus(200)
            ->assertJsonPath('meta.current_page', 3);
    }

    public function test_filter_by_location_matches_city_or_location(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, ['title' => 'Chandigarh Flat', 'location' => 'Chandigarh']);
        $this->publishedProperty($owner, ['title' => 'Goa Villa', 'location' => 'Goa']);

        $this->getJson('/api/v1/properties?location=chandigarh')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Chandigarh Flat');
    }

    public function test_filter_by_property_type(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, ['title' => 'Villa One', 'property_type' => 'Villa']);
        $this->publishedProperty($owner, ['title' => 'Villa Two', 'property_type' => 'Villa']);
        $this->publishedProperty($owner, ['title' => 'Apartment One', 'property_type' => 'Apartment']);

        $this->getJson('/api/v1/properties?type=Villa')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_filter_by_price_range(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, ['title' => 'Budget Stay', 'price_per_night' => 1500]);
        $this->publishedProperty($owner, ['title' => 'Mid Stay', 'price_per_night' => 4500]);
        $this->publishedProperty($owner, ['title' => 'Luxury Stay', 'price_per_night' => 12000]);

        $this->getJson('/api/v1/properties?min_price=2000&max_price=5000')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Mid Stay');
    }

    public function test_filter_by_maximum_price_only(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, ['title' => 'Budget Stay', 'price_per_night' => 1500]);
        $this->publishedProperty($owner, ['title' => 'Luxury Stay', 'price_per_night' => 12000]);

        $this->getJson('/api/v1/properties?max_price=5000')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Budget Stay');
    }

    public function test_filter_by_guests(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, ['title' => 'Small Stay', 'guests' => 2]);
        $this->publishedProperty($owner, ['title' => 'Large Stay', 'guests' => 8]);

        $this->getJson('/api/v1/properties?guests=4')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Large Stay');
    }

    public function test_combined_filters_return_matching_results(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, [
            'title' => 'Urban Nest Apartment',
            'property_type' => 'Apartment',
            'location' => 'Chandigarh',
            'price_per_night' => 2500,
            'guests' => 4,
        ]);
        $this->publishedProperty($owner, [
            'title' => 'Wrong Price',
            'property_type' => 'Apartment',
            'location' => 'Chandigarh',
            'price_per_night' => 8000,
            'guests' => 4,
        ]);
        $this->publishedProperty($owner, [
            'title' => 'Wrong Location',
            'property_type' => 'Apartment',
            'location' => 'Goa',
            'price_per_night' => 2500,
            'guests' => 4,
        ]);

        $this->getJson('/api/v1/properties?location=Chandigarh&type=Apartment&max_price=5000')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Urban Nest Apartment');
    }

    public function test_search_matches_title_and_description(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, ['title' => 'Lakeview Cottage', 'description' => 'Peaceful escape']);
        $this->publishedProperty($owner, ['title' => 'City Loft', 'description' => 'A lakeview apartment downtown']);

        $this->getJson('/api/v1/properties?search=lakeview')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_empty_result_when_no_property_matches(): void
    {
        [$owner, $token] = $this->authUser();

        $this->publishedProperty($owner, ['title' => 'Goa Villa']);

        $this->getJson('/api/v1/properties?location=Chennai')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }
}
