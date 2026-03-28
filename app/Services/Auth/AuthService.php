<?php

namespace App\Services\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use App\Services\Profile\ProfileStatsService;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected AuthRepository $authRepository,
        protected ProfileStatsService $profileStatsService
    ) {}

    /**
     * @return array{success: bool, message?: string, token?: string, user?: UserResource, http_code?: int}
     */
    public function register(array $data): array
    {
        try {
            $user = $this->authRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'gender' => $data['gender'],
                'age' => $data['age'],
            ]);

            $token = auth('api')->login($user);
            if (! is_string($token)) {
                return [
                    'success' => false,
                    'message' => 'Registration failed',
                    'http_code' => 500,
                ];
            }
            $this->authRepository->updateSessionToken($user, $token);

            return [
                'success' => true,
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => new UserResource($user->fresh()),
                'http_code' => 201,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Registration failed',
                'http_code' => 500,
            ];
        }
    }

    /**
     * @return array{success: bool, message?: string, token?: string, user?: UserResource, http_code?: int}
     */
    public function login(string $email, string $password): array
    {
        try {
            $user = $this->authRepository->findByEmail($email);

            if (! $user || ! Hash::check($password, $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'http_code' => 401,
                ];
            }

            $token = auth('api')->login($user);
            if (! is_string($token)) {
                return [
                    'success' => false,
                    'message' => 'Login failed',
                    'http_code' => 500,
                ];
            }
            $this->authRepository->updateSessionToken($user, $token);

            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => new UserResource($user->fresh()),
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Login failed',
                'http_code' => 500,
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, http_code: int}
     */
    public function logout(User $user): array
    {
        try {
            $this->authRepository->clearSessionToken($user);

            return [
                'success' => true,
                'message' => 'Logged out successfully',
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Logout failed',
                'http_code' => 500,
            ];
        }
    }

    /**
     * @return array{success: bool, message?: string, data?: array<string, mixed>, http_code?: int}
     */
    public function profile(User $user): array
    {
        try {
            $fresh = $user->fresh() ?? $user;
            $data = (new UserResource($fresh))->resolve();
            $data['stats'] = $this->profileStatsService->forUser($fresh);

            return [
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $data,
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Unable to retrieve profile',
                'http_code' => 500,
            ];
        }
    }
}
