<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AISuggestionService
{
    public const FALLBACK = [
        'activity' => 'Thoda walk le le fresh air mein 🚶‍♂️',
        'quote' => 'Yeh phase temporary hai, tu strong hai 💪',
        'extra' => 'Smile kar thoda 🙂',
    ];

    public function generateForMood(string $mood): array
    {
        $apiKey = config('services.gemini.key');

        if (! is_string($apiKey) || $apiKey === '') {
            Log::warning('Gemini API key missing; using fallback mood suggestion.');

            return self::FALLBACK;
        }

        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $baseUrl = rtrim(config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $url = sprintf(
            '%s/models/%s:generateContent?key=%s',
            $baseUrl,
            $model,
            urlencode($apiKey)
        );

        try {
            $pending = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('services.gemini.timeout', 45));

            if (! config('services.gemini.verify_ssl', true)) {
                $pending = $pending->withoutVerifying();
            }

            $response = $pending->post($url, [
                'systemInstruction' => [
                    'parts' => [
                        [
                            'text' => 'You respond with a single JSON object only. No markdown fences, no commentary. Keys: activity (string), quote (string), extra (string). For moods other than sad, extra may be a very short fun Hinglish line or an empty string.',
                        ],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $this->buildPrompt($mood)],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.85,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if (! $response->successful()) {
                Log::warning('Gemini API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return self::FALLBACK;
            }

            $content = $this->extractTextFromGeminiResponse($response->json());
            $parsed = $this->decodeAiPayload($content);
            $normalized = $this->normalizePayload($parsed);

            if ($normalized === null) {
                Log::warning('Gemini returned invalid JSON or missing fields.', [
                    'raw' => $content,
                ]);

                return self::FALLBACK;
            }

            return $normalized;
        } catch (\Throwable $e) {
            Log::warning('Gemini mood suggestion failed: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return self::FALLBACK;
        }
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    private function extractTextFromGeminiResponse(?array $json): ?string
    {
        if ($json === null) {
            return null;
        }

        $candidates = $json['candidates'] ?? null;
        if (! is_array($candidates) || $candidates === []) {
            return null;
        }

        $first = $candidates[0];
        if (! is_array($first)) {
            return null;
        }

        $content = $first['content'] ?? null;
        if (! is_array($content)) {
            return null;
        }

        $parts = $content['parts'] ?? null;
        if (! is_array($parts) || $parts === []) {
            return null;
        }

        $part = $parts[0];
        if (! is_array($part) || ! isset($part['text'])) {
            return null;
        }

        return (string) $part['text'];
    }

    private function buildPrompt(string $mood): string
    {
        $mood = trim($mood);

        return <<<PROMPT
User mood is: {$mood}.
Suggest:

1. One helpful activity to improve mood
2. One motivational quote

If mood is 'sad', also include a short light joke.

Tone:

* Hinglish (Hindi + English mix)
* Gen-Z friendly
* Use emojis
* Keep response short and human-like

Return response strictly in JSON format:
{
"activity": "...",
"quote": "...",
"extra": "..."
}
PROMPT;
    }

    /**
     * @return array<string, string>|null
     */
    private function decodeAiPayload(?string $content): ?array
    {
        if ($content === null || $content === '') {
            return null;
        }

        $trimmed = trim($content);

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*\}/u', $trimmed, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @return array{activity: string, quote: string, extra: string}|null
     */
    private function normalizePayload(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $activity = isset($data['activity']) ? trim((string) $data['activity']) : '';
        $quote = isset($data['quote']) ? trim((string) $data['quote']) : '';

        if ($activity === '' || $quote === '') {
            return null;
        }

        $extra = array_key_exists('extra', $data) && $data['extra'] !== null
            ? trim((string) $data['extra'])
            : '';

        return [
            'activity' => $activity,
            'quote' => $quote,
            'extra' => $extra,
        ];
    }
}
