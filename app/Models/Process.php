<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    public const STAGES = ['manual', 'assisted', 'semi_automated', 'automated'];

    protected $fillable = ['name', 'description', 'scope', 'automation_stage'];

    public function assessments(): HasMany
    {
        return $this->hasMany(ProcessAssessment::class);
    }

    public function stageIndex(): int
    {
        return array_search($this->automation_stage, self::STAGES) ?: 0;
    }

    public function advanceTo(string $stage, ?string $note = null): void
    {
        ProcessAssessment::create([
            'process_id' => $this->id,
            'from_stage' => $this->automation_stage,
            'to_stage' => $stage,
            'note' => $note,
        ]);

        $this->update(['automation_stage' => $stage]);
    }
}
