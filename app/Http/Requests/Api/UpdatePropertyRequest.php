<?php

namespace App\Http\Requests\Api;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'property_type' => ['required', Rule::in(Property::PROPERTY_TYPES)],
            'location' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'price_per_night' => ['required', 'numeric', 'min:1', 'max:9999999'],
            'guests' => ['required', 'integer', 'min:1', 'max:100'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:100'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in([Property::STATUS_PUBLISHED, Property::STATUS_DRAFT])],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
}
