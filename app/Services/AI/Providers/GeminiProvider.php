<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('ai.providers.gemini.api_key', '');
        $this->apiUrl = (string) config('ai.providers.gemini.api_url');
        $this->model  = (string) config('ai.providers.gemini.model');
    }

    /**
     * {@inheritdoc}
     *
     * Gemini format mapping:
     *   role "assistant" → "model"   (Gemini's term for AI turns)
     *   system prompt    → prepended as first "user" turn, then empty "model" turn
     */
    public function generateResponse(array $messages, array $options = []): array
    {
        if (trim($this->apiKey) === '') {
            Log::error('Gemini API key is not configured');

            return [
                'success'   => false,
                'message'   => 'AI service is not configured. Please contact support.',
                'http_code' => 503,
            ];
        }

        try {
            $contents = $this->buildContents($messages, $options['system'] ?? null);

            $maxTokens = $options['max_tokens'] ?? 1024;

            $url = "{$this->apiUrl}/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents'         => $contents,
                    'generationConfig' => [
                        'maxOutputTokens' => $maxTokens,
                        'temperature'     => 0.7,
                    ],
                ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');

                if (is_string($text) && trim($text) !== '') {
                    return [
                        'success'   => true,
                        'text'      => $text,
                        'http_code' => 200,
                    ];
                }

                // Check if Gemini blocked the response
                $finishReason = $response->json('candidates.0.finishReason');
                if ($finishReason === 'SAFETY') {
                    Log::warning('Gemini blocked response due to safety filters');

                    return [
                        'success'   => false,
                        'message'   => 'AI service could not generate a response for this input.',
                        'http_code' => 422,
                    ];
                }

                Log::error('Gemini returned an empty response', ['body' => $response->json()]);

                return [
                    'success'   => false,
                    'message'   => 'AI service returned an empty response. Please try again.',
                    'http_code' => 502,
                ];
            }

            $status      = $response->status();
            $errorStatus = $response->json('error.status');

            report(new \RuntimeException('Gemini API error: ' . $status . ' ' . $response->body()));

            if ($status === 429 || $errorStatus === 'RESOURCE_EXHAUSTED') {
                return [
                    'success'   => false,
                    'message'   => 'AI service is temporarily unavailable due to quota limits. Please try again later.',
                    'http_code' => 503,
                ];
            }

            if ($status === 404 || $errorStatus === 'NOT_FOUND') {
                return [
                    'success'   => false,
                    'message'   => 'AI service model is misconfigured. Please contact support.',
                    'http_code' => 503,
                ];
            }

            if ($status === 400) {
                return [
                    'success'   => false,
                    'message'   => 'AI service could not process this request.',
                    'http_code' => 422,
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

    /**
     * Convert canonical message array + optional system prompt into Gemini's "contents" format.
     *
     * Gemini requires:
     * - roles: "user" or "model"
     * - the first turn must be "user"
     * - turns must alternate; consecutive same-role turns are merged
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<int, array{role: string, parts: array<int, array{text: string}>}>
     */
    private function buildContents(array $messages, ?string $systemPrompt): array
    {
        $contents = [];

        // Inject system prompt as an initial user + model exchange
        if (! empty($systemPrompt)) {
            $contents[] = [
                'role'  => 'user',
                'parts' => [['text' => 'System context: ' . $systemPrompt]],
            ];
            $contents[] = [
                'role'  => 'model',
                'parts' => [['text' => 'Understood. I will follow those guidelines.']],
            ];
        }

        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';
            $text = (string) $message['content'];

            // Merge consecutive same-role turns (Gemini rejects them)
            $last = end($contents);
            if ($last !== false && $last['role'] === $role) {
                $contents[array_key_last($contents)]['parts'][] = ['text' => $text];
            } else {
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }

        return $contents;
    }
}
