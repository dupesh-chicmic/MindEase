<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SuggestionIndexRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AISuggestionService;

class SuggestionController extends Controller
{
    public function __construct(
        private readonly AISuggestionService $aiSuggestionService
    ) {}

    public function index(SuggestionIndexRequest $request)
    {
        $data = $this->aiSuggestionService->generateForMood(
            $request->validated('mood')
        );

        return ApiResponse::success('AI suggestion generated', $data);
    }
}
