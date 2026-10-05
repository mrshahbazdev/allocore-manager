<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Source extends Model
{
    protected $fillable = ['key', 'name', 'type', 'ingest_token', 'meta', 'is_active'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Source $source) {
            $source->ingest_token ??= Str::random(48);
        });
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function signals(): HasMany
    {
        return $this->hasMany(Signal::class);
    }
}
