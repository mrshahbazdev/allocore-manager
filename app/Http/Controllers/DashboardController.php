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

        $recs = Recommendation::query()
            ->with('latestOutcome')
            ->when($user->role === 'member' && $user->company_key,
                fn ($q) => $q->where(fn ($q2) => $q2->where('company_key', $user->company_key)->orWhereNull('company_key')))
            ->when($user->role === 'member' && ! $user->company_key,
                fn ($q) => $q->where('company_key', 'default'))
            ->orderByRaw("case severity when 'critical' then 0 when 'warning' then 1 else 2 end")
            ->orderByDesc('created_at')
            ->get();

        $open = $recs->where('status', 'open')->values();
        $done = $recs->whereIn('status', ['done', 'dismissed'])->values();

        $stats = [
            'signals' => Signal::count(),
            'open' => $open->count(),
            'done' => $done->count(),
            'success_rate' => $this->successRate(),
        ];

        return view('app', [
            'user' => $user,
            'open' => $open,
            'done' => $done,
            'stats' => $stats,
            'platformIntel' => in_array($user->role, ['platform_manager', 'allocore']) ? $this->platformIntel() : null,
            'allocoreIntel' => $user->role === 'allocore' ? $this->allocoreIntel() : null,
            'disavoIntel' => $user->role === 'disavo' ? $this->disavoIntel() : null,
        ]);
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
