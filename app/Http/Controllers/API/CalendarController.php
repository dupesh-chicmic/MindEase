<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\CalendarIndexRequest;
use App\Services\Calendar\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function __construct(
        protected CalendarService $calendarService
    ) {}

    public function index(CalendarIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->calendarService->monthly(
            Auth::guard('api')->user(),
            (int) $validated['month'],
            (int) $validated['year']
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['http_code']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ], $result['http_code']);
    }
}
