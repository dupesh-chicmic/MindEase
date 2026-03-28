<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'token' => $result['token'],
            'user' => $result['user']->resolve(),
        ], $result['http_code'] ?? 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['http_code'] ?? 401);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'token' => $result['token'],
            'user' => $result['user']->resolve(),
        ], $result['http_code'] ?? 200);
    }

    public function logout(): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $result = $this->authService->logout($user);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => new \stdClass,
        ], $result['http_code'] ?? 200);
    }

    public function me(): JsonResponse
    {
        $result = $this->authService->profile(Auth::guard('api')->user());

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
