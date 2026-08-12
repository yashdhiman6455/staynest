<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'property_type' => $this->property_type,
            'location' => $this->location,
            'city' => $this->city,
            'country' => $this->country,
            'price_per_night' => $this->price_per_night,
            'guests' => $this->guests,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
