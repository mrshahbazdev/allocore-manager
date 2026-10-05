<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Collection;

class CompanySimilarity
{
    /**
     * Cohort key used to bucket pattern statistics.
     * Companies are similar by situation and maturity, not industry alone.
     */
    public function cohortFor(Company $company): string
    {
        $maturity = $company->maturity ?: 'unknown';
        $situation = collect($company->situation ?? [])->sort()->take(3)->implode('+');

        return $situation !== '' ? "{$maturity}:{$situation}" : $maturity;
    }

    /**
     * Score how similar another company is (0-100).
     * Weights: shared situation tags 60, maturity 25, industry 15.
     */
    public function score(Company $a, Company $b): float
    {
        if ($a->id === $b->id) {
            return 0.0;
        }

        $situationA = collect($a->situation ?? []);
        $situationB = collect($b->situation ?? []);
        $shared = $situationA->intersect($situationB)->count();
        $union = $situationA->merge($situationB)->unique()->count();
        $situationScore = $union > 0 ? $shared / $union : 0;

        $maturityScore = $a->maturity !== null && $a->maturity === $b->maturity ? 1.0 : 0.0;
        $industryScore = $a->industry !== null && $a->industry === $b->industry ? 1.0 : 0.0;

        return round(($situationScore * 60) + ($maturityScore * 25) + ($industryScore * 15), 1);
    }

    /**
     * Companies similar to the given one, above a minimum score.
     *
     * @return Collection<int, Company>
     */
    public function similarTo(Company $company, float $minScore = 30.0, int $limit = 50): Collection
    {
        return Company::query()
            ->whereKeyNot($company->id)
            ->get()
            ->map(fn (Company $other) => [$other, $this->score($company, $other)])
            ->filter(fn (array $pair) => $pair[1] >= $minScore)
            ->sortByDesc(fn (array $pair) => $pair[1])
            ->take($limit)
            ->map(fn (array $pair) => $pair[0])
            ->values();
    }
}
