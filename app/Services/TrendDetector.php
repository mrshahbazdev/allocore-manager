<?php

namespace App\Services;

use App\Models\Signal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrendDetector
{
    /**
     * Compare challenge signal volume in the recent window vs the prior
     * window of equal length. Returns trends the system spotted — ideally
     * before users notice them.
     *
     * @return Collection<int, array{challenge_key: string, recent: int, previous: int, growth_pct: float|null, direction: string}>
     */
    public function detect(int $windowDays = 30, ?int $sourceId = null): Collection
    {
        $recentFrom = now()->subDays($windowDays);
        $previousFrom = now()->subDays($windowDays * 2);

        $recent = $this->counts($recentFrom, now(), $sourceId);
        $previous = $this->counts($previousFrom, $recentFrom, $sourceId);

        return $recent->keys()->merge($previous->keys())->unique()
            ->map(function (string $key) use ($recent, $previous) {
                $r = $recent[$key] ?? 0;
                $p = $previous[$key] ?? 0;

                return [
                    'challenge_key' => $key,
                    'recent' => $r,
                    'previous' => $p,
                    'growth_pct' => $p > 0 ? round(($r - $p) / $p * 100, 1) : ($r > 0 ? null : 0.0),
                    'direction' => $r > $p ? 'rising' : ($r < $p ? 'falling' : 'stable'),
                ];
            })
            ->sortByDesc(fn (array $t) => $t['recent'])
            ->values();
    }

    public function emerging(int $windowDays = 30, int $minSignals = 2): Collection
    {
        return $this->detect($windowDays)
            ->filter(fn (array $t) => $t['direction'] === 'rising' && $t['recent'] >= $minSignals)
            ->values();
    }

    private function counts($from, $to, ?int $sourceId = null): Collection
    {
        return Signal::whereNotNull('challenge_key')
            ->when($sourceId, fn ($q) => $q->where('source_id', $sourceId))
            ->whereBetween('occurred_at', [$from, $to])
            ->select('challenge_key', DB::raw('count(*) as total'))
            ->groupBy('challenge_key')
            ->pluck('total', 'challenge_key');
    }
}
