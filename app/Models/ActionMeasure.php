<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActionMeasure extends Model
{
    protected $fillable = ['key', 'name', 'description', 'addresses_challenges', 'is_active'];

    protected function casts(): array
    {
        return ['addresses_challenges' => 'array', 'is_active' => 'boolean'];
    }

    public function patterns(): HasMany
    {
        return $this->hasMany(Pattern::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }
}
