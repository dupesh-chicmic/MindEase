<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private string $version;

    public function __construct()
    {
        $this->apiKey  = (string) config('ai.providers.claude.api_key', '');
        $this->apiUrl  = (string) config('ai.providers.claude.api_url');
        $this->model   = (string) config('ai.providers.claude.model');
        $this->version = (string) config('ai.providers.claude.version');
    }

    /**
     * {@inheritdoc}
     */
    public function generateResponse(array $messages, array $options = []): array
    {
        if (trim($this->apiKey) === '') {
            Log::error('Claude API key is not configured');

            return [
                'success'   => false,
                'message'   => 'AI service is not configured. Please contact support.',
                'http_code' => 503,
            ];
        }

        try {
            $payload = [
                'model'      => $this->model,
                'max_tokens' => $options['max_tokens'] ?? 1024,
                'messages'   => $messages,
            ];

            if (! empty($options['system'])) {
                $payload['system'] = $options['system'];
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => $this->version,
                    'content-type'      => 'application/json',
                ])
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $text = $response->json('content.0.text');

                if (is_string($text) && trim($text) !== '') {
                    return [
                        'success'   => true,
                        'text'      => $text,
                        'http_code' => 200,
                    ];
                }

                Log::error('Claude returned an empty response', ['body' => $response->json()]);

                return [
                    'success'   => false,
                    'message'   => 'AI service returned an empty response. Please try again.',
                    'http_code' => 502,
                ];
            }

            $status          = $response->status();
            $providerMessage = $response->json('error.message');

            report(new \RuntimeException('Claude API error: ' . $status . ' ' . $response->body()));

            if ($status === 400 && is_string($providerMessage) && str_contains(strtolower($providerMessage), 'credit balance is too low')) {
                return [
                    'success'   => false,
                    'message'   => 'AI service is unavailable due to insufficient credits.',
                    'http_code' => 503,
                ];
            }

            return [
                'success'   => false,
                'message'   => 'AI service is temporarily unavailable. Please try again later.',
                'http_code' => 502,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'AI service is temporarily unavailable. Please try again later.',
                'http_code' => 502,
            ];
        }
    }
}
