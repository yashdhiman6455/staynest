<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePropertyRequest;
use App\Http\Requests\Api\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    /**
     * List published properties with optional search / filters.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 12), 1), 24);
        $page = max((int) $request->integer('page', 1), 1);

        $properties = Property::query()
            ->published()
            ->with('user:id,name,email,phone,avatar')
            ->filter($request->all())
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Properties retrieved successfully.',
            'data' => PropertyResource::collection($properties),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ],
        ]);
    }

    /**
     * Return a single property by slug.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $property = Property::with('user:id,name,email,phone,avatar')->where('slug', $slug)->first();

        if (! $property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.',
            ], 404);
        }

        $isOwner = $request->user('sanctum')?->id === $property->user_id;

        if ($property->status !== Property::STATUS_PUBLISHED && ! $isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property retrieved successfully.',
            'data' => new PropertyResource($property),
        ]);
    }

    /**
     * Create a new property owned by the authenticated user.
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $this->authorize('create', Property::class);

        $imagePath = $request->file('image')?->store('properties', 'public');

        $property = Property::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'slug' => Property::generateSlug($request->title),
            'description' => $request->description,
            'property_type' => $request->property_type,
            'location' => $request->location,
            'city' => $request->city,
            'country' => $request->country,
            'price_per_night' => $request->price_per_night,
            'guests' => $request->guests,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'image' => $imagePath,
            'status' => $request->status ?? Property::STATUS_PUBLISHED,
        ]);

        $property->load('user:id,name,avatar');

        return response()->json([
            'success' => true,
            'message' => 'Property published successfully.',
            'data' => new PropertyResource($property),
        ], 201);
    }

    /**
     * Update an existing property owned by the authenticated user.
     */
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        $this->authorize('update', $property);

        $imagePath = $property->image;

        if ($request->hasFile('image')) {
            if ($property->image && Storage::disk('public')->exists($property->image)) {
                Storage::disk('public')->delete($property->image);
            }

            $imagePath = $request->file('image')->store('properties', 'public');
        }

        $property->update([
            'title' => $request->title,
            'slug' => $property->title !== $request->title
                ? Property::generateSlug($request->title)
                : $property->slug,
            'description' => $request->description,
            'property_type' => $request->property_type,
            'location' => $request->location,
            'city' => $request->city,
            'country' => $request->country,
            'price_per_night' => $request->price_per_night,
            'guests' => $request->guests,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'image' => $imagePath,
            'status' => $request->status ?? $property->status,
        ]);

        $property->load('user:id,name,avatar');

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully.',
            'data' => new PropertyResource($property),
        ]);
    }

    /**
     * Delete a property owned by the authenticated user.
     */
    public function destroy(Request $request, Property $property): JsonResponse
    {
        $this->authorize('delete', $property);

        if ($property->image && Storage::disk('public')->exists($property->image)) {
            Storage::disk('public')->delete($property->image);
        }

        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully.',
        ]);
    }

    /**
     * Return all properties owned by the authenticated user.
     */
    public function myProperties(Request $request): JsonResponse
    {
        $properties = Property::with('user:id,name,email,phone,avatar')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Your properties retrieved successfully.',
            'data' => PropertyResource::collection($properties),
        ]);
    }
}

