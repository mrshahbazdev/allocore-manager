<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pattern extends Model
{
    protected $fillable = [
        'challenge_key', 'action_measure_id', 'cohort',
        'attempts', 'successes', 'failures', 'failure_reasons', 'last_outcome_at',
    ];

    protected function casts(): array
    {
        return [
            'failure_reasons' => 'array',
            'attempts' => 'integer',
            'successes' => 'integer',
            'failures' => 'integer',
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

    public function effectiveRate(): ?float
    {
        $rate = $this->successRate();

        return $rate === null ? null : round($rate * $this->freshnessFactor(), 1);
    }
}
