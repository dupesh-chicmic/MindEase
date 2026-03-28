<?php

namespace App\Repositories\Auth;

use App\Models\User;

class AuthRepository
{
    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function findById(int|string $id): ?User
    {
        return User::query()->find($id);
    }

    public function updateSessionToken(User $user, string $token): bool
    {
        return $user->forceFill(['current_session_token' => $token])->save();
    }

    public function clearSessionToken(User $user): bool
    {
        return $user->forceFill(['current_session_token' => null])->save();
    }
}
