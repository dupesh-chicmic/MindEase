<?php

namespace App\Services\Auth;

use App\Mail\SendOtpMail;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Auth\PasswordRepository;
use Illuminate\Support\Facades\Mail;

class PasswordService
{
    public function __construct(
        protected PasswordRepository $passwordRepository,
        protected AuthRepository $authRepository
    ) {}

    /**
     * @return array{success: bool, message: string, http_code: int}
     */
    public function sendForgotPasswordOtp(string $email): array
    {
        try {
            $this->passwordRepository->deleteAllForEmail($email);
            $otp = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
            $this->passwordRepository->create($email, $otp, now()->addMinutes(10));
            Mail::to($email)->send(new SendOtpMail($otp));

            return [
                'success' => true,
                'message' => 'OTP sent to email',
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Unable to send OTP',
                'http_code' => 500,
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, http_code: int}
     */
    public function verifyOtp(string $email, string $otp): array
    {
        try {
            $record = $this->passwordRepository->findUnverifiedValid($email, $otp);
            if (! $record) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                    'http_code' => 422,
                ];
            }

            $this->passwordRepository->markVerified($record);

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'OTP verification failed',
                'http_code' => 500,
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, http_code: int}
     */
    public function updatePassword(string $email, string $password): array
    {
        try {
            $otpRecord = $this->passwordRepository->findVerifiedForEmail($email);
            if (! $otpRecord) {
                return [
                    'success' => false,
                    'message' => 'OTP verification required',
                    'http_code' => 422,
                ];
            }

            $user = $this->authRepository->findByEmail($email);
            if (! $user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'http_code' => 404,
                ];
            }

            $user->password = $password;
            $user->save();
            $this->passwordRepository->deleteAllForEmail($email);

            return [
                'success' => true,
                'message' => 'Password updated successfully',
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Password update failed',
                'http_code' => 500,
            ];
        }
    }
}
