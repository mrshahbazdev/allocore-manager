<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pattern extends Model
{
    protected $fillable = ['code', 'challenge', 'evidence', 'companies_count'];

    protected function casts(): array
    {
        return ['evidence' => 'array'];
    }
}
