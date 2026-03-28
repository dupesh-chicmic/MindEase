<?php

namespace App\Services\Profile;

use App\Models\User;
use App\Repositories\Mood\MoodRepository;
use Carbon\Carbon;

class ProfileStatsService
{
    public function __construct(
        protected MoodRepository $moodRepository
    ) {}

    /**
     * @return array{days_tracked: int, current_streak: int, best_streak: int}
     */
    public function forUser(User $user): array
    {
        $userId = $user->id;

        return [
            'days_tracked' => $this->moodRepository->countForUser($userId),
            'current_streak' => $this->currentStreak($userId),
            'best_streak' => $this->bestStreak($userId),
        ];
    }

    private function currentStreak(int $userId): int
    {
        $today = Carbon::now(config('app.timezone'))->startOfDay();
        $streak = 0;
        $d = $today->copy();
        while ($this->moodRepository->existsForUserOnDate($userId, $d)) {
            $streak++;
            $d->subDay();
        }

        return $streak;
    }

    private function bestStreak(int $userId): int
    {
        $dates = $this->moodRepository->getLoggedDateStringsAscending($userId);
        if ($dates->isEmpty()) {
            return 0;
        }

        $max = 1;
        $run = 1;
        for ($i = 1; $i < $dates->count(); $i++) {
            $prev = Carbon::parse($dates[$i - 1])->startOfDay();
            $curr = Carbon::parse($dates[$i])->startOfDay();
            if ($curr->equalTo($prev->copy()->addDay())) {
                $run++;
                if ($run > $max) {
                    $max = $run;
                }
            } else {
                $run = 1;
            }
        }

        return $max;
    }
}
