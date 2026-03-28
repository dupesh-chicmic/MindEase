<?php

namespace App\Services\Chat;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use Illuminate\Support\Facades\Http;

class ChatService
{
    private const CONTEXT_LIMIT = 10;
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    private const CLAUDE_MODEL = 'claude-haiku-4-5-20251001';
    private const SYSTEM_PROMPT = 'You are a helpful mental wellness assistant. Be supportive, empathetic, and practical. Avoid harmful or extreme advice.';
    private const FALLBACK_RESPONSE = 'I\'m here to support you. Could you please try again? I want to make sure I give you the best response possible.';

    public function createThread(int $userId): array
    {
        try {
            $thread = ChatThread::create(['user_id' => $userId]);

            return [
                'success'   => true,
                'message'   => 'Thread created successfully',
                'data'      => $thread,
                'http_code' => 201,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'Failed to create thread',
                'http_code' => 500,
            ];
        }
    }

    public function handleSendMessage(int $userId, int $threadId, string $message): array
    {
        try {
            // 1. Save user message
            $userMessage = ChatMessage::create([
                'thread_id' => $threadId,
                'message'   => $message,
                'sender'    => 'user',
            ]);

            // 2. Fetch context (last N messages before this one)
            $contextMessages = $this->fetchContextMessages($threadId, excludeId: $userMessage->id);

            // 3. Generate AI response
            $aiText = $this->generateAIResponse($contextMessages, $message);

            // 4. Save AI response
            $aiMessage = ChatMessage::create([
                'thread_id' => $threadId,
                'message'   => $aiText,
                'sender'    => 'ai',
            ]);

            return [
                'success'   => true,
                'message'   => 'Message sent successfully',
                'data'      => [
                    'user_message' => $userMessage,
                    'ai_message'   => $aiMessage,
                ],
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'Failed to send message',
                'http_code' => 500,
            ];
        }
    }

    /**
     * Fetch the last N messages from the thread for context, optionally excluding a specific message ID.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function fetchContextMessages(int $threadId, ?int $excludeId = null): array
    {
        $query = ChatMessage::where('thread_id', $threadId)
            ->orderBy('created_at', 'desc')
            ->limit(self::CONTEXT_LIMIT);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $messages = $query->get()->reverse()->values();

        return $messages->map(fn (ChatMessage $msg) => [
            'role'    => $msg->sender === 'user' ? 'user' : 'assistant',
            'content' => $msg->message,
        ])->toArray();
    }

    /**
     * Send context + new message to Claude API and return the AI response text.
     */
    public function generateAIResponse(array $contextMessages, string $newUserMessage): string
    {
        try {
            $messages = $contextMessages;
            $messages[] = ['role' => 'user', 'content' => $newUserMessage];

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'         => config('services.claude.api_key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post(self::CLAUDE_API_URL, [
                    'model'      => self::CLAUDE_MODEL,
                    'max_tokens' => 1024,
                    'system'     => self::SYSTEM_PROMPT,
                    'messages'   => $messages,
                ]);

            if ($response->successful()) {
                $body = $response->json();

                return $body['content'][0]['text'] ?? self::FALLBACK_RESPONSE;
            }

            report(new \RuntimeException('Claude API error: ' . $response->status() . ' ' . $response->body()));

            return self::FALLBACK_RESPONSE;
        } catch (\Throwable $e) {
            report($e);

            return self::FALLBACK_RESPONSE;
        }
    }

    public function getHistory(int $userId, int $threadId): array
    {
        try {
            $thread = ChatThread::where('id', $threadId)
                ->where('user_id', $userId)
                ->first();

            if (! $thread) {
                return [
                    'success'   => false,
                    'message'   => 'Thread not found',
                    'http_code' => 404,
                ];
            }

            $messages = ChatMessage::where('thread_id', $threadId)
                ->orderBy('created_at', 'asc')
                ->get();

            return [
                'success'   => true,
                'message'   => 'History retrieved successfully',
                'data'      => $messages,
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'Failed to retrieve history',
                'http_code' => 500,
            ];
        }
    }

    public function getThreads(int $userId): array
    {
        try {
            $threads = ChatThread::where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->get();

            return [
                'success'   => true,
                'message'   => 'Threads retrieved successfully',
                'data'      => $threads,
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'Failed to retrieve threads',
                'http_code' => 500,
            ];
        }
    }

    public function deleteThread(int $userId, int $threadId): array
    {
        try {
            $thread = ChatThread::where('id', $threadId)
                ->where('user_id', $userId)
                ->first();

            if (! $thread) {
                return [
                    'success'   => false,
                    'message'   => 'Thread not found',
                    'http_code' => 404,
                ];
            }

            $thread->delete();

            return [
                'success'   => true,
                'message'   => 'Thread deleted successfully',
                'data'      => new \stdClass,
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'Failed to delete thread',
                'http_code' => 500,
            ];
        }
    }
}