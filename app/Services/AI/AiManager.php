<?php

namespace App\Services\AI;

use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;

class AiManager implements AiProviderInterface
{
    private const PROVIDER_CLAUDE = 1;
    private const PROVIDER_GEMINI = 2;

    private AiProviderInterface $provider;

    public function __construct()
    {
        $this->provider = $this->resolveProvider((int) config('ai.provider', self::PROVIDER_CLAUDE));
    }

    /**
     * {@inheritdoc}
     *
     * Delegates to the active provider resolved from AI_PROVIDER env.
     */
    public function generateResponse(array $messages, array $options = []): array
    {
        return $this->provider->generateResponse($messages, $options);
    }

    private function resolveProvider(int $providerId): AiProviderInterface
    {
        return match ($providerId) {
            self::PROVIDER_CLAUDE => new ClaudeProvider(),
            self::PROVIDER_GEMINI => new GeminiProvider(),
            default               => throw new \InvalidArgumentException(
                "Unsupported AI_PROVIDER value: {$providerId}. Use 1 (Claude) or 2 (Gemini)."
            ),
        };
    }
}
