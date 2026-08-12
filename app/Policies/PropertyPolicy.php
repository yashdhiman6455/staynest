<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Any authenticated user may create a property.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Users may view their own properties.
     */
    public function view(User $user, Property $property): bool
    {
        return $user->id === $property->user_id;
    }

    /**
     * Only the property owner may update it.
     */
    public function update(User $user, Property $property): bool
    {
        return $user->id === $property->user_id;
    }

    /**
     * Only the property owner may delete it.
     */
    public function delete(User $user, Property $property): bool
    {
        return $user->id === $property->user_id;
    }
}
