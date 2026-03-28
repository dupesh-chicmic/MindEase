<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\Auth\PasswordService;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected PasswordService $passwordService
    ) {}

    public function sendOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->passwordService->sendForgotPasswordOtp($request->validated('email'));

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['http_code']);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->passwordService->verifyOtp($data['email'], $data['otp']);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['http_code']);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->passwordService->updatePassword(
            $data['email'],
            $data['password']
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['http_code']);
    }
}
