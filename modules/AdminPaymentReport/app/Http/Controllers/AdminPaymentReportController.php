<?php

namespace Modules\AdminPaymentReport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminPaymentReportController extends Controller
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
        $charts = $request->input('charts');

        [$startDate, $endDate] = \Core::parseDateRange($request);
        $info = \PaymentReport::paymentInfo($startDate, $endDate);
        $latestPayments = \PaymentReport::latestPayments();

        $pdf = \Pdf::loadView('adminpaymentreport::export_pdf', [
            'charts' => $charts,
            'info' => $info,
            'latestPayments' => $latestPayments,
            'startDate' => $startDate,
            'endDate' => $endDate
        ])->setPaper('a4', 'portrait');

        return $pdf->download('payment_report_' . now()->format('Ymd_His') . '.pdf');
    }
}
