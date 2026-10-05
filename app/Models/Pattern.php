<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pattern extends Model
{
    protected $fillable = [
        'challenge_key', 'action_measure_id', 'cohort',
        'attempts', 'successes', 'failures', 'dismissals', 'failure_reasons', 'last_outcome_at',
    ];

    protected function casts(): array
    {
        return [
            'failure_reasons' => 'array',
            'attempts' => 'integer',
            'successes' => 'integer',
            'failures' => 'integer',
            'dismissals' => 'integer',
            'last_outcome_at' => 'datetime',
        ];
    }

    public function actionMeasure(): BelongsTo
    {
        return $this->belongsTo(ActionMeasure::class);
    }

    public function successRate(): ?float
    {
        if ($this->attempts === 0) {
            return null;
        }

        return round($this->successes / $this->attempts * 100, 1);
    }

    /**
     * Confidence decay: evidence older than ~6 months counts for less —
     * a 90% success rate from a year ago is weaker evidence than 90% last week.
     */
    public function freshnessFactor(): float
    {
        $days = $this->last_outcome_at?->diffInDays(now());

        return match (true) {
            $days === null => 1.0, // unknown age — don't penalize
            $days <= 90 => 1.0,
            $days <= 180 => 0.7,
            default => 0.5,
        };
    }

    /**
     * Recommendations that keep getting dismissed teach the system its
     * advice is being ignored — adoption weighs the effective rate down.
     */
    public function adoptionFactor(): float
    {
        $total = $this->attempts + $this->dismissals;

        return $total === 0 ? 1.0 : $this->attempts / $total;
    }

    public function effectiveRate(): ?float
    {
        $rate = $this->successRate();

        return $rate === null ? null : round($rate * $this->freshnessFactor() * $this->adoptionFactor(), 1);
    }
}
