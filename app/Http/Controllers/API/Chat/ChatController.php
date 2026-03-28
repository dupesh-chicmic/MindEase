<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\GetHistoryRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    public function createThread(): JsonResponse
    {
        $userId = Auth::guard('api')->id();
        $result = $this->chatService->createThread($userId);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => new \stdClass,
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ], $result['http_code'] ?? 201);
    }

    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $userId = Auth::guard('api')->id();
        $validated = $request->validated();

        $result = $this->chatService->handleSendMessage(
            $userId,
            (int) $validated['thread_id'],
            $validated['message']
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => new \stdClass,
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ], $result['http_code'] ?? 200);
    }

    public function getHistory(GetHistoryRequest $request): JsonResponse
    {
        $userId = Auth::guard('api')->id();
        $threadId = (int) $request->input('thread_id');

        $result = $this->chatService->getHistory($userId, $threadId);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => new \stdClass,
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ], $result['http_code'] ?? 200);
    }

    public function getThreads(): JsonResponse
    {
        $userId = Auth::guard('api')->id();
        $result = $this->chatService->getThreads($userId);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => new \stdClass,
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ], $result['http_code'] ?? 200);
    }

    public function deleteThread(int $id): JsonResponse
    {
        $userId = Auth::guard('api')->id();
        $result = $this->chatService->deleteThread($userId, $id);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => new \stdClass,
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ], $result['http_code'] ?? 200);
    }
}
