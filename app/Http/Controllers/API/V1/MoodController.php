<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MoodController extends Controller
{
    public function updateMood(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mood_score' => 'required|integer|min:1|max:5',
            'mood_label' => 'required|string|max:50',
            'emoji' => 'required|string|max:10',
        ]);

        $user = $request->user('api');
        $user->mood_score = $validated['mood_score'];
        $user->mood_label = $validated['mood_label'];
        $user->emoji = $validated['emoji'];
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Mood updated successfully',
            'data' => [
                'mood_score' => (int) $user->mood_score,
                'mood_label' => $user->mood_label,
                'emoji' => $user->emoji,
            ],
        ]);
    }
}
