<?php

namespace App\Services\Insight;

use App\Models\Mood;
use App\Services\AI\AiManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InsightService
{
    private const SYSTEM_PROMPT  = 'You are a mental wellness assistant. Based on user mood data, generate helpful, empathetic insights and practical suggestions. Keep your insight to 2-3 sentences. Return ONLY valid JSON with keys "insight" (string) and "suggestions" (array of strings, max 4 items).';
    private const LOOKBACK_DAYS  = 14;

    /** Moods considered positive */
    private const POSITIVE_MOODS = ['happy', 'excited', 'calm', 'content', 'grateful', 'hopeful'];

    /** Moods considered negative */
    private const NEGATIVE_MOODS = ['sad', 'stressed', 'anxious', 'angry', 'depressed', 'lonely', 'frustrated'];

    /** Static fallback recommendations per mood */
    private const RECOMMENDATIONS = [
        'happy'      => ['Set a new personal goal', 'Help someone around you', 'Start a creative project', 'Practice gratitude journaling'],
        'excited'    => ['Channel energy into a workout', 'Plan your next milestone', 'Celebrate with someone close', 'Start a new learning course'],
        'calm'       => ['Do a mindful meditation session', 'Read a book', 'Take a gentle walk in nature', 'Write in your journal'],
        'content'    => ['Reflect on what is going well', 'Share positivity with a friend', 'Try a relaxing hobby', 'Plan a self-care activity'],
        'grateful'   => ['Write a gratitude list', 'Reach out to someone you appreciate', 'Volunteer or give back', 'Practice morning affirmations'],
        'hopeful'    => ['Visualize your goals', 'Break a big goal into steps', 'Journal about your future plans', 'Connect with a mentor'],
        'sad'        => ['Try a 10-minute guided meditation', 'Write your thoughts in a journal', 'Reach out to a trusted friend', 'Watch something comforting'],
        'stressed'   => ['Try box breathing for 5 minutes', 'Take a short walk outside', 'Listen to calming music', 'Write down your stressors and prioritize'],
        'anxious'    => ['Practice the 5-4-3-2-1 grounding technique', 'Do slow deep breathing', 'Limit screen time for an hour', 'Speak to someone you trust'],
        'angry'      => ['Try a vigorous physical activity', 'Write your feelings without filtering', 'Take a brief time-out in a quiet space', 'Practice counting to 10 slowly'],
        'depressed'  => ['Start with one small positive action', 'Go outside for fresh air', 'Reach out to a mental health professional', 'Engage in light stretching or yoga'],
        'lonely'     => ['Text or call a friend right now', 'Join an online community with shared interests', 'Adopt a small routine with others', 'Consider volunteering locally'],
        'frustrated' => ['Identify the root cause in writing', 'Take a break and revisit later', 'Try a quick exercise burst', 'Talk through it with someone objective'],
    ];

    private const DEFAULT_RECOMMENDATIONS = [
        'Practice mindful breathing for 5 minutes',
        'Take a short walk and enjoy fresh air',
        'Write down three things you are grateful for',
        'Connect with a friend or loved one',
    ];

    public function __construct(
        protected AiManager $ai
    ) {}

    // -----------------------------------------------------------------------

    public function getInsights(int $userId): array
    {
        try {
            $moods = $this->fetchMoods($userId);

            if ($moods->isEmpty()) {
                return [
                    'success'   => false,
                    'message'   => 'No mood data found for the last ' . self::LOOKBACK_DAYS . ' days. Start logging your moods to get personalized insights.',
                    'http_code' => 404,
                ];
            }

            $analysis  = $this->analyzeMoodData($moods);
            $aiResult  = $this->generateAIInsight($analysis);

            if (! $aiResult['success']) {
                return [
                    'success'   => false,
                    'message'   => $aiResult['message'],
                    'http_code' => $aiResult['http_code'],
                ];
            }

            return [
                'success'   => true,
                'message'   => 'Insights generated',
                'data'      => [
                    'summary'         => $analysis['summary'],
                    'dominant_mood'   => $analysis['dominant_mood'],
                    'trend'           => $analysis['trend'],
                    'insight'         => $aiResult['insight'],
                    'recommendations' => $this->getRecommendations($analysis['dominant_mood'], $aiResult['suggestions']),
                ],
                'http_code' => 200,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success'   => false,
                'message'   => 'Failed to generate insights. Please try again later.',
                'http_code' => 500,
            ];
        }
    }

    // -----------------------------------------------------------------------

    private function fetchMoods(int $userId): Collection
    {
        return Mood::where('user_id', $userId)
            ->where('recorded_at', '>=', Carbon::now()->subDays(self::LOOKBACK_DAYS)->startOfDay())
            ->orderBy('recorded_at', 'asc')
            ->get(['id', 'mood', 'note', 'recorded_at']);
    }

    // -----------------------------------------------------------------------

    /**
     * @return array{summary: array<string,int>, dominant_mood: string, trend: string, moods: Collection}
     */
    public function analyzeMoodData(Collection $moods): array
    {
        $summary = $moods
            ->groupBy('mood')
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc()
            ->toArray();

        $dominantMood = array_key_first($summary) ?? 'unknown';
        $trend        = $this->detectTrend($moods);

        return compact('summary', 'dominantMood', 'trend', 'moods') + ['dominant_mood' => $dominantMood];
    }

    // -----------------------------------------------------------------------

    public function detectTrend(Collection $moods): string
    {
        if ($moods->count() < 2) {
            return 'stable';
        }

        $half  = (int) ceil($moods->count() / 2);
        $first = $moods->take($half);
        $last  = $moods->slice($half);

        $score = fn (Collection $chunk): float =>
            $chunk->filter(fn (Mood $m) => in_array(strtolower($m->mood), self::POSITIVE_MOODS, true))->count()
            - $chunk->filter(fn (Mood $m) => in_array(strtolower($m->mood), self::NEGATIVE_MOODS, true))->count();

        $firstScore = $score($first);
        $lastScore  = $score($last);

        if ($lastScore > $firstScore) {
            return 'improving';
        }

        if ($lastScore < $firstScore) {
            return 'declining';
        }

        return 'stable';
    }

    // -----------------------------------------------------------------------

    /**
     * @param  array{summary: array<string,int>, dominant_mood: string, trend: string}  $analysis
     * @return array{success: bool, message?: string, http_code: int, insight?: string, suggestions?: string[]}
     */
    public function generateAIInsight(array $analysis): array
    {
        $result = $this->ai->generateResponse(
            [['role' => 'user', 'content' => $this->buildPrompt($analysis)]],
            ['system' => self::SYSTEM_PROMPT, 'max_tokens' => 512]
        );

        if (! $result['success']) {
            return $result;
        }

        $text = $result['text'];

        // Strip markdown fences if the provider wraps output in ```json … ```
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);

        $parsed = json_decode($text, true);

        if (
            ! is_array($parsed)
            || empty($parsed['insight'])
            || ! isset($parsed['suggestions'])
            || ! is_array($parsed['suggestions'])
        ) {
            Log::error('AI insights response could not be parsed', ['response_text' => $text]);

            return [
                'success'   => false,
                'message'   => 'Insights service returned an invalid response. Please try again later.',
                'http_code' => 502,
            ];
        }

        return [
            'success'     => true,
            'http_code'   => 200,
            'insight'     => (string) $parsed['insight'],
            'suggestions' => array_values(array_filter(array_map('strval', $parsed['suggestions']))),
        ];
    }

    // -----------------------------------------------------------------------

    /**
     * @param  string[]  $aiSuggestions
     * @return string[]
     */
    public function getRecommendations(string $dominantMood, array $aiSuggestions = []): array
    {
        $static = self::RECOMMENDATIONS[strtolower($dominantMood)] ?? self::DEFAULT_RECOMMENDATIONS;
        $merged = array_values(array_unique(array_merge($aiSuggestions, $static)));

        return array_slice($merged, 0, 4);
    }

    // -----------------------------------------------------------------------

    private function buildPrompt(array $analysis): string
    {
        $summaryLines = collect($analysis['summary'])
            ->map(fn (int $count, string $mood) => "- {$mood}: {$count} times")
            ->implode("\n");

        return <<<TEXT
        Here is the user's mood data for the last 14 days:

        Mood frequency:
        {$summaryLines}

        Dominant mood: {$analysis['dominant_mood']}
        Emotional trend: {$analysis['trend']}

        Based on this, generate a compassionate insight and 3-4 practical activity suggestions.
        Return ONLY a JSON object with keys "insight" and "suggestions".
        TEXT;
    }
}
