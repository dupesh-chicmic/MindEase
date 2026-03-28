<?php

namespace App\Repositories\Calendar;

use App\Models\Mood;
use Illuminate\Support\Collection;

class CalendarRepository
{
    /**
     * @return Collection<string, Mood>
     */
    public function getMoodsForUserMonth(int $userId, int $month, int $year): Collection
    {
        return Mood::query()
            ->where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->keyBy(fn (Mood $m) => $m->date->toDateString());
    }
}
