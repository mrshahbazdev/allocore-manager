<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SourceAdminController extends Controller
{
    private function gate(Request $request): void
    {
        abort_unless($request->user()->role === 'allocore', 403);
    }

    public function index(Request $request): View
    {
        $this->gate($request);

        return view('sources', [
            'user' => $request->user(),
            'sources' => Source::withCount('signals')->withMax('signals', 'occurred_at')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->gate($request);

        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);

        $source = Source::create([
            'name' => $data['name'],
            'token' => Str::random(40),
        ]);

        return back()->with('status', __('ui.source_created', ['token' => $source->token]));
    }

    public function destroy(Request $request, Source $source): RedirectResponse
    {
        $this->gate($request);

        $source->delete();

        return back()->with('status', __('ui.source_deleted'));
    }
}
