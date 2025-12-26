<?php

namespace Modules\AdminAffiliate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminAffiliateController extends Controller
{
    public $table;
    public $modules;
    public function __construct()
    {
        $this->table = "affiliate";
    }

    public function index()
    {
        $total = DB::table($this->table)->count();
        return view(module("key").'::index', [
            'total' => 0,
        ]);
    }

    public function statistics(Request $request)
    {
        $uid = $request->user_id
            ? User::where('id_secure', $request->user_id)->value('id') ?? 0
            : 0;

        [$startDate, $endDate] = \Core::parseDateRange($request);
        $response = \Affiliate::info(false, $uid);
        $commissionChart = \Affiliate::reportCommissionByMonth($uid, $startDate, $endDate);
        $commissionChartByDay = \Affiliate::reportCommissionByDay($uid, $startDate, $endDate);
        $reportWithdrawalByDay = \Affiliate::reportWithdrawalByDay($uid, $startDate, $endDate);

        $statusData = [
            ['name' => 'Approved', 'y' => (int)$response['approved_count']],
            ['name' => 'Rejected', 'y' => (int)$response['rejected_count']],
            ['name' => 'Pending',  'y' => (int)$response['pending_count']],
        ];

        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::statistics', [
                "result" => $response,
                "commissionChart" => $commissionChart,
                "commissionChartByDay" => $commissionChartByDay,
                "reportWithdrawalByDay" => $reportWithdrawalByDay,
                "statusData" => $statusData,
            ])->render()
        ]);
    }

    public function exportPdf(Request $request)
    {
        $charts = $request->input('charts');

        $uid = $request->user_id
            ? \App\Models\User::where('id_secure', $request->user_id)->value('id') ?? 0
            : 0;

        $info = \Affiliate::info(false, $uid);

        $pdf = Pdf::loadView(module("key") . '::export_pdf', [
            'charts' => $charts,
            'info' => $info
        ])->setPaper('a4', 'portrait');

        return $pdf->download('affiliate_report_' . now()->format('Ymd_His') . '.pdf');

    }

}


