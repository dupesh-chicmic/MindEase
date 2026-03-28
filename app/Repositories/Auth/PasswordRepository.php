<?php

namespace App\Repositories\Auth;

use App\Models\PasswordOtp;
use Carbon\CarbonInterface;

class PasswordRepository
{
    public function deleteAllForEmail(string $email): int
    {
        return PasswordOtp::query()->where('email', $email)->delete();
    }

    public function create(string $email, string $otp, CarbonInterface $expiresAt): PasswordOtp
    {
        return PasswordOtp::query()->create([
            'email' => $email,
            'otp' => $otp,
            'is_verified' => false,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findUnverifiedValid(string $email, string $otp): ?PasswordOtp
    {
        return PasswordOtp::query()
            ->where('email', $email)
            ->where('otp', $otp)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    public function markVerified(PasswordOtp $otp): bool
    {
        return $otp->forceFill(['is_verified' => true])->save();
    }

    public function findVerifiedForEmail(string $email): ?PasswordOtp
    {
        return PasswordOtp::query()
            ->where('email', $email)
            ->where('is_verified', true)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    public function delete(PasswordOtp $otp): bool
    {
        return (bool) $otp->delete();
    }
}
