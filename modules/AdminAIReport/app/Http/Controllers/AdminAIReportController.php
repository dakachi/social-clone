<?php

namespace Modules\AdminAIReport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminAIReportController extends Controller
{
    public function index()
    {
        return view(module('key').'::index');
    }

    public function statistics(Request $request) 
    {
        [$startDate, $endDate] = \Core::parseDateRange($request);
        return response()->json([
            "status" => 1,
            "data" => view(module('key').'::statistics', [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])->render()
        ]);
    }

    public function exportPdf(Request $request)
    {
        [$startDate, $endDate] = \Core::parseDateRange($request);

        $modelChart  = \Credit::getCreditUsageByModel(-1, $startDate, $endDate, 'ai_%');
        $usageChart  = \Credit::getCreditUsageChartData(-1, $startDate, $endDate, 'ai_%');

        $charts = $request->input('charts', []);

        $startDateText = $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : (string)$startDate;
        $endDateText   = $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : (string)$endDate;

        $pdf = Pdf::loadView('adminaireport::export_pdf', [
            'startDate'  => $startDateText,
            'endDate'    => $endDateText,
            'modelChart' => $modelChart,
            'usageChart' => $usageChart,
            'charts'     => $charts,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('ai_credit_report_' . now()->format('Ymd_His') . '.pdf');
    }
}
