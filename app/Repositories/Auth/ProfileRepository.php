<?php

namespace App\Repositories\Auth;

use App\Models\User;

class ProfileRepository
{
    /**
     * @param  array{name: string, gender: string, age: int}  $attributes
     */
    public function updateProfile(User $user, array $attributes): bool
    {
        return $user->forceFill($attributes)->save();
    }
}
