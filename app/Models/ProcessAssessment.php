<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessAssessment extends Model
{
    protected $fillable = ['process_id', 'from_stage', 'to_stage', 'note'];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }
}
