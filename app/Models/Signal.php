<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signal extends Model
{
    protected $fillable = [
        'source_id', 'company_id', 'external_user_id', 'type',
        'challenge_key', 'payload', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'occurred_at' => 'datetime'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
