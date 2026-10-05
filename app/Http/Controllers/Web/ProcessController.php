<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Services\AutomationAdvisor;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index(AutomationAdvisor $advisor)
    {
        return view('processes.index', [
            'processes' => Process::with('assessments')->latest()->get(),
            'stages' => Process::STAGES,
            'paths' => $advisor->assess(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scope' => ['required', 'in:ecosystem,platform,company'],
        ]);

        Process::create($data);

        return back()->with('status', 'Process registered (starts as manual).');
    }

    // One-click: adopt the advisor's suggested stage for a challenge as a
    // tracked process (manual → automated ladder applied to the loop itself).
    public function adopt(Request $request, AutomationAdvisor $advisor)
    {
        $data = $request->validate(['challenge_key' => ['required', 'string', 'max:255']]);
        $path = collect($advisor->assess())->firstWhere('challenge_key', $data['challenge_key']);

        abort_unless($path, 404);

        $process = Process::firstOrCreate(
            ['name' => "decision-loop:{$data['challenge_key']}"],
            ['scope' => 'ecosystem', 'description' => "Decision loop for {$data['challenge_key']}"]
        );
        $process->advanceTo($path['suggested_stage'], $path['reason']);

        return back()->with('status', "Challenge loop tracked at {$path['suggested_stage']}.");
    }

    public function advance(Request $request, Process $process)
    {
        $data = $request->validate([
            'stage' => ['required', 'in:'.implode(',', Process::STAGES)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $process->advanceTo($data['stage'], $data['note'] ?? null);

        return back()->with('status', "Process moved to {$data['stage']}.");
    }
}
