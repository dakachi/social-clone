<?php

namespace Modules\AdminUserReport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminUserReportController extends Controller
{

    public function index()
    {
        return view(module("key") . '::index');
    }

    public function statistics(Request $request) 
    {
        [$startDate, $endDate] = \Core::parseDateRange($request);
        return response()->json([
            "status" => 1,
            "data" => view(module("key") . '::statistics', [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])->render()
        ]);
    }

    public function exportPdf(Request $request)
    {
        $charts = $request->input('charts');

        $info = \UserReport::summary();
        $latestUsers = \UserReport::latestUsers();

        $pdf = \Pdf::loadView(module('key') . '::export_pdf', [
            'charts' => $charts,
            'info' => $info,
            'latestUsers' => $latestUsers
        ])->setPaper('a4', 'portrait');

        return $pdf->download('user_report_' . now()->format('Ymd_His') . '.pdf');
    }
}
