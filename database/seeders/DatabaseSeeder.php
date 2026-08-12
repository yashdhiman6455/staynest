<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo property data: title, type, location, price, guests, bedrooms, bathrooms, image file.
     */
    private const PROPERTIES = [
        ['Sunset Skyline Apartment', 'Apartment', 'Mumbai', 8500, 4, 2, 2, 1],
        ['The Emerald Garden Villa', 'Villa', 'Goa', 12000, 6, 3, 3, 2],
        ['Cozy Heritage Cottage', 'Cottage', 'Manali', 3200, 3, 1, 1, 3],
        ['Modern Downtown House', 'House', 'Delhi', 4800, 5, 2, 2, 4],
        ['Poolside Palm Retreat', 'Villa', 'Goa', 15500, 8, 4, 4, 5],
        ['Chic Studio Loft', 'Apartment', 'Bangalore', 3900, 2, 1, 1, 6],
        ['Serene Pinewood Cabin', 'Cottage', 'Manali', 2800, 2, 1, 1, 7],
        ['Heritage Courtyard Haveli', 'Guest House', 'Jaipur', 2500, 4, 2, 2, 8],
        ['Ivory Sands Beachfront Villa', 'Villa', 'Goa', 18000, 10, 5, 5, 9],
        ['Royal Amber Palace Stay', 'Hotel', 'Jaipur', 6500, 3, 1, 1, 10],
        ['Lakeview Boutique Hotel', 'Hotel', 'Chandigarh', 5200, 4, 2, 2, 11],
        ['Canal-side Terrace House', 'House', 'Delhi', 4200, 6, 3, 2, 12],
        ['Himalayan Escapade Resort', 'Hotel', 'Manali', 9800, 5, 2, 2, 13],
        ['Urban Nest Apartment', 'Apartment', 'Chandigarh', 2500, 4, 2, 2, 14],
    ];

    private const IMAGE_DIR = 'database/seeders/assets/properties';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $yash = User::create([
            'name' => 'Yash Dhiman',
            'email' => 'yash@staynest.test',
            'phone' => '+91 98765 43210',
            'password' => 'password',
        ]);

        $priya = User::create([
            'name' => 'Priya Sharma',
            'email' => 'priya@staynest.test',
            'phone' => '+91 91234 56780',
            'password' => 'password',
        ]);

        $amit = User::create([
            'name' => 'Amit Verma',
            'email' => 'amit@staynest.test',
            'phone' => '+91 99887 76655',
            'password' => 'password',
        ]);

        $hosts = collect([$yash, $priya, $amit]);

        foreach (self::PROPERTIES as $index => [$title, $type, $location, $price, $guests, $bedrooms, $bathrooms, $imageNo]) {
            Property::create([
                'user_id' => $hosts[$index % 3]->id,
                'title' => $title,
                'slug' => Property::generateSlug($title),
                'description' => $this->descriptionFor($title, $location, $type),
                'property_type' => $type,
                'location' => $location,
                'city' => $location,
                'country' => 'India',
                'price_per_night' => $price,
                'guests' => $guests,
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'image' => $this->publishSeedImage($imageNo),
                'status' => Property::STATUS_PUBLISHED,
            ]);
        }

        // One draft listing to demonstrate the draft / ownership flow.
        Property::create([
            'user_id' => $yash->id,
            'title' => 'Private Lakeview Suite',
            'slug' => Property::generateSlug('Private Lakeview Suite'),
            'description' => 'A quiet, private suite overlooking the lake. Perfect for a weekend of calm.',
            'property_type' => 'Apartment',
            'location' => 'Chandigarh',
            'city' => 'Chandigarh',
            'country' => 'India',
            'price_per_night' => 1500,
            'guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'image' => $this->publishSeedImage(6),
            'status' => Property::STATUS_DRAFT,
        ]);
    }

    /**
     * Copy a seed image into public storage and return the stored path.
     */
    private function publishSeedImage(int $number): string
    {
        $source = base_path(self::IMAGE_DIR.'/'.$number.'.jpg');
        $path = 'properties/seed-'.$number.'.jpg';

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put(
                $path,
                file_get_contents($source)
            );
        }

        return $path;
    }

    /**
     * Build a realistic description for a seeded property.
     */
    private function descriptionFor(string $title, string $location, string $type): string
    {
        return "Welcome to {$title} — a charming {$type} in the heart of {$location}. "
            .'Step into a thoughtfully designed space with warm natural light, premium furnishings and every comfort you need for a memorable stay. '
            .'The property is minutes away from local cafes, markets and public transport, making it effortless to explore everything the city has to offer. '
            .'Whether you are travelling solo, with family or on a weekend getaway, you will feel right at home from the moment you arrive.';
    }
}
