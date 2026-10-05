<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outcome extends Model
{
    public const RESULT_SUCCESS = 'success';

    public const RESULT_FAILURE = 'failure';

    public const RESULT_PARTIAL = 'partial';

    protected $fillable = [
        'recommendation_id', 'result', 'failure_reason', 'metrics', 'measured_at',
    ];

    protected function casts(): array
    {
        return ['metrics' => 'array', 'measured_at' => 'datetime'];
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(Recommendation::class);
    }
}
