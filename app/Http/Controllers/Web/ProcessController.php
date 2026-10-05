<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Process;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index()
    {
        return view('processes.index', [
            'processes' => Process::with('assessments')->latest()->get(),
            'stages' => Process::STAGES,
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
