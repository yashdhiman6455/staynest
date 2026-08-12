<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    public const PROPERTY_TYPES = [
        'Apartment',
        'House',
        'Villa',
        'Cottage',
        'Hotel',
        'Guest House',
    ];

    public const STATUS_PUBLISHED = 'published';
    public const STATUS_DRAFT = 'draft';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'property_type',
        'location',
        'city',
        'country',
        'price_per_night',
        'guests',
        'bedrooms',
        'bathrooms',
        'image',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
        ];
    }

    /**
     * A property belongs to a single user (host).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return a full, browsable URL for the property image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return url('storage/'.$this->image);
    }

    /**
     * Generate a unique slug from the property title.
     */
    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);

        return static::uniqueSlug($slug);
    }

    /**
     * Ensure the given slug is unique in the properties table.
     */
    protected static function uniqueSlug(string $slug): string
    {
        $base = $slug;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Scope a query to only include published properties.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Apply search / filter parameters to the query.
     */
    public function scopeFilter($query, array $filters): void
    {
        $query->when(
            $filters['search'] ?? null,
            fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
        );

        $query->when(
            $filters['location'] ?? null,
            fn ($q, $location) => $q->where(function ($q) use ($location) {
                $q->where('location', 'like', "%{$location}%")
                    ->orWhere('city', 'like', "%{$location}%")
                    ->orWhere('country', 'like', "%{$location}%");
            })
        );

        $query->when(
            $filters['type'] ?? null,
            fn ($q, $type) => $q->where('property_type', $type)
        );

        $query->when(
            $filters['min_price'] ?? null,
            fn ($q, $min) => $q->where('price_per_night', '>=', $min)
        );

        $query->when(
            $filters['max_price'] ?? null,
            fn ($q, $max) => $q->where('price_per_night', '<=', $max)
        );

        $query->when(
            $filters['guests'] ?? null,
            fn ($q, $guests) => $q->where('guests', '>=', $guests)
        );
    }
}
