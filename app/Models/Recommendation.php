<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recommendation extends Model
{
    protected $fillable = ['user_id', 'company_key', 'pattern_id', 'code', 'title', 'title_en', 'description', 'description_en', 'evidence', 'severity', 'status'];

    public function localizedTitle(): string
    {
        return app()->getLocale() === 'en' && $this->title_en ? $this->title_en : $this->title;
    }

    public function localizedDescription(): string
    {
        return app()->getLocale() === 'en' && $this->description_en ? $this->description_en : $this->description;
    }

    protected function casts(): array
    {
        return ['evidence' => 'array'];
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(Outcome::class);
    }

    public function latestOutcome(): HasOne
    {
        return $this->hasOne(Outcome::class)->latestOfMany();
    }

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }
}
