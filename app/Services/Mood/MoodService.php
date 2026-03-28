<?php

namespace App\Services\Mood;

use App\Models\User;
use App\Repositories\Mood\MoodRepository;
use Carbon\Carbon;

class MoodService
{
    public function __construct(
        protected MoodRepository $moodRepository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, http_code: int}
     */
    public function upsert(User $user, array $data): array
    {
        try {
            $today = Carbon::now(config('app.timezone'))->toDateString();

            $allowed = ['mood_score', 'mood_label', 'emoji', 'sleep_score', 'stress_score', 'productivity_score', 'ate_well'];
            $patch = [];
            foreach ($allowed as $key) {
                if (array_key_exists($key, $data)) {
                    $patch[$key] = $data[$key];
                }
            }

            if (array_key_exists('mood_label', $patch) && is_string($patch['mood_label'])) {
                $patch['mood_label'] = strtolower($patch['mood_label']);
            }

            $this->moodRepository->saveToday($user->id, $today, $patch);

            return [
                'success' => true,
                'message' => 'Mood saved successfully',
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Unable to save mood',
                'http_code' => 500,
            ];
        }
    }
}
