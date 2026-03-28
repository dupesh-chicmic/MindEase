<?php

namespace App\Services\Fcm;

use App\Models\FcmToken;
use App\Models\Mood;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FcmService
{
    private const GOOGLE_TOKEN_URL   = 'https://oauth2.googleapis.com/token';
    private const FCM_SCOPE          = 'https://www.googleapis.com/auth/firebase.messaging';
    private const TOKEN_CACHE_KEY    = 'fcm_oauth_access_token';
    private const TOKEN_BUFFER_SECS  = 120; // refresh 2 min before expiry

    // -----------------------------------------------------------------------
    // Token Management
    // -----------------------------------------------------------------------

    public function saveToken(int $userId, string $token): array
    {
        if (! $this->hasFcmTokensTable()) {
            Log::warning('FCM: saveToken skipped because fcm_tokens table is missing');

            return [
                'success'   => false,
                'message'   => 'FCM storage is not ready',
                'http_code' => 503,
            ];
        }

        try {
            FcmToken::firstOrCreate(
                ['token_hash' => $this->hashToken($token)],
                [
                    'user_id' => $userId,
                    'token' => $token,
                ]
            );

            return [
                'success'   => true,
                'message'   => 'FCM token saved successfully',
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'Failed to save FCM token',
                'http_code' => 500,
            ];
        }
    }

    public function deleteToken(int $userId, string $token): array
    {
        if (! $this->hasFcmTokensTable()) {
            Log::warning('FCM: deleteToken skipped because fcm_tokens table is missing');

            return [
                'success'   => false,
                'message'   => 'FCM storage is not ready',
                'http_code' => 503,
            ];
        }

        try {
            $deleted = FcmToken::where('user_id', $userId)
                ->where('token_hash', $this->hashToken($token))
                ->delete();

            if (! $deleted) {
                return [
                    'success'   => false,
                    'message'   => 'Token not found',
                    'http_code' => 404,
                ];
            }

            return [
                'success'   => true,
                'message'   => 'FCM token deleted successfully',
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'Failed to delete FCM token',
                'http_code' => 500,
            ];
        }
    }

    // -----------------------------------------------------------------------
    // Send to a single device token  (HTTP v1)
    // -----------------------------------------------------------------------

    /**
     * @param  array<string, string>  $data  Extra key/value data payload
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            if (! $accessToken) {
                return false;
            }

            $projectId = config('services.firebase.project_id');
            $url       = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // FCM HTTP v1 requires all data values to be strings
            $stringData = array_map('strval', $data);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ])
                ->post($url, [
                    'message' => [
                        'token'        => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'data'         => $stringData,
                        'android'      => [
                            'notification' => ['sound' => 'default'],
                        ],
                        'apns'         => [
                            'payload' => ['aps' => ['sound' => 'default']],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            $this->handleFcmError($token, $response->status(), $response->json());

            return false;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    // -----------------------------------------------------------------------
    // Send to all tokens of a user
    // -----------------------------------------------------------------------

    /**
     * @param  array<string, string>  $data
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        if (! $this->hasFcmTokensTable()) {
            Log::warning('FCM: sendToUser skipped because fcm_tokens table is missing');

            return false;
        }

        $tokens = FcmToken::where('user_id', $userId)->pluck('token');

        if ($tokens->isEmpty()) {
            return false;
        }

        $sent = false;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $sent = true;
            }
        }

        return $sent;
    }

    // -----------------------------------------------------------------------
    // OAuth 2.0 access token  (cached until near-expiry)
    // -----------------------------------------------------------------------

    /**
     * Return a valid FCM OAuth 2.0 access token, served from cache when possible.
     */
    public function getAccessToken(): ?string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        return $this->fetchAndCacheAccessToken();
    }

    /**
     * Build a RS256-signed JWT, exchange it for a Google OAuth access token,
     * then store it in the Laravel cache.
     *
     * No external packages needed — uses PHP's built-in OpenSSL extension.
     */
    private function fetchAndCacheAccessToken(): ?string
    {
        try {
            $credentialsPath = config('services.firebase.credentials_path');

            if (! $credentialsPath || ! file_exists($credentialsPath)) {
                Log::error('FCM: service account file not found', ['path' => $credentialsPath]);

                return null;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            if (! $credentials || ($credentials['type'] ?? '') !== 'service_account') {
                Log::error('FCM: invalid service account JSON');

                return null;
            }

            $now = time();
            $exp = $now + 3600;

            // --- Build JWT ---
            $header  = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = $this->base64UrlEncode(json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => self::FCM_SCOPE,
                'aud'   => self::GOOGLE_TOKEN_URL,
                'iat'   => $now,
                'exp'   => $exp,
            ]));

            $signingInput = $header . '.' . $payload;

            $privateKey = openssl_pkey_get_private($credentials['private_key']);
            if (! $privateKey) {
                Log::error('FCM: failed to load private key from service account');

                return null;
            }

            openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            $jwt = $signingInput . '.' . $this->base64UrlEncode($signature);

            // --- Exchange JWT for access token ---
            $response = Http::timeout(10)
                ->asForm()
                ->post(self::GOOGLE_TOKEN_URL, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);

            if (! $response->successful()) {
                Log::error('FCM: token exchange failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return null;
            }

            $body        = $response->json();
            $accessToken = $body['access_token'] ?? null;
            $expiresIn   = (int) ($body['expires_in'] ?? 3600);

            if (! $accessToken) {
                Log::error('FCM: access_token missing in response', $body);

                return null;
            }

            // Cache slightly before actual expiry to avoid edge-case races
            $cacheTtl = max(60, $expiresIn - self::TOKEN_BUFFER_SECS);
            Cache::put(self::TOKEN_CACHE_KEY, $accessToken, $cacheTtl);

            return $accessToken;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    // -----------------------------------------------------------------------
    // Smart notification: last 3 moods all negative
    // -----------------------------------------------------------------------

    public function sendSmartNotificationIfNeeded(int $userId): void
    {
        $lastMoods = Mood::where('user_id', $userId)
            ->orderBy('recorded_at', 'desc')
            ->limit(3)
            ->pluck('mood')
            ->map(fn (string $m) => strtolower($m))
            ->toArray();

        if (count($lastMoods) < 3) {
            return;
        }

        $lowMoods = ['sad', 'stressed', 'anxious', 'depressed', 'frustrated', 'angry', 'lonely'];
        $allLow   = collect($lastMoods)->every(fn (string $m) => in_array($m, $lowMoods, true));

        if ($allLow) {
            $this->sendToUser(
                $userId,
                'MindEase',
                "We noticed you've been feeling low. Try a short walk or breathing exercise \u{1F499}",
                ['type' => 'smart_wellness', 'screen' => 'wellness']
            );
        }
    }

    // -----------------------------------------------------------------------
    // Daily mood reminder — called by the scheduler
    // -----------------------------------------------------------------------

    public function sendDailyMoodReminders(): void
    {
        User::query()->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $this->sendToUser(
                    $user->id,
                    'MindEase',
                    "How are you feeling today? Take a moment to log your mood \u{1F60A}",
                    ['type' => 'mood_reminder', 'screen' => 'mood']
                );
            }
        });
    }

    // -----------------------------------------------------------------------
    // Insights-ready notification
    // -----------------------------------------------------------------------

    public function sendInsightsReadyNotification(int $userId): void
    {
        $this->sendToUser(
            $userId,
            'MindEase',
            "Your latest mood insights are ready \u{1F4CA}",
            ['type' => 'insights_ready', 'screen' => 'insights']
        );
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param  array<string, mixed>|null  $responseBody
     */
    private function handleFcmError(string $token, int $status, ?array $responseBody): void
    {
        $errorCode = $responseBody['error']['details'][0]['errorCode']
            ?? $responseBody['error']['status']
            ?? 'UNKNOWN';

        Log::warning('FCM: send failed', [
            'token'  => substr($token, 0, 20) . '...',
            'status' => $status,
            'error'  => $errorCode,
        ]);

        // Remove tokens that FCM says are no longer valid
        if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
            FcmToken::where('token_hash', $this->hashToken($token))->delete();
            Log::info('FCM: removed stale token', ['token' => substr($token, 0, 20) . '...']);
        }
    }

    private function hasFcmTokensTable(): bool
    {
        static $hasTable;

        return $hasTable ??= Schema::hasTable('fcm_tokens');
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
