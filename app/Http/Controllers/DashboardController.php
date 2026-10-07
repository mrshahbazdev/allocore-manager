<?php

namespace App\Http\Controllers;

use App\Models\Outcome;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Support\AiCoach;
use App\Support\DecisionEngine;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $recs = $this->recommendationsFor($user);

        $open = $recs->where('status', 'open')->values();
        $done = $recs->whereIn('status', ['done', 'dismissed', 'expired'])->values();

        $sortedOpen = $this->sortBySeverity($open);
        $signalScope = Signal::query()
            ->when($user->role === 'member' && $user->company_key,
                fn ($q) => $q->where(fn ($q2) => $q2->where('company_key', $user->company_key)->orWhereNull('company_key')))
            ->when($user->role === 'member' && ! $user->company_key,
                fn ($q) => $q->where('company_key', 'default'));

        $coach = AiCoach::summary(
            $user->id,
            $sortedOpen->map(fn ($r) => ['title' => $r->localizedTitle(), 'severity' => $r->severity, 'company' => $r->company_key ?? '—'])->all(),
            (clone $signalScope)->selectRaw('type, count(*) as n')->where('occurred_at', '>=', now()->subDays(7))->groupBy('type')->pluck('n', 'type')->all(),
            (clone $signalScope)->whereNotNull('payload')->latest('occurred_at')->limit(10)->get()->map(fn ($s) => [
                'type' => $s->type,
                'company' => $s->company_key ?? '—',
                'fields' => collect($s->payload ?? [])->filter(fn ($v) => ! is_array($v) && $v !== null)->take(6)->map(fn ($v, $k) => "{$k}=".mb_strimwidth((string) (is_bool($v) ? var_export($v, true) : $v), 0, 60, '…'))->implode(', '),
                'at' => $s->occurred_at?->diffForHumans(),
            ])->all(),
            app()->getLocale()
        );

        return view('dashboard', [
            'coach' => $coach,
            'user' => $user,
            'open' => $sortedOpen,
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

        $code = $request->query('code');
        $codes = $recs->pluck('code')->filter()->unique()->sort()->values();
        if ($code) {
            $recs = $recs->where('code', $code);
        }

        $company = $request->query('company');
        $companies = $recs->pluck('company_key')->filter()->unique()->sort()->values();
        if ($company) {
            $recs = $recs->where('company_key', $company);
        }

        return view('recommendations', [
            'user' => $user,
            'open' => $this->sortBySeverity($recs->where('status', 'open')->values()),
            'done' => $recs->whereIn('status', ['done', 'dismissed', 'expired'])->values(),
            'navOpen' => $recs->where('status', 'open')->count(),
            'codes' => $codes,
            'activeCode' => $code,
            'companies' => $companies,
            'activeCompany' => $company,
        ]);
    }

    public function signals(Request $request)
    {
        $user = $request->user();
        $navOpen = $this->recommendationsFor($user)->where('status', 'open')->count();

        $query = Signal::with('source')->latest('occurred_at')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('company'), fn ($q) => $q->where('company_key', $request->company));

        return view('signals', [
            'user' => $user,
            'navOpen' => $navOpen,
            'signals' => $query->paginate(50)->withQueryString(),
            'types' => Signal::selectRaw('type, count(*) as n')->groupBy('type')->orderBy('type')->pluck('n', 'type'),
            'companies' => Signal::whereNotNull('company_key')->distinct()->orderBy('company_key')->pluck('company_key'),
            'filters' => $request->only('type', 'company'),
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
                $done = Recommendation::where('company_key', $c->company_key)->whereIn('status', ['done', 'dismissed', 'expired'])->count();

                return (object) ['company_key' => $c->company_key, 'signals' => $c->n, 'open' => $open, 'done' => $done];
            });

        return view('companies', [
            'user' => $user,
            'navOpen' => $navOpen,
            'companies' => $companies,
        ]);
    }

    public function patterns(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['platform_manager', 'allocore']), 403);

        $navOpen = $this->recommendationsFor($user)->where('status', 'open')->count();

        $patterns = Pattern::orderByDesc('companies_count')->get()
            ->map(function ($p) {
                $p->open_recs = Recommendation::where('pattern_id', $p->id)->where('status', 'open')->count();
                $p->total_recs = Recommendation::where('pattern_id', $p->id)->count();

                return $p;
            });

        return view('patterns', [
            'user' => $user,
            'navOpen' => $navOpen,
            'patterns' => $patterns,
        ]);
    }

    public function learning(Request $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'allocore', 403);

        $navOpen = $this->recommendationsFor($user)->where('status', 'open')->count();

        $rules = Recommendation::selectRaw('code, count(*) as n')
            ->groupBy('code')
            ->orderByDesc('n')
            ->get()
            ->map(function ($r) {
                $eff = DecisionEngine::effectiveness($r->code);
                $ids = Recommendation::where('code', $r->code)->pluck('id');
                $outcomes = Outcome::whereIn('recommendation_id', $ids)->selectRaw('result, count(*) as n')->groupBy('result')->pluck('n', 'result');

                return (object) [
                    'code' => $r->code,
                    'recommendations' => $r->n,
                    'open' => Recommendation::where('code', $r->code)->where('status', 'open')->count(),
                    'tried' => $eff['tried'],
                    'succeeded' => (int) ($outcomes['success'] ?? 0),
                    'failed' => (int) ($outcomes['failed'] ?? 0),
                    'dismissed' => (int) ($outcomes['dismissed'] ?? 0),
                    'success_rate' => $eff['success_rate'],
                ];
            });

        return view('learning', [
            'user' => $user,
            'navOpen' => $navOpen,
            'rules' => $rules,
            'overall' => $this->successRate(),
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

    private function sortBySeverity($recs)
    {
        $order = ['critical' => 0, 'warning' => 1, 'info' => 2];

        return $recs->sortBy(fn ($r) => $order[$r->severity] ?? 3)->values();
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
