<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Outcome;
use App\Models\Recommendation;
use App\Services\LearningLoop;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $challenge = $request->query('challenge');
        $companyId = $request->query('company_id');

        $query = Recommendation::with('company', 'actionMeasure', 'outcome')
            ->latest();

        if (in_array($status, ['pending', 'accepted', 'implemented', 'dismissed'])) {
            $query->where('status', $status);
        }
        if ($challenge) {
            $query->where('challenge_key', $challenge);
        }
        if ($companyId) {
            $query->where('company_id', (int) $companyId);
        }

        return view('recommendations.index', [
            'recommendations' => $query->limit(200)->get(),
            'status' => $status,
            'challenge' => $challenge,
            'companyId' => $companyId,
            'challenges' => Recommendation::whereNotNull('challenge_key')->distinct()->orderBy('challenge_key')->pluck('challenge_key'),
        ]);
    }

    public function outcomes(Request $request)
    {
        $result = $request->query('result', '');
        $challenge = $request->query('challenge', '');

        $outcomes = Outcome::with('recommendation.company', 'recommendation.actionMeasure')
            ->when($result !== '', fn ($q) => $q->where('result', $result))
            ->when($challenge !== '', fn ($q) => $q->whereHas('recommendation', fn ($r) => $r->where('challenge_key', $challenge)))
            ->latest('measured_at')
            ->paginate(50)
            ->withQueryString();

        return view('recommendations.outcomes', [
            'outcomes' => $outcomes,
            'result' => $result,
            'challenge' => $challenge,
            'challenges' => Outcome::join('recommendations', 'outcomes.recommendation_id', '=', 'recommendations.id')
                ->whereNotNull('recommendations.challenge_key')
                ->distinct()->orderBy('recommendations.challenge_key')
                ->pluck('recommendations.challenge_key'),
            'byResult' => Outcome::selectRaw('result, count(*) as total')
                ->groupBy('result')->pluck('total', 'result'),
        ]);
    }

    public function show(Recommendation $recommendation)
    {
        return view('recommendations.show', [
            'recommendation' => $recommendation->load('company', 'actionMeasure', 'signal', 'outcome'),
        ]);
    }


    /**
     * Accept/dismiss/implement many recommendations at once — one
     * decision instead of ten clicks.
     */
    public function bulkUpdate(Request $request, LearningLoop $loop)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:recommendations,id'],
            'status' => ['required', 'in:accepted,implemented,dismissed'],
        ]);

        $count = 0;
        foreach (Recommendation::whereIn('id', $data['ids'])->where('status', 'pending')->get() as $rec) {
            $rec->update(['status' => $data['status']]);
            if ($data['status'] === 'dismissed') {
                $loop->recordDismissal($rec);
            }
            $count++;
        }

        return back()->with('status', "{$count} recommendation(s) {$data['status']}.");
    }

    public function update(Request $request, Recommendation $recommendation, LearningLoop $loop)
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,implemented,dismissed'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $recommendation->update(['status' => $data['status']]);

        if ($data['status'] === 'dismissed') {
            if (! empty($data['reason'])) {
                $recommendation->update(['rationale' => array_merge(
                    $recommendation->rationale ?? [],
                    ['dismiss_reason' => $data['reason']]
                )]);
            }
            $loop->recordDismissal($recommendation, $data['reason'] ?? null);
        }

        return back()->with('status', "Recommendation {$data['status']}.");
    }

    public function editOutcome(Recommendation $recommendation)
    {
        return view('recommendations.outcome', ['recommendation' => $recommendation]);
    }

    public function outcome(Request $request, Recommendation $recommendation, LearningLoop $loop)
    {
        $data = $request->validate([
            'result' => ['required', 'in:success,failure,partial'],
            'failure_reason' => ['nullable', 'string', 'max:255', 'required_if:result,failure'],
        ]);

        $loop->recordOutcome(
            $recommendation,
            $data['result'],
            $data['failure_reason'] ?? null
        );

        return back()->with('status', 'Outcome recorded — patterns updated.');
    }
}
