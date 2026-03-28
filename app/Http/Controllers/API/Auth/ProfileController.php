<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Services\Auth\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $result = $this->profileService->updateProfile(
            Auth::guard('api')->user(),
            $request->validated()
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data']->resolve(),
        ], $result['http_code'] ?? 200);
    }
}
