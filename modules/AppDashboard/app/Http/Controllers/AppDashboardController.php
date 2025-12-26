<?php

namespace Modules\AppDashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppDashboardController extends Controller
{
    public function index()
    {
        return view('appdashboard::index');
    }

    public function statistics(Request $request) 
    {
        [$startDate, $endDate] = \Core::parseDateRange($request);
        return response()->json([
            "status" => 1,
            "data" => view('appdashboard::statistics', [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])->render()
        ]);
    }
}
