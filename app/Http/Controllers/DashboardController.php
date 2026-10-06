<?php

namespace App\Http\Controllers;

use App\Models\Outcome;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Support\DecisionEngine;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $recs = $this->recommendationsFor($user);

        $open = $recs->where('status', 'open')->values();
        $done = $recs->whereIn('status', ['done', 'dismissed'])->values();

        return view('dashboard', [
            'user' => $user,
            'open' => $open,
            'done' => $done,
            'navOpen' => $open->count(),
            'stats' => $this->stats($open, $done),
            'recentSignals' => Signal::with('source')->latest('occurred_at')->limit(8)->get(),
            'platformIntel' => in_array($user->role, ['platform_manager', 'allocore']) ? $this->platformIntel() : null,
            'allocoreIntel' => $user->role === 'allocore' ? $this->allocoreIntel() : null,
            'disavoIntel' => $user->role === 'disavo' ? $this->disavoIntel() : null,
        ]);
    }

    public function recommendations(Request $request)
    {
        $user = $request->user();
        $recs = $this->recommendationsFor($user);

        return view('recommendations', [
            'user' => $user,
            'open' => $recs->where('status', 'open')->values(),
            'done' => $recs->whereIn('status', ['done', 'dismissed'])->values(),
            'navOpen' => $recs->where('status', 'open')->count(),
        ]);
    }

    public function signals(Request $request)
    {
        $user = $request->user();
        $navOpen = $this->recommendationsFor($user)->where('status', 'open')->count();

        return view('signals', [
            'user' => $user,
            'navOpen' => $navOpen,
            'signals' => Signal::with('source')->latest('occurred_at')->paginate(50),
        ]);
    }

    public function companies(Request $request)
    {
        $user = $request->user();
        $navOpen = $this->recommendationsFor($user)->where('status', 'open')->count();

        $companies = Signal::selectRaw('company_key, count(*) as n')
            ->whereNotNull('company_key')
            ->groupBy('company_key')
            ->orderByDesc('n')
            ->get()
            ->map(function ($c) {
                $open = Recommendation::where('company_key', $c->company_key)->where('status', 'open')->count();
                $done = Recommendation::where('company_key', $c->company_key)->whereIn('status', ['done', 'dismissed'])->count();

                return (object) ['company_key' => $c->company_key, 'signals' => $c->n, 'open' => $open, 'done' => $done];
            });

        return view('companies', [
            'user' => $user,
            'navOpen' => $navOpen,
            'companies' => $companies,
        ]);
    }

    private function recommendationsFor($user)
    {
        return Recommendation::query()
            ->with('latestOutcome')
            ->when($user->role === 'member' && $user->company_key,
                fn ($q) => $q->where(fn ($q2) => $q2->where('company_key', $user->company_key)->orWhereNull('company_key')))
            ->when($user->role === 'member' && ! $user->company_key,
                fn ($q) => $q->where('company_key', 'default'))
            ->orderByRaw("case severity when 'critical' then 0 when 'warning' then 1 else 2 end")
            ->orderByDesc('created_at')
            ->get();
    }

    private function stats($open, $done): array
    {
        return [
            'signals' => Signal::count(),
            'open' => $open->count(),
            'done' => $done->count(),
            'success_rate' => $this->successRate(),
        ];
    }

    private function successRate(): ?int
    {
        $outcomes = Outcome::whereIn('result', ['success', 'failed'])->get();
        if ($outcomes->isEmpty()) {
            return null;
        }

        return (int) round(100 * $outcomes->where('result', 'success')->count() / $outcomes->count());
    }

    /** Product intelligence for platform managers. */
    private function platformIntel(): array
    {
        return [
            'top_challenges' => Pattern::orderByDesc('companies_count')->limit(5)->get(),
            'by_type' => Signal::selectRaw('type, count(*) as n')->groupBy('type')->orderByDesc('n')->limit(8)->get(),
            'recommendation_stats' => Recommendation::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
        ];
    }

    /** Model quality for the Allocore team. */
    private function allocoreIntel(): array
    {
        $byCode = Recommendation::selectRaw('code, count(*) as n')->groupBy('code')->get()
            ->map(fn ($r) => ['code' => $r->code, 'recommendations' => $r->n] + DecisionEngine::effectiveness($r->code));

        return [
            'clusters' => Signal::selectRaw('company_key, count(*) as n')->whereNotNull('company_key')->groupBy('company_key')->get(),
            'effectiveness' => $byCode,
        ];
    }

    /** Decision intelligence for DISAVO — portfolio only, no operational detail. */
    private function disavoIntel(): array
    {
        $companies = Signal::whereNotNull('company_key')->distinct()->count('company_key');
        $resolved = Outcome::whereIn('result', ['success', 'failed'])->count();
        $resolvedOk = Outcome::where('result', 'success')->count();

        return [
            'companies' => $companies,
            'recommendations_total' => Recommendation::count(),
            'resolved' => $resolved,
            'success_rate' => $this->successRate(),
            'critical_open' => Recommendation::where('status', 'open')->where('severity', 'critical')->count(),
        ];
    }
}
