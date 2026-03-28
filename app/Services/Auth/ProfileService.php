<?php

namespace App\Services\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Auth\ProfileRepository;
use App\Services\Profile\ProfileStatsService;

class ProfileService
{
    public function __construct(
        protected ProfileRepository $profileRepository,
        protected ProfileStatsService $profileStatsService
    ) {}

    /**
     * @param  array{name: string, gender: string, age: int}  $data
     * @return array{success: bool, message?: string, data?: array<string, mixed>, http_code?: int}
     */
    public function updateProfile(User $user, array $data): array
    {
        try {
            $this->profileRepository->updateProfile($user, [
                'name' => $data['name'],
                'gender' => $data['gender'],
                'age' => $data['age'],
            ]);

            $fresh = $user->fresh();
            $payload = (new UserResource($fresh))->resolve();
            $payload['stats'] = $this->profileStatsService->forUser($fresh);

            return [
                'success' => true,
                'message' => 'Profile updated',
                'data' => $payload,
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
