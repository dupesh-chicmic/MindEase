<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mood\LogMoodRequest;
use App\Services\Mood\MoodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MoodController extends Controller
{
    public function __construct(
        protected MoodService $moodService
    ) {}

    public function log(LogMoodRequest $request): JsonResponse
    {
        $result = $this->moodService->upsert(
            Auth::guard('api')->user(),
            $request->validated()
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => new \stdClass,
        ], $result['http_code']);
    }
}
