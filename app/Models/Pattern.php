<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pattern extends Model
{
    protected $fillable = [
        'challenge_key', 'action_measure_id', 'cohort',
        'attempts', 'successes', 'failures', 'failure_reasons',
    ];

    protected function casts(): array
    {
        return [
            'failure_reasons' => 'array',
            'attempts' => 'integer',
            'successes' => 'integer',
            'failures' => 'integer',
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
}
