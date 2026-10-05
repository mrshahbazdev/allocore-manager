<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TrendDetector;
use Illuminate\Http\Request;

class TrendController extends Controller
{
    public function index(Request $request, TrendDetector $detector)
    {
        $window = (int) $request->query('days', 30);
        $window = in_array($window, [7, 14, 30, 60, 90]) ? $window : 30;

        return view('trends.index', [
            'trends' => $detector->detect($window),
            'window' => $window,
        ]);
    }
}
