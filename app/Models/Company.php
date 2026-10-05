<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'source_id', 'external_id', 'name', 'industry', 'size',
        'maturity', 'situation', 'meta',
    ];

    protected function casts(): array
    {
        return ['situation' => 'array', 'meta' => 'array', 'size' => 'integer'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function signals(): HasMany
    {
        return $this->hasMany(Signal::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }
}
