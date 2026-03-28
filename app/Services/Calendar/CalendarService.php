<?php

namespace App\Services\Calendar;

use App\Models\User;
use App\Repositories\Calendar\CalendarRepository;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CalendarService
{
    public function __construct(
        protected CalendarRepository $calendarRepository
    ) {}

    /**
     * @return array{success: bool, message: string, data?: array<string, mixed>, http_code: int}
     */
    public function monthly(User $user, int $month, int $year): array
    {
        try {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            if ((int) $start->month !== $month || (int) $start->year !== $year) {
                return [
                    'success' => false,
                    'message' => 'Invalid month or year',
                    'http_code' => 422,
                ];
            }

            $totalDays = $start->daysInMonth;
            $byDate = $this->calendarRepository->getMoodsForUserMonth($user->id, $month, $year);

            $days = [];
            for ($d = 1; $d <= $totalDays; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $mood = $byDate->get($dateStr);
                $days[] = [
                    'day' => $d,
                    'date' => $dateStr,
                    'is_logged' => $mood !== null,
                    'mood_score' => $mood?->mood_score,
                    'mood_label' => $mood && $mood->mood_label !== null
                        ? Str::title((string) $mood->mood_label)
                        : null,
                    'emoji' => $mood?->emoji,
                ];
            }

            return [
                'success' => true,
                'message' => 'Monthly mood data fetched',
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'total_days' => $totalDays,
                    'days' => $days,
                ],
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Unable to fetch calendar data',
                'http_code' => 500,
            ];
        }
    }
}
