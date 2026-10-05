<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserIntelligenceController extends Controller
{
    /**
     * Users across the ecosystem (by the source's own user id), with their
     * activity and the best next action waiting for their company.
     */
    public function index(Request $request)
    {
        $sourceId = $request->query('source_id', '');

        $users = Signal::whereNotNull('external_user_id')
            ->when($sourceId !== '', fn ($q) => $q->where('source_id', $sourceId))
            ->select('external_user_id', 'source_id', DB::raw('count(*) as total'), DB::raw('max(occurred_at) as last_seen'))
            ->groupBy('external_user_id', 'source_id')
            ->orderByDesc('total')
            ->paginate(25)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'sourceId' => $sourceId,
            'sources' => Source::orderBy('name')->get(),
        ]);
    }

    public function show(string $externalUserId)
    {
        $signals = Signal::where('external_user_id', $externalUserId)
            ->with('source', 'company')
            ->latest('occurred_at')
            ->paginate(50);

        $companyIds = $signals->pluck('company_id')->filter()->unique();
        $recommendations = Recommendation::whereIn('company_id', $companyIds)
            ->with('actionMeasure', 'company')
            ->where('status', Recommendation::STATUS_PENDING)
            ->orderByDesc('confidence')
            ->get();

        return view('users.show', [
            'externalUserId' => $externalUserId,
            'signals' => $signals,
            'recommendations' => $recommendations,
            'topChallenges' => Signal::where('external_user_id', $externalUserId)
                ->whereNotNull('challenge_key')
                ->select('challenge_key', DB::raw('count(*) as total'))
                ->groupBy('challenge_key')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            'companyCount' => $companyIds->count(),
        ]);
    }
}
