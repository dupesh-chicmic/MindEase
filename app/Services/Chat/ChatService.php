<?php

namespace App\Services\Chat;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Services\AI\AiManager;

class ChatService
{
    private const CONTEXT_LIMIT = 10;
    private const SYSTEM_PROMPT = 'You are a helpful mental wellness assistant. Be supportive, empathetic, and practical. Avoid harmful or extreme advice.';

    public function __construct(
        protected AiManager $ai
    ) {}

    public function createThread(int $userId): array
    {
        try {
            $thread = ChatThread::create(['user_id' => $userId]);

            return [
                'success' => true,
                'message' => 'Thread created successfully',
                'data' => $thread,
                'http_code' => 201,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Failed to create thread',
                'http_code' => 500,
            ];
        }
    }

    public function handleSendMessage(int $userId, int $threadId, string $message): array
    {
        try {
            $userMessage = ChatMessage::create([
                'thread_id' => $threadId,
                'message' => $message,
                'sender' => 'user',
            ]);

            $contextMessages = $this->fetchContextMessages($threadId, excludeId: $userMessage->id);
            $aiResponse = $this->generateAIResponse($contextMessages, $message);

            if (! $aiResponse['success']) {
                return [
                    'success' => false,
                    'message' => $aiResponse['message'],
                    'data' => [
                        'user_message' => $userMessage,
                    ],
                    'http_code' => $aiResponse['http_code'],
                ];
            }

            $aiMessage = ChatMessage::create([
                'thread_id' => $threadId,
                'message' => $aiResponse['message'],
                'sender' => 'ai',
            ]);

            return [
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'user_message' => $userMessage,
                    'ai_message' => $aiMessage,
                ],
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Failed to send message',
                'http_code' => 500,
            ];
        }
    }

    /**
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
            'role' => $msg->sender === 'user' ? 'user' : 'assistant',
            'content' => $msg->message,
        ])->toArray();
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $contextMessages
     * @return array{success: bool, text?: string, message?: string, http_code: int}
     */
    public function generateAIResponse(array $contextMessages, string $newUserMessage): array
    {
        $messages = $contextMessages;
        $messages[] = ['role' => 'user', 'content' => $newUserMessage];

        $response = $this->ai->generateResponse($messages, [
            'system' => self::SYSTEM_PROMPT,
            'max_tokens' => 1024,
        ]);

        if (! $response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'message' => $response['text'] ?? '',
            'http_code' => $response['http_code'],
        ];
    }

    public function getHistory(int $userId, int $threadId): array
    {
        try {
            $thread = ChatThread::where('id', $threadId)
                ->where('user_id', $userId)
                ->first();

            if (! $thread) {
                return [
                    'success' => false,
                    'message' => 'Thread not found',
                    'http_code' => 404,
                ];
            }

            $messages = ChatMessage::where('thread_id', $threadId)
                ->orderBy('created_at', 'asc')
                ->get();

            return [
                'success' => true,
                'message' => 'History retrieved successfully',
                'data' => $messages,
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Failed to retrieve history',
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
                'success' => true,
                'message' => 'Threads retrieved successfully',
                'data' => $threads,
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Failed to retrieve threads',
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
                    'success' => false,
                    'message' => 'Thread not found',
                    'http_code' => 404,
                ];
            }

            $thread->delete();

            return [
                'success' => true,
                'message' => 'Thread deleted successfully',
                'data' => new \stdClass,
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Failed to delete thread',
                'http_code' => 500,
            ];
        }
    }
}
