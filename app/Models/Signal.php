<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signal extends Model
{
    protected $fillable = ['source_id', 'type', 'company_key', 'user_email', 'payload', 'occurred_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'occurred_at' => 'datetime'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
