<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Recommendation;
use App\Services\RecommendationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $recommendations = $company->recommendations()
            ->with('actionMeasure:key,name,description', 'outcome')
            ->latest()
            ->get();

        return response()->json($recommendations);
    }

    public function refresh(Company $company, RecommendationEngine $engine): JsonResponse
    {
        return response()->json($engine->refreshFor($company));
    }

    public function update(Request $request, Recommendation $recommendation): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,implemented,dismissed'],
        ]);

        $recommendation->update($data);

        return response()->json($recommendation);
    }
}
