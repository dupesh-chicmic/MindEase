<?php

namespace App\Services\AI;

interface AiProviderInterface
{
    /**
     * Generate a response from the AI provider.
     *
     * $messages must be an array of role/content pairs in the canonical format:
     * [
     *   ['role' => 'user',      'content' => '...'],
     *   ['role' => 'assistant', 'content' => '...'],
     * ]
     *
     * $options may include:
     *   - system    (string)  system-level prompt
     *   - max_tokens (int)    max output tokens
     *
     * Returns:
     * [
     *   'success'   => bool,
     *   'text'      => string,   // AI response text (present when success=true)
     *   'message'   => string,   // user-facing error message (present when success=false)
     *   'http_code' => int,
     * ]
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{success: bool, text?: string, message?: string, http_code: int}
     */
    public function generateResponse(array $messages, array $options = []): array;
}
