<?php

namespace App\Http\Controllers\Api\Insight;

use App\Http\Controllers\Controller;
use App\Services\Insight\InsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class InsightController extends Controller
{
    public function __construct(
        protected InsightService $insightService
    ) {}

    public function index(): JsonResponse
    {
        $userId = Auth::guard('api')->id();
        $result = $this->insightService->getInsights($userId);

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
            'data'    => $result['data'],
        ], $result['http_code'] ?? 200);
    }
}
