<?php

namespace App\Http\Controllers\Api\Fcm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fcm\DeleteFcmTokenRequest;
use App\Http\Requests\Fcm\SaveFcmTokenRequest;
use App\Services\Fcm\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FcmController extends Controller
{
    public function __construct(
        protected FcmService $fcmService
    ) {}

    public function saveToken(SaveFcmTokenRequest $request): JsonResponse
    {
        $userId = Auth::guard('api')->id();
        $result = $this->fcmService->saveToken($userId, $request->input('token'));

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data'    => new \stdClass,
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data'    => new \stdClass,
        ], $result['http_code'] ?? 200);
    }

    public function deleteToken(DeleteFcmTokenRequest $request): JsonResponse
    {
        $userId = Auth::guard('api')->id();
        $result = $this->fcmService->deleteToken($userId, $request->input('token'));

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data'    => new \stdClass,
            ], $result['http_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data'    => new \stdClass,
        ], $result['http_code'] ?? 200);
    }
}
