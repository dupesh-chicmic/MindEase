<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(): JsonResponse
    {
        $result = $this->dashboardService->build(Auth::guard('api')->user());
        $code = $result['success'] ? 200 : 500;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'],
        ], $code);
    }
}
