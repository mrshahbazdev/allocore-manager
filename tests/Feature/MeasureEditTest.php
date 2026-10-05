<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasureEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_measure_edit_page_and_update(): void
    {
        $measure = ActionMeasure::create([
            'key' => 'backup', 'name' => 'Backup', 'description' => 'old',
            'addresses_challenges' => ['it_risk'], 'is_active' => true,
        ]);

        $this->get(route('measures.edit', $measure))->assertOk()->assertSee('Backup');

        $this->patch(route('measures.update', $measure), [
            'name' => 'Backup & Recovery',
            'description' => 'new desc',
            'addresses_challenges' => 'it_risk, data_loss',
            'is_active' => '1',
        ])->assertRedirect(route('measures.show', $measure));

        $measure->refresh();
        $this->assertSame('Backup & Recovery', $measure->name);
        $this->assertSame(['it_risk', 'data_loss'], $measure->addresses_challenges);
        $this->assertTrue($measure->is_active);
    }

    public function test_quick_toggle_still_works(): void
    {
        $measure = ActionMeasure::create(['key' => 'backup', 'name' => 'Backup', 'is_active' => true]);

        $this->patch(route('measures.update', $measure), ['is_active' => '0']);
        $this->assertFalse($measure->fresh()->is_active);
    }
}
