<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recommendation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_IMPLEMENTED = 'implemented';

    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'company_id', 'action_measure_id', 'challenge_key',
        'confidence', 'rationale', 'status', 'signal_id',
    ];

    protected function casts(): array
    {
        return ['rationale' => 'array', 'confidence' => 'float'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function actionMeasure(): BelongsTo
    {
        return $this->belongsTo(ActionMeasure::class);
    }

    public function signal(): BelongsTo
    {
        return $this->belongsTo(Signal::class);
    }

    public function outcome(): HasOne
    {
        return $this->hasOne(Outcome::class);
    }
}
