<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * AI coach layer — summarizes open recommendations into a short
 * natural-language coaching note (EN/DE) via an OpenAI-compatible
 * chat API. Falls back to a deterministic templated summary when
 * no API key is configured or the call fails.
 */
class AiCoach
{
    public static function available(): bool
    {
        return (bool) config('services.openai.key');
    }

    /**
     * @param  array<int, array{title:string,severity:string,company:string}>  $open
     * @param  array<string, int>  $signalTypes
     */
    public static function summary(int $userId, array $open, array $signalTypes, string $locale): string
    {
        $key = "coach:{$userId}:{$locale}:".md5(json_encode($open));

        return Cache::remember($key, 1800, function () use ($open, $signalTypes, $locale) {
            if (self::available()) {
                $text = self::generate($open, $signalTypes, $locale);
                if ($text !== null) {
                    return $text;
                }
            }

            return self::fallback($open, $locale);
        });
    }

    private static function generate(array $open, array $signalTypes, string $locale): ?string
    {
        $items = collect($open)->take(10)->map(
            fn (array $r) => "- [{$r['severity']}] {$r['company']}: {$r['title']}"
        )->implode("\n");

        $signals = collect($signalTypes)->map(fn (int $n, string $t) => "{$t}×{$n}")->implode(', ');

        $lang = $locale === 'de' ? 'German' : 'English';
        $messages = [
            ['role' => 'system', 'content' => "You are a concise business coach inside a decision-intelligence dashboard. Answer in {$lang}. Write 2-3 sentences max: what needs attention most right now and why. Plain sentences, no lists, no markdown."],
            ['role' => 'user', 'content' => "Open recommendations:\n{$items}\n\nRecent signal types: {$signals}"],
        ];

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(20)
                ->post(rtrim(config('services.openai.base_url'), '/').'/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => $messages,
                    'temperature' => 0.4,
                    'max_tokens' => 150,
                ]);

            return $response->successful()
                ? trim((string) data_get($response->json(), 'choices.0.message.content'))
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function fallback(array $open, string $locale): string
    {
        if ($open === []) {
            return $locale === 'de'
                ? 'Alles im grünen Bereich — keine offenen Empfehlungen.'
                : 'All clear — no open recommendations right now.';
        }

        $top = $open[0];
        $critical = collect($open)->where('severity', 'critical')->count();

        return $locale === 'de'
            ? sprintf('%d offene Empfehlungen (%d kritisch). Wichtigste zuerst: „%s" bei %s.', count($open), $critical, $top['title'], $top['company'])
            : sprintf('%d open recommendations (%d critical). Most important first: "%s" at %s.', count($open), $critical, $top['title'], $top['company']);
    }
}
