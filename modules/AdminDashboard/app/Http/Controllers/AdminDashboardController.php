<?php

namespace Modules\AdminDashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admindashboard::index');
    }

    public function statistics(Request $request) 
    {
        [$startDate, $endDate] = \Core::parseDateRange($request);
        return response()->json([
            "status" => 1,
            "data" => view('admindashboard::statistics', [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])->render()
        ]);
    }
}
