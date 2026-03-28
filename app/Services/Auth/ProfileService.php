<?php

namespace App\Services\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Auth\ProfileRepository;

class ProfileService
{
    public function __construct(
        protected ProfileRepository $profileRepository
    ) {}

    /**
     * @param  array{name: string, gender: string, age: int}  $data
     * @return array{success: bool, message?: string, data?: UserResource, http_code?: int}
     */
    public function updateProfile(User $user, array $data): array
    {
        try {
            $this->profileRepository->updateProfile($user, [
                'name' => $data['name'],
                'gender' => $data['gender'],
                'age' => $data['age'],
            ]);

            return [
                'success' => true,
                'message' => 'Profile updated',
                'data' => new UserResource($user->fresh()),
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Profile update failed',
                'http_code' => 500,
            ];
        }
    }
}
