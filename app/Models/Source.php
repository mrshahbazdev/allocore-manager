<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $fillable = ['name', 'token'];

    public function signals(): HasMany
    {
        return $this->hasMany(Signal::class);
    }
}
