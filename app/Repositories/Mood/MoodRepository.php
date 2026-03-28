<?php

namespace App\Repositories\Mood;

use App\Models\Mood;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class MoodRepository
{
    public function existsForUserOnDate(int $userId, CarbonInterface $date): bool
    {
        return Mood::query()
            ->where('user_id', $userId)
            ->whereDate('date', $date->toDateString())
            ->exists();
    }

    public function findForUserOnDate(int $userId, CarbonInterface $date): ?Mood
    {
        return Mood::query()
            ->where('user_id', $userId)
            ->whereDate('date', $date->toDateString())
            ->first();
    }

    /**
     * @return Collection<int, Mood>
     */
    public function getForUserBetweenDates(int $userId, CarbonInterface $start, CarbonInterface $end): Collection
    {
        return Mood::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();
    }

    public function saveToday(int $userId, string $dateYmd, array $attributes): Mood
    {
        return Mood::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'date' => $dateYmd,
            ],
            $attributes
        );
    }

    public function countForUser(int $userId): int
    {
        return Mood::query()->where('user_id', $userId)->count();
    }

    /**
     * @return Collection<int, string>
     */
    public function getLoggedDateStringsAscending(int $userId): Collection
    {
        return Mood::query()
            ->where('user_id', $userId)
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : (string) $d)
            ->unique()
            ->values();
    }
}
