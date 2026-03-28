<?php

namespace App\Services\Dashboard;

use App\Models\Mood;
use App\Models\User;
use App\Repositories\Dashboard\DashboardRepository;
use App\Repositories\Mood\MoodRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardService
{
    public function __construct(
        protected MoodRepository $moodRepository,
        protected DashboardRepository $dashboardRepository
    ) {}

    /**
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function build(User $user): array
    {
        try {
            $now = Carbon::now(config('app.timezone'));
            $today = $now->copy()->startOfDay();
            $weekStart = $today->copy()->subDays(6);

            $quote = $this->dashboardRepository->randomQuote();
            $todayMood = $this->moodRepository->findForUserOnDate($user->id, $today);
            $showQuestions = $this->shouldShowQuestions($todayMood);

            $base = [
                'greeting' => $this->greetingFor($now),
                'user_name' => $user->name,
                'quote' => $quote
                    ? [
                        'text' => $quote->text,
                        'author' => $quote->author,
                    ]
                    : [
                        'text' => '',
                        'author' => '',
                    ],
                'show_questions_card' => $showQuestions,
            ];

            if ($showQuestions) {
                $base['questions_config'] = $this->questionsConfig();

                return [
                    'success' => true,
                    'message' => 'Dashboard loaded',
                    'data' => $base,
                ];
            }

            $weekMoods = $this->moodRepository->getForUserBetweenDates(
                $user->id,
                $weekStart,
                $today
            );

            $weeklyMoodScore = $weekMoods->isEmpty()
                ? null
                : round($weekMoods->avg('mood_score'), 1);

            $base['today_mood'] = [
                'emoji' => $todayMood?->emoji,
                'label' => $todayMood ? Str::title((string) $todayMood->mood_label) : null,
            ];
            $base['weekly_mood_score'] = $weeklyMoodScore;
            $base['mood_trend'] = $this->moodTrendScores($weekStart, $today, $weekMoods);
            $base['mood_distribution'] = $this->moodDistribution($weekMoods);
            $base['mood_streak'] = $this->computeStreak($user->id, $today);
            $base['most_frequent_mood'] = $this->mostFrequentMoodLabel($weekMoods);
            $base['difficult_days_count'] = $this->difficultDaysCount($weekMoods);
            $base['journal_impact'] = $this->journalImpactString($weekMoods);
            $base['weekly_insight'] = $this->weeklyInsight($weeklyMoodScore, $weekMoods);

            return [
                'success' => true,
                'message' => 'Dashboard loaded',
                'data' => $base,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Unable to load dashboard',
                'data' => new \stdClass,
            ];
        }
    }

    private function shouldShowQuestions(?Mood $todayMood): bool
    {
        if (! $todayMood) {
            return true;
        }

        return ! $todayMood->followUpComplete();
    }

    /**
     * @return array<string, mixed>
     */
    private function questionsConfig(): array
    {
        return [
            'mood_options' => [
                ['score' => 1, 'label' => 'Terrible', 'emoji' => '😫'],
                ['score' => 2, 'label' => 'Bad', 'emoji' => '😔'],
                ['score' => 3, 'label' => 'Okay', 'emoji' => '😐'],
                ['score' => 4, 'label' => 'Good', 'emoji' => '🙂'],
                ['score' => 5, 'label' => 'Excellent', 'emoji' => '😄'],
            ],
            'sliders' => [
                'sleep_score' => 'How well did you sleep last night?',
                'stress_score' => 'How stressed do you feel right now?',
                'productivity_score' => 'How productive was your day?',
            ],
            'boolean_questions' => [
                'ate_well' => 'Did you eat well today?',
            ],
        ];
    }

    private function greetingFor(Carbon $now): string
    {
        $h = (int) $now->format('G');

        if ($h >= 5 && $h < 12) {
            return 'Good Morning';
        }
        if ($h >= 12 && $h < 17) {
            return 'Good Afternoon';
        }

        return 'Good Evening';
    }

    /**
     * @param  Collection<int, Mood>  $weekMoods
     * @return array<int, int|null>
     */
    private function moodTrendScores(Carbon $weekStart, Carbon $today, Collection $weekMoods): array
    {
        $byDate = $weekMoods->keyBy(fn (Mood $m) => $m->date->toDateString());
        $out = [];
        $cursor = $weekStart->copy();
        while ($cursor->lte($today)) {
            $m = $byDate->get($cursor->toDateString());
            $out[] = $m?->mood_score;
            $cursor->addDay();
        }

        return $out;
    }

    /**
     * @param  Collection<int, Mood>  $weekMoods
     * @return array{happy: int, neutral: int, sad_anxious: int}
     */
    private function moodDistribution(Collection $weekMoods): array
    {
        $total = $weekMoods->count();
        if ($total === 0) {
            return [
                'happy' => 0,
                'neutral' => 0,
                'sad_anxious' => 0,
            ];
        }

        $happy = $weekMoods->filter(fn (Mood $m) => $m->mood_score !== null && $m->mood_score >= 4)->count();
        $neutral = $weekMoods->filter(fn (Mood $m) => $m->mood_score === 3)->count();
        $sadAnxious = $weekMoods->filter(fn (Mood $m) => $m->mood_score !== null && $m->mood_score <= 2)->count();

        $pct = fn (int $n) => (int) round(($n / $total) * 100);

        return [
            'happy' => $pct($happy),
            'neutral' => $pct($neutral),
            'sad_anxious' => $pct($sadAnxious),
        ];
    }

    private function computeStreak(int $userId, Carbon $today): int
    {
        $streak = 0;
        $d = $today->copy();
        while ($this->moodRepository->existsForUserOnDate($userId, $d)) {
            $streak++;
            $d->subDay();
        }

        return $streak;
    }

    /**
     * @param  Collection<int, Mood>  $weekMoods
     */
    private function mostFrequentMoodLabel(Collection $weekMoods): ?string
    {
        $withLabel = $weekMoods->filter(fn (Mood $m) => $m->mood_label !== null && $m->mood_label !== '');
        if ($withLabel->isEmpty()) {
            return null;
        }

        $counts = $withLabel->groupBy(fn (Mood $m) => strtolower((string) $m->mood_label))
            ->map->count()
            ->sortDesc();

        $top = $counts->keys()->first();

        return $top ? Str::title($top) : null;
    }

    /**
     * @param  Collection<int, Mood>  $weekMoods
     */
    private function difficultDaysCount(Collection $weekMoods): int
    {
        return $weekMoods->filter(fn (Mood $m) => $m->mood_score !== null && $m->mood_score <= 2)->count();
    }

    /**
     * @param  Collection<int, Mood>  $weekMoods
     */
    private function journalImpactString(Collection $weekMoods): string
    {
        $goodSleep = $weekMoods->filter(fn (Mood $m) => $m->sleep_score !== null && $m->sleep_score >= 3);
        $poorSleep = $weekMoods->filter(fn (Mood $m) => $m->sleep_score !== null && $m->sleep_score < 3);

        if ($goodSleep->isEmpty() || $poorSleep->isEmpty()) {
            return 'Not enough sleep ratings this week to compare with mood.';
        }

        $avgG = (float) $goodSleep->avg('mood_score');
        $avgP = (float) $poorSleep->avg('mood_score');

        if ($avgP <= 0 && $avgG <= 0) {
            return 'Not enough mood data paired with sleep to measure impact.';
        }

        if ($avgG >= $avgP) {
            if ($avgP <= 0) {
                return 'Your mood averages higher on days you rate sleep 3 or above.';
            }
            $pct = (int) round((($avgG - $avgP) / $avgP) * 100);

            return "Your mood averages about {$pct}% higher on days you rate sleep 3 or above.";
        }

        if ($avgG <= 0) {
            return 'Your mood averages lower on days you rate sleep 3 or above.';
        }
        $pct = (int) round((($avgP - $avgG) / $avgG) * 100);

        return "Your mood averages about {$pct}% lower on days you rate sleep 3 or above versus poorer sleep days.";
    }

    /**
     * @param  Collection<int, Mood>  $weekMoods
     */
    private function weeklyInsight(?float $weeklyMoodScore, Collection $weekMoods): string
    {
        $goodSleep = $weekMoods->filter(fn (Mood $m) => $m->sleep_score !== null && $m->sleep_score >= 3);
        $poorSleep = $weekMoods->filter(fn (Mood $m) => $m->sleep_score !== null && $m->sleep_score < 3);
        if ($goodSleep->isNotEmpty() && $poorSleep->isNotEmpty()) {
            $avgG = (float) $goodSleep->avg('mood_score');
            $avgP = (float) $poorSleep->avg('mood_score');
            if ($avgG > $avgP + 0.3) {
                return 'You feel better on days when you sleep well.';
            }
        }

        if ($weeklyMoodScore === null) {
            return 'Log your mood this week to unlock personalized insights.';
        }
        if ($weeklyMoodScore >= 4) {
            return 'You had a strong emotional week overall.';
        }
        if ($weeklyMoodScore >= 3) {
            return 'Your week had a mix of highs and lows—keep checking in with yourself.';
        }

        return 'This week looked heavy—prioritize rest and small wins.';
    }
}
